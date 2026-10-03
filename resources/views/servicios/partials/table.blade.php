{{-- ======================================================
     DATA TABLE MODERNA (LISTA DE SERVICIOS)

     El partial es además el anfitrión del diálogo de confirmación: cada
     botón de la basura publica el servicio con `$dispatch` y el diálogo
     llama a `eliminar()` de Livewire solo cuando se confirma. Se evita
     `wire:confirm` porque su diálogo es el del navegador y no admite el
     nombre del servicio ni el estilo del módulo.
     ====================================================== --}}

<div
    x-data="{
        pendiente: null,
        confirmarEliminacion() {
            if (this.pendiente === null) {
                return;
            }

            $wire.eliminar(this.pendiente.id);

            this.pendiente = null;
        },
    }"
    x-on:eliminar-servicio.window="pendiente = $event.detail"
>

<div class="bg-white rounded-2xl border border-slate-100 overflow-hidden">
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-slate-50">
            <thead class="bg-slate-100">
                <tr>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-600">
                        SERVICIO
                    </th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-600">
                        CATEGORÍA
                    </th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-600">
                        PRECIO (MXN)
                    </th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-600">
                        CARGOS
                    </th>
                    <th scope="col" class="px-6 py-3 text-right text-xs font-semibold uppercase tracking-wider text-slate-600">
                        ACCIONES
                    </th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-50 bg-white">
                @forelse ($servicios as $servicio)
                    <tr wire:key="servicio-tabla-{{ $servicio->id }}">
                        <td class="px-6 py-4 whitespace-normal align-top">
                            <p class="text-slate-800 font-bold">
                                {{ $servicio->nombre }}
                            </p>
                            <p class="text-sm text-slate-400">
                                {{ $servicio->descripcion ?? 'Sin descripción' }}
                            </p>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap align-top">
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-amber-100 text-amber-800">
                                {{ $servicio->clasificacion?->nombre ?? 'Sin categoría' }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap align-top text-slate-700 font-medium">
                            ${{ number_format($servicio->precio, 2) }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap align-top text-slate-700 font-medium">
                            {{ $servicio->reservas_servicio_count }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap align-top">
                            <div class="flex items-center justify-end gap-2">
                                <button
                                    type="button"
                                    wire:click="editar({{ $servicio->id }})"
                                    title="Editar servicio"
                                    aria-label="Editar servicio {{ $servicio->nombre }}"
                                    class="inline-flex items-center justify-center rounded-lg p-2 text-gray-400 transition-colors duration-200 hover:bg-amber-50 hover:text-amber-600 focus:outline-none focus:ring-2 focus:ring-amber-500/30 active:scale-95"
                                >
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="size-4">
                                        <path d="M5.433 13.917l1.262-3.155A4 4 0 017.58 9.42l6.92-6.918a2.121 2.121 0 013 3l-6.92 6.918c-.383.383-.84.685-1.343.886l-3.154 1.262a.5.5 0 01-.65-.65z" />
                                        <path d="M3.5 5.75c0-.69.56-1.25 1.25-1.25H10A.75.75 0 0010 3H4.75A2.75 2.75 0 002 5.75v9.5A2.75 2.75 0 004.75 18h9.5A2.75 2.75 0 0017 15.25V10a.75.75 0 00-1.5 0v5.25c0 .69-.56 1.25-1.25 1.25h-9.5c-.69 0-1.25-.56-1.25-1.25v-9.5z" />
                                    </svg>
                                </button>

                                <button
                                    type="button"
                                    wire:click="alternarActivo({{ $servicio->id }})"
                                    title="{{ $servicio->activo ? 'Desactivar servicio' : 'Activar servicio' }}"
                                    aria-label="{{ $servicio->activo ? 'Desactivar servicio' : 'Activar servicio' }} {{ $servicio->nombre }}"
                                    class="inline-flex items-center justify-center rounded-lg p-2 transition-colors duration-200 focus:outline-none focus:ring-2 active:scale-95 {{ $servicio->activo ? 'text-gray-400 hover:bg-gray-100 hover:text-gray-600 focus:ring-gray-300/40' : 'text-emerald-500 hover:bg-emerald-50 hover:text-emerald-600 focus:ring-emerald-500/30' }}"
                                >
                                    @if ($servicio->activo)
                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="size-4">
                                            <path fill-rule="evenodd" d="M10 2a8 8 0 100 16 8 8 0 000-16zM7.75 8.75a.75.75 0 000 1.5h4.5a.75.75 0 000-1.5h-4.5z" clip-rule="evenodd" />
                                        </svg>
                                    @else
                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="size-4">
                                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm.75-11.25a.75.75 0 00-1.5 0v2.5h-2.5a.75.75 0 000 1.5h2.5v2.5a.75.75 0 001.5 0v-2.5h2.5a.75.75 0 000-1.5h-2.5v-2.5z" clip-rule="evenodd" />
                                        </svg>
                                    @endif
                                </button>

                                <button
                                    type="button"
                                    data-id="{{ $servicio->id }}"
                                    data-nombre="{{ $servicio->nombre }}"
                                    x-on:click="$dispatch('eliminar-servicio', { id: $event.currentTarget.dataset.id, nombre: $event.currentTarget.dataset.nombre })"
                                    title="Eliminar servicio"
                                    aria-label="Eliminar servicio {{ $servicio->nombre }}"
                                    class="inline-flex items-center justify-center rounded-lg p-2 text-gray-400 transition-colors duration-200 hover:bg-red-50 hover:text-red-600 focus:outline-none focus:ring-2 focus:ring-red-500/30 active:scale-95"
                                >
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="size-4">
                                        <path fill-rule="evenodd" d="M8.75 1A2.75 2.75 0 006 3.75v.443c-.795.077-1.584.176-2.365.298a.75.75 0 10.23 1.482l.149-.022.841 10.518A2.75 2.75 0 007.596 19h4.807a2.75 2.75 0 002.742-2.53l.841-10.52.149.023a.75.75 0 00.23-1.482A41.03 41.03 0 0014 4.193V3.75A2.75 2.75 0 0011.25 1h-2.5zM10 4c.84 0 1.673.025 2.5.075V3.75c0-.69-.56-1.25-1.25-1.25h-2.5c-.69 0-1.25.56-1.25 1.25v.325C8.327 4.025 9.16 4 10 4zM8.58 7.72a.75.75 0 00-1.5.06l.3 7.5a.75.75 0 101.5-.06l-.3-7.5zm4.34.06a.75.75 0 10-1.5-.06l-.3 7.5a.75.75 0 101.5.06l.3-7.5z" clip-rule="evenodd" />
                                    </svg>
                                </button>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-6 py-12 text-center text-sm text-slate-500">
                            No hay servicios registrados.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if ($servicios->hasPages())
        <div class="border-t border-slate-50 bg-slate-50/40 px-6 py-3">
            {{ $servicios->links() }}
        </div>
    @endif
</div>

{{-- ======================================================
     DIÁLOGO DE CONFIRMACIÓN

     Vive fuera de la tarjeta para no quedar recortado por su
     `overflow-hidden`. El aviso de latencia cero del contenedor
     monta en `z-50`, así que este diálogo sube a `z-[60]` para
     quedar por encima.
     ====================================================== --}}

<div
    x-show="pendiente !== null"
    x-cloak
    x-on:keydown.escape.window="pendiente = null"
    x-on:click.self="pendiente = null"
    class="fixed inset-0 z-[60] flex items-center justify-center bg-slate-900/60 p-4 backdrop-blur-sm"
    role="dialog"
    aria-modal="true"
    aria-labelledby="confirmar-eliminacion-titulo"
    x-transition:enter="transition ease-out duration-150"
    x-transition:enter-start="opacity-0"
    x-transition:enter-end="opacity-100"
    x-transition:leave="transition ease-in duration-150"
    x-transition:leave-start="opacity-100"
    x-transition:leave-end="opacity-0"
>
    <div
        x-show="pendiente !== null"
        class="w-full max-w-md rounded-2xl bg-white p-6 shadow-2xl"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0 scale-95"
        x-transition:enter-end="opacity-100 scale-100"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100 scale-100"
        x-transition:leave-end="opacity-0 scale-95"
    >
        <div class="flex items-start gap-4">
            <span class="inline-flex size-10 shrink-0 items-center justify-center rounded-full bg-red-50 text-red-600">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="size-5">
                    <path fill-rule="evenodd" d="M8.75 1A2.75 2.75 0 006 3.75v.443c-.795.077-1.584.176-2.365.298a.75.75 0 10.23 1.482l.149-.022.841 10.518A2.75 2.75 0 007.596 19h4.807a2.75 2.75 0 002.742-2.53l.841-10.52.149.023a.75.75 0 00.23-1.482A41.03 41.03 0 0014 4.193V3.75A2.75 2.75 0 0011.25 1h-2.5zM10 4c.84 0 1.673.025 2.5.075V3.75c0-.69-.56-1.25-1.25-1.25h-2.5c-.69 0-1.25.56-1.25 1.25v.325C8.327 4.025 9.16 4 10 4zM8.58 7.72a.75.75 0 00-1.5.06l.3 7.5a.75.75 0 101.5-.06l-.3-7.5zm4.34.06a.75.75 0 10-1.5-.06l-.3 7.5a.75.75 0 101.5.06l.3-7.5z" clip-rule="evenodd" />
                </svg>
            </span>

            <div class="min-w-0 flex-1">
                <h3 id="confirmar-eliminacion-titulo" class="text-base font-semibold text-gray-900">
                    Eliminar servicio
                </h3>

                <p class="mt-1 text-sm leading-relaxed text-gray-600">
                    ¿Eliminar <span x-text="pendiente?.nombre" class="font-semibold text-gray-900"></span>?
                    Esta acción es permanente y no se puede deshacer.
                </p>
            </div>
        </div>

        <div class="mt-6 flex items-center justify-end gap-3">
            <button
                type="button"
                x-on:click="pendiente = null"
                class="inline-flex items-center justify-center rounded-lg px-4 py-2 text-sm font-medium text-gray-500 transition-colors duration-200 hover:bg-gray-100 hover:text-gray-700 focus:outline-none focus:ring-2 focus:ring-gray-300"
            >
                Cancelar
            </button>

            <button
                type="button"
                x-on:click="confirmarEliminacion()"
                class="inline-flex items-center justify-center gap-2 rounded-lg bg-red-600 px-4 py-2 text-sm font-medium text-white shadow-md transition-all duration-200 hover:bg-red-700 hover:shadow-lg focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 active:scale-95"
            >
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="size-4">
                    <path fill-rule="evenodd" d="M8.75 1A2.75 2.75 0 006 3.75v.443c-.795.077-1.584.176-2.365.298a.75.75 0 10.23 1.482l.149-.022.841 10.518A2.75 2.75 0 007.596 19h4.807a2.75 2.75 0 002.742-2.53l.841-10.52.149.023a.75.75 0 00.23-1.482A41.03 41.03 0 0014 4.193V3.75A2.75 2.75 0 0011.25 1h-2.5zM10 4c.84 0 1.673.025 2.5.075V3.75c0-.69-.56-1.25-1.25-1.25h-2.5c-.69 0-1.25.56-1.25 1.25v.325C8.327 4.025 9.16 4 10 4zM8.58 7.72a.75.75 0 00-1.5.06l.3 7.5a.75.75 0 101.5-.06l-.3-7.5zm4.34.06a.75.75 0 10-1.5-.06l-.3 7.5a.75.75 0 101.5.06l.3-7.5z" clip-rule="evenodd" />
                </svg>

                Sí, eliminar
            </button>
        </div>
    </div>
</div>

</div>
