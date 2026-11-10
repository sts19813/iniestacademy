<?php

namespace App\Services;

use App\Mail\RegistrationConfirmation;
use App\Models\Registration;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use RuntimeException;
use Throwable;

class RegistrationConfirmationSender
{
    public function send(Registration $registration): bool
    {
        if ($registration->confirmation_email_status === null || $registration->confirmation_email_status === 'sent') {
            return true;
        }
        $claimed = Registration::whereKey($registration->id)->where('status', 'paid')->where(function ($query) {
            $query->where('confirmation_email_status', 'pending')->orWhere(function ($query) {
                $query->where('confirmation_email_status', 'sending')->where('confirmation_email_attempted_at', '<', now()->subMinutes(5));
            });
        })->update(['confirmation_email_status' => 'sending', 'confirmation_email_attempted_at' => now()]);
        if (! $claimed) {
            return Registration::whereKey($registration->id)->where('confirmation_email_status', 'sent')->exists();
        }
        try {
            if (app()->environment('production') && config('mail.default') !== 'smtp') {
                throw new RuntimeException('SMTP is not configured.');
            }
            $sent = Mail::to($registration->buyer_email)->send(new RegistrationConfirmation($registration));
            if (app()->environment('production') && ! $sent) {
                throw new RuntimeException('SMTP did not accept the message.');
            }
            Registration::whereKey($registration->id)->where('confirmation_email_status', 'sending')->update(['confirmation_email_status' => 'sent', 'confirmation_email_sent_at' => now()]);

            return true;
        } catch (Throwable $e) {
            Registration::whereKey($registration->id)->where('confirmation_email_status', 'sending')->update(['confirmation_email_status' => 'pending']);
            Log::error('Registration confirmation email failed', ['registration_id' => $registration->id, 'exception_type' => get_class($e)]);

            return false;
        }
    }
}
