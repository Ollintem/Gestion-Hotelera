{{-- ======================================================
     INVENTARIO EN CUADRÍCULA
     ====================================================== --}}

<div class="mt-8 grid grid-cols-1 gap-5 animate-fade-in-up sm:grid-cols-2 xl:grid-cols-3 2xl:grid-cols-4 [animation-delay:120ms]">
    @forelse ($habitaciones as $habitacion)
        <article class="group flex flex-col overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm transition-all duration-300 hover:-translate-y-1 hover:shadow-lg dark:border-zinc-700 dark:bg-slate-800">
            <div class="relative h-52 shrink-0 overflow-hidden">
                <img
                    src="{{ $this->imagenPara($habitacion) }}"
                    alt="{{ $habitacion->tipo?->nombre ?? 'Habitación '.$habitacion->numero_habitacion }}"
                    class="h-full w-full object-cover transition-transform duration-500 ease-out group-hover:scale-105"
                    loading="lazy"
                />
                <div class="absolute inset-0 bg-gradient-to-t from-slate-950/70 via-slate-950/10 to-transparent"></div>

                <span class="absolute left-3 top-3 inline-flex items-center gap-1.5 rounded-full border border-white/40 bg-white/70 px-3 py-1 text-xs font-semibold shadow-sm backdrop-blur-md dark:border-white/10 dark:bg-slate-900/60 {{ $this->clasesTextoEstado($habitacion->estado) }}">
                    <span class="size-1.5 rounded-full {{ $this->clasesPuntoEstado($habitacion->estado) }}"></span>
                    {{ $this->etiquetaEstado($habitacion->estado) }}
                </span>

                <span class="absolute bottom-2.5 right-4 font-serif text-3xl font-bold italic leading-none text-white drop-shadow-lg">
                    #{{ $habitacion->numero_habitacion }}
                </span>
            </div>

            <div class="flex flex-1 flex-col p-5">
                <h3 class="text-lg font-bold text-slate-900 dark:text-white">{{ $habitacion->tipo?->nombre ?? '—' }}</h3>
                <p class="mt-0.5 text-sm text-slate-500 dark:text-slate-400">
                    Piso {{ $habitacion->piso }} &middot; {{ $habitacion->tipo?->capacidad ?? '—' }} personas
                </p>

                <div class="mt-3 flex items-baseline gap-1">
                    <span class="text-2xl font-extrabold tracking-tight text-slate-950 dark:text-white">
                        ${{ number_format((float) ($habitacion->tipo?->precio_base ?? 0), 0) }}
                    </span>
                    <span class="text-sm font-medium text-slate-400 dark:text-slate-500">/noche</span>
                </div>

                <div class="mt-5 flex items-center justify-between border-t border-slate-100 pt-4 dark:border-zinc-700">
                    <span class="inline-flex items-center gap-1.5 text-xs font-semibold text-slate-500 dark:text-zinc-400">
                        <span class="size-2 rounded-full {{ $this->clasesPuntoEstado($habitacion->estado) }}"></span>
                        {{ $this->etiquetaLimpieza($habitacion->estado) }}
                    </span>

                    <div class="flex items-center gap-1.5">
                        <button
                            type="button"
                            wire:click="editar({{ $habitacion->id }})"
                            title="Editar habitación"
                            aria-label="Editar habitación"
                            class="rounded-lg p-2 text-slate-400 transition-all duration-200 hover:bg-slate-100 hover:text-slate-700 focus:outline-none focus:ring-2 focus:ring-amber-500 active:scale-90 dark:hover:bg-zinc-700 dark:hover:text-zinc-100"
                        >
                            <flux:icon.pencil-square class="size-4" />
                        </button>
                        <button
                            type="button"
                            wire:click="eliminar({{ $habitacion->id }})"
                            wire:confirm="¿Eliminar esta habitación?"
                            title="Eliminar habitación"
                            aria-label="Eliminar habitación"
                            class="rounded-lg p-2 text-slate-400 transition-all duration-200 hover:bg-red-50 hover:text-red-600 focus:outline-none focus:ring-2 focus:ring-red-500 active:scale-90 dark:hover:bg-red-500/10 dark:hover:text-red-400"
                        >
                            <flux:icon.trash class="size-4" />
                        </button>
                    </div>
                </div>
            </div>
        </article>
    @empty
        <div class="col-span-full rounded-2xl border border-dashed border-slate-300 bg-white/60 px-6 py-16 text-center dark:border-zinc-700 dark:bg-slate-800/40">
            <span class="mx-auto flex size-12 items-center justify-center rounded-full bg-slate-100 dark:bg-zinc-700">
                <flux:icon.building-office class="size-6 text-slate-400 dark:text-zinc-300" />
            </span>
            <h3 class="mt-4 text-base font-semibold text-slate-800 dark:text-zinc-200">Sin habitaciones</h3>
            <p class="mt-1 text-sm text-slate-500 dark:text-zinc-400">
                {{ $search !== '' || $filtroEstado !== 'todos'
                    ? 'Ninguna habitación coincide con los filtros actuales.'
                    : 'Aún no hay habitaciones registradas.' }}
            </p>
        </div>
    @endforelse
</div>