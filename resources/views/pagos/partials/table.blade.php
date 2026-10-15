{{-- ======================================================
     TABLA DE PAGOS
     ====================================================== --}}

<div class="overflow-hidden rounded-2xl border border-slate-200 bg-white dark:border-zinc-800 dark:bg-zinc-900">
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-slate-100 dark:divide-zinc-800">
            <thead class="bg-slate-100 dark:bg-zinc-900/60">
                <tr>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-600 dark:text-slate-400">Reservación</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-600 dark:text-slate-400">Monto</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-600 dark:text-slate-400">Método</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-600 dark:text-slate-400">Referencia</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-600 dark:text-slate-400">Estado</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-600 dark:text-slate-400">Registrado por</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-600 dark:text-slate-400">Fecha de pago</th>
                    <th scope="col" class="px-6 py-3 text-right text-xs font-semibold uppercase tracking-wider text-slate-600 dark:text-slate-400">Acciones</th>
                </tr>
            </thead>

            <tbody class="divide-y divide-slate-100 bg-white dark:divide-zinc-800 dark:bg-zinc-900">
                @forelse ($pagos as $pago)
                    <tr wire:key="pago-fila-{{ $pago->id }}">
                        <td class="px-6 py-4 align-top">
                            <p class="font-bold text-slate-800 dark:text-slate-100">
                                #{{ $pago->reserva_id }} · {{ $pago->reserva?->cliente?->nombreCompleto() ?? 'Sin cliente' }}
                            </p>
                            <p class="text-sm text-slate-400">{{ $pago->notas ?? 'Sin notas' }}</p>
                        </td>
                        <td class="px-6 py-4 align-top font-medium tabular-nums text-slate-700 dark:text-slate-200">
                            ${{ number_format((float) $pago->monto, 2) }}
                        </td>
                        <td class="px-6 py-4 align-top">
                            <span class="inline-flex items-center rounded-full bg-amber-100 px-3 py-1 text-xs font-bold text-amber-800">
                                {{ $pago->metodo_pago }}
                            </span>
                        </td>
                        <td class="px-6 py-4 align-top text-slate-600 dark:text-slate-300">{{ $pago->referencia ?: '—' }}</td>
                        <td class="px-6 py-4 align-top">
                            <x-estado-badge :estado="$pago->estado" />
                        </td>
                        <td class="px-6 py-4 align-top text-slate-600 dark:text-slate-300">
                            {{ $pago->usuario?->name ?? '—' }}
                        </td>
                        <td class="px-6 py-4 align-top text-slate-600 dark:text-slate-300">
                            {{ $pago->fecha_pago?->format('d/m/Y H:i') ?? '—' }}
                        </td>
                        <td class="px-6 py-4 align-top">
                            <div class="flex items-center justify-end gap-2">
                                <button
                                    type="button"
                                    wire:click="editar({{ $pago->id }})"
                                    title="Editar pago"
                                    aria-label="Editar pago #{{ $pago->id }}"
                                    class="inline-flex items-center justify-center rounded-lg p-2 text-slate-400 transition-colors duration-200 hover:bg-blue-50 hover:text-blue-600 focus:outline-none focus:ring-2 focus:ring-blue-500/30 active:scale-95"
                                >
                                    <flux:icon.pencil-square class="size-4" />
                                </button>

                                <button
                                    type="button"
                                    wire:click="eliminar({{ $pago->id }})"
                                    wire:confirm="¿Eliminar este pago?"
                                    title="Eliminar pago"
                                    aria-label="Eliminar pago #{{ $pago->id }}"
                                    class="inline-flex items-center justify-center rounded-lg p-2 text-slate-400 transition-colors duration-200 hover:bg-red-50 hover:text-red-600 focus:outline-none focus:ring-2 focus:ring-red-500/30 active:scale-95"
                                >
                                    <flux:icon.trash class="size-4" />
                                </button>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="px-6 py-12 text-center text-sm text-slate-500">
                            No hay pagos registrados.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
