<?php

namespace App\Notifications;

use App\Models\TenantSubscription;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PaymentFailureNotification extends Notification
{
    use Queueable;

    protected $subscription;
    protected $errorMessage;

    public function __construct(TenantSubscription $subscription, $errorMessage = null)
    {
        $this->subscription = $subscription;
        $this->errorMessage = $errorMessage;
    }

    public function via($notifiable)
    {
        return ['mail', 'database'];
    }

    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject('Payment Failed - Action Required')
            ->greeting('Payment Issue')
            ->line('We were unable to process your subscription payment.')
            ->line('Plan: ' . $this->subscription->Plan->name)
            ->line('Amount: $' . number_format($this->subscription->Plan->price, 2))
            ->when($this->errorMessage, function ($mail) {
                return $mail->line('Error: ' . $this->errorMessage);
            })
            ->line('Please update your payment method to avoid service interruption.')
            ->action('Update Payment Method', url('/tenant/' . $this->subscription->tenant_id . '/billing'))
            ->line('Your subscription will remain active for a few more days while we attempt to process payment again.');
    }

    public function toArray($notifiable)
    {
        return [
            'type' => 'payment_failed',
            'subscription_id' => $this->subscription->id,
            'plan_name' => $this->subscription->Plan->name,
            'amount' => $this->subscription->Plan->price,
            'error_message' => $this->errorMessage,
            'message' => 'Payment failed for your subscription. Please update your payment method.'
        ];
    }
}