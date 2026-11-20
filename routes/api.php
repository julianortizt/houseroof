<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\ColorController;
use App\Http\Controllers\Api\ContentController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\QuoteController;
use App\Http\Controllers\Api\AiController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes (Mobile App)
|--------------------------------------------------------------------------
|
| Endpoints consumed by the House Roof mobile app (Android/iOS).
| All responses are locale-aware via the "api.locale" middleware
| (?lang=es|en|de|it|fr|pt|nl or X-Locale header).
|
*/

Route::post('ai/chat', [AiController::class, 'chat']);

Route::prefix('v1')->middleware('api.locale')->group(function () {

    // Public content.
    Route::get('pages/{slug}', [ContentController::class, 'page']);
    Route::get('blog', [ContentController::class, 'blogIndex']);
    Route::get('blog/{slug}', [ContentController::class, 'blogShow']);

    Route::middleware('module.enabled:products')->group(function () {
        Route::get('categories', [CategoryController::class, 'index']);
        Route::get('products', [ProductController::class, 'index']);
        Route::get('products/{product}', [ProductController::class, 'show'])->where('product', '[A-Za-z0-9\-_]+');
        Route::get('colors', [ColorController::class, 'index']);
    });

    // Authentication.
    Route::post('register', [AuthController::class, 'register']);
    Route::post('login', [AuthController::class, 'login']);

    // Authenticated routes.
    Route::middleware('api.auth')->group(function () {
        Route::post('logout', [AuthController::class, 'logout']);
        Route::get('me', [AuthController::class, 'me']);
        Route::middleware('module.enabled:quotes')->group(function () {
            Route::get('quotes', [QuoteController::class, 'index']);
            Route::post('quotes', [QuoteController::class, 'store']);
            Route::get('quotes/{quote}', [QuoteController::class, 'show']);
        });
    });
});
