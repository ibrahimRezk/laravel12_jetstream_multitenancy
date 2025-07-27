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


class AdminTenantsController extends Controller
{
        private string $routeResourceName = 'admin.tenants';



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


}