<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\DashboardController;

use App\Http\Controllers\StudentsController;
use App\Http\Controllers\ServicesController;
use App\Http\Controllers\PaymentMethodsController;
use App\Http\Controllers\PaymentsController;
use App\Http\Controllers\PaymentRequestsController;

use App\Http\Controllers\UsersController;
use App\Http\Controllers\RolesController;
use App\Http\Controllers\ModulesController;
use App\Http\Controllers\PermissionsController;

use App\Http\Controllers\AuditLogsController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

Route::get('/', function () {
    return auth()->check() ? redirect('/dashboard') : redirect('/login');
});

// =====================
// AUTH (login por AJAX)
// =====================
Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'show'])->name('login');
    Route::post('/login', [LoginController::class, 'ajaxLogin'])->name('login.ajax');
});

// Logout (POST normal)
Route::post('/logout', [LoginController::class, 'logout'])
    ->middleware('auth')
    ->name('logout');


// =====================
// APP (AUTH + MÓDULO GLOBAL)
// =====================
Route::middleware(['auth', 'module.access.audit'])->group(function () {

    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::resource('students', StudentsController::class);
    Route::resource('services', ServicesController::class);
    Route::resource('payment-methods', PaymentMethodsController::class);

    Route::resource('payments', PaymentsController::class);
    Route::resource('payment-requests', PaymentRequestsController::class);

    Route::resource('users', UsersController::class);
    Route::resource('roles', RolesController::class);
    Route::resource('modules', ModulesController::class);
    Route::resource('permissions', PermissionsController::class);

    Route::get('audit-logs', [AuditLogsController::class, 'index'])->name('audit-logs.index');
});
