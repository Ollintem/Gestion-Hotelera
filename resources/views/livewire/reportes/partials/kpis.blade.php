@php
    $balancePositivo = (float) $balance >= 0;
@endphp

<div class="grid gap-5 sm:grid-cols-2 xl:grid-cols-4">
    <div class="group relative overflow-hidden rounded-2xl border border-slate-200/80 bg-white p-6 shadow-sm transition-all duration-300 hover:-translate-y-1 hover:shadow-lg dark:border-zinc-700 dark:bg-slate-800">
        <div class="absolute inset-x-0 top-0 h-1 bg-gradient-to-r from-amber-500 to-amber-300"></div>

        <div class="flex items-start justify-between gap-4">
            <div>
                <p class="text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">Ocupación</p>
                <p class="mt-3 text-4xl font-extrabold tracking-tight text-slate-900 dark:text-white">
                    {{ $ocupacion }}%
                </p>
            </div>

            <span class="flex size-12 shrink-0 items-center justify-center rounded-xl bg-amber-50 text-amber-600 transition-colors duration-300 group-hover:bg-amber-600 group-hover:text-white dark:bg-amber-500/10 dark:text-amber-400">
                <flux:icon.building-office-2 class="size-6" />
            </span>
        </div>

        <p class="mt-3 text-sm font-medium text-slate-500 dark:text-slate-400">
            {{ $habitacionesOcupadas }} de {{ $totalHabitaciones }} habitaciones ocupadas
        </p>

        <div class="mt-4 h-2 overflow-hidden rounded-full bg-slate-100 dark:bg-zinc-700">
            <div class="h-full rounded-full bg-gradient-to-r from-amber-500 to-amber-300" style="width: {{ min(100, (int) $ocupacion) }}%"></div>
        </div>
    </div>

    <div class="group relative overflow-hidden rounded-2xl border border-slate-200/80 bg-white p-6 shadow-sm transition-all duration-300 hover:-translate-y-1 hover:shadow-lg dark:border-zinc-700 dark:bg-slate-800">
        <div class="absolute inset-x-0 top-0 h-1 bg-gradient-to-r from-emerald-500 to-emerald-300"></div>

        <div class="flex items-start justify-between gap-4">
            <div>
                <p class="text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">Ingresos totales</p>
                <p class="mt-3 text-4xl font-extrabold tracking-tight text-slate-900 dark:text-white">
                    ${{ number_format((float) $ingresosTotales, 2) }}
                </p>
            </div>

            <span class="flex size-12 shrink-0 items-center justify-center rounded-xl bg-emerald-50 text-emerald-600 transition-colors duration-300 group-hover:bg-emerald-600 group-hover:text-white dark:bg-emerald-500/10 dark:text-emerald-400">
                <flux:icon.arrow-trending-up class="size-6" />
            </span>
        </div>

        <p class="mt-4 inline-flex items-center gap-1.5 rounded-full bg-emerald-50 px-2.5 py-1 text-xs font-semibold text-emerald-700 dark:bg-emerald-500/10 dark:text-emerald-300">
            <span class="size-1.5 rounded-full bg-emerald-500"></span>
            Pagos registrados
        </p>
    </div>

    <div class="group relative overflow-hidden rounded-2xl border border-slate-200/80 bg-white p-6 shadow-sm transition-all duration-300 hover:-translate-y-1 hover:shadow-lg dark:border-zinc-700 dark:bg-slate-800">
        <div class="absolute inset-x-0 top-0 h-1 bg-gradient-to-r from-rose-500 to-rose-300"></div>

        <div class="flex items-start justify-between gap-4">
            <div>
                <p class="text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">Gastos totales</p>
                <p class="mt-3 text-4xl font-extrabold tracking-tight text-slate-900 dark:text-white">
                    ${{ number_format((float) $gastosTotales, 2) }}
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
                <p class="text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">Balance</p>
                <p class="mt-3 text-4xl font-extrabold tracking-tight {{ $balancePositivo ? 'text-slate-900 dark:text-white' : 'text-rose-600 dark:text-rose-400' }}">
                    ${{ number_format((float) $balance, 2) }}
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
</div>
