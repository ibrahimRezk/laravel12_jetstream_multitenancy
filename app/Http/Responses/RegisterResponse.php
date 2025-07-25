<?php

namespace App\Http\Responses;

use Inertia\Inertia;
use App\Models\Tenant;
use Illuminate\Routing\Route;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Contracts\Support\Responsable;
use Laravel\Fortify\Contracts\RegisterResponse as RegisterResponseContract;

class RegisterResponse implements RegisterResponseContract
{
    public function toResponse($request)
    {

        $user = auth()->user();
        $tenant = Tenant::findOrFail($user->tenants()->latest()->first()->id);
        $domainData = $tenant->domains()->first();
        
        return Inertia::location('http://' . $domainData->domain  . '.' . config('tenancy.central_domains')[0] . ':8000' . '/dashboard');

    }
}