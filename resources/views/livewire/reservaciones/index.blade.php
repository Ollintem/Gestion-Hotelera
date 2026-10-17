{{-- ======================================================
     RESERVACIONES · CONTENEDOR PRINCIPAL
     ====================================================== --}}

<div>
    @if ($mensajeExito)
        <div class="mb-4 animate-fade-in">
            <flux:callout variant="success" icon="check-circle">
                <p>{{ $mensajeExito }}</p>
            </flux:callout>
        </div>
    @endif

    {{-- ======================================================
         CABECERA
         ====================================================== --}}
    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <flux:heading size="xl" class="!text-2xl !font-bold !text-slate-900 dark:!text-white">
                Reservaciones
            </flux:heading>
            <flux:subheading class="!font-medium !text-slate-600 dark:!text-slate-400">
                Consulta las reservaciones del hotel, sus huéspedes y su estado de pago.
            </flux:subheading>
        </div>

        <flux:button
            type="button"
            variant="primary"
            icon="plus"
            x-on:click="$dispatch('reserva.crear')"
            class="shrink-0"
        >
            Nueva reservación
        </flux:button>
    </div>

    @include('livewire.reservaciones.partials.tabla', ['reservas' => $reservas])

    @include('livewire.reservaciones.partials.modal-crear')
</div>
