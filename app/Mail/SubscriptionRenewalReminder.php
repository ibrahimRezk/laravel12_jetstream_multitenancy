<?php

namespace App\Mail;

use App\Models\TenantSubscription;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class SubscriptionRenewalReminder extends Mailable
{
    use Queueable, SerializesModels;

    public TenantSubscription $tenantSubscription;

    public function __construct(TenantSubscription $tenantSubscription)
    {
        $this->tenantSubscription = $tenantSubscription;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Your Subscription is Expiring Soon - Renew Now',
            from: config('mail.from.address'),
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.subscription-renewal-reminder',
            with: [
                'subscription' => $this->tenantSubscription,
                'user' => $this->tenantSubscription->user,
                'plan' => $this->tenantSubscription->plan,
                'expiresAt' => $this->tenantSubscription->expires_at,
                'renewalUrl' => route('tenant.renew_subscription', $this->tenantSubscription),
            ]
        );
    }

    public function attachments(): array
    {
        return [];
    }
}