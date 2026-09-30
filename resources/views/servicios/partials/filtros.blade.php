{{-- ======================================================
     BARRA DE BÚSQUEDA Y FILTROS

     Es la única barra de filtros del módulo: el buscador acota por
     nombre o descripción y el desplegable acota por categoría. Ambos
     viajan en la URL, así que el recorte se puede compartir.

     El desplegable lista solo las categorías activas y manda el
     identificador, que es la llave foránea real de la clasificación.
     ====================================================== --}}

<div class="flex gap-4 mb-6">
    <div class="w-full">
        <input
            type="text"
            wire:model.live.debounce.400ms="search"
            placeholder="Buscar por nombre o descripción..."
            aria-label="Buscar servicios"
            class="w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-800 shadow-sm transition duration-150 placeholder:text-slate-400 focus:border-amber-500 focus:outline-none focus:ring-2 focus:ring-amber-500/10"
        />
    </div>

    <div>
        <select
            wire:model.live="categoriaFiltro"
            aria-label="Filtrar por categoría"
            class="rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-800 shadow-sm transition duration-150 focus:border-amber-500 focus:outline-none focus:ring-2 focus:ring-amber-500/10"
        >
            <option value="">Todas las categorías</option>
            @foreach ($categorias as $categoria)
                <option value="{{ $categoria->id }}">{{ $categoria->nombre }}</option>
            @endforeach
        </select>
    </div>

    <div>
        <button
            type="button"
            wire:click="limpiarFiltros"
            class="rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-semibold text-slate-600 shadow-sm transition duration-150 hover:bg-slate-50 hover:text-slate-900 focus:outline-none focus:ring-2 focus:ring-amber-500/10"
        >
            Limpiar
        </button>
    </div>
</div>
