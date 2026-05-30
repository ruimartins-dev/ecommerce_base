@props([
    // Array of breadcrumb items: ['label' => string, 'url' => ?string].
    // The last item is rendered as the active (non-link) crumb.
    'items' => [],
])

@if (! empty($items))
    <nav aria-label="breadcrumb" {{ $attributes }}>
        <ol class="breadcrumb">
            @foreach ($items as $item)
                @php $isLast = $loop->last; @endphp
                <li class="breadcrumb-item {{ $isLast ? 'active' : '' }}"
                    @if ($isLast) aria-current="page" @endif>
                    @if (! $isLast && ! empty($item['url']))
                        <a href="{{ $item['url'] }}">{{ $item['label'] }}</a>
                    @else
                        {{ $item['label'] }}
                    @endif
                </li>
            @endforeach
        </ol>
    </nav>
@endif

