{{-- ======================================================
     TARJETA: GASTOS TOTALES

     El importe llega desde `Gasto::sum('monto')`, que recorre
     toda la tabla: la cifra no depende de la página visible
     aunque la tabla esté paginada. Con ceros imprime $0.00.
     ====================================================== --}}

@php
    $tarjeta = 'relative overflow-hidden rounded-2xl border border-gray-200 bg-white p-4 shadow-sm transition-all duration-300 hover:border-amber-300 hover:shadow-md';
    $watermark = 'pointer-events-none absolute -bottom-3 -right-3 size-20 text-amber-500/20';
@endphp

<div class="grid grid-cols-1 gap-4 mb-6 sm:grid-cols-3">
    <div class="{{ $tarjeta }} sm:col-span-2">
        <flux:icon.banknotes class="{{ $watermark }}" />

        <p class="text-xs font-medium uppercase tracking-wider text-gray-500">
            Gastos totales
        </p>
        <p class="mt-2 text-3xl font-bold text-gray-900 tabular-nums">
            ${{ number_format($total, 2) }}
        </p>
        <p class="mt-1 text-xs text-gray-400">
            Suma de todos los gastos registrados
        </p>
    </div>

    <div class="{{ $tarjeta }}">
        <flux:icon.document-text class="{{ $watermark }}" />

        <p class="text-xs font-medium uppercase tracking-wider text-gray-500">
            Registros
        </p>
        <p class="mt-2 text-3xl font-bold text-gray-900 tabular-nums">
            {{ $gastos->total() }}
        </p>
        <p class="mt-1 text-xs text-gray-400">
            {{ $gastos->total() === 1 ? 'Gasto registrado' : 'Gastos registrados' }}
        </p>
    </div>
</div>
