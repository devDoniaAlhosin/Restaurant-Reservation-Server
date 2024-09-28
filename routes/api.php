<?php

use App\Http\Controllers\NotificationController;
use App\Http\Controllers\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\BookingController;


// Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
//     return $request->user();
// });


Route::post('/bookings', [BookingController::class, 'userBooking']);
Route::patch('/bookings/{id}/status', [BookingController::class, 'updateStatus']); // For updating booking status
// Route::get('/user/notifications', [NotificationController::class, 'getUserNotifications']);
// Route::post('/send-notification/{id}', [BookingController::class, 'sendNotification']);
