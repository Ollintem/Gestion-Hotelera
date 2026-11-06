<div>
    <x-aviso-temporal :mensaje="$mensajeExito" tipo="success" clave="habitaciones-exito" />

    @include('habitaciones.partials.header')

    @if ($vista === 'cuadricula')
        @include('habitaciones.partials.cuadricula')
    @else
        @include('habitaciones.partials.lista')
    @endif
</div>