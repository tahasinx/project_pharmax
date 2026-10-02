<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\Platform\SchemaCompare;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class InstallController extends Controller
{
    public function index()
    {
        if ($this->isInstalled()) {
            return redirect()->route('dashboard');
        }

        return inertia('Install/Index', [
            'requirements' => $this->checkRequirements(),
            'permissions'  => $this->checkPermissions(),
            'appName'      => config('app.name'),
        ]);
    }

    public function checkDatabase(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'db_host'     => 'required|string',
            'db_port'     => 'required|integer',
            'db_name'     => 'required|string',
            'db_username' => 'required|string',
            'db_password' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        $connection = $this->testDatabaseConnection($request->all());

        if ($connection['success']) {
            // store to session for later install step
            session([
                'install.db_host'     => $request->db_host,
                'install.db_port'     => $request->db_port,
                'install.db_name'     => $request->db_name,
                'install.db_username' => $request->db_username,
                'install.db_password' => $request->db_password,
            ]);

            return response()->json(['success' => true, 'message' => 'Database connection successful']);
        }

        return response()->json(['success' => false, 'message' => $connection['message']], 422);
    }

    public function install(Request $request)
    {
        // increase limits for long-running installation
        @set_time_limit(600);
        ini_set('max_execution_time', '600');
        ini_set('memory_limit', '512M');

        $validator = Validator::make($request->all(), [
            'app_name'        => 'required|string|max:255',
            'app_url'         => 'required|url',
            'admin_name'      => 'required|string|max:255',
            'admin_email'     => 'required|email|max:255',
            'admin_password'  => 'required|string|min:8',
            'company_name'    => 'required|string|max:255',
            'company_email'   => 'required|email|max:255',
            'company_phone'   => 'required|string|max:20',
            'company_address' => 'required|string|max:500',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        try {
            Log::info('Installation request received', [
                'payload' => array_keys($request->all()),
            ]);

            // retrieve DB config saved in checkDatabase step
            $databaseConfig = [
                'db_host'     => session('install.db_host'),
                'db_port'     => session('install.db_port'),
                'db_name'     => session('install.db_name'),
                'db_username' => session('install.db_username'),
                'db_password' => session('install.db_password'),
            ];

            if (empty($databaseConfig['db_host']) || empty($databaseConfig['db_name'])) {
                return response()->json(['success' => false, 'message' => 'Database configuration not found. Please run database test step first.'], 422);
            }

            $allConfig = array_merge($request->all(), $databaseConfig);

            // 1) Ensure .env exists (create from .env.example if needed) and write basic APP and DB keys
            $this->ensureEnvExists();
            $this->updateEnvFile($allConfig);

            // 2) Override runtime config immediately (don't rely on reload)
            Config::set('database.connections.mysql.host', $allConfig['db_host']);
            Config::set('database.connections.mysql.port', $allConfig['db_port']);
            Config::set('database.connections.mysql.database', $allConfig['db_name']);
            Config::set('database.connections.mysql.username', $allConfig['db_username']);
            Config::set('database.connections.mysql.password', $allConfig['db_password'] ?? '');
            Config::set('app.name', $allConfig['app_name']);
            Config::set('app.url', $allConfig['app_url']);

            // 3) Purge and reconnect the mysql connection
            DB::purge('mysql');
            DB::reconnect('mysql');

            // Optional: test the new connection before migrations
            try {
                DB::connection('mysql')->getPdo();
            } catch (\Exception $e) {
                throw new \Exception('Unable to connect to the database with provided credentials: '.$e->getMessage());
            }

            // 4) Run pharmacy (tenant) migrations only — never central/
            Log::info('Install Step: running tenant migrations');
            $migrateExit = Artisan::call('migrate', [
                '--force' => true,
                '--path'  => SchemaCompare::TENANT_PATH,
            ]);

            if ($migrateExit !== 0) {
                Log::error('tenant migrate failed', ['exit' => $migrateExit, 'output' => Artisan::output()]);
                throw new \Exception('Database migrations failed: '.Artisan::output());
            }

            // 5) Create / update admin user, roles, permissions
            // $this->createAdminUser($allConfig);

            // 6) Seed database (if you have seeders)
            Log::info('Install Step: running db:seed');
            $seedExit = Artisan::call('db:seed', ['--force' => true]);
            if ($seedExit !== 0) {
                Log::warning('db:seed returned non-zero exit', ['exit' => $seedExit, 'output' => Artisan::output()]);
                // not fatal in many apps, but let's treat as error if seeds are expected
            }

            // 7) Create installation flag
            $this->createInstallationFlag();

            // 8) Clear caches
            try {
                Artisan::call('cache:clear');
                Artisan::call('config:clear');
                Artisan::call('route:clear');
                Artisan::call('view:clear');
            } catch (\Exception $e) {
                Log::warning('Cache clearing failed', ['error' => $e->getMessage()]);
            }

            return response()->json([
                'success'      => true,
                'message'      => 'Installation completed successfully!',
                'redirect_url' => route('login'),
            ]);
        } catch (\Exception $e) {
            Log::error('Installation failed', [
                'message' => $e->getMessage(),
                'trace'   => $e->getTraceAsString(),
            ]);

            $msg = $e->getMessage();
            if (strpos($msg, 'SQLSTATE') !== false || strpos(strtolower($msg), 'access denied') !== false) {
                $userMsg = 'Database error during installation. Please verify credentials and ensure the database exists and user has privileges.';
            } elseif (strpos(strtolower($msg), 'timeout') !== false) {
                $userMsg = 'Installation timed out. Check server resources and try again.';
            } else {
                $userMsg = 'Installation failed: '.$e->getMessage();
            }

            return response()->json(['success' => false, 'message' => $userMsg], 500);
        }
    }

    protected function isInstalled()
    {
        return File::exists(storage_path('app/installed'));
    }

    protected function checkRequirements()
    {
        return [
            'php_version' => [
                'name'     => 'PHP Version',
                'required' => '8.1.0',
                'current'  => PHP_VERSION,
                'status'   => version_compare(PHP_VERSION, '8.1.0', '>='),
            ],
            'openssl' => [
                'name'     => 'OpenSSL Extension',
                'required' => 'Enabled',
                'current'  => extension_loaded('openssl') ? 'Enabled' : 'Disabled',
                'status'   => extension_loaded('openssl'),
            ],
            'pdo' => [
                'name'     => 'PDO Extension',
                'required' => 'Enabled',
                'current'  => extension_loaded('pdo') ? 'Enabled' : 'Disabled',
                'status'   => extension_loaded('pdo'),
            ],
            'mbstring' => [
                'name'     => 'Mbstring Extension',
                'required' => 'Enabled',
                'current'  => extension_loaded('mbstring') ? 'Enabled' : 'Disabled',
                'status'   => extension_loaded('mbstring'),
            ],
            'tokenizer' => [
                'name'     => 'Tokenizer Extension',
                'required' => 'Enabled',
                'current'  => extension_loaded('tokenizer') ? 'Enabled' : 'Disabled',
                'status'   => extension_loaded('tokenizer'),
            ],
            'xml' => [
                'name'     => 'XML Extension',
                'required' => 'Enabled',
                'current'  => extension_loaded('xml') ? 'Enabled' : 'Disabled',
                'status'   => extension_loaded('xml'),
            ],
            'ctype' => [
                'name'     => 'Ctype Extension',
                'required' => 'Enabled',
                'current'  => extension_loaded('ctype') ? 'Enabled' : 'Disabled',
                'status'   => extension_loaded('ctype'),
            ],
            'json' => [
                'name'     => 'JSON Extension',
                'required' => 'Enabled',
                'current'  => extension_loaded('json') ? 'Enabled' : 'Disabled',
                'status'   => extension_loaded('json'),
            ],
            'bcmath' => [
                'name'     => 'BCMath Extension',
                'required' => 'Enabled',
                'current'  => extension_loaded('bcmath') ? 'Enabled' : 'Disabled',
                'status'   => extension_loaded('bcmath'),
            ],
        ];
    }

    protected function checkPermissions()
    {
        return [
            'storage' => [
                'path'     => storage_path(),
                'writable' => is_writable(storage_path()),
            ],
            'bootstrap_cache' => [
                'path'     => base_path('bootstrap/cache'),
                'writable' => is_writable(base_path('bootstrap/cache')),
            ],
            'public' => [
                'path'     => public_path(),
                'writable' => is_writable(public_path()),
            ],
        ];
    }

    protected function testDatabaseConnection($config)
    {
        try {
            $password = $config['db_password'] ?? '';

            $pdo = new \PDO(
                "mysql:host={$config['db_host']};port={$config['db_port']};dbname={$config['db_name']}",
                $config['db_username'],
                $password,
                [
                    \PDO::ATTR_ERRMODE            => \PDO::ERRMODE_EXCEPTION,
                    \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC,
                    \PDO::ATTR_TIMEOUT            => 5,
                ]
            );

            $stmt = $pdo->query('SELECT 1 as test');
            $stmt->fetch();

            return ['success' => true, 'message' => 'Database connection successful'];
        } catch (\PDOException $e) {
            $errorMessage = $e->getMessage();

            if (strpos($errorMessage, 'Access denied') !== false) {
                $errorMessage = 'Access denied. Check username/password and privileges.';
            } elseif (strpos($errorMessage, 'Unknown database') !== false) {
                $errorMessage = 'Database "'.($config['db_name'] ?? '').'" does not exist. Create it first.';
            } elseif (strpos($errorMessage, 'Connection refused') !== false) {
                $errorMessage = 'Cannot connect to MySQL server. Check host/port and that MySQL is running.';
            } elseif (strpos(strtolower($errorMessage), 'timeout') !== false) {
                $errorMessage = 'Connection timeout. Check network and MySQL server.';
            }

            return ['success' => false, 'message' => $errorMessage];
        }
    }

    /**
     * Ensure .env exists. If not, try to create from .env.example
     */
    protected function ensureEnvExists()
    {
        $envPath = base_path('.env');
        $example = base_path('.env.example');

        if (! File::exists($envPath)) {
            if (File::exists($example)) {
                File::copy($example, $envPath);
            } else {
                // create minimal .env if example missing
                $content = "APP_NAME=\"Laravel\"\nAPP_ENV=local\nAPP_KEY=\nAPP_DEBUG=true\nAPP_URL=http://localhost\n\n";
                $content .= "DB_CONNECTION=mysql\nDB_HOST=127.0.0.1\nDB_PORT=3306\nDB_DATABASE=homestead\nDB_USERNAME=homestead\nDB_PASSWORD=\n";
                File::put($envPath, $content);
            }
        }
    }

    /**
     * Robust .env writer: replace or append keys
     */
    protected function updateEnvFile(array $data)
    {
        $envPath = base_path('.env');

        if (! File::exists($envPath)) {
            throw new \Exception('.env file not found at '.$envPath);
        }

        $content = File::get($envPath);

        $replacements = [
            'APP_NAME'    => "\"{$data['app_name']}\"",
            'APP_URL'     => $data['app_url'],
            'DB_HOST'     => $data['db_host'],
            'DB_PORT'     => $data['db_port'],
            'DB_DATABASE' => $data['db_name'],
            'DB_USERNAME' => $data['db_username'],
            'DB_PASSWORD' => ($data['db_password'] ?? ''),
        ];

        foreach ($replacements as $key => $value) {
            $pattern = "/^{$key}=.*$/m";
            $line    = "{$key}={$value}";
            if (preg_match($pattern, $content)) {
                $content = preg_replace($pattern, $line, $content);
            } else {
                // append new key
                $content .= PHP_EOL.$line;
            }
        }

        File::put($envPath, $content);

        // reset opcache if present
        if (function_exists('opcache_reset')) {
            @opcache_reset();
        }

        // clear config cache (best-effort)
        try {
            Artisan::call('config:clear');
        } catch (\Exception $e) {
            // not critical
        }
    }

    protected function createAdminUser(array $data)
    {
        // create/update admin user
        $user = User::where('email', $data['admin_email'])->first();

        if ($user) {
            $user->update([
                'name'              => $data['admin_name'],
                'password'          => Hash::make($data['admin_password']),
                'email_verified_at' => now(),
            ]);
        } else {
            $user = User::create([
                'name'              => $data['admin_name'],
                'email'             => $data['admin_email'],
                'password'          => Hash::make($data['admin_password']),
                'email_verified_at' => now(),
            ]);
        }

        // create roles
        $adminRole = Role::firstOrCreate(['name' => 'admin']);
        Role::firstOrCreate(['name' => 'manager']);
        Role::firstOrCreate(['name' => 'cashier']);

        // permissions (list)
        $permissions = [
            'view-medicines',
            'create-medicines',
            'edit-medicines',
            'delete-medicines',
            'view-customers',
            'create-customers',
            'edit-customers',
            'delete-customers',
            'view-invoices',
            'create-invoices',
            'edit-invoices',
            'delete-invoices',
            'view-stocks',
            'create-stocks',
            'edit-stocks',
            'delete-stocks',
            'view-purchases',
            'create-purchases',
            'edit-purchases',
            'delete-purchases',
            'view-reports',
            'manage-users',
            'manage-system',
            'manage-data',
        ];

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
            // not fatal — continue
        }

        // clear spatie cache
        try {
            app()[PermissionRegistrar::class]->forgetCachedPermissions();
        } catch (\Exception $e) {
            // ignore
        }

        // sync permissions to admin role
        try {
            $allPermissions = Permission::all();
            if ($allPermissions->isNotEmpty()) {
                $adminRole->syncPermissions($allPermissions);
            }
        } catch (\Exception $e) {
            Log::warning('Failed to sync permissions to admin role', ['error' => $e->getMessage()]);
        }

        // assign role to user
        try {
            if (! $user->hasRole($adminRole)) {
                $user->assignRole($adminRole);
            }
        } catch (\Exception $e) {
            Log::warning('Failed to assign admin role to user', ['error' => $e->getMessage()]);
        }

        // store default settings file
        try {
            $settings = [
                'company_name'        => $data['company_name'],
                'company_email'       => $data['company_email'],
                'company_phone'       => $data['company_phone'],
                'company_address'     => $data['company_address'],
                'currency_symbol'     => '$',
                'currency_position'   => 'before',
                'tax_rate'            => 10,
                'low_stock_threshold' => 10,
                'expiry_alert_days'   => 30,
            ];
            File::put(storage_path('app/settings.json'), json_encode($settings, JSON_PRETTY_PRINT));
        } catch (\Exception $e) {
            Log::warning('Failed to store settings.json', ['error' => $e->getMessage()]);
        }
    }

    protected function createInstallationFlag()
    {
        File::put(storage_path('app/installed'), now()->toISOString());
    }
}
