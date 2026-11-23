<?php

namespace App\Services;

use App\Contracts\SmsChannel;
use App\Mail\ApplicationProgressMail;
use App\Models\Application;
use App\Models\Notification;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class NotificationService
{
    /**
     * Create an in-app notification and automatically email the applicant's email(s).
     * If the user also has a contact number and an SMS driver is configured, send SMS inline.
     */
    public function notify(User $user, string $type, string $title, string $body, ?Application $application = null): void
    {
        $notification = Notification::create([
            'user_id' => $user->getKey(),
            'application_id' => $application?->getKey(),
            'type' => $type,
            'title' => $title,
            'body' => $body,
            'channel' => 'in_app',
        ]);

        $driver = config('permit.sms.driver');
        $dirty = false;

        if ($user->contact_number && $driver !== null) {
            try {
                $result = app(SmsChannel::class)->send($user->contact_number, $title.': '.$body);
                $notification->sms_status = $result['status'] ?? 'sent';
            } catch (\Throwable $e) {
                Log::warning('SMS send failed; in-app notification kept.', [
                    'user_id' => $user->getKey(),
                    'error' => $e->getMessage(),
                ]);
                $notification->sms_status = 'failed';
            }

            $dirty = true;
        }

        $recipients = collect([$application?->email, $user->email])
            ->filter(fn ($email): bool => filled($email) && filter_var($email, FILTER_VALIDATE_EMAIL) !== false)
            ->unique(fn ($email): string => strtolower((string) $email))
            ->values()
            ->all();

        if ($recipients !== []) {
            if ($application !== null) {
                if ($type === 'permit.released') {
                    $application->load('permit');
                }
                $application->loadMissing(['permit', 'remarks']);
            }

            try {
                Mail::to($recipients)->send(new ApplicationProgressMail($user, $type, $title, $body, $application));
                $notification->email_status = 'sent';
            } catch (\Throwable $e) {
                Log::warning('Progress email send failed; in-app notification kept.', [
                    'user_id' => $user->getKey(),
                    'recipients' => $recipients,
                    'error' => $e->getMessage(),
                ]);
                $notification->email_status = 'failed';
            }

            $dirty = true;
        }

        if ($dirty) {
            $notification->save();
        }
    }
}
