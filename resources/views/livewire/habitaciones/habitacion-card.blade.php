{{-- ======================================================
     TARJETA DE HABITACIÓN (CUADRÍCULA / LISTA)
     ====================================================== --}}

@php
    $esLista = $variante !== 'cuadricula';
@endphp

<article @class([
    'group flex flex-col overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm transition-all duration-300 dark:border-zinc-700 dark:bg-slate-800',
    'hover:-translate-y-1 hover:shadow-lg' => ! $esLista,
    'hover:shadow-lg sm:flex-row' => $esLista,
])>
    <div @class([
        'relative shrink-0 overflow-hidden',
        'h-52' => ! $esLista,
        'h-44 sm:h-auto sm:w-56' => $esLista,
    ])>
        <img
            src="{{ $habitacion->imagen() }}"
            alt="{{ $habitacion->tipoHabitacion?->nombre ?? 'Habitación '.$habitacion->numero_habitacion }}"
            class="h-full w-full object-cover transition-transform duration-500 ease-out group-hover:scale-105"
            loading="lazy"
        />
        <div @class([
            'absolute inset-0 bg-gradient-to-t from-slate-950/70 via-slate-950/10 to-transparent',
            'sm:bg-gradient-to-r' => $esLista,
        ])></div>

        <span class="absolute left-3 top-3 inline-flex max-w-[calc(100%-1.5rem)] items-center gap-1.5 truncate rounded-full border border-white/40 bg-white/70 px-3 py-1 text-xs font-semibold shadow-sm backdrop-blur-md dark:border-white/10 dark:bg-slate-900/60 {{ $this->clasesTextoEstado($habitacion->estado) }}">
            <span class="size-1.5 shrink-0 rounded-full {{ $this->clasesPuntoEstado($habitacion->estado) }}"></span>
            <span class="truncate">{{ $this->etiquetaEstado($habitacion->estado) }}</span>
        </span>

        <span class="absolute bottom-2.5 right-4 max-w-[45%] truncate font-serif text-3xl font-bold italic leading-none text-white drop-shadow-lg">
            #{{ $habitacion->numero_habitacion }}
        </span>
    </div>

    <div @class([
        'flex flex-1 flex-col',
        'gap-4 p-5 lg:flex-row lg:items-center lg:justify-between' => $esLista,
        'p-5' => ! $esLista,
    ])>
        <div class="min-w-0">
            <h3 class="line-clamp-2 break-words text-lg font-bold text-slate-900 dark:text-white">{{ $habitacion->tipoHabitacion?->nombre ?? '—' }}</h3>
            <p class="mt-0.5 text-sm text-slate-500 dark:text-slate-400">
                Piso {{ $habitacion->piso }} &middot; {{ $habitacion->tipoHabitacion?->capacidad ?? '—' }} personas
            </p>

            <div class="mt-3 flex flex-wrap items-center gap-4">
                <span class="inline-flex items-center gap-1.5 text-xs font-semibold text-slate-500 dark:text-zinc-400">
                    <span class="size-2 rounded-full {{ $this->clasesPuntoEstado($habitacion->estado) }}"></span>
                    {{ $this->etiquetaLimpieza($habitacion->estado) }}
                </span>
            </div>
        </div>

        @if (! $esLista)
            <div class="mt-5 flex items-baseline gap-1">
                <span class="text-2xl font-extrabold tracking-tight text-slate-950 dark:text-white">
                    ${{ number_format((float) ($habitacion->tipoHabitacion?->precio_base ?? 0), 0) }}
                </span>
                <span class="text-sm font-medium text-slate-400 dark:text-zinc-500">/noche</span>
            </div>
        @endif
    </div>

    <div @class([
        'flex flex-wrap items-center justify-between gap-3 sm:gap-6',
        'border-t border-slate-100 px-5 py-4 dark:border-zinc-700' => $esLista,
        'border-t border-slate-100 pt-4 dark:border-zinc-700' => ! $esLista,
    ])>
        @if ($esLista)
            <div class="flex items-baseline gap-1">
                <span class="text-xl font-extrabold tracking-tight text-slate-950 dark:text-white">
                    ${{ number_format((float) ($habitacion->tipoHabitacion?->precio_base ?? 0), 0) }}
                </span>
                <span class="text-sm font-medium text-slate-400 dark:text-zinc-500">/noche</span>
            </div>
        @else
            <span class="line-clamp-2 min-w-0 flex-1 text-xs text-slate-400 dark:text-zinc-500">
                {{ $habitacion->tipoHabitacion?->descripcion ?? 'Sin descripción' }}
            </span>
        @endif

        <div class="flex items-center gap-1.5">
            {{-- Sin `wire:click`: el <dialog> se abre en el navegador y el id
                 viaja en el evento que solo atiende el formulario. --}}
            <button
                type="button"
                x-data
                x-on:click="$dispatch('modal-show', { name: 'habitacion-form' }); $dispatch('abrir-formulario', { id: {{ $habitacion->id }} })"
                title="Editar habitación"
                aria-label="Editar habitación"
                class="rounded-lg p-2 text-slate-400 transition-all duration-200 hover:bg-slate-100 hover:text-slate-700 focus:outline-none focus:ring-2 focus:ring-amber-500 active:scale-90 dark:hover:bg-zinc-700 dark:hover:text-zinc-100"
            >
                <flux:icon.pencil-square class="size-4" />
            </button>
            <button
                type="button"
                wire:click="$parent.eliminar({{ $habitacion->id }})"
                wire:confirm="¿Eliminar esta habitación?"
                title="Eliminar habitación"
                aria-label="Eliminar habitación"
                class="rounded-lg p-2 text-slate-400 transition-all duration-200 hover:bg-red-50 hover:text-red-600 focus:outline-none focus:ring-2 focus:ring-red-500 active:scale-90 dark:hover:bg-red-500/10 dark:hover:text-red-400"
            >
                <flux:icon.trash class="size-4" />
            </button>
        </div>
    </div>
</article>
