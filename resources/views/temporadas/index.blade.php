{{-- ======================================================
     PANTALLA DEL MÓDULO DE TEMPORADAS

     Contenedor principal: cabecera, secciones y ventanas. No hay lógica aquí
     dentro; lo único que declara es el estado de Alpine del que dependen los
     dos modales y el aviso flotante.

     `abierto` y `pagina` están entrelazados con Livewire, de modo que el servidor
     es la única fuente de verdad. Aun así la tecla Escape baja primero la
     bandera de Alpine y luego avisa a Livewire: así la tarjeta se retira en el
     mismo fotograma del clic y sin esperar al morph.

     Los dos modales se incluyen siempre, abiertos o no, y cada uno se muestra con
     `x-show`. Si el HTML se borrara al cerrar, Morphdom lo desaparecería de golpe
     y la transición de salida no se vería nunca: solo la de entrada.
     ====================================================== --}}

<div
    x-data="{ abierto: @entangle('modalAbierto'), pagina: @entangle('pagina') }"
    x-on:keydown.escape.window="abierto = false; $wire.cerrarModal()"
>
    {{-- ======================================================
         AVISO FLOTANTE

         El alta, la edición, el borrado y el cambio de estado emiten el mismo
         evento `notificacion`, así que un único aviso los cubre a todos. Lo pinta
         Alpine escuchando el evento en lugar de leer una propiedad del
         componente: aparece en el mismo clic que lo emitió, sin esperar un morph,
         y el temporizador de 3 s lo retira solo.

         En móvil el ancho es completo y baja al borde superior, porque un aviso
         pegado a la esquina queda fuera del alcance del pulgar.
         ====================================================== --}}

    <div
        x-data="{ visible: false, mensaje: '', temporizador: null }"
        x-on:notificacion.window="visible = true; mensaje = $event.detail.mensaje; clearTimeout(temporizador); temporizador = setTimeout(() => visible = false, 3000)"
        x-show="visible"
        x-cloak
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0 -translate-y-2 scale-95"
        x-transition:enter-end="opacity-100 translate-y-0 scale-100"
        x-transition:leave="transition ease-in duration-200"
        x-transition:leave-start="opacity-100 translate-y-0 scale-100"
        x-transition:leave-end="opacity-0 -translate-y-2 scale-95"
        class="fixed inset-x-4 top-4 z-50 mx-auto flex max-w-sm items-start gap-3 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 shadow-lg shadow-emerald-900/5 sm:inset-x-auto sm:right-4"
        role="status"
        aria-live="polite"
    >
        <span class="inline-flex size-8 shrink-0 items-center justify-center rounded-full bg-emerald-100 text-emerald-600">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="size-5" aria-hidden="true">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.857-9.809a.75.75 0 00-1.214-.882l-3.483 4.79-1.88-1.88a.75.75 0 10-1.06 1.061l2.5 2.5a.75.75 0 001.137-.089l4-5.5z" clip-rule="evenodd" />
            </svg>
        </span>

        <span x-text="mensaje" class="flex-1 text-sm font-semibold text-emerald-900"></span>

        <button
            type="button"
            x-on:click="visible = false"
            class="-mr-1 shrink-0 rounded-lg p-1 text-emerald-500 transition-colors duration-200 hover:bg-emerald-100 hover:text-emerald-700 focus:outline-none focus:ring-2 focus:ring-offset-1"
            aria-label="Cerrar notificación"
        >
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="size-4" aria-hidden="true">
                <path d="M6.28 5.22a.75.75 0 00-1.06 1.06L8.94 10l-3.72 3.72a.75.75 0 101.06 1.06L10 11.06l3.72 3.72a.75.75 0 101.06-1.06L11.06 10l3.72-3.72a.75.75 0 00-1.06-1.06L10 8.94 6.28 5.22z" />
            </svg>
        </button>
    </div>

    {{-- ======================================================
         CABECERA

         El título es el ancla de la pantalla y el botón de alta va arriba a la
         derecha, en el ámbar de la marca: es la única acción que crea algo y
         tiene que encontrarse sin leer.
         ====================================================== --}}

    <div class="animate-fade-in flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="font-serif text-3xl font-bold tracking-tight text-slate-900 dark:text-white">
                Temporadas
            </h1>

            <p class="mt-1 text-sm font-medium text-slate-500 dark:text-slate-400">
                Administra los períodos y precios de temporada
            </p>
        </div>

        <button
            type="button"
            wire:click="crear"
            wire:loading.attr="disabled"
            wire:target="crear"
            wire:loading.class="opacity-70"
            class="inline-flex items-center gap-2 self-start rounded-xl bg-amber-500 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition-all duration-300 ease-out hover:scale-[1.02] hover:bg-amber-600 hover:shadow-lg focus:outline-none focus:ring-2 focus:ring-amber-500 focus:ring-offset-2 active:scale-95 disabled:pointer-events-none disabled:opacity-70 sm:self-auto"
        >
            <flux:icon.plus class="size-4" />
            Nueva temporada
        </button>
    </div>

    {{-- ======================================================
         SECCIÓN 1: calendario de temporadas.
         SECCIÓN 2: tabla de gestión con edición y borrado.

         La paleta de demanda se declara aquí y no en las secciones porque Blade
         comparte el ámbito con los parciales: las tarjetas y la tabla no pueden
         teñir el mismo nivel de dos maneras distintas. Es pintura, no regla: la
         regla la aplica `Temporada::nivelDemanda()` y el texto,
         `Temporada::NIVELES_DEMANDA`.
         ====================================================== --}}

    @php
        $tonosDemanda = [
            'baja' => [
                'borde' => 'border-l-emerald-500',
                'fondo' => 'bg-emerald-50',
                'pastilla' => 'bg-emerald-100 text-emerald-700',
                'punto' => 'bg-emerald-500',
            ],
            'media' => [
                'borde' => 'border-l-amber-400',
                'fondo' => 'bg-amber-50',
                'pastilla' => 'bg-amber-100 text-amber-800',
                'punto' => 'bg-amber-400',
            ],
            'alta' => [
                'borde' => 'border-l-rose-500',
                'fondo' => 'bg-rose-50',
                'pastilla' => 'bg-rose-100 text-rose-700',
                'punto' => 'bg-rose-500',
            ],
        ];
    @endphp

    @include('temporadas.partials.cards-view')

    @include('temporadas.partials.table-view')

    @include('temporadas.create')

    @include('temporadas.edit')
</div>