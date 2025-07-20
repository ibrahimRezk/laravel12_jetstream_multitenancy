<?php

namespace App\Listeners;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use App\Events\SubscriptionCreated;
use App\Mail\SubscriptionWelcome;
use Illuminate\Support\Facades\Mail;

class SendSubscriptionWelcomeEmail
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(SubscriptionCreated $event)
    {
        $subscription = $event->subscription;
        $tenant = $subscription->tenant;
        
        // Assuming tenant has an email or user relationship
        if ($tenant->email) {
            Mail::to($tenant->email)->send(new SubscriptionWelcome($subscription));
        }
    }
}
