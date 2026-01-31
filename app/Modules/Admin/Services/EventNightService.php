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
            $code = $this->normalizeCode($data['code'] ?? null);
            $data['code'] = $code ?: $this->generateUniqueCode();

            return EventNight::create($data);
        });
    }

    public function update(EventNight $eventNight, array $data): EventNight
    {
        return DB::transaction(function () use ($eventNight, $data) {
            if (array_key_exists('code', $data)) {
                $data['code'] = $this->normalizeCode($data['code']);
            }

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

    private function normalizeCode(?string $code): ?string
    {
        $trimmed = $code !== null ? trim($code) : null;

        if ($trimmed === '' || $trimmed === null) {
            return null;
        }

        return Str::upper($trimmed);
    }
}
