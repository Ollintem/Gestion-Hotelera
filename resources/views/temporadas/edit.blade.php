{{-- ======================================================
     MODAL: EDITAR TEMPORADA

     Comparte el formulario con el alta a través de
     `partials/form-fields.blade.php`: lo que se ve aquí es exactamente lo que se
     valida al guardar.

     Lo único que no comparte con el alta es la banda de resumen del periodo que se
     está tocando. Se lee del registro que el contenedor ya cargó al abrir el
     modal, no de una consulta nueva, y evita el error más caro de este módulo:
     corregir la temporada equivocada.
     ====================================================== --}}

@php($prefijoIds = 'editar-')

<div
    x-show="abierto && pagina === 'editar'"
    x-cloak
    x-on:click.self="abierto = false; $wire.cerrarModal()"
    class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4 backdrop-blur-sm"
    role="dialog"
    aria-modal="true"
    aria-labelledby="temporada-editar-titulo"
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
            <div class="flex shrink-0 items-start justify-between gap-4 border-b border-gray-100 px-6 py-5">
                <div class="flex items-center gap-3">
                    <span class="flex size-10 shrink-0 items-center justify-center rounded-xl bg-amber-500/15">
                        <flux:icon.pencil-square class="size-5 text-amber-600" />
                    </span>

                    <div class="min-w-0">
                        <h2 id="temporada-editar-titulo" class="font-serif text-xl font-bold tracking-tight text-gray-900">
                            Editar temporada
                        </h2>

                        <p class="mt-0.5 truncate text-sm font-medium text-gray-500">
                            {{ $nombre }} · {{ $fecha_inicio }} → {{ $fecha_fin }}
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
                        Guardar cambios
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