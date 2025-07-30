<?php

namespace App\Http\Controllers;

use App\Models\Plan;
use App\Models\TenantSubscription;
use Inertia\Inertia;
use App\Models\Tenant;
use Illuminate\Http\Request;
use App\Services\PlanService;
use App\Services\PaymentService;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;
use App\Http\Resources\TenantSubscriptionResource;
use Laravel\Cashier\Subscription;

class TenantSubscriptionController extends Controller
{
    protected $planService;

    public function __construct(PlanService $planService)
    {
        $this->planService = $planService;
    }



    public function checkout(Request $request)
    {

        $tenant = auth()->user()->tenants[0];

        if (!$tenant) {
            return back()->with('error', 'Tenant not found');
        }

        if ($tenant->hasActiveSubscription()) {
            return back()->with('error', 'Tenant already has an active subscription');
        }

        try {

            return PaymentService::processRecurringPayment($request->plan);
            // after payment success there is webhook event comes from stripe  and stripewebhook listener  and in case of success it will complete subscription in the listener

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
            $tenant = auth()->user()->tenants[0];
            if (!$tenant) {
                return back()->with('error', 'Tenant not found');
            }
            // $tenant = Tenant::findOrFail(tenant('id'));
            $subscription = $tenant->currentSubscription();

            if ($subscription == null) {
                return back()->with('error', 'no subscription found for this tenant');
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

            return Inertia::render('Subscription', [
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


    public function tenantSubscriptionDetails()
    {
        return Tenant::findOrFail(auth()->user()->tenants[0]?->id)?->currentSubscription();
    }


    /**
     * Upgrade/downgrade tenant's subscription
     */
    public function changeSubscription(Request $request, Plan $plan)
    {
        $tenant = auth()->user()->tenants[0];
        if (!$tenant) {
            return back()->with('error', 'Tenant not found');
        }
        // $tenant = Tenant::findOrFail(tenant('id'));
        // Cancel existing subscription
        try {
            $this->planService->cancelSubscription($tenant);

            // Create new subscription
            $subscription = $this->planService->subscribeTenant($tenant, $plan);

            return to_route('dashboard')->with('success', 'changed successfully');

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



            $tenant = auth()->user()->tenants[0];
            if (!$tenant) {
                return back()->with('error', 'Tenant not found');
            }
            // $tenant = Tenant::findOrFail(tenant('id'));
            $subscription = $this->planService->cancelSubscription($tenant);



            if (!$subscription) {
                return response()->json([
                    'success' => false,
                    'message' => 'No active subscription found'
                ], 404);
            }

            return back()->with('success', 'cancelled succcessfully');

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
     * Ssubscribe tenant to a plan
     */

}