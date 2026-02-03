<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\MeController;

Route::prefix('auth')->group(function () {
    Route::post('login', [AuthController::class, 'login']);
    Route::post('sign-up', [AuthController::class, 'signUp']);
});


Route::middleware('auth:sanctum')->group(function () {
    Route::prefix('auth')->group(function () {
        Route::post('logout', [AuthController::class, 'logout']);
    });
    Route::prefix('me')->group(function () {
        Route::get('/', [MeController::class, 'getProfile']);
        Route::prefix('user/profile')->group(function () {
            Route::post('/', [MeController::class, 'storeUserProfile']);
            Route::patch('/', [MeController::class, 'updateUserProfile']);
        });
        Route::prefix('company/profile')->group(function () {
            Route::post('/', [MeController::class, 'storeCompanyProfile']);
            Route::patch('/', [MeController::class, 'updateCompanyProfile']);
        });
    });
});

Route::middleware(['auth:sanctum', 'is_admin'])->group(function () {

});

Route::middleware(['auth:sanctum', 'is_company'])->group(function () {
    
});