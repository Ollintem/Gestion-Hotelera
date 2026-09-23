<div>
    @if ($mensajeExito)
        <div class="mb-4 animate-fade-in">
            <flux:callout variant="success" icon="check-circle">
                <p>{{ $mensajeExito }}</p>
            </flux:callout>
        </div>
    @endif

    @if ($mensajeError)
        <div class="mb-4 animate-fade-in">
            <flux:callout variant="danger" icon="x-circle">
                <p>{{ $mensajeError }}</p>
            </flux:callout>
        </div>
    @endif

    @include('empleados.partials.header')

    @include('empleados.partials.kpis')

    @include('empleados.partials.filtros')

    @include('empleados.partials.table')

    @php
        $accionesVisibles = collect($this->accionesMatriz());
    @endphp

    @include('empleados.partials.modal-permisos')
</div>