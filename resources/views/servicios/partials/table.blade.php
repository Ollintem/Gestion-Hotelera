{{-- ======================================================
     DATA TABLE MODERNA (LISTA DE SERVICIOS)
     ====================================================== --}}

<div class="bg-white rounded-2xl border border-slate-100 overflow-hidden">
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-slate-50">
            <thead class="bg-slate-50/50">
                <tr>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-bold uppercase tracking-wider text-slate-400">
                        SERVICIO
                    </th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-bold uppercase tracking-wider text-slate-400">
                        CATEGORÍA
                    </th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-bold uppercase tracking-wider text-slate-400">
                        PRECIO (MXN)
                    </th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-bold uppercase tracking-wider text-slate-400">
                        CARGOS
                    </th>
                    <th scope="col" class="px-6 py-3 text-right text-xs font-bold uppercase tracking-wider text-slate-400">
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
                            <span class="inline-flex items-center px-3 py-1 rounded-full bg-orange-50 text-orange-600 text-xs font-semibold">
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
                                    class="inline-flex size-9 items-center justify-center rounded-lg border border-slate-200 bg-white text-slate-600 shadow-sm transition-all duration-200 hover:bg-slate-50 hover:text-slate-900 hover:shadow-sm focus:outline-none focus:ring-2 focus:ring-slate-500/10 active:scale-95"
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
                                    class="inline-flex size-9 items-center justify-center rounded-lg border border-slate-200 bg-white text-slate-600 shadow-sm transition-all duration-200 hover:bg-slate-50 hover:text-slate-900 hover:shadow-sm focus:outline-none focus:ring-2 focus:ring-slate-500/10 active:scale-95"
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
                                    wire:click="eliminar({{ $servicio->id }})"
                                    wire:confirm="¿Eliminar este servicio?"
                                    title="Eliminar servicio"
                                    aria-label="Eliminar servicio {{ $servicio->nombre }}"
                                    class="inline-flex size-9 items-center justify-center rounded-lg border border-slate-200 bg-white text-red-500 shadow-sm transition-all duration-200 hover:bg-red-50 hover:text-red-600 hover:shadow-sm focus:outline-none focus:ring-2 focus:ring-red-500/10 active:scale-95"
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
