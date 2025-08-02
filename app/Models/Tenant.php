<?php

namespace App\Models;

use Stancl\Tenancy\Database\Models\Tenant as BaseTenant;
use Stancl\Tenancy\Contracts\TenantWithDatabase;
use Stancl\Tenancy\Database\Concerns\HasDatabase;
use Stancl\Tenancy\Database\Concerns\HasDomains;

class Tenant extends BaseTenant implements TenantWithDatabase
{
    use HasDatabase, HasDomains;



// protected $casts = [
//             'data' => 'array', // Cast the JSON column to an array
//         ];

    public function owner()
    {
        return $this->belongsTo(User::class, 'owner_id');

    }
    public function plan()
    {
        return $this->belongsTo(Plan::class, 'plan_id');
    }



    public function users()
    {
        return $this->belongsToMany(User::class);
    }



    public function subscription()
    {
        return $this->hasOne(TenantSubscription::class);
    }


    public function subscriptions()
    {
        return $this->hasMany(TenantSubscription::class);
    }

    public function currentSubscription()
    {
        return $this->subscription()->where('id', $this->tenant_subscription_id)->first();
        // return $this->subscription()->where('status', 'active')->first();
    }


    public function hasActiveSubscription()
    {
        $subscription = $this->currentSubscription();
        return $subscription && $subscription->isActive();
    }

    public function isOnTrial()
    {
        $subscription = $this->currentSubscription();
        return $subscription && $subscription->onTrial();
    }

    public function canAccess($feature)
    {
        $subscription = $this->currentSubscription();
        if (!$subscription || !$subscription->isActive()) {
            return false;
        }

        $features = $subscription->plan->features ?? [];
        return in_array($feature, $features);
    }




    public function notificationEmail()
    {
        // Return the email for notifications
        // You might have this stored in tenant data or related user
        return $this->data['email'] ?? $this->domains->first()?->domain . '@example.com';
    }


}