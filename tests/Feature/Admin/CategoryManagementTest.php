<?php

declare(strict_types=1);

namespace Tests\Feature\Admin;

use App\Enums\RoleEnum;
use App\Models\Category;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CategoryManagementTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::factory()->role(RoleEnum::Admin)->create();
    }

    public function test_admin_can_create_a_nested_category(): void
    {
        $parent = Category::factory()->create();

        $this->actingAs($this->admin())->post(route('admin.categories.store'), [
            'parent_id' => $parent->id,
            'name' => 'Phones',
            'slug' => 'phones',
            'is_active' => true,
            'sort_order' => 1,
        ])->assertRedirect(route('admin.categories.index'));

        $this->assertDatabaseHas('categories', [
            'name' => 'Phones',
            'parent_id' => $parent->id,
        ]);
    }

    public function test_a_category_cannot_be_its_own_parent(): void
    {
        $category = Category::factory()->create();

        $this->actingAs($this->admin())->put(route('admin.categories.update', $category), [
            'parent_id' => $category->id,
            'name' => $category->name,
            'slug' => $category->slug,
            'sort_order' => 0,
        ])->assertSessionHasErrors('parent_id');
    }

    public function test_a_category_cannot_be_parented_to_its_descendant(): void
    {
        $parent = Category::factory()->create();
        $child = Category::factory()->childOf($parent)->create();

        // Trying to set the parent's parent to its own child creates a loop.
        $this->actingAs($this->admin())->put(route('admin.categories.update', $parent), [
            'parent_id' => $child->id,
            'name' => $parent->name,
            'slug' => $parent->slug,
            'sort_order' => 0,
        ])->assertSessionHasErrors('parent_id');
    }
}

