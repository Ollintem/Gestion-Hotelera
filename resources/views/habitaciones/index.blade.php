<div>
    @if ($mensajeExito)
        <div class="mb-4 animate-fade-in">
            <flux:callout variant="success" icon="check-circle">
                <p>{{ $mensajeExito }}</p>
            </flux:callout>
        </div>
    @endif

    @include('habitaciones.partials.header')

    @if ($vista === 'cuadricula')
        @include('habitaciones.partials.cuadricula')
    @else
        @include('habitaciones.partials.lista')
    @endif

    @include('habitaciones.partials.modal-form')
</div>