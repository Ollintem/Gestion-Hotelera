{{-- ======================================================
     MODAL: CREAR / EDITAR TAREA DE LIMPIEZA

     Los tres selectores usan el mismo enlace que Servicios: `modelo` empuja el
     valor a la propiedad de Livewire con `$wire.set` en el mismo clic que lo
     elige, así que `wire:submit` no puede adelantarse y enviar la propiedad
     vacía; `clave` ata el bloque a un `wire:key` dependiente del valor para que
     Morphdom reconstruya el control y el botón muestre lo que devolvió el
     servidor.
     ====================================================== --}}

<flux:modal name="limpieza-form" wire:model="mostrarModal" class="w-full max-w-lg">
    <form wire:submit="guardar">
        <div class="mb-6">
            <flux:heading size="lg" class="!text-slate-800 !font-semibold">{{ $tareaId ? 'Editar tarea' : 'Nueva tarea' }}</flux:heading>
            <flux:subheading class="!text-slate-600 !font-medium">Completa los datos de la tarea de limpieza.</flux:subheading>
        </div>

        <div class="grid gap-4 sm:grid-cols-2">
            @if(!(auth()->user()->hasRole('limpieza') || auth()->user()->rol === 'limpieza' || auth()->user()->rol === 'Limpieza'))
            <flux:field>
                <flux:label>Habitación</flux:label>
                <x-dropdown
                    id="habitacion_id"
                    wire:model="habitacion_id"
                    modelo="habitacion_id"
                    clave="habitacion_id-{{ $habitacion_id }}"
                    required
                    :selected="$habitacion_id"
                    placeholder="Selecciona…"
                    :options="$habitaciones->mapWithKeys(fn ($habitacion) => [$habitacion->id => 'Hab. '.$habitacion->numero_habitacion.' ('.$habitacion->estado.')'])->all()"
                    variant="soft"
                    class="w-full"
                >
                    <x-slot:leadingIcon>
                        <flux:icon.home-modern class="size-5" />
                    </x-slot:leadingIcon>
                </x-dropdown>
                <flux:error name="habitacion_id" />
            </flux:field>
            @endif

            @if(!(auth()->user()->hasRole('limpieza') || auth()->user()->rol === 'limpieza' || auth()->user()->rol === 'Limpieza'))
            <flux:field>
                <flux:label>Responsable</flux:label>
                <x-dropdown
                    id="user_id"
                    wire:model="user_id"
                    modelo="user_id"
                    clave="user_id-{{ $user_id }}"
                    required
                    :selected="$user_id"
                    placeholder="Selecciona…"
                    :options="$usuarios->mapWithKeys(fn ($usuario) => [$usuario->id => $usuario->name])->all()"
                    variant="soft"
                    class="w-full"
                >
                    <x-slot:leadingIcon>
                        <flux:icon.user class="size-5" />
                    </x-slot:leadingIcon>
                </x-dropdown>
                <flux:error name="user_id" />
            </flux:field>
            @endif

            <flux:field class="sm:col-span-2">
                <flux:label>Estado</flux:label>
                <x-dropdown
                    id="estado"
                    wire:model="estado"
                    modelo="estado"
                    clave="estado-{{ $estado }}"
                    :selected="$estado"
                    :options="array_combine($estados, $estados)"
                    variant="soft"
                    class="w-full"
                >
                    <x-slot:leadingIcon>
                        <flux:icon.signal class="size-5" />
                    </x-slot:leadingIcon>
                </x-dropdown>
                <flux:error name="estado" />
            </flux:field>

            @if(!(auth()->user()->hasRole('limpieza') || auth()->user()->rol === 'limpieza' || auth()->user()->rol === 'Limpieza'))
            <flux:field class="sm:col-span-2">
                <flux:label>Notas</flux:label>
                <textarea wire:model="notas" rows="3" class="w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm text-slate-900 focus:border-amber-500 focus:outline-none focus:ring-2 focus:ring-amber-500/40 dark:border-zinc-600 dark:bg-zinc-800 dark:text-white"></textarea>
                <flux:error name="notas" />
            </flux:field>
            @endif
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