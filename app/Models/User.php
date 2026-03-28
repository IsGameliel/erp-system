<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    public const ROLE_SUPER_ADMIN = 'super_admin';
    public const ROLE_ADMIN = 'admin';
    public const ROLE_SALES_OFFICER = 'sales_officer';
    public const ROLE_PROCUREMENT_OFFICER = 'procurement_officer';

    public const ROLES = [
        self::ROLE_SUPER_ADMIN,
        self::ROLE_ADMIN,
        self::ROLE_SALES_OFFICER,
        self::ROLE_PROCUREMENT_OFFICER,
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'organization_id',
        'role',
        'access_enabled',
        'access_expires_at',
        'store_id',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'access_enabled' => 'boolean',
            'access_expires_at' => 'date',
            'password' => 'hashed',
        ];
    }

    public static function roleOptions(): array
    {
        return self::ROLES;
    }

    public function getConnectionName()
    {
        return config('database.default');
    }

    public function hasRole(string $role): bool
    {
        return $this->role === $role;
    }

    public function hasAnyRole(array|string $roles): bool
    {
        $roles = is_array($roles) ? $roles : [$roles];

        return in_array($this->role, $roles, true);
    }

    public function isSuperAdmin(): bool
    {
        return $this->hasRole(self::ROLE_SUPER_ADMIN);
    }

    public function canManageUsage(): bool
    {
        return $this->isSuperAdmin();
    }

    public function canUseApplication(): bool
    {
        if ($this->isSuperAdmin()) {
            return true;
        }

        if ($this->organization && (! $this->organization->setupCompleted() || ! $this->organization->hasActiveSubscription())) {
            return false;
        }

        if (! $this->access_enabled) {
            return false;
        }

        return ! $this->access_expires_at || $this->access_expires_at->isFuture() || $this->access_expires_at->isToday();
    }

    public function hasPendingPayment(): bool
    {
        if ($this->organization) {
            return $this->organization->subscriptionPayments()
                ->where('status', SubscriptionPayment::STATUS_PENDING)
                ->exists();
        }

        return $this->subscriptionPayments()
            ->where('status', SubscriptionPayment::STATUS_PENDING)
            ->exists();
    }

    public function accessStatusLabel(): string
    {
        if ($this->isSuperAdmin()) {
            return 'Owner access';
        }

        if ($this->organization && ! $this->organization->setupCompleted()) {
            return 'Organization setup pending';
        }

        if ($this->organization && ! $this->organization->hasActiveSubscription()) {
            if ($this->hasPendingPayment()) {
                return 'Subscription payment pending';
            }

            return 'Subscription inactive';
        }

        if (! $this->access_enabled) {
            if ($this->hasPendingPayment()) {
                return 'Payment pending approval';
            }

            return 'Disabled';
        }

        if ($this->access_expires_at && $this->access_expires_at->isPast()) {
            return 'Expired';
        }

        if ($this->access_expires_at) {
            return 'Active until '.$this->access_expires_at->format('M d, Y');
        }

        return 'Active';
    }

    public function customers(): HasMany
    {
        return $this->hasMany(Customer::class, 'created_by');
    }

    public function vendors(): HasMany
    {
        return $this->hasMany(Vendor::class, 'created_by');
    }

    public function salesOrders(): HasMany
    {
        return $this->hasMany(SalesOrder::class);
    }

    public function purchaseOrders(): HasMany
    {
        return $this->hasMany(PurchaseOrder::class);
    }

    public function activityLogs(): HasMany
    {
        return $this->hasMany(ActivityLog::class);
    }

    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class);
    }

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    public function subscriptionPayments(): HasMany
    {
        return $this->hasMany(SubscriptionPayment::class, 'submitted_by');
    }

    public function approvedSubscriptionPayments(): HasMany
    {
        return $this->subscriptionPayments()->where('status', SubscriptionPayment::STATUS_APPROVED);
    }

    public function latestSubscription(): ?SubscriptionPayment
    {
        if ($this->organization) {
            return $this->organization->subscriptionPayments()->latest()->first();
        }

        return $this->subscriptionPayments->first();
    }
}
