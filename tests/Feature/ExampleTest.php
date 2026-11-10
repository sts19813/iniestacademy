<?php

namespace Tests\Feature;

use Tests\TestCase;

class ExampleTest extends TestCase
{
    public function test_landing_is_served_without_session_and_with_security_headers(): void
    {
        $response = $this->get('/');

        $response->assertOk()->assertSee('INIESTA ACADEMY')->assertSee('data-dialog="inscripcion"', false);
        $response->assertHeader('Content-Security-Policy');
        $response->assertHeader('X-Frame-Options', 'DENY');
        $this->assertEmpty($response->headers->getCookies());
        $this->post('/')->assertStatus(405);
    }

    public function test_promotion_shows_configured_prices_and_percentage(): void
    {
        config(['registration.regular_amount' => 1099900, 'registration.promotion_amount' => 820000]);
        $this->get('/')->assertOk()->assertSee('25% de descuento por tiempo limitado')->assertSee('$10,999')->assertSee('$8,200');
        config(['registration.promotion_amount' => null]);
        $this->get('/')->assertDontSee('de descuento por tiempo limitado');
    }
}
