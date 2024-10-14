<?php

use App\Http\Controllers\ContactController;
use App\Http\Controllers\NotificationController;


use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\GoogleController;
use Laravel\Socialite\Facades\Socialite;
use Illuminate\Support\Facades\Mail;
use App\Http\Controllers\UserController;
use App\Http\Controllers\BookingController;
use App\Http\Controllers\MenuController;
use Swagger\Swagger;


//menu api
Route::apiResource('menu', MenuController::class);

// // Google Auth
Route::get('/auth/google/redirect', [GoogleController::class, 'redirectToGoogle']);
Route::get('/auth/google/callback', [GoogleController::class, 'handleGoogleCallback']);

Route::post('/contact', [ContactController::class, 'store']);


Route::middleware(['auth:sanctum'])->group(function () {

    // Route::post('/contact', [ContactController::class, 'store']);
     Route::post('/bookings', [BookingController::class, 'userBooking']);



    Route::middleware('user')->group(function () {
        Route::patch('/user/update', [UserController::class, 'updateOwnUser']);
        Route::patch('/bookings/{id}', [BookingController::class, 'updateUserBooking']);
        Route::get('/bookings/my', [BookingController::class, 'getUserBookings']);

    });

    // Routes specific to admin users
    Route::middleware('admin')->group(function () {
        Route::get('/users', [UserController::class, 'index']);
        Route::get('/admin/get-user/{user}', [UserController::class, 'getUser']);
        Route::post('/admin/create-user', [UserController::class, 'createUser']);
        Route::patch('/admin/update-user/{user}', [UserController::class, 'updateUser']); // Admin can update any user && can't change his role to user
        Route::delete('/admin/delete-user/{user}', [UserController::class, 'deleteUser']); // cant delete his account


        Route::get('/admin/contacts', [ContactController::class, 'index']);
        Route::delete('/admin/contacts/{id}', [ContactController::class, 'destroy']);


        Route::get('/bookings', [BookingController::class, 'getAllBookings']); // Get all bookings for admin
        Route::delete('/bookings/{id}', [BookingController::class, 'deleteBooking']);
        Route::patch('/bookings/{id}/status', [BookingController::class, 'updateStatus']);
    });
    Route::get('/user', [UserController::class, 'user']); // Logged-in User (admin - Normal user)

});



