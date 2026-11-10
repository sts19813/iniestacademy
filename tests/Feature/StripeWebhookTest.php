<?php

namespace Tests\Feature;

use App\Mail\RegistrationConfirmation;
use App\Models\Registration;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Illuminate\Testing\TestResponse;
use Tests\TestCase;

class StripeWebhookTest extends TestCase
{
    use RefreshDatabase;

    private function sendEvent(array $data, string $secret): TestResponse
    {
        $payload = json_encode($data);
        $timestamp = time();
        $signature = hash_hmac('sha256', $timestamp.'.'.$payload, $secret);

        return $this->call('POST', '/stripe/webhook', [], [], [], ['HTTP_STRIPE_SIGNATURE' => 't='.$timestamp.',v1='.$signature, 'CONTENT_TYPE' => 'application/json'], $payload);
    }

    public function test_only_verified_payment_with_matching_amount_marks_registration_paid(): void
    {
        Mail::fake();
        config(['registration.stripe_webhook_secret' => 'whsec_test']);
        $registration = Registration::create(['buyer_name' => 'Test buyer', 'buyer_email' => 'test@example.com', 'buyer_phone' => '9991234567', 'participant_name' => 'Test player', 'participant_birth_date' => '2015-01-01', 'amount' => 820000, 'currency' => 'mxn', 'stripe_session_id' => 'cs_test_123']);
        $event = ['id' => 'evt_123', 'type' => 'checkout.session.completed', 'data' => ['object' => ['id' => 'cs_test_123', 'payment_status' => 'paid', 'amount_total' => 100, 'currency' => 'mxn', 'client_reference_id' => $registration->id, 'metadata' => ['registration_id' => $registration->id]]]];
        $this->sendEvent($event, 'wrong_secret')->assertStatus(400);
        $this->sendEvent($event, 'whsec_test')->assertStatus(400);
        $this->assertSame('pending', $registration->fresh()->status);
        $event['data']['object']['amount_total'] = 820000;
        $this->sendEvent($event, 'whsec_test')->assertOk();
        $this->sendEvent($event, 'whsec_test')->assertOk();
        $this->assertSame('paid', $registration->fresh()->status);
        $this->assertNotNull($registration->fresh()->paid_at);
        $this->assertSame('sent', $registration->fresh()->confirmation_email_status);
        Mail::assertSent(RegistrationConfirmation::class, 1);
        Mail::assertSent(RegistrationConfirmation::class, fn ($mail) => $mail->hasTo('test@example.com') && $mail->registration->id === $registration->id);
        $this->assertNotSame('Test buyer', \DB::table('registrations')->where('id', $registration->id)->value('buyer_name'));
    }
}
