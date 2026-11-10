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
}
