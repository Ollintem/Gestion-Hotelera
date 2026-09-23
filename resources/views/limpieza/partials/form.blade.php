{{-- ======================================================
     FORMULARIO COMPARTIDO: CREAR / EDITAR TAREA DE LIMPIEZA
     ====================================================== --}}

<flux:card class="w-full max-w-2xl">
    <form wire:submit="guardar">
        <div class="mb-6">
            <flux:heading size="lg" class="!text-slate-800 !font-semibold">{{ $tareaId ? 'Editar tarea' : 'Nueva tarea' }}</flux:heading>
            <flux:subheading class="!text-slate-600 !font-medium">Completa los datos de la tarea de limpieza.</flux:subheading>
        </div>

        <div class="grid gap-4 sm:grid-cols-2">
            <flux:field>
                <flux:label>Habitación</flux:label>
                <x-dropdown
                    wire:model="habitacion_id"
                    required
                    :selected="$habitacion_id"
                    placeholder="Selecciona…"
                    :options="$habitaciones->mapWithKeys(fn ($habitacion) => [$habitacion->id => 'Hab. '.$habitacion->numero_habitacion.' ('.$habitacion->estado.')'])->all()"
                />
                <flux:error name="habitacion_id" />
            </flux:field>

            <flux:field>
                <flux:label>Responsable</flux:label>
                <x-dropdown
                    wire:model="user_id"
                    required
                    :selected="$user_id"
                    placeholder="Selecciona…"
                    :options="$usuarios->mapWithKeys(fn ($usuario) => [$usuario->id => $usuario->name])->all()"
                />
                <flux:error name="user_id" />
            </flux:field>

            <flux:field class="sm:col-span-2">
                <flux:label>Estado</flux:label>
                <x-dropdown
                    wire:model="estado"
                    :selected="$estado"
                    :options="array_combine($estados, $estados)"
                />
                <flux:error name="estado" />
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