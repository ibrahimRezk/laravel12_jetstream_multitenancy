<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class TenantDashboardController extends Controller
{

    public function addUser()
    {
        
             $user =    User::create([
                'name' => 'kamil',
                'email' => 'kamil@hotmail.com',
                'password' => Hash::make('55555sssss'),
            ]);


            $user->tenants()->attach(tenant('id')); /// very important line to attatch users with there tenants and we control access to only this tenant  from CheckTenantUserMiddleware 

            
    }
        public function index(Request $request)
    {
        $tenant = tenant();
        $subscription = $tenant->currentSubscription();
        
        return view('tenant.dashboard', compact('tenant', 'subscription'));
    }
    
    public function subscription(Request $request)
    {
        $tenant = tenant();
        $subscription = $tenant->currentSubscription();
        
        return view('tenant.subscription', compact('tenant', 'subscription'));
    }
}
