{{-- ======================================================
     MODAL: CREAR / EDITAR SERVICIO
     ====================================================== --}}

<flux:modal name="servicio-form" wire:model="mostrarModal" class="w-full max-w-md">
    <form wire:submit="guardar">
        <div class="mb-6">
            <flux:heading size="lg" class="!text-slate-800 !font-semibold">{{ $servicioId ? 'Editar servicio' : 'Nuevo servicio' }}</flux:heading>
            <flux:subheading class="!text-slate-600 !font-medium">Completa los datos del servicio.</flux:subheading>
        </div>

        <div class="space-y-4">
            <flux:field>
                <flux:label>Nombre</flux:label>
                <flux:select wire:model="nombre" placeholder="Selecciona un servicio..." required>
                    @foreach ($nombresServicio as $nombreOpcion)
                        <flux:select.option value="{{ $nombreOpcion }}">{{ $nombreOpcion }}</flux:select.option>
                    @endforeach
                </flux:select>
                <flux:error name="nombre" />
            </flux:field>

            <flux:field>
                <flux:label>Categoría</flux:label>
                <flux:select wire:model="categoria_id" placeholder="Selecciona una categoría..." required>
                    @foreach ($categoriasModal as $categoria)
                        <flux:select.option value="{{ $categoria->id }}">{{ $categoria->nombre }}</flux:select.option>
                    @endforeach
                </flux:select>
                <flux:error name="categoria_id" />
            </flux:field>

            <flux:field>
                <flux:label>Precio</flux:label>
                <flux:input type="number" step="0.01" min="0" wire:model="precio" required />
                <flux:error name="precio" />
            </flux:field>

            <flux:field>
                <flux:label>Descripción</flux:label>
                <flux:textarea wire:model="descripcion" rows="3" placeholder="Ejemplo: Incluye café, juice y panadería caliente." />
                <flux:error name="descripcion" />
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
</flux:modal>