<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Participant extends Model
{
    use HasFactory;

    protected $fillable = [
        'event_night_id',
        'device_cookie_id',
        'join_token_hash',
        'pin_verified_at',
        'display_name',
    ];

    protected $casts = [
        'pin_verified_at' => 'datetime',
    ];

    public function eventNight(): BelongsTo
    {
        return $this->belongsTo(EventNight::class);
    }

    public function songRequests(): HasMany
    {
        return $this->hasMany(SongRequest::class);
    }
}
