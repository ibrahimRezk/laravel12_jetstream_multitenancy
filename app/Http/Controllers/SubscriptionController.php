<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Resources\TenantSubscriptionResource;
use App\Models\Plan;
use App\Models\Tenant;
use App\Services\PlanService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Inertia\Inertia;

class SubscriptionController extends Controller
{
    protected $planService;

    public function __construct(PlanService $planService)
    {
        $this->planService = $planService;
    }


    ///////////////////////////////// this part for manual renewal of subscription////////////////////////////////////////////////////
        public function renewSubscription(Request $request, $subscriptionId)
    {
        try {
            $subscription = TenantSubscription::findOrFail($subscriptionId);
            
            // Dispatch the renewal job immediately
            ProcessSubscriptionRenewal::dispatch($subscriptionId);
            return back()->with('success'  , 'Renewal process started');
            

        } catch (\Exception $e) {
            return back()->with('error'  , $e->getMessage());
        }

    }

    public function bulkRenew(Request $request)
    {
        $subscriptionIds = $request->validate([
            'subscription_ids' => 'required|array',
            'subscription_ids.*' => 'exists:tenant_subscriptions,id'
        ])['subscription_ids'];

        foreach ($subscriptionIds as $id) {
            // Dispatch with delay to prevent overwhelming the queue
            ProcessSubscriptionRenewal::dispatch($id)->delay(now()->addSeconds(rand(1, 30)));
        }

        return response()->json([
            'success' => true,
            'message' => count($subscriptionIds) . ' subscription renewals queued'
        ]);
    }
    ///////////////////////////////// end of part for manual renewal of subscription////////////////////////////////////////////////////








    /**
     * Get all tenants
     */
    // public function getTenants()
    // {
    //     try {
    //         $tenants = Tenant::select('id', 'name')
    //             ->orderBy('name')
    //             ->get();
            
    //         return response()->json([
    //             'success' => true,
    //             'tenants' => $tenants->map(function ($tenant) {
    //                 return [
    //                     'id' => $tenant->id,
    //                     'name' => $tenant->name ?? $tenant->id
    //                 ];
    //             })
    //         ]);
    //     } catch (\Exception $e) {
    //         return response()->json([
    //             'success' => false,
    //             'message' => 'Failed to fetch tenants: ' . $e->getMessage()
    //         ], 500);
    //     }
    // }

    /**
     * SSubscripe tenant to a plan
     */
    public function subscripe( Plan $plan)
    {
        
        
        $tenant = Tenant::findOrFail(tenant('id'));
        // Check if tenant already has an active subscription
        if ($tenant->hasActiveSubscription()) {

            return back()->with('error', 'Tenant already has an active subscription');

        }
        try {
            
            $subscription = $this->planService->subscripeTenant($tenant, $plan);
            
            return back();
            // return to_route('dashboard')->with('success' , 'created successfully');
            
            // return response()->json([
            //     'success' => true,
            //     'message' => 'Successfully subscriped to ' . $plan->name,
            //     'subscription' => [
            //         'id' => $subscription->id,
            //         'tenant_id' => $subscription->tenant_id,
            //         'plan_name' => $plan->name,
            //         'status' => $subscription->status,
            //         'trial_ends_at' => $subscription->trial_ends_at,
            //         'ends_at' => $subscription->ends_at,
            //         'created_at' => $subscription->created_at
            //     ]
            // ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to subscripe: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get tenant's current subscription
     */
    public function getTenantSubscription(Request $request)
    {
        try {
            $tenant = Tenant::findOrFail(tenant('id'));
            $subscription = $tenant->currentSubscription();

            if($subscription == null)
            {
                return back()->with('error' , 'no subscription found for this tenant');
            }

            $subscription->load('plan');
            

            $subscription = new TenantSubscriptionResource($subscription);

            if (!$subscription) {
                return response()->json([
                    'success' => true,
                    'subscription' => null
                ]);
            }

// dd($subscription);

              return Inertia::render('Subscription' , [
                'subscription' => $subscription 
               
            ]);

            
            // return response()->json([
            //     'success' => true,
            //     'subscription' => [
            //         'id' => $subscription->id,
            //         'tenant_id' => $subscription->tenant_id,
            //         'plan' => [
            //             'id' => $subscription->plan->id,
            //             'name' => $subscription->plan->name,
            //             'description' => $subscription->plan->description,
            //             'price' => $subscription->plan->price,
            //             'currency' => $subscription->plan->currency,
            //             'interval' => $subscription->plan->interval,
            //             'features' => $subscription->plan->features
            //         ],
            //         'status' => $subscription->status,
            //         'trial_ends_at' => $subscription->trial_ends_at,
            //         'ends_at' => $subscription->ends_at,
            //         'is_active' => $subscription->isActive(),
            //         'on_trial' => $subscription->onTrial(),
            //         'created_at' => $subscription->created_at
            //     ]
            // ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch subscription: ' . $e->getMessage()
            ], 500);
        }
    }


        /**
     * Upgrade/downgrade tenant's subscription
     */
    public function changeSubscription(Request $request,  Plan $plan )
    {
        $tenant = Tenant::findOrFail(tenant('id'));
        // Cancel existing subscription
        try {
            $this->planService->cancelSubscription($tenant);
            
            // Create new subscription
            $subscription = $this->planService->subscripeTenant($tenant, $plan);
            
                        return to_route('dashboard')->with('success' , 'changed successfully');

            // return response()->json([
            //     'success' => true,
            //     'message' => 'Subscription changed successfully to ' . $plan->name,
            //     'subscription' => [
            //         'id' => $subscription->id,
            //         'tenant_id' => $subscription->tenant_id,
            //         'plan_name' => $plan->name,
            //         'status' => $subscription->status,
            //         'trial_ends_at' => $subscription->trial_ends_at,
            //         'ends_at' => $subscription->ends_at,
            //         'created_at' => $subscription->created_at
            //     ]
            // ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to change subscription: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Cancel tenant's subscription
     */
    public function cancelSubscription(Request $request)
    {
        try {
            $tenant = Tenant::findOrFail(tenant('id'));
            $subscription = $this->planService->cancelSubscription($tenant);


            
            if (!$subscription) {
                return response()->json([
                    'success' => false,
                    'message' => 'No active subscription found'
                ], 404);
            }

            return back()->with('success' , 'cancelled succcessfully');
            
            // return response()->json([
            //     'success' => true,
            //     'message' => 'Subscription cancelled successfully',
            //     'subscription' => [
            //         'id' => $subscription->id,
            //         'status' => $subscription->status,
            //         'cancelled_at' => $subscription->updated_at
            //     ]
            // ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to cancel subscription: ' . $e->getMessage()
            ], 500);
        }
    }



}