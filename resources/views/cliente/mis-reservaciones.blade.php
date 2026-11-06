<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Mis reservaciones - NovaStay</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-slate-50 font-sans antialiased text-slate-800">

    <!-- Navbar -->
    <nav class="border-b border-slate-200/60 bg-white/80 backdrop-blur-md">
        <div class="mx-auto flex max-w-7xl items-center justify-between px-6 py-4 lg:px-8">
            <a href="{{ route('home') }}" class="flex items-center gap-2.5 transition-colors duration-200">
                <div class="rounded-md bg-amber-600 p-1.5 text-white">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path></svg>
                </div>
                <div>
                    <span class="block font-serif text-xl font-bold leading-none tracking-wide text-slate-900">NovaStay</span>
                    <span class="block text-[10px] font-semibold uppercase tracking-widest text-amber-600">Hotel Management</span>
                </div>
            </a>

            <div class="hidden items-center gap-8 text-sm font-medium md:flex">
                <a href="{{ route('home') }}" class="text-slate-600 transition-colors duration-200 hover:text-amber-600">Inicio</a>
                <a href="{{ route('mis-reservaciones') }}" class="text-amber-600 transition-colors duration-200 hover:text-amber-700">Mis reservaciones</a>
            </div>

            <div class="flex items-center gap-4 text-sm font-medium">
                <span class="text-slate-700">{{ auth()->user()->name }}</span>
                <form action="{{ route('logout') }}" method="POST">
                    @csrf
                    <button type="submit" class="text-slate-600 transition-colors duration-200 hover:text-amber-600">Salir</button>
                </form>
            </div>
        </div>
    </nav>

    <main class="max-w-5xl mx-auto px-6 py-10">

        <div class="mb-8">
            <h1 class="text-3xl font-bold text-slate-900 mb-2">Mis reservaciones</h1>
            <p class="text-slate-500 text-sm">
                Bienvenido, <span class="font-medium text-slate-700">{{ $cliente->nombreCompleto() }}</span>. Revisa el estado de tus estancias y descarga tu estado de cuenta.
            </p>
        </div>

        @if (session('success'))
            <div
                data-notificacion
                data-notificacion-duracion="3000"
                role="status"
                class="mb-6 flex items-start gap-3 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-900 transition duration-200"
            >
                <span class="flex-1">{{ session('success') }}</span>
                <button type="button" data-notificacion-cerrar class="-mr-1 shrink-0 rounded-lg p-1 opacity-60 transition hover:opacity-100" aria-label="Cerrar notificación">
                    <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" /></svg>
                </button>
            </div>
        @endif

        @forelse ($reservas as $estancia)
            @php
                $reserva = $estancia['reserva'];
                $estadoColores = [
                    'Confirmada' => 'bg-emerald-100 text-emerald-800',
                    'Pendiente' => 'bg-amber-100 text-amber-800',
                    'Finalizada' => 'bg-slate-100 text-slate-800',
                    'Cancelada' => 'bg-rose-100 text-rose-800',
                ];
                $colorEstado = $estadoColores[$reserva->estado] ?? 'bg-slate-100 text-slate-800';
            @endphp

            <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden mb-6">
                <!-- Encabezado de la tarjeta -->
                <div class="px-6 py-4 border-b border-slate-100 bg-slate-50/60 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                    <div class="flex items-center gap-3">
                        <span class="text-sm font-bold text-slate-900">Reservación #{{ str_pad($reserva->id, 5, '0', STR_PAD_LEFT) }}</span>
                        <span class="text-xs font-semibold px-2.5 py-1 rounded-full {{ $colorEstado }}">{{ $reserva->estado }}</span>
                    </div>
                    <a href="{{ route('estado-cuenta.pdf', $reserva->id) }}"
                       class="inline-flex items-center gap-2 bg-amber-500 hover:bg-amber-600 text-white text-sm font-semibold px-4 py-2 rounded-lg shadow-sm transition">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                        Descargar estado de cuenta (PDF)
                    </a>
                </div>

                <div class="p-6">
                    <!-- Fechas y habitación -->
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 text-sm mb-6">
                        <div class="rounded-xl bg-slate-50 border border-slate-100 p-4">
                            <p class="text-[10px] uppercase tracking-wider font-semibold text-slate-400 mb-1">Check-in</p>
                            <p class="font-semibold text-slate-800">{{ $reserva->check_in->format('d/m/Y') }}</p>
                        </div>
                        <div class="rounded-xl bg-slate-50 border border-slate-100 p-4">
                            <p class="text-[10px] uppercase tracking-wider font-semibold text-slate-400 mb-1">Check-out</p>
                            <p class="font-semibold text-slate-800">{{ $reserva->check_out->format('d/m/Y') }}</p>
                        </div>
                        <div class="rounded-xl bg-slate-50 border border-slate-100 p-4">
                            <p class="text-[10px] uppercase tracking-wider font-semibold text-slate-400 mb-1">Noches</p>
                            <p class="font-semibold text-slate-800">{{ $estancia['noches'] }}</p>
                        </div>
                        <div class="rounded-xl bg-slate-50 border border-slate-100 p-4">
                            <p class="text-[10px] uppercase tracking-wider font-semibold text-slate-400 mb-1">Habitación</p>
                            <p class="font-semibold text-slate-800">
                                @forelse ($estancia['asignaciones'] as $asignacion)
                                    #{{ $asignacion->habitacion?->numero_habitacion }}
                                    @if ($asignacion->habitacion?->tipoHabitacion)
                                        · {{ $asignacion->habitacion->tipoHabitacion->nombre }}
                                    @endif
                                @empty
                                    —
                                @endforelse
                            </p>
                        </div>
                    </div>

                    <!-- Desglose de gastos extra -->
                    <h3 class="text-base font-bold text-slate-900 mb-3">Gastos extra de tu estancia</h3>

                    @if ($estancia['gastosExtra']->isEmpty())
                        <p class="text-sm text-slate-500 mb-4">No registraste consumos adicionales durante esta estancia.</p>
                    @else
                        <div class="overflow-x-auto mb-4">
                            <table class="w-full text-sm text-slate-800">
                                <thead>
                                    <tr class="border-b border-slate-200 text-left">
                                        <th class="py-2 pr-4 font-semibold text-slate-700">Servicio</th>
                                        <th class="py-2 pr-4 font-semibold text-slate-700 text-right">Cantidad</th>
                                        <th class="py-2 font-semibold text-slate-700 text-right">Subtotal</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100">
                                    @foreach ($estancia['gastosExtra'] as $gasto)
                                        <tr>
                                            <td class="py-2 pr-4 text-slate-800">{{ $gasto->servicio?->nombre ?? 'Consumo' }}</td>
                                            <td class="py-2 pr-4 text-right text-slate-700">{{ $gasto->cantidad }}</td>
                                            <td class="py-2 text-right font-medium text-slate-800">${{ number_format((float) $gasto->subtotal, 2) }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif

                    <!-- Totales -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
                        <div class="rounded-xl bg-slate-50 border border-slate-100 p-4">
                            <p class="text-[10px] uppercase tracking-wider font-semibold text-slate-400 mb-1">Tarifa ({{ $estancia['noches'] }} noches)</p>
                            <p class="font-semibold text-slate-800">${{ number_format($estancia['tarifa'], 2) }}</p>
                        </div>
                        <div class="rounded-xl bg-slate-50 border border-slate-100 p-4">
                            <p class="text-[10px] uppercase tracking-wider font-semibold text-slate-400 mb-1">Gastos extra</p>
                            <p class="font-semibold text-slate-800">${{ number_format($estancia['subtotalExtras'], 2) }}</p>
                        </div>
                        <div class="rounded-xl bg-slate-50 border border-slate-100 p-4">
                            <p class="text-[10px] uppercase tracking-wider font-semibold text-slate-400 mb-1">Total consumido</p>
                            <p class="font-semibold text-slate-900">${{ number_format($estancia['totalConsumos'], 2) }}</p>
                        </div>
                        <div class="rounded-xl bg-white border border-amber-200 p-4">
                            <p class="text-[10px] uppercase tracking-wider font-semibold text-amber-700 mb-1">Pagado / Pendiente</p>
                            <p class="font-semibold text-emerald-700">${{ number_format($estancia['pagado'], 2) }}</p>
                            <p class="text-sm font-bold text-slate-900">Pendiente: ${{ number_format($estancia['pendiente'], 2) }}</p>
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-14 text-center">
                <h2 class="text-xl font-bold text-slate-900 mb-2">Aún no tienes reservaciones</h2>
                <p class="text-slate-500 text-sm mb-6">Cuando reserves una habitación, podrás ver aquí su estado y descargar tu comprobante en PDF.</p>
                <a href="{{ route('habitaciones.public') }}" class="inline-block bg-amber-500 hover:bg-amber-600 text-white text-sm font-medium px-5 py-2.5 rounded-lg transition shadow-md">Reservar ahora</a>
            </div>
        @endforelse

    </main>

</body>
</html>