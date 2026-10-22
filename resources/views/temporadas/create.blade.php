{{-- ======================================================
     MODAL: NUEVA TEMPORADA

     Modal completo —velo, tarjeta, encabezado, formulario y botones— porque es la
     ventana que el usuario negocia tal cual. La animación va en dos niveles: el
     velo se desvanece y la tarjeta entra con una escala corta.

     El `x-show` depende de `abierto` y de `pagina` —las dos entrelazadas con
     Livewire en `temporadas/index.blade.php`— porque esta vista y la de editar
     conviven en el DOM: montarlas siempre es lo que permite animar la salida.

     `$prefijoIds` separa los identificadores de los campos de los de la ventana
     de edición. Sin ese prefijo los dos formularios repetirían `id` y el `for` de
     cada etiqueta apuntaría al input de la ventana oculta.
     ====================================================== --}}

@php($prefijoIds = 'nueva-')

<div
    x-show="abierto && pagina === 'crear'"
    x-cloak
    x-on:click.self="abierto = false; $wire.cerrarModal()"
    class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4 backdrop-blur-sm"
    role="dialog"
    aria-modal="true"
    aria-labelledby="temporada-nueva-titulo"
    x-transition:enter="ease-out duration-300"
    x-transition:enter-start="opacity-0"
    x-transition:enter-end="opacity-100"
    x-transition:leave="ease-in duration-200"
    x-transition:leave-start="opacity-100"
    x-transition:leave-end="opacity-0"
>
    <div
        class="flex max-h-[90vh] w-full max-w-2xl flex-col overflow-hidden rounded-2xl bg-white shadow-2xl ring-1 ring-gray-900/5"
        x-transition:enter="ease-out duration-300"
        x-transition:enter-start="opacity-0 scale-95 translate-y-4"
        x-transition:enter-end="opacity-100 scale-100 translate-y-0"
        x-transition:leave="ease-in duration-200"
        x-transition:leave-start="opacity-100 scale-100 translate-y-0"
        x-transition:leave-end="opacity-0 scale-95 translate-y-4"
    >
        <form wire:submit="guardar" class="flex min-h-0 flex-col">
            {{-- El encabezado se separa del cuerpo para poder quedar fijo: en
                 pantallas bajas, sin esto el botón de cerrar se iría con el
                 desplazamiento. --}}

            <div class="flex shrink-0 items-start justify-between gap-4 border-b border-gray-100 px-6 py-5">
                <div class="flex items-center gap-3">
                    <span class="flex size-10 shrink-0 items-center justify-center rounded-xl bg-amber-500/15">
                        <flux:icon.sun class="size-5 text-amber-600" />
                    </span>

                    <div class="min-w-0">
                        <h2 id="temporada-nueva-titulo" class="font-serif text-xl font-bold tracking-tight text-gray-900">
                            Nueva temporada
                        </h2>

                        <p class="mt-0.5 text-sm font-medium text-gray-500">
                            Define el período, su multiplicador y su precio base.
                        </p>
                    </div>
                </div>

                <button
                    type="button"
                    wire:click="cerrarModal"
                    aria-label="Cerrar"
                    title="Cerrar"
                    class="shrink-0 rounded-lg p-1.5 text-gray-400 transition-all duration-200 ease-out hover:bg-gray-100 hover:text-gray-600 focus:outline-none focus:ring-2 focus:ring-amber-500/40 active:scale-90"
                >
                    <flux:icon.x-mark class="size-5" />
                </button>
            </div>

            {{-- `min-h-0` es lo que permite que el cuerpo en vez de dejar que el
                 contenido desborde la tarjeta en pantallas bajas. --}}

            <div class="min-h-0 flex-1 overflow-y-auto px-6 py-6">
                @include('temporadas.partials.form-fields')
            </div>

            <div class="flex shrink-0 items-center justify-end gap-3 border-t border-gray-100 bg-gray-50/80 px-6 py-4">
                <button
                    type="button"
                    wire:click="cerrarModal"
                    class="inline-flex items-center justify-center rounded-lg border border-gray-200 bg-white px-4 py-2 text-sm font-semibold text-gray-600 shadow-sm transition-all duration-200 hover:bg-gray-50 hover:shadow-md hover:scale-[1.02] focus:outline-none focus:ring-2 focus:ring-gray-300 active:scale-95"
                >
                    Cancelar
                </button>

                <button
                    type="submit"
                    class="inline-flex items-center justify-center gap-2 rounded-lg bg-amber-500 px-4 py-2 text-sm font-semibold text-white shadow-md transition-all duration-200 hover:bg-amber-600 hover:shadow-lg hover:scale-[1.02] focus:outline-none focus:ring-2 focus:ring-amber-500 focus:ring-offset-2 active:scale-95"
                >
                    <span wire:loading.remove wire:target="guardar" class="inline-flex items-center gap-2">
                        <flux:icon.check class="size-4" />
                        Guardar temporada
                    </span>

                    <span wire:loading wire:target="guardar" class="inline-flex items-center gap-2">
                        <span class="size-4 animate-spin rounded-full border-2 border-white/40 border-t-white"></span>
                        Guardando...
                    </span>
                </button>
            </div>
        </form>
    </div>
</div>