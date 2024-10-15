<?php

use App\Http\Controllers\PaymentController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\NotificationController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\GoogleController;
use Laravel\Socialite\Facades\Socialite;
use App\Http\Controllers\BookingController;


Route::post('/register', [UserController::class, 'register']);
Route::post('/login', [UserController::class, 'login']);


// // Google Auth
// Route::get('/auth/google/redirect', [GoogleController::class, 'redirectToGoogle']);

// Route::get('/auth/callback/google', [GoogleController::class, 'handleGoogleCallback']);
// // Route::get('/auth/google/callback', function (Request $request)  {
// //     dd(Socialite::driver('google')->stateless()->user());

// // });




Route::middleware(['auth:sanctum'])->group(function () {
    // Routes accessible to both users and admins
    Route::post('/bookings', [BookingController::class, 'userBooking']);
    Route::get('/user', [UserController::class, 'user']);
    Route::post('/logout', [UserController::class, 'logout']);
    // Routes specific to normal users
    Route::middleware('user')->group(function () {
        Route::patch('/user/update', [UserController::class, 'updateOwnUser']);
        Route::patch('/bookings/{id}', [BookingController::class, 'updateUserBooking']);
        Route::get('/bookings/my', [BookingController::class, 'getUserBookings']);

    });

    // Routes specific to admin users
    Route::middleware('admin')->group(function () {
        Route::get('/users', [UserController::class, 'index']); // Get all users
        Route::get('/admin/get-user/{user}', [UserController::class, 'getUser']); // Get a specific user by ID
        Route::post('/admin/create-user', [UserController::class, 'createUser']); // Create a new user
        Route::patch('/admin/update-user/{user}', [UserController::class, 'updateUser']); // Admin can update any user but can't change own role
        Route::delete('/admin/delete-user/{user}', [UserController::class, 'deleteUser']); // Admin cannot delete own account
        Route::get('/bookings', [BookingController::class, 'getAllBookings']); // Get all bookings for admin
        Route::delete('/bookings/{id}', [BookingController::class, 'deleteBooking']);
        Route::patch('/bookings/{id}/status', [BookingController::class, 'updateStatus']);
    });
});
