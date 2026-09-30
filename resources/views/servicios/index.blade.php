{{-- ======================================================
     PANTALLA DEL MÓDULO DE SERVICIOS

     El contenedor no lleva booleanos de visibilidad: el <dialog>
     del catálogo lo abre el propio formulario al terminar de
     cargar los datos, y el de cargos Flux desde el navegador
     con `modal-show`. Ningún morph del contenedor puede cerrarlos
     por accident porque viven bajo `wire:ignore`.
     ====================================================== --}}

<div>
    {{-- Alerta de latencia cero: Alpine escucha el evento y se pinta sin esperar
         a que Livewire re-renderice el bloque. --}}
    <div
        x-data="{ show: false, mensaje: '' }"
        x-on:notificacion.window="show = true; mensaje = $event.detail.mensaje; setTimeout(() => show = false, 3000)"
        x-show="show"
        x-cloak
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0 -translate-y-2"
        x-transition:enter-end="opacity-100 translate-y-0"
        x-transition:leave="transition ease-in duration-200"
        x-transition:leave-start="opacity-100 translate-y-0"
        x-transition:leave-end="opacity-0 -translate-y-2"
        class="fixed top-4 right-4 z-50 flex items-center gap-3 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 shadow-lg"
        role="status"
        aria-live="polite"
    >
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="size-5 shrink-0 text-emerald-600">
            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.857-9.809a.75.75 0 00-1.214-.882l-3.483 4.79-1.88-1.88a.75.75 0 10-1.06 1.061l2.5 2.5a.75.75 0 001.137-.089l4-5.5z" clip-rule="evenodd" />
        </svg>

        <span x-text="mensaje" class="text-sm font-semibold text-emerald-800"></span>

        <button
            type="button"
            x-on:click="show = false"
            class="shrink-0 text-emerald-600 transition-colors hover:text-emerald-800 focus:outline-none focus:ring-2 focus:ring-emerald-500/20"
            aria-label="Cerrar notificación"
        >
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="size-4">
                <path d="M6.28 5.22a.75.75 0 00-1.06 1.06L8.94 10l-3.72 3.72a.75.75 0 101.06 1.06L10 11.06l3.72 3.72a.75.75 0 101.06-1.06L11.06 10l3.72-3.72a.75.75 0 00-1.06-1.06L10 8.94 6.28 5.22z" />
            </svg>
        </button>
    </div>

    @include('servicios.partials.header')

    @include('servicios.partials.kpis')

    @include('servicios.partials.filtros')

    @include('servicios.partials.table')

    @include('servicios.partials.modal-form')
</div>
