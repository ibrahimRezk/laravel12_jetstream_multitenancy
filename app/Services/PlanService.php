<?php


namespace App\Services;

use Carbon\Carbon;
use App\Models\Tenant;
use App\Models\Plan;
use App\Models\TenantSubscription;
use Illuminate\Support\Facades\DB;
use App\Events\SubscriptionCreated;
use Illuminate\Support\Facades\Log;
use Laravel\Cashier\Subscription;

class PlanService
{
    public function getAvailablePlans()
    {
        return Plan::where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('price')
            ->get();
    }

    public function getPopularPlan()
    {
        return TenantSubscription::select('plan_id', DB::raw('COUNT(plan_id) as occurrences'))
            ->where('status', 'active')
            ->where('ends_at', '>', Carbon::now())
            ->groupBy('plan_id')
            ->orderByDesc('occurrences')
            ->limit(1)
            ->first();
    }


    public function subscribeTenant(Tenant $tenant, Plan $plan, $trialDays = null) // this is for first time subscription and automatic renewal if payment is successful
    {

        try {
            return DB::transaction(function () use ($tenant, $plan, $trialDays) {

                // Cancel existing subscription if any
                $this->cancelExistingSubscription($tenant);

                $tenantSubscriptionExists = TenantSubscription::where(['tenant_id' => $tenant->id, 'plan_id' => $plan->id,])->latest()->first(); // means this is not first time so no trial period allowed

                $trialDays  ?? $plan->trial_days;
                $trialEndsAt = $trialDays > 0 && $tenantSubscriptionExists == null ? Carbon::now()->addDays($trialDays) : null; // only apply for first period so check subscriptionExists

                $tenantSubscription = TenantSubscription::updateOrCreate([  // important : if we use stripe subscription it will create or update existing subscription
                    'tenant_id' => $tenant->id,
                    'plan_id' => $plan->id,
                    'id' => $tenant->tenant_subscription_id,
                ], [
                    'tenant_id' => $tenant->id,
                    'plan_id' => $plan->id,
                    'status' => 'active',
                    'price' => $plan->price,
                    'trial_ends_at' => $trialEndsAt,
                    'ends_at' => $this->calculateEndDate($plan, $trialEndsAt),
                ]);



                // update tenant data // important to match tenant subscription on our site and stripe subscription
                $tenant->update([
                    'tenant_subscription_id' => $tenantSubscription->id, 
                    'plan_id' => $plan->id
                ]);


                // $subscription = TenantSubscription::create([
                //     'tenant_id' => $tenant->id,
                //     'plan_id' => $plan->id,
                //     'status' => 'active',
                //     'price' => $plan->price,
                //     'trial_ends_at' => $trialEndsAt,
                //     'ends_at' => $this->calculateEndDate($plan, $trialEndsAt),
                // ]);

                Log::info('Tenant subscribed to plan', [
                    'tenant_id' => $tenant->id,
                    'plan_id' => $plan->id,
                    'subscription_id' => $tenantSubscription->id
                ]);



                event(new SubscriptionCreated($tenantSubscription));   ///// /////// important ////////////////




                return $tenantSubscription;
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

    public function cancelSubscription(Tenant $tenant,  $cancelType = 'at_the_end')  // to cancel here and on stripe
    {
        try {
            return DB::transaction(function () use ($tenant, $cancelType) {


                $user = $tenant->owner; // Assuming the tenant has an owner user
                if (!$user) {
                    throw new \Exception('Tenant owner not found');
                }

                $subscription = Subscription::where('user_id', $user->id)->where('stripe_status', 'active')->first();

                if ($subscription) {
                    $type = $subscription->type;
                    if ($cancelType == 'immediate') {
                        $user->subscription($type)->cancelNow(); // immediate cancel
                    } else {
                        $user->subscription($type)->cancel();  // cancel at the end of subscription and no renewal
                    }
                    ///////////////////////////////////////////////////////////////////////
                }

                
                $tenantSubscription = $tenant->currentSubscription();
                // $tenantSubscription = $tenant->subscription();
                if ($tenantSubscription) {
                    $tenantSubscription->update([
                        'status' => 'cancelled',
                        'ends_at' => Carbon::now(),
                    ]);

                    Log::info('Tenant subscription cancelled', [
                        'tenant_id' => $tenant->id,
                        'subscription_id' => $tenantSubscription->id
                    ]);
                }
                return $tenantSubscription;
            });



        } catch (\Exception $e) {
            Log::error('Failed to cancel tenant subscription', [
                'tenant_id' => $tenant->id,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    public function renewSubscription(TenantSubscription $tenantSubscription)  // this is for manual renewal  // check not used yet
    {
        try {
            return DB::transaction(function () use ($tenantSubscription) {
                $plan = $tenantSubscription->plan;
                $newEndDate = $this->calculateEndDate($plan, $tenantSubscription->ends_at);

                $tenantSubscription->update([
                    'ends_at' => $newEndDate,
                    'status' => 'active'
                ]);

                Log::info('tenant subscription renewed', [
                    'tenant_subscription_id' => $tenantSubscription->id,
                    'new_end_date' => $newEndDate
                ]);

                return $tenantSubscription;
            });
        } catch (\Exception $e) {
            Log::error('Failed to renew subscription', [
                'tenant_subscription_id' => $tenantSubscription->id,
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

    private function calculateEndDate(Plan $plan, $trialEndsAt = null)
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