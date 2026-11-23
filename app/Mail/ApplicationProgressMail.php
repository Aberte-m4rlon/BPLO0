<?php

namespace App\Mail;

use App\Models\Application;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ApplicationProgressMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public User $applicant,
        public string $notificationType,
        public string $progressTitle,
        public string $progressBody,
        public ?Application $application = null,
    ) {}

    public function envelope(): Envelope
    {
        $tracking = $this->application?->tracking_number;
        $municipality = (string) config('permit.municipality', 'Municipality of Bansud');
        $prefix = $tracking ? "[{$tracking}] " : '';

        return new Envelope(
            subject: "{$prefix}{$this->progressTitle} — {$municipality} BPLO",
        );
    }

    public function content(): Content
    {
        $statusLabel = $this->application
            ? ucwords(strtolower(str_replace('_', ' ', (string) $this->application->status)))
            : $this->progressTitle;

        $actionUrl = $this->application
            ? route('applications.show', $this->application)
            : url('/dashboard');

        if ($this->notificationType === 'permit.released' && $this->application?->permit) {
            $actionUrl = route('applications.permit', $this->application);
        } elseif ($this->notificationType === 'application.approved_for_payment' && $this->application) {
            $actionUrl = route('applications.pay', $this->application);
        } elseif ($this->notificationType === 'payment.rejected' && $this->application) {
            $actionUrl = route('applications.pay', $this->application);
        } elseif ($this->notificationType === 'application.for_correction' && $this->application) {
            $actionUrl = route('applications.edit', $this->application);
        }

        return new Content(
            view: 'emails.application-progress',
            text: 'emails.application-progress-text',
            with: [
                'municipality' => config('permit.municipality'),
                'province' => config('permit.province'),
                'statusLabel' => $statusLabel,
                'actionUrl' => $actionUrl,
                'actionText' => match ($this->notificationType) {
                    'permit.released' => "View & Download Mayor's Permit",
                    'application.approved_for_payment', 'payment.rejected' => 'Proceed to Payment',
                    'application.for_correction' => 'Update Application Requirements',
                    default => 'View Application Status',
                },
            ],
        );
    }
}
