{{-- ======================================================
     FORMULARIO COMPARTIDO: CREAR / EDITAR GASTO
     ====================================================== --}}

<flux:card class="w-full max-w-2xl">
    <form wire:submit="guardar">
        <div class="mb-6">
            <flux:heading size="lg" class="!text-slate-800 !font-semibold">{{ $gastoId ? 'Editar gasto' : 'Nuevo gasto' }}</flux:heading>
            <flux:subheading class="!text-slate-600 !font-medium">Completa los datos del gasto.</flux:subheading>
        </div>

        <div class="grid gap-4 sm:grid-cols-2">
            <flux:field class="sm:col-span-2">
                <flux:label>Concepto</flux:label>
                <flux:input wire:model="concepto" placeholder="Ejemplo: Compra de blancos" required />
                <flux:error name="concepto" />
            </flux:field>

            <flux:field>
                <flux:label>Monto</flux:label>
                <flux:input type="number" step="0.01" min="0.01" wire:model="monto" required />
                <flux:error name="monto" />
            </flux:field>

            <flux:field>
                <flux:label>Fecha del gasto</flux:label>
                <flux:input type="date" wire:model="fecha_gasto" required />
                <flux:error name="fecha_gasto" />
            </flux:field>

            <flux:field class="sm:col-span-2">
                <flux:label>Categoría</flux:label>
                {{--
                    `modelo` escribe el valor con `$wire.set()` en la misma
                    petición que el clic. Sin él el valor viaja por el evento
                    del <select> oculto, una segunda petición asíncrona que
                    `wire:submit` puede adelantar, y `guardar()` recibe la
                    categoría vacía: por eso no pasaba la validación `required`.

                    `clave` ata la raíz al valor para que Livewire reconstruya
                    el control cuando cambia y el botón muestre la opción
                    elegida en lugar de la etiqueta anterior.
                --}}
                <x-dropdown
                    wire:model="categoria"
                    modelo="categoria"
                    clave="categoria-{{ $categoria }}"
                    required
                    :selected="$categoria"
                    placeholder="Selecciona…"
                    :options="array_combine($categorias, $categorias)"
                />
                <flux:error name="categoria" />
            </flux:field>

            <flux:field class="sm:col-span-2">
                <flux:label>Observaciones <flux:text size="sm" class="!text-slate-400">(opcional)</flux:text></flux:label>
                <flux:textarea
                    wire:model="observaciones"
                    rows="3"
                    maxlength="1000"
                    placeholder="Información adicional del gasto…"
                    class="resize-none"
                />
                <flux:error name="observaciones" />
            </flux:field>
        </div>

        <div class="mt-6 flex items-center justify-end gap-3">
            <flux:button type="button" variant="ghost" wire:click="cerrarModal">Cancelar</flux:button>
            <flux:button type="submit" variant="primary" wire:loading.attr="disabled" wire:target="guardar" class="transition-all duration-200 hover:scale-105 active:scale-95 disabled:pointer-events-none disabled:opacity-70">
                <span wire:loading.remove wire:target="guardar" class="inline-flex items-center gap-2">
                    <flux:icon.check class="size-4" />
                    Guardar
                </span>
                <span wire:loading wire:target="guardar">Guardando…</span>
            </flux:button>
        </div>
    </form>
</flux:card>