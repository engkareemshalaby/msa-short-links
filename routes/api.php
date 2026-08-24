<?php

use App\Http\Controllers\Api\ShortLinkApiController;
use Illuminate\Support\Facades\Route;

Route::middleware('api.key')->group(function () {
    Route::get('/links', [ShortLinkApiController::class, 'index']);
    Route::post('/links', [ShortLinkApiController::class, 'store']);
    Route::get('/links/{link}', [ShortLinkApiController::class, 'show']);
});
