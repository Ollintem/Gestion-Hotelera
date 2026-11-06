{{-- ======================================================
     PANTALLA DEL MÓDULO DE PAGOS Y ESTADO DE CUENTA
     ====================================================== --}}

<div>
    {{-- Los avisos de este módulo se dibujan con el mecanismo global que
         escucha el evento `notificacion`. --}}

    @include('pagos.partials.header')

    @include('pagos.partials.estado-cuenta')

    @include('pagos.partials.table')

    @include('pagos.partials.modal-form')
</div>
