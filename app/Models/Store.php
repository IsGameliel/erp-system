<?php

namespace App\Models;

use App\Support\TenantContext;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class Store extends TenantModel
{
    use HasFactory;

    protected $fillable = [
        'name',
        'code',
        'location',
        'description',
    ];

    public function users(): HasMany
    {
        return $this->hasMany(User::class)->where('organization_id', $this->organizationId());
    }

    public function salesOrders(): HasMany
    {
        return $this->hasMany(SalesOrder::class);
    }

    public function salesOfficers(): HasMany
    {
        return $this->users()->where('role', User::ROLE_SALES_OFFICER);
    }

    public function productQuantities(): HasMany
    {
        return $this->hasMany(StoreProductQuantity::class);
    }

    public function procurementOfficers(): HasMany
    {
        return $this->users()->where('role', User::ROLE_PROCUREMENT_OFFICER);
    }

    public function activityLogs(): MorphMany
    {
        return $this->morphMany(ActivityLog::class, 'subject');
    }

    private function organizationId(): ?int
    {
        return auth()->user()?->organization_id ?: app(TenantContext::class)->get()?->id;
    }
}
