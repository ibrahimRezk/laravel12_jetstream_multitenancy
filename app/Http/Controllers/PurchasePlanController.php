<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Resources\TenantSubscriptionResource;
use App\Models\PurchasePlan;
use App\Models\Tenant;
use App\Services\PurchasePlanService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Inertia\Inertia;

class PurchasePlanController extends Controller
{
    protected $purchasePlanService;

    public function __construct(PurchasePlanService $purchasePlanService)
    {
        $this->purchasePlanService = $purchasePlanService;
    }

    /**
     * Get all available purchase plans
     */
    public function index(Request $request)
    {
        try {
            $plans = $this->purchasePlanService->getAvailablePlans();
            return Inertia::render('PurchasePlans' , [
                'type' => $request->type,   /// tyes  select for first time , or change plan
                'plans' => $plans->map(function ($plan) {
                    return [
                        'id' => $plan->id,
                        'name' => $plan->name,
                        'description' => $plan->description,
                        'price' => $plan->price,
                        'currency' => $plan->currency,
                        'interval' => $plan->interval,
                        'features' => $plan->features,
                        'trial_days' => $plan->trial_days,
                        'sort_order' => $plan->sort_order
                    ];
                })
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch plans: ' . $e->getMessage()
            ], 500);
        }
    }

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
     * Subscribe tenant to a plan
     */
    public function subscribe( PurchasePlan $plan)
    {
        
        
        $tenant = Tenant::findOrFail(tenant('id'));
        // Check if tenant already has an active subscription
        if ($tenant->hasActiveSubscription()) {

            return back()->with('error', 'Tenant already has an active subscription');

        }
        try {
            
            $subscription = $this->purchasePlanService->subscribeTenant($tenant, $plan);
            
            return to_route('dashboard')->with('success' , 'created successfully');
            
            // return response()->json([
            //     'success' => true,
            //     'message' => 'Successfully subscribed to ' . $plan->name,
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
                'message' => 'Failed to subscribe: ' . $e->getMessage()
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

            $subscription->load('purchasePlan');
            

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
            //             'id' => $subscription->purchasePlan->id,
            //             'name' => $subscription->purchasePlan->name,
            //             'description' => $subscription->purchasePlan->description,
            //             'price' => $subscription->purchasePlan->price,
            //             'currency' => $subscription->purchasePlan->currency,
            //             'interval' => $subscription->purchasePlan->interval,
            //             'features' => $subscription->purchasePlan->features
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
     * Cancel tenant's subscription
     */
    public function cancelSubscription(Request $request)
    {
        try {
            $tenant = Tenant::findOrFail(tenant('id'));
            $subscription = $this->purchasePlanService->cancelSubscription($tenant);
            
            if (!$subscription) {
                return response()->json([
                    'success' => false,
                    'message' => 'No active subscription found'
                ], 404);
            }
            
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

    /**
     * Upgrade/downgrade tenant's subscription
     */
    public function changeSubscription(Request $request,  PurchasePlan $plan)
    {
        $tenant = Tenant::findOrFail(tenant('id'));
        // Cancel existing subscription
        try {
            $this->purchasePlanService->cancelSubscription($tenant);
            
            // Create new subscription
            $subscription = $this->purchasePlanService->subscribeTenant($tenant, $plan);
            
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
}