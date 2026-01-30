<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

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

    public function songRequests(): HasMany
    {
        return $this->hasMany(SongRequest::class);
    }
}
