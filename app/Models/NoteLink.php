<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class NoteLink extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'note_links';
    public $incrementing = false;
    protected $keyType = 'string';
    protected $fillable = [
        'source_note',
        'target_note',
    ];

    public function source(): BelongsTo
    {
        return $this->belongsTo(Note::class, 'source_note', 'note_id');
    }

    public function target(): BelongsTo
    {
        return $this->belongsTo(Note::class, 'target_note', 'note_id');
    }
}
