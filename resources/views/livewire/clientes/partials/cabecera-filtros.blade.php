<div class="animate-fade-in">
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-2xl font-bold tracking-tight text-slate-900 dark:text-white">Clientes</h1>
            <p class="mt-1 text-sm font-medium text-slate-500 dark:text-slate-400">
                {{ $this->totalClientes }}
                {{ $this->totalClientes === 1 ? 'huésped registrado' : 'huéspedes registrados' }}
            </p>
        </div>

        <flux:button
            type="button"
            variant="primary"
            icon="plus"
            wire:click="crear"
            class="shrink-0"
        >
            Nuevo cliente
        </flux:button>
    </div>

    <div class="mt-6 grid gap-3 sm:grid-cols-[minmax(0,1fr)_18rem]">
        <div class="relative">
            <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-4 text-slate-400 dark:text-zinc-500">
                <flux:icon.magnifying-glass class="size-5" />
            </span>
            <input
                type="search"
                wire:model.live.debounce.300ms="search"
                placeholder="Buscar por nombre, correo, teléfono o identificación..."
                aria-label="Buscar clientes"
                class="block w-full rounded-2xl border-0 bg-white py-3.5 pl-12 pr-4 text-sm text-slate-900 shadow-sm ring-1 ring-inset ring-slate-200 transition-all duration-300 ease-out placeholder:text-slate-400 focus:ring-2 focus:ring-inset focus:ring-amber-500 dark:bg-slate-800 dark:text-white dark:ring-zinc-700 dark:placeholder:text-zinc-500"
            />
        </div>

        <div>
            <x-dropdown
                wire:model.live="filtroEstado"
                aria-label="Filtrar clientes por estado"
                :selected="$filtroEstado"
                :options="\App\Livewire\Clientes::OPCIONES_ESTADO"
                clave="filtro-clientes-{{ $filtroEstado }}"
            >
                <x-slot:leadingIcon>
                    <flux:icon.funnel class="size-4" />
                </x-slot:leadingIcon>
            </x-dropdown>
        </div>
    </div>

    @if ($this->hayFiltrosActivos)
        <div class="mt-4 flex flex-wrap items-center justify-between gap-3 rounded-xl border border-slate-200 bg-white/70 px-4 py-3 text-sm dark:border-zinc-700 dark:bg-slate-800/60">
            <p class="font-medium text-slate-600 dark:text-slate-300">
                Mostrando
                <span class="font-bold text-slate-900 dark:text-white">{{ $this->clientes->total() }}</span>
                {{ $this->clientes->total() === 1 ? 'resultado' : 'resultados' }}
                de {{ $this->totalClientes }}.
            </p>

            <button
                type="button"
                wire:click="limpiarFiltros"
                class="inline-flex items-center gap-1.5 rounded-lg px-2 py-1 text-xs font-semibold text-amber-700 transition-colors hover:bg-amber-50 focus:outline-none focus:ring-2 focus:ring-amber-500 dark:text-amber-300 dark:hover:bg-amber-500/10"
            >
                <flux:icon.x-mark class="size-3.5" />
                Limpiar filtros
            </button>
        </div>
    @endif
</div>
