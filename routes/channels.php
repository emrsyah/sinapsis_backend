<?php

use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (string) $user->user_id === (string) $id;
});

Broadcast::channel('note.{noteId}', function ($user, $noteId) {
    $note = \App\Models\Note::find($noteId);
    return $note && (string) $note->user_id === (string) $user->user_id;
});
