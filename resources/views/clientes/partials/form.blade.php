{{-- ======================================================
     FORMULARIO COMPARTIDO: CREAR / EDITAR CLIENTE
     ====================================================== --}}

<flux:card class="w-full max-w-2xl">
    <form wire:submit="guardar">
        <div class="mb-6">
            <flux:heading size="lg" class="!text-slate-800 !font-semibold">{{ $clienteId ? 'Editar cliente' : 'Nuevo cliente' }}</flux:heading>
            <flux:subheading class="!text-slate-600 !font-medium">Completa los datos del cliente.</flux:subheading>
        </div>

        <div class="grid gap-4 sm:grid-cols-2">
            <flux:field>
                <flux:label>Nombre</flux:label>
                <flux:input wire:model="nombre" required />
                <flux:error name="nombre" />
            </flux:field>

            <flux:field>
                <flux:label>Apellido</flux:label>
                <flux:input wire:model="apellido" required />
                <flux:error name="apellido" />
            </flux:field>

            <flux:field>
                <flux:label>Email</flux:label>
                <flux:input type="email" wire:model="email" />
                <flux:error name="email" />
            </flux:field>

            <flux:field>
                <flux:label>Teléfono</flux:label>
                <flux:input wire:model="telefono" />
                <flux:error name="telefono" />
            </flux:field>

            <flux:field>
                <flux:label>Tipo de identificación</flux:label>
                <x-dropdown
                    wire:model="tipo_identificacion"
                    :selected="$tipo_identificacion"
                    placeholder="Sin especificar…"
                    :options="['INE' => 'INE', 'Pasaporte' => 'Pasaporte']"
                />
                <flux:error name="tipo_identificacion" />
            </flux:field>

            <flux:field>
                <flux:label>Número de identificación</flux:label>
                <flux:input wire:model="numero_identificacion" />
                <flux:error name="numero_identificacion" />
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