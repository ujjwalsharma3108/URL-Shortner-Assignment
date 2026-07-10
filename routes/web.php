<?php

use App\Http\Controllers\AcceptAdminInvitationController;
use App\Http\Controllers\AdminInvitationController;
use App\Http\Controllers\AnalyticsController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CompanyController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ShortUrlController;
use App\Http\Controllers\ShortUrlRedirectController;
use App\Http\Controllers\TeamController;
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
Route::get('/s/{code}', ShortUrlRedirectController::class)
    ->where('code', '[A-Za-z0-9]+')
    ->name('short-urls.redirect');

Route::middleware('guest:api')->group(function () {
    Route::view('/login', 'auth.login')->name('login');

    Route::post('/login', [AuthController::class, 'login'])->name('login.store');

    Route::get('/admin-invitations/accept/{token}', [AcceptAdminInvitationController::class, 'show'])
        ->where('token', '[A-Za-z0-9]{64}')
        ->name('admin-invitations.accept');
    Route::post('/admin-invitations/accept/{token}', [AcceptAdminInvitationController::class, 'store'])
        ->where('token', '[A-Za-z0-9]{64}')
        ->name('admin-invitations.complete');
});

Route::middleware('auth:api')->group(function () {
    Route::get('/dashboard', DashboardController::class)->name('dashboard');
    Route::get('/short-urls', [ShortUrlController::class, 'index'])->name('short-urls.index');
    Route::get('/super-admin/analytics', [AnalyticsController::class, 'superAdmin'])
        ->middleware('can:view-super-admin-analytics')
        ->name('super-admin.analytics');
    Route::get('/admin/analytics', [AnalyticsController::class, 'admin'])
        ->middleware('can:view-admin-analytics')
        ->name('admin.analytics');
    Route::get('/me', [AuthController::class, 'me'])->name('me');
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    Route::post('/short-urls', [ShortUrlController::class, 'store'])
        ->middleware('can:create-short-url')
        ->name('short-urls.store');

    Route::middleware('can:manage-users')->group(function () {
        Route::post('/admin-invitations', [AdminInvitationController::class, 'store'])
            ->name('admin-invitations.store');
        Route::post('/admin-invitations/{invitation}/resend', [AdminInvitationController::class, 'resend'])
            ->name('admin-invitations.resend');
    });

    Route::get('/team', TeamController::class)
        ->middleware('can:manage-team')
        ->name('team.index');

    Route::middleware('can:manage-admins')->group(function () {
        Route::get('/companies', [CompanyController::class, 'index'])
            ->name('companies.index');
        Route::post('/companies', [CompanyController::class, 'store'])
            ->name('companies.store');
    });
});
