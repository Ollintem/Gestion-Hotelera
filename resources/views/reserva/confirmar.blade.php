<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Confirmar reserva - NovaStay</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-slate-50 font-sans antialiased text-slate-800">

    <x-flash-messages />

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
                <a href="{{ route('habitaciones.public') }}" class="text-slate-600 transition-colors duration-200 hover:text-amber-600">Habitaciones</a>
                <a href="{{ route('servicios.public') }}" class="text-slate-600 transition-colors duration-200 hover:text-amber-600">Servicios</a>
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

    <main class="max-w-4xl mx-auto px-6 py-10">

        <div class="mb-8">
            <h1 class="text-3xl font-bold text-slate-900 mb-2">Confirmar tu reserva</h1>
            <p class="text-slate-500 text-sm">Revisa el detalle de tu estancia y confirma la reserva para asegurar tu habitación.</p>
        </div>

        <div class="grid grid-cols-1 gap-6 md:grid-cols-5">
            <!-- Detalle de la habitación -->
            <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm md:col-span-3">
                <div class="relative h-48 bg-slate-200">
                    <img src="{{ $habitacion->imagen() }}" alt="{{ $habitacion->tipoHabitacion->nombre }}" class="w-full h-full object-cover">
                    <div class="absolute top-4 left-4 bg-white text-slate-700 text-xs font-bold px-3 py-1 rounded-md shadow-sm">Habitación #{{ $habitacion->numero_habitacion }}</div>
                </div>
                <div class="p-6">
                    <h2 class="mb-1 break-words text-xl font-bold text-slate-900">{{ $habitacion->tipoHabitacion->nombre }}</h2>
                    <p class="mb-4 text-sm text-slate-500">Piso {{ $habitacion->piso }} &middot; hasta {{ $habitacion->tipoHabitacion->capacidad }} personas</p>
                    <p class="break-words text-sm leading-relaxed text-slate-600">{{ $habitacion->tipoHabitacion->descripcion }}</p>

                    <div class="mt-6 grid grid-cols-1 gap-4 text-sm sm:grid-cols-2">
                        <div class="rounded-xl bg-slate-50 border border-slate-100 p-4">
                            <p class="text-[10px] uppercase tracking-wider font-semibold text-slate-400 mb-1">Check-in</p>
                            <p class="font-semibold text-slate-800">{{ $checkIn->format('d/m/Y') }}</p>
                        </div>
                        <div class="rounded-xl bg-slate-50 border border-slate-100 p-4">
                            <p class="text-[10px] uppercase tracking-wider font-semibold text-slate-400 mb-1">Check-out</p>
                            <p class="font-semibold text-slate-800">{{ $checkOut->format('d/m/Y') }}</p>
                        </div>
                        <div class="rounded-xl bg-slate-50 border border-slate-100 p-4">
                            <p class="text-[10px] uppercase tracking-wider font-semibold text-slate-400 mb-1">Noches</p>
                            <p class="font-semibold text-slate-800">{{ $noches }}</p>
                        </div>
                        <div class="rounded-xl bg-slate-50 border border-slate-100 p-4">
                            <p class="text-[10px] uppercase tracking-wider font-semibold text-slate-400 mb-1">Huéspedes</p>
                            <p class="font-semibold text-slate-800">{{ $datos['guests'] ?? 1 }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Desglose de precios -->
            <div class="md:col-span-2">
                <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6 sticky top-24">
                    <h3 class="text-lg font-bold text-slate-900 mb-4">Resumen de tu reserva</h3>

                    <div class="space-y-3 text-sm">
                        <div class="flex justify-between text-slate-600">
                            <span>${{ number_format($precioPorNoche, 2) }} x {{ $noches }} {{ $noches === 1 ? 'noche' : 'noches' }}</span>
                            <span class="font-medium text-slate-800">${{ number_format($precioPorNoche * $noches, 2) }}</span>
                        </div>
                        <div class="flex justify-between text-slate-600">
                            <span>Impuestos y cargos</span>
                            <span class="font-medium text-slate-800">-</span>
                        </div>
                        <div class="border-t border-slate-100 pt-3 flex justify-between items-center">
                            <span class="font-semibold text-slate-900">Total</span>
                            <span class="text-2xl font-bold text-amber-600">${{ number_format($total, 2) }}</span>
                        </div>
                    </div>

                    @if ($errors->any())
                        <div class="mt-4 rounded-lg border border-red-100 bg-red-50 px-4 py-3 text-sm text-red-700">
                            {{ $errors->first() }}
                        </div>
                    @endif

                    <form action="{{ route('reserva.store') }}" method="POST" class="mt-6">
                        @csrf
                        <input type="hidden" name="habitacion_id" value="{{ $habitacion->id }}">
                        <input type="hidden" name="check_in" value="{{ $checkIn->format('Y-m-d') }}">
                        <input type="hidden" name="check_out" value="{{ $checkOut->format('Y-m-d') }}">
                        <input type="hidden" name="guests" value="{{ $datos['guests'] ?? 1 }}">
                        <button type="submit" class="w-full bg-amber-500 hover:bg-amber-600 text-white font-semibold py-3 rounded-xl shadow-md transition focus:outline-none focus:ring-2 focus:ring-amber-500 focus:ring-offset-2">
                            Confirmar reserva
                        </button>
                    </form>

                    <a href="{{ route('habitaciones.public') }}" class="mt-4 block text-center text-sm text-slate-500 hover:text-slate-700 transition">&larr; Elegir otra habitación</a>
                </div>
            </div>
        </div>

    </main>

</body>
</html>