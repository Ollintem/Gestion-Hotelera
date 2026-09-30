{{-- ======================================================
     ENCABEZADO DEL CATÁLOGO

     Solo el título y las acciones. La búsqueda y el filtro por
     categoría viven en `partials.filtros`: mantenerlos en los dos
     sitios los duplicaba en pantalla y hacía que el filtro quedara
     desincronizado del que el usuario estaba usando.
     ====================================================== --}}

<div class="animate-fade-in">
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-2xl font-bold tracking-tight text-slate-900 dark:text-white">Servicios</h1>
            <p class="mt-1 text-sm font-medium text-slate-500 dark:text-slate-400">
                {{ $kpis['totalServicios'] }} {{ $kpis['totalServicios'] === 1 ? 'servicio en catálogo' : 'servicios en catálogo' }}
            </p>
        </div>

        <div class="flex flex-wrap items-center gap-3">
            {{-- El alta y la edición viajan por el servidor: el formulario carga el
                 servicio y abre el diálogo en una sola petición, de modo que el
                 <dialog> nunca aparece con los datos de otro servicio. El consumo
                 no necesita datos del folio, así que Flux lo abre desde el
                 navegador. --}}
            @can('servicios.cargos')
                <button
                    type="button"
                    x-data
                    x-on:click="$dispatch('modal-show', { name: 'servicio-cargo' })"
                    class="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 shadow-sm transition-all duration-300 ease-out hover:scale-[1.02] hover:bg-slate-50 hover:shadow-lg focus:outline-none focus:ring-2 focus:ring-amber-500 active:scale-95 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-200"
                >
                    <flux:icon.receipt-percent class="size-4 text-amber-600" />
                    Cargar consumo
                </button>
            @endcan

            <button
                type="button"
                wire:click="crear"
                wire:loading.attr="disabled"
                wire:target="crear"
                wire:loading.class="opacity-70"
                class="inline-flex items-center gap-2 rounded-xl bg-amber-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition-all duration-300 ease-out hover:scale-[1.02] hover:bg-amber-700 hover:shadow-lg focus:outline-none focus:ring-2 focus:ring-amber-600 focus:ring-offset-2 active:scale-95 disabled:pointer-events-none disabled:opacity-70 dark:bg-amber-600 dark:hover:bg-amber-700"
            >
                <flux:icon.plus class="size-4" />
                Nuevo servicio
            </button>
        </div>
    </div>
</div>
