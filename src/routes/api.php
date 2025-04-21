<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\CollectionController;

Route::get('/test', function () {
    return response()->json(['message' => 'API is working']);
});

Route::prefix('auth')->group(function () {
    Route::post('register', [AuthController::class, 'register']);
    Route::post('login', [AuthController::class, 'login']);

    Route::middleware('jwt.auth')->group(function () {
        Route::post('logout', [AuthController::class, 'logout']);
        Route::get('me', [AuthController::class, 'me']);
    });

    Route::post('refresh', [AuthController::class, 'refresh']);
});

Route::group([
    'prefix' => 'user',
    'middleware' => 'jwt.auth'
], function () {
    Route::put('profile', [UserController::class, 'updateProfile']);
});

// Collection routes
Route::group([
    'prefix' => 'collections',
    // 'middleware' => 'jwt.auth'
], function () {
    Route::get('/', [CollectionController::class, 'index']);
    Route::post('/', [CollectionController::class, 'store']);
    Route::get('/{id}', [CollectionController::class, 'show']);
    Route::put('/{id}', [CollectionController::class, 'update']);
    Route::delete('/{id}', [CollectionController::class, 'destroy']);
});
