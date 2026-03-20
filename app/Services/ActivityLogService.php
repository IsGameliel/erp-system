<?php

namespace App\Services;

use App\Models\ActivityLog;
use Illuminate\Database\Eloquent\Model;

class ActivityLogService
{
    public function log(?int $userId, string $action, string $module, string $description, ?Model $subject = null): ActivityLog
    {
        return ActivityLog::create([
            'user_id' => $userId,
            'action' => $action,
            'module' => $module,
            'description' => $description,
            'subject_id' => $subject?->getKey(),
            'subject_type' => $subject ? $subject::class : null,
        ]);
    }
}
