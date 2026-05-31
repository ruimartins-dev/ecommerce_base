@props([
    'product',
])

{{-- Visual stock state: out of stock, low stock warning, or healthy count. --}}
@if ($product->is_out_of_stock)
    <span class="badge badge-soft-danger"><span class="status-dot"></span>{{ __('Out of stock') }}</span>
@elseif ($product->is_low_stock)
    <span class="badge badge-soft-warning"><span class="status-dot"></span>{{ __('Low stock (:count)', ['count' => $product->stock]) }}</span>
@else
    <span class="badge badge-soft-success"><span class="status-dot"></span>{{ __(':count in stock', ['count' => $product->stock]) }}</span>
@endif

