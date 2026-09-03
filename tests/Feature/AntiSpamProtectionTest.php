<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AntiSpamProtectionTest extends TestCase
{
    use RefreshDatabase;

    public function test_honeypot_rejects_submission_when_bot_fills_hidden_field(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/lumbung-pangan/donate', [
            'donor_name' => 'Spam Bot',
            'donor_phone' => '08123456789',
            'donation_type' => 'barang',
            'items' => 'Beras 10kg',
            'website_hp' => 'I am a bot', // Filled honeypot field
            'hp_time' => time() - 10,
        ]);

        $response->assertSessionHasErrors('spam');
    }

    public function test_honeypot_rejects_submission_when_submitted_too_fast(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/lumbung-pangan/donate', [
            'donor_name' => 'Fast Bot',
            'donor_phone' => '08123456789',
            'donation_type' => 'barang',
            'items' => 'Beras 10kg',
            'website_hp' => '',
            'hp_time' => time(), // Submitted in 0 seconds
        ]);

        $response->assertSessionHasErrors('spam');
    }

    public function test_public_form_rate_limiter_blocks_excessive_submissions(): void
    {
        $user = User::factory()->create();

        // 5 allowed submissions
        for ($i = 0; $i < 5; $i++) {
            $this->actingAs($user)->post('/lumbung-pangan/donate', [
                'donor_name' => 'John Doe',
                'donor_phone' => '08123456789',
                'donation_type' => 'barang',
                'items' => 'Beras 10kg',
                'website_hp' => '',
                'hp_time' => time() - 10,
            ]);
        }

        // 6th submission should be rate limited (429)
        $response = $this->actingAs($user)->post('/lumbung-pangan/donate', [
            'donor_name' => 'John Doe',
            'donor_phone' => '08123456789',
            'donation_type' => 'barang',
            'items' => 'Beras 10kg',
            'website_hp' => '',
            'hp_time' => time() - 10,
        ]);

        $response->assertStatus(429);
    }
}
