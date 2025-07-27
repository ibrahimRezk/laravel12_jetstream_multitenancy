<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;
use App\Models\Tenant;
use App\Models\Subscription;
use App\Models\Payment;
use App\Services\PaymentGatewayService;
use Stancl\Tenancy\Database\Models\Tenant as TenancyTenant;
use Stancl\Tenancy\Facades\Tenancy;
use Exception;

class WebhookController extends Controller
{
    protected $paymentGateway;

    public function __construct(PaymentGatewayService $paymentGateway)
    {
        $this->paymentGateway = $paymentGateway;
    }

    /**
     * Handle PayPal webhooks
     */
    public function handlePaypal(Request $request)
    {
        $payload = $request->all();
        $eventType = $payload['event_type'] ?? null;
        
        try {
            Log::info('PayPal webhook received', ['type' => $eventType]);
            
            switch ($eventType) {
                case 'BILLING.SUBSCRIPTION.ACTIVATED':
                    $this->handlePaypalSubscriptionActivated($payload);
                    break;
                    
                case 'BILLING.SUBSCRIPTION.CANCELLED':
                case 'BILLING.SUBSCRIPTION.SUSPENDED':
                    $this->handlePaypalSubscriptionCanceled($payload);
                    break;
                    
                case 'PAYMENT.SALE.COMPLETED':
                    $this->handlePaypalPaymentCompleted($payload);
                    break;
                    
                case 'PAYMENT.SALE.DENIED':
                case 'PAYMENT.SALE.REFUNDED':
                    $this->handlePaypalPaymentFailed($payload);
                    break;
                    
                case 'BILLING.SUBSCRIPTION.PAYMENT.FAILED':
                    $this->handlePaypalPaymentFailed($payload);
                    break;
                    
                default:
                    Log::info('Unhandled PayPal webhook event', ['type' => $eventType]);
            }
            
            return response()->json(['status' => 'success'], 200);
            
        } catch (Exception $e) {
            Log::error('PayPal webhook processing failed', [
                'error' => $e->getMessage(),
                'payload' => $payload
            ]);
            
            return response()->json(['error' => 'Webhook processing failed'], 400);
        }
    }

    /**
     * Handle Stripe webhooks
     */
    public function handleStripe(Request $request)
    {
        $payload = $request->getContent();
        $signature = $request->header('Stripe-Signature');
        
        try {
            // Verify webhook signature
            $event = $this->paymentGateway->constructEvent($payload, $signature);
            
            Log::info('Stripe webhook received', ['type' => $event->type]);
            
            switch ($event->type) {
                case 'invoice.payment_succeeded':
                    $this->handlePaymentSucceeded($event->data->object);
                    break;
                    
                case 'invoice.payment_failed':
                    $this->handlePaymentFailed($event->data->object);
                    break;
                    
                case 'customer.subscription.created':
                case 'customer.subscription.updated':
                    $this->handleSubscriptionUpdated($event->data->object);
                    break;
                    
                case 'customer.subscription.deleted':
                    $this->handleSubscriptionCanceled($event->data->object);
                    break;
                    
                case 'invoice.upcoming':
                    $this->handleUpcomingInvoice($event->data->object);
                    break;
                    
                default:
                    Log::info('Unhandled webhook event', ['type' => $event->type]);
            }
            
            return response()->json(['status' => 'success'], 200);
            
        } catch (Exception $e) {
            Log::error('Webhook processing failed', [
                'error' => $e->getMessage(),
                'payload' => $payload
            ]);
            
            return response()->json(['error' => 'Webhook processing failed'], 400);
        }
    }

    /**
     * Handle successful payment
     */
    protected function handlePaymentSucceeded($invoice)
    {
        $customerId = $invoice->customer;
        $subscriptionId = $invoice->subscription;
        
        // Find the tenant by customer ID (this happens on central database)
        $tenant = TenancyTenant::where('stripe_customer_id', $customerId)->first();
        
        if (!$tenant) {
            Log::error('Tenant not found for customer', ['customer_id' => $customerId]);
            return;
        }
        
        // Initialize tenancy for this specific tenant
        tenancy()->initialize($tenant);
        
        // Now we're in the tenant's database context
        // Find or create subscription record
        $subscription = Subscription::firstOrCreate(
            ['stripe_subscription_id' => $subscriptionId],
            [
                'stripe_customer_id' => $customerId,
                'status' => 'active',
                'current_period_start' => now(),
                'current_period_end' => now()->addMonth(),
            ]
        );
        
        // Create payment record in tenant database
        Payment::create([
            'subscription_id' => $subscription->id,
            'stripe_payment_intent_id' => $invoice->payment_intent,
            'amount' => $invoice->amount_paid,
            'currency' => $invoice->currency,
            'status' => 'succeeded',
            'paid_at' => now(),
        ]);
        
        // Update subscription status and dates
        $subscription->update([
            'status' => 'active',
            'current_period_start' => \Carbon\Carbon::createFromTimestamp($invoice->period_start),
            'current_period_end' => \Carbon\Carbon::createFromTimestamp($invoice->period_end),
            'last_payment_at' => now(),
        ]);
        
        // Update tenant status in central database
        tenancy()->end(); // End tenant context
        
        $tenant->update([
            'status' => 'active',
            'suspended_at' => null,
        ]);
        
        // Activate tenant services
        $this->activateTenantServices($tenant);
        
        Log::info('Payment succeeded processed', [
            'tenant_id' => $tenant->id,
            'subscription_id' => $subscription->id,
            'amount' => $invoice->amount_paid
        ]);
    }

    /**
     * Handle failed payment
     */
    protected function handlePaymentFailed($invoice)
    {
        $customerId = $invoice->customer;
        $tenant = TenancyTenant::where('stripe_customer_id', $customerId)->first();
        
        if (!$tenant) {
            Log::error('Tenant not found for failed payment', ['customer_id' => $customerId]);
            return;
        }
        
        tenancy()->initialize($tenant);
        
        $subscription = Subscription::where('stripe_subscription_id', $invoice->subscription)->first();
        
        if ($subscription) {
            // Create failed payment record
            Payment::create([
                'subscription_id' => $subscription->id,
                'stripe_payment_intent_id' => $invoice->payment_intent,
                'amount' => $invoice->amount_due,
                'currency' => $invoice->currency,
                'status' => 'failed',
                'failed_at' => now(),
            ]);
            
            // Update subscription status
            $subscription->update(['status' => 'past_due']);
            
            // Send notification to tenant
            $this->notifyPaymentFailed($tenant, $subscription);
        }
        
        tenancy()->end();
        
        Log::info('Payment failed processed', [
            'tenant_id' => $tenant->id,
            'amount' => $invoice->amount_due
        ]);
    }

    /**
     * Handle subscription updates
     */
    protected function handleSubscriptionUpdated($stripeSubscription)
    {
        $customerId = $stripeSubscription->customer;
        $tenant = TenancyTenant::where('stripe_customer_id', $customerId)->first();
        
        if (!$tenant) {
            Log::error('Tenant not found for subscription update', ['customer_id' => $customerId]);
            return;
        }
        
        tenancy()->initialize($tenant);
        
        $subscription = Subscription::where('stripe_subscription_id', $stripeSubscription->id)->first();
        
        if ($subscription) {
            $subscription->update([
                'status' => $stripeSubscription->status,
                'current_period_start' => \Carbon\Carbon::createFromTimestamp($stripeSubscription->current_period_start),
                'current_period_end' => \Carbon\Carbon::createFromTimestamp($stripeSubscription->current_period_end),
                'quantity' => $stripeSubscription->items->data[0]->quantity ?? 1,
                'stripe_price_id' => $stripeSubscription->items->data[0]->price->id ?? null,
            ]);
            
            // Update tenant limits based on subscription
            $this->updateTenantLimits($tenant, $subscription);
        }
        
        tenancy()->end();
        
        Log::info('Subscription updated', [
            'tenant_id' => $tenant->id,
            'status' => $stripeSubscription->status
        ]);
    }

    /**
     * Handle subscription cancellation
     */
    protected function handleSubscriptionCanceled($stripeSubscription)
    {
        $customerId = $stripeSubscription->customer;
        $tenant = TenancyTenant::where('stripe_customer_id', $customerId)->first();
        
        if (!$tenant) {
            Log::error('Tenant not found for subscription cancellation', ['customer_id' => $customerId]);
            return;
        }
        
        tenancy()->initialize($tenant);
        
        $subscription = Subscription::where('stripe_subscription_id', $stripeSubscription->id)->first();
        
        if ($subscription) {
            $subscription->update([
                'status' => 'canceled',
                'canceled_at' => now(),
            ]);
        }
        
        tenancy()->end();
        
        // Suspend tenant services
        $this->suspendTenantServices($tenant);
        
        Log::info('Subscription canceled', [
            'tenant_id' => $tenant->id,
            'subscription_id' => $subscription->id ?? null
        ]);
    }

    /**
     * Handle upcoming invoice (for dunning management)
     */
    protected function handleUpcomingInvoice($invoice)
    {
        $customerId = $invoice->customer;
        $tenant = TenancyTenant::where('stripe_customer_id', $customerId)->first();
        
        if ($tenant) {
            // Send upcoming payment notification
            $this->notifyUpcomingPayment($tenant, $invoice);
        }
    }

    /**
     * Set tenant context for multi-tenancy (deprecated - using tenancy() facade instead)
     */
    protected function setTenantContext($tenant)
    {
        // This method is kept for backwards compatibility
        // but we now use tenancy()->initialize($tenant) directly
        tenancy()->initialize($tenant);
    }

    /**
     * Activate tenant services after successful payment
     */
    protected function activateTenantServices(Tenant $tenant)
    {
        $tenant->update([
            'status' => 'active',
            'suspended_at' => null,
        ]);
        
        // Re-enable features, remove limitations, etc.
        $this->enableTenantFeatures($tenant);
    }

    /**
     * Suspend tenant services after cancellation
     */
    protected function suspendTenantServices(Tenant $tenant)
    {
        $tenant->update([
            'status' => 'suspended',
            'suspended_at' => now(),
        ]);
        
        // Disable premium features, apply limitations, etc.
        $this->disableTenantFeatures($tenant);
    }

    /**
     * Update tenant limits based on subscription
     */
    protected function updateTenantLimits($tenant, Subscription $subscription)
    {
        // Get plan limits based on subscription
        $planLimits = $this->getPlanLimits($subscription->stripe_price_id);
        
        // Update tenant in central database (end tenancy context first)
        tenancy()->end();
        
        $tenant->update([
            'max_users' => $planLimits['max_users'],
            'max_storage' => $planLimits['max_storage'],
            'features' => $planLimits['features'],
        ]);
    }

    /**
     * Get plan limits based on price ID
     */
    protected function getPlanLimits($priceId)
    {
        $plans = [
            'price_basic' => [
                'max_users' => 5,
                'max_storage' => 1024, // MB
                'features' => ['basic_feature_1', 'basic_feature_2']
            ],
            'price_pro' => [
                'max_users' => 25,
                'max_storage' => 10240, // MB
                'features' => ['basic_feature_1', 'basic_feature_2', 'pro_feature_1']
            ],
            'price_enterprise' => [
                'max_users' => -1, // unlimited
                'max_storage' => -1, // unlimited
                'features' => ['basic_feature_1', 'basic_feature_2', 'pro_feature_1', 'enterprise_feature_1']
            ]
        ];
        
        return $plans[$priceId] ?? $plans['price_basic'];
    }

    /**
     * Enable tenant features
     */
    protected function enableTenantFeatures(Tenant $tenant)
    {
        // Implementation depends on your feature flagging system
        // Could update a features JSON column, toggle feature flags, etc.
    }

    /**
     * Disable tenant features
     */
    protected function disableTenantFeatures(Tenant $tenant)
    {
        // Disable premium features, show upgrade prompts, etc.
    }

    /**
     * Send payment failure notification
     */
    protected function notifyPaymentFailed(Tenant $tenant, Subscription $subscription)
    {
        // Send email, in-app notification, etc.
        // Mail::to($tenant->email)->send(new PaymentFailedMail($tenant, $subscription));
    }

    /**
     * Send upcoming payment notification
     */
    protected function notifyUpcomingPayment(Tenant $tenant, $invoice)
    {
        // Send reminder email about upcoming payment
        // Mail::to($tenant->email)->send(new UpcomingPaymentMail($tenant, $invoice));
    }

    // =============================================
    // PayPal Webhook Handlers
    // =============================================

    /**
     * Handle PayPal subscription activation
     */
    protected function handlePaypalSubscriptionActivated($payload)
    {
        $resource = $payload['resource'];
        $subscriptionId = $resource['id'];
        $customId = $resource['custom_id'] ?? null; // Should contain tenant ID
        
        if (!$customId) {
            Log::error('PayPal subscription activated without custom_id', ['subscription_id' => $subscriptionId]);
            return;
        }
        
        $tenant = TenancyTenant::find($customId);
        if (!$tenant) {
            Log::error('Tenant not found for PayPal subscription', ['tenant_id' => $customId]);
            return;
        }
        
        tenancy()->initialize($tenant);
        
        // Create or update subscription in tenant database
        $subscription = Subscription::updateOrCreate(
            ['paypal_subscription_id' => $subscriptionId],
            [
                'paypal_subscription_id' => $subscriptionId,
                'status' => 'active',
                'current_period_start' => now(),
                'current_period_end' => now()->addMonth(), // Adjust based on billing cycle
            ]
        );
        
        tenancy()->end();
        
        // Update tenant with PayPal customer info in central database
        $tenant->update([
            'paypal_subscription_id' => $subscriptionId,
            'status' => 'active',
        ]);
        
        $this->activateTenantServices($tenant);
        
        Log::info('PayPal subscription activated', [
            'tenant_id' => $tenant->id,
            'subscription_id' => $subscriptionId
        ]);
    }

    /**
     * Handle PayPal subscription cancellation
     */
    protected function handlePaypalSubscriptionCanceled($payload)
    {
        $resource = $payload['resource'];
        $subscriptionId = $resource['id'];
        
        // Find tenant by PayPal subscription ID in central database
        $tenant = TenancyTenant::where('paypal_subscription_id', $subscriptionId)->first();
        
        if (!$tenant) {
            Log::error('Tenant not found for PayPal subscription cancellation', ['subscription_id' => $subscriptionId]);
            return;
        }
        
        tenancy()->initialize($tenant);
        
        $subscription = Subscription::where('paypal_subscription_id', $subscriptionId)->first();
        
        if ($subscription) {
            $subscription->update([
                'status' => 'canceled',
                'canceled_at' => now(),
            ]);
        }
        
        tenancy()->end();
        
        $this->suspendTenantServices($tenant);
        
        Log::info('PayPal subscription canceled', [
            'tenant_id' => $tenant->id,
            'subscription_id' => $subscriptionId
        ]);
    }

    /**
     * Handle PayPal payment completion
     */
    protected function handlePaypalPaymentCompleted($payload)
    {
        $resource = $payload['resource'];
        $saleId = $resource['id'];
        $amount = $resource['amount']['total'];
        $currency = $resource['amount']['currency'];
        
        // Extract subscription info from billing agreement or custom field
        $billingAgreementId = $resource['billing_agreement_id'] ?? null;
        $customId = $resource['custom'] ?? null;
        
        $tenant = null;
        $subscription = null;
        
        if ($billingAgreementId) {
            $subscription = Subscription::where('paypal_subscription_id', $billingAgreementId)->first();
            $tenant = $subscription?->tenant;
        } elseif ($customId) {
            $tenant = Tenant::find($customId);
            $subscription = $tenant?->activeSubscription();
        }
        
        if (!$tenant) {
            Log::error('Tenant not found for PayPal payment', [
                'sale_id' => $saleId,
                'billing_agreement_id' => $billingAgreementId,
                'custom_id' => $customId
            ]);
            return;
        }
        
        $this->setTenantContext($tenant);
        
        // Create payment record
        Payment::create([
            'tenant_id' => $tenant->id,
            'subscription_id' => $subscription?->id,
            'paypal_sale_id' => $saleId,
            'amount' => (int) ($amount * 100), // Convert to cents
            'currency' => strtolower($currency),
            'status' => 'succeeded',
            'paid_at' => now(),
        ]);
        
        // Update subscription if exists
        if ($subscription) {
            $subscription->update([
                'status' => 'active',
                'last_payment_at' => now(),
            ]);
        }
        
        $this->activateTenantServices($tenant);
        
        Log::info('PayPal payment completed', [
            'tenant_id' => $tenant->id,
            'sale_id' => $saleId,
            'amount' => $amount
        ]);
    }

    /**
     * Handle PayPal payment failure
     */
    protected function handlePaypalPaymentFailed($payload)
    {
        $resource = $payload['resource'];
        $eventType = $payload['event_type'];
        
        $tenant = null;
        $subscription = null;
        $amount = 0;
        $currency = 'usd';
        
        // Handle different failure event types
        if ($eventType === 'BILLING.SUBSCRIPTION.PAYMENT.FAILED') {
            $subscriptionId = $resource['id'];
            $subscription = Subscription::where('paypal_subscription_id', $subscriptionId)->first();
            $tenant = $subscription?->tenant;
            
            // Extract amount from last_failed_payment if available
            $lastFailedPayment = $resource['last_failed_payment'] ?? null;
            if ($lastFailedPayment) {
                $amount = $lastFailedPayment['amount']['value'] ?? 0;
                $currency = strtolower($lastFailedPayment['amount']['currency_code'] ?? 'usd');
            }
        } else {
            // Handle PAYMENT.SALE.DENIED or PAYMENT.SALE.REFUNDED
            $saleId = $resource['id'];
            $amount = $resource['amount']['total'] ?? 0;
            $currency = strtolower($resource['amount']['currency'] ?? 'usd');
            
            $billingAgreementId = $resource['billing_agreement_id'] ?? null;
            if ($billingAgreementId) {
                $subscription = Subscription::where('paypal_subscription_id', $billingAgreementId)->first();
                $tenant = $subscription?->tenant;
            }
        }
        
        if (!$tenant) {
            Log::error('Tenant not found for PayPal payment failure', [
                'event_type' => $eventType,
                'resource_id' => $resource['id']
            ]);
            return;
        }
        
        $this->setTenantContext($tenant);
        
        // Create failed payment record
        Payment::create([
            'tenant_id' => $tenant->id,
            'subscription_id' => $subscription?->id,
            'paypal_sale_id' => $resource['id'],
            'amount' => (int) ($amount * 100), // Convert to cents
            'currency' => $currency,
            'status' => 'failed',
            'failure_reason' => $resource['reason_code'] ?? 'Payment failed',
            'failed_at' => now(),
        ]);
        
        // Update subscription status if applicable
        if ($subscription) {
            $subscription->update(['status' => 'past_due']);
            $this->notifyPaymentFailed($tenant, $subscription);
        }
        
        Log::info('PayPal payment failed', [
            'tenant_id' => $tenant->id,
            'event_type' => $eventType,
            'amount' => $amount
        ]);
    }

    
}