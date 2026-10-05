{{-- ======================================================
     TABLA DE GESTIÓN DE TEMPORADAS

     Donde se consulta y se corrige: el precio base, el factor y el precio que de
     verdad se cobra van en columnas propias porque son los tres números con los
     que recepción razona.

     El estado es un interruptor dentro de una píldora clicable: alterna entre
     activa e inactiva sin salir del listado, que es donde se consulta el precio.

     El borrado pide confirmación en el propio botón con `wire:confirm`: para una
     operación de un solo clic y destructiva es más honesto que una pantalla
     intermedia que además hay que construir y mantener.
     ====================================================== --}}

<div class="mt-8">
    <h2 class="font-serif text-lg font-bold tracking-tight text-slate-800 dark:text-white">
        Gestión de temporadas
    </h2>

    <div class="mt-4 overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-100">
                <thead class="bg-slate-100">
                    <tr>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-600">
                            Temporada
                        </th>

                        <th scope="col" class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-600">
                            Fecha inicio
                        </th>

                        <th scope="col" class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-600">
                            Fecha fin
                        </th>

                        <th scope="col" class="px-6 py-3 text-center text-xs font-semibold uppercase tracking-wider text-slate-600">
                            Multiplicador
                        </th>

                        <th scope="col" class="px-6 py-3 text-right text-xs font-semibold uppercase tracking-wider text-slate-600">
                            Precio base
                        </th>

                        <th scope="col" class="px-6 py-3 text-right text-xs font-semibold uppercase tracking-wider text-slate-600">
                            Precio efectivo
                        </th>

                        <th scope="col" class="px-6 py-3 text-center text-xs font-semibold uppercase tracking-wider text-slate-600">
                            Estado
                        </th>

                        <th scope="col" class="px-6 py-3 text-right text-xs font-semibold uppercase tracking-wider text-slate-600">
                            Acciones
                        </th>
                    </tr>
                </thead>

                <tbody class="divide-y divide-slate-100 bg-white">
                    @forelse ($temporadas as $temporada)
                        @php($tono = $tonosDemanda[$temporada->nivelDemanda()])

                        <tr
                            wire:key="temporada-fila-{{ $temporada->id }}"
                            class="border-l-4 transition-colors duration-200 hover:bg-slate-50 {{ $tono['borde'] }} {{ $temporada->activo ? '' : 'opacity-70' }}"
                        >
                            <td class="whitespace-nowrap px-6 py-4 align-top">
                                <p class="font-bold text-slate-800">
                                    {{ $temporada->nombre }}
                                </p>

                                <p class="mt-1.5 inline-flex items-center gap-1.5 rounded-full px-2.5 py-1 text-[11px] font-bold {{ $tono['pastilla'] }}">
                                    <span class="inline-flex size-1.5 shrink-0 rounded-full {{ $tono['punto'] }}"></span>
                                    {{ $temporada->etiquetaNivelDemanda() }}
                                </p>
                            </td>

                            <td class="whitespace-nowrap px-6 py-4 align-top font-medium text-slate-700 tabular-nums">
                                {{ $temporada->fecha_inicio->format('Y-m-d') }}
                            </td>

                            <td class="whitespace-nowrap px-6 py-4 align-top font-medium text-slate-700 tabular-nums">
                                {{ $temporada->fecha_fin->format('Y-m-d') }}
                            </td>

                            <td class="whitespace-nowrap px-6 py-4 text-center align-top">
                                <span class="inline-flex items-center rounded-full bg-amber-100 px-3 py-1 text-xs font-bold text-amber-800 tabular-nums">
                                    {{ $temporada->multiplicadorEnTexto() }}
                                </span>
                            </td>

                            <td class="whitespace-nowrap px-6 py-4 text-right align-top font-medium text-slate-700 tabular-nums">
                                {{ $temporada->precioBaseEnPesos() }}
                            </td>

                            <td class="whitespace-nowrap px-6 py-4 text-right align-top">
                                <span class="block text-base font-bold tabular-nums {{ $temporada->activo ? 'text-amber-700' : 'text-slate-500' }}">
                                    {{ $temporada->precioEfectivoEnPesos() }}
                                </span>

                                @if ($temporada->variacionPrecio() !== 0.0)
                                    <span @class([
                                        'mt-1 inline-flex items-center rounded-full px-2 py-0.5 text-[11px] font-bold',
                                        'bg-emerald-50 text-emerald-700' => $temporada->variacionPrecio() < 0,
                                        'bg-rose-50 text-rose-700' => $temporada->variacionPrecio() > 0,
                                    ])>
                                        {{ $temporada->variacionPrecio() > 0 ? '+' : '' }}{{ number_format($temporada->variacionPrecio(), 0) }}%
                                    </span>
                                @endif
                            </td>

                            <td class="whitespace-nowrap px-6 py-4 align-top">
                                <button
                                    type="button"
                                    wire:click="alternarActivo({{ $temporada->id }})"
                                    wire:loading.attr="disabled"
                                    wire:target="alternarActivo({{ $temporada->id }})"
                                    title="{{ $temporada->activo ? 'Desactivar temporada' : 'Activar temporada' }}"
                                    aria-label="{{ $temporada->activo ? 'Desactivar' : 'Activar' }} {{ $temporada->nombre }}"
                                    aria-pressed="{{ $temporada->activo ? 'true' : 'false' }}"
                                    class="inline-flex items-center gap-1.5 rounded-full px-3 py-1 text-xs font-bold transition-all duration-300 ease-out hover:scale-105 focus:outline-none focus:ring-2 active:scale-95 {{ $temporada->activo ? 'bg-emerald-100 text-emerald-800 hover:bg-emerald-200 focus:ring-emerald-500/30' : 'bg-slate-100 text-slate-500 hover:bg-slate-200 hover:text-slate-700 focus:ring-slate-400/30' }}"
                                >
                                    <span class="relative inline-flex h-5 w-9 shrink-0 rounded-full transition-colors duration-300 ease-in-out {{ $temporada->activo ? 'bg-emerald-500' : 'bg-slate-300 dark:bg-slate-600' }}">
                                        <span class="inline-block size-4 rounded-full bg-white shadow-sm transition-transform duration-300 ease-in-out {{ $temporada->activo ? 'translate-x-4' : 'translate-x-0.5' }}"></span>
                                    </span>

                                    {{ $temporada->activo ? 'Activa' : 'Inactiva' }}
                                </button>
                            </td>

                            <td class="whitespace-nowrap px-6 py-4 align-top">
                                <div class="flex items-center justify-end gap-2">
                                    <button
                                        type="button"
                                        wire:click="editar({{ $temporada->id }})"
                                        wire:loading.attr="disabled"
                                        wire:target="editar({{ $temporada->id }})"
                                        title="Editar temporada"
                                        aria-label="Editar temporada {{ $temporada->nombre }}"
                                        class="inline-flex items-center justify-center rounded-lg p-2 text-gray-400 transition-colors duration-200 hover:bg-amber-50 hover:text-amber-600 focus:outline-none focus:ring-2 focus:ring-amber-500/30 active:scale-95"
                                    >
                                        <flux:icon.pencil-square class="size-4" />
                                    </button>

                                    <button
                                        type="button"
                                        wire:click="eliminar({{ $temporada->id }})"
                                        wire:confirm="¿Eliminar la temporada «{{ $temporada->nombre }}»? Esta acción no se puede deshacer."
                                        wire:loading.attr="disabled"
                                        wire:target="eliminar({{ $temporada->id }})"
                                        title="Eliminar temporada"
                                        aria-label="Eliminar temporada {{ $temporada->nombre }}"
                                        class="inline-flex items-center justify-center rounded-lg p-2 text-gray-400 transition-colors duration-200 hover:bg-red-50 hover:text-red-600 focus:outline-none focus:ring-2 focus:ring-red-500/30 active:scale-95"
                                    >
                                        <flux:icon.trash class="size-4" />
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-6 py-12 text-center">
                                <span class="mx-auto mb-3 flex size-12 items-center justify-center rounded-full bg-slate-100 text-slate-400">
                                    <flux:icon.calendar-days class="size-6" />
                                </span>

                                <p class="text-sm font-semibold text-slate-700">No hay temporadas registradas.</p>
                                <p class="mt-1 text-sm text-slate-500">Crea la primera para empezar a aplicar precios por periodo.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>