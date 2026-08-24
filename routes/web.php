<?php

use App\Http\Controllers\AnalyticsController;
use App\Http\Controllers\AuditLogController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ApiKeyController;
use App\Http\Controllers\BulkLinkController;
use App\Http\Controllers\CampaignController;
use App\Http\Controllers\ExportController;
use App\Http\Controllers\LocaleController;
use App\Http\Controllers\RedirectController;
use App\Http\Controllers\PixelController;
use App\Http\Controllers\QrCodeController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\ShortLinkController;
use App\Http\Controllers\SmartTargetController;
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
    Route::get('/links/bulk', [BulkLinkController::class, 'create'])->middleware('permission:links.create')->name('links.bulk.create');
    Route::post('/links/bulk', [BulkLinkController::class, 'store'])->middleware('permission:links.create')->name('links.bulk.store');
    Route::get('/links/create', [ShortLinkController::class, 'create'])->middleware('permission:links.create')->name('links.create');
    Route::post('/links', [ShortLinkController::class, 'store'])->middleware('permission:links.create')->name('links.store');
    Route::get('/links/{link}', [ShortLinkController::class, 'show'])->middleware('permission:links.view')->name('links.show');
    Route::get('/links/{link}/qr/{format?}', [QrCodeController::class, 'show'])->where('format', 'png|svg')->middleware('permission:links.view')->name('links.qr');
    Route::get('/links/{link}/smart-targets', [SmartTargetController::class, 'index'])->middleware('permission:links.update')->name('links.smart-targets.index');
    Route::post('/links/{link}/smart-targets', [SmartTargetController::class, 'store'])->middleware('permission:links.update')->name('links.smart-targets.store');
    Route::delete('/links/{link}/smart-targets/{target}', [SmartTargetController::class, 'destroy'])->middleware('permission:links.update')->name('links.smart-targets.destroy');
    Route::get('/links/{link}/edit', [ShortLinkController::class, 'edit'])->middleware('permission:links.update')->name('links.edit');
    Route::put('/links/{link}', [ShortLinkController::class, 'update'])->middleware('permission:links.update')->name('links.update');
    Route::delete('/links/{link}', [ShortLinkController::class, 'destroy'])->middleware('permission:links.delete')->name('links.destroy');
    Route::patch('/links/{link}/toggle', [ShortLinkController::class, 'toggle'])->middleware('permission:links.update')->name('links.toggle');

    Route::get('/analytics', [AnalyticsController::class, 'index'])->middleware('permission:analytics.view')->name('analytics.index');
    Route::get('/analytics/links/{link}', [AnalyticsController::class, 'link'])->middleware('permission:analytics.view')->name('analytics.link');
    Route::get('/audit', [AuditLogController::class, 'index'])->middleware('permission:audit.view')->name('audit.index');
    Route::get('/exports/links.csv', [ExportController::class, 'links'])->middleware('permission:analytics.view')->name('exports.links');
    Route::get('/exports/visits.csv', [ExportController::class, 'visits'])->middleware('permission:analytics.view')->name('exports.visits');
    Route::get('/campaigns', [CampaignController::class, 'index'])->middleware('permission:links.create')->name('campaigns.index');
    Route::post('/campaigns', [CampaignController::class, 'store'])->middleware('permission:links.create')->name('campaigns.store');
    Route::post('/tags', [CampaignController::class, 'tag'])->middleware('permission:links.create')->name('tags.store');
    Route::get('/api-keys', [ApiKeyController::class, 'index'])->middleware('role:Super Admin')->name('api-keys.index');
    Route::post('/api-keys', [ApiKeyController::class, 'store'])->middleware('role:Super Admin')->name('api-keys.store');
    Route::delete('/api-keys/{apiKey}', [ApiKeyController::class, 'destroy'])->middleware('role:Super Admin')->name('api-keys.destroy');
    Route::get('/pixels', [PixelController::class, 'index'])->middleware('role:Super Admin')->name('pixels.index');
    Route::post('/pixels', [PixelController::class, 'store'])->middleware('role:Super Admin')->name('pixels.store');
    Route::delete('/pixels/{pixel}', [PixelController::class, 'destroy'])->middleware('role:Super Admin')->name('pixels.destroy');

    Route::resource('users', UserController::class)->except('show')->middleware('permission:users.manage');
    Route::resource('roles', RoleController::class)->except('show')->middleware('role:Super Admin');
});

Route::post('/{code}/unlock', [RedirectController::class, 'unlock'])->middleware('throttle:10,1')->name('redirect.unlock');

Route::get('/{code}', RedirectController::class)
    ->where('code', '[A-Za-z0-9][A-Za-z0-9_-]{1,49}')
    ->name('redirect');
