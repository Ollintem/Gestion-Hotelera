{{-- ======================================================
     PESTAÑA DE PENDIENTES: LLEGADAS Y SALIDAS POR REGISTRAR
     ====================================================== --}}

@php
    $cabecera = '!text-slate-700 !font-semibold uppercase !text-xs tracking-wider';
@endphp

<div class="mb-6">
    {{-- Selector de pestaña: mismo segmented control que usa el módulo de Habitaciones --}}
    <div class="flex items-center gap-1 rounded-xl border border-slate-200 bg-white p-1 shadow-sm dark:border-zinc-700 dark:bg-zinc-900">
        <button
            type="button"
            wire:click="verPendientes"
            aria-pressed="{{ $pestana === 'pendientes' ? 'true' : 'false' }}"
            @class([
                'flex-1 rounded-lg px-4 py-2 text-sm font-semibold transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-amber-500',
                'bg-amber-600 text-white shadow-sm' => $pestana === 'pendientes',
                'text-slate-500 hover:bg-slate-100 hover:text-slate-800 dark:text-slate-400 dark:hover:bg-zinc-800 dark:hover:text-white' => $pestana !== 'pendientes',
            ])
        >
            Pendientes
        </button>
        <button
            type="button"
            wire:click="verHistorial('checkins')"
            aria-pressed="{{ $pestana === 'historial' ? 'true' : 'false' }}"
            @class([
                'flex-1 rounded-lg px-4 py-2 text-sm font-semibold transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-amber-500',
                'bg-amber-600 text-white shadow-sm' => $pestana === 'historial',
                'text-slate-500 hover:bg-slate-100 hover:text-slate-800 dark:text-slate-400 dark:hover:bg-zinc-800 dark:hover:text-white' => $pestana !== 'historial',
            ])
        >
            Historial
        </button>
    </div>
</div>

<div wire:loading.class="opacity-60" wire:target="filtroBusqueda, filtroFecha, filtroHabitacion">
    @if ($pestana === 'pendientes')
        {{-- ==================================================
             LLEGADAS PENDIENTES (CHECK-IN)
             ================================================== --}}
    <flux:card class="mb-6">
        <div class="mb-4 flex items-center justify-between">
            <flux:heading size="lg" class="!text-slate-800 !font-semibold">Llegadas pendientes</flux:heading>
            <flux:badge color="amber" size="sm">{{ $llegadas->total() }}</flux:badge>
        </div>

        <flux:table>
            <flux:table.columns>
                <flux:table.column class="{{ $cabecera }}">Folio</flux:table.column>
                <flux:table.column class="{{ $cabecera }}">Huésped</flux:table.column>
                <flux:table.column class="{{ $cabecera }}">Habitaciones</flux:table.column>
                <flux:table.column class="{{ $cabecera }}">Llegada programada</flux:table.column>
                <flux:table.column class="{{ $cabecera }}">Estado</flux:table.column>
                <flux:table.column align="end" class="{{ $cabecera }}">Acciones</flux:table.column>
            </flux:table.columns>

            <flux:table.rows>
                @forelse ($llegadas as $reserva)
                    <flux:table.row :key="$reserva->id">
                        <flux:table.cell variant="strong" class="!text-slate-900 !font-medium">#{{ $reserva->id }}</flux:table.cell>
                        <flux:table.cell class="!text-slate-700">{{ $reserva->cliente?->nombreCompleto() ?? '—' }}</flux:table.cell>
                        <flux:table.cell class="!text-slate-700">{{ $reserva->habitaciones->pluck('numero_habitacion')->join(', ') ?: '—' }}</flux:table.cell>
                        <flux:table.cell class="!text-slate-700">
                            <span @class([
                                'inline-flex items-center gap-1',
                                'font-semibold text-rose-600 dark:text-rose-400' => $reserva->check_in->lte(now()->startOfDay()),
                            ])>
                                {{ $reserva->check_in->format('d/m/Y') }}
                                @if ($reserva->check_in->isToday())
                                    <flux:badge color="amber" size="sm">Hoy</flux:badge>
                                @elseif ($reserva->check_in->lt(now()->startOfDay()))
                                    <flux:badge color="rose" size="sm">Atrasada</flux:badge>
                                @endif
                            </span>
                        </flux:table.cell>
                        <flux:table.cell>
                            <x-estado-badge :estado="$reserva->estado" />
                        </flux:table.cell>
                        <flux:table.cell align="end">
                            <div class="flex items-center justify-end gap-2">
                                @can('checkin_checkout.crear')
                                    <flux:button type="button" size="sm" variant="outline" color="emerald" icon="arrow-down-on-square" tooltip="Realizar check-in" aria-label="Realizar check-in de {{ $reserva->cliente?->nombreCompleto() }}" wire:click="abrirCheckIn({{ $reserva->id }})" class="transition-all duration-200 hover:scale-105 active:scale-95" />
                                @endcan

                                @can('checkin_checkout.editar')
                                    <flux:button type="button" size="sm" variant="outline" color="red" icon="trash" tooltip="Cancelar reservación" aria-label="Cancelar reservación número {{ $reserva->id }}" wire:click="cancelar({{ $reserva->id }})" wire:confirm="¿Cancelar la reservación número {{ $reserva->id }}? Esta acción no se puede deshacer." class="transition-all duration-200 hover:scale-105 active:scale-95" />
                                @endcan
                            </div>
                        </flux:table.cell>
                    </flux:table.row>
                @empty
                    <flux:table.row>
                        <flux:table.cell colspan="6" align="center">
                            <div class="flex flex-col items-center gap-2 py-8 text-slate-500 dark:text-slate-400">
                                <flux:icon.check-circle class="size-8 text-emerald-500" />
                                <p>No hay llegadas pendientes.</p>
                            </div>
                        </flux:table.cell>
                    </flux:table.row>
                @endforelse
            </flux:table.rows>
        </flux:table>

        @if ($llegadas->hasPages())
            <div class="mt-4 border-t border-slate-50 pt-3 dark:border-zinc-800">
                {{ $llegadas->links() }}
            </div>
        @endif
    </flux:card>

    {{-- ==================================================
         SALIDAS PENDIENTES (CHECK-OUT)
         ================================================== --}}
    <flux:card>
        <div class="mb-4 flex items-center justify-between">
            <flux:heading size="lg" class="!text-slate-800 !font-semibold">Salidas pendientes</flux:heading>
            <flux:badge color="blue" size="sm">{{ $salidas->total() }}</flux:badge>
        </div>

        <flux:table>
            <flux:table.columns>
                <flux:table.column class="{{ $cabecera }}">Folio</flux:table.column>
                <flux:table.column class="{{ $cabecera }}">Huésped</flux:table.column>
                <flux:table.column class="{{ $cabecera }}">Habitaciones</flux:table.column>
                <flux:table.column class="{{ $cabecera }}">Salida programada</flux:table.column>
                <flux:table.column class="{{ $cabecera }}">Estado</flux:table.column>
                <flux:table.column align="end" class="{{ $cabecera }}">Acciones</flux:table.column>
            </flux:table.columns>

            <flux:table.rows>
                @forelse ($salidas as $reserva)
                    <flux:table.row :key="$reserva->id">
                        <flux:table.cell variant="strong" class="!text-slate-900 !font-medium">#{{ $reserva->id }}</flux:table.cell>
                        <flux:table.cell class="!text-slate-700">{{ $reserva->cliente?->nombreCompleto() ?? '—' }}</flux:table.cell>
                        <flux:table.cell class="!text-slate-700">{{ $reserva->habitaciones->pluck('numero_habitacion')->join(', ') ?: '—' }}</flux:table.cell>
                        <flux:table.cell class="!text-slate-700">
                            <span @class([
                                'inline-flex items-center gap-1',
                                'font-semibold text-rose-600 dark:text-rose-400' => $reserva->check_out->lte(now()->startOfDay()),
                            ])>
                                {{ $reserva->check_out->format('d/m/Y') }}
                                @if ($reserva->check_out->isToday())
                                    <flux:badge color="amber" size="sm">Hoy</flux:badge>
                                @elseif ($reserva->check_out->lt(now()->startOfDay()))
                                    <flux:badge color="rose" size="sm">Vencida</flux:badge>
                                @endif
                            </span>
                        </flux:table.cell>
                        <flux:table.cell>
                            <x-estado-badge :estado="$reserva->estado" />
                        </flux:table.cell>
                        <flux:table.cell align="end">
                            <div class="flex items-center justify-end gap-2">
                                @can('checkin_checkout.crear')
                                    <flux:button type="button" size="sm" variant="outline" color="blue" icon="arrow-up-on-square" tooltip="Realizar check-out" aria-label="Realizar check-out de {{ $reserva->cliente?->nombreCompleto() }}" wire:click="abrirCheckOut({{ $reserva->id }})" class="transition-all duration-200 hover:scale-105 active:scale-95" />
                                @endcan
                            </div>
                        </flux:table.cell>
                    </flux:table.row>
                @empty
                    <flux:table.row>
                        <flux:table.cell colspan="6" align="center">
                            <div class="flex flex-col items-center gap-2 py-8 text-slate-500 dark:text-slate-400">
                                <flux:icon.check-circle class="size-8 text-emerald-500" />
                                <p>No hay salidas pendientes.</p>
                            </div>
                        </flux:table.cell>
                    </flux:table.row>
                @endforelse
            </flux:table.rows>
        </flux:table>

        @if ($salidas->hasPages())
            <div class="mt-4 border-t border-slate-50 pt-3 dark:border-zinc-800">
                {{ $salidas->links() }}
            </div>
        @endif
    </flux:card>
    @endif
</div>
