<?php

use App\Http\Controllers\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

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
Route::post('/register', [UserController::class, 'register']);
Route::post('/login', [UserController::class, 'login']);

Route::middleware(['auth:sanctum'])->group(function () {
    Route::middleware('user')->group(function () {
        Route::patch('/user/update', [UserController::class, 'updateOwnUser']);
    });

    Route::middleware('admin')->group(function () {
        Route::get('/users', [UserController::class, 'index']);
        Route::post('/admin/create-user', [UserController::class, 'createUser']);
        Route::patch('/admin/update-user/{user}', [UserController::class, 'updateUser']); // Admin can update any user
        Route::delete('/admin/delete-user/{user}', [UserController::class, 'deleteUser']);

    });
    Route::get('/user', [UserController::class, 'user']); // Logged-in User (admin - Normal user)
    Route::post('/logout', [UserController::class, 'logout']);
});
