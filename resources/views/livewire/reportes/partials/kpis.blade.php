@php
    $balancePositivo = (float) $indicadores['resultadoNeto'] >= 0;
    $esDia = $periodo === \App\Services\Reportes\ResumenReportes::DIARIO;
    $etiquetaOcupadas = $esDia ? 'habitaciones ocupadas' : 'noches-habitación ocupadas';
    $etiquetaDisponibles = $esDia ? 'habitaciones disponibles' : 'noches-habitación disponibles';
@endphp

<div class="grid gap-5 sm:grid-cols-2 xl:grid-cols-4">
    <div class="group relative overflow-hidden rounded-2xl border border-slate-200/80 bg-white p-6 shadow-sm transition-all duration-300 hover:-translate-y-1 hover:shadow-lg dark:border-zinc-700 dark:bg-slate-800">
        <div class="absolute inset-x-0 top-0 h-1 bg-gradient-to-r from-amber-500 to-amber-300"></div>

        <div class="flex items-start justify-between gap-4">
            <div>
                <p class="text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">Ocupación</p>
                <p class="mt-3 text-4xl font-extrabold tracking-tight text-slate-900 dark:text-white">
                    {{ number_format((float) $indicadores['ocupacion'], 1) }}%
                </p>
            </div>

            <span class="flex size-12 shrink-0 items-center justify-center rounded-xl bg-amber-50 text-amber-600 transition-colors duration-300 group-hover:bg-amber-600 group-hover:text-white dark:bg-amber-500/10 dark:text-amber-400">
                <flux:icon.building-office-2 class="size-6" />
            </span>
        </div>

        <p class="mt-3 text-sm font-medium text-slate-500 dark:text-slate-400">
            {{ number_format($indicadores['habitacionesOcupadas']) }} {{ $etiquetaOcupadas }} de
            {{ number_format($indicadores['habitacionesOcupadas'] + $indicadores['habitacionesDisponibles']) }}
        </p>

        <p class="mt-1 text-xs font-medium text-slate-400 dark:text-slate-500">
            {{ number_format($indicadores['habitacionesDisponibles']) }} {{ $etiquetaDisponibles }} · {{ $indicadores['totalHabitaciones'] }} habitaciones
        </p>
    </div>

    <div class="group relative overflow-hidden rounded-2xl border border-slate-200/80 bg-white p-6 shadow-sm transition-all duration-300 hover:-translate-y-1 hover:shadow-lg dark:border-zinc-700 dark:bg-slate-800">
        <div class="absolute inset-x-0 top-0 h-1 bg-gradient-to-r from-emerald-500 to-emerald-300"></div>

        <div class="flex items-start justify-between gap-4">
            <div>
                <p class="text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">Ingresos totales</p>
                <p class="mt-3 text-4xl font-extrabold tracking-tight text-slate-900 dark:text-white">
                    ${{ number_format((float) $indicadores['ingresos'], 2) }}
                </p>
            </div>

            <span class="flex size-12 shrink-0 items-center justify-center rounded-xl bg-emerald-50 text-emerald-600 transition-colors duration-300 group-hover:bg-emerald-600 group-hover:text-white dark:bg-emerald-500/10 dark:text-emerald-400">
                <flux:icon.arrow-trending-up class="size-6" />
            </span>
        </div>

        <p class="mt-4 inline-flex items-center gap-1.5 rounded-full bg-emerald-50 px-2.5 py-1 text-xs font-semibold text-emerald-700 dark:bg-emerald-500/10 dark:text-emerald-300">
            <span class="size-1.5 rounded-full bg-emerald-500"></span>
            {{ number_format($indicadores['pagosCantidad']) }} pagos completados
        </p>
    </div>

    <div class="group relative overflow-hidden rounded-2xl border border-slate-200/80 bg-white p-6 shadow-sm transition-all duration-300 hover:-translate-y-1 hover:shadow-lg dark:border-zinc-700 dark:bg-slate-800">
        <div class="absolute inset-x-0 top-0 h-1 bg-gradient-to-r from-rose-500 to-rose-300"></div>

        <div class="flex items-start justify-between gap-4">
            <div>
                <p class="text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">Gastos totales</p>
                <p class="mt-3 text-4xl font-extrabold tracking-tight text-slate-900 dark:text-white">
                    ${{ number_format((float) $indicadores['gastos'], 2) }}
                </p>
            </div>

            <span class="flex size-12 shrink-0 items-center justify-center rounded-xl bg-rose-50 text-rose-600 transition-colors duration-300 group-hover:bg-rose-600 group-hover:text-white dark:bg-rose-500/10 dark:text-rose-400">
                <flux:icon.arrow-trending-down class="size-6" />
            </span>
        </div>

        <p class="mt-4 inline-flex items-center gap-1.5 rounded-full bg-rose-50 px-2.5 py-1 text-xs font-semibold text-rose-700 dark:bg-rose-500/10 dark:text-rose-300">
            <span class="size-1.5 rounded-full bg-rose-500"></span>
            Egresos registrados
        </p>
    </div>

    <div class="group relative overflow-hidden rounded-2xl border border-slate-200/80 bg-white p-6 shadow-sm transition-all duration-300 hover:-translate-y-1 hover:shadow-lg dark:border-zinc-700 dark:bg-slate-800">
        <div class="absolute inset-x-0 top-0 h-1 bg-gradient-to-r {{ $balancePositivo ? 'from-sky-500 to-sky-300' : 'from-rose-500 to-rose-300' }}"></div>

        <div class="flex items-start justify-between gap-4">
            <div>
                <p class="text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">Balance del periodo</p>
                <p class="mt-3 text-4xl font-extrabold tracking-tight {{ $balancePositivo ? 'text-slate-900 dark:text-white' : 'text-rose-600 dark:text-rose-400' }}">
                    ${{ number_format((float) $indicadores['resultadoNeto'], 2) }}
                </p>
            </div>

            <span class="flex size-12 shrink-0 items-center justify-center rounded-xl transition-colors duration-300 {{ $balancePositivo ? 'bg-sky-50 text-sky-600 group-hover:bg-sky-600 group-hover:text-white dark:bg-sky-500/10 dark:text-sky-400' : 'bg-rose-50 text-rose-600 group-hover:bg-rose-600 group-hover:text-white dark:bg-rose-500/10 dark:text-rose-400' }}">
                <flux:icon.scale class="size-6" />
            </span>
        </div>

        <p class="mt-4 inline-flex items-center gap-1.5 rounded-full px-2.5 py-1 text-xs font-semibold {{ $balancePositivo ? 'bg-sky-50 text-sky-700 dark:bg-sky-500/10 dark:text-sky-300' : 'bg-rose-50 text-rose-700 dark:bg-rose-500/10 dark:text-rose-300' }}">
            <span class="size-1.5 rounded-full {{ $balancePositivo ? 'bg-sky-500' : 'bg-rose-500' }}"></span>
            {{ $balancePositivo ? 'Ingresos por encima de gastos' : 'Gastos por encima de ingresos' }}
        </p>
    </div>

    <div class="rounded-2xl border border-slate-200/80 bg-white p-5 shadow-sm dark:border-zinc-700 dark:bg-slate-800">
        <p class="text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">Reservaciones</p>
        <p class="mt-2 text-3xl font-extrabold text-slate-900 dark:text-white">{{ number_format($indicadores['totalReservaciones']) }}</p>
        <p class="mt-1 text-sm font-medium text-slate-500 dark:text-slate-400">
            {{ $indicadores['reservacionesCanceladas'] }} canceladas en el periodo
        </p>
    </div>

    <div class="rounded-2xl border border-slate-200/80 bg-white p-5 shadow-sm dark:border-zinc-700 dark:bg-slate-800">
        <p class="text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">Check-in / Check-out</p>
        <p class="mt-2 text-3xl font-extrabold text-slate-900 dark:text-white">
            {{ number_format($indicadores['checkins']) }} <span class="text-lg font-semibold text-slate-400">/</span> {{ number_format($indicadores['checkouts']) }}
        </p>
        <p class="mt-1 text-sm font-medium text-slate-500 dark:text-slate-400">Llegadas y salidas registradas</p>
    </div>

    <div class="rounded-2xl border border-slate-200/80 bg-white p-5 shadow-sm dark:border-zinc-700 dark:bg-slate-800">
        <p class="text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">Servicios vendidos</p>
        <p class="mt-2 text-3xl font-extrabold text-slate-900 dark:text-white">{{ number_format($indicadores['serviciosCantidad']) }}</p>
        <p class="mt-1 text-sm font-medium text-slate-500 dark:text-slate-400">
            ${{ number_format((float) $indicadores['serviciosTotal'], 2) }} en cargos al folio
        </p>
    </div>

    <div class="rounded-2xl border border-slate-200/80 bg-white p-5 shadow-sm dark:border-zinc-700 dark:bg-slate-800">
        <p class="text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">Saldo pendiente de cobro</p>
        <p class="mt-2 text-3xl font-extrabold {{ (float) $indicadores['saldoPendiente'] > 0 ? 'text-rose-600 dark:text-rose-400' : 'text-emerald-600 dark:text-emerald-400' }}">
            ${{ number_format((float) $indicadores['saldoPendiente'], 2) }}
        </p>
        <p class="mt-1 text-sm font-medium text-slate-500 dark:text-slate-400">
            Cargos del periodo: ${{ number_format((float) $indicadores['cargosTotales'], 2) }}
        </p>
    </div>
</div>
