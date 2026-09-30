{{-- ======================================================
     MODAL: CREAR / EDITAR TAREA DE LIMPIEZA
     ====================================================== --}}

<flux:modal name="limpieza-form" wire:model="mostrarModal" class="w-full max-w-lg">
    <form wire:submit="guardar">
        <div class="mb-6">
            <flux:heading size="lg" class="!text-slate-800 !font-semibold">{{ $tareaId ? 'Editar tarea' : 'Nueva tarea' }}</flux:heading>
            <flux:subheading class="!text-slate-600 !font-medium">Completa los datos de la tarea de limpieza.</flux:subheading>
        </div>

        <div class="grid gap-4 sm:grid-cols-2">
            <flux:field>
                <flux:label>Habitación</flux:label>
                <select wire:model="habitacion_id" required class="w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm text-slate-900 focus:border-amber-500 focus:outline-none focus:ring-2 focus:ring-amber-500/40 dark:border-zinc-600 dark:bg-zinc-800 dark:text-white">
                    <option value="">Selecciona…</option>
                    @foreach ($habitaciones as $habitacion)
                        <option value="{{ $habitacion->id }}">Hab. {{ $habitacion->numero_habitacion }} ({{ $habitacion->estado }})</option>
                    @endforeach
                </select>
                <flux:error name="habitacion_id" />
            </flux:field>

            <flux:field>
                <flux:label>Responsable</flux:label>
                <select wire:model="user_id" required class="w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm text-slate-900 focus:border-amber-500 focus:outline-none focus:ring-2 focus:ring-amber-500/40 dark:border-zinc-600 dark:bg-zinc-800 dark:text-white">
                    <option value="">Selecciona…</option>
                    @foreach ($usuarios as $usuario)
                        <option value="{{ $usuario->id }}">{{ $usuario->name }}</option>
                    @endforeach
                </select>
                <flux:error name="user_id" />
            </flux:field>

            <flux:field class="sm:col-span-2">
                <flux:label>Estado</flux:label>
                <select wire:model="estado" class="w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm text-slate-900 focus:border-amber-500 focus:outline-none focus:ring-2 focus:ring-amber-500/40 dark:border-zinc-600 dark:bg-zinc-800 dark:text-white">
                    @foreach ($estados as $estado)
                        <option value="{{ $estado }}">{{ $estado }}</option>
                    @endforeach
                </select>
                <flux:error name="estado" />
            </flux:field>

            <flux:field class="sm:col-span-2">
                <flux:label>Notas</flux:label>
                <textarea wire:model="notas" rows="3" class="w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm text-slate-900 focus:border-amber-500 focus:outline-none focus:ring-2 focus:ring-amber-500/40 dark:border-zinc-600 dark:bg-zinc-800 dark:text-white"></textarea>
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
</flux:modal>