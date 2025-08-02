<?php

namespace App\Http\Controllers;

use App\Models\User;
use Inertia\Inertia;
use App\Models\Tenant;
use Illuminate\Http\Request;
use App\Services\PlanService;
use Illuminate\Support\Facades\Hash;

class TenantController extends Controller
{

    protected $planService;

    public function __construct(PlanService $planService)
    {
        $this->planService = $planService;
    }

    /**
     * Get all available purchase plans
     */
    public function index(Request $request)
    {
        try {
            $plans = $this->planService->getAvailablePlans();
            return Inertia::render('Plans', [
                'type' => $request->type,   /// tyes  select for first time , or change plan
                'tenantId' => auth()->user()->tenants[0]?->id ?? null,
                'plans' => $plans->map(function ($plan) {
                    return [
                        'id' => $plan->id,
                        'name' => $plan->name,
                        'description' => $plan->description,
                        'price_id_on_stripe' => $plan->price_id_on_stripe,
                        'product_id_on_stripe' => $plan->product_id_on_stripe,
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


    public function addUser()
    {
        $time = time();
        $user = User::create([
            'name' => $time . 'kamil',
            'email' => $time . 'kamil@hotmail.com',
            'password' => Hash::make('55555sssss'),
        ]);


        $user->tenants()->attach(auth()->user()->tenants[0]?->id); /// very important line to attatch users with there tenants and we control access to only this tenant  from CheckTenantUserMiddleware 


    }




    //     public function index(Request $request)
    // {


    //     $tenant = tenant();
    //     $subscription = $tenant->subscription();

    //     return view('tenant.dashboard', compact('tenant', 'subscription'));
    // }

    // public function subscription(Request $request)
    // {
    //     $tenant = tenant();
    //     $subscription = $tenant->subscription();

    //     return view('tenant.subscription', compact('tenant', 'subscription'));
    // }
}
