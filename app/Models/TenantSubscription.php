<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Stancl\Tenancy\Database\Concerns\BelongsToTenant;

class TenantSubscription extends Model
{
     use BelongsToTenant;  //// notice this trait

    protected $fillable = [
        'tenant_id',
        'purchase_plan_id',
        'status',
        'price',
        'trial_ends_at',
        'ends_at',
        'created_at',
        'updated_at'
    ];

    protected $casts = [
        'trial_ends_at' => 'datetime',
        'ends_at' => 'datetime'
    ];

    public function tenant()
    {
        return $this->belongsTo(config('tenancy.tenant_model'));
    }

    public function purchasePlan()
    {
        return $this->belongsTo(PurchasePlan::class);
    }

    public function isActive()
    {
        return $this->status === 'active' && 
               ($this->ends_at === null || $this->ends_at->isFuture());
    }

    public function onTrial()
    {
        return $this->trial_ends_at && $this->trial_ends_at->isFuture();
    }
}
