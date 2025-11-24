<?php

use App\Http\Controllers\AccountController;
use App\Http\Controllers\InstallController;
use App\Http\Controllers\BackupController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\DataExportController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\ManufacturerController;
use App\Http\Controllers\MedicineController;
use App\Http\Controllers\MenuController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\PurchaseController;
use App\Http\Controllers\SettingController;
use App\Http\Controllers\StockController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\UserController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return redirect()->route('dashboard');
});

// Test route for 404 page (remove in production)
Route::get('/test-404', function () {
    abort(404);
});

Route::get('/test-500', function () {
    abort(500);
});

Route::get('/dashboard', [DashboardController::class, 'index'])
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Medicine Routes
    Route::resource('medicines', MedicineController::class);
    Route::post('medicines/import', [MedicineController::class, 'import'])->name('medicines.import');
    Route::get('medicines/{medicine}/codes', [MedicineController::class, 'generateCodes'])->name('medicines.codes');
    Route::post('medicines/{medicine}/codes/save', [MedicineController::class, 'saveCodes'])->name('medicines.codes.save');

    // Customer Routes
    Route::resource('customers', CustomerController::class);

    // Invoice Routes
    Route::resource('invoices', InvoiceController::class);
    Route::get('pos', [InvoiceController::class, 'pos'])->name('pos');
    Route::get('invoices/{invoice}/print', [InvoiceController::class, 'print'])->name('invoices.print');
    Route::get('api/medicines/search', [InvoiceController::class, 'searchMedicines'])->name('api.medicines.search');
    Route::get('api/customers/search', [InvoiceController::class, 'searchCustomers'])->name('api.customers.search');
    Route::get('api/medicines/{medicine}/stocks', [InvoiceController::class, 'availableStocks'])->name('api.medicines.stocks');

    // Purchase Routes
    Route::resource('purchases', PurchaseController::class);
    Route::get('api/manufacturers/search', [ManufacturerController::class, 'search'])->name('api.manufacturers.search');

    // Category Routes
    Route::resource('categories', CategoryController::class);

    // Manufacturer Routes
    Route::resource('manufacturers', ManufacturerController::class);


    // Account Routes
    Route::resource('accounts', AccountController::class);

    // Settings Routes
    Route::get('settings', [SettingController::class, 'index'])->name('settings.index');
    Route::put('settings', [SettingController::class, 'update'])->name('settings.update');

    // User Management Routes
    Route::resource('users', UserController::class);

    // External medicine save API and MedEx proxies
    Route::post('api/medicines/store-external', [MedicineController::class, 'storeExternal'])->name('api.medicines.storeExternal');
    Route::get('api/medex/search', [MedicineController::class, 'medexSearch'])->name('api.medex.search');
    Route::get('api/medex/product', [MedicineController::class, 'medexProduct'])->name('api.medex.product');

    // Menu Management Routes
    Route::resource('menus', MenuController::class);
    Route::post('menus/{menu}/toggle-status', [MenuController::class, 'toggleStatus'])->name('menus.toggle-status');

    // Stock Management Routes
    Route::get('stocks/reports', [StockController::class, 'reports'])->name('stocks.reports');
    Route::get('stocks/alerts', [StockController::class, 'alerts'])->name('stocks.alerts');
    Route::resource('stocks', StockController::class);

    // Reports Routes
    Route::get('reports', [ReportController::class, 'index'])->name('reports.index');
    Route::get('reports/sales', [ReportController::class, 'sales'])->name('reports.sales');
    Route::get('reports/purchases', [ReportController::class, 'purchases'])->name('reports.purchases');
    Route::get('reports/profit-loss', [ReportController::class, 'profitLoss'])->name('reports.profit-loss');
    Route::get('reports/customer-dues', [ReportController::class, 'customerDues'])->name('reports.customer-dues');

    // Terminal Routes
    Route::get('/terminal', [App\Http\Controllers\TerminalController::class, 'index'])->name('terminal');
    Route::post('/terminal/execute', [App\Http\Controllers\TerminalController::class, 'execute'])->name('terminal.execute');
    Route::get('/terminal/commands', [App\Http\Controllers\TerminalController::class, 'getAvailableCommands'])->name('terminal.commands');
    Route::get('/terminal/system-info', [App\Http\Controllers\TerminalController::class, 'getSystemInfo'])->name('terminal.system-info');
    Route::post('/terminal/refresh-paths', [App\Http\Controllers\TerminalController::class, 'refreshCommandPaths'])->name('terminal.refresh-paths');

    // Backup routes
    Route::resource('backup', BackupController::class)->only(['index', 'create', 'destroy']);
    Route::post('/backup/restore', [BackupController::class, 'restore'])->name('backup.restore');
    Route::get('/backup/{backupName}/download', [BackupController::class, 'download'])->name('backup.download');

    // Data Export/Import routes
    Route::get('/data-export', [DataExportController::class, 'index'])->name('data-export.index');
    Route::post('/data-export/export-medicines', [DataExportController::class, 'exportMedicines'])->name('data-export.export-medicines');
    Route::post('/data-export/export-customers', [DataExportController::class, 'exportCustomers'])->name('data-export.export-customers');
    Route::post('/data-export/export-invoices', [DataExportController::class, 'exportInvoices'])->name('data-export.export-invoices');
    Route::post('/data-export/export-stocks', [DataExportController::class, 'exportStocks'])->name('data-export.export-stocks');
    Route::post('/data-export/import-medicines', [DataExportController::class, 'importMedicines'])->name('data-export.import-medicines');
    Route::post('/data-export/import-customers', [DataExportController::class, 'importCustomers'])->name('data-export.import-customers');
    Route::post('/data-export/import-stocks', [DataExportController::class, 'importStocks'])->name('data-export.import-stocks');
    Route::post('/data-export/sample', [DataExportController::class, 'downloadSample'])->name('data-export.sample');
    Route::post('/data-export/validation-rules', [DataExportController::class, 'getValidationRules'])->name('data-export.validation-rules');
});

// Installation routes (should be before auth middleware)
require __DIR__ . '/install.php';

require __DIR__ . '/auth.php';
