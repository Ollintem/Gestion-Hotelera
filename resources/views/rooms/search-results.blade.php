<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Habitaciones disponibles - NovaStay</title>
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
                <a href="{{ route('habitaciones.public') }}" class="text-amber-600 transition-colors duration-200 hover:text-amber-700">Habitaciones</a>
                <a href="{{ route('servicios.public') }}" class="text-slate-600 transition-colors duration-200 hover:text-amber-600">Servicios</a>
            </div>

            <div class="flex items-center gap-4 text-sm font-medium">
                @auth
                    @if (auth()->user()->hasRole('cliente'))
                        <a href="{{ route('mis-reservaciones') }}" class="text-amber-600 transition-colors duration-200 hover:text-amber-700">Mis reservaciones</a>
                    @endif
                    <span class="text-slate-700">{{ auth()->user()->name }}</span>
                @else
                    <a href="{{ route('login') }}" class="text-slate-700 transition-colors duration-200 hover:text-amber-600">Iniciar sesión</a>
                @endauth
                <a href="#reservar" class="rounded-lg bg-slate-900 px-5 py-2 text-white transition-colors duration-200 hover:bg-slate-800">Reservar</a>
            </div>
        </div>
    </nav>

    <main class="max-w-7xl mx-auto px-6 py-10">

        <!-- Header de Resultados -->
        <div class="flex flex-col md:flex-row justify-between items-end mb-8">
            <div>
                <h1 class="text-3xl font-bold text-slate-900 mb-2">Habitaciones disponibles</h1>
                <p class="text-slate-500 text-sm">
                    <span class="font-medium text-slate-700">{{ date('d/m/Y', strtotime($checkIn)) }} &rarr; {{ date('d/m/Y', strtotime($checkOut)) }}</span>
                    &middot; {{ $noches }} {{ $noches === 1 ? 'noche' : 'noches' }}
                    @if ($guests)
                        &middot; {{ $guests }} {{ $guests === 1 ? 'huésped' : 'huéspedes' }}
                    @endif
                </p>
            </div>
        </div>

        <!-- Buscador para afinar la búsqueda -->
        <div class="mb-10">
            <form action="{{ route('rooms.search') }}" method="GET" id="reservar" class="bg-white rounded-2xl border border-slate-100 shadow-sm p-5 grid grid-cols-1 md:grid-cols-4 gap-4 items-end">
                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1">Check-in</label>
                    <input type="date" name="check_in" value="{{ $checkIn }}" class="w-full border border-slate-200 rounded-lg p-2.5 text-sm bg-slate-50 text-slate-800 focus:ring-2 focus:ring-amber-500 outline-none" required>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1">Check-out</label>
                    <input type="date" name="check_out" value="{{ $checkOut }}" class="w-full border border-slate-200 rounded-lg p-2.5 text-sm bg-slate-50 text-slate-800 focus:ring-2 focus:ring-amber-500 outline-none" required>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1">Huéspedes</label>
                    <input type="number" name="guests" min="1" max="10" value="{{ $guests ?? 2 }}" class="w-full border border-slate-200 rounded-lg p-2.5 text-sm bg-slate-50 text-slate-800 focus:ring-2 focus:ring-amber-500 outline-none">
                </div>
                <div>
                    <button type="submit" class="w-full bg-amber-500 hover:bg-amber-600 text-white font-medium py-2.5 px-4 rounded-lg transition shadow-md">
                        Buscar disponibilidad
                    </button>
                </div>
            </form>
        </div>

        @if ($habitaciones->isEmpty())
            <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-12 text-center">
                <h2 class="text-xl font-bold text-slate-900 mb-2">Sin habitaciones disponibles</h2>
                <p class="text-slate-500 text-sm mb-6">No encontramos habitaciones disponibles para las fechas seleccionadas. Prueba con otras fechas o con menos huéspedes.</p>
                <a href="{{ route('home') }}" class="inline-block bg-amber-500 hover:bg-amber-600 text-white text-sm font-medium px-5 py-2.5 rounded-lg transition shadow-md">Volver al inicio</a>
            </div>
        @else
            <!-- Grid de Habitaciones -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                @foreach ($habitaciones as $habitacion)
                    @php
                        $total = round((float) $habitacion->tipoHabitacion->precio_base * $noches, 2);
                    @endphp
                    <div class="bg-white rounded-2xl overflow-hidden border border-slate-200 shadow-sm hover:shadow-md transition duration-300 flex flex-col">
                        <div class="relative h-56 bg-slate-200">
                            <img src="{{ $habitacion->imagen() }}" alt="{{ $habitacion->tipoHabitacion->nombre }}" loading="lazy" class="w-full h-full object-cover">
                            <div class="absolute top-4 left-4 bg-emerald-100 text-emerald-700 text-xs font-bold px-3 py-1 rounded-full shadow-sm">Disponible</div>
                            <div class="absolute top-4 right-4 bg-white text-slate-700 text-xs font-bold px-3 py-1 rounded-md shadow-sm">#{{ $habitacion->numero_habitacion }}</div>
                        </div>
                        <div class="p-5 flex flex-col flex-grow">
                            <div class="mb-2 flex flex-col gap-2 sm:flex-row sm:items-start sm:justify-between">
                                <div class="min-w-0">
                                    <h3 class="break-words text-lg font-bold text-slate-900">{{ $habitacion->tipoHabitacion->nombre }}</h3>
                                    <p class="text-xs text-slate-500">Piso {{ $habitacion->piso }} &middot; hasta {{ $habitacion->tipoHabitacion->capacidad }} personas</p>
                                </div>
                                <div class="shrink-0 sm:text-right">
                                    <span class="text-xl font-bold text-amber-600">${{ number_format((float) $habitacion->tipoHabitacion->precio_base, 2) }}</span>
                                    <p class="text-[10px] text-slate-400 uppercase">por noche</p>
                                </div>
                            </div>
                            <p class="text-sm text-slate-600 mb-4 line-clamp-3">{{ $habitacion->tipoHabitacion->descripcion }}</p>
                            <div class="mt-auto flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                                <div class="text-sm text-slate-500">
                                    <span class="font-semibold text-slate-700">${{ number_format($total, 2) }}</span>
                                    <p class="text-[10px] text-slate-400 uppercase">{{ $noches }} {{ $noches === 1 ? 'noche' : 'noches' }}</p>
                                </div>
                                <form action="{{ route('reserva.iniciar') }}" method="POST" id="form-reservar-{{ $habitacion->id }}">
                                    @csrf
                                    <input type="hidden" name="habitacion_id" value="{{ $habitacion->id }}">
                                    <input type="hidden" name="check_in" value="{{ $checkIn }}">
                                    <input type="hidden" name="check_out" value="{{ $checkOut }}">
                                    <input type="hidden" name="guests" value="{{ $guests ?? 1 }}">
                                    <input type="hidden" name="accion" value="login">
                                    @auth
                                        <button type="submit" class="w-full bg-amber-500 hover:bg-amber-600 text-white text-sm font-medium py-2.5 px-5 rounded-lg transition shadow-md sm:w-auto">
                                            Reservar ahora
                                        </button>
                                    @else
                                        <button type="button"
                                            onclick="abrirModalCuenta(
                                                'form-reservar-{{ $habitacion->id }}',
                                                '{{ $habitacion->tipoHabitacion->nombre }}',
                                                'Hab. #{{ $habitacion->numero_habitacion }}',
                                                '{{ number_format((float) $habitacion->tipoHabitacion->precio_base, 2) }}'
                                            )"
                                            class="bg-amber-500 hover:bg-amber-600 w-full text-white text-sm font-medium py-2.5 px-5 rounded-lg transition shadow-md">
                                            Reservar ahora
                                        </button>
                                    @endauth
                                </form>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif

    </main>

    <!-- Modal: Iniciar sesión / Crear cuenta (flujo de reserva) -->
    <div id="modal-cuenta" class="fixed inset-0 z-50 hidden items-center justify-center p-4">
        <div class="absolute inset-0 bg-slate-950/60" onclick="cerrarModalCuenta()"></div>
        <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-md overflow-hidden">
            <div class="bg-slate-900 px-6 py-5 flex items-center justify-between">
                <div>
                    <p class="text-[10px] uppercase tracking-widest text-slate-400 font-semibold">Completa tu reserva</p>
                    <h3 class="text-lg font-bold text-white" id="modal-titulo">Reservar habitación</h3>
                </div>
                <button onclick="cerrarModalCuenta()" class="text-slate-400 hover:text-white transition text-2xl leading-none">&times;</button>
            </div>
            <div class="p-6">
                <div id="modal-resumen" class="mb-6 rounded-xl bg-slate-50 border border-slate-100 p-4 text-sm"></div>

                <p class="text-sm text-slate-600 mb-4">
                    Para continuar con la reservación necesitas una cuenta. Si ya tienes una, inicia sesión; si no, créala en un minuto y guardaremos los datos de tu reserva.
                </p>

                <button type="button"
                    onclick="enviarReserva('login')"
                    class="w-full bg-slate-900 hover:bg-slate-800 text-white font-semibold py-3 rounded-xl shadow-sm transition">
                    Iniciar sesión y continuar
                </button>

                <div class="flex items-center gap-3 my-4">
                    <span class="flex-1 h-px bg-slate-200"></span>
                    <span class="text-xs font-semibold text-slate-400 uppercase">o</span>
                    <span class="flex-1 h-px bg-slate-200"></span>
                </div>

                <button type="button"
                    onclick="enviarReserva('registro')"
                    class="w-full bg-amber-500 hover:bg-amber-600 text-white font-bold py-3 rounded-xl shadow-md transition">
                    Crear cuenta y continuar
                </button>

                <p class="mt-4 text-center text-xs text-slate-400">Al crear tu cuenta se registra como Cliente y quedan guardados los datos de tu reserva.</p>
            </div>
        </div>
    </div>

    <script>
        let formularioReserva = null;

        function abrirModalCuenta(formId, tipo, habitacion, precio) {
            formularioReserva = document.getElementById(formId);
            document.getElementById('modal-titulo').textContent = tipo;
            document.getElementById('modal-resumen').innerHTML =
                '<p class="font-semibold text-slate-900 mb-1">' + habitacion + '</p>' +
                '<p class="text-slate-500">' + tipo + ' &middot; $' + precio + ' por noche</p>';
            const modal = document.getElementById('modal-cuenta');
            modal.classList.remove('hidden');
            modal.classList.add('flex');
        }

        function cerrarModalCuenta() {
            const modal = document.getElementById('modal-cuenta');
            modal.classList.add('hidden');
            modal.classList.remove('flex');
        }

        function enviarReserva(accion) {
            if (!formularioReserva) return;
            formularioReserva.querySelector('input[name="accion"]').value = accion;
            formularioReserva.submit();
        }
    </script>

</body>
</html>