<div>
    <x-aviso-temporal :mensaje="$mensajeExito" tipo="success" clave="empleados-exito" />
    <x-aviso-temporal :mensaje="$mensajeError" tipo="error" clave="empleados-error" />

    @include('empleados.partials.header')

    @include('empleados.partials.kpis')

    @include('empleados.partials.filtros')

    @include('empleados.partials.table')

    @php
        $accionesVisibles = collect($this->accionesMatriz());
    @endphp

    @include('empleados.partials.modal-permisos')
</div>