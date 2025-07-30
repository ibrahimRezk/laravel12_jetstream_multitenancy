<?php

namespace App\Http\Controllers;

use App\Models\Plan;
use App\Models\User;
use Inertia\Inertia;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function __invoke()
    {

 

        if (auth()->user()->main_site_admin == true) {
            return Inertia::render('AdminDashboard');
        } else {
            dd(tenant('id'));
            return Inertia::render('TenantDashboard');
        }
    }


        public function findPlan(array $array, string $searchKey)
{
    foreach ($array as $key => $value) {
        // Check if the current key matches the search key
        if ($key === $searchKey) {
            return $value; // Return the value associated with the found key
        }

        // If the current value is an array, recursively search within it
        if (is_array($value)) {
            $found = $this->findPlan($value, $searchKey);
            if ($found !== null) { // If the key was found in a nested array
                return $found; // Return the found value
            }
        }
    }

    return null; // Return null if the key is not found anywhere
}

}
