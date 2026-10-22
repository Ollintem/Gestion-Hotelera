{{-- ======================================================
     CONTENEDOR DEL INVENTARIO DE HABITACIONES
     ====================================================== --}}

<div>
    {{-- El aviso es de lectura fleeting: Alpine arma un temporizador de tres
         segundos y avisa al contenedor para que retire el mensaje. El `wire:key`
         lleva la secuencia del mensaje, no su texto, para que dos avisos
         seguidos reinicien el temporizador en vez de heredar el del anterior. --}}
    @if ($mensajeExito)
        <div
            wire:key="mensaje-exito-{{ $secuenciaMensaje }}"
            class="mb-4 animate-fade-in"
            x-data
            x-init="setTimeout(() => $dispatch('mensaje-exito-oculto'), 3000)"
        >
            <flux:callout variant="success" icon="check-circle">
                <p>{{ $mensajeExito }}</p>
            </flux:callout>
        </div>
    @endif

    <livewire:habitaciones.filtros
        wire:model.live.debounce.400ms="search"
        :vista="$vista"
        :totalHabitaciones="$this->totalHabitaciones"
        :tarjetas="$this->tarjetasEstado"
        :wire:key="$this->firmaFiltros"
    />

    @if ($this->hayFiltrosActivos)
        <div class="mt-4 animate-fade-in">
            <flux:callout variant="secondary" icon="information-circle">
                <p>Mostrando {{ $this->habitaciones->total() }} {{ $this->habitaciones->total() === 1 ? 'habitación' : 'habitaciones' }} de {{ $this->totalHabitaciones }}.</p>
            </flux:callout>
        </div>
    @endif

    @if ($this->habitaciones->isEmpty())
        <div class="mt-8 rounded-2xl border border-dashed border-slate-300 bg-white/60 px-6 py-16 text-center animate-fade-in-up dark:border-zinc-700 dark:bg-slate-800/40">
            <span class="mx-auto flex size-12 items-center justify-center rounded-full bg-slate-100 dark:bg-zinc-700">
                <flux:icon.building-office class="size-6 text-slate-400 dark:text-zinc-300" />
            </span>
            <h3 class="mt-4 text-base font-semibold text-slate-800 dark:text-zinc-200">Sin habitaciones</h3>
            <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">
                {{ $this->hayFiltrosActivos
                    ? 'Ninguna habitación coincide con los filtros actuales.'
                    : 'Aún no hay habitaciones registradas.' }}
            </p>
        </div>
    @else
        <div @class([
            'mt-8 grid grid-cols-1 gap-5 animate-fade-in-up [animation-delay:120ms]',
            'sm:grid-cols-2 xl:grid-cols-3 2xl:grid-cols-4' => $vista === 'cuadricula',
            'space-y-4' => $vista !== 'cuadricula',
        ])>
            @include('livewire.habitaciones.lista-tarjetas', [
                'habitaciones' => $this->habitaciones,
                'variante' => $vista,
                'claveTarjeta' => $this->claveTarjeta(...),
            ])
        </div>

        <div class="mt-8">
            {{ $this->habitaciones->links() }}
        </div>
    @endif

    {{-- `wire:ignore` (no `.self`) es obligatorio en la raíz del modal.
         Flux solo protege el <dialog> con `wire:ignore.self`, dejando a su
         envoltorio <ui-modal> expuesto: si el contenedor re-renderiza, el
         morph recrea el <dialog> y pierde el atributo `open` que puso
         showModal(), lo que provoca el parpadeo. Ignorando todo el subárbol el
         estado nativo sobrevive a cualquier re-renderizado del contenedor.
         El formulario es un subcomponente con clave estable, así que sigue
        actualizándose con sus propias peticiones. --}}
    <div wire:ignore>
        {{-- El <dialog> no lleva `wire:model`: Flux lo abre desde el navegador
             con `modal-show`, sin pasar por el servidor. `variant="bare"` lo
             deja transparente y el cristal, la tarjeta blanca y las animaciones
             de entrada se construyen en form-modal. --}}
        <flux:modal
            name="habitacion-form"
            variant="bare"
            class="novastay-habitacion-modal w-full max-w-2xl"
        >
            <livewire:habitaciones.form-modal wire:key="formulario" />
        </flux:modal>
    </div>
</div>
