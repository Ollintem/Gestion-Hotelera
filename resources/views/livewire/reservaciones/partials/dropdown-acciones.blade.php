{{-- ======================================================
     RESERVACIONES · MENÚ DE ACCIONES (tres puntos)
     El panel se teletransporta al <body> para que el
     scroll horizontal de la tabla no lo recorte.
     ====================================================== --}}

<div
    x-data="{
        abierto: false,
        arriba: 0,
        derecha: 0,
        posicionar() {
            const rect = this.$refs.boton.getBoundingClientRect();
            this.arriba = rect.bottom + 8;
            this.derecha = window.innerWidth - rect.right;
        },
        alternar() {
            this.posicionar();
            this.abierto = !this.abierto;
        },
        confirmarCancelacion(id, codigo) {
            this.abierto = false;

            const cancelar = () => this.$wire.cancelarReserva(id);

            if (!window.Swal) {
                if (window.confirm(`¿Cancelar la reservación ${codigo}?`)) {
                    cancelar();
                }

                return;
            }

            window.Swal.fire({
                icon: 'warning',
                title: '¿Cancelar la reservación?',
                text: `La reservación ${codigo} se marcará como cancelada.`,
                showCancelButton: true,
                confirmButtonText: 'Sí, cancelar',
                cancelButtonText: 'Volver',
                confirmButtonColor: '#e11d48',
                cancelButtonColor: '#64748b',
                reverseButtons: true,
                focusCancel: true,
            }).then((resultado) => {
                if (resultado.isConfirmed) {
                    cancelar();
                }
            });
        },
    }"
    @keydown.escape.window="abierto = false"
    @scroll.window="abierto = false"
    @resize.window="abierto = false"
    class="flex justify-end"
>
    <button
        x-ref="boton"
        type="button"
        @click.stop="alternar()"
        :aria-expanded="abierto.toString()"
        aria-haspopup="menu"
        aria-label="Acciones de la reservación"
        class="inline-flex size-9 items-center justify-center rounded-lg text-slate-400 transition-colors duration-200 hover:bg-slate-100 hover:text-slate-700 focus:outline-none focus-visible:ring-2 focus-visible:ring-amber-500 active:scale-90 dark:hover:bg-zinc-700 dark:hover:text-zinc-100"
    >
        <flux:icon.ellipsis-horizontal class="size-5" />
    </button>

    <template x-teleport="body">
        <div
            x-show="abierto"
            x-cloak
            @click.outside="abierto = false"
            x-transition:enter="transition ease-out duration-150"
            x-transition:enter-start="opacity-0 -translate-y-1 scale-95"
            x-transition:enter-end="opacity-100 translate-y-0 scale-100"
            x-transition:leave="transition ease-in duration-100"
            x-transition:leave-start="opacity-100 translate-y-0 scale-100"
            x-transition:leave-end="opacity-0 -translate-y-1 scale-95"
            :style="`position: fixed; top: ${arriba}px; right: ${derecha}px;`"
            role="menu"
            aria-label="Acciones de la reservación"
            class="z-[90] w-52 origin-top-right rounded-xl border border-slate-200/80 bg-white p-1.5 shadow-xl shadow-slate-950/10 focus:outline-none dark:border-zinc-700/80 dark:bg-zinc-800"
        >
            @php
                $claseItem = 'flex w-full items-center gap-2.5 rounded-lg px-3 py-2 text-left text-sm font-medium text-slate-700 transition-colors duration-150 hover:bg-slate-100 focus:outline-none focus-visible:bg-slate-100 dark:text-zinc-200 dark:hover:bg-zinc-700';
            @endphp

            <button type="button" role="menuitem" wire:click="editar({{ $reserva->id }})" @click="abierto = false" class="{{ $claseItem }}">
                <flux:icon.pencil-square class="size-4 text-slate-400 dark:text-slate-500" />
                Editar
            </button>

            <button type="button" role="menuitem" wire:click="registrarPago({{ $reserva->id }})" @click="abierto = false" class="{{ $claseItem }}">
                <flux:icon.banknotes class="size-4 text-slate-400 dark:text-slate-500" />
                Registrar pago
            </button>

            <button type="button" role="menuitem" wire:click="checkIn({{ $reserva->id }})" @click="abierto = false" class="{{ $claseItem }}">
                <flux:icon.arrow-right-start-on-rectangle class="size-4 text-slate-400 dark:text-slate-500" />
                Check-in
            </button>

            <button type="button" role="menuitem" wire:click="checkOut({{ $reserva->id }})" @click="abierto = false" class="{{ $claseItem }}">
                <flux:icon.arrow-left-end-on-rectangle class="size-4 text-slate-400 dark:text-slate-500" />
                Check-out
            </button>

            <div class="my-1 h-px bg-slate-100 dark:bg-zinc-700"></div>

            <button
                type="button"
                role="menuitem"
                data-codigo="{{ $this->codigo($reserva) }}"
                x-on:click="confirmarCancelacion({{ $reserva->id }}, $el.dataset.codigo)"
                class="flex w-full items-center gap-2.5 rounded-lg px-3 py-2 text-left text-sm font-medium text-rose-600 transition-colors duration-150 hover:bg-rose-50 focus:outline-none focus-visible:bg-rose-50 dark:text-rose-400 dark:hover:bg-rose-500/10"
            >
                <flux:icon.x-circle class="size-4" />
                Cancelar
            </button>
        </div>
    </template>
</div>
