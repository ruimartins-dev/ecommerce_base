<?php

declare(strict_types=1);

namespace Tests\Feature\Admin;

use App\Enums\RoleEnum;
use App\Models\Customer;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CustomerManagementTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::factory()->role(RoleEnum::Admin)->create();
    }

    public function test_admin_can_create_a_customer_linked_to_a_user(): void
    {
        $user = User::factory()->role(RoleEnum::Customer)->create();

        $this->actingAs($this->admin())->post(route('admin.customers.store'), [
            'user_id' => $user->id,
            'company_name' => 'Acme Corp',
            'vat_number' => 'PT123456789',
            'phone' => '+351 912 345 678',
        ])->assertRedirect(route('admin.customers.index'));

        $this->assertDatabaseHas('customers', [
            'user_id' => $user->id,
            'company_name' => 'Acme Corp',
        ]);
    }

    public function test_customer_cannot_be_linked_to_a_user_twice(): void
    {
        $customer = Customer::factory()->create();

        $this->actingAs($this->admin())->post(route('admin.customers.store'), [
            'user_id' => $customer->user_id,
            'company_name' => 'Duplicate Co',
        ])->assertSessionHasErrors('user_id');
    }

    public function test_admin_can_block_and_unblock_a_customer(): void
    {
        $customer = Customer::factory()->create(['is_blocked' => false]);
        $admin = $this->admin();

        $this->actingAs($admin)
            ->patch(route('admin.customers.toggle-block', $customer))
            ->assertRedirect();
        $this->assertTrue($customer->fresh()->is_blocked);

        $this->actingAs($admin)
            ->patch(route('admin.customers.toggle-block', $customer))
            ->assertRedirect();
        $this->assertFalse($customer->fresh()->is_blocked);
    }
}

