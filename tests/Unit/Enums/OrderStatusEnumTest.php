<?php

declare(strict_types=1);

namespace Tests\Unit\Enums;

use App\Enums\OrderStatusEnum;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * Pure tests for the order lifecycle state machine. This enum is the single
 * source of truth for legal status transitions, so the rules are pinned here
 * (no database, no framework boot).
 */
class OrderStatusEnumTest extends TestCase
{
    public function test_every_case_exposes_a_human_label(): void
    {
        $this->assertSame('Pending', OrderStatusEnum::Pending->label());
        $this->assertSame('Confirmed', OrderStatusEnum::Confirmed->label());
        $this->assertSame('Processing', OrderStatusEnum::Processing->label());
        $this->assertSame('Shipped', OrderStatusEnum::Shipped->label());
        $this->assertSame('Completed', OrderStatusEnum::Completed->label());
        $this->assertSame('Cancelled', OrderStatusEnum::Cancelled->label());
    }

    public function test_options_maps_every_value_to_its_label(): void
    {
        $this->assertSame([
            'pending' => 'Pending',
            'confirmed' => 'Confirmed',
            'processing' => 'Processing',
            'shipped' => 'Shipped',
            'completed' => 'Completed',
            'cancelled' => 'Cancelled',
        ], OrderStatusEnum::options());
    }

    public function test_completed_and_cancelled_are_terminal(): void
    {
        $this->assertTrue(OrderStatusEnum::Completed->isTerminal());
        $this->assertTrue(OrderStatusEnum::Cancelled->isTerminal());

        $this->assertSame(
            [OrderStatusEnum::Completed, OrderStatusEnum::Cancelled],
            OrderStatusEnum::terminal(),
        );
    }

    public function test_non_terminal_statuses_are_not_terminal(): void
    {
        $this->assertFalse(OrderStatusEnum::Pending->isTerminal());
        $this->assertFalse(OrderStatusEnum::Confirmed->isTerminal());
        $this->assertFalse(OrderStatusEnum::Processing->isTerminal());
        $this->assertFalse(OrderStatusEnum::Shipped->isTerminal());
    }

    /**
     * @return array<string, array{0: OrderStatusEnum, 1: OrderStatusEnum}>
     */
    public static function allowedTransitionProvider(): array
    {
        return [
            'pending -> confirmed' => [OrderStatusEnum::Pending, OrderStatusEnum::Confirmed],
            'pending -> cancelled' => [OrderStatusEnum::Pending, OrderStatusEnum::Cancelled],
            'confirmed -> processing' => [OrderStatusEnum::Confirmed, OrderStatusEnum::Processing],
            'confirmed -> cancelled' => [OrderStatusEnum::Confirmed, OrderStatusEnum::Cancelled],
            'processing -> shipped' => [OrderStatusEnum::Processing, OrderStatusEnum::Shipped],
            'processing -> cancelled' => [OrderStatusEnum::Processing, OrderStatusEnum::Cancelled],
            'shipped -> completed' => [OrderStatusEnum::Shipped, OrderStatusEnum::Completed],
        ];
    }

    #[DataProvider('allowedTransitionProvider')]
    public function test_allowed_transitions_are_permitted(OrderStatusEnum $from, OrderStatusEnum $to): void
    {
        $this->assertTrue($from->canTransitionTo($to));
        $this->assertContains($to, $from->allowedTransitions());
    }

    /**
     * The forbidden transitions explicitly called out by the spec, plus a few
     * other illegal jumps that must never be allowed.
     *
     * @return array<string, array{0: OrderStatusEnum, 1: OrderStatusEnum}>
     */
    public static function forbiddenTransitionProvider(): array
    {
        return [
            'completed -> pending' => [OrderStatusEnum::Completed, OrderStatusEnum::Pending],
            'cancelled -> processing' => [OrderStatusEnum::Cancelled, OrderStatusEnum::Processing],
            'completed -> cancelled' => [OrderStatusEnum::Completed, OrderStatusEnum::Cancelled],
            'cancelled -> completed' => [OrderStatusEnum::Cancelled, OrderStatusEnum::Completed],
            'pending -> shipped' => [OrderStatusEnum::Pending, OrderStatusEnum::Shipped],
            'pending -> completed' => [OrderStatusEnum::Pending, OrderStatusEnum::Completed],
            'shipped -> cancelled' => [OrderStatusEnum::Shipped, OrderStatusEnum::Cancelled],
            'shipped -> processing' => [OrderStatusEnum::Shipped, OrderStatusEnum::Processing],
        ];
    }

    #[DataProvider('forbiddenTransitionProvider')]
    public function test_forbidden_transitions_are_rejected(OrderStatusEnum $from, OrderStatusEnum $to): void
    {
        $this->assertFalse($from->canTransitionTo($to));
        $this->assertNotContains($to, $from->allowedTransitions());
    }

    public function test_terminal_statuses_allow_no_further_transitions(): void
    {
        $this->assertSame([], OrderStatusEnum::Completed->allowedTransitions());
        $this->assertSame([], OrderStatusEnum::Cancelled->allowedTransitions());
    }
}

