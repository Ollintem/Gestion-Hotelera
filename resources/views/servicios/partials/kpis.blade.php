{{-- ======================================================
     KPIs (Métricas Superiores)

     Las cifras llegan en el array $kpis desde el contenedor y
     describen el hotel completo, no el recorte que se esté viendo.

     Las cuatro tarjetas comparten la misma base: el color no las
     distingue, la distingue la cifra. Solo el icono de fondo,
     apenas visible, evita que la fila se lea como cuatro bloques
     idénticos, y la sombra al pasar el cursor marca cuál se está
     leyendo.
     ====================================================== --}}

@php
    $tarjeta = 'relative overflow-hidden rounded-2xl border border-gray-200 bg-white p-4 shadow-sm transition-all duration-300 hover:border-amber-300 hover:shadow-md';

    $watermark = 'pointer-events-none absolute -bottom-3 -right-3 size-20 text-amber-500/20';
@endphp

<div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
    <div class="{{ $tarjeta }}">
        <flux:icon.cube class="{{ $watermark }}" />

        <p class="text-xs font-medium uppercase tracking-wider text-gray-500">
            CATÁLOGO
        </p>
        <p class="mt-2 text-2xl font-bold text-gray-900">
            {{ $kpis['totalServicios'] }}
        </p>
        <p class="mt-1 text-xs text-gray-400">
            Total de servicios
        </p>
    </div>

    <div class="{{ $tarjeta }}">
        <flux:icon.squares-2x2 class="{{ $watermark }}" />

        <p class="text-xs font-medium uppercase tracking-wider text-gray-500">
            CATEGORÍAS
        </p>
        <p class="mt-2 text-2xl font-bold text-gray-900">
            {{ $kpis['categoriasActivas'] }}
        </p>
        <p class="mt-1 text-xs text-gray-400">
            Categorías activas
        </p>
    </div>

    <div class="{{ $tarjeta }}">
        <flux:icon.rectangle-stack class="{{ $watermark }}" />

        <p class="text-xs font-medium uppercase tracking-wider text-gray-500">
            CARGOS REGISTRADOS
        </p>
        <p class="mt-2 text-2xl font-bold text-gray-900">
            {{ $kpis['cargosRegistrados'] }}
        </p>
        <p class="mt-1 text-xs text-gray-400">
            Cargos en reserva_servicio
        </p>
    </div>

    <div class="{{ $tarjeta }}">
        <flux:icon.banknotes class="{{ $watermark }}" />

        <p class="text-xs font-medium uppercase tracking-wider text-gray-500">
            CONSUMOS FACTURADOS
        </p>
        <p class="mt-2 text-2xl font-bold text-gray-900">
            ${{ number_format($kpis['consumosFacturados'], 2) }}
        </p>
        <p class="mt-1 text-xs text-gray-400">
            Reservas finalizadas
        </p>
    </div>
</div>
