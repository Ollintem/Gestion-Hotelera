{{-- ======================================================
     FORMULARIO COMPARTIDO: CREAR / EDITAR SERVICIO
     ====================================================== --}}

<flux:card class="w-full max-w-xl">
    <form wire:submit="guardar">
        <div class="mb-6">
            <flux:heading size="lg" class="!text-slate-800 !font-semibold">{{ $servicioId ? 'Editar servicio' : 'Nuevo servicio' }}</flux:heading>
            <flux:subheading class="!text-slate-600 !font-medium">Completa los datos del servicio.</flux:subheading>
        </div>

        <div class="space-y-4">
            <flux:field>
                <flux:label>Nombre</flux:label>
                <flux:input wire:model="nombre" placeholder="Ejemplo: Desayuno buffet" required />
                <flux:error name="nombre" />
            </flux:field>

            <flux:field>
                <flux:label>Precio</flux:label>
                <flux:input type="number" step="0.01" min="0" wire:model="precio" required />
                <flux:error name="precio" />
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