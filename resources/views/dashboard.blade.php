<x-layouts.app>
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        <!-- Aquí van las 4 tarjetas que te pasé -->
         <!-- Ejemplo para el estado vacío dentro de tu tabla de llegadas -->
<div class="flex flex-col items-center justify-center py-12 px-4">
    <!-- Un ícono sutil (Usa tu componente Flux o un SVG de Heroicons) -->
    <div class="bg-slate-50 p-4 rounded-full mb-3">
        <svg class="w-8 h-8 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" />
        </svg>
    </div>
    <h4 class="text-sm font-semibold text-slate-900">El lobby está despejado</h4>
    <p class="text-sm text-slate-500 mt-1 text-center max-w-sm">No hay clientes programados para llegar en las próximas horas.</p>
</div>
    </div>
</x-layouts.app>
