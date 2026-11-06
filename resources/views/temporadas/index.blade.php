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
    {{-- El alta, la edición, el borrado y el cambio de estado emiten el mismo
         evento `notificacion`, que el mecanismo global convierte en un aviso
         flotante de 3 s. --}}

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