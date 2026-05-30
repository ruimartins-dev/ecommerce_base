<?php

declare(strict_types=1);

namespace Tests\Feature\Api;

use App\Enums\RoleEnum;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class AuthApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_returns_token_user_and_role(): void
    {
        $user = User::factory()->role(RoleEnum::Admin)->create([
            'email' => 'admin@example.com',
        ]);

        $response = $this->postJson('/api/login', [
            'email' => 'admin@example.com',
            'password' => 'password',
        ]);

        $response->assertCreated()
            ->assertJsonPath('data.token_type', 'Bearer')
            ->assertJsonPath('data.user.email', 'admin@example.com')
            ->assertJsonPath('data.user.role.slug', RoleEnum::Admin->value)
            ->assertJsonStructure(['data' => ['token', 'token_type', 'user' => ['id', 'name', 'email', 'role']]]);

        $this->assertNotEmpty($response->json('data.token'));
        // Sensitive data is never leaked.
        $response->assertJsonMissingPath('data.user.password');
        $this->assertCount(1, $user->fresh()->tokens);
    }

    public function test_login_validates_required_fields(): void
    {
        $this->postJson('/api/login', [])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['email', 'password']);
    }

    public function test_login_rejects_invalid_credentials(): void
    {
        User::factory()->create(['email' => 'jane@example.com']);

        $this->postJson('/api/login', [
            'email' => 'jane@example.com',
            'password' => 'wrong-password',
        ])->assertUnprocessable()->assertJsonValidationErrors(['email']);
    }

    public function test_login_rejects_disabled_accounts(): void
    {
        User::factory()->inactive()->create(['email' => 'off@example.com']);

        $this->postJson('/api/login', [
            'email' => 'off@example.com',
            'password' => 'password',
        ])->assertUnprocessable()->assertJsonValidationErrors(['email']);
    }

    public function test_me_returns_the_authenticated_user(): void
    {
        $user = User::factory()->role(RoleEnum::Customer)->create();

        Sanctum::actingAs($user);

        $this->getJson('/api/me')
            ->assertOk()
            ->assertJsonPath('data.id', $user->id)
            ->assertJsonPath('data.role.slug', RoleEnum::Customer->value);
    }

    public function test_protected_routes_require_authentication(): void
    {
        $this->getJson('/api/me')->assertUnauthorized();
        $this->getJson('/api/products')->assertUnauthorized();
    }

    public function test_logout_revokes_the_current_token(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('test')->plainTextToken;

        $this->withToken($token)->postJson('/api/logout')->assertOk();

        $this->assertCount(0, $user->fresh()->tokens);

        // Reset the resolved guard so the next request re-authenticates from
        // scratch instead of reusing the cached user from the logout request.
        $this->app['auth']->forgetGuards();

        // The revoked token can no longer authenticate.
        $this->withToken($token)->getJson('/api/me')->assertUnauthorized();
    }
}

