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
use Laravel\Cashier\SubscriptionItem;

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


    /// for the first time subscription
    public function subscribeTenant(Tenant $tenant, Plan $plan, $trialDays = null) // this is for first time subscription and automatic renewal if payment is successful
    {

        try {
            return DB::transaction(function () use ($tenant, $plan, $trialDays) {

                // Cancel existing subscription if any
                $this->cancelExistingSubscription($tenant);

                /// no need on case of ugrade to change subscription-> type witch refer to subscription nam because we don't use it e /////////////////////////////////

                $tenantSubscriptionExists = TenantSubscription::where(['tenant_id' => $tenant->id, 'plan_id' => $plan->id])->latest()->first(); // means this is not first time so no trial period allowed

                $trialDays ?? $plan->trial_days;
                $trialEndsAt = $trialDays > 0 && $tenantSubscriptionExists == null ? Carbon::now()->addDays($trialDays) : null; // only apply for first period so check subscriptionExists

                $tenantSubscription = TenantSubscription::create([ 
                    'tenant_id' => $tenant->id,
                    'plan_id' => $plan->id,
                    'status' => 'inactive', ////// notice inactive for now   it will be active on payment succeded
                    'price' => $plan->price,
                    'trial_ends_at' => $trialEndsAt,
                    'ends_at' => $this->calculateEndDate($plan, $trialEndsAt),
                ]);



                
                $tenant->tenant_subscription_id = $tenantSubscription->id;
                $tenant->plan_id = $plan->id;
                $tenant->save();





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


    public function handlePaymentSucceded(Tenant $tenant, Plan $plan) // this is for first time subscription and automatic renewal if payment is successful
    {

        try {
            return DB::transaction(function () use ($tenant, $plan) {

    
                $tenantSubscription = TenantSubscription::where(['tenant_id' => $tenant->id, 'plan_id' => $plan->id])->latest()->first();
                
                $tenantSubscription->status = 'active';
                $tenantSubscription->save();
                
                $tenant->tenant_subscription_id = $tenantSubscription->id;
                $tenant->plan_id = $plan->id;
                $tenant->save();

            });
        } catch (\Exception $e) {
            Log::error('Failed to update  tenant subscription to plan', [
                'tenant_id' => $tenant->id,
                'plan_id' => $plan->id,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }
    public function updateTenantSubscription(Tenant $tenant , Plan $plan) // this is for first time subscription and automatic renewal if payment is successful
    {
        Log::info("update ......................... ");
        try {
            return DB::transaction(function () use ($tenant , $plan) {

                $this->cancelExistingSubscription($tenant);

                $tenantSubscription = TenantSubscription::updateOrCreate([  
                    'id' => $tenant->tenant_subscription_id,
                    'tenant_id' => $tenant->id,
                    'plan_id' => $plan->id,
                ],[            
                    'tenant_id' => $tenant->id,
                    'plan_id' => $plan->id,
                    'status' => 'inactive', //// check this one
                    'price' => $plan->price,
                    // 'trial_ends_at' => $trialEndsAt,
                    'ends_at' => $this->calculateEndDate($plan, null),
                ]);


                $tenant->tenant_subscription_id = $tenantSubscription->id;
                $tenant->plan_id = $plan->id;
                $tenant->save();



            });
        } catch (\Exception $e) {
            Log::error('Failed to update  tenant subscription to plan', [
                'tenant_id' => $tenant->id,
                'plan_id' => $plan->id,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }
    public function cancelSubscription(Tenant $tenant, $cancelType = 'at_the_end')  // to cancel here and on stripe
    {
        try {
            return DB::transaction(function () use ($tenant, $cancelType) {


                $user = $tenant->owner; // Assuming the tenant has an owner user
                if (!$user) {
                    throw new \Exception('Tenant owner not found');
                }



                $subscription = Subscription::where('user_id', $user->id)->where('stripe_status', 'active')->first();
                $subscriptionItem = SubscriptionItem::where('subscription_id', $subscription->id)->first();



                if ($subscriptionItem) {
                    $product = $subscriptionItem->stripe_product;
                    if ($cancelType == 'immediate') {
                        $user->subscription($product)->cancelNow(); // immediate cancel
                    } else {
                        $user->subscription($product)->cancel();  // cancel at the end of subscription and no renewal
                    }
                    ///////////////////////////////////////////////////////////////////////
                }



                $tenantSubscription = $tenant->currentSubscription();
                // $tenantSubscription = $tenant->subscription();
                if ($tenantSubscription) {
                    $tenantSubscription->update([
                        'status' => 'canceled',
                        'ends_at' => Carbon::now(),
                    ]);

                    Log::info('Tenant subscription canceled', [
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


    private function cancelExistingSubscription(Tenant $tenant)
    {
        TenantSubscription::where('tenant_id', $tenant->id)
            ->where('status', 'active')
            ->update([
                'status' => 'canceled',
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