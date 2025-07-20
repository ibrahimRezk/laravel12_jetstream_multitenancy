<?php 


namespace App\Services;

use Carbon\Carbon;
use App\Models\Tenant;
use App\Models\PurchasePlan;
use App\Models\TenantSubscription;
use Illuminate\Support\Facades\DB;
use App\Events\SubscriptionCreated;
use Illuminate\Support\Facades\Log;

class PurchasePlanService
{
    public function getAvailablePlans()
    {
        return PurchasePlan::where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('price')
            ->get();
    }

    public function subscribeTenant(Tenant $tenant, PurchasePlan $plan, $trialDays = null)
    {
        try {
            return DB::transaction(function () use ($tenant, $plan, $trialDays) {
                // Cancel existing subscription if any
                $this->cancelExistingSubscription($tenant);

                $trialDays = $trialDays ?? $plan->trial_days;
                $trialEndsAt = $trialDays > 0 ? Carbon::now()->addDays($trialDays) : null;

                $subscription = TenantSubscription::create([
                    'tenant_id' => $tenant->id,
                    'purchase_plan_id' => $plan->id,
                    'status' => 'active',
                    'trial_ends_at' => $trialEndsAt,
                    'ends_at' => $this->calculateEndDate($plan, $trialEndsAt),
                ]);

                Log::info('Tenant subscribed to plan', [
                    'tenant_id' => $tenant->id,
                    'plan_id' => $plan->id,
                    'subscription_id' => $subscription->id
                ]);



                event(new SubscriptionCreated($subscription));   ///// /////// important ////////////////




                return $subscription;
            });
        } catch (\Exception $e) {
            Log::error('Failed to subscribe tenant to plan', [
                'tenant_id' => $tenant->id,
                'plan_id' => $plan->id,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    public function cancelSubscription(Tenant $tenant)
    {
        try {
            return DB::transaction(function () use ($tenant) {
                $subscription = $tenant->currentSubscription();
                if ($subscription) {
                    $subscription->update([
                        'status' => 'cancelled',
                        'ends_at' => Carbon::now()
                    ]);
                    
                    Log::info('Tenant subscription cancelled', [
                        'tenant_id' => $tenant->id,
                        'subscription_id' => $subscription->id
                    ]);
                }
                return $subscription;
            });
        } catch (\Exception $e) {
            Log::error('Failed to cancel tenant subscription', [
                'tenant_id' => $tenant->id,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    public function renewSubscription(TenantSubscription $subscription)
    {
        try {
            return DB::transaction(function () use ($subscription) {
                $plan = $subscription->purchasePlan;
                $newEndDate = $this->calculateEndDate($plan, $subscription->ends_at);
                
                $subscription->update([
                    'ends_at' => $newEndDate,
                    'status' => 'active'
                ]);
                
                Log::info('Subscription renewed', [
                    'subscription_id' => $subscription->id,
                    'new_end_date' => $newEndDate
                ]);
                
                return $subscription;
            });
        } catch (\Exception $e) {
            Log::error('Failed to renew subscription', [
                'subscription_id' => $subscription->id,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    private function cancelExistingSubscription(Tenant $tenant)
    {
        TenantSubscription::where('tenant_id', $tenant->id)
            ->where('status', 'active')
            ->update([
                'status' => 'cancelled',
                'ends_at' => Carbon::now()
            ]);
    }

    private function calculateEndDate(PurchasePlan $plan, $trialEndsAt = null)
    {
        $startDate = $trialEndsAt ?? Carbon::now();
        
        switch ($plan->interval) {
            case 'monthly':
                return $startDate->addMonth();
            case 'yearly':
                return $startDate->addYear();
            case 'weekly':
                return $startDate->addWeek();
            case 'daily':
                return $startDate->addDay();
            default:
                return $startDate->addMonth();
        }
    }
}