<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\PropertyController;
use Illuminate\Support\Facades\Route;

Route::prefix('admin')->group(function () {
    Route::post('/login', [AuthController::class, 'login']);

    Route::middleware('auth:sanctum')->group(function () {
        Route::prefix('properties')->controller(PropertyController::class)->group(function () {
            Route::get('/', 'index');
            Route::post('/', 'store');
            Route::post('/update-multiple',  'updateMultiple');
        });
    });
});
