<?php

namespace App\Modules\Auth\Services;

use App\Models\AdminAuditLog;
use App\Modules\Auth\DTOs\AdminActionData;

class AdminAuditLogger
{
    public function log(AdminActionData $data): AdminAuditLog
    {
        return AdminAuditLog::create([
            'user_id' => $data->userId,
            'action' => $data->action,
            'subject_type' => $data->subjectType,
            'subject_id' => $data->subjectId,
            'metadata' => $data->metadata,
        ]);
    }
}
