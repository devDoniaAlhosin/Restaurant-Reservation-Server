<?php

use App\Http\Controllers\ContactController;
use App\Http\Controllers\UserController;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\GoogleController;
use Laravel\Socialite\Facades\Socialite;
use Illuminate\Support\Facades\Mail;


/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/




//Route::post('/register', [UserController::class, 'register']);
//Route::post('/login', [UserController::class, 'login']);


// // Google Auth
Route::get('/auth/google/redirect', [GoogleController::class, 'redirectToGoogle']);
Route::get('/auth/google/callback', [GoogleController::class, 'handleGoogleCallback']);


Route::middleware(['auth:sanctum'])->group(function () {
    Route::post('/contact', [ContactController::class, 'store']);
    Route::middleware('user')->group(function () {
        Route::patch('/user/update', [UserController::class, 'updateOwnUser']);
    });

    Route::middleware('admin')->group(function () {
        Route::get('/users', [UserController::class, 'index']);
        Route::get('/admin/get-user/{user}', [UserController::class, 'getUser']);
        Route::post('/admin/create-user', [UserController::class, 'createUser']);
        Route::patch('/admin/update-user/{user}', [UserController::class, 'updateUser']); // Admin can update any user && can't change his role to user
        Route::delete('/admin/delete-user/{user}', [UserController::class, 'deleteUser']); // cant delete his account


        Route::get('/admin/contacts', [ContactController::class, 'index'])->middleware('auth:admin');
        Route::delete('/admin/contacts/{id}', [ContactController::class, 'destroy']);
    });
    Route::get('/user', [UserController::class, 'user']); // Logged-in User (admin - Normal user)
    // Route::post('/logout', [UserController::class, 'logout']);
});



