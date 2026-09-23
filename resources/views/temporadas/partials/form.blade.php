{{-- ======================================================
     FORMULARIO COMPARTIDO: CREAR / EDITAR TEMPORADA
     ====================================================== --}}

<flux:card class="w-full max-w-2xl">
    <form wire:submit="guardar">
        <div class="mb-6">
            <flux:heading size="lg" class="!text-slate-800 !font-semibold">{{ $temporadaId ? 'Editar temporada' : 'Nueva temporada' }}</flux:heading>
            <flux:subheading class="!text-slate-600 !font-medium">Completa los datos de la temporada.</flux:subheading>
        </div>

        <div class="grid gap-4 sm:grid-cols-2">
            <flux:field class="sm:col-span-2">
                <flux:label>Nombre</flux:label>
                <flux:input wire:model="nombre" placeholder="Ejemplo: Temporada Alta" required />
                <flux:error name="nombre" />
            </flux:field>

            <flux:field>
                <flux:label>Fecha de inicio</flux:label>
                <flux:input type="date" wire:model="fecha_inicio" required />
                <flux:error name="fecha_inicio" />
            </flux:field>

            <flux:field>
                <flux:label>Fecha de fin</flux:label>
                <flux:input type="date" wire:model="fecha_fin" required />
                <flux:error name="fecha_fin" />
            </flux:field>

            <flux:field class="sm:col-span-2">
                <flux:label>Multiplicador de precio</flux:label>
                <flux:input type="number" step="0.01" min="0.01" wire:model="multiplicador_precio" required />
                <flux:error name="multiplicador_precio" />
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