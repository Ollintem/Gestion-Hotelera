<div class="grid gap-6 xl:grid-cols-2">
    <section class="rounded-2xl border border-slate-200/80 bg-white p-6 shadow-sm dark:border-zinc-700 dark:bg-slate-800">
        <div class="flex items-start justify-between gap-4">
            <div>
                <h2 class="text-lg font-bold text-slate-900 dark:text-white">Ingresos vs. gastos</h2>
                <p class="mt-1 text-sm font-medium text-slate-500 dark:text-slate-400">
                    Comportamiento financiero durante {{ $rangoTexto }}.
                </p>
            </div>

            <span class="flex size-11 shrink-0 items-center justify-center rounded-xl bg-sky-50 text-sky-600 dark:bg-sky-500/10 dark:text-sky-400">
                <flux:icon.scale class="size-5" />
            </span>
        </div>

        <div class="relative mt-6 h-72">
            <div wire:ignore class="absolute inset-0">
                <canvas id="grafica-ingresos-gastos" role="img" aria-label="Gráfica de ingresos y gastos del periodo"></canvas>
            </div>

            @if ((float) $indicadores['ingresos'] === 0.0 && (float) $indicadores['gastos'] === 0.0)
                <div class="pointer-events-none absolute inset-0 flex items-center justify-center">
                    <p class="rounded-full bg-slate-100 px-3 py-1.5 text-sm font-medium text-slate-500 dark:bg-zinc-700 dark:text-slate-300">
                        Sin movimientos financieros
                    </p>
                </div>
            @endif
        </div>
    </section>

    <section class="rounded-2xl border border-slate-200/80 bg-white p-6 shadow-sm dark:border-zinc-700 dark:bg-slate-800">
        <div class="flex items-start justify-between gap-4">
            <div>
                <h2 class="text-lg font-bold text-slate-900 dark:text-white">Reservaciones por estado</h2>
                <p class="mt-1 text-sm font-medium text-slate-500 dark:text-slate-400">
                    Distribución de las reservaciones realizadas en el periodo.
                </p>
            </div>

            <span class="flex size-11 shrink-0 items-center justify-center rounded-xl bg-amber-50 text-amber-600 dark:bg-amber-500/10 dark:text-amber-400">
                <flux:icon.chart-pie class="size-5" />
            </span>
        </div>

        <div class="relative mt-6 h-72">
            <div wire:ignore class="absolute inset-0">
                <canvas id="grafica-reservas-estado" role="img" aria-label="Gráfica de reservaciones agrupadas por estado"></canvas>
            </div>

            @if ($reservasPorEstado->isEmpty())
                <div class="pointer-events-none absolute inset-0 flex items-center justify-center">
                    <p class="rounded-full bg-slate-100 px-3 py-1.5 text-sm font-medium text-slate-500 dark:bg-zinc-700 dark:text-slate-300">
                        Sin reservaciones registradas
                    </p>
                </div>
            @endif
        </div>
    </section>

    <section class="rounded-2xl border border-slate-200/80 bg-white p-6 shadow-sm dark:border-zinc-700 dark:bg-slate-800">
        <div class="flex items-start justify-between gap-4">
            <div>
                <h2 class="text-lg font-bold text-slate-900 dark:text-white">Pagos por método</h2>
                <p class="mt-1 text-sm font-medium text-slate-500 dark:text-slate-400">
                    Dinero efectivamente recibido según el método de pago.
                </p>
            </div>

            <span class="flex size-11 shrink-0 items-center justify-center rounded-xl bg-emerald-50 text-emerald-600 dark:bg-emerald-500/10 dark:text-emerald-400">
                <flux:icon.credit-card class="size-5" />
            </span>
        </div>

        <div class="relative mt-6 h-72">
            <div wire:ignore class="absolute inset-0">
                <canvas id="grafica-pagos-metodo" role="img" aria-label="Gráfica de pagos agrupados por método de pago"></canvas>
            </div>

            @if ($pagosPorMetodo->isEmpty())
                <div class="pointer-events-none absolute inset-0 flex items-center justify-center">
                    <p class="rounded-full bg-slate-100 px-3 py-1.5 text-sm font-medium text-slate-500 dark:bg-zinc-700 dark:text-slate-300">
                        Sin pagos registrados
                    </p>
                </div>
            @endif
        </div>
    </section>

    <section class="rounded-2xl border border-slate-200/80 bg-white p-6 shadow-sm dark:border-zinc-700 dark:bg-slate-800">
        <div class="flex items-start justify-between gap-4">
            <div>
                <h2 class="text-lg font-bold text-slate-900 dark:text-white">Gastos por categoría</h2>
                <p class="mt-1 text-sm font-medium text-slate-500 dark:text-slate-400">
                    Egresos acumulados en cada rubro del hotel.
                </p>
            </div>

            <span class="flex size-11 shrink-0 items-center justify-center rounded-xl bg-rose-50 text-rose-600 dark:bg-rose-500/10 dark:text-rose-400">
                <flux:icon.banknotes class="size-5" />
            </span>
        </div>

        <div class="relative mt-6 h-72">
            <div wire:ignore class="absolute inset-0">
                <canvas id="grafica-gastos-categoria" role="img" aria-label="Gráfica de gastos agrupados por categoría"></canvas>
            </div>

            @if ($gastosPorCategoria->isEmpty())
                <div class="pointer-events-none absolute inset-0 flex items-center justify-center">
                    <p class="rounded-full bg-slate-100 px-3 py-1.5 text-sm font-medium text-slate-500 dark:bg-zinc-700 dark:text-slate-300">
                        Sin gastos registrados
                    </p>
                </div>
            @endif
        </div>
    </section>

    <section class="rounded-2xl border border-slate-200/80 bg-white p-6 shadow-sm dark:border-zinc-700 dark:bg-slate-800">
        <div class="flex items-start justify-between gap-4">
            <div>
                <h2 class="text-lg font-bold text-slate-900 dark:text-white">Ocupación de habitaciones</h2>
                <p class="mt-1 text-sm font-medium text-slate-500 dark:text-slate-400">
                    Porcentaje de ocupación por día dentro del periodo.
                </p>
            </div>

            <span class="flex size-11 shrink-0 items-center justify-center rounded-xl bg-indigo-50 text-indigo-600 dark:bg-indigo-500/10 dark:text-indigo-400">
                <flux:icon.building-office-2 class="size-5" />
            </span>
        </div>

        <div class="relative mt-6 h-72">
            <div wire:ignore class="absolute inset-0">
                <canvas id="grafica-ocupacion" role="img" aria-label="Gráfica de ocupación diaria de habitaciones"></canvas>
            </div>
        </div>
    </section>

    <section class="rounded-2xl border border-slate-200/80 bg-white p-6 shadow-sm dark:border-zinc-700 dark:bg-slate-800">
        <div class="flex items-start justify-between gap-4">
            <div>
                <h2 class="text-lg font-bold text-slate-900 dark:text-white">Servicios más solicitados</h2>
                <p class="mt-1 text-sm font-medium text-slate-500 dark:text-slate-400">
                    Servicios con mayor cantidad vendida en el periodo.
                </p>
            </div>

            <span class="flex size-11 shrink-0 items-center justify-center rounded-xl bg-violet-50 text-violet-600 dark:bg-violet-500/10 dark:text-violet-400">
                <flux:icon.sparkles class="size-5" />
            </span>
        </div>

        <div class="relative mt-6 h-72">
            <div wire:ignore class="absolute inset-0">
                <canvas id="grafica-servicios-top" role="img" aria-label="Gráfica de servicios más solicitados"></canvas>
            </div>

            @if ($serviciosTop->isEmpty())
                <div class="pointer-events-none absolute inset-0 flex items-center justify-center">
                    <p class="rounded-full bg-slate-100 px-3 py-1.5 text-sm font-medium text-slate-500 dark:bg-zinc-700 dark:text-slate-300">
                        Sin servicios vendidos
                    </p>
                </div>
            @endif
        </div>
    </section>
</div>

<script type="application/json" id="reportes-datos">
    {!! json_encode($graficas, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) !!}
</script>
