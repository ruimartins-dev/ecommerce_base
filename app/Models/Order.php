<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\OrderStatusEnum;
use Database\Factories\OrderFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Order extends Model
{
    /** @use HasFactory<OrderFactory> */
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'customer_id',
        'address_id',
        'order_number',
        'status',
        'subtotal',
        'tax_amount',
        'discount_amount',
        'total',
        'notes',
        'placed_at',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'status' => OrderStatusEnum::class,
            'subtotal' => 'decimal:2',
            'tax_amount' => 'decimal:2',
            'discount_amount' => 'decimal:2',
            'total' => 'decimal:2',
            'placed_at' => 'datetime',
        ];
    }

    /**
     * The customer that placed the order.
     *
     * @return BelongsTo<Customer, $this>
     */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    /**
     * The shipping/billing address snapshot reference for the order.
     *
     * @return BelongsTo<Address, $this>
     */
    public function address(): BelongsTo
    {
        return $this->belongsTo(Address::class);
    }

    /**
     * The line items belonging to the order.
     *
     * @return HasMany<OrderItem, $this>
     */
    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    /**
     * Determine whether the order is pending.
     */
    public function isPending(): bool
    {
        return $this->status === OrderStatusEnum::Pending;
    }

    /**
     * Determine whether the order is completed.
     */
    public function isCompleted(): bool
    {
        return $this->status === OrderStatusEnum::Completed;
    }

    /**
     * Determine whether the order is cancelled.
     */
    public function isCancelled(): bool
    {
        return $this->status === OrderStatusEnum::Cancelled;
    }

    /**
     * Scope a query to orders with the given status.
     *
     * @param  Builder<Order>  $query
     */
    public function scopeStatus(Builder $query, OrderStatusEnum $status): void
    {
        $query->where('status', $status->value);
    }
}

