{{-- ======================================================
     RESERVACIONES · MODAL DE CREACIÓN
     Se renderiza solo cuando $showModalCrear es true.
     ====================================================== --}}

@if ($showModalCrear)
    @php
        $etiquetaCampo = 'mb-1.5 block text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400';

        $campo = 'peer w-full rounded-xl border-0 bg-slate-50 py-2.5 pl-11 pr-3.5 text-sm text-slate-900 shadow-sm ring-1 ring-inset ring-slate-200 transition-all duration-300 ease-out placeholder:text-slate-400 hover:bg-slate-100 hover:ring-slate-300 focus:bg-white focus:outline-none focus:ring-2 focus:ring-inset focus:ring-amber-500 dark:bg-zinc-900 dark:text-white dark:ring-zinc-700 dark:hover:bg-zinc-800 dark:hover:ring-zinc-600 dark:focus:bg-zinc-900 dark:focus:ring-amber-500';

        $icono = 'pointer-events-none absolute left-3.5 top-1/2 size-5 -translate-y-1/2 text-slate-400 transition-colors duration-200 peer-focus:text-amber-500 dark:text-slate-500 dark:peer-focus:text-amber-400';

        $clientesOpciones = $this->clientes
            ->mapWithKeys(fn ($cliente) => [$cliente->id => $cliente->nombreCompleto()])
            ->all();

        $habitacionesOpciones = $this->habitacionesDisponibles
            ->mapWithKeys(function ($habitacion): array {
                $tipo = $habitacion->tipoHabitacion;
                $etiqueta = $habitacion->numero_habitacion.' · '.($tipo?->nombre ?? 'Sin tipo').' · $'.number_format((float) ($tipo?->precio_base ?? 0), 2);

                return [$habitacion->id => $etiqueta];
            })
            ->all();

        $resumen = $this->resumenEstadia;
    @endphp

    <div
        class="fixed inset-0 z-50 flex items-center justify-center p-4 sm:p-6"
        x-data="{
            init() { document.documentElement.classList.add('overflow-hidden') },
            destroy() { document.documentElement.classList.remove('overflow-hidden') },
        }"
        x-on:keydown.escape.window="$wire.cerrarModalCrear()"
        role="dialog"
        aria-modal="true"
        aria-labelledby="titulo-modal-crear"
    >
        {{-- Fondo: clic para cerrar --}}
        <div
            class="absolute inset-0 animate-fade-in bg-slate-900/60 backdrop-blur-sm"
            wire:click="cerrarModalCrear"
            aria-hidden="true"
        ></div>

        {{-- Tarjeta --}}
        <div class="relative z-10 w-full max-w-2xl animate-modal-card-in overflow-hidden rounded-2xl bg-white shadow-2xl ring-1 ring-slate-900/5 dark:bg-zinc-900 dark:ring-white/10">
            <form wire:submit="guardarReserva">
                {{-- ---------------------------------------------------
                     ENCABEZADO
                     --------------------------------------------------- --}}
                <div class="flex items-start gap-4 border-b border-slate-100 bg-gradient-to-r from-amber-50/80 to-white px-6 py-5 dark:border-zinc-800 dark:from-amber-950/40 dark:to-zinc-900">
                    <span class="flex size-11 shrink-0 items-center justify-center rounded-xl bg-amber-600 text-white shadow-lg shadow-amber-600/25">
                        <flux:icon.calendar-days class="size-6" />
                    </span>

                    <div class="min-w-0 flex-1">
                        <h2 id="titulo-modal-crear" class="text-lg font-semibold text-slate-900 dark:text-white">
                            Nueva reservación
                        </h2>
                        <p class="mt-0.5 text-sm text-slate-500 dark:text-slate-400">
                            Registra al huésped y asigna una habitación disponible.
                        </p>
                    </div>

                    <button
                        type="button"
                        wire:click="cerrarModalCrear"
                        aria-label="Cerrar"
                        class="-me-2 -mt-1 shrink-0 rounded-lg p-2 text-slate-400 transition-all duration-200 hover:rotate-90 hover:bg-slate-100 hover:text-slate-700 focus:outline-none focus:ring-2 focus:ring-amber-500 dark:hover:bg-zinc-800 dark:hover:text-white"
                    >
                        <flux:icon.x-mark class="size-5" />
                    </button>
                </div>

                {{-- ---------------------------------------------------
                     CUERPO
                     --------------------------------------------------- --}}
                <div
                    wire:loading.class="opacity-60"
                    wire:target="guardarReserva"
                    class="max-h-[70vh] space-y-5 overflow-y-auto px-6 py-6 transition-opacity duration-200"
                >
                    <div class="grid gap-5 sm:grid-cols-2">
                        {{-- Cliente --}}
                        <div class="sm:col-span-2">
                            <label for="cliente_id" class="{{ $etiquetaCampo }}">
                                Cliente <span class="text-red-500">*</span>
                            </label>
                            <x-dropdown
                                id="cliente_id"
                                wire:model="cliente_id"
                                modelo="cliente_id"
                                clave="cliente-{{ $cliente_id }}"
                                variant="soft"
                                required
                                :selected="$cliente_id"
                                placeholder="Busca y selecciona un cliente..."
                                :options="$clientesOpciones"
                                class="w-full"
                            >
                                <x-slot:leadingIcon>
                                    <flux:icon.user-group class="size-5" />
                                </x-slot:leadingIcon>
                            </x-dropdown>
                            <flux:error name="cliente_id" class="mt-1.5" />
                        </div>

                        {{-- Check-in --}}
                        <div>
                            <label for="check_in" class="{{ $etiquetaCampo }}">
                                Check-in <span class="text-red-500">*</span>
                            </label>
                            <div class="relative">
                                <input id="check_in" type="date" wire:model="check_in" required class="{{ $campo }}" />
                                <flux:icon.calendar-days class="{{ $icono }}" />
                            </div>
                            <flux:error name="check_in" class="mt-1.5" />
                        </div>

                        {{-- Check-out --}}
                        <div>
                            <label for="check_out" class="{{ $etiquetaCampo }}">
                                Check-out <span class="text-red-500">*</span>
                            </label>
                            <div class="relative">
                                <input id="check_out" type="date" wire:model="check_out" required class="{{ $campo }}" />
                                <flux:icon.calendar-days class="{{ $icono }}" />
                            </div>
                            <flux:error name="check_out" class="mt-1.5" />
                        </div>

                        {{-- Habitación --}}
                        <div class="sm:col-span-2">
                            <label for="habitacion_id" class="{{ $etiquetaCampo }}">
                                Habitación <span class="text-red-500">*</span>
                            </label>

                            @if ($this->habitacionesDisponibles->isEmpty())
                                <div class="flex items-center gap-2.5 rounded-xl border border-dashed border-slate-300 bg-slate-50 px-4 py-3 text-sm text-slate-500 dark:border-zinc-700 dark:bg-zinc-900/60 dark:text-slate-400">
                                    <flux:icon.information-circle class="size-5 shrink-0 text-slate-400" />
                                    No hay habitaciones disponibles en este momento.
                                </div>
                            @else
                                <x-dropdown
                                    id="habitacion_id"
                                    wire:model="habitacion_id"
                                    modelo="habitacion_id"
                                    clave="habitacion-{{ $habitacion_id }}"
                                    variant="soft"
                                    required
                                    :selected="$habitacion_id"
                                    placeholder="Selecciona una habitación..."
                                    :options="$habitacionesOpciones"
                                    class="w-full"
                                >
                                    <x-slot:leadingIcon>
                                        <flux:icon.home-modern class="size-5" />
                                    </x-slot:leadingIcon>
                                </x-dropdown>
                            @endif
                            <flux:error name="habitacion_id" class="mt-1.5" />
                        </div>

                        {{-- Huéspedes --}}
                        <div>
                            <label for="num_huespedes" class="{{ $etiquetaCampo }}">
                                Huéspedes <span class="text-red-500">*</span>
                            </label>
                            <div class="relative">
                                <input id="num_huespedes" type="number" min="1" wire:model="num_huespedes" required class="{{ $campo }}" />
                                <flux:icon.users class="{{ $icono }}" />
                            </div>
                            <flux:error name="num_huespedes" class="mt-1.5" />
                        </div>

                        {{-- Estado con el que nace la reservación --}}
                        <div>
                            <p class="{{ $etiquetaCampo }}">Estado inicial</p>
                            <div class="relative">
                                <span class="flex w-full items-center rounded-xl bg-emerald-50 py-2.5 pl-11 pr-3.5 text-sm font-semibold text-emerald-700 ring-1 ring-inset ring-emerald-200 dark:bg-emerald-500/10 dark:text-emerald-400 dark:ring-emerald-500/30">
                                    Confirmada
                                </span>
                                <flux:icon.check-circle class="pointer-events-none absolute left-3.5 top-1/2 size-5 -translate-y-1/2 text-emerald-500" />
                            </div>
                        </div>
                    </div>

                    @if ($resumen !== null)
                        <div class="grid grid-cols-3 gap-3 rounded-xl border border-amber-200/70 bg-amber-50/70 px-4 py-3 dark:border-amber-500/20 dark:bg-amber-950/30">
                            <div>
                                <p class="text-[11px] font-semibold uppercase tracking-wide text-amber-700/80 dark:text-amber-300/80">Noches</p>
                                <p class="mt-0.5 text-lg font-semibold text-slate-900 dark:text-white">{{ $resumen['noches'] }}</p>
                            </div>
                            <div>
                                <p class="text-[11px] font-semibold uppercase tracking-wide text-amber-700/80 dark:text-amber-300/80">Precio / noche</p>
                                <p class="mt-0.5 text-lg font-semibold text-slate-900 dark:text-white">${{ number_format($resumen['precio'], 2) }}</p>
                            </div>
                            <div class="text-right">
                                <p class="text-[11px] font-semibold uppercase tracking-wide text-amber-700/80 dark:text-amber-300/80">Monto total</p>
                                <p class="mt-0.5 text-lg font-semibold text-amber-700 dark:text-amber-400">${{ number_format($resumen['total'], 2) }}</p>
                            </div>
                        </div>
                    @endif
                </div>

                {{-- ---------------------------------------------------
                     ACCIONES
                     --------------------------------------------------- --}}
                <div class="flex items-center justify-end gap-3 border-t border-slate-100 bg-slate-50/70 px-6 py-4 dark:border-zinc-800 dark:bg-zinc-900/60">
                    <button
                        type="button"
                        wire:click="cerrarModalCrear"
                        class="inline-flex items-center justify-center rounded-xl px-5 py-2.5 text-sm font-semibold text-slate-600 transition-all duration-200 hover:bg-slate-100 hover:text-slate-900 focus:outline-none focus:ring-2 focus:ring-amber-500 active:scale-95 dark:text-slate-300 dark:hover:bg-zinc-800 dark:hover:text-white"
                    >
                        Cancelar
                    </button>

                    <button
                        type="submit"
                        wire:loading.attr="disabled"
                        wire:target="guardarReserva"
                        class="inline-flex items-center justify-center gap-2 rounded-xl bg-amber-600 px-5 py-2.5 text-sm font-semibold text-white shadow-md shadow-amber-600/25 transition-all duration-200 ease-out hover:-translate-y-0.5 hover:bg-amber-700 hover:shadow-lg focus:outline-none focus:ring-2 focus:ring-amber-600 focus:ring-offset-2 active:translate-y-0 active:scale-95 disabled:pointer-events-none disabled:opacity-70 disabled:shadow-none disabled:hover:translate-y-0 disabled:hover:bg-amber-600 disabled:hover:shadow-md"
                    >
                        <span wire:loading.remove wire:target="guardarReserva">
                            <flux:icon.check class="size-4" />
                        </span>
                        <span wire:loading wire:target="guardarReserva" class="flex items-center gap-2">
                            <flux:icon.arrow-path class="size-4 animate-spin" />
                            Guardando...
                        </span>
                        <span wire:loading.remove wire:target="guardarReserva">Guardar reservación</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
@endif
