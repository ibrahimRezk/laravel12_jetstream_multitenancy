<?php

namespace App\Services;

use App\Models\Tenant;
use App\Models\Plan;
use Illuminate\Support\Facades\Log;

class PaymentService
{
    public static function processRecurringPayment( $plan)
    {


        try {

            return self::chargePaymentMethod( $plan);

            // $success = self::chargePaymentMethod();

            // if ($success) {
            //     $paymentId = 'pay_' . uniqid();
            //     Log::info('Payment processed successfully', [
            //         'tenant_id' => $tenant->id,
            //         'amount' => $plan->price,
            //         'payment_id' => $paymentId
            //     ]);

            //     return new PaymentResult(true, $paymentId, null);
            // } else {
            //     return new PaymentResult(false, null, 'Payment processing failed');
            // }

        } catch (\Exception $e) {
            // Log::error('Payment processing exception', [
            //     'tenant_id' => $tenant->id,
            //     'error' => $e->getMessage()
            // ]);

            return new PaymentResult(false, null, $e->getMessage());
        }
    }

    public static function chargePaymentMethod( $plan)
    {
        return request()->user()
            ->newSubscription($plan['product_id_on_stripe'], $plan['price_id_on_stripe'])
            // ->trialDays(5)
            // ->allowPromotionCodes()
            ->checkout([
                'success_url' => route('tenant.dashboard'),
                'cancel_url' => route('tenant.dashboard'),
            ]);

        // For demo purposes, randomly succeed/fail
        // return rand(1, 10) > 2; // 80% success rate 
    }
}
