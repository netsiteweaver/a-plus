<?php

use App\Http\Controllers\Api\CatalogHomeController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Auth\GoogleOAuthController;
use App\Http\Resources\UserResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:sanctum'])->get('/user', function (Request $request) {
    return new UserResource($request->user());
});

Route::prefix('catalog')->group(function () {
    Route::get('/home', CatalogHomeController::class);
    Route::get('/categories/{slug}', [CategoryController::class, 'show']);
    Route::get('/products/{slug}', [ProductController::class, 'show']);
});

require __DIR__.'/auth.php';

Route::prefix('auth/google')->group(function () {
    Route::get('/redirect', [GoogleOAuthController::class, 'redirect'])
        ->middleware('guest:sanctum')
        ->name('auth.google.redirect');

    Route::get('/callback', [GoogleOAuthController::class, 'callback'])
        ->name('auth.google.callback');
});
