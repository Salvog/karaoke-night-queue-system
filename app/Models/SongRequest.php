<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\QueryException;

class SongRequest extends Model
{
    use HasFactory;

    public const STATUS_QUEUED = 'queued';
    public const STATUS_PLAYING = 'playing';
    public const STATUS_PLAYED = 'played';
    public const STATUS_CANCELED = 'canceled';
    public const STATUS_SKIPPED = 'skipped';

    public const STATUSES = [
        self::STATUS_QUEUED,
        self::STATUS_PLAYING,
        self::STATUS_PLAYED,
        self::STATUS_CANCELED,
        self::STATUS_SKIPPED,
    ];

    protected $fillable = [
        'event_night_id',
        'participant_id',
        'song_id',
        'status',
        'position',
        'played_at',
    ];

    protected $casts = [
        'played_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::saving(function (SongRequest $songRequest) {
            // Simplest guard: enforce status constraints when SQLite skips CHECK constraints.
            if (! in_array($songRequest->status, self::STATUSES, true)) {
                throw new QueryException('sqlite', '', [], new \RuntimeException('Invalid status.'));
            }
        });
    }

    public function eventNight(): BelongsTo
    {
        return $this->belongsTo(EventNight::class);
    }

    public function participant(): BelongsTo
    {
        return $this->belongsTo(Participant::class);
    }

    public function song(): BelongsTo
    {
        return $this->belongsTo(Song::class);
    }
}
