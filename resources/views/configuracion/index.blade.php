{{-- ======================================================
     PANTALLA DEL MÓDULO DE CONFIGURACIÓN

     Cabecera, selector de apartado y contenido activo. Solo
     hay dos apartados: el general (solo lectura) y la
     información del hotel (editable con configuracion.editar).
     ====================================================== --}}

<div>
    <x-aviso-temporal :mensaje="$mensajeExito" tipo="success" clave="configuracion-exito" />
    <x-aviso-temporal :mensaje="$mensajeError" tipo="error" clave="configuracion-error" />

    <div class="mb-6">
        <flux:heading size="xl" class="!text-slate-900 !font-bold text-2xl">Configuración</flux:heading>
        <flux:subheading class="!text-slate-600 !font-medium">
            Preferencias generales del sistema y datos del establecimiento.
        </flux:subheading>
    </div>

    {{-- Selector de apartado: mismo segmented control que usan los demás módulos --}}
    <div class="mb-6 flex items-center gap-1 rounded-xl border border-slate-200 bg-white p-1 shadow-sm dark:border-zinc-700 dark:bg-zinc-900">
        <button
            type="button"
            wire:click="verApartado('general')"
            aria-pressed="{{ $apartado === 'general' ? 'true' : 'false' }}"
            @class([
                'flex-1 rounded-lg px-4 py-2 text-sm font-semibold transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-amber-500',
                'bg-amber-600 text-white shadow-sm' => $apartado === 'general',
                'text-slate-500 hover:bg-slate-100 hover:text-slate-800 dark:text-slate-400 dark:hover:bg-zinc-800 dark:hover:text-white' => $apartado !== 'general',
            ])
        >
            Configuración general
        </button>
        <button
            type="button"
            wire:click="verApartado('hotel')"
            aria-pressed="{{ $apartado === 'hotel' ? 'true' : 'false' }}"
            @class([
                'flex-1 rounded-lg px-4 py-2 text-sm font-semibold transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-amber-500',
                'bg-amber-600 text-white shadow-sm' => $apartado === 'hotel',
                'text-slate-500 hover:bg-slate-100 hover:text-slate-800 dark:text-slate-400 dark:hover:bg-zinc-800 dark:hover:text-white' => $apartado !== 'hotel',
            ])
        >
            Información del hotel
        </button>
    </div>

    @if ($apartado === 'hotel')
        @include('configuracion.partials.hotel')
    @else
        @include('configuracion.partials.general')
    @endif
</div>
