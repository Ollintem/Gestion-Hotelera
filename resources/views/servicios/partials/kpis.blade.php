{{-- ======================================================
     KPIs (Métricas Superiores)

     Las cifras llegan en el array $kpis desde el contenedor y
     describen el hotel completo, no el recorte que se esté viendo.
     ====================================================== --}}

<div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
    <div class="bg-white rounded-2xl border border-slate-100 p-4 shadow-sm">
        <p class="text-xs font-medium uppercase tracking-wider text-slate-500">
            CATÁLOGO
        </p>
        <p class="mt-2 text-2xl font-bold text-slate-900">
            {{ $kpis['totalServicios'] }}
        </p>
        <p class="mt-1 text-xs text-slate-400">
            Total de servicios
        </p>
    </div>

    <div class="bg-white rounded-2xl border border-slate-100 p-4 shadow-sm">
        <p class="text-xs font-medium uppercase tracking-wider text-slate-500">
            CATEGORÍAS
        </p>
        <p class="mt-2 text-2xl font-bold text-slate-900">
            {{ $kpis['categoriasActivas'] }}
        </p>
        <p class="mt-1 text-xs text-slate-400">
            Categorías activas
        </p>
    </div>

    <div class="bg-white rounded-2xl border border-slate-100 p-4 shadow-sm">
        <p class="text-xs font-medium uppercase tracking-wider text-slate-500">
            CARGOS REGISTRADOS
        </p>
        <p class="mt-2 text-2xl font-bold text-slate-900">
            {{ $kpis['cargosRegistrados'] }}
        </p>
        <p class="mt-1 text-xs text-slate-400">
            Cargos en reserva_servicio
        </p>
    </div>

    <div class="bg-white rounded-2xl border border-slate-100 p-4 shadow-sm">
        <p class="text-xs font-medium uppercase tracking-wider text-slate-500">
            CONSUMOS FACTURADOS
        </p>
        <p class="mt-2 text-2xl font-bold text-slate-900">
            ${{ number_format($kpis['consumosFacturados'], 2) }}
        </p>
        <p class="mt-1 text-xs text-slate-400">
            Reservas finalizadas
        </p>
    </div>
</div>
