{{-- ======================================================
     CARGO DE UN CONSUMO AL FOLIO DE UNA HABITACIÓN OCUPADA

     Lo usa recepción. El folio se elige primero para que el
     resumen de la derecha muestre el estado de cuenta real
     antes y después de cargar el consumo.
     ====================================================== --}}

@php
    $campoBase = 'w-full rounded-xl border-0 border-transparent bg-slate-50 py-2.5 text-sm text-slate-900 shadow-sm ring-1 ring-inset ring-slate-200 transition-all duration-200 ease-out placeholder:text-slate-400 hover:bg-slate-100 hover:ring-slate-300 focus:border-transparent focus:bg-white focus:ring-2 focus:ring-inset focus:ring-amber-500 dark:bg-zinc-900 dark:text-white dark:ring-zinc-700 dark:hover:bg-zinc-800 dark:hover:ring-zinc-600 dark:focus:bg-zinc-900 dark:focus:ring-amber-500';
    $campo = $campoBase.' pl-11 pr-3.5';
    $icono = 'pointer-events-none absolute left-3.5 top-1/2 size-5 -translate-y-1/2 text-slate-400 transition-colors duration-200 peer-focus:text-amber-500 dark:text-slate-500 dark:peer-focus:text-amber-400';
    $etiqueta = 'mb-1.5 block text-xs font-semibold uppercase tracking-wide text-slate-500 transition-colors duration-200 dark:text-slate-400';
    $obligatorio = '<span class="text-red-500 font-extrabold text-sm ml-0.5">*</span>';

    /*
     | Los folios se identifican por su número, las habitaciones ocupadas y el
     | huésped: es como recepción los reconoce en el mostrador.
     */
    $reservasOpciones = $this->reservas->mapWithKeys(function ($reserva): array {
        $habitaciones = $reserva->habitacionesAsignadas
            ->pluck('habitacion.numero_habitacion')
            ->filter()
            ->map(fn (string $numero): string => '#'.$numero)
            ->join(', ');

        return [$reserva->id => 'Folio #'.$reserva->id
            .' · '.$reserva->cliente?->nombreCompleto()
            .($habitaciones !== '' ? ' · '.$habitaciones : '')];
    })->all();

    $serviciosOpciones = $this->servicios->mapWithKeys(fn ($servicio) => [
        $servicio->id => $servicio->clasificacion?->nombre.' · '.$servicio->nombre.' — $'.number_format((float) $servicio->precio, 2),
    ])->all();

    $empleadosOpciones = $this->empleados->mapWithKeys(fn ($empleado) => [
        $empleado->id_empleado => trim($empleado->nombre.' '.$empleado->apellidos).' — '.$empleado->puesto,
    ])->all();

    $resumen = $this->resumen;
@endphp

<form wire:submit="guardar" class="w-full">
    <div class="w-full animate-modal-card-in overflow-hidden rounded-2xl bg-white shadow-2xl ring-1 ring-slate-900/5 dark:bg-zinc-900 dark:ring-white/10">
        {{-- Encabezado --}}
        <div class="flex items-start gap-4 border-b border-slate-100 bg-gradient-to-r from-amber-50/80 to-white px-6 py-5 dark:border-zinc-800 dark:from-amber-950/40 dark:to-zinc-900">
            <span class="flex size-11 shrink-0 items-center justify-center rounded-xl bg-amber-600 text-white shadow-lg shadow-amber-600/25">
                <flux:icon.receipt-percent class="size-5" />
            </span>

            <div class="min-w-0 flex-1">
                <h2 class="text-lg font-semibold tracking-tight text-slate-900 dark:text-white">Cargar consumo al folio</h2>
                <p class="mt-0.5 text-sm text-slate-500 dark:text-slate-400">
                    El cargo se suma de inmediato al total de check-out del huésped.
                </p>
            </div>

            <button
                type="button"
                x-data
                x-on:click="$dispatch('modal-close', { name: 'servicio-cargo' })"
                aria-label="Cerrar"
                class="-me-2 -mt-1 shrink-0 rounded-lg p-2 text-slate-400 transition-all duration-200 hover:rotate-90 hover:bg-slate-100 hover:text-slate-700 focus:outline-none focus:ring-2 focus:ring-amber-500 dark:hover:bg-zinc-800 dark:hover:text-white"
            >
                <flux:icon.x-mark class="size-5" />
            </button>
        </div>

        <div
            wire:loading.class="opacity-60"
            wire:target="guardar"
            class="max-h-[70vh] overflow-y-auto px-6 py-6 transition-opacity duration-200"
        >
            @if ($this->reservas->isEmpty())
                <div class="flex flex-col items-center gap-3 rounded-2xl border-2 border-dashed border-slate-300 bg-slate-50 px-6 py-12 text-center dark:border-zinc-700 dark:bg-zinc-900/60">
                    <span class="flex size-14 items-center justify-center rounded-2xl bg-white text-amber-600 shadow-sm ring-1 ring-slate-200 dark:bg-zinc-800 dark:ring-zinc-700">
                        <flux:icon.key class="size-6" />
                    </span>
                    <p class="text-sm font-semibold text-slate-700 dark:text-slate-200">No hay habitaciones ocupadas</p>
                    <p class="text-xs text-slate-500 dark:text-slate-400">
                        Los consumos se cargan sobre reservaciones con check-in realizado.
                    </p>
                </div>
            @else
                <div class="grid gap-6 lg:grid-cols-2">
                    {{-- ---------------------------------------------------
                         CONSUMO: qué se cargó, a qué folio y a cargo de quién
                         --------------------------------------------------- --}}
                    <div class="space-y-5">
                        <div>
                            <label for="reserva_id" class="{{ $etiqueta }}">Folio de la habitación {!! $obligatorio !!}</label>
                            <x-dropdown
                                id="reserva_id"
                                wire:model="reserva_id"
                                :clave="'reserva_id-'.$reserva_id"
                                variant="soft"
                                required
                                :selected="$reserva_id"
                                placeholder="Selecciona un folio..."
                                :options="$reservasOpciones"
                                class="w-full"
                            >
                                <x-slot:leadingIcon>
                                    <flux:icon.key class="size-5" />
                                </x-slot:leadingIcon>
                            </x-dropdown>
                            <flux:error name="reserva_id" class="mt-1.5" />
                        </div>

                        <div>
                            <label for="servicio_id" class="{{ $etiqueta }}">Servicio consumido {!! $obligatorio !!}</label>
                            <x-dropdown
                                id="servicio_id"
                                wire:model="servicio_id"
                                :clave="'servicio_id-'.$servicio_id"
                                variant="soft"
                                required
                                :selected="$servicio_id"
                                placeholder="Selecciona un servicio..."
                                :options="$serviciosOpciones"
                                class="w-full"
                            >
                                <x-slot:leadingIcon>
                                    <flux:icon.sparkles class="size-5" />
                                </x-slot:leadingIcon>
                            </x-dropdown>
                            <flux:error name="servicio_id" class="mt-1.5" />
                        </div>

                        <div class="grid gap-5 sm:grid-cols-2">
                            <div>
                                <label for="cantidad" class="{{ $etiqueta }}">Cantidad {!! $obligatorio !!}</label>
                                <div class="relative">
                                    <input
                                        id="cantidad"
                                        type="number"
                                        min="1"
                                        max="99"
                                        wire:model="cantidad"
                                        required
                                        class="{{ $campo }} peer"
                                    />
                                    <flux:icon.hashtag class="{{ $icono }}" />
                                </div>
                                <flux:error name="cantidad" class="mt-1.5" />
                            </div>

                            <div>
                                <label for="precio_aplicado" class="{{ $etiqueta }}">Precio aplicado (MXN) {!! $obligatorio !!}</label>
                                <div class="relative">
                                    <input
                                        id="precio_aplicado"
                                        type="number"
                                        step="0.01"
                                        min="0.01"
                                        wire:model="precio_aplicado"
                                        placeholder="0.00"
                                        required
                                        class="{{ $campo }} peer"
                                    />
                                    <flux:icon.banknotes class="{{ $icono }}" />
                                </div>
                                <flux:error name="precio_aplicado" class="mt-1.5" />
                            </div>
                        </div>

                        <div>
                            <label for="empleado_id" class="{{ $etiqueta }}">Empleado responsable {!! $obligatorio !!}</label>
                            <x-dropdown
                                id="empleado_id"
                                wire:model="empleado_id"
                                :clave="'empleado_id-'.$empleado_id"
                                variant="soft"
                                required
                                :selected="$empleado_id"
                                placeholder="Selecciona un empleado..."
                                :options="$empleadosOpciones"
                                class="w-full"
                            >
                                <x-slot:leadingIcon>
                                    <flux:icon.user class="size-5" />
                                </x-slot:leadingIcon>
                            </x-dropdown>
                            <flux:error name="empleado_id" class="mt-1.5" />
                        </div>

                        {{-- Importe del cargo en curso, antes de confirmarlo. --}}
                        <div class="flex items-center justify-between gap-4 rounded-xl bg-slate-50 px-4 py-3.5 ring-1 ring-inset ring-slate-200 dark:bg-zinc-900 dark:ring-zinc-700">
                            <span class="flex items-center gap-2 text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">
                                <flux:icon.calculator class="size-4" />
                                Importe del cargo
                            </span>
                            <span class="text-lg font-bold tabular-nums text-slate-900 dark:text-white">
                                ${{ number_format($this->subtotalPropuesto, 2) }}
                            </span>
                        </div>
                    </div>

                    {{-- ---------------------------------------------------
                         ESTADO DE CUENTA del folio seleccionado
                         --------------------------------------------------- --}}
                    <div>
                        @if ($resumen)
                            <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm dark:border-zinc-700 dark:bg-zinc-800">
                                <div class="border-b border-slate-100 bg-slate-50/80 px-5 py-4 dark:border-zinc-700 dark:bg-zinc-900/60">
                                    <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Folio #{{ $this->folio->id }}</p>
                                    <p class="mt-1 text-sm font-semibold text-slate-900 dark:text-white">
                                        {{ $this->folio->cliente?->nombreCompleto() }}
                                    </p>
                                    <p class="mt-0.5 text-xs text-slate-500 dark:text-slate-400">
                                        {{ $this->folio->check_in->format('d/m/Y') }} — {{ $this->folio->check_out->format('d/m/Y') }}
                                    </p>
                                </div>

                                <dl class="divide-y divide-slate-100 text-sm dark:divide-zinc-700">
                                    <div class="flex items-center justify-between gap-4 px-5 py-3">
                                        <dt class="text-slate-500 dark:text-slate-400">Tarifa de habitación</dt>
                                        <dd class="font-medium tabular-nums text-slate-800 dark:text-slate-200">${{ number_format($resumen['tarifa'], 2) }}</dd>
                                    </div>
                                    <div class="flex items-center justify-between gap-4 px-5 py-3">
                                        <dt class="text-slate-500 dark:text-slate-400">Consumos extras</dt>
                                        <dd class="font-medium tabular-nums text-slate-800 dark:text-slate-200">${{ number_format($resumen['extras'], 2) }}</dd>
                                    </div>
                                    <div class="flex items-center justify-between gap-4 bg-slate-50/70 px-5 py-3 dark:bg-zinc-900/60">
                                        <dt class="font-semibold text-slate-700 dark:text-slate-200">Total de check-out</dt>
                                        <dd class="text-base font-bold tabular-nums text-slate-900 dark:text-white">${{ number_format($resumen['total'], 2) }}</dd>
                                    </div>
                                    <div class="flex items-center justify-between gap-4 px-5 py-3">
                                        <dt class="text-slate-500 dark:text-slate-400">Abonado</dt>
                                        <dd class="font-medium tabular-nums text-emerald-600">${{ number_format($resumen['pagado'], 2) }}</dd>
                                    </div>
                                    <div class="flex items-center justify-between gap-4 px-5 py-3">
                                        <dt class="font-semibold text-slate-700 dark:text-slate-200">Saldo pendiente</dt>
                                        <dd class="font-bold tabular-nums text-amber-600">${{ number_format($resumen['pendiente'], 2) }}</dd>
                                    </div>
                                </dl>

                                {{-- Cargos ya registrados en el folio. --}}
                                <div class="border-t border-slate-100 bg-slate-50/50 px-5 py-4 dark:border-zinc-700 dark:bg-zinc-900/40">
                                    <p class="mb-2.5 text-[10px] font-bold uppercase tracking-wider text-slate-400">Cargos del folio</p>

                                    @forelse ($this->folio->serviciosAsignados->sortByDesc('id') as $cargo)
                                        <div class="flex items-start justify-between gap-3 border-b border-slate-100 py-2 last:border-0 dark:border-zinc-700">
                                            <div class="min-w-0">
                                                <p class="truncate text-sm font-medium text-slate-800 dark:text-slate-200">
                                                    {{ $cargo->servicio?->nombre }}
                                                </p>
                                                <p class="text-xs text-slate-500 dark:text-slate-400">
                                                    {{ $cargo->cantidad }} × ${{ number_format($cargo->precioUnitario(), 2) }}
                                                    @if ($cargo->empleado)
                                                        · {{ $cargo->empleado->nombre }}
                                                    @endif
                                                </p>
                                            </div>
                                            <span class="shrink-0 text-sm font-semibold tabular-nums text-slate-700 dark:text-slate-200">
                                                ${{ number_format((float) $cargo->subtotal, 2) }}
                                            </span>
                                        </div>
                                    @empty
                                        <p class="py-1 text-xs text-slate-500 dark:text-slate-400">
                                            Todavía no hay consumos extras en este folio.
                                        </p>
                                    @endforelse
                                </div>
                            </div>
                        @else
                            <div class="flex h-full min-h-[18rem] flex-col items-center justify-center gap-3 rounded-2xl border-2 border-dashed border-slate-300 bg-slate-50 px-6 py-12 text-center dark:border-zinc-700 dark:bg-zinc-900/60">
                                <span class="flex size-14 items-center justify-center rounded-2xl bg-white text-amber-600 shadow-sm ring-1 ring-slate-200 dark:bg-zinc-800 dark:ring-zinc-700">
                                    <flux:icon.document-text class="size-6" />
                                </span>
                                <p class="text-sm font-semibold text-slate-700 dark:text-slate-200">Selecciona un folio</p>
                                <p class="text-xs text-slate-500 dark:text-slate-400">
                                    Aquí verás el estado de cuenta de la habitación y el efecto del cargo.
                                </p>
                            </div>
                        @endif
                    </div>
                </div>
            @endif
        </div>

        {{-- Acciones --}}
        <div class="flex items-center justify-end gap-3 border-t border-slate-100 bg-slate-50/70 px-6 py-4 dark:border-zinc-800 dark:bg-zinc-900/60">
            <button
                type="button"
                x-data
                x-on:click="$dispatch('modal-close', { name: 'servicio-cargo' })"
                class="inline-flex items-center justify-center rounded-xl px-5 py-2.5 text-sm font-semibold text-slate-600 transition-all duration-200 hover:bg-slate-100 hover:text-slate-900 focus:outline-none focus:ring-2 focus:ring-amber-500 active:scale-95 dark:text-slate-300 dark:hover:bg-zinc-800 dark:hover:text-white"
            >
                Cancelar
            </button>

            <button
                type="submit"
                wire:loading.attr="disabled"
                wire:target="guardar"
                @disabled($this->reservas->isEmpty())
                class="inline-flex items-center justify-center gap-2 rounded-xl bg-amber-600 px-5 py-2.5 text-sm font-semibold text-white shadow-md shadow-amber-600/25 transition-all duration-200 ease-out hover:-translate-y-0.5 hover:bg-amber-700 hover:shadow-lg focus:outline-none focus:ring-2 focus:ring-amber-600 focus:ring-offset-2 active:translate-y-0 active:scale-95 disabled:pointer-events-none disabled:opacity-70 disabled:shadow-none disabled:hover:translate-y-0 disabled:hover:bg-amber-600 disabled:hover:shadow-md"
            >
                <span wire:loading.remove wire:target="guardar">
                    <flux:icon.receipt-percent class="size-4" />
                </span>
                <span wire:loading wire:target="guardar" class="flex items-center gap-2">
                    <flux:icon.arrow-path class="size-4 animate-spin" />
                    Cargando...
                </span>
                <span wire:loading.remove wire:target="guardar">Cargar al folio</span>
            </button>
        </div>
    </div>
</form>
