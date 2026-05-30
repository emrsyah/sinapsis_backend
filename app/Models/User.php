<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, HasUuids, Notifiable;

    protected $primaryKey = 'user_id';

    protected $keyType = 'string';

    public $incrementing = false;

    protected $fillable = [
        'name',
        'email',
        'image',
        'google_id',
        'last_opened_note_id',
    ];

    protected $hidden = [
        'remember_token',
    ];

    // Relationships
    public function notes(): HasMany
    {
        return $this->hasMany(Note::class, 'user_id', 'user_id');
    }

    public function folders(): HasMany
    {
        return $this->hasMany(Folder::class, 'user_id', 'user_id');
    }

    public function tags(): HasMany
    {
        return $this->hasMany(Tag::class, 'user_id', 'user_id');
    }

    public function lastOpenedNote(): BelongsTo
    {
        return $this->belongsTo(Note::class, 'last_opened_note_id');
    }

    // Scopes
    public function scopeForUser(Builder $query, User $user): Builder
    {
        return $query->where('user_id', $user->user_id);
    }
}
