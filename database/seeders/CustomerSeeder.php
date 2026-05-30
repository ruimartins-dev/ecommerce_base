<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\RoleEnum;
use App\Models\Address;
use App\Models\Customer;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class CustomerSeeder extends Seeder
{
    /**
     * Seed the default B2B customer account plus a few extra customers.
     */
    public function run(): void
    {
        $customerRole = Role::where('slug', RoleEnum::Customer->value)->firstOrFail();

        // Deterministic test customer.
        $user = User::updateOrCreate(
            ['email' => 'customer@example.com'],
            [
                'role_id' => $customerRole->id,
                'name' => 'Customer User',
                'password' => Hash::make('password'),
                'is_active' => true,
                'email_verified_at' => now(),
            ],
        );

        $customer = Customer::updateOrCreate(
            ['user_id' => $user->id],
            [
                'company_name' => 'Acme Industrial Supplies, Lda.',
                'vat_number' => 'PT123456789',
                'phone' => '+351 210 000 000',
                'is_blocked' => false,
            ],
        );

        $defaultAddress = Address::updateOrCreate(
            [
                'customer_id' => $customer->id,
                'label' => 'Headquarters',
            ],
            [
                'recipient_name' => 'Acme Receiving Dept.',
                'company_name' => 'Acme Industrial Supplies, Lda.',
                'address_line_1' => 'Rua do Comércio, 100',
                'address_line_2' => 'Piso 2',
                'postal_code' => '1100-150',
                'city' => 'Lisboa',
                'country' => 'Portugal',
                'vat_number' => 'PT123456789',
                'is_default' => true,
            ],
        );

        Address::updateOrCreate(
            [
                'customer_id' => $customer->id,
                'label' => 'Warehouse',
            ],
            [
                'recipient_name' => 'Acme Logistics',
                'company_name' => 'Acme Industrial Supplies, Lda.',
                'address_line_1' => 'Zona Industrial, Lote 4',
                'postal_code' => '2600-100',
                'city' => 'Vila Franca de Xira',
                'country' => 'Portugal',
                'is_default' => false,
            ],
        );

        $customer->update(['default_address_id' => $defaultAddress->id]);

        // A handful of additional realistic customers with their own addresses.
        Customer::factory()
            ->count(5)
            ->create()
            ->each(function (Customer $customer): void {
                $default = Address::factory()
                    ->default()
                    ->create(['customer_id' => $customer->id]);

                Address::factory()
                    ->count(fake()->numberBetween(0, 2))
                    ->create(['customer_id' => $customer->id]);

                $customer->update(['default_address_id' => $default->id]);
            });
    }
}

