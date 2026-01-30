<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\QueryException;

class Song extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'artist',
        'duration_seconds',
    ];

    protected $casts = [
        'duration_seconds' => 'integer',
    ];

    protected static function booted(): void
    {
        static::saving(function (Song $song) {
            // Simplest guard: enforce duration constraints when SQLite skips CHECK constraints.
            if ($song->duration_seconds <= 0) {
                throw new QueryException('sqlite', '', [], new \RuntimeException('Duration must be positive.'));
            }
        });
    }

    public function songRequests(): HasMany
    {
        return $this->hasMany(SongRequest::class);
    }
}
