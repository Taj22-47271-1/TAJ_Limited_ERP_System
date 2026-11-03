<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\URL;
use Tests\TestCase;

class HttpsProxyTest extends TestCase
{
    public function test_https_urls_generated_behind_reverse_proxy(): void
    {
        $response = $this->withHeaders([
            'X-Forwarded-Proto' => 'https',
            'X-Forwarded-Port' => '443',
            'X-Forwarded-For' => '203.0.113.195',
        ])->get('/login');

        $response->assertStatus(200);

        // Ensure asset URLs in the response use HTTPS
        $content = $response->getContent();
        $this->assertStringContainsString('https://', $content);
        $this->assertStringNotContainsString('http://localhost', $content);
        $this->assertStringNotContainsString('http://', $content);
    }
}
