<?php

use App\Http\Controllers\InstallationController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Installation Routes
|--------------------------------------------------------------------------
|
| Routes for the installation wizard. These routes should be accessible
| before the application is fully installed.
|
*/

Route::prefix('install')->name('install.')->group(function () {
    Route::get('/', [InstallationController::class, 'index'])->name('index');
    Route::get('/database', [InstallationController::class, 'database'])->name('database');
    Route::post('/database', [InstallationController::class, 'testDatabase'])->name('test.database');
    Route::get('/app', [InstallationController::class, 'app'])->name('app');
    Route::post('/app', [InstallationController::class, 'saveApp'])->name('save.app');
    Route::get('/admin', [InstallationController::class, 'admin'])->name('admin');
    Route::post('/admin', [InstallationController::class, 'saveAdmin'])->name('save.admin');
    Route::get('/install', [InstallationController::class, 'install'])->name('install');
    Route::post('/execute', [InstallationController::class, 'execute'])->name('execute');
    Route::get('/complete', [InstallationController::class, 'complete'])->name('complete');
});
