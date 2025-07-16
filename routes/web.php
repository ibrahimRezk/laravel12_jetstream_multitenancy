<?php

use App\Http\Middleware\CheckMainSiteAdminMiddleware;
use App\Models\User;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;




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
                return Inertia::render('Dashboard');
            })->name('dashboard');
        });


    });
}

// remains    
// 1  redirect after register to the right place with the subdomain
// 2  add purchases and plans for site owner and check login for him and login for subdomains 

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

// migrations 
// domains
// tenants
// tenant_user


// remember laravel herd will not work with subdomains because it has to be added manualy in C:\Windows\System32\drivers\etc