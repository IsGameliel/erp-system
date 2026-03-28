<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Support\Facades\Schema;

class ProductCategory extends TenantModel
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
    ];

    public static function schemaIsReady(): bool
    {
        return once(function (): bool {
            $model = new static();

            try {
                return Schema::connection($model->getConnectionName())->hasTable($model->getTable());
            } catch (\Throwable) {
                return false;
            }
        });
    }

    public function products(): HasMany
    {
        return $this->hasMany(Product::class, 'category_id');
    }

    public function activityLogs(): MorphMany
    {
        return $this->morphMany(ActivityLog::class, 'subject');
    }
}
