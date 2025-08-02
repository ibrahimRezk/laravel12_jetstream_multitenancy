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
            'checkout.session.completed' => $this->handleCheckoutCompleted($payload),
            //'customer.subscription.updated' => $this->handleSubscriptionUpdated($payload),  // suitable on case of changing subscription updated or cancelled

            default => Log::info('Unhandled webhook event: ' . $payload['type'])
        };
    }


    protected function handleCheckoutCompleted(array $payload)
    {

        // $planFromStripe = $payload['data']['object']['lines']['data'][0]['plan']; //=======long way 
        $planFromStripe = $this->findPlan($payload, 'plan'); //=========better method

        $invoice = $payload['data']['object'];
        $customerId = $invoice['customer'];
        $user = User::where('stripe_id', $customerId)->first();



        if ($user) {

            $planService = new PlanService();

            $plan_id = str_replace('"', '', $planFromStripe['id']); // important

            $tenant = $user->tenants[0]; /// check
            $plan = Plan::where('price_id_on_stripe', $plan_id)->first();


            // check in case of first time subscription
            $planService->subscribeTenant($tenant, $plan);

            Log::info("Invoice payment succeeded for user: {$user->email}");
        }
    }

    // to find the plan details from returned data in webhook 
    public function findPlan(array $array, string $searchKey)
    {
        foreach ($array as $key => $value) {
            // Check if the current key matches the search key
            if ($key === $searchKey) {
                return $value; // Return the value associated with the found key
            }

            // If the current value is an array, recursively search within it
            if (is_array($value)) {
                $found = $this->findPlan($value, $searchKey);
                if ($found !== null) { // If the key was found in a nested array
                    return $found; // Return the found value
                }
            }
        }

        return null; // Return null if the key is not found anywhere
    }

}
