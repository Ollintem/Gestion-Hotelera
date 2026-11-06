<div>
    <x-aviso-temporal :mensaje="$mensajeExito" tipo="success" clave="clientes-exito" />
    <x-aviso-temporal :mensaje="$mensajeError" tipo="error" clave="clientes-error" />

    @include('livewire.clientes.partials.cabecera-filtros')

    @if ($this->clientes->isEmpty())
        <div class="mt-8 rounded-2xl border border-dashed border-slate-300 bg-white/70 px-6 py-16 text-center animate-fade-in-up dark:border-zinc-700 dark:bg-slate-800/40">
            <span class="mx-auto flex size-12 items-center justify-center rounded-full bg-slate-100 dark:bg-zinc-700">
                <flux:icon.users class="size-6 text-slate-400 dark:text-zinc-300" />
            </span>
            <h3 class="mt-4 text-base font-semibold text-slate-800 dark:text-zinc-100">Sin clientes</h3>
            <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">
                {{ $this->hayFiltrosActivos
                    ? 'Ningún huésped coincide con los filtros actuales.'
                    : 'Aún no hay huéspedes registrados en el hotel.' }}
            </p>

            @if ($this->hayFiltrosActivos)
                <flux:button type="button" variant="outline" icon="arrow-path" wire:click="limpiarFiltros" class="mt-5">
                    Limpiar filtros
                </flux:button>
            @else
                <flux:button type="button" variant="primary" icon="plus" wire:click="crear" class="mt-5">
                    Registrar primer cliente
                </flux:button>
            @endif
        </div>
    @else
        <div class="mt-6 grid grid-cols-1 gap-5 md:grid-cols-2 xl:grid-cols-3">
            @foreach ($this->clientes as $cliente)
                @include('livewire.clientes.partials.tarjeta-grid', ['cliente' => $cliente])
            @endforeach
        </div>

        <div class="mt-8">
            {{ $this->clientes->links() }}
        </div>
    @endif

    <flux:modal name="cliente-form" class="w-full max-w-2xl">
        <form wire:submit="guardar" class="space-y-6">
            <div>
                <flux:heading size="lg">
                    {{ $clienteId ? 'Editar cliente' : 'Nuevo cliente' }}
                </flux:heading>
                <flux:subheading>
                    {{ $clienteId
                        ? 'Actualiza la información de contacto e identificación del huésped.'
                        : 'Captura los datos del huésped para tener su expediente listo.' }}
                </flux:subheading>
            </div>

            <div class="grid gap-4 sm:grid-cols-2">
                <flux:field>
                    <flux:label>Nombre</flux:label>
                    <flux:input wire:model="nombre" required autocomplete="given-name" />
                    <flux:error name="nombre" />
                </flux:field>

                <flux:field>
                    <flux:label>Apellido</flux:label>
                    <flux:input wire:model="apellido" required autocomplete="family-name" />
                    <flux:error name="apellido" />
                </flux:field>

                <flux:field>
                    <flux:label>Correo electrónico</flux:label>
                    <flux:input type="email" wire:model="email" autocomplete="email" />
                    <flux:error name="email" />
                </flux:field>

                <flux:field>
                    <flux:label>Teléfono</flux:label>
                    <flux:input wire:model="telefono" autocomplete="tel" />
                    <flux:error name="telefono" />
                </flux:field>

                <flux:field>
                    <flux:label>Tipo de identificación</flux:label>
                    <x-dropdown
                        id="tipo_identificacion"
                        wire:model="tipo_identificacion"
                        modelo="tipo_identificacion"
                        clave="tipo-identificacion-{{ $tipo_identificacion }}"
                        :selected="$tipo_identificacion"
                        placeholder="Sin especificar"
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

            <div class="flex items-center justify-end gap-3 border-t border-slate-100 pt-5 dark:border-zinc-700">
                <flux:button type="button" variant="ghost" wire:click="cerrarModal">
                    Cancelar
                </flux:button>
                <flux:button type="submit" variant="primary" icon="check">
                    {{ $clienteId ? 'Guardar cambios' : 'Registrar cliente' }}
                </flux:button>
            </div>
        </form>
    </flux:modal>
</div>
