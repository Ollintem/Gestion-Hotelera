{{-- ======================================================
     MODAL: REGISTRAR / EDITAR PAGO

     Los <select> son nativos y se estilan con Tailwind, sin
     componente de por medio: así el valor viaja al backend en el
     mismo ciclo de Livewire y no se desincroniza con el botón.
     ====================================================== --}}

@php
    $campo = 'w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-800 shadow-sm transition-all duration-200 placeholder:text-slate-400 focus:border-amber-500 focus:outline-none focus:ring-2 focus:ring-amber-500/20 dark:border-zinc-700 dark:bg-zinc-900 dark:text-white';
    $selectClass = 'w-full appearance-none rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-800 shadow-sm transition-all duration-200 focus:border-amber-500 focus:outline-none focus:ring-2 focus:ring-amber-500/20 dark:border-zinc-700 dark:bg-zinc-900 dark:text-white cursor-pointer';
    $etiqueta = 'mb-1.5 block text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400';

    $reservasOpciones = $reservas->mapWithKeys(fn ($item) => [
        $item->id => '#'.$item->id.' · '.($item->cliente?->nombreCompleto() ?? 'Sin cliente').' · '.$item->estado,
    ])->all();
@endphp

<flux:modal
    name="pago-form"
    wire:model="mostrarModal"
    class="w-full max-w-lg rounded-2xl shadow-2xl"
>
    <form wire:submit="guardar">
        <div class="mb-6">
            <flux:heading size="lg" class="!font-semibold !text-slate-900 dark:!text-white">
                {{ $pagoId ? 'Editar pago' : 'Registrar pago' }}
            </flux:heading>
            <flux:subheading class="!font-medium !text-slate-500 dark:!text-slate-400">
                El abono se descuenta del saldo pendiente del folio.
            </flux:subheading>
        </div>

        <div class="space-y-5">
            {{-- Reservación --}}
            <div>
                <label for="reserva_id" class="{{ $etiqueta }}">Reservación</label>
                <div class="relative">
                    <select id="reserva_id" wire:model="reserva_id" required class="{{ $selectClass }}">
                        <option value="">Selecciona un folio…</option>
                        @foreach ($reservasOpciones as $valor => $etiquetaOpcion)
                            <option value="{{ $valor }}">{{ $etiquetaOpcion }}</option>
                        @endforeach
                    </select>
                    <svg class="pointer-events-none absolute right-3 top-1/2 size-4 -translate-y-1/2 text-slate-400" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                        <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 11.168l3.71-3.938a.75.75 0 111.08 1.04l-4.25 4.51a.75.75 0 01-1.08 0l-4.25-4.51a.75.75 0 01.02-1.06z" clip-rule="evenodd" />
                    </svg>
                </div>
                <flux:error name="reserva_id" class="mt-1.5" />
            </div>

            <div class="grid gap-5 sm:grid-cols-2">
                {{-- Monto --}}
                <div>
                    <label for="monto" class="{{ $etiqueta }}">Monto (MXN)</label>
                    <input
                        id="monto"
                        type="number"
                        step="0.01"
                        min="0.01"
                        wire:model="monto"
                        placeholder="0.00"
                        required
                        class="{{ $campo }}"
                    />
                    <flux:error name="monto" class="mt-1.5" />
                </div>

                {{-- Método de pago --}}
                <div>
                    <label for="metodo_pago" class="{{ $etiqueta }}">Método de pago</label>
                    <div class="relative">
                        <select id="metodo_pago" wire:model.live="metodo_pago" required class="{{ $selectClass }}">
                            @foreach ($metodos as $metodo)
                                <option value="{{ $metodo }}">{{ $metodo }}</option>
                            @endforeach
                        </select>
                        <svg class="pointer-events-none absolute right-3 top-1/2 size-4 -translate-y-1/2 text-slate-400" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                            <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 11.168l3.71-3.938a.75.75 0 111.08 1.04l-4.25 4.51a.75.75 0 01-1.08 0l-4.25-4.51a.75.75 0 01.02-1.06z" clip-rule="evenodd" />
                        </svg>
                    </div>
                    <flux:error name="metodo_pago" class="mt-1.5" />
                </div>
            </div>

            {{-- Referencia / Folio: solo para tarjeta o transferencia --}}
            <div
                x-data
                x-show="$wire.metodo_pago === 'Tarjeta' || $wire.metodo_pago === 'Transferencia'"
                x-cloak
            >
                <label for="referencia" class="{{ $etiqueta }}">Referencia / Folio</label>
                <input
                    id="referencia"
                    type="text"
                    wire:model="referencia"
                    maxlength="100"
                    placeholder="Ej. 0021 / autorización"
                    @required(in_array($metodo_pago, ['Tarjeta', 'Transferencia'], true))
                    class="{{ $campo }}"
                />
                <flux:error name="referencia" class="mt-1.5" />
            </div>

            <div class="grid gap-5 sm:grid-cols-2">
                {{-- Estado --}}
                <div>
                    <label for="estado" class="{{ $etiqueta }}">Estado</label>
                    <div class="relative">
                        <select id="estado" wire:model="estado" required class="{{ $selectClass }}">
                            @foreach ($estados as $itemEstado)
                                <option value="{{ $itemEstado }}">{{ $itemEstado }}</option>
                            @endforeach
                        </select>
                        <svg class="pointer-events-none absolute right-3 top-1/2 size-4 -translate-y-1/2 text-slate-400" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                            <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 11.168l3.71-3.938a.75.75 0 111.08 1.04l-4.25 4.51a.75.75 0 01-1.08 0l-4.25-4.51a.75.75 0 01.02-1.06z" clip-rule="evenodd" />
                        </svg>
                    </div>
                    <flux:error name="estado" class="mt-1.5" />
                </div>

                {{-- Fecha de pago --}}
                <div>
                    <label for="fecha_pago" class="{{ $etiqueta }}">Fecha de pago</label>
                    <input
                        id="fecha_pago"
                        type="datetime-local"
                        wire:model="fecha_pago"
                        class="{{ $campo }}"
                    />
                    <flux:error name="fecha_pago" class="mt-1.5" />
                </div>
            </div>

            {{-- Pago anticipado --}}
            <label class="flex cursor-pointer items-start gap-3 rounded-xl border border-slate-200 bg-slate-50/70 px-4 py-3 transition-colors hover:bg-slate-100 dark:border-zinc-700 dark:bg-zinc-900/60 dark:hover:bg-zinc-800">
                <input
                    type="checkbox"
                    wire:model="pagoAnticipado"
                    class="mt-0.5 size-4 rounded border-slate-300 text-amber-600 focus:ring-2 focus:ring-amber-500/30"
                />
                <span class="text-sm">
                    <span class="block font-semibold text-slate-700 dark:text-slate-200">Pago anticipado</span>
                    <span class="block text-xs text-slate-500 dark:text-slate-400">
                        Permite abonar un importe mayor al saldo pendiente.
                    </span>
                </span>
            </label>

            {{-- Notas --}}
            <div>
                <label for="notas" class="{{ $etiqueta }}">Notas</label>
                <textarea
                    id="notas"
                    wire:model="notas"
                    rows="3"
                    maxlength="500"
                    placeholder="Observaciones del pago…"
                    class="{{ $campo }} resize-none"
                ></textarea>
                <flux:error name="notas" class="mt-1.5" />
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
                type="submit"
                wire:loading.attr="disabled"
                wire:target="guardar"
                class="inline-flex items-center justify-center gap-2 rounded-xl bg-amber-600 px-5 py-2.5 text-sm font-semibold text-white shadow-md shadow-amber-600/25 transition-all duration-200 ease-out hover:-translate-y-0.5 hover:bg-amber-700 hover:shadow-lg focus:outline-none focus:ring-2 focus:ring-amber-600 focus:ring-offset-2 active:translate-y-0 active:scale-95 disabled:pointer-events-none disabled:opacity-70"
            >
                <span wire:loading.remove wire:target="guardar" class="inline-flex items-center gap-2">
                    <flux:icon.check class="size-4" />
                    Guardar
                </span>
                <span wire:loading wire:target="guardar">Guardando…</span>
            </button>
        </div>
    </form>
</flux:modal>
