<?php

namespace App\Listeners;

use App\Models\Plan;
use App\Models\Tenant;
use App\Services\PlanService;
use Laravel\Cashier\Events\WebhookReceived;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class StripeWebhookListenerWithAllEvents
{
    /**
     * Handle the webhook received event.
     */
    // public function handle(WebhookReceived $event): void
    // {
    //     $payload = $event->payload;

    //     match ($payload['type']) {
    //         'checkout.session.completed' => $this->handleCheckoutCompleted($payload),
    //         'customer.subscription.created' => $this->handleSubscriptionCreated($payload),
    //         'customer.subscription.updated' => $this->handleSubscriptionUpdated($payload),
    //         'customer.subscription.deleted' => $this->handleSubscriptionDeleted($payload),
    //         'payment_intent.succeeded' => $this->handlePaymentSucceeded($payload),
    //         'payment_intent.payment_failed' => $this->handlePaymentFailed($payload),
    //         'invoice.payment_succeeded' => $this->handleInvoicePaymentSucceeded($payload),
    //         'invoice.payment_failed' => $this->handleInvoicePaymentFailed($payload),
    //         'invoice.paid' => $this->handleInvoicePaid($payload),
    //         default => Log::info('Unhandled webhook event: ' . $payload['type'])
    //     };
    // }

    /**
     * Handle checkout session completed
     */
    protected function handleCheckoutCompleted(array $payload): void
    {
        $session = $payload['data']['object'];
        $customerId = $session['customer'];
        $metadata = $session['metadata'] ?? [];

        $user = $this->findUser($customerId, $metadata);

        if ($user) {
            $user->update([
                'subscription_status' => 'active',
                'checkout_completed_at' => now(),
                'subscription_type' => $metadata['subscription_type'] ?? 'basic_monthly',
            ]);

            // Send welcome email notification
            // $user->notify(new SubscriptionActivatedNotification());

            Log::info("Checkout completed for user: {$user->email}");
        }
    }

    /**
     * Handle subscription created
     */
    protected function handleSubscriptionCreated(array $payload): void
    {
        $subscription = $payload['data']['object'];
        $customerId = $subscription['customer'];
        $user = User::where('stripe_id', $customerId)->first();

        if ($user) {
            $user->update([
                'subscription_status' => $subscription['status'],
                'subscription_created_at' => now(),
                'trial_ends_at' => isset($subscription['trial_end']) ?
                    Carbon::createFromTimestamp($subscription['trial_end']) : null,
            ]);

            Log::info("Subscription created for user: {$user->email}");
        }
    }

    /**
     * Handle subscription updated
     */
    protected function handleSubscriptionUpdated(array $payload): void
    {
        $subscription = $payload['data']['object'];
        $customerId = $subscription['customer'];
        $user = User::where('stripe_id', $customerId)->first();

        if ($user) {
            $user->update([
                'subscription_status' => $subscription['status'],
                'subscription_updated_at' => now(),
            ]);

            Log::info("Subscription updated for user: {$user->email}, Status: {$subscription['status']}");
        }
    }

    /**
     * Handle subscription deleted/cancelled
     */
    protected function handleSubscriptionDeleted(array $payload): void
    {
        $subscription = $payload['data']['object'];
        $customerId = $subscription['customer'];
        $user = User::where('stripe_id', $customerId)->first();

        if ($user) {
            $user->update([
                'subscription_status' => 'cancelled',
                'subscription_ended_at' => now(),
            ]);

            // Send cancellation notification
            // $user->notify(new SubscriptionCancelledNotification());

            Log::info("Subscription cancelled for user: {$user->email}");
        }
    }

    /**
     * Handle payment intent succeeded
     */
    protected function handlePaymentSucceeded(array $payload): void
    {

        \Log::info("11111111111111111111111");


        $paymentIntent = $payload['data']['object'];
        $customerId = $paymentIntent['customer'];

        if ($customerId) {
            $user = User::where('stripe_id', $customerId)->first();

            if ($user) {
                $user->update([
                    'subscription_status' => 'active',
                    'last_payment_date' => now(),
                    'payment_failures' => 0, // Reset failure count
                ]);

                // Send payment success notification
                // $user->notify(new PaymentSuccessfulNotification());

                Log::info("Payment succeeded for user: {$user->email}");
            }
        }
    }

    /**
     * Handle payment intent failed
     */
    protected function handlePaymentFailed(array $payload): void
    {
        $paymentIntent = $payload['data']['object'];
        $customerId = $paymentIntent['customer'];

        if ($customerId) {
            $user = User::where('stripe_id', $customerId)->first();

            if ($user) {
                $user->increment('payment_failures');
                $user->update(['last_payment_failure' => now()]);

                // Send payment failure notification
                // $user->notify(new PaymentFailedNotification());

                Log::warning("Payment failed for user: {$user->email}");
            }
        }
    }

    /**
     * Handle invoice paid  (recurring payments)
     */
    protected function handleInvoicePaid(array $payload)
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

    /**
     * Handle invoice payment succeeded (recurring payments)
     */
    protected function handleInvoicePaymentSucceeded(array $payload)
    {
        $invoice = $payload['data']['object'];
        $customerId = $invoice['customer'];
        $user = User::where('stripe_id', $customerId)->first();


        // $planService = new PlanService();
        // $tenant = Tenant::first(); // Assuming User model has a tenant relationship

        // $plan = Plan::first();

        // $planService->subscribeTenant($tenant, $plan);




        if ($user) {
            // $user->update([
            //     'last_payment_date' => now(),
            //     'subscription_status' => 'active',
            //     'payment_failures' => 0, // Reset failure count
            // ]);

            Log::info("Invoice payment succeeded for user: {$user->email}");
        }
    }

    /**
     * Handle invoice payment failed (recurring payment failures)
     */
    protected function handleInvoicePaymentFailed(array $payload): void
    {
        $invoice = $payload['data']['object'];
        $customerId = $invoice['customer'];
        $user = User::where('stripe_id', $customerId)->first();

        if ($user) {
            $user->increment('payment_failures');
            $user->update([
                'last_payment_failure' => now(),
                'subscription_status' => 'past_due',
            ]);

            // Send payment failure notification for recurring payment
            // $user->notify(new RecurringPaymentFailedNotification($invoice));

            Log::warning("Invoice payment failed for user: {$user->email}");
        }
    }

    /**
     * Find user by customer ID or metadata
     */
    protected function findUser(?string $customerId, array $metadata = []): ?User
    {
        // First try to find by user_id in metadata (most reliable)
        if (isset($metadata['user_id'])) {
            return User::find($metadata['user_id']);
        }

        // Fallback to customer ID lookup
        if ($customerId) {
            return User::where('stripe_id', $customerId)->first();
        }

        return null;
    }
}
