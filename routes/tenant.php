<?php

declare(strict_types=1);

use Inertia\Inertia;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PlanController;
use App\Http\Controllers\TaskController;
use App\Http\Controllers\TenantController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\SubscriptionController;
use App\Http\Middleware\CheckTenantUserMiddleware;
use App\Http\Controllers\TenantDashboardController;
use Stancl\Tenancy\Middleware\InitializeTenancyByDomain;
use Stancl\Tenancy\Middleware\InitializeTenancyBySubdomain;
use Stancl\Tenancy\Middleware\PreventAccessFromCentralDomains;

/*
|--------------------------------------------------------------------------
| Tenant Routes
|--------------------------------------------------------------------------
|
| Here you can register the tenant routes for your application.
| These routes are loaded by the TenantRouteServiceProvider.
|
| Feel free to customize them however you want. Good luck!
|
*/

Route::middleware([
    'web',
    'auth:sanctum',
    config('jetstream.auth_session'),
    'verified',

    CheckTenantUserMiddleware::class, // to prevent a user from login to other tenants

    InitializeTenancyBySubdomain::class, // new  helpfull to add only the subdomain 
        // InitializeTenancyByDomain::class,
    PreventAccessFromCentralDomains::class,
])->group(function () {


    Route::middleware('check.subscription')->group(function () {
        // Route::get('/', function () {
        //     dd(\App\Models\User::all());
        //     return 'This is your multi-tenant application. The id of the current tenant is ' . tenant('id');
        // });

        Route::resource('projects', ProjectController::class);
        Route::resource('tasks', TaskController::class);


    });



    Route::get('/dashboard', function () {
            // if(auth()->user()->main_site_admin == true)
            //     {
            //         return Inertia::render('AdminDashboard');
                // }else{
                    return Inertia::render('TenantDashboard' , ['tenantSubscription' => tenant()->currentSubscription(), ]);
                // }
    })->name('dashboard');

    

    //tenantController /////////////////////////////////////////////////
    Route::get('/tenant/purchase-plans', [TenantController::class, 'index'])->name('tenant.plans');
    Route::get('addUser' ,[ TenantController::class , 'addUser'])->name('tenant.addUser');
    Route::get('tenantSubscriptionDetails' , [TenantController::class , 'tenantSubscriptionDetails'])->name('tenantSubscriptionDetails');
    ///////////////////////////////////////////////////////////////////////////////////////////////
    
    
    
    
    ///  tenantSubsriptionController/////////////////////////////////////////////
    Route::post('/tenant/subscripe/{plan}', [SubscriptionController::class, 'subscripe'])->name('tenant.subscripe');
    Route::get('/tenant/subscription', [SubscriptionController::class, 'getTenantSubscription'])->name('tenant.getTenantSubscription');
    Route::put('/tenant/subscription/{plan}/{tenant}', [SubscriptionController::class, 'changeSubscription'])->name('tenant.changeSubscription');
    Route::delete('/tenant/cancel_subscription', [SubscriptionController::class, 'cancelSubscription'])->name('tenant.cancelSubscription');
    Route::get('/tenant/renew_subscription/{supscriptionId}', [SubscriptionController::class, 'renewSubscription'])->name('tenant.renew_subscription');
    ///  tenantSubsriptionController end/////////////////////////////////////////////
    

    






    // Feature-specific routes
    Route::get('/advanced-features', function () {
        return view('tenant.advanced');
    })->middleware('check.subscription:advanced_features')->name('tenant.advanced');




});






