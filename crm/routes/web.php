<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\CarController;
use App\Http\Controllers\FinanceController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\WalletController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Authentication Routes
|--------------------------------------------------------------------------
*/
Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [LoginController::class, 'login']);
});

Route::post('/logout', [LoginController::class, 'logout'])
    ->middleware('auth')
    ->name('logout');

/*
|--------------------------------------------------------------------------
| Protected Routes (Require Authentication)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'security.headers'])->group(function () {
    
    // Dashboard (Car Index)
    Route::get('/', [CarController::class, 'index'])->name('dashboard');
    
    /*
    |--------------------------------------------------------------------------
    | Car Management Routes
    |--------------------------------------------------------------------------
    */
    Route::prefix('cars')->name('cars.')->group(function () {
        // Admin only routes
        Route::middleware('role:admin')->group(function () {
            Route::get('/create', [CarController::class, 'create'])->name('create');
            Route::post('/', [CarController::class, 'store'])->name('store');
            Route::delete('/{car}', [CarController::class, 'destroy'])->name('destroy');
            Route::post('/{car}/set-main-photo/{file}', [CarController::class, 'setMainPhoto'])->name('setMainPhoto');
        });
        
        // Admin and Dealer routes
        Route::middleware('role:admin,dealer')->group(function () {
            Route::get('/{car}/edit', [CarController::class, 'edit'])->name('edit');
            Route::put('/{car}', [CarController::class, 'update'])->name('update');
            Route::post('/{car}/files', [CarController::class, 'uploadFiles'])->name('uploadFiles');
            Route::delete('/{car}/files/{file}', [CarController::class, 'deleteFile'])->name('deleteFile');
        });
        
        // All authenticated users
        Route::get('/{car}', [CarController::class, 'show'])->name('show');
    });
    
    /*
    |--------------------------------------------------------------------------
    | Wallet Routes (Admin & Dealer)
    |--------------------------------------------------------------------------
    */
    Route::middleware('role:admin,dealer')->prefix('wallet')->name('wallet.')->group(function () {
        Route::get('/', [WalletController::class, 'index'])->name('index');
        Route::post('/transfer', [WalletController::class, 'transfer'])->name('transfer');
    });
    
    /*
    |--------------------------------------------------------------------------
    | Finance Routes (Admin & Dealer)
    |--------------------------------------------------------------------------
    */
    Route::middleware('role:admin,dealer')->prefix('finance')->name('finance.')->group(function () {
        Route::get('/', [FinanceController::class, 'index'])->name('index');
        Route::get('/transactions', [FinanceController::class, 'transactions'])->name('transactions');
        
        // Admin only transaction management
        Route::middleware('role:admin')->group(function () {
            Route::post('/transactions', [FinanceController::class, 'storeTransaction'])->name('transactions.store');
            Route::put('/transactions/{transaction}', [FinanceController::class, 'updateTransaction'])->name('transactions.update');
            Route::delete('/transactions/{transaction}', [FinanceController::class, 'destroyTransaction'])->name('transactions.destroy');
        });
    });
    
    /*
    |--------------------------------------------------------------------------
    | User Management Routes (Admin)
    |--------------------------------------------------------------------------
    */
    Route::middleware('role:admin')->prefix('users')->name('users.')->group(function () {
        Route::get('/', [UserController::class, 'index'])->name('index');
        Route::post('/', [UserController::class, 'store'])->name('store');
        Route::put('/{user}', [UserController::class, 'update'])->name('update');
        Route::delete('/{user}', [UserController::class, 'destroy'])->name('destroy');
        Route::post('/{user}/toggle-sms', [UserController::class, 'toggleSms'])->name('toggleSms');
        Route::post('/{user}/reset-password', [UserController::class, 'resetPassword'])->name('resetPassword');
    });
    
    /*
    |--------------------------------------------------------------------------
    | Profile Routes (All Users)
    |--------------------------------------------------------------------------
    */
    Route::get('/change-password', [UserController::class, 'showChangePasswordForm'])->name('password.change');
    Route::post('/change-password', [UserController::class, 'changePassword'])->name('password.update');
});

/*
|--------------------------------------------------------------------------
| Fallback Route
|--------------------------------------------------------------------------
*/
Route::fallback(function () {
    return redirect()->route('dashboard');
});
