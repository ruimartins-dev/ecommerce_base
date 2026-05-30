<?php

declare(strict_types=1);

namespace Tests\Feature\Customer;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\InteractsWithStorefront;
use Tests\TestCase;

class CartTest extends TestCase
{
    use InteractsWithStorefront;
    use RefreshDatabase;

    public function test_customer_can_add_a_product_to_the_cart(): void
    {
        $product = $this->visibleProduct(['stock' => 5]);

        $response = $this->actingAs($this->customerUser())
            ->post(route('customer.cart.add'), [
                'product_id' => $product->id,
                'quantity' => 2,
            ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');
        $this->assertSame([$product->id => 2], session('cart'));
    }

    public function test_cart_page_shows_added_product(): void
    {
        $user = $this->customerUser();
        $product = $this->visibleProduct(['name' => 'Cart Product', 'stock' => 5]);

        $this->actingAs($user)->post(route('customer.cart.add'), [
            'product_id' => $product->id,
            'quantity' => 1,
        ]);

        $this->actingAs($user)
            ->get(route('customer.cart.index'))
            ->assertOk()
            ->assertSee('Cart Product');
    }

    public function test_empty_cart_shows_empty_state(): void
    {
        $this->actingAs($this->customerUser())
            ->get(route('customer.cart.index'))
            ->assertOk()
            ->assertSee('O carrinho está vazio');
    }

    public function test_quantity_cannot_exceed_stock(): void
    {
        $product = $this->visibleProduct(['stock' => 3]);

        $this->actingAs($this->customerUser())
            ->post(route('customer.cart.add'), [
                'product_id' => $product->id,
                'quantity' => 4,
            ])
            ->assertSessionHasErrors('quantity');

        $this->assertNull(session('cart'));
    }

    public function test_accumulated_quantity_respects_stock(): void
    {
        $user = $this->customerUser();
        $product = $this->visibleProduct(['stock' => 3]);

        // First add of 2 is fine.
        $this->actingAs($user)->post(route('customer.cart.add'), [
            'product_id' => $product->id,
            'quantity' => 2,
        ])->assertSessionHasNoErrors();

        // Adding 2 more would total 4 > stock of 3.
        $this->actingAs($user)->post(route('customer.cart.add'), [
            'product_id' => $product->id,
            'quantity' => 2,
        ])->assertSessionHasErrors('quantity');

        $this->assertSame([$product->id => 2], session('cart'));
    }

    public function test_out_of_stock_product_cannot_be_added(): void
    {
        $product = $this->visibleProduct(['stock' => 0]);

        $this->actingAs($this->customerUser())
            ->post(route('customer.cart.add'), [
                'product_id' => $product->id,
                'quantity' => 1,
            ])
            ->assertSessionHasErrors('quantity');
    }

    public function test_inactive_product_cannot_be_added(): void
    {
        $product = $this->visibleProduct(['stock' => 5]);
        $product->update(['is_active' => false]);

        $this->actingAs($this->customerUser())
            ->post(route('customer.cart.add'), [
                'product_id' => $product->id,
                'quantity' => 1,
            ])
            ->assertSessionHasErrors('product_id');
    }

    public function test_customer_can_update_cart_quantity(): void
    {
        $user = $this->customerUser();
        $product = $this->visibleProduct(['stock' => 10]);

        $this->actingAs($user)->post(route('customer.cart.add'), [
            'product_id' => $product->id,
            'quantity' => 1,
        ]);

        $this->actingAs($user)->patch(route('customer.cart.update', $product), [
            'quantity' => 4,
        ])->assertRedirect(route('customer.cart.index'));

        $this->assertSame([$product->id => 4], session('cart'));
    }

    public function test_customer_can_remove_product_from_cart(): void
    {
        $user = $this->customerUser();
        $product = $this->visibleProduct(['stock' => 10]);

        $this->actingAs($user)->post(route('customer.cart.add'), [
            'product_id' => $product->id,
            'quantity' => 1,
        ]);

        $this->actingAs($user)->delete(route('customer.cart.remove', $product))
            ->assertRedirect(route('customer.cart.index'));

        $this->assertNull(session('cart'));
    }

    public function test_guest_cannot_add_to_cart(): void
    {
        $product = $this->visibleProduct();

        $this->post(route('customer.cart.add'), [
            'product_id' => $product->id,
            'quantity' => 1,
        ])->assertRedirect(route('login'));
    }
}

