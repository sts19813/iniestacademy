<?php

namespace App\Http\Controllers;

use App\Models\Registration;
use Illuminate\Http\Request;
use Stripe\Webhook;
use Throwable;

class StripeWebhookController extends Controller
{
    public function __invoke(Request $request)
    {
        $secret = config('registration.stripe_webhook_secret');
        if (! $secret) {
            return response('Unavailable', 503);
        }
        try {
            $event = Webhook::constructEvent($request->getContent(), $request->header('Stripe-Signature', ''), $secret);
        } catch (Throwable $e) {
            return response('Invalid signature', 400);
        }
        if (! in_array($event->type, ['checkout.session.completed', 'checkout.session.async_payment_succeeded'], true)) {
            return response('OK', 200);
        }
        $session = $event->data->object;
        if ($session->payment_status !== 'paid') {
            return response('OK', 200);
        }
        $id = $session->metadata->registration_id ?? null;
        if (! $id) {
            return response('Invalid registration', 400);
        }
        $registration = Registration::find($id);
        if (! $registration || $session->client_reference_id !== $id || $session->amount_total !== $registration->amount || $session->currency !== $registration->currency || ($registration->stripe_session_id && $registration->stripe_session_id !== $session->id)) {
            return response('Invalid payment', 400);
        }
        Registration::whereKey($id)->where('status', 'pending')->update(['status' => 'paid', 'paid_at' => now(), 'stripe_session_id' => $session->id]);

        return response('OK', 200);
    }
}
