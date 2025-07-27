<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\TenantSubscription;
use Carbon\Carbon;
use Illuminate\Http\Request;

class RenewalDashboardController extends Controller
{
    public function index()
    {
        $stats = [
            'due_today' => TenantSubscription::where('status', 'active')
                ->whereDate('ends_at', Carbon::today())
                ->count(),
                
            'due_this_week' => TenantSubscription::where('status', 'active')
                ->whereBetween('ends_at', [Carbon::now(), Carbon::now()->addWeek()])
                ->count(),
                
            'failed_renewals' => TenantSubscription::where('status', 'payment_failed')
                ->whereDate('updated_at', '>=', Carbon::now()->subDays(7))
                ->count(),
                
            'pending_jobs' => \Illuminate\Support\Facades\Queue::size('subscriptions')
        ];

        $upcomingRenewals = TenantSubscription::with(['tenant', 'Plan'])
            ->where('status', 'active')
            ->whereBetween('ends_at', [Carbon::now(), Carbon::now()->addDays(7)])
            ->orderBy('ends_at')
            ->get();

        return view('admin.renewals.dashboard', compact('stats', 'upcomingRenewals'));
    }

    public function processRenewals(Request $request)
    {
        $subscriptionIds = $request->input('subscription_ids', []);
        
        foreach ($subscriptionIds as $id) {
            ProcessSubscriptionRenewal::dispatch($id);
        }

        return redirect()->back()->with('success', 'Renewal jobs queued successfully');
    }


     public function immediateRenewal($subscriptionId)
    {
        // Dispatch job to run immediately
        ProcessSubscriptionRenewal::dispatch($subscriptionId);
    }

    public function delayedRenewal($subscriptionId, $delayMinutes = 30)
    {
        // Dispatch job with delay
        ProcessSubscriptionRenewal::dispatch($subscriptionId)
            ->delay(now()->addMinutes($delayMinutes));
    }

    public function specificQueueRenewal($subscriptionId)
    {
        // Dispatch to specific queue
        ProcessSubscriptionRenewal::dispatch($subscriptionId)
            ->onQueue('high-priority');
    }

    public function batchRenewals(array $subscriptionIds)
    {
        // Dispatch multiple renewals with staggered delays
        foreach ($subscriptionIds as $index => $id) {
            ProcessSubscriptionRenewal::dispatch($id)
                ->delay(now()->addSeconds($index * 5)); // 5 second intervals
        }
    }
}