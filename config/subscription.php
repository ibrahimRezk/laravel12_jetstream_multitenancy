<?php 
return [
    'grace_period_days' => env('SUBSCRIPTION_GRACE_PERIOD_DAYS', 3),
    'reminder_days_before_expiry' => env('SUBSCRIPTION_REMINDER_DAYS', 7),
    'max_retry_attempts' => env('SUBSCRIPTION_MAX_RETRIES', 3),
    'retry_backoff_minutes' => [1, 5, 15], // Minutes to wait between retries
    
    'notifications' => [
        'from_email' => env('SUBSCRIPTION_FROM_EMAIL', 'noreply@yourapp.com'),
        'from_name' => env('SUBSCRIPTION_FROM_NAME', 'Your App'),
    ],
    
    'payment' => [
        'provider' => env('PAYMENT_PROVIDER', 'stripe'),
        'webhook_secret' => env('PAYMENT_WEBHOOK_SECRET'),
    ]
];