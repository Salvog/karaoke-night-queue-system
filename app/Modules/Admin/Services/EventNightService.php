<?php

namespace App\Modules\Admin\Services;

use App\Models\EventNight;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class EventNightService
{
    public function create(array $data): EventNight
    {
        return DB::transaction(function () use ($data) {
            $data['code'] = $data['code'] ?? $this->generateUniqueCode();

            return EventNight::create($data);
        });
    }

    public function update(EventNight $eventNight, array $data): EventNight
    {
        return DB::transaction(function () use ($eventNight, $data) {
            $eventNight->update($data);

            return $eventNight;
        });
    }

    private function generateUniqueCode(): string
    {
        do {
            $code = Str::upper(Str::random(10));
        } while (EventNight::where('code', $code)->exists());

        return $code;
    }
}
