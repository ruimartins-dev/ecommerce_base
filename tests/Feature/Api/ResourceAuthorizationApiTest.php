<?php

declare(strict_types=1);

namespace Tests\Feature\Api;

use App\Enums\RoleEnum;
use App\Models\Address;
use App\Models\Catalog;
use App\Models\Category;
use App\Models\Customer;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ResourceAuthorizationApiTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::factory()->role(RoleEnum::Admin)->create();
    }

    /**
     * Create a customer user together with its customer profile.
     */
    private function customerWithProfile(): array
    {
        $user = User::factory()->role(RoleEnum::Customer)->create();
        $customer = Customer::factory()->create(['user_id' => $user->id]);

        return [$user->refresh(), $customer];
    }

    public function test_customer_cannot_access_admin_only_catalog_endpoints(): void
    {
        [$user] = $this->customerWithProfile();
        Sanctum::actingAs($user);

        $this->getJson('/api/catalogs')->assertForbidden();
        $this->postJson('/api/catalogs', ['name' => 'X'])->assertForbidden();
    }

    public function test_customer_cannot_list_or_create_customers(): void
    {
        [$user] = $this->customerWithProfile();
        Sanctum::actingAs($user);

        $this->getJson('/api/customers')->assertForbidden();
        $this->postJson('/api/customers', [])->assertForbidden();
    }

    public function test_admin_can_manage_catalogs(): void
    {
        Sanctum::actingAs($this->admin());

        $create = $this->postJson('/api/catalogs', [
            'name' => 'Spring Catalog',
            'is_active' => true,
        ])->assertCreated();

        $id = $create->json('data.id');

        $this->getJson('/api/catalogs')->assertOk()->assertJsonStructure(['data', 'meta', 'links']);
        $this->deleteJson("/api/catalogs/{$id}")->assertNoContent();
    }

    public function test_customer_can_browse_categories_but_not_write(): void
    {
        $category = Category::factory()->create();
        [$user] = $this->customerWithProfile();
        Sanctum::actingAs($user);

        $this->getJson('/api/categories')->assertOk()->assertJsonStructure(['data', 'meta', 'links']);
        $this->getJson("/api/categories/{$category->id}")->assertOk();
        $this->postJson('/api/categories', ['name' => 'Nope'])->assertForbidden();
    }

    public function test_category_update_prevents_circular_parent(): void
    {
        $parent = Category::factory()->create();
        $child = Category::factory()->childOf($parent)->create();

        Sanctum::actingAs($this->admin());

        // Trying to make the parent a child of its own descendant must fail.
        $this->putJson("/api/categories/{$parent->id}", [
            'name' => $parent->name,
            'slug' => $parent->slug,
            'parent_id' => $child->id,
        ])->assertUnprocessable()->assertJsonValidationErrors(['parent_id']);
    }

    public function test_customer_manages_only_their_own_addresses(): void
    {
        [$userA, $customerA] = $this->customerWithProfile();
        [, $customerB] = $this->customerWithProfile();

        $foreignAddress = Address::factory()->create(['customer_id' => $customerB->id]);

        Sanctum::actingAs($userA);

        // Listing only returns the caller's own addresses.
        Address::factory()->create(['customer_id' => $customerA->id]);
        $this->getJson('/api/addresses')->assertOk()->assertJsonCount(1, 'data');

        // Cannot view another customer's address.
        $this->getJson("/api/addresses/{$foreignAddress->id}")->assertForbidden();
    }

    public function test_customer_cannot_assign_address_to_another_customer(): void
    {
        [$userA, $customerA] = $this->customerWithProfile();
        [, $customerB] = $this->customerWithProfile();

        Sanctum::actingAs($userA);

        // Even when spoofing customer_id, the address is forced to the caller.
        $response = $this->postJson('/api/addresses', [
            'customer_id' => $customerB->id,
            'recipient_name' => 'Owner Test',
            'address_line_1' => '1 Main St',
            'postal_code' => '12345',
            'city' => 'Lisbon',
            'country' => 'Portugal',
        ])->assertCreated();

        $this->assertSame($customerA->id, $response->json('data.customer_id'));
        $this->assertDatabaseHas('addresses', [
            'id' => $response->json('data.id'),
            'customer_id' => $customerA->id,
        ]);
    }
}

