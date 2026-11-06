<div class="space-y-6 pb-10">
    <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <h1 class="text-2xl font-bold tracking-tight text-slate-900 dark:text-white">Reportes</h1>
            <p class="mt-1 text-sm font-medium text-slate-500 dark:text-slate-400">
                Indicadores, ocupación y resumen financiero de la operación del hotel por periodo.
            </p>
        </div>

        <div class="flex flex-col items-start gap-3 sm:items-end">
            <span class="inline-flex items-center gap-2 self-start rounded-full border border-slate-200 bg-white px-3 py-1.5 text-xs font-semibold text-slate-600 shadow-sm sm:self-auto dark:border-zinc-700 dark:bg-slate-800 dark:text-slate-300">
                <span class="size-2 rounded-full bg-emerald-500"></span>
                {{ $rangoTexto }}
            </span>

            <a
                href="{{ $urlPdf }}"
                target="_blank"
                rel="noopener"
                class="inline-flex items-center gap-2 rounded-xl bg-amber-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition-colors duration-200 hover:bg-amber-700 focus:outline-none focus-visible:ring-2 focus-visible:ring-amber-500 focus-visible:ring-offset-2 dark:focus-visible:ring-offset-slate-900"
            >
                <flux:icon.document-arrow-down class="size-4" />
                Exportar PDF
            </a>
        </div>
    </div>

    <section class="rounded-2xl border border-slate-200/80 bg-white p-5 shadow-sm dark:border-zinc-700 dark:bg-slate-800">
        <div class="flex flex-col gap-5 lg:flex-row lg:items-end lg:justify-between">
            <div>
                <p class="text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">Periodicidad</p>

                <div class="mt-2 inline-flex rounded-xl bg-slate-100 p-1 dark:bg-zinc-900">
                    @foreach ($periodos as $clave => $etiqueta)
                        <button
                            type="button"
                            wire:click="$set('periodo', '{{ $clave }}')"
                            class="rounded-lg px-4 py-2 text-sm font-semibold transition-colors duration-200 {{ $periodo === $clave ? 'bg-white text-slate-900 shadow-sm dark:bg-slate-700 dark:text-white' : 'text-slate-500 hover:text-slate-800 dark:text-slate-400 dark:hover:text-slate-200' }}"
                        >
                            {{ $etiqueta }}
                        </button>
                    @endforeach
                </div>
            </div>

            <div class="flex flex-wrap items-end gap-3">
                @if ($periodo === \App\Services\Reportes\ResumenReportes::DIARIO)
                    <label class="flex flex-col gap-1.5">
                        <span class="text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">Día</span>
                        <input
                            type="date"
                            wire:model.live="fecha"
                            class="rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm font-medium text-slate-700 shadow-sm focus:border-amber-500 focus:outline-none focus:ring-2 focus:ring-amber-500/20 dark:border-zinc-700 dark:bg-zinc-900 dark:text-slate-200"
                        >
                    </label>
                @elseif ($periodo === \App\Services\Reportes\ResumenReportes::SEMANAL)
                    <label class="flex flex-col gap-1.5">
                        <span class="text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">Semana (lunes a domingo)</span>
                        <input
                            type="week"
                            wire:model.live="semana"
                            class="rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm font-medium text-slate-700 shadow-sm focus:border-amber-500 focus:outline-none focus:ring-2 focus:ring-amber-500/20 dark:border-zinc-700 dark:bg-zinc-900 dark:text-slate-200"
                        >
                    </label>
                @else
                    <label class="flex flex-col gap-1.5">
                        <span class="text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">Mes</span>
                        <select
                            wire:model.live="mes"
                            class="min-w-36 rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm font-medium text-slate-700 shadow-sm focus:border-amber-500 focus:outline-none focus:ring-2 focus:ring-amber-500/20 dark:border-zinc-700 dark:bg-zinc-900 dark:text-slate-200"
                        >
                            @foreach ($meses as $numero => $nombre)
                                <option value="{{ $numero }}">{{ ucfirst($nombre) }}</option>
                            @endforeach
                        </select>
                    </label>

                    <label class="flex flex-col gap-1.5">
                        <span class="text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">Año</span>
                        <select
                            wire:model.live="anio"
                            class="min-w-28 rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm font-medium text-slate-700 shadow-sm focus:border-amber-500 focus:outline-none focus:ring-2 focus:ring-amber-500/20 dark:border-zinc-700 dark:bg-zinc-900 dark:text-slate-200"
                        >
                            @foreach ($anios as $numero)
                                <option value="{{ $numero }}">{{ $numero }}</option>
                            @endforeach
                        </select>
                    </label>
                @endif

                <span wire:loading class="pb-2 text-xs font-semibold text-amber-600 dark:text-amber-400">
                    Actualizando…
                </span>
            </div>
        </div>

        <p class="mt-4 text-sm font-medium text-slate-500 dark:text-slate-400">
            Periodo consultado: <span class="font-bold text-slate-700 dark:text-slate-200">{{ $rangoTexto }}</span>.
            Los ingresos solo consideran pagos completados.
        </p>
    </section>

    @include('livewire.reportes.partials.kpis')

    @include('livewire.reportes.partials.graficas')

    @include('livewire.reportes.partials.tablas')
</div>

@assets
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.7/dist/chart.umd.min.js"></script>
@endassets

@script
<script>
    (() => {
        const iniciarGraficasReportes = () => {
            if (!window.Chart) {
                window.setTimeout(iniciarGraficasReportes, 80);
                return;
            }

            const nodoDatos = document.getElementById('reportes-datos');

            if (!nodoDatos) {
                return;
            }

            const datos = JSON.parse(nodoDatos.textContent || '{}');
            const formatearMoneda = (valor) => '$' + Number(valor).toLocaleString('es-MX', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2,
            });

            window.Chart.defaults.font.family = "'Instrument Sans', ui-sans-serif, system-ui, sans-serif";
            window.Chart.defaults.color = '#64748b';

            const crearGrafica = (id, configuracion) => {
                const canvas = document.getElementById(id);

                if (!canvas) {
                    return;
                }

                const anterior = window.Chart.getChart(canvas);

                if (anterior) {
                    anterior.destroy();
                }

                return new window.Chart(canvas, configuracion);
            };

            const coloresEstado = {
                Pendiente: '#f59e0b',
                Confirmada: '#10b981',
                Cancelada: '#f43f5e',
                Finalizada: '#64748b',
            };

            const opcionesDona = {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '68%',
                animation: { duration: 700, easing: 'easeOutQuart' },
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: { usePointStyle: true, boxWidth: 8, padding: 18, font: { size: 12, weight: '600' } },
                    },
                    tooltip: { backgroundColor: '#0f172a', padding: 12, cornerRadius: 10, displayColors: true },
                },
            };

            crearGrafica('grafica-ingresos-gastos', {
                type: 'bar',
                data: {
                    labels: datos.etiquetas ?? [],
                    datasets: [
                        {
                            label: 'Ingresos',
                            data: datos.ingresos ?? [],
                            backgroundColor: 'rgba(16, 185, 129, 0.75)',
                            hoverBackgroundColor: 'rgba(16, 185, 129, 1)',
                            borderColor: '#059669',
                            borderWidth: 1,
                            borderRadius: 7,
                            borderSkipped: false,
                            maxBarThickness: 28,
                        },
                        {
                            label: 'Gastos',
                            data: datos.gastos ?? [],
                            backgroundColor: 'rgba(244, 63, 94, 0.72)',
                            hoverBackgroundColor: 'rgba(244, 63, 94, 1)',
                            borderColor: '#e11d48',
                            borderWidth: 1,
                            borderRadius: 7,
                            borderSkipped: false,
                            maxBarThickness: 28,
                        },
                    ],
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    animation: { duration: 700, easing: 'easeOutQuart' },
                    plugins: {
                        legend: { position: 'bottom', labels: { usePointStyle: true, boxWidth: 8, padding: 14 } },
                        tooltip: {
                            backgroundColor: '#0f172a',
                            padding: 12,
                            cornerRadius: 10,
                            callbacks: { label: (contexto) => ` ${contexto.dataset.label}: ${formatearMoneda(contexto.parsed.y)}` },
                        },
                    },
                    scales: {
                        x: { grid: { display: false }, ticks: { color: '#64748b' } },
                        y: {
                            beginAtZero: true,
                            grid: { color: 'rgba(148, 163, 184, 0.18)' },
                            ticks: { color: '#94a3b8', callback: (valor) => formatearMoneda(valor) },
                        },
                    },
                },
            });

            crearGrafica('grafica-reservas-estado', {
                type: 'doughnut',
                data: {
                    labels: datos.reservasEstado?.etiquetas ?? [],
                    datasets: [{
                        data: datos.reservasEstado?.valores ?? [],
                        backgroundColor: (datos.reservasEstado?.etiquetas ?? []).map((estado) => coloresEstado[estado] ?? '#94a3b8'),
                        borderColor: '#ffffff',
                        borderWidth: 3,
                        hoverOffset: 8,
                    }],
                },
                options: {
                    ...opcionesDona,
                    plugins: {
                        ...opcionesDona.plugins,
                        tooltip: {
                            ...opcionesDona.plugins.tooltip,
                            callbacks: { label: (contexto) => ` ${contexto.label}: ${contexto.parsed} reservaciones` },
                        },
                    },
                },
            });

            crearGrafica('grafica-pagos-metodo', {
                type: 'bar',
                data: {
                    labels: datos.pagosMetodo?.etiquetas ?? [],
                    datasets: [{
                        label: 'Monto cobrado',
                        data: datos.pagosMetodo?.valores ?? [],
                        backgroundColor: 'rgba(16, 185, 129, 0.72)',
                        hoverBackgroundColor: 'rgba(16, 185, 129, 0.95)',
                        borderColor: '#059669',
                        borderWidth: 1,
                        borderRadius: 8,
                        borderSkipped: false,
                        maxBarThickness: 54,
                    }],
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    animation: { duration: 700, easing: 'easeOutQuart' },
                    plugins: {
                        legend: { display: false },
                        tooltip: {
                            backgroundColor: '#0f172a',
                            padding: 12,
                            cornerRadius: 10,
                            callbacks: {
                                label: (contexto) => ` ${formatearMoneda(contexto.parsed.y)} en ${datos.pagosMetodo?.cantidades?.[contexto.dataIndex] ?? 0} pagos`,
                            },
                        },
                    },
                    scales: {
                        x: { grid: { display: false }, ticks: { color: '#64748b', font: { weight: '600' } } },
                        y: {
                            beginAtZero: true,
                            grid: { color: 'rgba(148, 163, 184, 0.18)' },
                            ticks: { color: '#94a3b8', callback: (valor) => formatearMoneda(valor) },
                        },
                    },
                },
            });

            crearGrafica('grafica-gastos-categoria', {
                type: 'bar',
                data: {
                    labels: datos.gastosCategoria?.etiquetas ?? [],
                    datasets: [{
                        label: 'Gasto acumulado',
                        data: datos.gastosCategoria?.valores ?? [],
                        backgroundColor: 'rgba(244, 63, 94, 0.72)',
                        hoverBackgroundColor: 'rgba(244, 63, 94, 0.95)',
                        borderColor: '#e11d48',
                        borderWidth: 1,
                        borderRadius: 8,
                        borderSkipped: false,
                        maxBarThickness: 28,
                    }],
                },
                options: {
                    indexAxis: 'y',
                    responsive: true,
                    maintainAspectRatio: false,
                    animation: { duration: 700, easing: 'easeOutQuart' },
                    plugins: {
                        legend: { display: false },
                        tooltip: {
                            backgroundColor: '#0f172a',
                            padding: 12,
                            cornerRadius: 10,
                            callbacks: { label: (contexto) => ` ${formatearMoneda(contexto.parsed.x)}` },
                        },
                    },
                    scales: {
                        x: {
                            beginAtZero: true,
                            grid: { color: 'rgba(148, 163, 184, 0.18)' },
                            ticks: { color: '#94a3b8', callback: (valor) => formatearMoneda(valor) },
                        },
                        y: { grid: { display: false }, ticks: { color: '#64748b', font: { weight: '600' } } },
                    },
                },
            });

            crearGrafica('grafica-ocupacion', {
                type: 'line',
                data: {
                    labels: datos.etiquetas ?? [],
                    datasets: [{
                        label: 'Ocupación',
                        data: datos.ocupacion ?? [],
                        fill: true,
                        backgroundColor: 'rgba(99, 102, 241, 0.14)',
                        borderColor: '#6366f1',
                        borderWidth: 2,
                        pointBackgroundColor: '#6366f1',
                        pointRadius: 3,
                        pointHoverRadius: 6,
                        tension: 0.35,
                    }],
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    animation: { duration: 700, easing: 'easeOutQuart' },
                    plugins: {
                        legend: { display: false },
                        tooltip: {
                            backgroundColor: '#0f172a',
                            padding: 12,
                            cornerRadius: 10,
                            callbacks: { label: (contexto) => ` ${contexto.parsed.y}% de ocupación` },
                        },
                    },
                    scales: {
                        x: { grid: { display: false }, ticks: { color: '#64748b' } },
                        y: {
                            beginAtZero: true,
                            suggestedMax: 100,
                            grid: { color: 'rgba(148, 163, 184, 0.18)' },
                            ticks: { color: '#94a3b8', callback: (valor) => valor + '%' },
                        },
                    },
                },
            });

            crearGrafica('grafica-servicios-top', {
                type: 'bar',
                data: {
                    labels: datos.serviciosTop?.etiquetas ?? [],
                    datasets: [{
                        label: 'Cantidad vendida',
                        data: datos.serviciosTop?.valores ?? [],
                        backgroundColor: 'rgba(139, 92, 246, 0.72)',
                        hoverBackgroundColor: 'rgba(139, 92, 246, 0.95)',
                        borderColor: '#7c3aed',
                        borderWidth: 1,
                        borderRadius: 8,
                        borderSkipped: false,
                        maxBarThickness: 26,
                    }],
                },
                options: {
                    indexAxis: 'y',
                    responsive: true,
                    maintainAspectRatio: false,
                    animation: { duration: 700, easing: 'easeOutQuart' },
                    plugins: {
                        legend: { display: false },
                        tooltip: {
                            backgroundColor: '#0f172a',
                            padding: 12,
                            cornerRadius: 10,
                            callbacks: {
                                label: (contexto) => ` ${contexto.parsed.x} vendidos · ${formatearMoneda(datos.serviciosTop?.montos?.[contexto.dataIndex] ?? 0)}`,
                            },
                        },
                    },
                    scales: {
                        x: { beginAtZero: true, grid: { color: 'rgba(148, 163, 184, 0.18)' }, ticks: { color: '#94a3b8', precision: 0 } },
                        y: { grid: { display: false }, ticks: { color: '#64748b', font: { weight: '600' } } },
                    },
                },
            });
        };

        iniciarGraficasReportes();

        $wire.on('reporte-actualizado', () => {
            window.setTimeout(iniciarGraficasReportes, 60);
        });
    })();
</script>
@endscript
