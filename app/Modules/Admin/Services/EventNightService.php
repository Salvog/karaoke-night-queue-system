<?php

namespace App\Modules\Admin\Services;

use App\Models\EventNight;
use Illuminate\Support\Facades\DB;

class EventNightService
{
    private const CODE_ALPHABET = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';
    private const CODE_LENGTH = 6;

    public function generateCode(): string
    {
        return $this->generateUniqueCode();
    }

    public function create(array $data): EventNight
    {
        return DB::transaction(function () use ($data) {
            $data['code'] = $data['code'] ?? $this->generateCode();

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
            $code = $this->generateReadableCode();
        } while (EventNight::where('code', $code)->exists());

        return $code;
    }

    private function generateReadableCode(): string
    {
        $maxIndex = strlen(self::CODE_ALPHABET) - 1;
        $code = '';

        for ($index = 0; $index < self::CODE_LENGTH; $index++) {
            $code .= self::CODE_ALPHABET[random_int(0, $maxIndex)];
        }

        return $code;
    }
}
