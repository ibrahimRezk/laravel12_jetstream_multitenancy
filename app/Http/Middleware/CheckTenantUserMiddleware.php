<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckTenantUserMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {

        // if(auth()->user()->tenants()->where('id' , tenant('id'))->doesntExist()) // not working 
        // if(auth()->user()->tenants->where('id' , tenant('id'))->first()->doesntExist()) // working for only first one

        if (auth()->user()->tenants()->whereKey(tenant('id'))->doesntExist()) {
            abort(403);
        }
        return $next($request);
    }
}
