<?php

namespace App\Models;

use Stancl\Tenancy\Database\Models\Tenant as BaseTenant;
use Stancl\Tenancy\Contracts\TenantWithDatabase;
use Stancl\Tenancy\Database\Concerns\HasDatabase;
use Stancl\Tenancy\Database\Concerns\HasDomains;

class Tenant extends BaseTenant implements TenantWithDatabase
{
    use HasDatabase, HasDomains;




        public function owner()
    {
        return $this->belongsTo(User::class , 'ownerId');
        
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
        return $this->subscription()->where('status', 'active')->first() ;
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





    


        public function paymentMethods()
    {
        return $this->hasMany(TenantPaymentMethod::class);
    }

    public function defaultPaymentMethod()
    {
        return $this->paymentMethods()->where('is_default', true)->first();
    }

    public function hasValidPaymentMethod(): bool
    {
        $paymentMethod = $this->defaultPaymentMethod();
        
        if (!$paymentMethod) {
            return false;
        }

        // Check if payment method is not expired
        if ($paymentMethod->expires_at && $paymentMethod->expires_at->isPast()) {
            return false;
        }

        return true;
    }

    public function notificationEmail()
    {
        // Return the email for notifications
        // You might have this stored in tenant data or related user
        return $this->data['email'] ?? $this->domains->first()?->domain . '@example.com';
    }



}