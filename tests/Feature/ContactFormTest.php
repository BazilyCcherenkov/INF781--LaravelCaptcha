<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ContactFormTest extends TestCase
{
    use RefreshDatabase;

    public function test_contact_screen_can_be_rendered(): void
    {
        $response = $this->get('/contact');

        $response->assertStatus(200);
    }

    public function test_contact_form_can_be_submitted(): void
    {
        $response = $this->post('/contact', [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'message' => 'This is a test message.',
            'captcha' => 'test',
        ]);

        $response->assertSessionHas('status');
        $response->assertRedirect(route('contact.show'));
    }

    public function test_contact_form_rejects_bot_submissions(): void
    {
        $this->withSession([]);

        $response = $this->post('/contact', [
            'name' => 'Bot',
            'email' => 'bot@example.com',
            'message' => 'Spam message',
            'website' => 'http://spam-link.com',
        ]);

        $response->assertSessionHas('status');
        $this->assertDatabaseCount('contacts', 0);
        $response->assertRedirect('/contact');
    }

    public function test_contact_form_validates_required_fields(): void
    {
        $response = $this->post('/contact', []);

        $response->assertSessionHasErrors(['name', 'email', 'message', 'captcha']);
    }

    public function test_contact_form_rate_limits_submissions(): void
    {
        for ($i = 0; $i < 5; $i++) {
            $this->post('/contact', [
                'name' => 'User',
                'email' => 'user@example.com',
                'message' => 'Test message ' . $i,
                'captcha' => 'test',
            ]);
        }

        $response = $this->post('/contact', [
            'name' => 'User',
            'email' => 'user@example.com',
            'message' => 'Too many submissions',
            'captcha' => 'test',
        ]);

        $response->assertStatus(429);
    }
}