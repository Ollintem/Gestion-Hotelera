{{-- ======================================================
     PANTALLA DEL MÓDULO DE CONFIGURACIÓN

     Cabecera, selector de apartado y contenido activo. Solo
     hay dos apartados: el general (solo lectura) y la
     información del hotel (editable con configuracion.editar).
     ====================================================== --}}

<div>
    {{--
        NOTIFICACIONES DE ÉXITO / ERROR — visibles 5 s (5000 ms).

        Mismo mecanismo que el módulo de gastos: `@entangle` + `$watch`
        relanzan el temporizador de 5000 ms en cuanto cambia el mensaje del
        servidor, así cada aviso de guardado u actualización del hotel recibe
        sus 5 segundos completos.
    --}}
    @if ($mensajeExito || $mensajeError)
        <div
            x-data="{
                exito: @entangle('mensajeExito'),
                error: @entangle('mensajeError'),
                visible: false,
                temporizador: null,
                init() {
                    this.$watch('exito', (valor) => valor && this.mostrar());
                    this.$watch('error', (valor) => valor && this.mostrar());
                    if (this.exito || this.error) this.mostrar();
                },
                mostrar() {
                    clearTimeout(this.temporizador);
                    this.visible = true;
                    this.temporizador = setTimeout(() => { this.visible = false }, 5000);
                },
            }"
            x-show="visible"
            x-cloak
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0 -translate-y-1"
            x-transition:enter-end="opacity-100 translate-y-0"
            x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="opacity-100 translate-y-0"
            x-transition:leave-end="opacity-0 -translate-y-1"
            role="status"
            aria-live="polite"
        >
            @if ($mensajeExito)
                <flux:callout variant="success" icon="check-circle" class="mb-4">
                    <p>{{ $mensajeExito }}</p>
                </flux:callout>
            @endif

            @if ($mensajeError)
                <flux:callout variant="danger" icon="exclamation-triangle" class="mb-4">
                    <p>{{ $mensajeError }}</p>
                </flux:callout>
            @endif
        </div>
    @endif

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
