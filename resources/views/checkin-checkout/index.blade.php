<div>
    <x-aviso-temporal :mensaje="$mensajeExito" tipo="success" clave="checkin-exito" />
    <x-aviso-temporal :mensaje="$mensajeError" tipo="error" clave="checkin-error" />

    @include('checkin-checkout.partials.header')

    @include('checkin-checkout.partials.pendientes')

    @include('checkin-checkout.partials.historial')

    @include('checkin-checkout.partials.modal')
</div>
