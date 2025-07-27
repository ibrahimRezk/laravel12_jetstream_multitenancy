<?php
namespace App\Console\Commands;

use App\Jobs\ProcessSubscriptionRenewal;
use App\Models\TenantSubscription;
use Carbon\Carbon;
use Illuminate\Console\Command;

class ProcessDueRenewals extends Command
{
    protected $signature = 'subscriptions:process-renewals {--dry-run : Show what would be renewed without actually processing}';
    protected $description = 'Process subscription renewals that are due';

    public function handle()
    {
        $isDryRun = $this->option('dry-run');
        
        // Find subscriptions that are due for renewal (expire in next 3 days)
        $dueSubscriptions = TenantSubscription::with(['tenant', 'Plan'])
            ->where('status', 'active')
            ->where('ends_at', '<=', Carbon::now()->addDays(3))
            ->where('ends_at', '>', Carbon::now())
            ->get();

        if ($dueSubscriptions->isEmpty()) {
            $this->info('No subscriptions due for renewal.');
            return 0;
        }

        $this->info("Found {$dueSubscriptions->count()} subscriptions due for renewal:");

        foreach ($dueSubscriptions as $subscription) {
            $tenantId = $subscription->tenant_id;
            $planName = $subscription->Plan->name;
            $expiryDate = $subscription->ends_at->format('Y-m-d H:i:s');
            
            $this->line("- Tenant: {$tenantId}, Plan: {$planName}, Expires: {$expiryDate}");

            if (!$isDryRun) {
                // Dispatch renewal job
                ProcessSubscriptionRenewal::dispatch($subscription->id);
                $this->info('  → Renewal job dispatched');
            }
        }

        if ($isDryRun) {
            $this->warn('This was a dry run. Use without --dry-run to actually process renewals.');
        } else {
            $this->info('All renewal jobs have been dispatched to the queue.');
        }

        return 0;
    }
}