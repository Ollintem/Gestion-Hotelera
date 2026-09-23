{{-- ======================================================
     FILTROS
     ====================================================== --}}

<div class="mb-6 animate-fade-in-up rounded-2xl border border-slate-200 bg-white p-5 shadow-sm [animation-delay:80ms] dark:border-slate-700 dark:bg-slate-800">
    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
        <div>
            <label for="busqueda-empleados" class="mb-1.5 block text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400">
                Buscar
            </label>
            <div class="relative">
                <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3.5 text-slate-400 dark:text-slate-500">
                    <flux:icon.magnifying-glass class="size-4" />
                </span>
                <input
                    id="busqueda-empleados"
                    type="text"
                    wire:model.live="busqueda"
                    placeholder="Nombre, correo electrónico…"
                    class="block w-full rounded-xl border-0 bg-white py-2.5 pl-10 pr-3.5 text-sm text-slate-900 shadow-sm ring-1 ring-inset ring-slate-300 transition-all duration-300 ease-out placeholder:text-slate-400 focus:ring-2 focus:ring-inset focus:ring-amber-500 dark:bg-zinc-900 dark:text-white dark:ring-zinc-700"
                />
            </div>
        </div>

        <div>
            <label for="filtro-estado" class="mb-1.5 block text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400">
                Estado
            </label>
            <x-dropdown
                    id="filtro-estado"
                    wire:model.live="filtroEstado"
                    :selected="$filtroEstado"
                    :options="['todos' => 'Todos', 'activos' => 'Activos', 'inactivos' => 'Inactivos']"
                />
        </div>

        <div class="flex items-end">
            <p class="text-sm text-slate-500 dark:text-slate-400">
                <span class="font-semibold text-slate-900 dark:text-white">{{ $empleados->total() }}</span>
                {{ $empleados->total() === 1 ? 'empleado encontrado' : 'empleados encontrados' }}
            </p>
        </div>
    </div>
</div>