<?php

namespace App\Models;

use App\Support\TenantContext;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\Model as EloquentModel;
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

    public static function schemaIsReady(?EloquentModel $subject = null, ?string $userRole = null): bool
    {
        return Schema::connection(static::connectionNameFor($subject, $userRole))->hasTable('activity_logs');
    }

    public static function supportsChangeSnapshots(?EloquentModel $subject = null, ?string $userRole = null): bool
    {
        $connection = static::connectionNameFor($subject, $userRole);

        return static::schemaIsReady($subject, $userRole)
            && Schema::connection($connection)->hasColumns('activity_logs', ['old_values', 'new_values']);
    }

    public static function connectionNameFor(?EloquentModel $subject = null, ?string $userRole = null): string
    {
        if ($subject instanceof TenantModel) {
            return 'tenant';
        }

        if ($userRole === User::ROLE_SUPER_ADMIN) {
            return 'mysql';
        }

        return app(TenantContext::class)->get() ? 'tenant' : 'mysql';
    }
}
