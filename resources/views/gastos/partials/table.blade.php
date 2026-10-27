{{-- ======================================================
     TABLA DE GASTOS

     Orden: Categoría, Concepto, Fecha, Monto, Observaciones,
     Acciones. Las observaciones se muestran recortadas a dos
     líneas con el texto completo en el tooltip; si están
     vacías se imprime un guion.
     ====================================================== --}}

@php
    $cabecera = '!text-slate-700 !font-semibold uppercase !text-xs tracking-wider';
@endphp

<flux:card>
    <flux:table>
        <flux:table.columns>
            <flux:table.column class="{{ $cabecera }}">Categoría</flux:table.column>
            <flux:table.column class="{{ $cabecera }}">Concepto</flux:table.column>
            <flux:table.column class="{{ $cabecera }}">Fecha</flux:table.column>
            <flux:table.column class="{{ $cabecera }}">Monto</flux:table.column>
            <flux:table.column class="{{ $cabecera }}">Observaciones</flux:table.column>
            <flux:table.column align="end" class="{{ $cabecera }}">Acciones</flux:table.column>
        </flux:table.columns>

        <flux:table.rows>
            @forelse ($gastos as $gasto)
                <flux:table.row :key="$gasto->id">
                    <flux:table.cell>
                        <flux:badge color="amber" size="sm" class="!bg-amber-100 !text-amber-800 !font-semibold">{{ $gasto->categoria }}</flux:badge>
                    </flux:table.cell>
                    <flux:table.cell variant="strong" class="!text-slate-900 !font-medium">{{ $gasto->concepto }}</flux:table.cell>
                    <flux:table.cell class="whitespace-nowrap">{{ $gasto->fecha_gasto->format('d/m/Y') }}</flux:table.cell>
                    <flux:table.cell class="whitespace-nowrap font-medium tabular-nums">${{ number_format($gasto->monto, 2) }}</flux:table.cell>
                    <flux:table.cell class="max-w-xs">
                        @if ($gasto->observaciones)
                            <span class="line-clamp-2 text-slate-600 dark:text-slate-400" title="{{ $gasto->observaciones }}">
                                {{ $gasto->observaciones }}
                            </span>
                        @else
                            <span class="text-slate-400 dark:text-slate-500">—</span>
                        @endif
                    </flux:table.cell>
                    <flux:table.cell align="end">
                        <div class="flex items-center justify-end gap-2">
                            @can('gastos.editar')
                                <flux:button type="button" size="sm" variant="outline" color="blue" icon="pencil-square" tooltip="Editar gasto" aria-label="Editar gasto" wire:click="editar({{ $gasto->id }})" class="transition-all duration-200 hover:scale-105 active:scale-95" />
                            @endcan

                            @can('gastos.eliminar')
                                <flux:button type="button" size="sm" variant="outline" color="red" icon="trash" tooltip="Eliminar gasto" aria-label="Eliminar gasto" wire:click="eliminar({{ $gasto->id }})" wire:confirm="¿Eliminar este gasto? Esta acción no se puede deshacer." class="transition-all duration-200 hover:scale-105 active:scale-95" />
                            @endcan
                        </div>
                    </flux:table.cell>
                </flux:table.row>
            @empty
                <flux:table.row>
                    <flux:table.cell colspan="6" align="center">
                        <div class="flex flex-col items-center gap-2 py-8 text-slate-500 dark:text-slate-400">
                            <flux:icon.banknotes class="size-8 text-slate-400" />
                            <p>No hay gastos registrados.</p>
                        </div>
                    </flux:table.cell>
                </flux:table.row>
            @endforelse
        </flux:table.rows>
    </flux:table>

    @if ($gastos->hasPages())
        <div class="mt-4 border-t border-slate-50 pt-3 dark:border-zinc-800">
            {{ $gastos->links() }}
        </div>
    @endif
</flux:card>
