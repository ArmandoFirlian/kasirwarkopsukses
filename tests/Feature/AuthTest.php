<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_returns_token(): void
    {
        $user = User::factory()->kasir()->create();

        $this->postJson('/api/v1/auth/login', [
            'email' => $user->email,
            'password' => 'password',
        ])
            ->assertOk()
            ->assertJsonStructure(['data' => ['token', 'token_type', 'user']]);
    }

    public function test_login_with_wrong_password_returns_401(): void
    {
        $user = User::factory()->create();

        $this->postJson('/api/v1/auth/login', [
            'email' => $user->email,
            'password' => 'wrong',
        ])->assertStatus(401);
    }

    public function test_me_returns_profile(): void
    {
        $user = User::factory()->owner()->create();
        Sanctum::actingAs($user);

        $this->getJson('/api/v1/auth/me')
            ->assertOk()
            ->assertJsonPath('data.role', 'owner');
    }

    public function test_logout_revokes_token(): void
    {
        Sanctum::actingAs(User::factory()->create());

        $this->postJson('/api/v1/auth/logout')->assertOk();
    }
}
