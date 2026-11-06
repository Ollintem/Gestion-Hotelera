<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Mi panel - NovaStay</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-slate-50 font-sans text-slate-800 antialiased">

    <x-flash-messages />

    @php
        $estadoColores = [
            'Confirmada' => 'bg-emerald-100 text-emerald-800',
            'Pendiente' => 'bg-amber-100 text-amber-800',
            'Finalizada' => 'bg-slate-100 text-slate-700',
            'Cancelada' => 'bg-rose-100 text-rose-800',
        ];

        $estadoServicio = fn (string $estado): string => match ($estado) {
            'Confirmada' => 'Activo',
            'Finalizada' => 'Facturado',
            'Cancelada' => 'Cancelado',
            default => 'Pendiente',
        };
    @endphp

    <!-- Barra de navegación -->
    <nav class="sticky top-0 z-40 border-b border-slate-200/60 bg-white/90 backdrop-blur-md">
        <div class="mx-auto flex max-w-7xl flex-wrap items-center justify-between gap-3 px-4 py-3 sm:px-6 lg:px-8">
            <a href="{{ route('home') }}" class="flex min-w-0 items-center gap-2.5 transition-colors duration-200">
                <div class="rounded-md bg-amber-600 p-1.5 text-white">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path></svg>
                </div>
                <div class="min-w-0">
                    <span class="block font-serif text-xl font-bold leading-none tracking-wide text-slate-900">NovaStay</span>
                    <span class="block text-[10px] font-semibold uppercase tracking-widest text-amber-600">Mi panel</span>
                </div>
            </a>

            <div class="flex flex-wrap items-center gap-3 text-sm font-medium sm:gap-5">
                <a href="#mis-datos" class="hidden text-slate-600 transition-colors duration-200 hover:text-amber-600 sm:inline">Mi perfil</a>
                <a href="#mis-reservaciones" class="hidden text-slate-600 transition-colors duration-200 hover:text-amber-600 sm:inline">Reservaciones</a>
                <a href="#mis-pagos" class="hidden text-slate-600 transition-colors duration-200 hover:text-amber-600 md:inline">Pagos</a>
                <a href="#estado-cuenta" class="hidden text-slate-600 transition-colors duration-200 hover:text-amber-600 md:inline">Estado de cuenta</a>
                <span class="hidden max-w-[14rem] truncate text-slate-700 lg:inline">{{ auth()->user()->name }}</span>
                <form action="{{ route('logout') }}" method="POST">
                    @csrf
                    <button type="submit" class="rounded-lg border border-slate-200 px-3 py-1.5 text-slate-600 transition-colors duration-200 hover:border-amber-300 hover:text-amber-600">Salir</button>
                </form>
            </div>

            <!-- Navegación móvil -->
            <div class="flex w-full gap-4 overflow-x-auto pb-1 text-xs font-semibold text-slate-600 sm:hidden">
                <a href="#mis-datos" class="shrink-0 rounded-full bg-slate-100 px-3 py-1.5 transition hover:bg-amber-100 hover:text-amber-700">Mi perfil</a>
                <a href="#mis-reservaciones" class="shrink-0 rounded-full bg-slate-100 px-3 py-1.5 transition hover:bg-amber-100 hover:text-amber-700">Reservaciones</a>
                <a href="#mis-pagos" class="shrink-0 rounded-full bg-slate-100 px-3 py-1.5 transition hover:bg-amber-100 hover:text-amber-700">Pagos</a>
                <a href="#mis-servicios" class="shrink-0 rounded-full bg-slate-100 px-3 py-1.5 transition hover:bg-amber-100 hover:text-amber-700">Servicios</a>
                <a href="#estado-cuenta" class="shrink-0 rounded-full bg-slate-100 px-3 py-1.5 transition hover:bg-amber-100 hover:text-amber-700">Estado de cuenta</a>
            </div>
        </div>
    </nav>

    <main class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">

        <div class="mb-8">
            <h1 class="text-2xl font-bold text-slate-900 sm:text-3xl">Hola, {{ $cliente->nombreCompleto() }}</h1>
            <p class="mt-1 text-sm text-slate-500">Consulta tus reservaciones, pagos, servicios y estado de cuenta en un solo lugar.</p>
        </div>

        <!-- Resumen del estado de cuenta -->
        <section class="mb-10 grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
            <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Reservaciones</p>
                <p class="mt-2 text-3xl font-bold text-slate-900">{{ $resumen['reservaciones'] }}</p>
                <p class="mt-1 text-xs text-slate-500">Registradas en tu cuenta</p>
            </div>
            <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Total de cargos</p>
                <p class="mt-2 text-3xl font-bold text-slate-900">${{ number_format($resumen['cargos'], 2) }}</p>
                <p class="mt-1 text-xs text-slate-500">Hospedaje y consumos vigentes</p>
            </div>
            <div class="rounded-2xl border border-emerald-200 bg-emerald-50 p-5 shadow-sm">
                <p class="text-[10px] font-bold uppercase tracking-wider text-emerald-700">Pagos realizados</p>
                <p class="mt-2 text-3xl font-bold text-emerald-700">${{ number_format($resumen['pagado'], 2) }}</p>
                <p class="mt-1 text-xs text-emerald-700/80">Abonos completados</p>
            </div>
            <div class="rounded-2xl border border-amber-200 bg-amber-50 p-5 shadow-sm">
                <p class="text-[10px] font-bold uppercase tracking-wider text-amber-700">Saldo pendiente</p>
                <p class="mt-2 text-3xl font-bold text-amber-700">${{ number_format($resumen['pendiente'], 2) }}</p>
                <p class="mt-1 text-xs text-amber-700/80">Por liquidar</p>
            </div>
        </section>

        <!-- Mi perfil -->
        <section id="mis-datos" class="mb-10 scroll-mt-24">
            <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
                <div class="border-b border-slate-100 bg-slate-50/70 px-5 py-4 sm:px-6">
                    <h2 class="text-lg font-bold text-slate-900">Mi perfil</h2>
                    <p class="text-sm text-slate-500">Actualiza tus datos de contacto. El correo también se usa para iniciar sesión.</p>
                </div>

                <form action="{{ route('cliente.perfil.update') }}" method="POST" class="px-5 py-6 sm:px-6">
                    @csrf
                    @method('PATCH')

                    <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
                        <div>
                            <label for="nombre" class="mb-1.5 block text-sm font-semibold text-slate-700">Nombre</label>
                            <input id="nombre" name="nombre" type="text" value="{{ old('nombre', $cliente->nombre) }}" required
                                class="w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-900 shadow-sm transition placeholder:text-slate-400 focus:border-amber-400 focus:outline-none focus:ring-2 focus:ring-amber-500/30">
                            @error('nombre') <p class="mt-1 text-xs font-medium text-rose-600">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label for="apellido" class="mb-1.5 block text-sm font-semibold text-slate-700">Apellido</label>
                            <input id="apellido" name="apellido" type="text" value="{{ old('apellido', $cliente->apellido) }}" required
                                class="w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-900 shadow-sm transition placeholder:text-slate-400 focus:border-amber-400 focus:outline-none focus:ring-2 focus:ring-amber-500/30">
                            @error('apellido') <p class="mt-1 text-xs font-medium text-rose-600">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label for="email" class="mb-1.5 block text-sm font-semibold text-slate-700">Correo electrónico</label>
                            <input id="email" name="email" type="email" value="{{ old('email', $cliente->email ?? auth()->user()->email) }}" required
                                class="w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-900 shadow-sm transition placeholder:text-slate-400 focus:border-amber-400 focus:outline-none focus:ring-2 focus:ring-amber-500/30">
                            @error('email') <p class="mt-1 text-xs font-medium text-rose-600">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label for="telefono" class="mb-1.5 block text-sm font-semibold text-slate-700">Teléfono</label>
                            <input id="telefono" name="telefono" type="tel" value="{{ old('telefono', $cliente->telefono) }}"
                                class="w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-900 shadow-sm transition placeholder:text-slate-400 focus:border-amber-400 focus:outline-none focus:ring-2 focus:ring-amber-500/30">
                            @error('telefono') <p class="mt-1 text-xs font-medium text-rose-600">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    <div class="mt-6 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                        <a href="{{ route('password.request') }}" class="text-sm font-medium text-amber-600 transition hover:text-amber-700">Cambiar mi contraseña</a>
                        <button type="submit" class="inline-flex items-center justify-center gap-2 rounded-xl bg-amber-500 px-5 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-amber-600 focus:outline-none focus:ring-2 focus:ring-amber-500 focus:ring-offset-2">
                            <flux:icon.check class="size-4" />
                            Guardar cambios
                        </button>
                    </div>
                </form>
            </div>
        </section>

        <!-- Mis reservaciones -->
        <section id="mis-reservaciones" class="mb-10 scroll-mt-24">
            <div class="mb-4 flex flex-col gap-2 sm:flex-row sm:items-end sm:justify-between">
                <div>
                    <h2 class="text-xl font-bold text-slate-900">Mis reservaciones</h2>
                    <p class="text-sm text-slate-500">Detalle de tus estancias y servicios contratados.</p>
                </div>
                <a href="{{ route('habitaciones.public') }}" class="inline-flex items-center justify-center rounded-xl bg-slate-900 px-4 py-2 text-sm font-semibold text-white transition hover:bg-slate-800">Reservar otra estancia</a>
            </div>

            @forelse ($reservas as $reserva)
                @php
                    $colorEstado = $estadoColores[$reserva->estado] ?? 'bg-slate-100 text-slate-700';
                @endphp

                <article class="mb-6 overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
                    <div class="flex flex-col gap-3 border-b border-slate-100 bg-slate-50/60 px-5 py-4 sm:flex-row sm:items-center sm:justify-between sm:px-6">
                        <div class="flex flex-wrap items-center gap-2">
                            <span class="text-sm font-bold text-slate-900">Reservación #{{ str_pad($reserva->id, 5, '0', STR_PAD_LEFT) }}</span>
                            <span class="rounded-full px-2.5 py-1 text-xs font-semibold {{ $colorEstado }}">{{ $reserva->estado }}</span>
                            <span class="text-xs text-slate-400">Creada el {{ $reserva->created_at?->format('d/m/Y') ?? '—' }}</span>
                        </div>
                        <a href="{{ route('estado-cuenta.pdf', $reserva->id) }}"
                           class="inline-flex items-center justify-center gap-2 rounded-lg bg-amber-500 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-amber-600">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                            Descargar estado de cuenta
                        </a>
                    </div>

                    <div class="p-5 sm:p-6">
                        <div class="mb-6 grid grid-cols-1 gap-3 text-sm sm:grid-cols-2 xl:grid-cols-4">
                            <div class="rounded-xl border border-slate-100 bg-slate-50 p-4">
                                <p class="mb-1 text-[10px] font-semibold uppercase tracking-wider text-slate-400">Check-in</p>
                                <p class="font-semibold text-slate-800">{{ $reserva->check_in->format('d/m/Y') }}</p>
                            </div>
                            <div class="rounded-xl border border-slate-100 bg-slate-50 p-4">
                                <p class="mb-1 text-[10px] font-semibold uppercase tracking-wider text-slate-400">Check-out</p>
                                <p class="font-semibold text-slate-800">{{ $reserva->check_out->format('d/m/Y') }}</p>
                            </div>
                            <div class="rounded-xl border border-slate-100 bg-slate-50 p-4">
                                <p class="mb-1 text-[10px] font-semibold uppercase tracking-wider text-slate-400">Noches / huéspedes</p>
                                <p class="font-semibold text-slate-800">{{ (int) $reserva->totalNoches() }} noches · {{ $reserva->huespedes() }} personas</p>
                            </div>
                            <div class="rounded-xl border border-slate-100 bg-slate-50 p-4">
                                <p class="mb-1 text-[10px] font-semibold uppercase tracking-wider text-slate-400">Habitaciones</p>
                                <p class="break-words font-semibold text-slate-800">
                                    @forelse ($reserva->habitacionesAsignadas as $asignacion)
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

                        <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
                            <div>
                                <h3 class="mb-3 text-sm font-bold text-slate-900">Servicios de esta estancia</h3>
                                @if ($reserva->serviciosAsignados->isEmpty())
                                    <p class="text-sm text-slate-500">No registraste servicios adicionales.</p>
                                @else
                                    <div class="overflow-x-auto">
                                        <table class="w-full min-w-[420px] text-sm">
                                            <thead>
                                                <tr class="border-b border-slate-200 text-left text-xs uppercase tracking-wider text-slate-500">
                                                    <th class="py-2 pr-3 font-semibold">Servicio</th>
                                                    <th class="py-2 pr-3 text-right font-semibold">Cant.</th>
                                                    <th class="py-2 text-right font-semibold">Subtotal</th>
                                                </tr>
                                            </thead>
                                            <tbody class="divide-y divide-slate-100">
                                                @foreach ($reserva->serviciosAsignados as $cargo)
                                                    <tr>
                                                        <td class="py-2 pr-3 text-slate-800">{{ $cargo->servicio?->nombre ?? 'Consumo' }}</td>
                                                        <td class="py-2 pr-3 text-right text-slate-700">{{ $cargo->cantidad }}</td>
                                                        <td class="py-2 text-right font-medium text-slate-800">${{ number_format((float) $cargo->subtotal, 2) }}</td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                @endif
                            </div>

                            <div>
                                <h3 class="mb-3 text-sm font-bold text-slate-900">Resumen de importes</h3>
                                <dl class="divide-y divide-slate-100 overflow-hidden rounded-xl border border-slate-200 text-sm">
                                    <div class="flex items-center justify-between gap-4 px-4 py-2.5">
                                        <dt class="text-slate-500">Tarifa de habitación</dt>
                                        <dd class="font-medium text-slate-800">${{ number_format($reserva->tarifaHabitaciones(), 2) }}</dd>
                                    </div>
                                    <div class="flex items-center justify-between gap-4 px-4 py-2.5">
                                        <dt class="text-slate-500">Servicios</dt>
                                        <dd class="font-medium text-slate-800">${{ number_format($reserva->subtotalServicios(), 2) }}</dd>
                                    </div>
                                    <div class="flex items-center justify-between gap-4 px-4 py-2.5">
                                        <dt class="text-slate-500">Total</dt>
                                        <dd class="font-semibold text-slate-900">${{ number_format($reserva->totalConsumos(), 2) }}</dd>
                                    </div>
                                    <div class="flex items-center justify-between gap-4 px-4 py-2.5">
                                        <dt class="text-slate-500">Pagado</dt>
                                        <dd class="font-medium text-emerald-600">${{ number_format($reserva->totalPagado(), 2) }}</dd>
                                    </div>
                                    <div class="flex items-center justify-between gap-4 bg-amber-50/60 px-4 py-2.5">
                                        <dt class="font-semibold text-amber-800">Saldo pendiente</dt>
                                        <dd class="font-bold text-amber-800">${{ number_format($reserva->saldoPendiente(), 2) }}</dd>
                                    </div>
                                </dl>
                            </div>
                        </div>
                    </div>
                </article>
            @empty
                <div class="rounded-2xl border border-dashed border-slate-300 bg-white p-10 text-center shadow-sm">
                    <h3 class="text-lg font-bold text-slate-900">Aún no tienes reservaciones</h3>
                    <p class="mt-1 text-sm text-slate-500">Cuando reserves una habitación, aquí verás su detalle y podrás descargar tu comprobante.</p>
                    <a href="{{ route('habitaciones.public') }}" class="mt-5 inline-block rounded-lg bg-amber-500 px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-amber-600">Reservar ahora</a>
                </div>
            @endforelse
        </section>

        <!-- Historial de pagos -->
        <section id="mis-pagos" class="mb-10 scroll-mt-24">
            <div class="mb-4">
                <h2 class="text-xl font-bold text-slate-900">Historial de pagos</h2>
                <p class="text-sm text-slate-500">Abonos registrados en tus reservaciones.</p>
            </div>

            <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
                <div class="overflow-x-auto">
                    <table class="w-full min-w-[760px] text-left text-sm">
                        <thead>
                            <tr class="border-b border-slate-100 bg-slate-50/80 text-xs font-semibold uppercase tracking-wider text-slate-500">
                                <th class="px-5 py-3">Fecha</th>
                                <th class="px-4 py-3">Concepto</th>
                                <th class="px-4 py-3">Reservación</th>
                                <th class="px-4 py-3">Método</th>
                                <th class="px-4 py-3 text-center">Estado</th>
                                <th class="px-5 py-3 text-right">Importe</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @forelse ($pagos as $pago)
                                <tr class="hover:bg-slate-50/70">
                                    <td class="whitespace-nowrap px-5 py-3 text-slate-600">{{ $pago->fecha_pago?->format('d/m/Y H:i') ?? 'Sin fecha' }}</td>
                                    <td class="px-4 py-3 text-slate-800">{{ $pago->notas ?: 'Abono a reservación' }}</td>
                                    <td class="whitespace-nowrap px-4 py-3 text-slate-600">#{{ str_pad($pago->reserva_id, 5, '0', STR_PAD_LEFT) }}</td>
                                    <td class="whitespace-nowrap px-4 py-3 text-slate-600">{{ $pago->metodo_pago }}</td>
                                    <td class="whitespace-nowrap px-4 py-3 text-center"><x-estado-badge :estado="$pago->estado" /></td>
                                    <td class="whitespace-nowrap px-5 py-3 text-right font-semibold text-slate-900">${{ number_format((float) $pago->monto, 2) }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-5 py-10 text-center text-sm text-slate-500">Todavía no hay pagos registrados en tu cuenta.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </section>

        <!-- Servicios contratados -->
        <section id="mis-servicios" class="mb-10 scroll-mt-24">
            <div class="mb-4">
                <h2 class="text-xl font-bold text-slate-900">Servicios contratados</h2>
                <p class="text-sm text-slate-500">Consumos y servicios asociados a tus reservaciones.</p>
            </div>

            <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
                <div class="overflow-x-auto">
                    <table class="w-full min-w-[820px] text-left text-sm">
                        <thead>
                            <tr class="border-b border-slate-100 bg-slate-50/80 text-xs font-semibold uppercase tracking-wider text-slate-500">
                                <th class="px-5 py-3">Servicio</th>
                                <th class="px-4 py-3">Descripción</th>
                                <th class="px-4 py-3">Fecha</th>
                                <th class="px-4 py-3 text-center">Cantidad</th>
                                <th class="px-4 py-3 text-right">Precio</th>
                                <th class="px-5 py-3 text-center">Estado</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @forelse ($servicios as $cargo)
                                <tr class="hover:bg-slate-50/70">
                                    <td class="px-5 py-3 font-medium text-slate-900">{{ $cargo->servicio?->nombre ?? 'Consumo' }}</td>
                                    <td class="max-w-xs px-4 py-3 text-slate-600">{{ $cargo->servicio?->descripcion ?? 'Sin descripción' }}</td>
                                    <td class="whitespace-nowrap px-4 py-3 text-slate-600">{{ $cargo->created_at?->format('d/m/Y') ?? '—' }}</td>
                                    <td class="px-4 py-3 text-center text-slate-700">{{ $cargo->cantidad }}</td>
                                    <td class="whitespace-nowrap px-4 py-3 text-right font-semibold text-slate-900">${{ number_format((float) $cargo->precio_aplicado, 2) }}</td>
                                    <td class="whitespace-nowrap px-5 py-3 text-center">
                                        <flux:badge color="zinc" size="sm" class="!bg-slate-100 !text-slate-700 !font-semibold">
                                            {{ $estadoServicio($cargo->reserva?->estado ?? 'Pendiente') }}
                                        </flux:badge>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-5 py-10 text-center text-sm text-slate-500">No tienes servicios contratados.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </section>

        <!-- Estado de cuenta -->
        <section id="estado-cuenta" class="scroll-mt-24">
            <div class="mb-4">
                <h2 class="text-xl font-bold text-slate-900">Estado de cuenta</h2>
                <p class="text-sm text-slate-500">Resumen de cargos, pagos y saldo por reservación.</p>
            </div>

            <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
                <div class="overflow-x-auto">
                    <table class="w-full min-w-[760px] text-left text-sm">
                        <thead>
                            <tr class="border-b border-slate-100 bg-slate-50/80 text-xs font-semibold uppercase tracking-wider text-slate-500">
                                <th class="px-5 py-3">Reservación</th>
                                <th class="px-4 py-3">Estado</th>
                                <th class="px-4 py-3 text-right">Cargos</th>
                                <th class="px-4 py-3 text-right">Pagos</th>
                                <th class="px-5 py-3 text-right">Saldo</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @forelse ($reservas as $reserva)
                                <tr class="hover:bg-slate-50/70">
                                    <td class="whitespace-nowrap px-5 py-3 font-medium text-slate-900">#{{ str_pad($reserva->id, 5, '0', STR_PAD_LEFT) }}</td>
                                    <td class="whitespace-nowrap px-4 py-3"><span class="rounded-full px-2.5 py-1 text-xs font-semibold {{ $estadoColores[$reserva->estado] ?? 'bg-slate-100 text-slate-700' }}">{{ $reserva->estado }}</span></td>
                                    <td class="whitespace-nowrap px-4 py-3 text-right text-slate-700">${{ number_format($reserva->totalConsumos(), 2) }}</td>
                                    <td class="whitespace-nowrap px-4 py-3 text-right text-emerald-600">${{ number_format($reserva->totalPagado(), 2) }}</td>
                                    <td class="whitespace-nowrap px-5 py-3 text-right font-bold text-slate-900">${{ number_format($reserva->saldoPendiente(), 2) }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-5 py-10 text-center text-sm text-slate-500">Sin movimientos para mostrar.</td>
                                </tr>
                            @endforelse
                        </tbody>
                        <tfoot class="bg-slate-50/80">
                            <tr class="text-sm font-bold text-slate-900">
                                <td class="px-5 py-3" colspan="2">Totales</td>
                                <td class="whitespace-nowrap px-4 py-3 text-right">${{ number_format($resumen['cargos'], 2) }}</td>
                                <td class="whitespace-nowrap px-4 py-3 text-right text-emerald-700">${{ number_format($resumen['pagado'], 2) }}</td>
                                <td class="whitespace-nowrap px-5 py-3 text-right text-amber-700">${{ number_format($resumen['pendiente'], 2) }}</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </section>

    </main>

</body>
</html>
