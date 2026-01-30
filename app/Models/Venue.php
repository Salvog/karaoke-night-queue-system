<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Venue extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'timezone',
    ];

    public function eventNights(): HasMany
    {
        return $this->hasMany(EventNight::class);
    }

    public function themes(): HasMany
    {
        return $this->hasMany(Theme::class);
    }

    public function adBanners(): HasMany
    {
        return $this->hasMany(AdBanner::class);
    }
}
