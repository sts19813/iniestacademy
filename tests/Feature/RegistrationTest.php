<?php

namespace Tests\Feature;

use App\Models\Registration;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Stripe\ApiRequestor;
use Stripe\HttpClient\ClientInterface;
use Tests\TestCase;

class RegistrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_checkout_uses_server_price_and_capacity_and_encrypts_data(): void
    {
        config(['registration.enabled' => true, 'registration.stripe_secret' => 'sk_test_dummy', 'registration.stripe_webhook_secret' => 'whsec_test', 'registration.regular_amount' => 820000, 'registration.promotion_amount' => 700000, 'registration.capacity' => 1]);
        $client = new class implements ClientInterface
        {
            public array $params = [];

            public function request($method, $absUrl, $headers, $params, $hasFile, $apiMode = 'v1', $maxNetworkRetries = null)
            {
                $this->params = $params;

                return [json_encode(['id' => 'cs_test_123', 'object' => 'checkout.session', 'url' => 'https://checkout.stripe.com/test', 'expires_at' => time() + 1860]), 200, []];
            }
        };
        ApiRequestor::setHttpClient($client);
        $data = ['buyer_name' => 'Comprador Test', 'buyer_email' => 'test@example.com', 'buyer_phone' => '9991234567', 'participant_name' => 'Jugador Test', 'participant_birth_date' => '2015-01-01', 'buyer_city' => 'Mérida', 'participant_position' => 'Defensa', 'football_years' => 3, 'birthplace' => 'Mérida', 'uniform_size' => 'M', 'socks_size' => 'M', 'shirt_name' => 'Jugador', 'privacy_consent' => '1', 'price_quote' => 700000, 'amount' => 1];
        $stalePrice = $data;
        $stalePrice['price_quote'] = 820000;
        $this->post('/inscripcion', $stalePrice)->assertSessionHasErrors('price_quote');
        $this->assertSame(0, Registration::count());
        $withoutConsent = $data;
        unset($withoutConsent['privacy_consent']);
        $this->post('/inscripcion', $withoutConsent)->assertSessionHasErrors('privacy_consent');
        $this->assertSame(0, Registration::count());
        $this->post('/inscripcion', $data)->assertRedirect('https://checkout.stripe.com/test');
        $this->assertSame(700000, $client->params['line_items'][0]['price_data']['unit_amount']);
        $this->assertStringContainsString('20 al 24 de marzo de 2027', $client->params['line_items'][0]['price_data']['product_data']['name']);
        $this->assertSame(700000, Registration::first()->amount);
        $this->assertSame(1, Registration::count());
        $this->assertSame('Mérida', Registration::first()->participant_details['buyer_city']);
        $this->assertNotNull(Registration::first()->privacy_accepted_at);
        $this->assertSame(hash_file('sha256', resource_path('views/privacy.blade.php')), Registration::first()->privacy_notice_hash);
        $this->assertNotSame('Comprador Test', \DB::table('registrations')->value('buyer_name'));
        $this->post('/inscripcion', $data)->assertSessionHasErrors('payment');
        $this->assertSame(1, Registration::count());
    }
}
