{{-- ======================================================
     CABECERA DEL MÓDULO: TÍTULO, DESCRIPCIÓN Y FILTROS
     ====================================================== --}}

@php
    $campo = 'w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-800 shadow-sm transition-all duration-200 placeholder:text-slate-400 focus:border-amber-500 focus:outline-none focus:ring-2 focus:ring-amber-500/20 dark:border-zinc-700 dark:bg-zinc-900 dark:text-white';
    $etiqueta = 'mb-1.5 block text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400';
@endphp

<div class="mb-6">
    <flux:heading size="xl" class="!text-slate-900 !font-bold text-2xl">Check-in / Check-out</flux:heading>
    <flux:subheading class="!text-slate-600 !font-medium">
        Registra las llegadas y salidas de los huéspedes, y consulta los movimientos ya realizados.
    </flux:subheading>

    {{-- Filtros de las dos pestañas del módulo --}}
    <div class="mt-5 rounded-2xl border border-slate-200 bg-white p-4 shadow-sm dark:border-zinc-700 dark:bg-zinc-900">
        <div class="grid gap-4 md:grid-cols-4">
            <div class="md:col-span-2">
                <label for="filtro-busqueda" class="{{ $etiqueta }}">Huésped o folio</label>
                <div class="relative">
                    <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-slate-400 dark:zinc-500">
                        <flux:icon.magnifying-glass class="size-4" />
                    </span>
                    <input
                        id="filtro-busqueda"
                        type="text"
                        wire:model.live.debounce.400ms="filtroBusqueda"
                        placeholder="Buscar por nombre, apellido o folio…"
                        class="{{ $campo }} pl-9"
                    />
                </div>
            </div>

            <div>
                <label for="filtro-fecha" class="{{ $etiqueta }}">Fecha</label>
                <input
                    id="filtro-fecha"
                    type="date"
                    wire:model.live="filtroFecha"
                    class="{{ $campo }}"
                />
            </div>

            <div>
                <label for="filtro-habitacion" class="{{ $etiqueta }}">Habitación</label>
                <select id="filtro-habitacion" wire:model.live="filtroHabitacion" class="{{ $campo }}">
                    <option value="">Todas</option>
                    @foreach ($habitaciones as $habitacion)
                        <option value="{{ $habitacion->id }}">{{ $habitacion->numero_habitacion }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <div class="mt-4 flex justify-end">
            <flux:button type="button" size="sm" variant="ghost" icon="x-mark" wire:click="limpiarFiltros">
                Limpiar filtros
            </flux:button>
        </div>
    </div>
</div>
