<div>
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

    @include('checkin-checkout.partials.header')

    @include('checkin-checkout.partials.pendientes')

    @include('checkin-checkout.partials.historial')

    @include('checkin-checkout.partials.modal')
</div>
