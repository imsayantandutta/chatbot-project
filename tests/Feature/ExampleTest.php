<?php

namespace Tests\Feature;

// use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    /**
     * A basic test example.
     */
    public function test_the_application_returns_a_successful_response(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200);
        $response->assertSee('Gemini AI Assistant');
    }

    public function test_chat_requires_a_message(): void
    {
        $response = $this->postJson('/chat', []);

        $response->assertStatus(422);
    }
}
