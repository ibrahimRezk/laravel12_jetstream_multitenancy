<?php 
namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Tenant;
use App\Models\TenantSubscription;
use App\Mail\SubscriptionRenewalReminder;
use Illuminate\Support\Facades\Mail;
use Carbon\Carbon;

class SendSubscriptionRenewalReminders extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'subscriptions:send-renewal-reminders 
                            {--tenant= : Process specific tenant ID}
                            {--days=7 : Days before expiration to send reminder}
                            {--dry-run : Run without actually sending emails}';

    /**
     * The console command description.
     */
    protected $description = 'Send renewal reminders for expiring subscriptions across all tenants';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $daysBeforeExpiration = $this->option('days');
        $specificTenant = $this->option('tenant');
        $dryRun = $this->option('dry-run');
        
        $this->info("Starting subscription renewal reminder process...");
        
        if ($dryRun) {
            $this->warn("DRY RUN MODE - No emails will be sent");
        }

        // Get tenants to process
        $tenants = $specificTenant 
            ? Tenant::where('id', $specificTenant)->get()
            : Tenant::where('status', 'active')->get();

        if ($tenants->isEmpty()) {
            $this->error('No tenants found to process');
            return Command::FAILURE;
        }

        $totalReminders = 0;
        $totalTenants = $tenants->count();

        $progressBar = $this->output->createProgressBar($totalTenants);
        $progressBar->start();

        foreach ($tenants as $tenant) {
            try {
                $remindersSent = $this->processTenantSubscriptions(
                    $tenant, 
                    $daysBeforeExpiration, 
                    $dryRun
                );
                
                $totalReminders += $remindersSent;
                
                $this->newLine();
                $this->line("Tenant [{$tenant->id}] {$tenant->name}: {$remindersSent} reminders processed");
                
            } catch (\Exception $e) {
                $this->newLine();
                $this->error("Error processing tenant [{$tenant->id}]: " . $e->getMessage());
                continue;
            }
            
            $progressBar->advance();
        }

        $progressBar->finish();
        $this->newLine(2);
        
        $action = $dryRun ? 'would be sent' : 'sent';
        $this->info("Process completed: {$totalReminders} renewal reminders {$action} across {$totalTenants} tenants");

        return Command::SUCCESS;
    }

    /**
     * Process subscriptions for a specific tenant
     */
    private function processTenantSubscriptions(Tenant $tenant, int $daysBeforeExpiration, bool $dryRun): int
    {
        // Switch to tenant context (adjust based on your multi-tenancy package)
        $this->switchToTenant($tenant);
        
        $reminderDate = Carbon::now()->addDays($daysBeforeExpiration)->startOfDay();
        
        // Find subscriptions expiring on the target date that haven't received reminders
        $subscriptions = TenantSubscription::with(['tenant' ,'tenant.owner', 'plan'])
            ->whereDate('expires_at', $reminderDate)
            ->where('status', 'active')
            ->whereNull('renewal_reminder_sent_at')
            ->get();

        $remindersSent = 0;

        foreach ($subscriptions as $subscription) {
            try {
                if (!$dryRun) {
                    // Send the renewal reminder email
                    Mail::to($subscription->tenant->owner->email)
                        ->send(new SubscriptionRenewalReminder($subscription));
                    
                    // Mark as reminder sent
                    $subscription->update([
                        'renewal_reminder_sent_at' => Carbon::now()
                    ]);
                }
                
                $remindersSent++;
                
            } catch (\Exception $e) {
                $this->error("Failed to send reminder for subscription [{$subscription->id}]: " . $e->getMessage());
                continue;
            }
        }

        return $remindersSent;
    }

    /**
     * Switch to tenant context - adjust based on your multi-tenancy implementation
     */
    private function switchToTenant(Tenant $tenant): void
    {
 
  
        // Example for Stancl Tenancy
        if (class_exists(\Stancl\Tenancy\Facades\Tenancy::class)) {
            \Stancl\Tenancy\Facades\Tenancy::initialize($tenant);
        }
        
        // Or set database connection manually
        // config(['database.connections.tenant.database' => $tenant->database]);
        // DB::setDefaultConnection('tenant');
    }
}