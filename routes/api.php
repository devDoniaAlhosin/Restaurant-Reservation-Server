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


Route::middleware(['auth:sanctum', 'admin'])->group(function () {

});
Route::middleware(['auth:sanctum', 'user'])->group(function () {
    Route::get('/user', [UserController::class, 'user']); // Loggedin User
//    Route::get('/user/profile', [UserController::class, 'profile']);
});


Route::middleware('auth:sanctum')->group(function () {
//    Route::get('/user', [UserController::class, 'user']);
    Route::post('/logout', [UserController::class, 'logout']);
});
