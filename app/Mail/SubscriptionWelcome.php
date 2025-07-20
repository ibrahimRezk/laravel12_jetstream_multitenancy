<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use App\Models\TenantSubscription;

class SubscriptionWelcome extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     */
   public $subscription;

    public function __construct(TenantSubscription $subscription)
    {
        $this->subscription = $subscription;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        // return new Envelope(
        //     subject: 'Subscription Welcome',
        // );
        
        
        return new Envelope(
            subject: 'Welcome to ' . $this->subscription->purchasePlan->name . ' Plan!',
        );
       
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        $mailData['planName'] = $this->subscription->purchasePlan->name;
        $mailData['features'] = $this->subscription->purchasePlan->features;
        $mailData['trialEndsAt'] = $this->subscription->trial_ends_at;
        $mailData['endsAt'] = $this->subscription->ends_at;

        return new Content(
            view: 'view.name',
            with: $mailData,
        );

    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}
