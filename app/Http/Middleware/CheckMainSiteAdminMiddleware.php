<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckMainSiteAdminMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (auth()->user()->main_site_admin == false) {
            auth()->guard('web')->logout();

            // tobe added after make toast work  and remove abort 
            // return back()->with('error' , 'check your url for your tenant site');  
            // return redirect('/login');

            abort(403);
        }
        return $next($request);
    }
}
