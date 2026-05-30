@props([
    'product',
])

{{-- Visual stock state: out of stock, low stock warning, or healthy count. --}}
@if ($product->is_out_of_stock)
    <span class="badge text-bg-danger">{{ __('Esgotado') }}</span>
@elseif ($product->is_low_stock)
    <span class="badge text-bg-warning">{{ __('Low stock (:count)', ['count' => $product->stock]) }}</span>
@else
    <span class="badge text-bg-success">{{ __(':count in stock', ['count' => $product->stock]) }}</span>
@endif

