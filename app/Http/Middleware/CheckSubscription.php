<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckSubscription
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next ,  $feature = null): Response
    {
         $tenant = tenant();
        
        if (!$tenant) {
            return redirect()->route('tenant.select');
        }

        if (!$tenant->hasActiveSubscription()) {
            return redirect()->route('subscription.expired');
        }

        if ($feature && !$tenant->canAccess($feature)) {
            return redirect()->route('subscription.upgrade');
        }

        return $next($request);
    }
    
}
