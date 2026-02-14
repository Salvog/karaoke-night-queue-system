<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class EventNight extends Model
{
    use HasFactory;

    public const STATUS_DRAFT = 'draft';
    public const STATUS_ACTIVE = 'active';
    public const STATUS_CLOSED = 'closed';

    public const STATUSES = [
        self::STATUS_DRAFT,
        self::STATUS_ACTIVE,
        self::STATUS_CLOSED,
    ];

    public const STATUS_LABELS = [
        self::STATUS_DRAFT => 'Bozza',
        self::STATUS_ACTIVE => 'Attivo',
        self::STATUS_CLOSED => 'Chiuso',
    ];

    protected $fillable = [
        'venue_id',
        'theme_id',
        'ad_banner_id',
        'code',
        'starts_at',
        'ends_at',
        'break_seconds',
        'request_cooldown_seconds',
        'join_pin',
        'status',
        'background_image_path',
        'brand_logo_path',
        'overlay_texts',
    ];

    protected $casts = [
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
        'overlay_texts' => 'array',
    ];

    public static function statusOptions(): array
    {
        return self::STATUS_LABELS;
    }

    public function venue(): BelongsTo
    {
        return $this->belongsTo(Venue::class);
    }

    public function theme(): BelongsTo
    {
        return $this->belongsTo(Theme::class);
    }

    public function adBanner(): BelongsTo
    {
        return $this->belongsTo(AdBanner::class);
    }

    public function participants(): HasMany
    {
        return $this->hasMany(Participant::class);
    }

    public function songRequests(): HasMany
    {
        return $this->hasMany(SongRequest::class);
    }

    public function playbackState(): HasOne
    {
        return $this->hasOne(PlaybackState::class);
    }
}
