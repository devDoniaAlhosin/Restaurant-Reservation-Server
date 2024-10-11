<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\MenuController;
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




