@props([
    // Column header labels for the table head.
    'headers' => [],
    // Sticky header keeps column labels visible while scrolling long tables.
    'sticky' => false,
])

{{-- Responsive Bootstrap table shell. The default slot renders the <tbody> rows. --}}
<div class="card">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0 {{ $sticky ? 'table-sticky' : '' }}">
            <thead>
                <tr>
                    @foreach ($headers as $header)
                        <th scope="col">{{ $header }}</th>
                    @endforeach
                </tr>
            </thead>
            <tbody>
                {{ $slot }}
            </tbody>
        </table>
    </div>
</div>

