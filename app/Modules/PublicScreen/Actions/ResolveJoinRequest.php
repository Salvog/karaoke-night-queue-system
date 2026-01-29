<?php

namespace App\Modules\PublicScreen\Actions;

class ResolveJoinRequest
{
    public function execute(string $eventCode, string $joinToken): array
    {
        return [
            'message' => 'Public join stub',
            'eventCode' => $eventCode,
            'joinToken' => $joinToken,
        ];
    }
}
