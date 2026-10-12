{{-- ======================================================
     TABLA DE TAREAS DE LIMPIEZA
     ====================================================== --}}

<flux:card>
    <flux:table>
        <flux:table.columns>
            <flux:table.column class="!text-slate-700 !font-semibold uppercase !text-xs tracking-wider">Habitación</flux:table.column>
            <flux:table.column class="!text-slate-700 !font-semibold uppercase !text-xs tracking-wider">Responsable</flux:table.column>
            <flux:table.column class="!text-slate-700 !font-semibold uppercase !text-xs tracking-wider">Estado</flux:table.column>
            <flux:table.column class="!text-slate-700 !font-semibold uppercase !text-xs tracking-wider">Notas</flux:table.column>
            <flux:table.column align="end" class="!text-slate-700 !font-semibold uppercase !text-xs tracking-wider">Acciones</flux:table.column>
        </flux:table.columns>

        <flux:table.rows>
            @forelse ($tareas as $tarea)
                <flux:table.row :key="$tarea->id">
                    <flux:table.cell variant="strong" class="!text-slate-900 !font-medium">Hab. {{ $tarea->habitacion?->numero_habitacion ?? '—' }}</flux:table.cell>
                    <flux:table.cell>{{ $tarea->usuario?->name ?? '—' }}</flux:table.cell>
                    <flux:table.cell>
                        <x-estado-badge :estado="$tarea->estado" />
                    </flux:table.cell>
                    <flux:table.cell>{{ $tarea->notas ?? '—' }}</flux:table.cell>
                    <flux:table.cell align="end">
                        <div class="flex items-center justify-end gap-2">
                            @if(!(auth()->user()->hasRole('limpieza') || auth()->user()->rol === 'limpieza' || auth()->user()->rol === 'Limpieza'))
                            <flux:button type="button" size="sm" variant="outline" color="blue" icon="pencil-square" tooltip="Editar tarea" aria-label="Editar tarea" wire:click="editar({{ $tarea->id }})" class="transition-all duration-200 hover:scale-105 active:scale-95" />
                            <flux:button type="button" size="sm" variant="outline" color="red" icon="trash" tooltip="Eliminar tarea" aria-label="Eliminar tarea" wire:click="eliminar({{ $tarea->id }})" wire:confirm="¿Eliminar esta tarea?" class="transition-all duration-200 hover:scale-105 active:scale-95" />
                            @endif
                        </div>
                    </flux:table.cell>
                </flux:table.row>
            @empty
                <flux:table.row>
                    <flux:table.cell colspan="5" align="center">
                        <p class="py-8">No hay tareas de limpieza registradas.</p>
                    </flux:table.cell>
                </flux:table.row>
            @endforelse
        </flux:table.rows>
    </flux:table>
</flux:card>