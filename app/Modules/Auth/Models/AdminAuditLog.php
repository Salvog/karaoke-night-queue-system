<?php

namespace App\Modules\Auth\Models;

use Illuminate\Database\Eloquent\Model;

class AdminAuditLog extends Model
{
    protected $table = 'audit_logs';

    protected $fillable = [
        'actor_id',
        'action',
        'subject_type',
        'subject_id',
        'metadata',
    ];

    protected $casts = [
        'metadata' => 'array',
    ];
}
