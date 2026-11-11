<?php

namespace Tests\Feature;

use Tests\TestCase;

class SeoTest extends TestCase
{
    public function test_landing_has_canonical_social_metadata_and_search_indexing(): void
    {
        config(['app.url' => 'https://iniestaacademy.mx']);
        $response = $this->get('/');
        $response->assertOk()
            ->assertSee('<meta name="robots" content="index, follow, max-image-preview:large"', false)
            ->assertSee('<link rel="canonical" href="https://iniestaacademy.mx/"', false)
            ->assertSee('<meta property="og:url" content="https://iniestaacademy.mx/"', false)
            ->assertSee('<meta property="og:image" content="https://iniestaacademy.mx/assets/compartir-iniesta-merida.jpg"', false)
            ->assertSee('<meta name="twitter:card" content="summary_large_image"', false)
            ->assertDontSee('noindex, nofollow');
        $dimensions = getimagesize(public_path('assets/compartir-iniesta-merida.jpg'));
        $this->assertSame(1200, $dimensions[0]);
        $this->assertSame(630, $dimensions[1]);
        $this->assertSame('image/jpeg', $dimensions['mime']);
    }

    public function test_sitemap_lists_public_home_and_not_admin(): void
    {
        config(['app.url' => 'https://iniestaacademy.mx']);
        $response = $this->get('/sitemap.xml');
        $response->assertOk()->assertHeader('Content-Type', 'application/xml; charset=UTF-8')->assertSee('<loc>https://iniestaacademy.mx/</loc>', false)->assertDontSee('/admin');
        $xml = simplexml_load_string($response->getContent());
        $this->assertNotFalse($xml);
        $this->assertCount(1, $xml->url);
    }
}
