<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\JobController;
use App\Http\Controllers\AdminController;

Route::prefix('auth')->group(function () {
    Route::post('login', [AuthController::class, 'login']);
    Route::post('sign-up', [AuthController::class, 'signUp']);
});
Route::prefix('jobs')->group(function () {
    Route::get('/', [JobController::class, 'index']);
    Route::post('/', [JobController::class, 'store']);
});


Route::middleware('auth:sanctum')->group(function () {
    Route::prefix('auth')->group(function () {
        Route::post('logout', [AuthController::class, 'logout']);
    });
    Route::prefix('admin')->group(function () {
        Route::prefix('jobs')->group(function () {
            Route::get('/', [AdminController::class, 'getPendingJobs']);
            Route::patch('/{id}/status', [AdminController::class, 'updateJobStatus']);
            Route::post('/{id}/metadata', [AdminController::class, 'updateJobMetadata']);
        });
        Route::get('/tags', [AdminController::class, 'getTags']);
    });
}); 