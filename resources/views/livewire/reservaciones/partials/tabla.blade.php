{{-- ======================================================
     RESERVACIONES · ESQUELETO DE LA TABLA
     ====================================================== --}}

<div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm dark:border-zinc-700 dark:bg-slate-800">
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-slate-200 dark:divide-zinc-700">
            <thead class="bg-slate-50 dark:bg-zinc-900/60">
                <tr>
                    @php
                        $clasesEncabezado = 'px-4 py-3.5 text-[11px] font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400';
                    @endphp

                    <th scope="col" class="{{ $clasesEncabezado }} text-left">Código</th>
                    <th scope="col" class="{{ $clasesEncabezado }} text-left">Cliente</th>
                    <th scope="col" class="{{ $clasesEncabezado }} text-left">Habitación</th>
                    <th scope="col" class="{{ $clasesEncabezado }} text-left">Check-in</th>
                    <th scope="col" class="{{ $clasesEncabezado }} text-left">Check-out</th>
                    <th scope="col" class="{{ $clasesEncabezado }} text-center">Huéspedes</th>
                    <th scope="col" class="{{ $clasesEncabezado }} text-right">Total</th>
                    <th scope="col" class="{{ $clasesEncabezado }} text-left">Estado</th>
                    <th scope="col" class="{{ $clasesEncabezado }} text-left">Pago</th>
                    <th scope="col" class="{{ $clasesEncabezado }} text-right">Acciones</th>
                </tr>
            </thead>

            <tbody class="divide-y divide-slate-100 dark:divide-zinc-700/60">
                @forelse ($reservas as $reserva)
                    @include('livewire.reservaciones.partials.fila-reserva', ['reserva' => $reserva])
                @empty
                    <tr>
                        <td colspan="10" class="px-6 py-16 text-center">
                            <span class="mx-auto flex size-12 items-center justify-center rounded-full bg-slate-100 dark:bg-zinc-700">
                                <flux:icon.calendar-days class="size-6 text-slate-400 dark:text-zinc-300" />
                            </span>
                            <h3 class="mt-4 text-base font-semibold text-slate-800 dark:text-zinc-200">
                                Sin reservaciones
                            </h3>
                            <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">
                                Aún no hay reservaciones registradas.
                            </p>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@if ($reservas->hasPages())
    <div class="mt-6">
        {{ $reservas->links() }}
    </div>
@endif
