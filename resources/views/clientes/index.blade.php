<div>
    <x-aviso-temporal :mensaje="$mensajeExito" tipo="success" clave="clientes-exito" />
    <x-aviso-temporal :mensaje="$mensajeError ?? null" tipo="error" clave="clientes-error" />

    @include('clientes.partials.header')

    @include('clientes.partials.table')
</div>