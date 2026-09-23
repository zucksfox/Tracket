<?php

namespace Tests\Feature;

// use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    /**
     * Halaman depan (/) adalah redirect ke portal tracking publik.
     */
    public function test_the_application_redirects_root_to_public_tracking(): void
    {
        $response = $this->get('/');

        $response->assertRedirect(route('tracking.index'));
    }
}
