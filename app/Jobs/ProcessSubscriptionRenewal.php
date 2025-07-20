<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use App\Models\TenantSubscription;
use App\Services\PurchasePlanService;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ProcessSubscriptionRenewal implements ShouldQueue
{
    use Queueable;
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
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
    public function handle(PurchasePlanService $service)
    {
        try {
            $subscription = TenantSubscription::findOrFail($this->subscriptionId);
            
            // Here you would typically integrate with payment processor
            // For now, we'll just renew the subscription
            $service->renewSubscription($subscription);
            
            Log::info('Subscription renewal processed', [
                'subscription_id' => $this->subscriptionId
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to process subscription renewal', [
                'subscription_id' => $this->subscriptionId,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }
}
