<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\AdminBookingController;
use App\Http\Controllers\Api\PropertyController;
use App\Http\Controllers\Api\RoomTypeController;
use App\Http\Controllers\Api\UserController;
use Illuminate\Support\Facades\Route;

Route::prefix('admin')->group(function () {
    Route::post('/login', [AuthController::class, 'login']);

    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/logout', [AuthController::class, 'logout']);


        Route::apiResource('properties', PropertyController::class);
        Route::apiResource('room-types', RoomTypeController::class);
        Route::apiResource('bookings', AdminBookingController::class);
    });
});

Route::prefix('/')->group(function () {
    Route::post('login', [UserController::class, 'login']);
    Route::post('logout', [UserController::class, 'logout'])->middleware('auth:sanctum');
});
