<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Organization extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'brand_name',
        'slug',
        'primary_domain',
        'domain_verified_at',
        'db_connection',
        'db_host',
        'db_port',
        'db_database',
        'db_username',
        'db_password',
        'contact_email',
        'contact_phone',
        'address',
        'current_subscription_plan_id',
        'subscription_active',
        'subscription_expires_at',
        'setup_completed_at',
    ];

    protected function casts(): array
    {
        return [
            'subscription_active' => 'boolean',
            'subscription_expires_at' => 'date',
            'setup_completed_at' => 'datetime',
            'domain_verified_at' => 'datetime',
            'db_password' => 'encrypted',
        ];
    }

    protected static function booted(): void
    {
        static::saving(function (self $organization): void {
            if (blank($organization->slug)) {
                $organization->slug = Str::slug($organization->name).'-'.Str::lower(Str::random(4));
            }
        });
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function subscriptionPayments(): HasMany
    {
        return $this->hasMany(SubscriptionPayment::class);
    }

    public function currentSubscriptionPlan(): BelongsTo
    {
        return $this->belongsTo(SubscriptionPlan::class, 'current_subscription_plan_id');
    }

    public function setupCompleted(): bool
    {
        return $this->setup_completed_at !== null;
    }

    public function hasActiveSubscription(): bool
    {
        if (! $this->subscription_active) {
            return false;
        }

        return ! $this->subscription_expires_at || $this->subscription_expires_at->isFuture() || $this->subscription_expires_at->isToday();
    }

    public function displayBrandName(): string
    {
        return $this->brand_name ?: $this->name;
    }
}
