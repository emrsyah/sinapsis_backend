<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\GoogleController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\NoteController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::get('v1/auth/login', [GoogleController::class, 'redirectToGoogle'])->name('google.login');

Route::middleware('auth:sanctum')->group(function () {
    Route::post('v1/auth/logout', [GoogleController::class, 'logout']);
    Route::patch('v1/auth/me', [UserController::class, 'update']);
    Route::get('v1/auth/me', [UserController::class, 'profile']);
});