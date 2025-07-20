<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\TenantSubscription;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class CheckExpiredSubscriptions extends Command
{
    
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'subscriptions:check-expired';
    // protected $signature = 'app:check-expired-subscriptions';
    
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check and update expired subscriptions';
    // protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
         $this->info('Checking for expired subscriptions...');
        
        $expiredSubscriptions = TenantSubscription::where('status', 'active')
            ->where('ends_at', '<', Carbon::now())
            ->get();
        
        $count = 0;
        foreach ($expiredSubscriptions as $subscription) {
            $subscription->update(['status' => 'expired']);
            $count++;
            
            Log::info('Subscription expired', [
                'subscription_id' => $subscription->id,
                'tenant_id' => $subscription->tenant_id
            ]);
        }
        
        $this->info("Updated {$count} expired subscriptions");
        
        return 0;
    }
}
