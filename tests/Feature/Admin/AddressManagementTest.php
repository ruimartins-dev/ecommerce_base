<?php

declare(strict_types=1);

namespace Tests\Feature\Admin;

use App\Enums\RoleEnum;
use App\Models\Address;
use App\Models\Customer;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AddressManagementTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::factory()->role(RoleEnum::Admin)->create();
    }

    public function test_setting_a_default_address_demotes_the_previous_default(): void
    {
        $customer = Customer::factory()->create();
        $existing = Address::factory()->default()->create(['customer_id' => $customer->id]);
        $customer->update(['default_address_id' => $existing->id]);

        $this->actingAs($this->admin())->post(route('admin.addresses.store'), [
            'customer_id' => $customer->id,
            'recipient_name' => 'New Recipient',
            'address_line_1' => '1 New Street',
            'postal_code' => '1000-001',
            'city' => 'Lisbon',
            'country' => 'Portugal',
            'is_default' => true,
        ])->assertRedirect(route('admin.addresses.index'));

        $new = Address::where('recipient_name', 'New Recipient')->firstOrFail();

        $this->assertTrue($new->is_default);
        $this->assertFalse($existing->fresh()->is_default);
        $this->assertSame($new->id, $customer->fresh()->default_address_id);
    }

    public function test_admin_can_delete_an_address(): void
    {
        $address = Address::factory()->create();

        $this->actingAs($this->admin())
            ->delete(route('admin.addresses.destroy', $address))
            ->assertRedirect(route('admin.addresses.index'));

        $this->assertSoftDeleted($address);
    }
}

