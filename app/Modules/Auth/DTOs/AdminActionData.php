<?php

namespace App\Modules\Auth\DTOs;

class AdminActionData
{
    public function __construct(
        public readonly int $actorId,
        public readonly string $action,
        public readonly ?string $subjectType = null,
        public readonly ?string $subjectId = null,
        public readonly array $metadata = [],
    ) {
    }
}
