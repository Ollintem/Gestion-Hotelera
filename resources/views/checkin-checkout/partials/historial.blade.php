{{-- ======================================================
     HISTORIAL DE MOVIMIENTOS REGISTRADOS

     Se consulta desde la pestaña «Historial» y lista las
     llegadas o las salidas ya registradas, con la hora real
     y el empleado que las anotó.
     ====================================================== --}}

@php
    $cabecera = '!text-slate-700 !font-semibold uppercase !text-xs tracking-wider';
@endphp

@if ($pestana === 'historial')
    <flux:card>
        <div class="mb-4 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <flux:heading size="lg" class="!text-slate-800 !font-semibold">Historial de movimientos</flux:heading>

            {{-- Selector del tipo de movimiento --}}
            <div class="flex items-center gap-1 rounded-xl border border-slate-200 bg-white p-1 shadow-sm dark:border-zinc-700 dark:bg-zinc-900">
                <button
                    type="button"
                    wire:click="verHistorial('checkins')"
                    aria-pressed="{{ $paginaHistorial === 'checkins' ? 'true' : 'false' }}"
                    @class([
                        'rounded-lg px-3 py-1.5 text-xs font-semibold transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-amber-500',
                        'bg-amber-600 text-white shadow-sm' => $paginaHistorial === 'checkins',
                        'text-slate-500 hover:bg-slate-100 hover:text-slate-800 dark:text-slate-400 dark:hover:bg-zinc-800 dark:hover:text-white' => $paginaHistorial !== 'checkins',
                    ])
                >
                    Check-ins
                </button>
                <button
                    type="button"
                    wire:click="verHistorial('salidas')"
                    aria-pressed="{{ $paginaHistorial === 'salidas' ? 'true' : 'false' }}"
                    @class([
                        'rounded-lg px-3 py-1.5 text-xs font-semibold transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-amber-500',
                        'bg-amber-600 text-white shadow-sm' => $paginaHistorial === 'salidas',
                        'text-slate-500 hover:bg-slate-100 hover:text-slate-800 dark:text-slate-400 dark:hover:bg-zinc-800 dark:hover:text-white' => $paginaHistorial !== 'salidas',
                    ])
                >
                    Check-outs
                </button>
            </div>
        </div>

        <flux:table>
            <flux:table.columns>
                <flux:table.column class="{{ $cabecera }}">Folio</flux:table.column>
                <flux:table.column class="{{ $cabecera }}">Huésped</flux:table.column>
                <flux:table.column class="{{ $cabecera }}">Habitaciones</flux:table.column>
                <flux:table.column class="{{ $cabecera }}">{{ $paginaHistorial === 'salidas' ? 'Salida real' : 'Llegada real' }}</flux:table.column>
                <flux:table.column class="{{ $cabecera }}">Registró</flux:table.column>
                <flux:table.column class="{{ $cabecera }}">Estado</flux:table.column>
            </flux:table.columns>

            <flux:table.rows>
                @forelse ($historial as $reserva)
                    <flux:table.row :key="$reserva->id">
                        <flux:table.cell variant="strong" class="!text-slate-900 !font-medium">#{{ $reserva->id }}</flux:table.cell>
                        <flux:table.cell class="!text-slate-700">{{ $reserva->cliente?->nombreCompleto() ?? '—' }}</flux:table.cell>
                        <flux:table.cell class="!text-slate-700">{{ $reserva->habitaciones->pluck('numero_habitacion')->join(', ') ?: '—' }}</flux:table.cell>
                        <flux:table.cell class="!text-slate-700">
                            @if ($paginaHistorial === 'salidas')
                                {{ $reserva->fecha_hora_checkout?->format('d/m/Y H:i') ?? '—' }}
                            @else
                                {{ $reserva->fecha_hora_checkin?->format('d/m/Y H:i') ?? '—' }}
                            @endif
                        </flux:table.cell>
                        <flux:table.cell class="!text-slate-700">
                            {{ ($paginaHistorial === 'salidas' ? $reserva->usuarioCheckOut?->name : $reserva->usuarioCheckIn?->name) ?? '—' }}
                        </flux:table.cell>
                        <flux:table.cell>
                            <x-estado-badge :estado="$reserva->estado" />
                        </flux:table.cell>
                    </flux:table.row>
                @empty
                    <flux:table.row>
                        <flux:table.cell colspan="6" align="center">
                            <div class="flex flex-col items-center gap-2 py-8 text-slate-500 dark:text-slate-400">
                                <flux:icon.calendar-days class="size-8 text-slate-400" />
                                <p>
                                    @if ($paginaHistorial === 'salidas')
                                        Aún no se han registrado check-outs.
                                    @else
                                        Aún no se han registrado check-ins.
                                    @endif
                                </p>
                            </div>
                        </flux:table.cell>
                    </flux:table.row>
                @endforelse
            </flux:table.rows>
        </flux:table>

        @if ($historial->hasPages())
            <div class="mt-4 border-t border-slate-50 pt-3 dark:border-zinc-800">
                {{ $historial->links() }}
            </div>
        @endif
    </flux:card>
@endif
