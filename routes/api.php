<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\GoogleController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\NoteController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::middleware('auth:sanctum')->group(function () {
    Route::post('v1/auth/logout', [GoogleController::class, 'logout']);

    Route::patch('v1/auth/me', [UserController::class, 'update']);

    Route::post('v1/notes', [NoteController::class, 'create']);
    Route::patch('v1/notes/{id}', [NoteController::class, 'update']);
    Route::delete('v1/notes/{id}', [NoteController::class, 'delete']);
    Route::patch('v1/notes/{id}/restore', [NoteController::class, 'restore']);
    Route::delete('v1/notes/{id}/force', [NoteController::class, 'destroy']);
});

Route::get('v1/notes', [NoteController::class, 'index']);
Route::get('v1/notes/{id}', [NoteController::class, 'showOne']);
