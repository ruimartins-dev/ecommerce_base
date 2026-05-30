@extends('layouts.admin')

@section('title', __('Audit logs'))

@section('content')
    <x-admin.page-header
        :title="__('Audit logs')"
        :subtitle="__('Asynchronously recorded trail of important admin actions.')"
        :breadcrumbs="[
            ['label' => __('Admin'), 'url' => route('admin.dashboard')],
            ['label' => __('Audit logs')],
        ]" />

    <form method="GET" class="row g-2 mb-3">
        <div class="col-8 col-md-4">
            <select name="action" class="form-select">
                <option value="">{{ __('All actions') }}</option>
                @foreach ($actions as $action)
                    <option value="{{ $action }}" @selected(request('action') === $action)>{{ $action }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-4 col-md-2 d-grid">
            <button class="btn btn-outline-secondary" type="submit">{{ __('Filter') }}</button>
        </div>
    </form>

    @if ($logs->isEmpty())
        <x-empty-state :message="__('No audit logs yet.')" />
    @else
        <x-table :headers="[__('When'), __('Action'), __('Entity'), __('User'), __('Details')]">
            @foreach ($logs as $log)
                <tr>
                    <td class="small text-muted text-nowrap">{{ $log->created_at?->format('Y-m-d H:i:s') }}</td>
                    <td><span class="badge bg-secondary">{{ $log->action }}</span></td>
                    <td class="small">
                        {{ class_basename($log->entity_type) }}
                        @if ($log->entity_id)<span class="text-muted">#{{ $log->entity_id }}</span>@endif
                    </td>
                    <td class="small">{{ $log->user?->name ?? __('System') }}</td>
                    <td class="small text-muted">
                        @if (! empty($log->metadata))
                            <code>{{ json_encode($log->metadata, JSON_UNESCAPED_SLASHES) }}</code>
                        @else
                            —
                        @endif
                    </td>
                </tr>
            @endforeach
        </x-table>

        <div class="mt-3">
            {{ $logs->links() }}
        </div>
    @endif
@endsection

