<?php

declare(strict_types=1);

use Inertia\Inertia;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PlanController;
use App\Http\Controllers\TaskController;
use App\Http\Controllers\TenantController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\TenantSubscriptionController;
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
        return Inertia::render('TenantSiteDashboard');
    })->name('dashboard');
    
    // Route::get('/dashboard', function () {
    //     return Inertia::render('TenantSiteDashboard', ['tenantSubscription' => tenant()->subscription(),]);
    // })->name('dashboard');









});






