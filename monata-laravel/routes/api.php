<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\PropertyController;
use App\Http\Controllers\Api\RoomTypeController;
use App\Http\Controllers\Api\RoomController;
use Illuminate\Support\Facades\Route;

Route::prefix('admin')->group(function () {
    Route::post('/login', [AuthController::class, 'login']);

    Route::middleware('auth:sanctum')->group(function () {
        Route::apiResource('properties', PropertyController::class);
        Route::apiResource('room-types', RoomTypeController::class);
        
        Route::apiResource('rooms', RoomController::class);
        Route::post('/rooms/{room}/restore', [RoomController::class, 'restore']);
        Route::post('/rooms/{room}', [RoomController::class, 'update']);
    });
});
