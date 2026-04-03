<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Support\Str;

class Note extends Model
{
    use HasFactory, HasUuids;

    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'user_id',
        'folder_id',
        'title',
        'content',
        'is_published',
        'share_token',
        'deleted_at',
    ];

    protected function casts(): array
    {
        return [
            'is_published' => 'boolean',
            'deleted_at'   => 'datetime',
        ];
    }

    // Custom soft delete (manual, not SoftDeletes trait, so we control trash queries)
    public function scopeActive($query)
    {
        return $query->whereNull('deleted_at');
    }

    public function scopeTrashed($query)
    {
        return $query->whereNotNull('deleted_at');
    }

    public function softDelete(): void
    {
        $this->update(['deleted_at' => now()]);
    }

    public function restore(): void
    {
        $this->update(['deleted_at' => null]);
    }

    public function isDeleted(): bool
    {
        return $this->deleted_at !== null;
    }

    // Sharing
    public function generateShareToken(): string
    {
        $token = Str::random(64);
        $this->update([
            'is_published' => true,
            'share_token'  => $token,
        ]);
        return $token;
    }

    public function unpublish(): void
    {
        $this->update([
            'is_published' => false,
            'share_token'  => null,
        ]);
    }

    // Relationships
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function folder()
    {
        return $this->belongsTo(Folder::class);
    }

    public function tags()
    {
        return $this->belongsToMany(Tag::class, 'note_tags');
    }

    public function attachments()
    {
        return $this->hasMany(Attachment::class);
    }

    public function studyTools()
    {
        return $this->hasMany(StudyToolGeneration::class);
    }

    // Bi-directional links
    public function outgoingLinks()
    {
        return $this->hasMany(NoteLink::class, 'source_note');
    }

    public function incomingLinks()
    {
        return $this->hasMany(NoteLink::class, 'target_note');
    }

    public function linkedNotes()
    {
        return $this->belongsToMany(
            Note::class,
            'note_links',
            'source_note',
            'target_note'
        );
    }

    public function backlinks()
    {
        return $this->belongsToMany(
            Note::class,
            'note_links',
            'target_note',
            'source_note'
        );
    }
}
