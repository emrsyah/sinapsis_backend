<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Note extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'user_id',
        'folder_id',
        'title',
        'content',
        'is_published',
        'share_token',
    ];

    protected function casts(): array
    {
        return [
            'is_published' => 'boolean',
            'deleted_at' => 'datetime',
        ];
    }

    // Sharing
    public function generateShareToken(): string
    {
        if ($this->share_token) {
            return $this->share_token;
        }

        $token = Str::random(64);
        $this->update([
            'is_published' => true,
            'share_token' => $token,
        ]);

        return $token;
    }

    public function unpublish(): void
    {
        $this->update([
            'is_published' => false,
            'share_token' => null,
        ]);
    }

    // Relationships
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'user_id');
    }

    public function folder(): BelongsTo
    {
        return $this->belongsTo(Folder::class);
    }

    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(Tag::class, 'note_tags');
    }

    public function attachments(): HasMany
    {
        return $this->hasMany(Attachment::class);
    }

    public function studyTools(): HasMany
    {
        return $this->hasMany(StudyToolGeneration::class);
    }

    public function outgoingLinks(): BelongsToMany
    {
        return $this->belongsToMany(Note::class, 'note_links', 'source_note', 'target_note');
    }

    public function backlinks(): BelongsToMany
    {
        return $this->belongsToMany(Note::class, 'note_links', 'target_note', 'source_note');
    }

    // Scopes
    public function scopeForUser(Builder $query, User $user): Builder
    {
        return $query->where('user_id', $user->user_id);
    }
 
    public function scopeNotTrashed(Builder $query): Builder
    {
        return $query->whereNull('deleted_at');
    }
}
