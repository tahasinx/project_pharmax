<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use App\Models\User;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class InstallController extends Controller
{
    public function index()
    {
        // Check if already installed
        if ($this->isInstalled()) {
            return redirect()->route('dashboard');
        }

        return inertia('Install/Index', [
            'requirements' => $this->checkRequirements(),
            'permissions' => $this->checkPermissions(),
            'appName' => config('app.name'),
        ]);
    }

    public function checkDatabase(Request $request)
    {
        // Debug logging
        \Log::info('Database check request received', [
            'method' => $request->method(),
            'content_type' => $request->header('Content-Type'),
            'data' => $request->all(),
        ]);

        $validator = Validator::make($request->all(), [
            'db_host' => 'required|string',
            'db_port' => 'required|integer',
            'db_name' => 'required|string',
            'db_username' => 'required|string',
            'db_password' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            \Log::error('Database validation failed', $validator->errors()->toArray());
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            // Test database connection
            $connection = $this->testDatabaseConnection($request->all());

            if ($connection['success']) {
                // Save database config to session
                session([
                    'install.db_host' => $request->db_host,
                    'install.db_port' => $request->db_port,
                    'install.db_name' => $request->db_name,
                    'install.db_username' => $request->db_username,
                    'install.db_password' => $request->db_password,
                ]);

                return response()->json([
                    'success' => true,
                    'message' => 'Database connection successful',
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => $connection['message'],
                ], 422);
            }
        } catch (\Exception $e) {
            \Log::error('Database connection exception', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Database connection failed: ' . $e->getMessage(),
            ], 422);
        }
    }

    public function install(Request $request)
    {
        // Increase execution time limit for installation process
        set_time_limit(300); // 5 minutes
        ini_set('max_execution_time', '300');
        ini_set('memory_limit', '256M');

        $validator = Validator::make($request->all(), [
            'app_name' => 'required|string|max:255',
            'app_url' => 'required|url',
            'admin_name' => 'required|string|max:255',
            'admin_email' => 'required|email|max:255',
            'admin_password' => 'required|string|min:8',
            'company_name' => 'required|string|max:255',
            'company_email' => 'required|email|max:255',
            'company_phone' => 'required|string|max:20',
            'company_address' => 'required|string|max:500',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            // Retrieve database configuration from session (saved during database test step)
            $databaseConfig = [
                'db_host' => session('install.db_host'),
                'db_port' => session('install.db_port'),
                'db_name' => session('install.db_name'),
                'db_username' => session('install.db_username'),
                'db_password' => session('install.db_password'),
            ];

            // Check if database config exists in session
            if (empty($databaseConfig['db_host']) || empty($databaseConfig['db_name'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Database configuration not found. Please test the database connection first.',
                ], 422);
            }

            // Merge database config with app config
            $allConfig = array_merge($request->all(), $databaseConfig);

            // Step 1: Update .env file
            $this->updateEnvFile($allConfig);

            // Step 2: Run migrations
            Artisan::call('migrate', ['--force' => true]);

            // Step 3: Create admin user
            $this->createAdminUser($allConfig);

            // Step 4: Seed initial data
            Artisan::call('db:seed', ['--force' => true]);

            // Step 5: Create installation flag
            $this->createInstallationFlag();

            // Step 6: Clear caches
            Artisan::call('cache:clear');
            Artisan::call('config:clear');
            Artisan::call('route:clear');
            Artisan::call('view:clear');

            return response()->json([
                'success' => true,
                'message' => 'Installation completed successfully!',
                'redirect_url' => route('login'),
            ]);
        } catch (\Exception $e) {
            \Log::error('Installation failed', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
            ]);

            $errorMessage = 'Installation failed: ' . $e->getMessage();
            
            // Provide more user-friendly error messages
            if (strpos($e->getMessage(), 'SQLSTATE') !== false) {
                $errorMessage = 'Database error during installation. Please check your database configuration and try again.';
            } elseif (strpos($e->getMessage(), 'timeout') !== false) {
                $errorMessage = 'Installation timed out. Please try again or check your server configuration.';
            } elseif (strpos($e->getMessage(), 'memory') !== false) {
                $errorMessage = 'Insufficient memory during installation. Please increase PHP memory limit and try again.';
            }

            return response()->json([
                'success' => false,
                'message' => $errorMessage,
            ], 500);
        }
    }

    protected function isInstalled()
    {
        return File::exists(storage_path('app/installed'));
    }

    protected function checkRequirements()
    {
        $requirements = [
            'php_version' => [
                'name' => 'PHP Version',
                'required' => '8.1.0',
                'current' => PHP_VERSION,
                'status' => version_compare(PHP_VERSION, '8.1.0', '>='),
            ],
            'openssl' => [
                'name' => 'OpenSSL Extension',
                'required' => 'Enabled',
                'current' => extension_loaded('openssl') ? 'Enabled' : 'Disabled',
                'status' => extension_loaded('openssl'),
            ],
            'pdo' => [
                'name' => 'PDO Extension',
                'required' => 'Enabled',
                'current' => extension_loaded('pdo') ? 'Enabled' : 'Disabled',
                'status' => extension_loaded('pdo'),
            ],
            'mbstring' => [
                'name' => 'Mbstring Extension',
                'required' => 'Enabled',
                'current' => extension_loaded('mbstring') ? 'Enabled' : 'Disabled',
                'status' => extension_loaded('mbstring'),
            ],
            'tokenizer' => [
                'name' => 'Tokenizer Extension',
                'required' => 'Enabled',
                'current' => extension_loaded('tokenizer') ? 'Enabled' : 'Disabled',
                'status' => extension_loaded('tokenizer'),
            ],
            'xml' => [
                'name' => 'XML Extension',
                'required' => 'Enabled',
                'current' => extension_loaded('xml') ? 'Enabled' : 'Disabled',
                'status' => extension_loaded('xml'),
            ],
            'ctype' => [
                'name' => 'Ctype Extension',
                'required' => 'Enabled',
                'current' => extension_loaded('ctype') ? 'Enabled' : 'Disabled',
                'status' => extension_loaded('ctype'),
            ],
            'json' => [
                'name' => 'JSON Extension',
                'required' => 'Enabled',
                'current' => extension_loaded('json') ? 'Enabled' : 'Disabled',
                'status' => extension_loaded('json'),
            ],
            'bcmath' => [
                'name' => 'BCMath Extension',
                'required' => 'Enabled',
                'current' => extension_loaded('bcmath') ? 'Enabled' : 'Disabled',
                'status' => extension_loaded('bcmath'),
            ],
        ];

        return $requirements;
    }

    protected function checkPermissions()
    {
        $permissions = [
            'storage' => [
                'path' => storage_path(),
                'writable' => is_writable(storage_path()),
            ],
            'bootstrap_cache' => [
                'path' => base_path('bootstrap/cache'),
                'writable' => is_writable(base_path('bootstrap/cache')),
            ],
            'public' => [
                'path' => public_path(),
                'writable' => is_writable(public_path()),
            ],
        ];

        return $permissions;
    }

    protected function testDatabaseConnection($config)
    {
        try {
            // Handle empty password
            $password = $config['db_password'] ?? '';

            $connection = new \PDO(
                "mysql:host={$config['db_host']};port={$config['db_port']};dbname={$config['db_name']}",
                $config['db_username'],
                $password,
                [
                    \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
                    \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC,
                    \PDO::ATTR_TIMEOUT => 5, // 5 second timeout
                ]
            );

            // Test a simple query to ensure the connection is working
            $stmt = $connection->query("SELECT 1 as test");
            $result = $stmt->fetch();

            return [
                'success' => true,
                'message' => 'Database connection successful',
            ];
        } catch (\PDOException $e) {
            $errorMessage = $e->getMessage();

            // Provide more user-friendly error messages
            if (strpos($errorMessage, 'Access denied') !== false) {
                $errorMessage = 'Access denied. Please check your username and password.';
            } elseif (strpos($errorMessage, 'Unknown database') !== false) {
                $errorMessage = 'Database "' . $config['db_name'] . '" does not exist. Please create it first.';
            } elseif (strpos($errorMessage, 'Connection refused') !== false) {
                $errorMessage = 'Cannot connect to MySQL server. Please check if MySQL is running and the host/port are correct.';
            } elseif (strpos($errorMessage, 'timeout') !== false) {
                $errorMessage = 'Connection timeout. Please check your network connection and MySQL server status.';
            }

            return [
                'success' => false,
                'message' => $errorMessage,
            ];
        }
    }

    protected function updateEnvFile($data)
    {
        $envPath = base_path('.env');
        $envContent = File::get($envPath);

        // Database configuration
        $envContent = preg_replace('/DB_HOST=.*/', "DB_HOST={$data['db_host']}", $envContent);
        $envContent = preg_replace('/DB_PORT=.*/', "DB_PORT={$data['db_port']}", $envContent);
        $envContent = preg_replace('/DB_DATABASE=.*/', "DB_DATABASE={$data['db_name']}", $envContent);
        $envContent = preg_replace('/DB_USERNAME=.*/', "DB_USERNAME={$data['db_username']}", $envContent);
        $envContent = preg_replace('/DB_PASSWORD=.*/', "DB_PASSWORD={$data['db_password']}", $envContent);

        // Application configuration
        $envContent = preg_replace('/APP_NAME=.*/', "APP_NAME=\"{$data['app_name']}\"", $envContent);
        $envContent = preg_replace('/APP_URL=.*/', "APP_URL={$data['app_url']}", $envContent);

        File::put($envPath, $envContent);
    }

    protected function createAdminUser($data)
    {
        // Create admin user
        $user = User::create([
            'name' => $data['admin_name'],
            'email' => $data['admin_email'],
            'password' => Hash::make($data['admin_password']),
            'email_verified_at' => now(),
        ]);

        // Create roles and permissions
        $adminRole = Role::create(['name' => 'admin']);
        $managerRole = Role::create(['name' => 'manager']);
        $cashierRole = Role::create(['name' => 'cashier']);

        // Create permissions
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

        foreach ($permissions as $permission) {
            Permission::create(['name' => $permission]);
        }

        // Assign all permissions to admin role
        $adminRole->givePermissionTo(Permission::all());

        // Assign user to admin role
        $user->assignRole($adminRole);

        // Create company settings
        $settings = [
            'company_name' => $data['company_name'],
            'company_email' => $data['company_email'],
            'company_phone' => $data['company_phone'],
            'company_address' => $data['company_address'],
            'currency_symbol' => '$',
            'currency_position' => 'before',
            'tax_rate' => 10,
            'low_stock_threshold' => 10,
            'expiry_alert_days' => 30,
        ];

        File::put(storage_path('app/settings.json'), json_encode($settings, JSON_PRETTY_PRINT));
    }

    protected function createInstallationFlag()
    {
        File::put(storage_path('app/installed'), now()->toISOString());
    }
}
