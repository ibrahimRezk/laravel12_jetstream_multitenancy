<?php

namespace App\Listeners;

use App\Models\Plan;
use App\Services\PlanService;
use Laravel\Cashier\Events\WebhookReceived;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use Laravel\Cashier\Subscription;

class StripeWebhookListener
{
    /**
     * Handle the webhook received event.
     */
    public function handle(WebhookReceived $event): void
    {
        $payload = $event->payload;

        match ($payload['type']) {

            'customer.subscription.created' => $this->handleSubscriptionCreated($payload), // object.plan
            'customer.subscription.updated' => $this->handleSubscriptionUpdated($payload),// object.plan
            'invoice.payment_succeeded' => $this->handleInvoicePaymentSucceded($payload), //object.lines.data.0.plan 0 for create  and the last if update

            default => Log::info('Unhandled webhook event: ' . $payload['type'])
        };
    }


    protected function handleSubscriptionCreated(array $payload)
    {
            $planFromStripe = $payload['data']['object']['plan'];
        $customerId = $payload['data']['object']['customer'];
        $user = User::where('stripe_id', $customerId)->first();

    
        if ($user) {
            $planService = new PlanService();
            
            $plan_id = str_replace('"', '', $planFromStripe['id']); // important
            Log::info(" from create  plan price is   :  {$plan_id}");

            $tenant = $user->tenants[0]; /// check
            $plan = Plan::where('price_id_on_stripe', $plan_id)->first();


            // check in case of first time subscription
            $planService->subscribeTenant($tenant, $plan);

            Log::info("Invoice subscription created for user: {$user->email}");
        }
    }
    protected function handleSubscriptionUpdated(array $payload)
    {

        $planFromStripe = $payload['data']['object']['plan'];
        
        
        $customerId = $payload['data']['object']['customer'];
        $user = User::where('stripe_id', $customerId)->first();
        
        if ($user) {
            
            $planService = new PlanService();
            
            $plan_id = str_replace('"', '', $planFromStripe['id']); // important
            $plan = Plan::where('price_id_on_stripe', $plan_id)->first();
            
            $tenant = $user->tenants[0]; /// check
        
            // check in case of first time subscription
            $planService->updateTenantSubscription($tenant , $plan);

            Log::info("Invoice subscription updated for user: {$user->email}");
        }
    }
    protected function handleInvoicePaymentSucceded(array $payload)
    {
        $lastData = end($payload['data']['object']['lines']['data']); /// customer may have subscribed to several plans before so weneed the last one
        $planFromStripe = $lastData['plan'];

        $invoice = $payload['data']['object'];
        $customerId = $invoice['customer'];
        $user = User::where('stripe_id', $customerId)->first();
        
        if ($user) {
            
            $planService = new PlanService();
            
            $plan_id = str_replace('"', '', $planFromStripe['id']); // important
            Log::info(" plan price is   :  {$plan_id}");

            $tenant = $user->tenants[0]; /// check
            $plan = Plan::where('price_id_on_stripe', $plan_id)->first();

            // check in case of first time subscription
            $planService->handlePaymentSucceded($tenant, $plan);

            Log::info("Invoice payment succeeded for user: {$user->email}");
        }
    }


}
