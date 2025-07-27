<?php

namespace App\Notifications;

use App\Models\TenantSubscription;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class SubscriptionRenewalSuccess extends Notification
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
            ->subject('Subscription Renewed Successfully')
            ->greeting('Hello!')
            ->line('Your subscription has been renewed successfully.')
            ->line('Plan: ' . $this->subscription->Plan->name)
            ->line('Next billing date: ' . $this->subscription->ends_at->format('F j, Y'))
            ->line('Amount: $' . number_format($this->subscription->Plan->price, 2))
            ->action('View Subscription', url('/tenant/' . $this->subscription->tenant_id . '/subscription'))
            ->line('Thank you for your continued business!');
    }

    public function toArray($notifiable)
    {
        return [
            'type' => 'subscription_renewed',
            'subscription_id' => $this->subscription->id,
            'plan_name' => $this->subscription->Plan->name,
            'amount' => $this->subscription->Plan->price,
            'next_billing_date' => $this->subscription->ends_at,
            'message' => 'Your subscription has been renewed successfully.'
        ];
    }
}