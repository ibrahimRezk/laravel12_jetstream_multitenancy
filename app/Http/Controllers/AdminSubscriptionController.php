<?php

namespace App\Http\Controllers;

use App\Models\User;
use Inertia\Inertia;
use App\Models\Tenant;
use App\Models\Plan;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\TenantRequest;
use Illuminate\Support\Facades\Hash;
use App\Services\PlanService;
use App\Http\Resources\TenantResource;
use App\Http\Requests\AddTenantRequest;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Password;
use App\Http\Resources\PlanResource;
use App\Http\Resources\TenantSubscriptionResource;


class AdminSubscriptionController extends Controller
{
    private string $routeResourceName = 'admin.subscription';

    protected $planService;

    public function __construct(PlanService $planService)
    {
        $this->planService = $planService;
    }

    /**
     * Get all available purchase plans
     */
    public function index()
    {
        try {
            $plans = $this->planService->getAvailablePlans();
            return Inertia::render('AdminControlPlans', [
                'tenants' => auth()->user()->tenants, //added only to the admin  because if the tenant choose a plan we already know his id by tenant('id)
                'routeResourceName' => $this->routeResourceName,


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
    public function getTenants()
    {
        try {
            $tenants = Tenant::with(['subscription', 'subscription.plan', 'subscriptions', 'subscriptions.plan', 'owner:id,name,email', 'users:id,name,email'])
                ->orderBy('id')
                // ->get();
                // ->paginate(1);
                ->paginate(10)->onEachSide(2)->appends(request()->query());


            $plans = PlanResource::collection(Plan::get());

            // dd($tenants);
            return Inertia::render('AllTenants', [
                'tenants' => TenantResource::collection($tenants),
                'plans' => $plans,
                // 'type' => $plans 

            ]);

            // return response()->json([
            //     'success' => true,
            //     'tenants' => $tenants->map(function ($tenant) {
            //         return [
            //             'id' => $tenant->id,
            //             'name' => $tenant->name ?? $tenant->id
            //         ];
            //     })
            // ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch tenants: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Subscribe tenant to a plan
     */
    public function subscribe(TenantRequest $request)
    {
        try {
            $user = User::create([
                'name' => $request['name'],
                'email' => $request['email'],
                'password' => Hash::make($request['password']),
            ]);

            $tenant = Tenant::create(
                [
                    'tenancy_db_name' => $request['subdomain'],  // must add this part other wise duplicate name error will appear
                    'ownerId' => $user->id  // must add this part other wise duplicate name error will appear

                ]
            );

            $tenant->domains()->create([
                'domain' => $request['subdomain'] // // if you use initializebysubdomain middleware in tenant.php
            ]);

            $user->tenants()->attach($tenant->id);

            $plan = Plan::find($request->plan_id);

            $this->planService->subscribeTenant($tenant, $plan);

            return back()->with('success', 'created successfully');
















            // $validated = $request->validate([
            //     'tenant_id' => 'required|exists:tenants,id'
            // ]);

            // $tenant = Tenant::findOrFail($validated['tenant_id']);

            // // Check if tenant already has an active subscription
            // if ($tenant->hasActiveSubscription()) {
            //     return response()->json([
            //         'success' => false,
            //         'message' => 'Tenant already has an active subscription'
            //     ], 400);
            // }

            // $subscription = $this->planService->subscribeTenant($tenant, $plan);

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
    // public function getTenantSubscription(Request $request, $tenantId)
    // {
    //     try {
    //         $tenant = Tenant::findOrFail($tenantId);
    //         $subscription = $tenant->currentSubscription();


    //         if ($subscription == null) {
    //             return back()->with('error', 'no subscription found for this tenant');
    //         }

    //         $subscription->load('plan');


    //         $subscription = new TenantSubscriptionResource($subscription);

    //         if (!$subscription) {
    //             return response()->json([
    //                 'success' => true,
    //                 'subscription' => null
    //             ]);
    //         }

    //         // dd($subscription);
    //         return Inertia::render('Subscription', [ // check where to return with what it suppose to return to admin subscriptions
    //             'subscription' => $subscription

    //         ]);

    //         // if (!$subscription) {
    //         //     return response()->json([
    //         //         'success' => true,
    //         //         'subscription' => null
    //         //     ]);
    //         // }

    //         // return response()->json([
    //         //     'success' => true,
    //         //     'subscription' => [
    //         //         'id' => $subscription->id,
    //         //         'tenant_id' => $subscription->tenant_id,
    //         //         'plan' => [
    //         //             'id' => $subscription->plan->id,
    //         //             'name' => $subscription->plan->name,
    //         //             'description' => $subscription->plan->description,
    //         //             'price' => $subscription->plan->price,
    //         //             'currency' => $subscription->plan->currency,
    //         //             'interval' => $subscription->plan->interval,
    //         //             'features' => $subscription->plan->features
    //         //         ],
    //         //         'status' => $subscription->status,
    //         //         'trial_ends_at' => $subscription->trial_ends_at,
    //         //         'ends_at' => $subscription->ends_at,
    //         //         'is_active' => $subscription->isActive(),
    //         //         'on_trial' => $subscription->onTrial(),
    //         //         'created_at' => $subscription->created_at
    //         //     ]
    //         // ]);
    //     } catch (\Exception $e) {
    //         return response()->json([
    //             'success' => false,
    //             'message' => 'Failed to fetch subscription: ' . $e->getMessage()
    //         ], 500);
    //     }
    // }

    /**
     * Cancel tenant's subscription
     */
    public function cancelSubscription($tenantIds)
    {
        try {
            $all_ids = explode(',', $tenantIds);

            foreach ($all_ids as $id) {
                $tenant = Tenant::findOrFail($id);
                $subscription = $this->planService->cancelSubscription($tenant);

                if (!$subscription) {
                    continue;
                    // return response()->json([
                    //     'success' => false,
                    //     'message' => 'No active subscription found'
                    // ], 404);
                }
            }
            return redirect()->back()->with('success', 'canceled successfully');
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
    public function changeSubscription(TenantRequest $request, $tenantId, Plan $plan)
    {
        // dd($plan->id);

        $tenant = Tenant::findOrFail($tenantId);
        try {

            $user = User::find($tenant->ownerId);

            $data = $request->safe()->only(['email', 'name']);
            $request->password !== null ? $data['password'] = bcrypt($request->safe()->password) : '';
            $user->update($data);


            $tenant->tenancy_db_name = $request['subdomain']; /// db name update
            $tenant->save();


            $domainModel = $tenant->domains[0];
            $domainModel->domain = $request['subdomain'];  /// subdomain update
            $domainModel->save();


            // Cancel existing subscription
            $this->planService->cancelSubscription($tenant);

            // Create new subscription
            $this->planService->subscribeTenant($tenant, $plan);

            return back()->with('success', 'created successfully');




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