<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class NoteLink extends Model
{
    use HasUuids;

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'source_note',
        'target_note',
    ];

    /**
     * The note that is the source of the link.
     */
    public function source(): BelongsTo
    {
        return $this->belongsTo(Note::class, 'source_note');
    }

    /**
     * The note that is the target of the link.
     */
    public function target(): BelongsTo
    {
        return $this->belongsTo(Note::class, 'target_note');
    }
}
