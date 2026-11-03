@php
    $estado = $this->estadoCliente($cliente);
    $iniciales = $this->iniciales($cliente);
    $clasesAvatar = $this->clasesAvatar($cliente);
@endphp

<article
    wire:key="{{ $this->claveTarjeta($cliente) }}"
    class="group relative flex h-full flex-col rounded-2xl border border-slate-200 bg-white p-5 shadow-sm transition-all duration-300 ease-out hover:-translate-y-1 hover:border-slate-300 hover:shadow-lg dark:border-zinc-700 dark:bg-slate-800 dark:hover:border-zinc-600"
>
    <div class="flex items-start justify-between gap-3">
        <div class="flex min-w-0 items-center gap-4">
            <div class="flex size-14 shrink-0 items-center justify-center rounded-full ring-2 {{ $clasesAvatar }}">
                <span class="text-base font-extrabold tracking-wide">{{ $iniciales }}</span>
            </div>

            <div class="min-w-0">
                <h3 class="truncate text-base font-bold text-slate-900 dark:text-white">
                    {{ $cliente->nombreCompleto() }}
                </h3>
                <p class="mt-0.5 truncate text-sm text-slate-500 dark:text-slate-400">
                    {{ $cliente->email ?? 'Sin correo registrado' }}
                </p>
            </div>
        </div>

        <div
            x-data="{ abierto: false }"
            @keydown.escape.window="abierto = false"
            class="relative shrink-0"
        >
            <button
                type="button"
                @click.stop="abierto = !abierto"
                :aria-expanded="abierto.toString()"
                aria-haspopup="menu"
                aria-label="Acciones del cliente"
                class="inline-flex size-9 items-center justify-center rounded-lg text-slate-400 transition-colors duration-200 hover:bg-slate-100 hover:text-slate-700 focus:outline-none focus-visible:ring-2 focus-visible:ring-amber-500 active:scale-90 dark:hover:bg-zinc-700 dark:hover:text-zinc-100"
            >
                <flux:icon.ellipsis-horizontal class="size-5" />
            </button>

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
                role="menu"
                aria-label="Acciones del cliente"
                class="absolute right-0 z-30 mt-2 w-52 origin-top-right rounded-xl border border-slate-200/80 bg-white p-1.5 shadow-xl shadow-slate-950/10 focus:outline-none dark:border-zinc-700/80 dark:bg-zinc-800"
            >
                <button
                    type="button"
                    role="menuitem"
                    wire:click="editar({{ $cliente->id }})"
                    @click="abierto = false"
                    class="flex w-full items-center gap-2.5 rounded-lg px-3 py-2 text-left text-sm font-medium text-slate-700 transition-colors duration-150 hover:bg-slate-100 focus:outline-none focus-visible:bg-slate-100 dark:text-zinc-200 dark:hover:bg-zinc-700"
                >
                    <flux:icon.pencil-square class="size-4 text-slate-400 dark:text-zinc-500" />
                    Editar cliente
                </button>

                <a
                    href="{{ route('reservaciones') }}"
                    wire:navigate
                    role="menuitem"
                    @click="abierto = false"
                    class="flex w-full items-center gap-2.5 rounded-lg px-3 py-2 text-left text-sm font-medium text-slate-700 transition-colors duration-150 hover:bg-slate-100 focus:outline-none focus-visible:bg-slate-100 dark:text-zinc-200 dark:hover:bg-zinc-700"
                >
                    <flux:icon.calendar-days class="size-4 text-slate-400 dark:text-zinc-500" />
                    Ver reservaciones
                </a>

                <div class="my-1 h-px bg-slate-100 dark:bg-zinc-700"></div>

                @if ($this->puedeEliminarse($cliente))
                    <button
                        type="button"
                        role="menuitem"
                        wire:click="eliminar({{ $cliente->id }})"
                        wire:confirm="¿Eliminar a {{ $cliente->nombreCompleto() }}? Esta acción no se puede deshacer."
                        @click="abierto = false"
                        class="flex w-full items-center gap-2.5 rounded-lg px-3 py-2 text-left text-sm font-medium text-rose-600 transition-colors duration-150 hover:bg-rose-50 focus:outline-none focus-visible:bg-rose-50 dark:text-rose-400 dark:hover:bg-rose-500/10"
                    >
                        <flux:icon.trash class="size-4" />
                        Eliminar cliente
                    </button>
                @else
                    <button
                        type="button"
                        role="menuitem"
                        disabled
                        title="No se puede eliminar un cliente con reservaciones"
                        class="flex w-full cursor-not-allowed items-center gap-2.5 rounded-lg px-3 py-2 text-left text-sm font-medium text-slate-300 dark:text-zinc-600"
                    >
                        <flux:icon.trash class="size-4" />
                        Eliminar cliente
                    </button>
                @endif
            </div>
        </div>
    </div>

    <div class="mt-4 flex flex-wrap items-center gap-2">
        <span class="inline-flex items-center gap-1.5 rounded-full px-2.5 py-1 text-xs font-semibold ring-1 ring-inset {{ $estado['clases'] }}">
            <span class="size-1.5 rounded-full {{ $estado['punto'] }}"></span>
            {{ $estado['etiqueta'] }}
        </span>

        <span class="inline-flex items-center gap-1.5 rounded-full bg-slate-100 px-2.5 py-1 text-xs font-semibold text-slate-600 ring-1 ring-inset ring-slate-200 dark:bg-zinc-700/60 dark:text-zinc-300 dark:ring-zinc-600">
            <flux:icon.identification class="size-3.5" />
            {{ $cliente->tipo_identificacion ?? 'Sin identificación' }}
        </span>
    </div>

    <dl class="mt-5 grid grid-cols-2 gap-3 border-t border-slate-100 pt-4 dark:border-zinc-700">
        <div class="rounded-xl bg-slate-50 px-3 py-2.5 dark:bg-zinc-900/40">
            <dt class="text-[10px] font-bold uppercase tracking-wider text-slate-400 dark:text-zinc-500">
                Reservas
            </dt>
            <dd class="mt-1 text-xl font-extrabold tracking-tight text-slate-900 dark:text-white">
                {{ $cliente->reservas_count }}
            </dd>
        </div>

        <div class="rounded-xl bg-slate-50 px-3 py-2.5 dark:bg-zinc-900/40">
            <dt class="text-[10px] font-bold uppercase tracking-wider text-slate-400 dark:text-zinc-500">
                Activas
            </dt>
            <dd class="mt-1 text-xl font-extrabold tracking-tight {{ $cliente->reservas_activas_count > 0 ? 'text-amber-600 dark:text-amber-400' : 'text-slate-900 dark:text-white' }}">
                {{ $cliente->reservas_activas_count }}
            </dd>
        </div>
    </dl>

    <div class="mt-4 flex flex-col gap-1.5 text-xs font-medium text-slate-500 dark:text-slate-400">
        <p class="flex min-w-0 items-center gap-2">
            <flux:icon.phone class="size-3.5 shrink-0" />
            <span class="truncate">{{ $cliente->telefono ?? 'Sin teléfono registrado' }}</span>
        </p>
        <p class="flex min-w-0 items-center gap-2">
            <flux:icon.document-text class="size-3.5 shrink-0" />
            <span class="truncate">
                {{ $cliente->numero_identificacion ?? 'Identificación pendiente' }}
            </span>
        </p>
    </div>
</article>
