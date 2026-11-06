<div>
    <x-aviso-temporal :mensaje="$mensajeExito" tipo="success" clave="gastos-exito" />
    <x-aviso-temporal :mensaje="$mensajeError" tipo="error" clave="gastos-error" />

    @include('gastos.partials.header')

    @include('gastos.partials.totales')

    @include('gastos.partials.table')
</div>
