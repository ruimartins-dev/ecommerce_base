@props([
    'align' => 'end', // dropdown-menu alignment
])

{{-- Authenticated user dropdown: identity + logout. Shared across layouts. --}}
@auth
    @php($user = auth()->user())
    <div class="dropdown">
        <button class="btn btn-light border d-flex align-items-center gap-2 px-2" type="button"
                data-bs-toggle="dropdown" aria-expanded="false">
            <span class="avatar-circle">{{ strtoupper(substr($user->name, 0, 1)) }}</span>
            <span class="d-none d-sm-inline text-start lh-1">
                <span class="d-block fw-semibold small text-truncate" style="max-width: 11rem;">{{ $user->name }}</span>
                @if ($user->role?->name)
                    <span class="d-block text-muted" style="font-size: 0.72rem;">{{ $user->role->name }}</span>
                @endif
            </span>
        </button>
        <ul class="dropdown-menu dropdown-menu-{{ $align }} shadow">
            <li class="px-3 py-2">
                <div class="fw-semibold small text-truncate">{{ $user->name }}</div>
                <div class="text-muted text-truncate" style="font-size: 0.78rem;">{{ $user->email }}</div>
            </li>
            <li><hr class="dropdown-divider"></li>
            <li>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="dropdown-item d-flex align-items-center gap-2 text-danger">
                        <x-icon name="logout" /> {{ __('Log out') }}
                    </button>
                </form>
            </li>
        </ul>
    </div>
@endauth

