<?php

namespace App\Http\Responses;

use App\Models\Tenant;
use Laravel\Fortify\Fortify;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Laravel\Fortify\Contracts\LoginResponse as LoginResponseContract;

class LoginResponse implements LoginResponseContract
{

    /**
     * Create an HTTP response that represents the object.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function toResponse($request)
    {
        if (Auth::check() && Auth::user()->main_site_admin) { // super admin
            return $request->wantsJson()
                ? response()->json(['two_factor' => false])
                : redirect()->intended(Fortify::redirects('login'));
        } else { // tenant
return $request->wantsJson()
                ? response()->json(['two_factor' => false])
                : redirect()->intended(route('dashboard')); // redirect to tenant dashboard       
             // : redirect()->intended($this->redirect());
        }
    }

    // public function redirect()
    // {

    //     $user = auth()->user();
    //     $tenant = Tenant::findOrFail($user->tenants[0]->id);
    //     $domainData = $tenant->domains()->first();
    //     // dd($domainData->domain  . '/dashboard');
    //     return  $domainData->domain  . '.' . config('tenancy.central_domains')[0] . '/dashboard';

    //     // if (auth()->user()->hasRole('Admin')) {
    //     //     return 'admin/dashboard';
    //     // } else if (auth()->user()->hasRole('Teacher')) {
    //     //     return 'teacher/dashboard';
    //     // } else if (auth()->user()->hasRole('Student')) {
    //     //     return 'student/dashboard';
    //     // } else if (auth()->user()->hasRole('Parent')) {
    //     //     return 'parent/dashboard';
    //     // } else {
    //     //     return '/';
    //     // }
    // }
}
