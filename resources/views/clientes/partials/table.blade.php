{{-- ======================================================
     TABLA DE CLIENTES
     ====================================================== --}}

<flux:card>
    <div class="overflow-x-auto">
    <flux:table>
        <flux:table.columns>
            <flux:table.column class="!text-slate-700 !font-semibold uppercase !text-xs tracking-wider">Nombre</flux:table.column>
            <flux:table.column class="!text-slate-700 !font-semibold uppercase !text-xs tracking-wider">Email</flux:table.column>
            <flux:table.column class="!text-slate-700 !font-semibold uppercase !text-xs tracking-wider">Teléfono</flux:table.column>
            <flux:table.column class="!text-slate-700 !font-semibold uppercase !text-xs tracking-wider">Identificación</flux:table.column>
            <flux:table.column align="center" class="!text-slate-700 !font-semibold uppercase !text-xs tracking-wider">Reservas</flux:table.column>
            <flux:table.column align="end" class="!text-slate-700 !font-semibold uppercase !text-xs tracking-wider">Acciones</flux:table.column>
        </flux:table.columns>

        <flux:table.rows>
            @forelse ($clientes as $cliente)
                <flux:table.row :key="$cliente->id">
                    <flux:table.cell variant="strong" class="!text-slate-900 !font-medium">{{ $cliente->nombreCompleto() }}</flux:table.cell>
                    <flux:table.cell>{{ $cliente->email ?? '—' }}</flux:table.cell>
                    <flux:table.cell>{{ $cliente->telefono ?? '—' }}</flux:table.cell>
                    <flux:table.cell>{{ $cliente->tipo_identificacion ? $cliente->tipo_identificacion.' · '.$cliente->numero_identificacion : '—' }}</flux:table.cell>
                    <flux:table.cell align="center">
                        <flux:badge color="zinc" size="sm" class="!bg-zinc-100 !text-zinc-800 !font-semibold">{{ $cliente->reservas_count }}</flux:badge>
                    </flux:table.cell>
                    <flux:table.cell align="end">
                        <div class="flex items-center justify-end gap-2">
                            <flux:button type="button" size="sm" variant="outline" color="blue" icon="pencil-square" tooltip="Editar cliente" aria-label="Editar cliente" wire:click="editar({{ $cliente->id }})" class="transition-all duration-200 hover:scale-105 active:scale-95" />
                            <flux:button type="button" size="sm" variant="outline" color="red" icon="trash" tooltip="Eliminar cliente" aria-label="Eliminar cliente" wire:click="eliminar({{ $cliente->id }})" wire:confirm="¿Eliminar este cliente?" class="transition-all duration-200 hover:scale-105 active:scale-95" />
                        </div>
                    </flux:table.cell>
                </flux:table.row>
            @empty
                <flux:table.row>
                    <flux:table.cell colspan="6" align="center">
                        <p class="py-8">No hay clientes registrados.</p>
                    </flux:table.cell>
                </flux:table.row>
            @endforelse
        </flux:table.rows>
    </flux:table>
    </div>
</flux:card>