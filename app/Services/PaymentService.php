<?php

namespace App\Services;

use App\Models\Tenant;
use App\Models\Plan;
use Illuminate\Support\Facades\Log;

class PaymentService
{
    public static function processRecurringPayment($newPlan, $changeSubscription)
    {



        try {

            if ($changeSubscription == true) {
                return self::chargeUpgradePaymentMethod($newPlan);
            } else {
                return self::chargePaymentMethod($newPlan);
            }


        } catch (\Exception $e) {
            Log::error('Payment processing exception', [
                // 'tenant_id' => $tenant->id,
                'error' => $e->getMessage()
            ]);

            return new PaymentResult(false, null, $e->getMessage());
        }
    }

    public static function chargePaymentMethod($plan)
    {
        return request()->user()
            ->newSubscription($plan['product_id_on_stripe'], $plan['price_id_on_stripe'])
            // ->trialDays(5)
            // ->allowPromotionCodes()
            ->checkout([
                'success_url' => route('dashboard'),
                'cancel_url' => route('dashboard'),
            ]);

    }


    public static function chargeUpgradePaymentMethod($newPlan)
    {
        $user = auth()->user();
        $newPriceId = $newPlan->price_id_on_stripe;

        $oldSubscription = $user->subscriptions()->where('stripe_status', 'active')->first();

        if (!$oldSubscription) {
            return redirect()->back()->with('error', 'No active subscription found.');
        }

        try {
            // Try to swap the subscription
            $oldSubscription->swapAndInvoice($newPriceId);

            return redirect()->route('dashboard')
                ->with('success', 'Subscription upgraded successfully!');

        } catch (\Stripe\Exception\CardException $e) {
            // Payment failed - redirect to update payment method
            return redirect()->route('payment.update')
                ->with('error', 'Payment failed: ' . $e->getMessage())
                ->with('pending_upgrade', $newPlan);

        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Failed to upgrade subscription: ' . $e->getMessage());
        }

    }
}
