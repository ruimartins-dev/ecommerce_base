<?php

declare(strict_types=1);

namespace Tests\Unit\Enums;

use App\Enums\RoleEnum;
use PHPUnit\Framework\TestCase;

class RoleEnumTest extends TestCase
{
    public function test_backed_values_are_stable(): void
    {
        // These string values are persisted on the roles table and used by
        // middleware/policies, so they must not drift.
        $this->assertSame('admin', RoleEnum::Admin->value);
        $this->assertSame('customer', RoleEnum::Customer->value);
    }

    public function test_labels_are_human_readable(): void
    {
        $this->assertSame('Administrator', RoleEnum::Admin->label());
        $this->assertSame('Customer', RoleEnum::Customer->label());
    }

    public function test_only_the_two_known_roles_exist(): void
    {
        $this->assertSame(
            ['admin', 'customer'],
            array_map(static fn (RoleEnum $role): string => $role->value, RoleEnum::cases()),
        );
    }
}

