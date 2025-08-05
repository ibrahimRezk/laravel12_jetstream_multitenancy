<?php

namespace App\Providers;

use Event;
use Illuminate\Support\ServiceProvider;
use Illuminate\Http\Resources\Json\JsonResource;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {


                JsonResource::withoutWrapping(); // this is to remove word data when calling data from any resource like usersResource collection

    }
}
