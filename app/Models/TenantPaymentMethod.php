<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Stancl\Tenancy\Database\Concerns\BelongsToTenant;

class TenantPaymentMethod extends Model
{
     use BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'type', // 'card', 'bank_account', etc.
        'provider', // 'stripe', 'paypal', etc.
        'provider_id', // External ID from payment provider
        'last_four',
        'brand', // visa, mastercard, etc.
        'expires_at',
        'is_default',
        'is_active'
    ];

    protected $casts = [
        'expires_at' => 'datetime',
        'is_default' => 'boolean',
        'is_active' => 'boolean'
    ];

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }
}
