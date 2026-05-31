@props([
    'status', // App\Enums\OrderStatusEnum
])

@php
    use App\Enums\OrderStatusEnum;

    // The happy-path lifecycle, in order. "Cancelled" is handled separately.
    $flow = [
        OrderStatusEnum::Pending,
        OrderStatusEnum::Confirmed,
        OrderStatusEnum::Processing,
        OrderStatusEnum::Shipped,
        OrderStatusEnum::Completed,
    ];

    $isCancelled = $status === OrderStatusEnum::Cancelled;

    $currentIndex = $isCancelled
        ? -1
        : array_search($status, $flow, true);
@endphp

<ul class="timeline">
    @foreach ($flow as $i => $step)
        @php
            $isDone = ! $isCancelled && $currentIndex !== false && $i < $currentIndex;
            $isCurrent = ! $isCancelled && $i === $currentIndex;
            $stateClass = $isCurrent ? 'is-current' : ($isDone ? 'is-done' : '');
        @endphp
        <li class="timeline-item {{ $stateClass }}">
            <span class="timeline-marker"></span>
            <div class="d-flex justify-content-between align-items-center">
                <span class="{{ $isCurrent ? 'fw-semibold' : ($isDone ? '' : 'text-muted') }}">
                    {{ $step->label() }}
                </span>
                @if ($isCurrent)
                    <span class="badge badge-soft-primary">{{ __('Current') }}</span>
                @elseif ($isDone)
                    <x-icon name="check" class="text-success" />
                @endif
            </div>
        </li>
    @endforeach

    @if ($isCancelled)
        <li class="timeline-item is-cancelled">
            <span class="timeline-marker"></span>
            <div class="d-flex justify-content-between align-items-center">
                <span class="fw-semibold text-danger">{{ __('Cancelled') }}</span>
                <span class="badge badge-soft-danger">{{ __('Current') }}</span>
            </div>
        </li>
    @endif
</ul>

