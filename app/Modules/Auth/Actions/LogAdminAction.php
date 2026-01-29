<?php

namespace App\Modules\Auth\Actions;

use App\Modules\Auth\DTOs\AdminActionData;
use App\Modules\Auth\Models\AdminAuditLog;
use App\Modules\Auth\Services\AdminAuditLogger;

class LogAdminAction
{
    public function __construct(private readonly AdminAuditLogger $logger)
    {
    }

    public function execute(AdminActionData $data): AdminAuditLog
    {
        return $this->logger->log($data);
    }
}
