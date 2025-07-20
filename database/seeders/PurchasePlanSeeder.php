<?php

namespace Database\Seeders;

use App\Models\PurchasePlan;
use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class PurchasePlanSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
         PurchasePlan::create([
            'name' => 'Basic',
            'description' => 'Perfect for small businesses',
            'price' => 19.99,
            'currency' => 'USD',
            'interval' => 'monthly',
            'features' => ['basic_features', 'email_support'],
            'trial_days' => 7,
            'sort_order' => 1
        ]);

        PurchasePlan::create([
            'name' => 'Pro',
            'description' => 'Great for growing businesses',
            'price' => 49.99,
            'currency' => 'USD',
            'interval' => 'monthly',
            'features' => ['basic_features', 'advanced_features', 'priority_support'],
            'trial_days' => 14,
            'sort_order' => 2
        ]);

        PurchasePlan::create([
            'name' => 'Enterprise',
            'description' => 'For large organizations',
            'price' => 99.99,
            'currency' => 'USD',
            'interval' => 'monthly',
            'features' => ['basic_features', 'advanced_features', 'enterprise_features', 'dedicated_support'],
            'trial_days' => 30,
            'sort_order' => 3
        ]);
    }
}
