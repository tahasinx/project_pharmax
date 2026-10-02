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
use App\Http\Controllers\PharmacyController;
use App\Http\Controllers\Platform\BillingController as PlatformBillingController;
use App\Http\Controllers\Platform\CompanyController as PlatformCompanyController;
use App\Http\Controllers\Platform\CompanyLoginController;
use App\Http\Controllers\Platform\DashboardController as PlatformDashboardController;
use App\Http\Controllers\Platform\OperationsController as PlatformOperationsController;
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
    if (config('database.tenant.enabled') && request()->attributes->get('tenant.mode') === 'central') {
        return redirect('/platform');
    }

    return redirect()->route('dashboard');
});

Route::get('/company-login/{token}', [CompanyLoginController::class, 'consume'])->name('platform.company-login');

Route::middleware(['auth', 'central.host', 'platform.admin'])->prefix('platform')->name('platform.')->group(function () {
    Route::get('/', [PlatformDashboardController::class, 'index'])->name('dashboard');
    Route::get('/billing', [PlatformDashboardController::class, 'billing'])->name('billing');
    Route::get('/settings', [PlatformOperationsController::class, 'settings'])->name('settings');
    Route::put('/settings', [PlatformOperationsController::class, 'updateSettings'])->name('settings.update');
    Route::post('/settings/email-test', [PlatformOperationsController::class, 'testEmail'])->name('settings.email-test');
    Route::get('/commands', [PlatformOperationsController::class, 'commands'])->name('commands');
    Route::post('/commands', [PlatformOperationsController::class, 'runCommand'])->name('commands.run');
    Route::get('/schema', [PlatformOperationsController::class, 'schema'])->name('schema');
    Route::post('/schema', [PlatformOperationsController::class, 'upgradeSchema'])->name('schema.upgrade');
    Route::get('/backups', [PlatformOperationsController::class, 'backups'])->name('backups');
    Route::post('/backups/{company}', [PlatformOperationsController::class, 'storeBackup'])->name('backups.store');
    Route::get('/backups/{company}/{filename}', [PlatformOperationsController::class, 'downloadBackup'])->name('backups.download')->where('filename', '[A-Za-z0-9_-]+\.sql\.gz');
    Route::delete('/backups/{company}/{filename}', [PlatformOperationsController::class, 'destroyBackup'])->name('backups.destroy')->where('filename', '[A-Za-z0-9_-]+\.sql\.gz');
    Route::get('/deploy', [PlatformOperationsController::class, 'deploy'])->name('deploy');
    Route::post('/deploy', [PlatformOperationsController::class, 'promote'])->name('deploy.promote');
    Route::get('/companies', [PlatformCompanyController::class, 'index'])->name('companies.index');
    Route::get('/companies/create', [PlatformCompanyController::class, 'create'])->name('companies.create');
    Route::post('/companies/validate-database', [PlatformCompanyController::class, 'validateDatabase'])->name('companies.validate-database');
    Route::post('/companies', [PlatformCompanyController::class, 'store'])->name('companies.store');
    Route::get('/companies/{company}/edit', [PlatformCompanyController::class, 'edit'])->name('companies.edit');
    Route::put('/companies/{company}', [PlatformCompanyController::class, 'update'])->name('companies.update');
    Route::delete('/companies/{company}', [PlatformCompanyController::class, 'destroy'])->name('companies.destroy');
    Route::get('/companies/{company}/provision', [PlatformCompanyController::class, 'provisionPage'])->name('companies.provision');
    Route::get('/companies/{company}/provision/log', [PlatformCompanyController::class, 'provisionLog'])->name('companies.provision.log');
    Route::post('/companies/{company}/provision/retry', [PlatformCompanyController::class, 'provisionRetry'])->name('companies.provision.retry');
    Route::get('/companies/{company}', [PlatformCompanyController::class, 'show'])->name('companies.show');
    Route::post('/companies/{company}/provision', [PlatformCompanyController::class, 'provision'])->name('companies.provision.start');
    Route::post('/companies/{company}/lock', [PlatformCompanyController::class, 'lock'])->name('companies.lock');
    Route::post('/companies/{company}/login-as', [CompanyLoginController::class, 'issue'])->name('companies.login-as');
    Route::get('/plans', [PlatformBillingController::class, 'plans'])->name('plans');
    Route::post('/plans', [PlatformBillingController::class, 'storePlan'])->name('plans.store');
    Route::get('/plans/{plan}/edit', [PlatformBillingController::class, 'editPlan'])->name('plans.edit');
    Route::put('/plans/{plan}', [PlatformBillingController::class, 'updatePlan'])->name('plans.update');
    Route::post('/plans/{plan}/archive', [PlatformBillingController::class, 'archivePlan'])->name('plans.archive');
    Route::get('/subscriptions', [PlatformBillingController::class, 'subscriptions'])->name('subscriptions');
    Route::post('/subscriptions', [PlatformBillingController::class, 'storeSubscription'])->name('subscriptions.store');
    Route::post('/subscriptions/{subscription}/expiry', [PlatformBillingController::class, 'updateExpiry'])->name('subscriptions.expiry');
    Route::post('/subscriptions/{subscription}/upgrade', [PlatformBillingController::class, 'upgradeSubscription'])->name('subscriptions.upgrade');
    Route::post('/subscriptions/{subscription}/invoice', [PlatformBillingController::class, 'invoiceSubscription'])->name('subscriptions.invoice');
    Route::get('/invoices', [PlatformBillingController::class, 'invoices'])->name('invoices');
    Route::post('/invoices', [PlatformBillingController::class, 'storeInvoice'])->name('invoices.store');
    Route::post('/invoices/{invoice}/toggle', [PlatformBillingController::class, 'markInvoice'])->name('invoices.toggle');
    Route::delete('/invoices/{invoice}', [PlatformBillingController::class, 'destroyInvoice'])->name('invoices.destroy');
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
Route::post('/dashboard/dead-stock-days', function (\Illuminate\Http\Request $request) {
    $data = $request->validate(['dead_stock_days' => 'required|integer|min:1|max:3650']);
    $setting = \App\Models\Setting::query()->first();
    if ($setting) {
        $setting->update(['dead_stock_days' => $data['dead_stock_days']]);
    }

    return back();
})->middleware(['auth', 'verified'])->name('dashboard.dead-stock-days');

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
    Route::post('pos/hold', [InvoiceController::class, 'hold'])->name('pos.hold');
    Route::post('pos/held/{heldBill}/resume', [InvoiceController::class, 'resumeHeld'])->name('pos.resume');
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
    Route::get('api/medex/brands', [MedicineController::class, 'medexBrands'])->name('api.medex.brands');
    Route::post('api/medex/brands/import', [MedicineController::class, 'importMedexBrands'])->name('api.medex.brands.import');
    Route::get('api/medex/companies', [MedicineController::class, 'medexCompanies'])->name('api.medex.companies');
    Route::post('api/medex/companies/import', [MedicineController::class, 'importMedexCompanies'])->name('api.medex.companies.import');

    // Menu Management Routes
    Route::resource('menus', MenuController::class);
    Route::post('menus/{menu}/toggle-status', [MenuController::class, 'toggleStatus'])->name('menus.toggle-status');

    // Stock Management Routes
    Route::get('stocks/reports', [StockController::class, 'reports'])->name('stocks.reports');
    Route::get('stocks/alerts', [StockController::class, 'alerts'])->name('stocks.alerts');
    Route::get('stocks/expiry', [PharmacyController::class, 'expiry'])->name('stocks.expiry');
    Route::post('stocks/adjust', [PharmacyController::class, 'adjust'])->name('stocks.adjust');
    Route::resource('stocks', StockController::class);

    Route::get('branches', [PharmacyController::class, 'branches'])->name('branches.index');
    Route::post('branches', [PharmacyController::class, 'storeBranch'])->name('branches.store');
    Route::post('branch/switch', [PharmacyController::class, 'switchBranch'])->name('branch.switch');

    Route::get('generics', [PharmacyController::class, 'generics'])->name('generics.index');
    Route::post('generics', [PharmacyController::class, 'storeGeneric'])->name('generics.store');
    Route::put('generics/{generic}', [PharmacyController::class, 'updateGeneric'])->name('generics.update');
    Route::delete('generics/{generic}', [PharmacyController::class, 'destroyGeneric'])->name('generics.destroy');
    Route::get('medicine-types', [PharmacyController::class, 'medicineTypes'])->name('medicine-types.index');
    Route::post('medicine-types', [PharmacyController::class, 'storeMedicineType'])->name('medicine-types.store');
    Route::put('medicine-types/{medicineType}', [PharmacyController::class, 'updateMedicineType'])->name('medicine-types.update');
    Route::delete('medicine-types/{medicineType}', [PharmacyController::class, 'destroyMedicineType'])->name('medicine-types.destroy');
    Route::get('units', [PharmacyController::class, 'units'])->name('units.index');
    Route::post('units', [PharmacyController::class, 'storeUnit'])->name('units.store');
    Route::put('units/{unit}', [PharmacyController::class, 'updateUnit'])->name('units.update');
    Route::delete('units/{unit}', [PharmacyController::class, 'destroyUnit'])->name('units.destroy');
    Route::get('brands', [PharmacyController::class, 'brands'])->name('brands.index');
    Route::post('brands', [PharmacyController::class, 'storeBrand'])->name('brands.store');
    Route::put('brands/{brand}', [PharmacyController::class, 'updateBrand'])->name('brands.update');
    Route::delete('brands/{brand}', [PharmacyController::class, 'destroyBrand'])->name('brands.destroy');

    Route::get('suppliers', [PharmacyController::class, 'suppliers'])->name('suppliers.index');
    Route::post('suppliers', [PharmacyController::class, 'storeSupplier'])->name('suppliers.store');

    Route::get('purchase-orders', [PharmacyController::class, 'purchaseOrders'])->name('purchase-orders.index');
    Route::post('purchase-orders', [PharmacyController::class, 'storePurchaseOrder'])->name('purchase-orders.store');
    Route::post('goods-receipts', [PharmacyController::class, 'storeGoodsReceipt'])->name('goods-receipts.store');
    Route::post('purchase-returns', [PharmacyController::class, 'storePurchaseReturn'])->name('purchase-returns.store');

    Route::post('stocks/{stock}/lock', [PharmacyController::class, 'lockBatch'])->name('stocks.lock');
    Route::post('stocks/{stock}/recall', [PharmacyController::class, 'recallBatch'])->name('stocks.recall');

    Route::get('stock-transfers', [PharmacyController::class, 'transfers'])->name('stock-transfers.index');
    Route::post('stock-transfers', [PharmacyController::class, 'storeTransfer'])->name('stock-transfers.store');
    Route::post('stock-transfers/{stockTransfer}/dispatch', [PharmacyController::class, 'dispatchTransfer'])->name('stock-transfers.dispatch');
    Route::post('stock-transfers/{stockTransfer}/receive', [PharmacyController::class, 'receiveTransfer'])->name('stock-transfers.receive');
    Route::post('purchase-invoices', [PharmacyController::class, 'storePurchaseInvoice'])->name('purchase-invoices.store');
    Route::post('finance/customer-receipt', [PharmacyController::class, 'customerReceipt'])->name('finance.customer-receipt');
    Route::post('finance/supplier-payment', [PharmacyController::class, 'supplierPayment'])->name('finance.supplier-payment');
    Route::get('clinical-rules', [PharmacyController::class, 'clinicalRules'])->name('clinical-rules.index');
    Route::post('clinical-rules', [PharmacyController::class, 'storeClinicalRule'])->name('clinical-rules.store');
    Route::post('prescriptions/{prescription}/status', [PharmacyController::class, 'setPrescriptionStatus'])->name('prescriptions.status');

    Route::get('sales-returns/create', [PharmacyController::class, 'salesReturnForm'])->name('sales-returns.create');
    Route::post('sales-returns', [PharmacyController::class, 'storeSalesReturn'])->name('sales-returns.store');

    Route::get('prescriptions', [PharmacyController::class, 'prescriptions'])->name('prescriptions.index');
    Route::post('prescriptions', [PharmacyController::class, 'storePrescription'])->name('prescriptions.store');
    Route::get('prescriptions/{prescription}', [PharmacyController::class, 'showPrescription'])->name('prescriptions.show');
    Route::post('prescriptions/{prescription}/dispense', [PharmacyController::class, 'dispense'])->name('prescriptions.dispense');
    Route::get('controlled-register', [PharmacyController::class, 'controlledRegister'])->name('controlled.index');

    Route::get('finance', [PharmacyController::class, 'finance'])->name('finance.index');
    Route::get('audit', [PharmacyController::class, 'audit'])->name('audit.index');

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
