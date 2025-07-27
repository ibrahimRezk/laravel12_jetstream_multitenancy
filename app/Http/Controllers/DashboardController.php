<?php

namespace App\Http\Controllers;

use Inertia\Inertia;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function __invoke()
    {
        dd('id');
        if (auth()->user()->main_site_admin == true) {
            return Inertia::render('AdminDashboard');
        } else {
            dd(tenant('id'));
            return Inertia::render('TenantDashboard');
        }
    }
}
