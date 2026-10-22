{{-- ======================================================
     MODAL: CONFIRMACIÓN DE CHECK-IN / CHECK-OUT

     Muestra la ficha de la reservación y su estado de cuenta
     en modo lectura antes de ejecutar el movimiento. El
     check-out se bloquea mientras quede saldo por liquidar.
     ====================================================== --}}

@php
    $etiqueta = 'mb-1.5 block text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400';
    $campo = 'w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-800 shadow-sm transition-all duration-200 placeholder:text-slate-400 focus:border-amber-500 focus:outline-none focus:ring-2 focus:ring-amber-500/20 dark:border-zinc-700 dark:bg-zinc-900 dark:text-white';
    $filaDato = 'flex items-baseline justify-between gap-4 py-1.5';

    $reserva = $reservaSeleccionada;
    $resumen = $resumenCuenta;
    $esCheckIn = $accion === 'checkin';
    $tieneSaldoPendiente = $resumen !== null && $resumen['pendiente'] > 0;
    $fueraDeRango = false;

    if ($reserva !== null) {
        $fechaProgramada = $esCheckIn ? $reserva->check_in : $reserva->check_out;
        $fueraDeRango = $fechaProgramada?->format('Y-m-d') !== now()->toDateString();
    }
@endphp

<flux:modal
    name="checkin-checkout-confirmacion"
    wire:model="mostrarModal"
    class="w-full max-w-xl rounded-2xl shadow-2xl"
>
    @if ($reserva !== null)
        <div class="mb-6">
            <flux:heading size="lg" class="!font-semibold !text-slate-900 dark:!text-white">
                {{ $esCheckIn ? 'Confirmar check-in' : 'Confirmar check-out' }}
            </flux:heading>
            <flux:subheading class="!font-medium !text-slate-500 dark:!text-slate-400">
                {{ $esCheckIn
                    ? 'Se registrará la llegada real del huésped y sus habitaciones pasarán a ocupadas.'
                    : 'Se registrará la salida real del huésped y sus habitaciones pasarán a limpieza.' }}
            </flux:subheading>
        </div>

        <div class="space-y-5">
            {{-- Ficha de la reservación --}}
            <div class="rounded-xl border border-slate-200 bg-slate-50/70 px-4 py-3 dark:border-zinc-700 dark:bg-zinc-900/60">
                <div class="{{ $filaDato }}">
                    <span class="{{ $etiqueta }} !mb-0">Folio</span>
                    <span class="text-sm font-semibold text-slate-900 dark:text-white">#{{ $reserva->id }}</span>
                </div>
                <div class="{{ $filaDato }}">
                    <span class="{{ $etiqueta }} !mb-0">Huésped</span>
                    <span class="text-sm font-semibold text-slate-900 dark:text-white">{{ $reserva->cliente?->nombreCompleto() ?? '—' }}</span>
                </div>
                <div class="{{ $filaDato }}">
                    <span class="{{ $etiqueta }} !mb-0">Identificación</span>
                    <span class="text-sm text-slate-700 dark:text-slate-300">
                        @if ($reserva->cliente?->tipo_identificacion && $reserva->cliente?->numero_identificacion)
                            {{ $reserva->cliente->tipo_identificacion }} {{ $reserva->cliente->numero_identificacion }}
                        @else
                            No registrada
                        @endif
                    </span>
                </div>
                <div class="{{ $filaDato }}">
                    <span class="{{ $etiqueta }} !mb-0">Habitaciones</span>
                    <span class="text-sm text-slate-700 dark:text-slate-300">{{ $reserva->habitaciones->pluck('numero_habitacion')->join(', ') ?: '—' }}</span>
                </div>
                <div class="{{ $filaDato }}">
                    <span class="{{ $etiqueta }} !mb-0">{{ $esCheckIn ? 'Llegada programada' : 'Salida programada' }}</span>
                    <span class="text-sm text-slate-700 dark:text-slate-300">
                        {{ ($esCheckIn ? $reserva->check_in : $reserva->check_out)->format('d/m/Y') }}
                    </span>
                </div>
                <div class="{{ $filaDato }}">
                    <span class="{{ $etiqueta }} !mb-0">Noches</span>
                    <span class="text-sm text-slate-700 dark:text-slate-300">{{ (int) $reserva->totalNoches() }}</span>
                </div>
                <div class="{{ $filaDato }}">
                    <span class="{{ $etiqueta }} !mb-0">Estado</span>
                    <x-estado-badge :estado="$reserva->estado" />
                </div>
            </div>

            {{-- Aviso: movimiento fuera del día programado --}}
            @if ($fueraDeRango)
                <flux:callout variant="warning" icon="exclamation-triangle">
                    <p>
                        La fecha programada para este movimiento es
                        <strong>{{ ($esCheckIn ? $reserva->check_in : $reserva->check_out)->format('d/m/Y') }}</strong>
                        y hoy es <strong>{{ now()->format('d/m/Y') }}</strong>.
                        {{ $esCheckIn ? 'Se registrará como llegada fuera de fecha.' : 'Se registrará como salida fuera de fecha.' }}
                    </p>
                </flux:callout>
            @endif

            {{-- Estado de cuenta: solo lectura --}}
            @if ($resumen !== null)
                <div>
                    <p class="{{ $etiqueta }}">Estado de cuenta</p>
                    <div class="rounded-xl border border-slate-200 px-4 py-2 dark:border-zinc-700">
                        <div class="{{ $filaDato }}">
                            <span class="text-sm text-slate-600 dark:text-slate-400">Tarifa de habitaciones</span>
                            <span class="text-sm font-medium text-slate-800 dark:text-slate-200">${{ number_format($resumen['tarifa'], 2) }}</span>
                        </div>
                        <div class="{{ $filaDato }}">
                            <span class="text-sm text-slate-600 dark:text-slate-400">Servicios adicionales</span>
                            <span class="text-sm font-medium text-slate-800 dark:text-slate-200">${{ number_format($resumen['extras'], 2) }}</span>
                        </div>
                        <div class="{{ $filaDato }} border-t border-slate-100 dark:border-zinc-800">
                            <span class="text-sm font-semibold text-slate-700 dark:text-slate-300">Total</span>
                            <span class="text-sm font-semibold text-slate-900 dark:text-white">${{ number_format($resumen['total'], 2) }}</span>
                        </div>
                        <div class="{{ $filaDato }}">
                            <span class="text-sm text-slate-600 dark:text-slate-400">Pagado</span>
                            <span class="text-sm font-medium text-emerald-600 dark:text-emerald-400">${{ number_format($resumen['pagado'], 2) }}</span>
                        </div>
                        <div class="{{ $filaDato }} border-t border-slate-100 dark:border-zinc-800">
                            <span class="text-sm font-semibold text-slate-700 dark:text-slate-300">Saldo pendiente</span>
                            <span @class([
                                'text-sm font-bold',
                                'text-rose-600 dark:text-rose-400' => $tieneSaldoPendiente,
                                'text-emerald-600 dark:text-emerald-400' => ! $tieneSaldoPendiente,
                            ])>
                                ${{ number_format($resumen['pendiente'], 2) }}
                            </span>
                        </div>
                    </div>
                </div>
            @endif

            {{-- Bloqueo: check-out sin liquidar --}}
            @if (! $esCheckIn && $tieneSaldoPendiente)
                <flux:callout variant="danger" icon="lock-closed">
                    <p>
                        No se puede registrar el check-out mientras quede saldo por liquidar.
                        Registra el pago en el módulo de <strong>Pagos</strong> y vuelve a intentarlo.
                    </p>
                </flux:callout>
            @endif

            {{-- Observaciones opcionales --}}
            <div>
                <label for="observaciones" class="{{ $etiqueta }}">
                    Observaciones {{ $esCheckIn ? 'de llegada' : 'de salida' }} (opcional)
                </label>
                <textarea
                    id="observaciones"
                    wire:model="observaciones"
                    rows="3"
                    maxlength="500"
                    placeholder="{{ $esCheckIn ? 'Ej. llegó antes de la hora, se asignó habitación lista…' : 'Ej. sin novedades, huésped dejó la llave en recepción…' }}"
                    class="{{ $campo }} resize-none"
                ></textarea>
                <flux:error name="observaciones" class="mt-1.5" />
            </div>
        </div>

        <div class="mt-8 flex items-center justify-end gap-3">
            <button
                type="button"
                wire:click="cerrarModal"
                class="inline-flex items-center justify-center rounded-xl px-5 py-2.5 text-sm font-semibold text-slate-600 transition-all duration-200 hover:bg-slate-100 hover:text-slate-900 focus:outline-none focus:ring-2 focus:ring-slate-300 active:scale-95 dark:text-slate-300 dark:hover:bg-zinc-800 dark:hover:text-white"
            >
                Cancelar
            </button>

            <button
                type="button"
                wire:click="{{ $esCheckIn ? 'confirmarCheckIn' : 'confirmarCheckOut' }}"
                wire:loading.attr="disabled"
                wire:target="{{ $esCheckIn ? 'confirmarCheckIn' : 'confirmarCheckOut' }}"
                @disabled(! $esCheckIn && $tieneSaldoPendiente)
                @class([
                    'inline-flex items-center justify-center gap-2 rounded-xl px-5 py-2.5 text-sm font-semibold text-white shadow-md transition-all duration-200 ease-out focus:outline-none focus:ring-2 active:translate-y-0 active:scale-95 disabled:pointer-events-none disabled:opacity-60',
                    'bg-emerald-600 shadow-emerald-600/25 hover:-translate-y-0.5 hover:bg-emerald-700 hover:shadow-lg focus:ring-emerald-600 focus:ring-offset-2' => $esCheckIn,
                    'bg-blue-600 shadow-blue-600/25 hover:-translate-y-0.5 hover:bg-blue-700 hover:shadow-lg focus:ring-blue-600 focus:ring-offset-2' => ! $esCheckIn,
                ])
            >
                <span wire:loading.remove wire:target="{{ $esCheckIn ? 'confirmarCheckIn' : 'confirmarCheckOut' }}" class="inline-flex items-center gap-2">
                    @if (! $esCheckIn && $tieneSaldoPendiente)
                        <flux:icon.lock-closed class="size-4" />
                    @else
                        <flux:icon.check class="size-4" />
                    @endif
                    {{ $esCheckIn ? 'Confirmar llegada' : 'Confirmar salida' }}
                </span>
                <span wire:loading wire:target="{{ $esCheckIn ? 'confirmarCheckIn' : 'confirmarCheckOut' }}">Registrando…</span>
            </button>
        </div>
    @endif
</flux:modal>
