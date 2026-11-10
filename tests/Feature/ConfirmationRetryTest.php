<?php

namespace Tests\Feature;

use App\Mail\RegistrationConfirmation;
use App\Models\Registration;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use RuntimeException;
use Tests\TestCase;

class ConfirmationRetryTest extends TestCase
{
    use RefreshDatabase;

    public function test_failed_mail_keeps_payment_confirmed_and_can_be_retried(): void
    {
        config(['registration.stripe_webhook_secret' => 'whsec_test']);
        $registration = Registration::create(['buyer_name' => 'Test buyer', 'buyer_email' => 'test@example.com', 'buyer_phone' => '9990000000', 'participant_name' => 'Test player', 'participant_birth_date' => '2015-01-01', 'amount' => 820000, 'currency' => 'mxn', 'stripe_session_id' => 'cs_test_retry']);
        $payload = json_encode(['id' => 'evt_retry', 'type' => 'checkout.session.completed', 'data' => ['object' => ['id' => 'cs_test_retry', 'payment_status' => 'paid', 'amount_total' => 820000, 'currency' => 'mxn', 'client_reference_id' => $registration->id, 'metadata' => ['registration_id' => $registration->id]]]]);
        $timestamp = time();
        $headers = ['HTTP_STRIPE_SIGNATURE' => 't='.$timestamp.',v1='.hash_hmac('sha256', $timestamp.'.'.$payload, 'whsec_test'), 'CONTENT_TYPE' => 'application/json'];
        $mailManager = Mail::getFacadeRoot();
        Mail::shouldReceive('to')->once()->andThrow(new RuntimeException('SMTP unavailable'));
        $this->call('POST', '/stripe/webhook', [], [], [], $headers, $payload)->assertStatus(503);
        $this->assertSame('paid', $registration->fresh()->status);
        $this->assertSame('pending', $registration->fresh()->confirmation_email_status);
        Mail::swap($mailManager);
        Mail::fake();
        $this->call('POST', '/stripe/webhook', [], [], [], $headers, $payload)->assertOk();
        $this->call('POST', '/stripe/webhook', [], [], [], $headers, $payload)->assertOk();
        $this->assertSame('sent', $registration->fresh()->confirmation_email_status);
        Mail::assertSent(RegistrationConfirmation::class, 1);
    }
}
