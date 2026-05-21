<?php

use App\Http\Controllers\AttachmentController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\FolderController;
use App\Http\Controllers\NoteController;
use App\Http\Controllers\NoteLinkController;
use App\Http\Controllers\ShareController;
use App\Http\Controllers\TagController;
use App\Http\Controllers\StudyToolController;
use Illuminate\Support\Facades\Route;

Route::get('auth/google/callback', [AuthController::class, 'handleGoogleCallback']);

Route::prefix('v1')->group(function () {
    // OAuth
    Route::get('auth/login', [AuthController::class, 'redirectToGoogle'])->name('google.login');

    // Public Sharing
    Route::get('shared/{token}', [ShareController::class, 'show']);

    // Protected routes
    Route::middleware('auth:sanctum')->group(function () {
        // Auth
        Route::post('auth/logout', [AuthController::class, 'logout']);
        Route::get('auth/me', [AuthController::class, 'me']);
        Route::patch('auth/me', [AuthController::class, 'update']);
        Route::patch('auth/me/last-opened', [AuthController::class, 'updateLastOpened']);

        // Notes
        Route::get('notes', [NoteController::class, 'index']);
        Route::post('notes', [NoteController::class, 'store']);
        Route::get('notes/{note}', [NoteController::class, 'show']);
        Route::patch('notes/{note}', [NoteController::class, 'update']);
        Route::delete('notes/{note}', [NoteController::class, 'destroy']);
        Route::patch('notes/{id}/restore', [NoteController::class, 'restore']);
        Route::delete('notes/{id}/force', [NoteController::class, 'forceDelete']);

        // Sharing actions
        Route::post('notes/{note}/publish', [NoteController::class, 'share']);
        Route::delete('notes/{note}/publish', [NoteController::class, 'unshare']);

        // Note Tags (Specific Note-Tag context)
        Route::post('notes/{note}/tags/{tag}', [NoteController::class, 'attachTag']);
        Route::delete('notes/{note}/tags/{tag}', [NoteController::class, 'detachTag']);

        // Note Links (Inter-note linking)
        Route::get('notes/{note}/backlinks', [NoteLinkController::class, 'index']);
        Route::post('notes/{note}/links', [NoteLinkController::class, 'store']);
        Route::delete('notes/{note}/links/{target}', [NoteLinkController::class, 'destroy']);

        // Attachments
        Route::get('notes/{note}/attachments', [AttachmentController::class, 'index']);
        Route::post('notes/{note}/attachments', [AttachmentController::class, 'store']);
        Route::delete('attachments/{attachment}', [AttachmentController::class, 'destroy']);

        // Folders
        Route::get('folders', [FolderController::class, 'index']);
        Route::post('folders', [FolderController::class, 'store']);
        Route::patch('folders/{folder}', [FolderController::class, 'update']);
        Route::delete('folders/{folder}', [FolderController::class, 'destroy']);

        // Tags
        Route::get('tags', [TagController::class, 'index']);
        Route::post('tags', [TagController::class, 'store']);
        Route::patch('tags/{tag}', [TagController::class, 'update']);
        Route::delete('tags/{tag}', [TagController::class, 'destroy']);

        // Study Tools
        Route::post('notes/{id}/study-tools', [StudyToolController::class, 'store']);
        Route::get('study-tools/{id}', [StudyToolController::class, 'showOne']);
        Route::get('notes/{id}/study-tools', [StudyToolController::class, 'index']);
    });
});
