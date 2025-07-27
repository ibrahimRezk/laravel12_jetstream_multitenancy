<?php

use App\Http\Controllers\Controller;



class WebhookController extends Controller
{
    public function handlePaymentWebhook(Request $request)
    {
        // Verify webhook signature (implementation depends on payment provider)
        if (!$this->verifyWebhookSignature($request)) {
            return response()->json(['error' => 'Invalid signature'], 400);
        }

        $eventType = $request->input('type');
        $eventData = $request->input('data');

        switch ($eventType) {
            case 'invoice.payment_succeeded':
                $this->handlePaymentSucceeded($eventData);
                break;
                
            case 'invoice.payment_failed':
                $this->handlePaymentFailed($eventData);
                break;
                
            case 'customer.subscription.updated':
                $this->handleSubscriptionUpdated($eventData);
                break;
        }

        return response()->json(['status' => 'success']);
    }

    private function handlePaymentSucceeded($data)
    {
        $subscriptionId = $data['subscription_id'] ?? null;
        
        if ($subscriptionId) {
            $subscription = TenantSubscription::where('external_id', $subscriptionId)->first();
            
            if ($subscription) {
                // Process renewal
                ProcessSubscriptionRenewal::dispatch($subscription->id);
            }
        }
    }

    private function handlePaymentFailed($data)
    {
        // Handle failed payment webhook
        Log::info('Payment failed webhook received', $data);
    }

    private function verifyWebhookSignature(Request $request)
    {
        // Implement signature verification for your payment provider
        return true; // Placeholder
    }
}
