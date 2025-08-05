<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\WebhookController;
use App\Models\User;
use Inertia\Inertia;
use App\Models\TenantSubscription;
use Illuminate\Support\Facades\Route;
use Illuminate\Foundation\Application;
use App\Http\Controllers\PlanController;
use App\Http\Controllers\TenantController;
use App\Http\Controllers\AdminPlanController;
use App\Http\Controllers\AdminTenantsController;
use App\Http\Controllers\TenantSubscriptionController;
use App\Http\Controllers\AdminSubscriptionController;
use App\Http\Middleware\CheckMainSiteAdminMiddleware;
use Laravel\Cashier\Subscription;




// routes/web.php, api.php or any other central route files you have

foreach (config('tenancy.central_domains') as $domain) {
    Route::domain($domain)->group(function () {
        // your actual routes

        Route::get('/', function () {
            return Inertia::render('Welcome', [
                'canLogin' => Route::has('login'),
                'canRegister' => Route::has('register'),
                'laravelVersion' => Application::VERSION,
                'phpVersion' => PHP_VERSION,
            ]);
        });

        /// for main site admin only
        Route::middleware([
            'web',
            'auth:sanctum',
            config('jetstream.auth_session'),
            'verified',
        ])->group(function () {

            // Route::get('/dashboard', DashboardController::class)->name('dashboard')->middleware(CheckMainSiteAdminMiddleware::class);
            Route::get('/dashboard', function () {

                if (auth()->user()->main_site_admin == true) {
                    return Inertia::render('AdminDashboard');
                } else {
                    return Inertia::render('TenantDashboard');
                }
            })->name('dashboard');






            /////////////////////////////// admin part /////////////////////////////////////////////////////////////////////////////////////////////////
            // admin controle  Tenant subscriptions
            Route::get('/admin/tenants', [AdminTenantsController::class, 'index'])->name('admin.tenants');
            // Route::get('/admin/tenant/{tenantId}/subscription', [AdminTenantsController::class, 'getTenantSubscription'])->name('admin.getTenantSubscription');
            Route::post('/admin/tenant/subscribe', [AdminTenantsController::class, 'subscribe'])->name('admin.tenant.subscribe');
            Route::put('/admin/tenant/{tenantId}/subscription/{plan}', [AdminTenantsController::class, 'changeSubscription'])->name('admin.changeSubscription');
            Route::delete('/admin/tenant/{tenantIds}/subscription', [AdminTenantsController::class, 'cancelSubscription'])->name('admin.cancelSubscription');



            Route::get('/admin/purchase-plans', [AdminPlanController::class, 'index'])->name('admin.plans');
            Route::post('/admin/store-purchase-plans', [AdminPlanController::class, 'store'])->name('admin.plans.store');
            Route::put('/admin/update-purchase-plans/{plan}', [AdminPlanController::class, 'update'])->name('admin.plans.update');
            Route::delete('/admin/delete-purchase-plans/{plan}', [AdminPlanController::class, 'destroy'])->name('admin.plans.destroy');
            ///////////////////////////////end of admin part /////////////////////////////////////////////////////////////////////////////////////////////



            /////////////////////////////// tenant part  for tenants on the main site   .... add middleware to let only tenant owner to access here /////////////////////////////////////////



            Route::get('/tenant/purchase-plans', [TenantController::class, 'index'])->name('tenant.plans');
            Route::get('addUser', [TenantController::class, 'addUser'])->name('tenant.addUser');/// temporarly for testing     tobe deleted

            Route::get('/tenant/checkout', [TenantSubscriptionController::class, 'checkout'])->name('tenant.checkout');
            Route::get('/tenant/subscription', [TenantSubscriptionController::class, 'getTenantSubscription'])->name('tenant.getTenantSubscription');
            Route::get('tenantSubscriptionDetails', [TenantSubscriptionController::class, 'tenantSubscriptionDetails'])->name('tenantSubscriptionDetails');
            Route::put('/tenant/subscription/{plan}/{tenant}', [TenantSubscriptionController::class, 'changeSubscription'])->name('tenant.changeSubscription');
            Route::delete('/tenant/cancel_subscription', [TenantSubscriptionController::class, 'cancelSubscription'])->name('tenant.cancelSubscription');
            Route::get('/payment/update', [TenantSubscriptionController::class, 'updatePaymentMethod'])->name('payment.update'); // for cashier stripe

            Route::get('/subscription/retry-upgrade', [TenantSubscriptionController::class, 'retryUpgrade'])->name('subscription.retry-upgrade'); // for cashier stripe



            // Feature-specific routes
            Route::get('/advanced-features', function () {
                return view('tenant.advanced');
            })->middleware('check.subscription:advanced_features')->name('tenant.advanced');

        });
        /////////////////////////////// end of tenant part ///////////////////////////////////////////////////////////////////////////////////////////

    });

}









// remains    

// check config.queues.php

// chargeUpgradePaymentMethod   => register as a new tenant and check swap   add begin transactions in checkout method to prevent saving data on error

// event(new SubscriptionCreated($tenantSubscription));  /// replace with subscription updated


// add payment status in admin tenants table

// check retryUpgrade method

// prevent admin from access to tenants/dashboard

//  في حالة تسجيل مشترك جديد من صفحة المدير العام يتم تسجيله ولكن عملية الدفع تتم من طرف المشترك نفسه وهنا  في صفحة المشترك اذا كان مسجل من قبل ولم يدفع نفتح له صفحة الدفع الآن 

//  لاختبار الالغاء بشكل سليم ننشئ خطة لمدة يوم واحد فقط ثم نقوم بالالغاء وانتظار النتيجة هل سيتم الغاؤها 

//  اذا حدث تغيير للخطة يتم الغاء القديمة هنا وعلى السترايب  cancel it in subscription table and it will automatically canceled on stripe
//  تغيير  بيانات تواريخ نهاية الاشتراك للمشتركين بقاعدة البيانات عند الاشتراك  اول مرة بالبيانات القادمة من سترايب

// check app =>  mail
// check // Payment failed - redirect to update payment method

// in plan service cancle subscription /// check if user has many subscriptions comes from cashier  if only one so replace get with first() the modify foreach to handle one item

// search for url('    and fix routes
// check notificationEmail()
// check =>  $trialEndsAt = $trialDays > 0 && $tenantSubscriptionExists == null ? Carbon::now()->addDays($trialDays) : null



// composable.js , middleware , offers , design  
// details:
// use useSubscription.js composable on admin side
// prevent user from going to view subscription if he has no one and prevent from call cancel if he has no subscription 
// AdmincontrolPlans   add old price and new price as an offer
// modify design on tenant side
// add cancel now on admin and tenant    immediate   at_the_end








////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////


// list of files with changes 

// for now no need to loginResponse in app/http/responses/loginResponse or LogoutResponse    so we disable both  in jetstreamProvider unless we make modifications in loginResponse and logoutResponse to seperate site admins from tenants

// web.php
// route tenant.php
// bootstrap providers
// tenancy service provider  disable thes lines : Jobs\CreateDatabase::class,  Jobs\MigrateDatabase::class,  Jobs\DeleteDatabase::class
// models tenant.php
// config tenancy
// env
// create new User
// register.vue
// session.php  in 'domain' but not working fin  // no need to change session domain   . read the explanation before     'domain' =>  env('SESSION_DOMAIN' ),  

// models user => belongs to many relationship
// middleware check tenant user middleware   to let tenants users to login to there own subdomain and data
// project model and migrate  check the foriegn keys added and traits to seperate date from being leaked to other tenants
// task model and migrate  check the foriegn keys added and traits to seperate date from being leaked to other tenants
// jetstream provider
// login response
// logout response
// user.php   main_site_admn added as aboolean and default false
// CheckMainSiteAdminMiddleware added to the web.php
// CheckTenantUserMiddleware added to the tenant.php
//  required components  from shadcn  : table , button , dropdown-menu , select
// add tenant request.php
// update valueUpdater.vue component
// in appServiceProvider add         JsonResource::withoutWrapping(); // this is to remove word data when calling data from any resource like usersResource collection
// install Alert Dialog from chadcn and add it in all tenants to approve cancel all
// update '@/lib/utils' tobe like in this project
// create vue component DataTableDropDown.vue
// create tenant dashboard controller to let the tenant to handle his own supscription and modify it if he wants 
// add pagination component in all tenants .vue 
// add card component from shadcn vue
// create container component 
// create RenewalDashboardController 
// create payment service 
// create payment result service
// when creating a user for any tenant we have to add this line    after creating user $user = User::create(.....)
// $user->tenants()->attach(tenant('id')); /// very important line to attatch users with there tenants and we control access to only this tenant  from CheckTenantUserMiddleware 

// create config/subscription.php
// install composer require stripe/stripe-php
// install paypal

//create these files in app/notifications :
// SubscriptionRenewalSuccess ,PaymentFailureNotification ,SubscriptionRenewalFailure ,PaymentMethodRequired 


// add these to .env\:
// QUEUE_CONNECTION=redis // check 
// SUBSCRIPTION_GRACE_PERIOD_DAYS=3
// SUBSCRIPTION_FROM_EMAIL=noreply@yourapp.com
// PAYMENT_PROVIDER=stripe

// # Subscription specific settings
// SUBSCRIPTION_GRACE_PERIOD_DAYS=3
// SUBSCRIPTION_REMINDER_DAYS=7
// SUBSCRIPTION_MAX_RETRIES=3
// SUBSCRIPTION_FROM_EMAIL=noreply@yourapp.com
// SUBSCRIPTION_FROM_NAME="Your App"


////////////////////////////////////////////////////////////
// to make queues works :
// # Development
// php artisan queue:work redis --queue=subscriptions

// # Production (use Supervisor)
// sudo supervisorctl start laravel-subscription-worker:*
////////////////////////////////////////////////////////////






// uncoment   Stancl\Tenancy\Features\ViteBundler::class in config/tenancy /// check
// uncoment   Stancl\Tenancy\Features\UserImpersonation::class, in config/tenancy /// check


// migrations 
// domains
// tenants
// tenant_user


/////////// to make purchase plans for tenants /////////////////////////////////////////////////////////////////////////////////////////

// create dpurchase plane model and migration 
// create Tenant Subscription  model and migration 
// update tenant model to handle subscriptions and purchases
// create PlanService service 
// create purchase plan controller 
// create CheckSubscription middleware
// register middleware alias in bootstrap           
// ->withMiddleware(function (Middleware $middleware) {
//     $middleware->alias([
// 'check.subscription' => \App\Http\Middleware\CheckSubscription::class,
//     ]);
// })


// create resource PlanResource
// create resource TenantSubscriptionResource
// create PlanSeeder  and register is
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

// make command  app/Console/Commands/CheckExpiredSubscriptions.php
// make mail SubscriptionWelcome  and email view
// add event SubscriptionCreated  check function broadcastOn   channel 
// add listener  SendSubscriptionWelcomeEmail
// Register the event listener in app/Providers/EventServiceProvider.php  /// no need in laravel 12
// create event listener for webhook and register it in appServiceProvider  ... important to make changes in other tables in succees or failure


// check expired tenants/////////////////////////////////////////////
// to let the job working we need to add this line in bootstrap/app.php

// use Illuminate\Console\Scheduling\Schedule;
// ->withSchedule(function (Schedule $schedule) {
// $schedule->command( CheckExpiredSubscriptions::class)->everyMinute(); // or daily
// })

// or  add this to routes/console.php

// use Illuminate\Support\Facades\Schedule;
// Schedule::command('subscriptions:check-expired')->everyMinute(); // or daily


// on live server we need to add cron job
/////////////////////////////////////////////////////////////////////////////////


//////////////////// for renewal of subscriptions /////////////////////////////////////////////////////////
// inside controller =>  TenantSubscriptionController.php
// for manual renewal we need to create a method renewSubscription in TenantSubscriptionController.php
// and a method bulkRenew for bulk renewals in TenantSubscriptionController.php


// for automatic renewal we need to create a command ProcessDueRenewals.php



//  create composable vue supscription management 
// create subscription card componenet






// notice this code const 
// tenants = toRef(props , 'tenants');
// const data = computed(()=>  tenants.value.data) it must be like this to let table accept data because it is not accepting data.data witch comes from pagination

// remember : in  const table = useVueTable({ this must be like this columnPinning: { left: [], },
// remember this line in const headers =>  cell: ({ row }) => h('div', { class: 'capitalize' },  row.original.subscription.purchase_plan.price),
// remember to check function calculateEndDate to handle if plan is monthly or yearly 
// remember when we use resources the data return with '.data'   like subscription.data     we dont use it in other projects becase we handle it
// remember there is a main_site_admin for the super admin for main site
// remember to change url in the real site in this file =>  RegisterResponse
// remember we use  subscriptions.at(-1).plan.name to get last subscription either active or canceled
// remember to  arange  tenant route for middleware 'check.subscription' 
// remember to call in console stripe listen --forward-to  localhost:8000/stripe/webhook to make webhook work
// remember laravel herd will not work with subdomains because it has to be added manualy in C:\Windows\System32\drivers\etc








////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////



// check the following for payment proccess /////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
//  1. Environment Setup
// # Add these to your .env file

// QUEUE_CONNECTION=redis
// REDIS_HOST=127.0.0.1
// REDIS_PASSWORD=null
// REDIS_PORT=6379

// # Subscription specific settings
// SUBSCRIPTION_GRACE_PERIOD_DAYS=3
// SUBSCRIPTION_REMINDER_DAYS=7
// SUBSCRIPTION_MAX_RETRIES=3
// SUBSCRIPTION_FROM_EMAIL=noreply@yourapp.com
// SUBSCRIPTION_FROM_NAME="Your App"

// # Payment provider settings
// PAYMENT_PROVIDER=stripe
// PAYMENT_WEBHOOK_SECRET=your_webhook_secret_here

// # 2. Database Migrations
// php artisan make:migration create_tenant_payment_methods_table
// php artisan make:migration add_notes_to_tenant_subscriptions_table

// # Run migrations
// php artisan migrate

// # 3. Create notifications
// php artisan make:notification SubscriptionRenewalSuccess
// php artisan make:notification PaymentFailureNotification
// php artisan make:notification SubscriptionRenewalFailure
// php artisan make:notification PaymentMethodRequired


// # 5. Create commands
// php artisan make:command ProcessDueRenewals
// php artisan make:command CheckExpiredSubscriptions
// php artisan make:command SendSubscriptionRenewalReminders 

// create mail notification for tenants that will be expired next days => class SubscriptionRenewalReminder extends Mailable


// # 6. Queue Setup Commands

// # Start Redis server (if not already running)
// redis-server

// # Start queue worker for subscriptions
// php artisan queue:work redis --queue=subscriptions --sleep=3 --tries=3 --max-time=3600

// # Or run specific queue worker in background
// nohup php artisan queue:work redis --queue=subscriptions --sleep=3 --tries=3 --max-time=3600 > /dev/null 2>&1 &

// # 7. Test Commands

// # Test dry run of renewals
// php artisan subscriptions:process-renewals --dry-run

// # Actually process renewals
// php artisan subscriptions:process-renewals

// # Check expired subscriptions
// php artisan subscriptions:check-expired

// # 8. Supervisor Configuration for Production
// # Create file: /etc/supervisor/conf.d/laravel-subscription-worker.conf

// [program:laravel-subscription-worker]
// process_name=%(program_name)s_%(process_num)02d
// command=php /path/to/your/app/artisan queue:work redis --queue=subscriptions --sleep=3 --tries=3 --max-time=3600
// directory=/path/to/your/app
// autostart=true
// autorestart=true
// user=www-data
// numprocs=2
// redirect_stderr=true
// stdout_logfile=/path/to/your/app/storage/logs/worker.log
// stopwaitsecs=3600

// # 9. Supervisor commands
// sudo supervisorctl reread
// sudo supervisorctl update
// sudo supervisorctl start laravel-subscription-worker:*

// # 10. Monitoring Commands

// # Check queue status
// php artisan queue:monitor redis:subscriptions --max=100

// # Clear failed jobs
// php artisan queue:flush

// # Retry failed jobs
// php artisan queue:retry all

// # Check queue size
// php artisan tinker
// Queue::size('subscriptions')

// # 11. Testing in Tinker
// php artisan tinker

// # Create test data
// $tenant = App\Models\Tenant::first();
// $plan = App\Models\Plan::first();



// # Create subscription
// $subscription = App\Models\TenantSubscription::create([
//     'tenant_id' => $tenant->id,
//     'purchase_plan_id' => $plan->id,
//     'status' => 'active',
//     'ends_at' => now()->addDay() // Expires tomorrow
// ]);


// # Check job was queued
// Queue::size('subscriptions')

// # 12. Cron Job Setup
// # Add to crontab (crontab -e)
// * * * * * cd /path/to/your/app && php artisan schedule:run >> /dev/null 2>&1

// # 13. Log Monitoring
// tail -f storage/logs/laravel.log | grep "subscription"
// tail -f storage/logs/worker.log

// # 14. Performance Testing Script
// php artisan tinker

// # Create multiple test subscriptions
// $tenant = App\Models\Tenant::first();
// $plan = App\Models\Plan::first();

// for ($i = 0; $i < 100; $i++) {
//     $subscription = App\Models\TenantSubscription::create([
//         'tenant_id' => $tenant->id,
//         'purchase_plan_id' => $plan->id,
//         'status' => 'active',
//         'ends_at' => now()->addDays(rand(1, 3))
//     ]);


// # Check queue size
// Queue::size('subscriptions')

// # 15. Health Check Script
// # Create file: scripts/check-subscription-health.sh

// #!/bin/bash

// QUEUE_SIZE=$(php artisan tinker --execute="echo Queue::size('subscriptions');")
// FAILED_JOBS=$(php artisan tinker --execute="echo DB::table('failed_jobs')->count();")

// echo "Subscription queue size: $QUEUE_SIZE"
// echo "Failed jobs count: $FAILED_JOBS"

// if [ "$QUEUE_SIZE" -gt 1000 ]; then
//     echo "WARNING: Queue size is high"
// fi

// if [ "$FAILED_JOBS" -gt 10 ]; then
//     echo "WARNING: Too many failed jobs"
// fi

// # 16. Debugging Commands

// # View failed jobs
// php artisan queue:failed

// # Get details of a specific failed job
// php artisan queue:failed --id=1

// # Retry specific failed job
// php artisan queue:retry 1

// # Clear all failed jobs
// php artisan queue:flush

// # 17. Load Testing with Artillery (optional)
// # Install artillery: npm install -g artillery
// # Create artillery-test.yml:

// config:
//   target: 'http://your-app.com'
//   phases:
//     - duration: 60
//       arrivalRate: 10
// scenarios:
//   - name: "Trigger renewals"
//     requests:
//       - post:
//           url: "/api/subscriptions/{{ $randomInt(1, 100) }}/renew"
//           headers:
//             Authorization: "Bearer your-token"

// # Run load test
// artillery run artillery-