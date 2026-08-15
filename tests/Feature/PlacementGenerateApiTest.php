<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PlacementGenerateApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_placement_generate_requires_authentication()
    {
        $response = $this->postJson('/api/placement/generate', [
            'category_id' => 1,
        ]);

        $response->assertUnauthorized();
    }

    public function test_placement_generate_requires_category_id()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user, 'sanctum')
            ->postJson('/api/placement/generate', []);

        $response->assertUnprocessable();
    }

    public function test_placement_generate_category_not_found()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user, 'sanctum')
            ->postJson('/api/placement/generate', [
                'category_id' => 99999,
            ]);

        $response->assertUnprocessable();
    }
}
