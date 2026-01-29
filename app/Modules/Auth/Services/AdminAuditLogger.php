<?php

namespace App\Modules\Auth\Services;

use App\Modules\Auth\DTOs\AdminActionData;
use App\Modules\Auth\Models\AdminAuditLog;

class AdminAuditLogger
{
    public function log(AdminActionData $data): AdminAuditLog
    {
        return AdminAuditLog::create([
            'actor_id' => $data->actorId,
            'action' => $data->action,
            'subject_type' => $data->subjectType,
            'subject_id' => $data->subjectId,
            'metadata' => $data->metadata,
        ]);
    }
}
