<?php

declare(strict_types=1);

namespace Tests\Feature\Auth;

use App\Enums\RoleEnum;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_screen_can_be_rendered(): void
    {
        $this->get(route('login'))->assertOk();
    }

    public function test_admin_can_authenticate_and_is_redirected_to_admin_area(): void
    {
        $admin = User::factory()->role(RoleEnum::Admin)->create();

        $response = $this->post(route('login'), [
            'email' => $admin->email,
            'password' => 'password',
        ]);

        $this->assertAuthenticatedAs($admin);
        $response->assertRedirect(route('admin.dashboard'));
    }

    public function test_customer_can_authenticate_and_is_redirected_to_customer_area(): void
    {
        $customer = User::factory()->role(RoleEnum::Customer)->create();

        $response = $this->post(route('login'), [
            'email' => $customer->email,
            'password' => 'password',
        ]);

        $this->assertAuthenticatedAs($customer);
        $response->assertRedirect(route('customer.dashboard'));
    }

    public function test_users_cannot_authenticate_with_invalid_password(): void
    {
        $user = User::factory()->role(RoleEnum::Customer)->create();

        $this->post(route('login'), [
            'email' => $user->email,
            'password' => 'wrong-password',
        ])->assertSessionHasErrors('email');

        $this->assertGuest();
    }

    public function test_authenticated_users_can_logout(): void
    {
        $user = User::factory()->role(RoleEnum::Customer)->create();

        $this->actingAs($user)
            ->post(route('logout'))
            ->assertRedirect(route('login'));

        $this->assertGuest();
    }

    public function test_already_authenticated_user_visiting_login_is_redirected_home(): void
    {
        $admin = User::factory()->role(RoleEnum::Admin)->create();

        $this->actingAs($admin)
            ->get(route('login'))
            ->assertRedirect(route('admin.dashboard'));
    }
}

