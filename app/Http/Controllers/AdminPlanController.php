<?php

namespace App\Http\Controllers;

use App\Http\Requests\PlanRequest;
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


class AdminPlanController extends Controller
{
        private string $routeResourceName = 'admin.plans';

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
            $popularPlan = $this->planService->getPopularPlan();
            return Inertia::render('AdminControlPlans', [
                'tenants' => auth()->user()->tenants, //added only to the admin  because if the tenant choose a plan we already know his id by tenant('id)
                'popularPlan' => $popularPlan ? [
                    'id' => $popularPlan->plan_id,
                    'occurrences' => $popularPlan->occurrences
                ] : null,
                
                'routeResourceName' => $this->routeResourceName,

                'plans' => $plans->map(function ($plan) {
                    return [
                        'id' => $plan->id,
                        'name' => $plan->name,
                        'description' => $plan->description,
                        'price' =>  (double)$plan->price *1 ,
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
    public function store(PlanRequest $request)
    {
        $plansCount = Plan::count();
        $modifiedFeatures = [];
        foreach ($request->features as $feature) {
            $feature = str_replace(' ', '_', $feature);
            $modifiedFeatures[] = $feature;
        }

        $data['name'] = $request->name;
        $data['description'] = $request->description;
        $data['price'] = $request->price;
        $data['currency'] = $request->currency;
        $data['interval'] = $request->interval;
        $data['features'] = $modifiedFeatures;
        $data['trial_days'] = $request->trial_days;
        $data['sort_order'] = $plansCount + 1; // Increment sort order based on existing plans

         Plan::create($data);
return back()->with('success', 'Plan created successfully.');

        
    }
    public function update(PlanRequest $request , Plan $plan)
    {

         $modifiedFeatures = [];
        foreach ($request->features as $feature) {
            $feature = str_replace(' ', '_', $feature);
            $modifiedFeatures[] = $feature;
        }

        $data['name'] = $request->name;
        $data['description'] = $request->description;
        $data['price'] = $request->price;
        $data['currency'] = $request->currency;
        $data['interval'] = $request->interval;
        $data['features'] = $modifiedFeatures;
        $data['trial_days'] = $request->trial_days;

         $plan->update($data);
return back()->with('success', 'Plan updated successfully.');
        // $feature = str_replace(' ', '_', $feature);
    }


    public function destroy( $ids)
    {
 $all_ids = explode(',', $ids);

        foreach ($all_ids as $id) {
            $plan = Plan::find($id);

            $plan->delete();
        }
        return redirect()->back()->with('success', 'item deleted successfully');
        
    }
}