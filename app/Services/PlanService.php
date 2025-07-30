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


    public function subscribeTenant(Tenant $tenant, Plan $plan, $trialDays = null)
    {

        try {
            return DB::transaction(function () use ($tenant, $plan, $trialDays) {

                // Cancel existing subscription if any
                $this->cancelExistingSubscription($tenant);

                $trialDays = $trialDays ?? $plan->trial_days;
                $trialEndsAt = $trialDays > 0 ? Carbon::now()->addDays($trialDays) : null;

                $subscription =  TenantSubscription::updateOrCreate([
                    'tenant_id' => $tenant->id,
                    'plan_id' => $plan->id,
                ],[
                    'tenant_id' => $tenant->id,
                    'plan_id' => $plan->id,
                    'status' => 'active',
                    'price' => $plan->price,
                    'trial_ends_at' => $trialEndsAt,
                    'ends_at' => $this->calculateEndDate($plan, $trialEndsAt ),
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

    public function cancelSubscription(Tenant $tenant , $cancelType = 'at_the_end')
    {
        try {
            return DB::transaction(function () use ($tenant, $cancelType) {


            $user = $tenant->owner; // Assuming the tenant has an owner user
            if (!$user) {
                throw new \Exception('Tenant owner not found');
            }
            
            $subscription = Subscription::where('user_id', $user->id)->where('stripe_status', 'active')->first();
            $type = $subscription->type;
            if ($cancelType == 'immediate') {
                $user->subscription($type)->cancelNow(); // immediate cancel
            } else {
                $user->subscription($type)->cancel();  // cancel at the end of subscription and no renewal
            }            
            ///////////////////////////////////////////////////////////////////////




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
                $plan = $subscription->plan;
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