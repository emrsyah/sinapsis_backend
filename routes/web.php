<?php

use App\Http\Controllers\AuthController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;

Route::get('/', function () {
    return view('welcome');
});

Route::get('auth/google/callback', [AuthController::class, 'handleGoogleCallback']);

Route::get('/websocket-test', function () {
    $user = \App\Models\User::firstOrCreate(
        ['email' => 'test@example.com'],
        ['name' => 'Test User']
    );
    
    $note = \App\Models\Note::firstOrCreate(
        ['user_id' => $user->user_id],
        ['title' => 'Test Note', 'content' => 'Test Content']
    );

    Auth::login($user);

    return view('websocket-test', ['note' => $note]);
});
