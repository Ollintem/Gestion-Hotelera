{{-- ======================================================
     PANTALLA DEL MÓDULO DE SERVICIOS

     El contenedor no lleva booleanos de visibilidad: el <dialog>
     del catálogo lo abre el propio formulario al terminar de
     cargar los datos, y el de cargos Flux desde el navegador
     con `modal-show`. Ningún morph del contenedor puede cerrarlos
     por accident porque viven bajo `wire:ignore`.
     ====================================================== --}}

<div>
    {{-- Los avisos de este módulo se dibujan con el mecanismo global que
         escucha el evento `notificacion`. --}}

    @include('servicios.partials.header')

    @include('servicios.partials.kpis')

    @include('servicios.partials.filtros')

    @include('servicios.partials.table')

    @include('servicios.partials.modal-form')
</div>
