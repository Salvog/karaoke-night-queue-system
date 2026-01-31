<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PlaybackState extends Model
{
    use HasFactory;

    public const STATE_IDLE = 'idle';
    public const STATE_PLAYING = 'playing';
    public const STATE_PAUSED = 'paused';

    public const STATES = [
        self::STATE_IDLE,
        self::STATE_PLAYING,
        self::STATE_PAUSED,
    ];

    protected $fillable = [
        'event_night_id',
        'current_request_id',
        'state',
        'started_at',
        'expected_end_at',
        'paused_at',
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'expected_end_at' => 'datetime',
        'paused_at' => 'datetime',
    ];

    public function eventNight(): BelongsTo
    {
        return $this->belongsTo(EventNight::class);
    }

    public function currentRequest(): BelongsTo
    {
        return $this->belongsTo(SongRequest::class, 'current_request_id');
    }
}
