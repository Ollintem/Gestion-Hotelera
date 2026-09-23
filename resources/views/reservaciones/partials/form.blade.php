{{-- ======================================================
     FORMULARIO COMPARTIDO: CREAR / EDITAR RESERVACIÓN
     ====================================================== --}}

<flux:card class="w-full max-w-3xl">
    <form wire:submit="guardar">
        <div class="mb-6">
            <flux:heading size="lg" class="!text-slate-800 !font-semibold">{{ $reservaId ? 'Editar reservación' : 'Nueva reservación' }}</flux:heading>
            <flux:subheading class="!text-slate-600 !font-medium">Completa los datos de la reservación.</flux:subheading>
        </div>

        <div class="grid gap-4 sm:grid-cols-2">
            <flux:field class="sm:col-span-2">
                <flux:label>Cliente</flux:label>
                <x-dropdown
                    wire:model="cliente_id"
                    required
                    :selected="$cliente_id"
                    placeholder="Selecciona un cliente…"
                    :options="$clientes->mapWithKeys(fn ($cliente) => [$cliente->id => $cliente->nombreCompleto()])->all()"
                />
                <flux:error name="cliente_id" />
            </flux:field>

            <flux:field>
                <flux:label>Check-in</flux:label>
                <flux:input type="date" wire:model="check_in" required />
                <flux:error name="check_in" />
            </flux:field>

            <flux:field>
                <flux:label>Check-out</flux:label>
                <flux:input type="date" wire:model="check_out" required />
                <flux:error name="check_out" />
            </flux:field>

            <flux:field>
                <flux:label>Estado</flux:label>
                <x-dropdown
                    wire:model="estado"
                    :selected="$estado"
                    :options="array_combine($estados, $estados)"
                />
                <flux:error name="estado" />
            </flux:field>

            <flux:field>
                <flux:label>Monto total</flux:label>
                <flux:input type="number" step="0.01" min="0" wire:model="monto_total" required />
                <flux:error name="monto_total" />
            </flux:field>

            <div class="sm:col-span-2">
                <p class="mb-2 text-sm font-semibold text-slate-800">Habitaciones asignadas</p>
                <div class="grid max-h-48 gap-1.5 overflow-y-auto pr-1 sm:grid-cols-3">
                    @foreach ($habitaciones as $habitacion)
                        <label class="flex cursor-pointer items-center gap-2 text-sm text-zinc-700 dark:text-zinc-300">
                            <input type="checkbox" value="{{ $habitacion->id }}" wire:model="habitacion_ids" class="size-4 rounded border-slate-300 text-amber-500 focus:ring-amber-500 focus:ring-offset-0 dark:border-zinc-600 dark:bg-zinc-800" />
                            Hab. {{ $habitacion->numero_habitacion }} ({{ $habitacion->estado }})
                        </label>
                    @endforeach
                </div>
            </div>
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