{{-- ======================================================
     FORMULARIO COMPARTIDO: CREAR / EDITAR PAGO
     ====================================================== --}}

<flux:card class="w-full max-w-2xl">
    <form wire:submit="guardar">
        <div class="mb-6">
            <flux:heading size="lg" class="!text-slate-800 !font-semibold">{{ $pagoId ? 'Editar pago' : 'Nuevo pago' }}</flux:heading>
            <flux:subheading class="!text-slate-600 !font-medium">Completa los datos del pago.</flux:subheading>
        </div>

        <div class="grid gap-4 sm:grid-cols-2">
            <flux:field class="sm:col-span-2">
                <flux:label>Reservación</flux:label>
                <x-dropdown
                    wire:model="reserva_id"
                    required
                    :selected="$reserva_id"
                    placeholder="Selecciona…"
                    :options="$reservas->mapWithKeys(fn ($reserva) => [$reserva->id => '#'.$reserva->id.' · '.($reserva->cliente?->nombreCompleto() ?? 'Sin cliente').' ($'.number_format($reserva->monto_total, 2).')'])->all()"
                />
                <flux:error name="reserva_id" />
            </flux:field>

            <flux:field>
                <flux:label>Monto</flux:label>
                <flux:input type="number" step="0.01" min="0.01" wire:model="monto" required />
                <flux:error name="monto" />
            </flux:field>

            <flux:field>
                <flux:label>Método de pago</flux:label>
                <x-dropdown
                    wire:model="metodo_pago"
                    :selected="$metodo_pago"
                    :options="array_combine($metodos, $metodos)"
                />
                <flux:error name="metodo_pago" />
            </flux:field>

            <flux:field class="sm:col-span-2">
                <flux:label>Fecha de pago</flux:label>
                <flux:input type="datetime-local" wire:model="fecha_pago" />
                <flux:error name="fecha_pago" />
            </flux:field>

            <flux:field class="sm:col-span-2">
                <flux:label>Notas</flux:label>
                <textarea wire:model="notas" rows="3" class="block w-full resize-none rounded-xl border-0 bg-white px-3.5 py-2.5 text-sm text-slate-900 shadow-sm ring-1 ring-inset ring-slate-300 transition-all duration-300 ease-out placeholder:text-slate-400 focus:ring-2 focus:ring-inset focus:ring-amber-500 dark:bg-zinc-900 dark:text-white dark:ring-zinc-700"></textarea>
                <flux:error name="notas" />
            </flux:field>
        </div>

        <div class="mt-6 flex items-center justify-end gap-3">
            <flux:button type="button" variant="ghost" wire:click="cerrarModal">Cancelar</flux:button>
            <flux:button type="submit" variant="primary">
                <flux:icon.check class="size-4" />
                Guardar
            </flux:button>
        </div>
    </form>
</flux:card>