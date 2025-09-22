<?php

use App\Http\Controllers\AccountController;
use App\Http\Controllers\BankController;
use App\Http\Controllers\CategoryController;
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
    Route::get('medicines/{medicine}/barcode', [MedicineController::class, 'generateBarcode'])->name('medicines.barcode');
    Route::get('medicines/{medicine}/qrcode', [MedicineController::class, 'generateQrCode'])->name('medicines.qrcode');

    // Customer Routes
    Route::resource('customers', CustomerController::class);

    // Invoice Routes
    Route::resource('invoices', InvoiceController::class);
    Route::get('pos', [InvoiceController::class, 'pos'])->name('pos');
    Route::get('invoices/{invoice}/print', [InvoiceController::class, 'print'])->name('invoices.print');
    Route::get('api/medicines/search', [InvoiceController::class, 'searchMedicines'])->name('api.medicines.search');
    Route::get('api/customers/search', [InvoiceController::class, 'searchCustomers'])->name('api.customers.search');

    // Purchase Routes
    Route::resource('purchases', PurchaseController::class);

    // Category Routes
    Route::resource('categories', CategoryController::class);

    // Manufacturer Routes
    Route::resource('manufacturers', ManufacturerController::class);

    // Bank Routes
    Route::resource('banks', BankController::class);

    // Account Routes
    Route::resource('accounts', AccountController::class);

    // Settings Routes
    Route::get('settings', [SettingController::class, 'index'])->name('settings.index');
    Route::put('settings', [SettingController::class, 'update'])->name('settings.update');

    // User Management Routes
    Route::resource('users', UserController::class);

    // Menu Management Routes
    Route::resource('menus', MenuController::class);
    Route::post('menus/{menu}/toggle-status', [MenuController::class, 'toggleStatus'])->name('menus.toggle-status');

    // Stock Management Routes
    Route::get('stocks/reports', [StockController::class, 'reports'])->name('stocks.reports');
    Route::get('stocks/alerts', [StockController::class, 'alerts'])->name('stocks.alerts');
    Route::resource('stocks', StockController::class);
});

require __DIR__ . '/auth.php';
