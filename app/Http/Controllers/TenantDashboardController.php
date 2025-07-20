<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class TenantDashboardController extends Controller
{
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
