<div>
    {{--
        NOTIFICACIONES DE ÉXITO / ERROR — visibles 5 s (5000 ms).

        `@entangle` enlaza el mensaje del servidor con Alpine. Cada vez que el
        valor cambia, `$watch` relanza `mostrar()`, que cancela el temporizador
        anterior y arranca uno nuevo de 5000 ms: así un aviso posterior no se
        oculta antes de tiempo porque todavía quedara tiempo del anterior, y el
        mismo texto repetido vuelve a mostrarse porque `crear()`/`editar()`
        vacían el mensaje entre medias y Alpine vuelve a montar el bloque.
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

    @include('gastos.partials.header')

    @include('gastos.partials.totales')

    @include('gastos.partials.table')
</div>
