<?php

namespace App\Notifications;

use App\Models\TenantSubscription;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class SubscriptionRenewalFailure extends Notification
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
            ->subject('Subscription Renewal Failed')
            ->greeting('Important: Subscription Issue')
            ->line('We were unable to renew your subscription after multiple attempts.')
            ->line('Plan: ' . $this->subscription->Plan->name)
            ->line('Your service may be interrupted if this issue is not resolved.')
            ->action('Resolve Issue', url('/tenant/' . $this->subscription->tenant_id . '/billing'))
            ->line('Please contact support if you need assistance.');
    }

    public function toArray($notifiable)
    {
        return [
            'type' => 'renewal_failed',
            'subscription_id' => $this->subscription->id,
            'plan_name' => $this->subscription->Plan->name,
            'message' => 'Subscription renewal failed after multiple attempts.'
        ];
    }
}