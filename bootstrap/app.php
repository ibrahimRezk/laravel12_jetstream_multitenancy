<?php

use Illuminate\Foundation\Application;
use App\Console\Commands\CheckExpiredSubscriptions;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Console\Scheduling\Schedule;


return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        api: __DIR__ . '/../routes/api.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->web(append: [
            \App\Http\Middleware\HandleInertiaRequests::class,
            \Illuminate\Http\Middleware\AddLinkHeadersForPreloadedAssets::class,
        ]);


        $middleware->alias([
            'check.subscription' => \App\Http\Middleware\CheckSubscription::class,
        ]);


        //
    })


    // new schadule added for the expired subscriptions command
    ->withSchedule(function (Schedule $schedule) {
        // Check for expired subscriptions every hour
        $schedule->command('subscriptions:check-expired')
            ->hourly()
            ->withoutOverlapping();

    })


    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
