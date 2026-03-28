<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Facades\Schema;

class ActivityLog extends TenantModel
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'user_role',
        'action',
        'module',
        'description',
        'old_values',
        'new_values',
        'subject_id',
        'subject_type',
    ];

    protected function casts(): array
    {
        return [
            'old_values' => 'array',
            'new_values' => 'array',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function subject(): MorphTo
    {
        return $this->morphTo();
    }

    public static function schemaIsReady(): bool
    {
        return Schema::connection('tenant')->hasTable('activity_logs');
    }

    public static function supportsChangeSnapshots(): bool
    {
        return static::schemaIsReady()
            && Schema::connection('tenant')->hasColumns('activity_logs', ['old_values', 'new_values']);
    }
}
