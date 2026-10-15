{{-- ======================================================
     PANEL DE ESTADO DE CUENTA

     Resumen financiero de la reservación seleccionada: total de
     hospedaje más consumos, lo abonado y el saldo pendiente, que
     se resalta para que recepción lo lea de un vistazo.
     ====================================================== --}}

@php
    $selectClass = 'w-full appearance-none rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-800 shadow-sm transition-all duration-200 focus:border-amber-500 focus:outline-none focus:ring-2 focus:ring-amber-500/20 dark:border-zinc-700 dark:bg-zinc-900 dark:text-white cursor-pointer';

    $reserva = $this->reserva;
    $resumen = $this->resumen;

    $reservasOpciones = $reservas->mapWithKeys(fn ($item) => [
        $item->id => '#'.$item->id.' · '.($item->cliente?->nombreCompleto() ?? 'Sin cliente').' · '.$item->estado,
    ])->all();
@endphp

<section class="mb-6 overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
    <div class="flex flex-col gap-4 border-b border-slate-100 bg-slate-50/70 px-6 py-5 sm:flex-row sm:items-end sm:justify-between dark:border-zinc-800 dark:bg-zinc-900/60">
        <div class="min-w-0">
            <flux:heading size="lg" class="!font-semibold !text-slate-800 dark:!text-white">Estado de cuenta</flux:heading>
            <flux:subheading class="!font-medium !text-slate-500 dark:!text-slate-400">
                Selecciona un folio para consultar su resumen financiero.
            </flux:subheading>
        </div>

        <div class="w-full sm:w-80">
            <label for="reservaSeleccionadaId" class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">
                Reservación
            </label>

            <div class="relative">
                <select
                    id="reservaSeleccionadaId"
                    wire:model.live="reservaSeleccionadaId"
                    class="{{ $selectClass }}"
                >
                    <option value="">Selecciona un folio…</option>
                    @foreach ($reservasOpciones as $valor => $etiqueta)
                        <option value="{{ $valor }}">{{ $etiqueta }}</option>
                    @endforeach
                </select>
                <svg class="pointer-events-none absolute right-3 top-1/2 size-4 -translate-y-1/2 text-slate-400" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                    <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 11.168l3.71-3.938a.75.75 0 111.08 1.04l-4.25 4.51a.75.75 0 01-1.08 0l-4.25-4.51a.75.75 0 01.02-1.06z" clip-rule="evenodd" />
                </svg>
            </div>
        </div>
    </div>

    @if ($reserva !== null && $resumen !== null)
        <div class="grid gap-6 px-6 py-6 lg:grid-cols-3">
            {{-- Resumen financiero --}}
            <div class="lg:col-span-2">
                <dl class="grid gap-4 sm:grid-cols-3">
                    <div class="rounded-xl border border-slate-200 bg-white px-4 py-3.5 shadow-sm dark:border-zinc-700 dark:bg-zinc-800">
                        <dt class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Total hospedaje + consumos</dt>
                        <dd class="mt-1 text-xl font-bold tabular-nums text-slate-900 dark:text-white">
                            ${{ number_format($resumen['total'], 2) }}
                        </dd>
                    </div>

                    <div class="rounded-xl border border-slate-200 bg-white px-4 py-3.5 shadow-sm dark:border-zinc-700 dark:bg-zinc-800">
                        <dt class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Total abonado</dt>
                        <dd class="mt-1 text-xl font-bold tabular-nums text-emerald-600 dark:text-emerald-400">
                            ${{ number_format($resumen['pagado'], 2) }}
                        </dd>
                    </div>

                    <div class="rounded-xl border px-4 py-3.5 shadow-sm {{ $resumen['estaPagada'] ? 'border-emerald-200 bg-emerald-50 dark:border-emerald-500/40 dark:bg-emerald-500/10' : 'border-amber-200 bg-amber-50 dark:border-amber-500/40 dark:bg-amber-500/10' }}">
                        <dt class="text-[10px] font-bold uppercase tracking-wider {{ $resumen['estaPagada'] ? 'text-emerald-600 dark:text-emerald-300' : 'text-amber-600 dark:text-amber-300' }}">
                            Saldo pendiente
                        </dt>
                        <dd class="mt-1 text-xl font-extrabold tabular-nums {{ $resumen['estaPagada'] ? 'text-emerald-700 dark:text-emerald-300' : 'text-amber-700 dark:text-amber-300' }}">
                            ${{ number_format($resumen['pendiente'], 2) }}
                        </dd>
                    </div>
                </dl>

                {{-- Desglose de conceptos --}}
                <dl class="mt-4 divide-y divide-slate-100 overflow-hidden rounded-xl border border-slate-200 text-sm dark:divide-zinc-700 dark:border-zinc-700">
                    <div class="flex items-center justify-between gap-4 px-4 py-3">
                        <dt class="text-slate-500 dark:text-slate-400">Tarifa de habitación</dt>
                        <dd class="font-medium tabular-nums text-slate-800 dark:text-slate-200">${{ number_format($resumen['tarifa'], 2) }}</dd>
                    </div>
                    <div class="flex items-center justify-between gap-4 px-4 py-3">
                        <dt class="text-slate-500 dark:text-slate-400">Consumos extras</dt>
                        <dd class="font-medium tabular-nums text-slate-800 dark:text-slate-200">${{ number_format($resumen['extras'], 2) }}</dd>
                    </div>
                    <div class="flex items-center justify-between gap-4 px-4 py-3">
                        <dt class="text-slate-500 dark:text-slate-400">Abonado</dt>
                        <dd class="font-medium tabular-nums text-emerald-600 dark:text-emerald-400">${{ number_format($resumen['pagado'], 2) }}</dd>
                    </div>
                    <div class="flex items-center justify-between gap-4 bg-slate-50/70 px-4 py-3 dark:bg-zinc-900/50">
                        <dt class="font-semibold text-slate-700 dark:text-slate-200">Saldo pendiente</dt>
                        <dd class="font-bold tabular-nums {{ $resumen['estaPagada'] ? 'text-emerald-600 dark:text-emerald-400' : 'text-amber-600 dark:text-amber-400' }}">
                            ${{ number_format($resumen['pendiente'], 2) }}
                        </dd>
                    </div>
                </dl>

                <div class="mt-4 flex flex-wrap items-center gap-3">
                    <button
                        type="button"
                        wire:click="crear"
                        class="inline-flex items-center justify-center gap-2 rounded-xl bg-amber-600 px-5 py-2.5 text-sm font-semibold text-white shadow-md shadow-amber-600/25 transition-all duration-200 ease-out hover:-translate-y-0.5 hover:bg-amber-700 hover:shadow-lg focus:outline-none focus:ring-2 focus:ring-amber-600 focus:ring-offset-2 active:translate-y-0 active:scale-95"
                    >
                        <flux:icon.plus class="size-4" />
                        Registrar pago
                    </button>

                    <a
                        href="{{ route('pagos.estado-cuenta', $reserva->id) }}"
                        target="_blank"
                        rel="noopener"
                        class="inline-flex items-center justify-center gap-2 rounded-xl border border-slate-200 bg-white px-5 py-2.5 text-sm font-semibold text-slate-700 shadow-sm transition-all duration-200 hover:bg-slate-50 focus:outline-none focus:ring-2 focus:ring-amber-500/20 dark:border-zinc-700 dark:bg-zinc-800 dark:text-slate-200 dark:hover:bg-zinc-700"
                    >
                        <flux:icon.document-arrow-down class="size-4" />
                        Descargar estado de cuenta
                    </a>
                </div>

                @if ($resumen['estaPagada'])
                    <p class="mt-3 inline-flex items-center gap-2 rounded-lg bg-emerald-50 px-3 py-2 text-xs font-semibold text-emerald-700 dark:bg-emerald-500/10 dark:text-emerald-300">
                        <flux:icon.check-circle class="size-4" />
                        Cuenta liquidada. Listo para check-out.
                    </p>
                @endif
            </div>

            {{-- Abonos del folio --}}
            <div class="lg:col-span-1">
                <div class="overflow-hidden rounded-xl border border-slate-200 dark:border-zinc-700">
                    <div class="border-b border-slate-100 bg-slate-50/70 px-4 py-3 dark:border-zinc-700 dark:bg-zinc-900/60">
                        <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Abonos registrados</p>
                    </div>

                    <div class="max-h-72 divide-y divide-slate-100 overflow-y-auto dark:divide-zinc-700">
                        @forelse ($reserva->pagos->sortByDesc('id') as $abono)
                            <div class="flex items-start justify-between gap-3 px-4 py-3">
                                <div class="min-w-0">
                                    <p class="truncate text-sm font-medium text-slate-800 dark:text-slate-200">
                                        {{ $abono->metodo_pago }}
                                    </p>
                                    <p class="text-xs text-slate-500 dark:text-slate-400">
                                        {{ $abono->fecha_pago?->format('d/m/Y H:i') ?? 'Sin fecha' }}
                                        @if ($abono->referencia)
                                            · Ref. {{ $abono->referencia }}
                                        @endif
                                    </p>
                                </div>
                                <div class="shrink-0 text-right">
                                    <span class="block text-sm font-semibold tabular-nums text-slate-800 dark:text-slate-100">
                                        ${{ number_format((float) $abono->monto, 2) }}
                                    </span>
                                    <x-estado-badge :estado="$abono->estado" />
                                </div>
                            </div>
                        @empty
                            <p class="px-4 py-6 text-center text-xs text-slate-500 dark:text-slate-400">
                                Todavía no hay abonos en este folio.
                            </p>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    @else
        <div class="flex flex-col items-center gap-3 px-6 py-14 text-center">
            <span class="flex size-14 items-center justify-center rounded-2xl bg-slate-50 text-amber-600 ring-1 ring-slate-200 dark:bg-zinc-800 dark:ring-zinc-700">
                <flux:icon.document-text class="size-6" />
            </span>
            <p class="text-sm font-semibold text-slate-700 dark:text-slate-200">Selecciona una reservación</p>
            <p class="text-xs text-slate-500 dark:text-slate-400">
                Aquí verás el total consumido, los abonos y el saldo pendiente del folio.
            </p>
        </div>
    @endif
</section>
