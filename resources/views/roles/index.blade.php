<div>
    @if ($mensajeExito)
        <flux:callout variant="success" icon="check-circle" class="mb-4 animate-fade-in">
            <p>{{ $mensajeExito }}</p>
        </flux:callout>
    @endif

    @if ($mensajeError)
        <flux:callout variant="danger" icon="x-circle" class="mb-4 animate-fade-in">
            <p>{{ $mensajeError }}</p>
        </flux:callout>
    @endif

    @include('roles.partials.header')

    @php
        $totalRoles = $this->roles->count();
    @endphp

    @include('roles.partials.table')

    @include('roles.partials.modal-eliminar')
</div>