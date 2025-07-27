<?php

namespace App\Jobs;

use App\Services\PaymentService;
use Exception;
use App\Services\PlanService;
use App\Models\TenantSubscription;
use Illuminate\Support\Facades\Log;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use App\Notifications\PaymentMethodRequired;
use App\Notifications\PaymentFailureNotification;
use App\Notifications\SubscriptionRenewalFailure;
use App\Notifications\SubscriptionRenewalSuccess;

class ProcessSubscriptionRenewal implements ShouldQueue
{
    use Queueable;
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    
    public $timeout = 120; // 2 minutes
    public $tries = 3;
    public $backoff = [60, 300, 900]; // Retry after 1min, 5min, 15min
    public $queue = 'subscriptions';


    protected $subscriptionId;



    /**
     * Create a new job instance.
     */
    public function __construct($subscriptionId)
    {
                $this->subscriptionId = $subscriptionId;

    }

    /**
     * Execute the job.
     */
    public function handle(PlanService $planService , PaymentService $paymentService)
    {
         try {
            $subscription = TenantSubscription::with(['tenant', 'plan'])
                ->findOrFail($this->subscriptionId);
            
            Log::info('Processing subscription renewal', [
                'subscription_id' => $this->subscriptionId,
                'tenant_id' => $subscription->tenant_id,
                'plan_name' => $subscription->plan->name
            ]);

            if ($subscription->status !== 'active') {
                Log::info('Skipping renewal for inactive subscription', [
                    'subscription_id' => $this->subscriptionId,
                    'status' => $subscription->status
                ]);
                return;
            }

            $tenant = $subscription->tenant;
            if (!$tenant->hasValidPaymentMethod()) {
                Log::warning('Tenant has no valid payment method', [
                    'tenant_id' => $tenant->id,
                    'subscription_id' => $this->subscriptionId
                ]);
                
                $this->sendPaymentMethodRequiredNotification($subscription);
                return;
            }

            $paymentResult = $paymentService->processRecurringPayment($tenant, $subscription->plan);

            if ($paymentResult->isSuccessful()) {
                $planService->renewSubscription($subscription);
                
                Log::info('Subscription renewed successfully', [
                    'subscription_id' => $this->subscriptionId,
                    'payment_id' => $paymentResult->getPaymentId()
                ]);

                $this->sendRenewalSuccessNotification($subscription);
                
            } else {
                Log::error('Payment failed for subscription renewal', [
                    'subscription_id' => $this->subscriptionId,
                    'error' => $paymentResult->getErrorMessage()
                ]);

                $this->handlePaymentFailure($subscription, $paymentResult);
            }

        } catch (Exception $e) {
            Log::error('Failed to process subscription renewal', [
                'subscription_id' => $this->subscriptionId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            throw $e;
        }
        


        // try {
        //     $subscription = TenantSubscription::findOrFail($this->subscriptionId);
            
        //     // Here you would typically integrate with payment processor
        //     // For now, we'll just renew the subscription
        //     $service->renewSubscription($subscription);
            
        //     Log::info('Subscription renewal processed', [
        //         'subscription_id' => $this->subscriptionId
        //     ]);
        // } catch (\Exception $e) {
        //     Log::error('Failed to process subscription renewal', [
        //         'subscription_id' => $this->subscriptionId,
        //         'error' => $e->getMessage()
        //     ]);
        //     throw $e;
        // }
    }

     public function failed(Exception $exception)
    {
        Log::error('Subscription renewal job failed permanently', [
            'subscription_id' => $this->subscriptionId,
            'error' => $exception->getMessage()
        ]);

        try {
            $subscription = TenantSubscription::find($this->subscriptionId);
            if ($subscription) {
                $subscription->update([
                    'status' => 'payment_failed',
                    'notes' => 'Renewal failed after ' . $this->tries . ' attempts: ' . $exception->getMessage()
                ]);

                $this->sendRenewalFailureNotification($subscription);
            }
        } catch (Exception $e) {
            Log::error('Failed to handle job failure', [
                'subscription_id' => $this->subscriptionId,
                'error' => $e->getMessage()
            ]);
        }
    }

    private function handlePaymentFailure($subscription, $paymentResult)
    {
        $gracePeriodDays = config('subscription.grace_period_days', 3);
        $newEndDate = $subscription->ends_at->addDays($gracePeriodDays);
        
        $subscription->update([
            'status' => 'payment_failed',
            'ends_at' => $newEndDate,
            'notes' => 'Payment failed: ' . $paymentResult->getErrorMessage()
        ]);

        $this->sendPaymentFailureNotification($subscription, $paymentResult->getErrorMessage());
        
        ProcessSubscriptionRenewal::dispatch($subscription->id)
            ->delay($newEndDate->subDay());
    }

    private function sendRenewalSuccessNotification($subscription)
    {
        $email = $subscription->tenant->notificationEmail();
        
        Notification::route('mail', $email)
            ->notify(new SubscriptionRenewalSuccess($subscription));
    }

    private function sendPaymentFailureNotification($subscription, $errorMessage = null)
    {
        $email = $subscription->tenant->notificationEmail();
        
        Notification::route('mail', $email)
            ->notify(new PaymentFailureNotification($subscription, $errorMessage));
    }

    private function sendRenewalFailureNotification($subscription)
    {
        $email = $subscription->tenant->notificationEmail();
        
        Notification::route('mail', $email)
            ->notify(new SubscriptionRenewalFailure($subscription));
    }

    private function sendPaymentMethodRequiredNotification($subscription)
    {
        $email = $subscription->tenant->notificationEmail();
        
        Notification::route('mail', $email)
            ->notify(new PaymentMethodRequired($subscription));
    }
}
