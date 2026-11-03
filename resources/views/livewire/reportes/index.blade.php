<div class="space-y-6 pb-10">
    <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <h1 class="text-2xl font-bold tracking-tight text-slate-900 dark:text-white">Reportes</h1>
            <p class="mt-1 text-sm font-medium text-slate-500 dark:text-slate-400">
                Indicadores, ocupación y resumen financiero de la operación del hotel.
            </p>
        </div>

        <span class="inline-flex items-center gap-2 self-start rounded-full border border-slate-200 bg-white px-3 py-1.5 text-xs font-semibold text-slate-600 shadow-sm sm:self-auto dark:border-zinc-700 dark:bg-slate-800 dark:text-slate-300">
            <span class="size-2 rounded-full bg-emerald-500"></span>
            Corte: {{ now()->format('d/m/Y') }}
        </span>
    </div>

    @include('livewire.reportes.partials.kpis')

    @include('livewire.reportes.partials.graficas')
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
                return null;
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
            animation: {
                duration: 700,
                easing: 'easeOutQuart',
            },
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: {
                        usePointStyle: true,
                        boxWidth: 8,
                        padding: 18,
                        font: {
                            size: 12,
                            weight: '600',
                        },
                    },
                },
                tooltip: {
                    backgroundColor: '#0f172a',
                    padding: 12,
                    cornerRadius: 10,
                    displayColors: true,
                },
            },
        };

        crearGrafica('grafica-reservas-estado', {
            type: 'doughnut',
            data: {
                labels: datos.reservas?.etiquetas ?? [],
                datasets: [{
                    data: datos.reservas?.valores ?? [],
                    backgroundColor: (datos.reservas?.etiquetas ?? []).map((estado) => coloresEstado[estado] ?? '#94a3b8'),
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
                        callbacks: {
                            label: (contexto) => ` ${contexto.label}: ${contexto.parsed} reservaciones`,
                        },
                    },
                },
            },
        });

        crearGrafica('grafica-pagos-metodo', {
            type: 'bar',
            data: {
                labels: datos.pagos?.etiquetas ?? [],
                datasets: [{
                    label: 'Monto cobrado',
                    data: datos.pagos?.valores ?? [],
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
                animation: {
                    duration: 700,
                    easing: 'easeOutQuart',
                },
                plugins: {
                    legend: {
                        display: false,
                    },
                    tooltip: {
                        backgroundColor: '#0f172a',
                        padding: 12,
                        cornerRadius: 10,
                        callbacks: {
                            label: (contexto) => ` ${formatearMoneda(contexto.parsed.y)} en ${contexto.parsed.x !== undefined ? datos.pagos.cantidades[contexto.dataIndex] : ''} pagos`,
                        },
                    },
                },
                scales: {
                    x: {
                        grid: {
                            display: false,
                        },
                        ticks: {
                            color: '#64748b',
                            font: {
                                weight: '600',
                            },
                        },
                    },
                    y: {
                        beginAtZero: true,
                        grid: {
                            color: 'rgba(148, 163, 184, 0.18)',
                        },
                        ticks: {
                            color: '#94a3b8',
                            callback: (valor) => formatearMoneda(valor),
                        },
                    },
                },
            },
        });

        crearGrafica('grafica-gastos-categoria', {
            type: 'bar',
            data: {
                labels: datos.gastos?.etiquetas ?? [],
                datasets: [{
                    label: 'Gasto acumulado',
                    data: datos.gastos?.valores ?? [],
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
                animation: {
                    duration: 700,
                    easing: 'easeOutQuart',
                },
                plugins: {
                    legend: {
                        display: false,
                    },
                    tooltip: {
                        backgroundColor: '#0f172a',
                        padding: 12,
                        cornerRadius: 10,
                        callbacks: {
                            label: (contexto) => ` ${formatearMoneda(contexto.parsed.x)}`,
                        },
                    },
                },
                scales: {
                    x: {
                        beginAtZero: true,
                        grid: {
                            color: 'rgba(148, 163, 184, 0.18)',
                        },
                        ticks: {
                            color: '#94a3b8',
                            callback: (valor) => formatearMoneda(valor),
                        },
                    },
                    y: {
                        grid: {
                            display: false,
                        },
                        ticks: {
                            color: '#64748b',
                            font: {
                                weight: '600',
                            },
                        },
                    },
                },
            },
        });

        crearGrafica('grafica-ingresos-gastos', {
            type: 'bar',
            data: {
                labels: datos.finanzas?.etiquetas ?? [],
                datasets: [{
                    label: 'Monto',
                    data: datos.finanzas?.valores ?? [],
                    backgroundColor: ['rgba(16, 185, 129, 0.75)', 'rgba(244, 63, 94, 0.75)'],
                    hoverBackgroundColor: ['rgba(16, 185, 129, 1)', 'rgba(244, 63, 94, 1)'],
                    borderColor: ['#059669', '#e11d48'],
                    borderWidth: 1,
                    borderRadius: 10,
                    borderSkipped: false,
                    maxBarThickness: 88,
                }],
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                animation: {
                    duration: 700,
                    easing: 'easeOutQuart',
                },
                plugins: {
                    legend: {
                        display: false,
                    },
                    tooltip: {
                        backgroundColor: '#0f172a',
                        padding: 12,
                        cornerRadius: 10,
                        callbacks: {
                            label: (contexto) => ` ${contexto.label}: ${formatearMoneda(contexto.parsed.y)}`,
                        },
                    },
                },
                scales: {
                    x: {
                        grid: {
                            display: false,
                        },
                        ticks: {
                            color: '#64748b',
                            font: {
                                weight: '600',
                            },
                        },
                    },
                    y: {
                        beginAtZero: true,
                        grid: {
                            color: 'rgba(148, 163, 184, 0.18)',
                        },
                        ticks: {
                            color: '#94a3b8',
                            callback: (valor) => formatearMoneda(valor),
                        },
                    },
                },
            },
        });
    };

    iniciarGraficasReportes();
    })();
</script>
@endscript
