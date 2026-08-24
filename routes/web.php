<?php

use App\Http\Controllers\AnalyticsController;
use App\Http\Controllers\AuditLogController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\LocaleController;
use App\Http\Controllers\RedirectController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\ShortLinkController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::get('/locale/{locale}', LocaleController::class)->name('locale');

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'create'])->name('login');
    Route::post('/login', [AuthController::class, 'store'])->name('login.store');
});

Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'destroy'])->name('logout');
    Route::get('/', fn () => redirect()->route('dashboard'));
    Route::get('/dashboard', DashboardController::class)->middleware('permission:dashboard.view')->name('dashboard');

    Route::get('/links', [ShortLinkController::class, 'index'])->middleware('permission:links.view')->name('links.index');
    Route::get('/links/create', [ShortLinkController::class, 'create'])->middleware('permission:links.create')->name('links.create');
    Route::post('/links', [ShortLinkController::class, 'store'])->middleware('permission:links.create')->name('links.store');
    Route::get('/links/{link}', [ShortLinkController::class, 'show'])->middleware('permission:links.view')->name('links.show');
    Route::get('/links/{link}/edit', [ShortLinkController::class, 'edit'])->middleware('permission:links.update')->name('links.edit');
    Route::put('/links/{link}', [ShortLinkController::class, 'update'])->middleware('permission:links.update')->name('links.update');
    Route::delete('/links/{link}', [ShortLinkController::class, 'destroy'])->middleware('permission:links.delete')->name('links.destroy');
    Route::patch('/links/{link}/toggle', [ShortLinkController::class, 'toggle'])->middleware('permission:links.update')->name('links.toggle');

    Route::get('/analytics', [AnalyticsController::class, 'index'])->middleware('permission:analytics.view')->name('analytics.index');
    Route::get('/analytics/links/{link}', [AnalyticsController::class, 'link'])->middleware('permission:analytics.view')->name('analytics.link');
    Route::get('/audit', [AuditLogController::class, 'index'])->middleware('permission:audit.view')->name('audit.index');

    Route::resource('users', UserController::class)->except('show')->middleware('permission:users.manage');
    Route::resource('roles', RoleController::class)->except('show')->middleware('role:Super Admin');
});

Route::get('/{code}', RedirectController::class)
    ->where('code', '[A-Za-z0-9][A-Za-z0-9_-]{1,49}')
    ->name('redirect');
