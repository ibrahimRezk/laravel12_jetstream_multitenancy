<?php

use App\Http\Controllers\AdminPurchasePlanController;
use App\Models\User;
use Inertia\Inertia;
use Illuminate\Support\Facades\Route;
use Illuminate\Foundation\Application;
use App\Http\Controllers\PurchasePlanController;
use App\Http\Middleware\CheckMainSiteAdminMiddleware;




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

        Route::middleware([
            'auth:sanctum',
            config('jetstream.auth_session'),
            'verified',
            CheckMainSiteAdminMiddleware::class
        ])->group(function () {
            Route::get('/dashboard', function () {
                if(auth()->user()->main_site_admin == true)
                {
                    return Inertia::render('AdminDashboard');
                }else{
                    return Inertia::render('TenantDashboard');
                }
            })->name('dashboard');


            Route::middleware(['web'])->group(function () {

                
                // Tenant subscription management

                Route::get('/admin/tenants', [AdminPurchasePlanController::class, 'getTenants'])->name('admin.getTenants');

                Route::get('/admin/purchase-plans', [AdminPurchasePlanController::class, 'index'])->name('admin.purchasePlans'); 
                Route::get('/admin/tenant/{tenantId}/subscription', [AdminPurchasePlanController::class, 'getTenantSubscription'])->name('admin.getTenantSubscription');
                Route::post('/admin/subscribe', [AdminPurchasePlanController::class, 'subscribe'])->name('admin.subscribe');
                Route::put('/admin/tenant/{tenantId}/subscription/{plan}', [AdminPurchasePlanController::class, 'changeSubscription'])->name('admin.changeSubscription');
                Route::delete('/admin/tenant/{tenantIds}/subscription', [AdminPurchasePlanController::class, 'cancelSubscription'])->name('admin.cancelSubscription');

            });
        });
    });
}

// remains    
// what is page.props in usesubscription
// AdmincontrolPlans   complete it to let admin control plans data and offers

// subscription card and usersupscription.js not used yet


// fetchPlans  fetchTenants  subscribeToPlan  cancelSubscription  changeSubscription
// check fetchTenantSubscription on admin side




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


// when creating a user for any tenant we have to add this line    after creating user $user = User::create(.....)
            // $user->tenants()->attach(tenant('id')); /// very important line to attatch users with there tenants and we control access to only this tenant  from CheckTenantUserMiddleware 

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
// create PurchasePlanService service 
// create purchase plan controller 
// create CheckSubscription middleware
// register middleware alias in bootstrap           
// ->withMiddleware(function (Middleware $middleware) {
//     $middleware->alias([
// 'check.subscription' => \App\Http\Middleware\CheckSubscription::class,
//     ]);
// })


// create resource PurchasePlanResource
// create resource TenantSubscriptionResource
// create PurchasePlanSeeder  and register is
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

// make command  app/Console/Commands/CheckExpiredSubscriptions.php
// make job ProcessSubscriptionRenewal
// make mail SubscriptionWelcome  and email view
// add event SubscriptionCreated  check function broadcastOn   channel 
// add listener  SendSubscriptionWelcomeEmail
// Register the event listener in app/Providers/EventServiceProvider.php  /// no need in laravel 12




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
// remember we use  subscriptions.at(-1).purchasePlan.name to get last subscription either active or canceled
// remember to  arange  tenant route for middleware 'check.subscription' 

// remember laravel herd will not work with subdomains because it has to be added manualy in C:\Windows\System32\drivers\etc