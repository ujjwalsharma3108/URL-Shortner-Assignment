<?php

use App\Http\Controllers\AcceptAdminInvitationController;
use App\Http\Controllers\AdminInvitationController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::view('/', 'welcome');

Route::middleware('guest:api')->group(function () {
    Route::view('/login', 'auth.login')->name('login');
    Route::view('/register', 'auth.register')->name('register');

    Route::post('/login', [AuthController::class, 'login'])->name('login.store');
    Route::post('/register', [AuthController::class, 'register'])->name('register.store');

    Route::get('/admin-invitations/accept/{token}', [AcceptAdminInvitationController::class, 'show'])
        ->where('token', '[A-Za-z0-9]{64}')
        ->name('admin-invitations.accept');
    Route::post('/admin-invitations/accept/{token}', [AcceptAdminInvitationController::class, 'store'])
        ->where('token', '[A-Za-z0-9]{64}')
        ->name('admin-invitations.complete');
});

Route::middleware('auth:api')->group(function () {
    Route::get('/dashboard', DashboardController::class)->name('dashboard');
    Route::get('/me', [AuthController::class, 'me'])->name('me');
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    Route::middleware('can:manage-admins')->group(function () {
        Route::post('/admin-invitations', [AdminInvitationController::class, 'store'])
            ->name('admin-invitations.store');
        Route::post('/admin-invitations/{invitation}/resend', [AdminInvitationController::class, 'resend'])
            ->name('admin-invitations.resend');
    });
});
