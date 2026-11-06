<div class="grid gap-6 xl:grid-cols-3">
    <section class="overflow-hidden rounded-2xl border border-slate-200/80 bg-white shadow-sm dark:border-zinc-700 dark:bg-slate-800 xl:col-span-2">
        <div class="border-b border-slate-100 px-6 py-5 dark:border-zinc-700">
            <h2 class="text-lg font-bold text-slate-900 dark:text-white">Desglose por día</h2>
            <p class="mt-1 text-sm font-medium text-slate-500 dark:text-slate-400">
                Reservaciones, llegadas, salidas y movimiento financiero de cada día del periodo.
            </p>
        </div>

        <div class="max-h-[32rem] overflow-auto">
            <table class="min-w-full divide-y divide-slate-100 text-sm dark:divide-zinc-700">
                <thead class="sticky top-0 bg-slate-50 text-xs font-bold uppercase tracking-wider text-slate-500 dark:bg-zinc-900 dark:text-slate-400">
                    <tr>
                        <th class="px-5 py-3 text-left">Fecha</th>
                        <th class="px-3 py-3 text-right">Reservas</th>
                        <th class="px-3 py-3 text-right">Check-in</th>
                        <th class="px-3 py-3 text-right">Check-out</th>
                        <th class="px-3 py-3 text-right">Ingresos</th>
                        <th class="px-3 py-3 text-right">Gastos</th>
                        <th class="px-3 py-3 text-right">Servicios</th>
                        <th class="px-5 py-3 text-right">Ocupación</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-zinc-700">
                    @forelse ($desglose as $dia)
                        <tr class="transition-colors hover:bg-slate-50/80 dark:hover:bg-zinc-700/40">
                            <td class="whitespace-nowrap px-5 py-3 font-semibold text-slate-700 dark:text-slate-200">{{ $dia['fechaTexto'] }}</td>
                            <td class="px-3 py-3 text-right text-slate-600 dark:text-slate-300">{{ $dia['reservaciones'] }}</td>
                            <td class="px-3 py-3 text-right text-slate-600 dark:text-slate-300">{{ $dia['checkins'] }}</td>
                            <td class="px-3 py-3 text-right text-slate-600 dark:text-slate-300">{{ $dia['checkouts'] }}</td>
                            <td class="px-3 py-3 text-right font-semibold text-emerald-700 dark:text-emerald-400">${{ number_format((float) $dia['ingresos'], 2) }}</td>
                            <td class="px-3 py-3 text-right font-semibold text-rose-700 dark:text-rose-400">${{ number_format((float) $dia['gastos'], 2) }}</td>
                            <td class="px-3 py-3 text-right text-slate-600 dark:text-slate-300">{{ $dia['servicios'] }}</td>
                            <td class="px-5 py-3 text-right">
                                <span class="inline-flex items-center rounded-full bg-slate-100 px-2.5 py-1 text-xs font-semibold text-slate-600 dark:bg-zinc-700 dark:text-slate-200">
                                    {{ number_format((float) $dia['ocupacion'], 1) }}%
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-6 py-8 text-center text-sm font-medium text-slate-500 dark:text-slate-400">
                                Sin movimientos en el periodo.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
                <tfoot class="bg-slate-50 font-bold text-slate-800 dark:bg-zinc-900 dark:text-slate-100">
                    <tr>
                        <td class="px-5 py-3">Totales</td>
                        <td class="px-3 py-3 text-right">{{ $indicadores['totalReservaciones'] }}</td>
                        <td class="px-3 py-3 text-right">{{ $indicadores['checkins'] }}</td>
                        <td class="px-3 py-3 text-right">{{ $indicadores['checkouts'] }}</td>
                        <td class="px-3 py-3 text-right text-emerald-700 dark:text-emerald-400">${{ number_format((float) $indicadores['ingresos'], 2) }}</td>
                        <td class="px-3 py-3 text-right text-rose-700 dark:text-rose-400">${{ number_format((float) $indicadores['gastos'], 2) }}</td>
                        <td class="px-3 py-3 text-right">{{ $indicadores['serviciosCantidad'] }}</td>
                        <td class="px-5 py-3 text-right">{{ number_format((float) $indicadores['ocupacion'], 1) }}%</td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </section>

    <div class="space-y-6">
        <section class="rounded-2xl border border-slate-200/80 bg-white p-6 shadow-sm dark:border-zinc-700 dark:bg-slate-800">
            <h2 class="text-base font-bold text-slate-900 dark:text-white">Pagos por método</h2>

            <div class="mt-4 space-y-3">
                @forelse ($pagosPorMetodo as $pago)
                    <div class="flex items-center justify-between gap-3 rounded-xl bg-slate-50 px-3 py-2 dark:bg-zinc-900/60">
                        <div>
                            <p class="text-sm font-semibold text-slate-700 dark:text-slate-200">{{ $pago->metodo_pago }}</p>
                            <p class="text-xs font-medium text-slate-500 dark:text-slate-400">{{ $pago->total }} pagos</p>
                        </div>
                        <p class="text-sm font-bold text-emerald-700 dark:text-emerald-400">${{ number_format((float) $pago->monto, 2) }}</p>
                    </div>
                @empty
                    <p class="text-sm font-medium text-slate-500 dark:text-slate-400">Sin pagos completados.</p>
                @endforelse
            </div>
        </section>

        <section class="rounded-2xl border border-slate-200/80 bg-white p-6 shadow-sm dark:border-zinc-700 dark:bg-slate-800">
            <h2 class="text-base font-bold text-slate-900 dark:text-white">Gastos por categoría</h2>

            <div class="mt-4 space-y-3">
                @forelse ($gastosPorCategoria as $gasto)
                    <div class="flex items-center justify-between gap-3 rounded-xl bg-slate-50 px-3 py-2 dark:bg-zinc-900/60">
                        <p class="text-sm font-semibold text-slate-700 dark:text-slate-200">{{ $gasto->categoria ?: 'Sin categoría' }}</p>
                        <p class="text-sm font-bold text-rose-700 dark:text-rose-400">${{ number_format((float) $gasto->monto, 2) }}</p>
                    </div>
                @empty
                    <p class="text-sm font-medium text-slate-500 dark:text-slate-400">Sin gastos registrados.</p>
                @endforelse
            </div>
        </section>

        <section class="rounded-2xl border border-slate-200/80 bg-white p-6 shadow-sm dark:border-zinc-700 dark:bg-slate-800">
            <h2 class="text-base font-bold text-slate-900 dark:text-white">Servicios más solicitados</h2>

            <div class="mt-4 space-y-3">
                @forelse ($serviciosTop as $servicio)
                    <div class="flex items-center justify-between gap-3 rounded-xl bg-slate-50 px-3 py-2 dark:bg-zinc-900/60">
                        <div>
                            <p class="text-sm font-semibold text-slate-700 dark:text-slate-200">{{ $servicio->nombre }}</p>
                            <p class="text-xs font-medium text-slate-500 dark:text-slate-400">{{ $servicio->cantidad }} vendidos</p>
                        </div>
                        <p class="text-sm font-bold text-slate-700 dark:text-slate-200">${{ number_format((float) $servicio->total, 2) }}</p>
                    </div>
                @empty
                    <p class="text-sm font-medium text-slate-500 dark:text-slate-400">Sin servicios vendidos.</p>
                @endforelse
            </div>
        </section>
    </div>
</div>

<div class="rounded-2xl border border-slate-200/80 bg-white p-6 shadow-sm dark:border-zinc-700 dark:bg-slate-800">
    <h2 class="text-base font-bold text-slate-900 dark:text-white">Criterios del reporte</h2>
    <ul class="mt-3 grid gap-2 text-sm font-medium text-slate-500 dark:text-slate-400 md:grid-cols-2">
        <li>Reservaciones: por fecha de creación dentro del periodo.</li>
        <li>Ingresos: solo pagos completados, por fecha de pago.</li>
        <li>Check-in y check-out: por la fecha y hora real registrada.</li>
        <li>Gastos: por la fecha del gasto.</li>
        <li>Servicios: por la fecha de captura del cargo al folio.</li>
        <li>Saldo pendiente: cargos de las reservaciones del periodo menos sus pagos completados.</li>
    </ul>
</div>
