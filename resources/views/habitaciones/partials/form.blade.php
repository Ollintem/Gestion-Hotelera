{{-- ======================================================
     FORMULARIO COMPARTIDO: CREAR / EDITAR HABITACIÓN
     ====================================================== --}}

@php
    $tiposOptions = $tipos->mapWithKeys(fn ($tipo) => [$tipo->id => $tipo->nombre.' — $'.number_format($tipo->precio_base, 2)])->all();
    $estadosOptions = array_combine($estados, $estados);
@endphp

<flux:card class="w-full max-w-3xl">
    <form wire:submit="guardar">
        <div class="mb-6">
            <flux:heading size="lg" class="!text-slate-800 !font-semibold dark:!text-slate-100">
                {{ $habitacionId ? 'Editar habitación' : 'Agregar habitación' }}
            </flux:heading>
            <flux:subheading class="!text-slate-600 !font-medium dark:!text-slate-400">
                Completa los datos de la habitación.
            </flux:subheading>
        </div>

        <div class="grid gap-4 sm:grid-cols-2">
            <flux:field>
                <flux:label>Número de habitación</flux:label>
                <flux:input wire:model="numero_habitacion" placeholder="Ejemplo: 101" required />
                <flux:error name="numero_habitacion" />
            </flux:field>

            <flux:field>
                <flux:label>Tipo de habitación</flux:label>
                <x-dropdown
                    wire:model="tipo_habitacion_id"
                    required
                    :selected="$tipo_habitacion_id"
                    placeholder="Selecciona un tipo..."
                    :options="$tiposOptions"
                />
                <flux:error name="tipo_habitacion_id" />
            </flux:field>

            <flux:field>
                <flux:label>Piso</flux:label>
                <flux:input type="number" min="1" wire:model="piso" required />
                <flux:error name="piso" />
            </flux:field>

            <flux:field>
                <flux:label>Capacidad (personas)</flux:label>
                <flux:input type="number" min="1" wire:model="capacidad" placeholder="Se toma del tipo..." />
                <flux:error name="capacidad" />
            </flux:field>

            <flux:field>
                <flux:label>Precio por noche</flux:label>
                <flux:input type="number" step="0.01" min="0" wire:model="precio_por_noche" placeholder="0.00" />
                <flux:error name="precio_por_noche" />
            </flux:field>

            <flux:field>
                <flux:label>Estado inicial</flux:label>
                <x-dropdown
                    wire:model="estado"
                    :selected="$estado"
                    :options="$estadosOptions"
                />
                <flux:error name="estado" />
            </flux:field>

            <flux:field class="sm:col-span-2">
                <flux:label>Descripción</flux:label>
                <textarea
                    wire:model="descripcion"
                    rows="3"
                    placeholder="Descripción de la habitación..."
                    class="block w-full resize-none rounded-xl border-0 bg-white px-3.5 py-2.5 text-sm text-slate-900 shadow-sm ring-1 ring-inset ring-slate-300 transition-all duration-300 ease-out placeholder:text-slate-400 focus:ring-2 focus:ring-inset focus:ring-amber-500 dark:bg-zinc-900 dark:text-white dark:ring-zinc-700"
                ></textarea>
                <flux:error name="descripcion" />
            </flux:field>

            <flux:field class="sm:col-span-2">
                <flux:label>URL de fotografía</flux:label>
                <flux:input type="url" wire:model="foto_url" placeholder="https://imagenes.ejemplo.com/habitacion.jpg" />
                <flux:error name="foto_url" />
                <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">
                    Capacidad, precio, descripción y foto se sincronizan con el tipo de habitación seleccionado.
                </p>
            </flux:field>
        </div>

        <div class="mt-6 flex items-center justify-end gap-3">
            <flux:button type="button" variant="ghost" wire:click="cerrarModal">Cancelar</flux:button>
            <button
                type="submit"
                class="inline-flex items-center justify-center gap-2 rounded-xl bg-amber-600 px-4 py-2 text-sm font-semibold text-white shadow-sm transition-all duration-300 ease-out hover:scale-[1.02] hover:bg-amber-700 hover:shadow-lg focus:outline-none focus:ring-2 focus:ring-amber-600 focus:ring-offset-2 active:scale-95 dark:bg-amber-600 dark:hover:bg-amber-700"
            >
                <span wire:loading.remove wire:target="guardar">
                    <flux:icon.check class="size-4" />
                </span>
                <span wire:loading wire:target="guardar">Guardando...</span>
                <span wire:loading.remove wire:target="guardar">Guardar habitación</span>
            </button>
        </div>
    </form>
</flux:card>