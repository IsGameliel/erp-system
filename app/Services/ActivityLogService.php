<?php

namespace App\Services;

use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

class ActivityLogService
{
    public function log(
        ?int $userId,
        string $action,
        string $module,
        string $description,
        ?Model $subject = null,
        array $oldValues = [],
        array $newValues = [],
    ): ActivityLog {
        [$oldValues, $newValues] = $this->diff($oldValues, $newValues);
        $userRole = $userId ? User::query()->find($userId)?->role : null;
        $payload = [
            'user_id' => $userId,
            'user_role' => $userRole,
            'action' => $action,
            'module' => $module,
            'description' => $description,
            'subject_id' => $subject?->getKey(),
            'subject_type' => $subject ? $subject::class : null,
        ];

        if (ActivityLog::supportsChangeSnapshots()) {
            $payload['old_values'] = $oldValues === [] ? null : $oldValues;
            $payload['new_values'] = $newValues === [] ? null : $newValues;
        }

        if (! ActivityLog::schemaIsReady()) {
            return new ActivityLog($payload);
        }

        return ActivityLog::create($payload);
    }

    public function diff(array $before, array $after): array
    {
        $oldValues = [];
        $newValues = [];

        foreach (array_unique([...array_keys($before), ...array_keys($after)]) as $key) {
            $oldValue = $this->normalizeValue($before[$key] ?? null);
            $newValue = $this->normalizeValue($after[$key] ?? null);

            if ($oldValue === $newValue) {
                continue;
            }

            $oldValues[$key] = $oldValue;
            $newValues[$key] = $newValue;
        }

        return [$oldValues, $newValues];
    }

    private function normalizeValue(mixed $value): mixed
    {
        if ($value instanceof Collection) {
            $value = $value->all();
        }

        if ($value instanceof \DateTimeInterface) {
            return $value->format('Y-m-d H:i:s');
        }

        if (is_array($value)) {
            $normalized = [];

            foreach ($value as $key => $item) {
                $normalized[$key] = $this->normalizeValue($item);
            }

            if ($this->isAssociative($normalized)) {
                ksort($normalized);
            }

            return $normalized;
        }

        if (is_bool($value) || is_null($value) || is_int($value) || is_float($value) || is_string($value)) {
            return $value;
        }

        if ($value instanceof \Stringable) {
            return (string) $value;
        }

        return $value;
    }

    private function isAssociative(array $value): bool
    {
        return $value !== [] && array_keys($value) !== range(0, count($value) - 1);
    }
}
