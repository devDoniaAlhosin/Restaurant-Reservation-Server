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
use App\Http\Controllers\PaymobController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\PasswordResetLinkController;
use App\Http\Controllers\Auth\EmailVerificationNotificationController;
use App\Http\Controllers\Auth\NewPasswordController;
use App\Http\Controllers\Auth\VerifyEmailController;


// Auth
Route::post('/login', [AuthenticatedSessionController::class, 'store'])
->name('login');

Route::post('/register', [RegisteredUserController::class, 'store'])
    ->name('register');

Route::post('/forgot-password', [PasswordResetLinkController::class, 'store'])
    ->name('password.email');

Route::post('/reset-password', [NewPasswordController::class, 'store'])
    ->name('password.store');

Route::get('/verify-email/{id}/{hash}', VerifyEmailController::class)
     ->middleware(['auth:sanctum', 'signed', 'throttle:6,1'])
     ->name('verification.verify');

Route::post('/email/verification-notification', [EmailVerificationNotificationController::class, 'store'])
     ->middleware(['auth:sanctum', 'throttle:6,1'])
     ->name('verification.send');
















//menu api
Route::prefix('menu')->group(function () {
    Route::get('', [MenuController::class, 'index']);
    Route::get('/{menu}', [MenuController::class, 'show']);

    Route::middleware(['auth:sanctum', 'admin'])->group(function () {
        Route::post('', [MenuController::class, 'store']);
        Route::patch('/{menu}', [MenuController::class, 'update']);
        Route::delete('/{menu}', [MenuController::class, 'destroy']);
    });
});




// // Google Auth
Route::get('/auth/google/redirect', [GoogleController::class, 'redirectToGoogle']);
Route::get('/auth/google/callback', [GoogleController::class, 'handleGoogleCallback']);

Route::post('/contact', [ContactController::class, 'store']);


Route::middleware(['auth:sanctum'])->group(function () {
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



