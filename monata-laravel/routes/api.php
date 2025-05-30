<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\AdminBookingController;
use App\Http\Controllers\Api\InvoiceDetailController;
use App\Http\Controllers\Api\PropertyController;
use App\Http\Controllers\Api\RoomTypeController;
use App\Http\Controllers\Api\ContactController;
use App\Http\Controllers\Api\RoomController;
use App\Http\Controllers\Api\AuthUserController;
use App\Http\Controllers\Api\ServiceController;
use Illuminate\Support\Facades\Route;

Route::prefix('admin')->group(function () {
    Route::post('/login', [AuthController::class, 'login']);

    // Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);

    Route::apiResource('properties', PropertyController::class);
    Route::apiResource('room-types', RoomTypeController::class);

    Route::apiResource('bookings', AdminBookingController::class);
    Route::post('/bookings/check-room-availability', [AdminBookingController::class, 'checkRoomAvailability']);
    Route::post('/bookings/{booking}/confirm', [AdminBookingController::class, 'confirm']);
    Route::post('/bookings/{booking}/check-in', [AdminBookingController::class, 'checkInGuest']);

    Route::get('bookings/{booking}/invoice-details', [InvoiceDetailController::class, 'index']);
    Route::post('bookings/{booking}/invoice-details', [InvoiceDetailController::class, 'editSave']);
    Route::delete('bookings/{booking}/invoice-details/{id}', [InvoiceDetailController::class, 'destroy']);

    Route::apiResource('rooms', RoomController::class)->except(['update']);
    Route::post('/rooms/{room}/restore', [RoomController::class, 'restore']);
    Route::post('/rooms/{room}', [RoomController::class, 'update']);

    Route::apiResource('services', ServiceController::class);
    Route::post('services/{id}/restore', [ServiceController::class, 'restore']);

    Route::apiResource('contacts', ContactController::class);
    Route::post('contacts/send-contact', [ContactController::class, 'sendContact']);
    Route::post('contacts/{id}/send-mail', [ContactController::class, 'sendMail']);
    // 
});

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthUserController::class, 'logout']);
    Route::get('/profile', [AuthUserController::class, 'getProfile']);
    Route::put('/profile', [AuthUserController::class, 'updateProfile']);
    Route::post('/change-password', [AuthUserController::class, 'changePassword']);
});
Route::post('/login', [AuthUserController::class, 'login']);
Route::post('/register', [AuthUserController::class, 'register']);
Route::post('contacts/send-contact', [ContactController::class, 'sendContact']);
