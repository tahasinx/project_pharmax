<?php

/**
 * Installation Controller
 *
 * @license    MIT License
 */

namespace App\Http\Controllers;

use App\Domain\Access\PermissionCatalog;
use App\Models\User;
use App\Services\Platform\SchemaCompare;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use PDO;
use PDOException;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

/**
 * InstallationController
 *
 * Handles the installation wizard.
 * Provides step-by-step installation process.
 */
class InstallationController extends Controller
{
    /**
     * Check if installation is already completed.
     */
    private function checkInstallationLock(): bool
    {
        $lockFile = storage_path('install.lock');

        return file_exists($lockFile);
    }

    /**
     * Create installation lock file.
     */
    private function createLockFile(): void
    {
        $lockFile = storage_path('install.lock');
        file_put_contents($lockFile, date('Y-m-d H:i:s'));
    }

    /**
     * Show installation welcome/check page.
     */
    public function index()
    {
        if ($this->checkInstallationLock()) {
            return redirect('/login')->with('info', 'Installation already completed.');
        }

        // Check PHP version
        $phpVersion   = PHP_VERSION;
        $phpVersionOk = version_compare($phpVersion, '8.1.0', '>=');

        // Check requirements
        $envExists = file_exists(base_path('.env'));

        $requirements = [
            'php_version' => [
                'name'   => 'PHP Version (8.1+)',
                'status' => $phpVersionOk,
                'value'  => $phpVersion,
            ],
            'env_file' => [
                'name'   => '.env File',
                'status' => true, // Always OK - will be created/overwritten during installation
                'value'  => $envExists ? 'Exists (will be overwritten)' : 'Will be created',
            ],
            'storage_writable' => [
                'name'   => 'Storage Directory Writable',
                'status' => is_writable(storage_path()),
                'value'  => is_writable(storage_path()) ? 'Yes' : 'No',
            ],
            'bootstrap_writable' => [
                'name'   => 'Bootstrap Cache Writable',
                'status' => is_writable(base_path('bootstrap/cache')),
                'value'  => is_writable(base_path('bootstrap/cache')) ? 'Yes' : 'No',
            ],
        ];

        $allRequirementsMet = collect($requirements)->every(function ($req) {
            return $req['status'];
        });

        return view('install.pages.index', compact('requirements', 'allRequirementsMet', 'phpVersion'));
    }

    /**
     * Show database configuration step.
     */
    public function database()
    {
        if ($this->checkInstallationLock()) {
            return redirect()->route('install.complete');
        }

        return view('install.pages.database');
    }

    /**
     * Test database connection and proceed.
     */
    public function testDatabase(Request $request)
    {
        $request->validate([
            'db_host'     => 'required|string',
            'db_port'     => 'required|numeric',
            'db_database' => 'required|string',
            'db_username' => 'required|string',
            'db_password' => 'nullable|string',
        ]);

        try {
            $pdo = new PDO(
                "mysql:host={$request->db_host};port={$request->db_port}",
                $request->db_username,
                $request->db_password,
                [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION],
            );

            // Try to create database if it doesn't exist
            $pdo->exec("CREATE DATABASE IF NOT EXISTS `{$request->db_database}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");

            // Save to session
            session([
                'db_config' => [
                    'host'     => $request->db_host,
                    'port'     => $request->db_port,
                    'database' => $request->db_database,
                    'username' => $request->db_username,
                    'password' => $request->db_password,
                ],
            ]);

            return redirect()->route('install.app')->with('success', 'Database connection successful!');
        } catch (PDOException $e) {
            return back()->withInput()->withErrors(['database' => 'Database connection failed: '.$e->getMessage()]);
        }
    }

    /**
     * Show application configuration step.
     */
    public function app()
    {
        if ($this->checkInstallationLock()) {
            return redirect()->route('install.complete');
        }

        if (! session()->has('db_config')) {
            return redirect()->route('install.database');
        }

        // Get saved app config from session if exists
        $appConfig = session('app_config', []);

        return view('install.pages.app', [
            'appConfig' => $appConfig,
        ]);
    }

    /**
     * Save application configuration.
     * Creates/updates .env file from .env.example if it exists.
     */
    public function saveApp(Request $request)
    {
        $request->validate([
            'app_name'  => 'required|string|max:255',
            'app_url'   => 'required|url',
            'app_env'   => 'required|in:local,production',
            'app_debug' => 'nullable|boolean',
        ]);

        if (! session()->has('db_config')) {
            return redirect()->route('install.database');
        }

        session([
            'app_config' => [
                'name'  => $request->app_name,
                'url'   => $request->app_url,
                'env'   => $request->app_env,
                'debug' => $request->has('app_debug') ? 'true' : 'false',
            ],
        ]);

        // Create/update .env file from .env.example if it exists
        $envExample = base_path('.env.example');
        $envFile    = base_path('.env');

        // Copy from .env.example if it exists and .env doesn't exist
        if (! file_exists($envFile) && file_exists($envExample)) {
            copy($envExample, $envFile);
        }

        // If .env still doesn't exist, create minimal one
        if (! file_exists($envFile)) {
            $minimalEnv = "APP_NAME=Laravel\nAPP_ENV=local\nAPP_KEY=\nAPP_DEBUG=true\nAPP_URL=http://localhost\n\nLOG_CHANNEL=stack\nLOG_LEVEL=debug\n\nDB_CONNECTION=mysql\nDB_HOST=127.0.0.1\nDB_PORT=3306\nDB_DATABASE=\nDB_USERNAME=\nDB_PASSWORD=\n\nSESSION_DRIVER=file\nSESSION_LIFETIME=120\n";
            file_put_contents($envFile, $minimalEnv);
        }

        // Read and update .env content
        $envContent = file_get_contents($envFile);
        $dbConfig   = session('db_config');
        $appConfig  = [
            'name'  => $request->app_name,
            'url'   => $request->app_url,
            'env'   => $request->app_env,
            'debug' => $request->has('app_debug') ? 'true' : 'false',
        ];

        // Update database configuration
        $replacements = [
            'DB_HOST='     => 'DB_HOST='.($dbConfig['host'] ?? '127.0.0.1'),
            'DB_PORT='     => 'DB_PORT='.($dbConfig['port'] ?? '3306'),
            'DB_DATABASE=' => 'DB_DATABASE='.($dbConfig['database'] ?? ''),
            'DB_USERNAME=' => 'DB_USERNAME='.($dbConfig['username'] ?? 'root'),
            'DB_PASSWORD=' => 'DB_PASSWORD='.($dbConfig['password'] ?? ''),
        ];

        // Update application configuration
        $replacements['APP_NAME=']  = 'APP_NAME="'.$appConfig['name'].'"';
        $replacements['APP_URL=']   = 'APP_URL='.$appConfig['url'];
        $replacements['APP_ENV=']   = 'APP_ENV='.$appConfig['env'];
        $replacements['APP_DEBUG='] = 'APP_DEBUG='.$appConfig['debug'];

        foreach ($replacements as $search => $replace) {
            $envContent = preg_replace('/^'.preg_quote($search, '/').'.*$/m', $replace, $envContent);
        }

        // Ensure APP_KEY exists and is not empty
        if (! preg_match('/^APP_KEY=(.+)$/m', $envContent, $matches) || empty(trim($matches[1] ?? ''))) {
            // Generate key if missing
            $key = 'base64:'.base64_encode(random_bytes(32));
            if (preg_match('/^APP_KEY=.*$/m', $envContent)) {
                $envContent = preg_replace('/^APP_KEY=.*$/m', 'APP_KEY='.$key, $envContent);
            } else {
                // Add APP_KEY if completely missing
                $envContent = "APP_KEY={$key}\n".$envContent;
            }
        }

        file_put_contents($envFile, $envContent);

        return redirect()->route('install.admin');
    }

    /**
     * Show admin user creation step.
     */
    public function admin()
    {
        if ($this->checkInstallationLock()) {
            return redirect()->route('install.complete');
        }

        if (! session()->has('app_config')) {
            return redirect()->route('install.app');
        }

        return view('install.pages.admin');
    }

    /**
     * Save admin user configuration.
     */
    public function saveAdmin(Request $request)
    {
        $request->validate([
            'name'             => 'required|string|max:255',
            'email'            => 'required|email|max:255',
            'password'         => 'required|min:9|regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[^A-Za-z\d]).{9,}$/',
            'confirm_password' => 'required|same:password',
        ]);

        session([
            'admin_config' => [
                'name'     => $request->name,
                'email'    => $request->email,
                'password' => $request->password,
            ],
        ]);

        return redirect()->route('install.install');
    }

    /**
     * Show installation confirmation step.
     */
    public function install()
    {
        if ($this->checkInstallationLock()) {
            return redirect()->route('install.complete');
        }

        if (! session()->has('admin_config')) {
            return redirect()->route('install.admin');
        }

        return view('install.pages.install');
    }

    /**
     * Execute installation.
     */
    public function execute(Request $request)
    {
        if ($this->checkInstallationLock()) {
            return redirect()->route('install.complete');
        }

        if (! session()->has('db_config') || ! session()->has('app_config') || ! session()->has('admin_config')) {
            return redirect()->route('install.index')->withErrors(['error' => 'Installation data missing. Please start over.']);
        }

        try {
            // .env file should already be created/updated in saveApp()
            // Just verify it exists
            $envFile = base_path('.env');
            if (! file_exists($envFile)) {
                throw new \Exception('.env file not found. Please go back and complete the application configuration step.');
            }

            // Try to run artisan commands
            $results     = [];
            $adminConfig = session('admin_config');

            // Generate application key
            try {
                Artisan::call('key:generate', ['--force' => true]);
                $results['key_generate'] = [
                    'success' => true,
                    'message' => 'Application key generated',
                ];
            } catch (\Exception $e) {
                $results['key_generate'] = [
                    'success' => false,
                    'message' => $e->getMessage(),
                ];
            }

            // Run migrations
            try {
                Artisan::call('migrate', [
                    '--force' => true,
                    '--path'  => SchemaCompare::TENANT_PATH,
                ]);
                $results['migrate'] = [
                    'success' => true,
                    'message' => 'Database migrations completed',
                ];
            } catch (\Exception $e) {
                $results['migrate'] = [
                    'success' => false,
                    'message' => $e->getMessage(),
                ];
            }

            // Create admin user with Spatie permissions
            try {
                // Check if user already exists
                $user = User::where('email', $adminConfig['email'])->first();

                if ($user) {
                    // Update existing user
                    $user->update([
                        'name'              => $adminConfig['name'],
                        'password'          => Hash::make($adminConfig['password']),
                        'email_verified_at' => now(),
                    ]);
                } else {
                    // Create new user
                    $user = User::create([
                        'name'              => $adminConfig['name'],
                        'email'             => $adminConfig['email'],
                        'password'          => Hash::make($adminConfig['password']),
                        'email_verified_at' => now(),
                    ]);
                }

                // Create roles
                $adminRole = Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
                Role::firstOrCreate(['name' => 'manager', 'guard_name' => 'web']);
                Role::firstOrCreate(['name' => 'cashier', 'guard_name' => 'web']);

                // Create permissions
                $permissions = PermissionCatalog::names();

                // Create permissions using DB transaction for better error handling
                DB::beginTransaction();
                try {
                    $existing = Permission::pluck('name')->toArray();
                    $new      = array_diff($permissions, $existing);

                    if (! empty($new)) {
                        $insert = array_map(function ($name) {
                            return [
                                'name'       => $name,
                                'guard_name' => 'web',
                                'created_at' => now(),
                                'updated_at' => now(),
                            ];
                        }, $new);

                        DB::table('permissions')->insert($insert);
                    }

                    DB::commit();
                } catch (\Exception $e) {
                    DB::rollBack();
                    Log::error('Permission insert failed', ['error' => $e->getMessage()]);
                    // Continue - permissions might already exist
                }

                // Clear Spatie permission cache
                try {
                    app()[PermissionRegistrar::class]->forgetCachedPermissions();
                } catch (\Exception $e) {
                    // Ignore cache clearing errors
                }

                PermissionCatalog::sync();

                // Sync all permissions to admin role
                try {
                    $allPermissions = Permission::all();
                    if ($allPermissions->isNotEmpty()) {
                        $adminRole->syncPermissions($allPermissions);
                    }
                } catch (\Exception $e) {
                    Log::warning('Failed to sync permissions to admin role', ['error' => $e->getMessage()]);
                }

                // Assign admin role to user
                try {
                    if (! $user->hasRole($adminRole)) {
                        $user->assignRole($adminRole);
                    }
                } catch (\Exception $e) {
                    Log::warning('Failed to assign admin role to user', ['error' => $e->getMessage()]);
                }

                $results['admin_user'] = [
                    'success' => true,
                    'message' => 'Admin user created successfully',
                ];
            } catch (\Exception $e) {
                $results['admin_user'] = [
                    'success' => false,
                    'message' => $e->getMessage(),
                ];
            }

            // Create lock file
            $this->createLockFile();

            // Save results to session
            session(['install_results' => $results]);

            return redirect()->route('install.complete');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Installation failed: '.$e->getMessage()]);
        }
    }

    /**
     * Show installation complete page.
     */
    public function complete()
    {
        if (! $this->checkInstallationLock()) {
            return redirect()->route('install.index');
        }

        $results = session('install_results', []);

        return view('install.pages.complete', compact('results'));
    }
}
