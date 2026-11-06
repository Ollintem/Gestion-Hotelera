{{-- ======================================================
     TARJETAS MÉTRICAS POR PUESTO (KPIs)
     ====================================================== --}}

<div class="mb-4 flex animate-fade-in items-center justify-between">
    <div class="flex items-center gap-2">
        <flux:icon.chart-bar class="size-5 text-slate-400 transition-colors duration-300 dark:text-slate-500" />
        <h2 class="text-base font-bold text-slate-900 dark:text-white">Personal por puesto laboral</h2>
    </div>
    <p class="text-sm font-medium text-slate-500 dark:text-slate-400">Conteo en tiempo real</p>
</div>

<div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-5">
    @foreach ($this->kpisPuestos as $kpi)
        <div
            class="group animate-fade-in-up rounded-2xl border border-slate-100 bg-white p-5 shadow-sm transition-all duration-300 ease-out hover:-translate-y-1 hover:border-slate-200 hover:shadow-md active:scale-[0.98] dark:border-slate-700/50 dark:bg-slate-800 dark:hover:border-slate-600"
            style="animation-delay: {{ $loop->index * 70 }}ms"
        >
            <div class="flex items-start justify-between gap-3">
                <span class="flex size-11 shrink-0 items-center justify-center rounded-xl {{ $kpi['color_fondo'] }} transition-transform duration-300 ease-out group-hover:scale-110">
                    <flux:icon :name="$kpi['icono']" class="size-5 {{ $kpi['color_texto'] }} transition-colors duration-300" />
                </span>
                <span class="text-3xl font-bold leading-none text-slate-900 dark:text-white">{{ $kpi['conteo'] }}</span>
            </div>

            <div class="mt-4">
                <p class="truncate text-sm font-bold text-slate-800 dark:text-slate-100">{{ $kpi['etiqueta'] }}</p>
                <p class="mt-0.5 text-xs font-medium text-slate-500 dark:text-slate-400">
                    {{ $kpi['conteo'] === 1 ? 'empleado' : 'empleados' }}
                </p>
            </div>
        </div>
    @endforeach
</div>