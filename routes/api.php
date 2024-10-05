<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\Api\NewsController;
use App\Http\Controllers\Api\UserPreferenceController;

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

Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout']);
Route::post('/reset-password', [AuthController::class, 'reset_password']);
Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::post('/register', [AuthController::class, 'register']);
// Version 1
Route::prefix('v1')->group(function () {
    Route::prefix('news')->group(function () {
        Route::get('/list', [NewsController::class, 'list']);
        Route::get('/search', [NewsController::class, 'search']);
    });

    Route::prefix('user-preference')->group(function () {
        Route::get('/list', [UserPreferenceController::class, 'list']);
        Route::post('/store', [UserPreferenceController::class, 'store']);
        Route::put('/update', [UserPreferenceController::class, 'update']);
        Route::delete('/delete', [UserPreferenceController::class, 'delete']);
    });
});
