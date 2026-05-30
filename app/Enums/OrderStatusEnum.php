<?php

declare(strict_types=1);

namespace App\Enums;

enum OrderStatusEnum: string
{
    case Pending = 'pending';
    case Confirmed = 'confirmed';
    case Processing = 'processing';
    case Shipped = 'shipped';
    case Completed = 'completed';
    case Cancelled = 'cancelled';

    /**
     * Human readable label for the status.
     */
    public function label(): string
    {
        return match ($this) {
            self::Pending => 'Pending',
            self::Confirmed => 'Confirmed',
            self::Processing => 'Processing',
            self::Shipped => 'Shipped',
            self::Completed => 'Completed',
            self::Cancelled => 'Cancelled',
        };
    }

    /**
     * Statuses that represent a terminal (closed) order.
     *
     * @return array<int, self>
     */
    public static function terminal(): array
    {
        return [self::Completed, self::Cancelled];
    }

    /**
     * Whether the status is terminal (no further transitions allowed).
     */
    public function isTerminal(): bool
    {
        return in_array($this, self::terminal(), true);
    }

    /**
     * The set of statuses this status is allowed to transition to.
     *
     * This is the single source of truth for the order lifecycle, consumed by
     * both the validation layer and the {@see \App\Services\OrderStatusService}.
     *
     * @return array<int, self>
     */
    public function allowedTransitions(): array
    {
        return match ($this) {
            self::Pending => [self::Confirmed, self::Cancelled],
            self::Confirmed => [self::Processing, self::Cancelled],
            self::Processing => [self::Shipped, self::Cancelled],
            self::Shipped => [self::Completed],
            self::Completed, self::Cancelled => [],
        };
    }

    /**
     * Whether a transition from this status to the given status is allowed.
     */
    public function canTransitionTo(self $target): bool
    {
        return in_array($target, $this->allowedTransitions(), true);
    }

    /**
     * Value => label map for building <select> options.
     *
     * @return array<string, string>
     */
    public static function options(): array
    {
        return array_reduce(
            self::cases(),
            static function (array $carry, self $status): array {
                $carry[$status->value] = $status->label();

                return $carry;
            },
            [],
        );
    }
}

