<?php

namespace App\Notifications;

use App\Models\TenantSubscription;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PaymentMethodRequired extends Notification
{
    use Queueable;

    protected $subscription;

    public function __construct(TenantSubscription $subscription)
    {
        $this->subscription = $subscription;
    }

    public function via($notifiable)
    {
        return ['mail', 'database'];
    }

    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject('Payment Method Required')
            ->greeting('Action Required')
            ->line('We need a valid payment method to continue your subscription.')
            ->line('Plan: ' . $this->subscription->Plan->name)
            ->line('Please add a payment method to avoid service interruption.')
            ->action('Add Payment Method', url('/tenant/' . $this->subscription->tenant_id . '/billing'))
            ->line('Thank you for your prompt attention to this matter.');
    }

    public function toArray($notifiable)
    {
        return [
            'type' => 'payment_method_required',
            'subscription_id' => $this->subscription->id,
            'plan_name' => $this->subscription->Plan->name,
            'message' => 'A valid payment method is required to continue your subscription.'
        ];
    }
}