<?php

namespace Database\Seeders;

use App\Models\Plan;
use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class PlanSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
         Plan::create([
            'name' => 'Basic',
            'description' => 'Perfect for small businesses',
            'price' => 10,
            'currency' => 'USD',
            'product_id_on_stripe' => 'prod_SnXnH1CTGnaAEC',
            'price_id_on_stripe' => 'price_1RrwiDK0tau89y8TT2reXLet',
            'interval' => 'daily',
            'features' => ['basic_features', 'email_support'],
            'trial_days' => 7,
            'sort_order' => 1
        ]);

        Plan::create([
            'name' => 'Pro',
            'description' => 'Great for growing businesses',
            'price' => 150,
            'currency' => 'USD',
                        'product_id_on_stripe' => 'prod_SldLr1LXMeTFbp',
            'price_id_on_stripe' => 'price_1Rq64uK0tau89y8TjYP4X8aV',
            'interval' => 'daily',
            'features' => ['basic_features', 'advanced_features', 'priority_support'],
            'trial_days' => 14,
            'sort_order' => 2
        ]);

        Plan::create([
            'name' => 'Enterprise',
            'description' => 'For large organizations',
            'price' => 500,
            'currency' => 'USD',
                        'product_id_on_stripe' => 'prod_SlhYmiHNewl0to',
            'price_id_on_stripe' => 'price_1RqA9aK0tau89y8TxU0lbI3s',
            'interval' => 'yearly',
            'features' => ['basic_features', 'advanced_features', 'enterprise_features', 'dedicated_support'],
            'trial_days' => 30,
            'sort_order' => 3
        ]);
    }
}
