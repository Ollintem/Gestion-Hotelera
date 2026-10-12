{{-- ======================================================
     PANEL DE HOUSEKEEPING — VISTA PRINCIPAL
     Tarjetas tipo Kanban optimizadas para móvil.
     ====================================================== --}}

<div
    x-data="{
        pendiente: null,
        confirmarEliminacion() {
            if (this.pendiente === null) {
                return;
            }

            $wire.eliminar(this.pendiente.id);

            this.pendiente = null;
        },
    }"
    x-on:eliminar-tarea.window="pendiente = $event.detail"
>
    {{-- ======================================================
         TOAST DE ÉXITO — flotante (top-end), se auto-oculta a los 3 s.
         Reacciona a `$mensajeExito` vía `@entangle`: cualquier cambio del
         mensaje vuelve a lanzar el toast sin depender de eventos del backend.
         ====================================================== --}}
    @if ($mensajeExito)
        <div
            x-data="{
                visible: false,
                mensaje: @entangle('mensajeExito'),
                temporizador: null,
                init() {
                    this.$watch('mensaje', (valor) => valor && this.mostrar());
                    if (this.mensaje) this.mostrar();
                },
                mostrar() {
                    clearTimeout(this.temporizador);
                    this.visible = true;
                    this.temporizador = setTimeout(() => this.visible = false, 3000);
                }
            }"
            x-show="visible"
            x-cloak
            x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="opacity-0 -translate-y-2"
            x-transition:enter-end="opacity-100 translate-y-0"
            x-transition:leave="transition ease-in duration-200"
            x-transition:leave-start="opacity-100 translate-y-0"
            x-transition:leave-end="opacity-0"
            role="status"
            aria-live="polite"
            class="pointer-events-auto fixed right-4 top-4 z-[100] flex w-[calc(100%-2rem)] max-w-sm items-start gap-3 rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-semibold text-emerald-900 shadow-lg shadow-slate-900/10 dark:border-emerald-500/40 dark:bg-emerald-500/10 dark:text-emerald-100"
        >
            <svg class="mt-0.5 size-5 shrink-0 text-emerald-600 dark:text-emerald-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
            </svg>

            <p class="min-w-0 flex-1 leading-snug">{{ $mensajeExito }}</p>

            <button
                type="button"
                x-on:click="visible = false"
                class="-mr-1 -mt-1 shrink-0 rounded-lg p-1 opacity-60 transition hover:opacity-100 focus:outline-none focus-visible:ring-2 focus-visible:ring-current"
                aria-label="Cerrar notificación"
            >
                <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                </svg>
            </button>
        </div>
    @endif

    @include('limpieza.partials.header')

    @php
        $conteoRequiereLimpieza = $tareas->where('estado', 'Pendiente')->count();
        $conteoEnLimpieza = $tareas->where('estado', 'En Proceso')->count();
        $conteoListas = $tareas->where('estado', 'Completado')->count();
    @endphp

    {{-- ======================================================
         CONTADORES (KPIs)
         ====================================================== --}}
    <div class="mb-6 grid grid-cols-1 gap-3 sm:grid-cols-3">
        <div class="flex items-center gap-3 rounded-2xl border border-rose-200 bg-rose-50 p-4 dark:border-rose-500/30 dark:bg-rose-500/10">
            <span class="flex size-10 shrink-0 items-center justify-center rounded-xl bg-rose-100 text-rose-600 dark:bg-rose-500/20 dark:text-rose-400">
                <flux:icon.exclamation-triangle class="size-5" />
            </span>
            <div class="min-w-0">
                <p class="text-2xl font-bold leading-none text-rose-700 dark:text-rose-400">{{ $conteoRequiereLimpieza }}</p>
                <p class="mt-1 truncate text-xs font-semibold text-rose-600 dark:text-rose-400">Requiere limpieza</p>
            </div>
        </div>

        <div class="flex items-center gap-3 rounded-2xl border border-sky-200 bg-sky-50 p-4 dark:border-sky-500/30 dark:bg-sky-500/10">
            <span class="flex size-10 shrink-0 items-center justify-center rounded-xl bg-sky-100 text-sky-600 dark:bg-sky-500/20 dark:text-sky-400">
                <flux:icon.arrow-path class="size-5" />
            </span>
            <div class="min-w-0">
                <p class="text-2xl font-bold leading-none text-sky-700 dark:text-sky-400">{{ $conteoEnLimpieza }}</p>
                <p class="mt-1 truncate text-xs font-semibold text-sky-600 dark:text-sky-400">En limpieza</p>
            </div>
        </div>

        <div class="flex items-center gap-3 rounded-2xl border border-emerald-200 bg-emerald-50 p-4 dark:border-emerald-500/30 dark:bg-emerald-500/10">
            <span class="flex size-10 shrink-0 items-center justify-center rounded-xl bg-emerald-100 text-emerald-600 dark:bg-emerald-500/20 dark:text-emerald-400">
                <flux:icon.check-circle class="size-5" />
            </span>
            <div class="min-w-0">
                <p class="text-2xl font-bold leading-none text-emerald-700 dark:text-emerald-400">{{ $conteoListas }}</p>
                <p class="mt-1 truncate text-xs font-semibold text-emerald-600 dark:text-emerald-400">Listas</p>
            </div>
        </div>
    </div>

    {{-- ======================================================
         TARJETAS DE HABITACIÓN
         ====================================================== --}}
    <div class="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-3">
        @forelse ($tareas as $tarea)
            @php
                $estado = $tarea->estado;
                $numero = $tarea->habitacion?->numero_habitacion ?? '—';
                $tipo = $tarea->habitacion?->tipoHabitacion?->nombre ?? 'Sin tipo';
                $piso = $tarea->habitacion?->piso ?? '—';

                $etiquetaEstado = match ($estado) {
                    'Pendiente' => 'Requiere limpieza',
                    'En Proceso' => 'En limpieza',
                    default => 'Lista',
                };

                $clasesInsignia = match ($estado) {
                    'Pendiente' => 'border-rose-200 bg-rose-100 text-rose-700 dark:border-rose-500/30 dark:bg-rose-500/15 dark:text-rose-300',
                    'En Proceso' => 'border-sky-200 bg-sky-100 text-sky-700 dark:border-sky-500/30 dark:bg-sky-500/15 dark:text-sky-300',
                    default => 'border-emerald-200 bg-emerald-100 text-emerald-700 dark:border-emerald-500/30 dark:bg-emerald-500/15 dark:text-emerald-300',
                };

                $etiquetaAccion = match ($estado) {
                    'Pendiente' => 'Iniciar limpieza',
                    'En Proceso' => 'Marcar como lista',
                    default => 'Ver detalle',
                };

                $clasesAccion = match ($estado) {
                    'Pendiente' => 'bg-slate-800 text-white hover:bg-slate-700 focus:ring-slate-500 dark:bg-slate-900 dark:hover:bg-slate-800',
                    'En Proceso' => 'bg-amber-500 text-white hover:bg-amber-600 focus:ring-amber-500 dark:bg-amber-500 dark:hover:bg-amber-600',
                    default => 'bg-slate-100 text-slate-600 hover:bg-slate-200 focus:ring-slate-400 dark:bg-zinc-800 dark:text-zinc-300 dark:hover:bg-zinc-700',
                };
            @endphp

            <article @class([
                'flex flex-col overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm transition-shadow duration-200 hover:shadow-md dark:border-zinc-700 dark:bg-slate-800',
            ])>
                <div class="flex items-start justify-between gap-3 p-5">
                    <div class="min-w-0">
                        <p class="text-3xl font-extrabold leading-none tracking-tight text-slate-900 dark:text-white">
                            #{{ $numero }}
                        </p>
                        <p class="mt-2 text-sm text-slate-500 dark:text-slate-400">
                            {{ $tipo }}
                            <span class="mx-1 text-slate-300 dark:text-zinc-600">&middot;</span>
                            Piso {{ $piso }}
                        </p>
                    </div>

                    <span @class([
                        'inline-flex shrink-0 items-center gap-1.5 rounded-full border px-2.5 py-1 text-xs font-semibold',
                        $clasesInsignia,
                    ])>
                        <span class="size-1.5 rounded-full bg-current"></span>
                        {{ $etiquetaEstado }}
                    </span>
                </div>

                @if (filled($tarea->notas))
                    <div class="px-5">
                        <p class="line-clamp-2 rounded-xl bg-slate-50 px-3 py-2 text-xs leading-relaxed text-slate-500 dark:bg-slate-900/50 dark:text-slate-400">
                            {{ $tarea->notas }}
                        </p>
                    </div>
                @endif

                <div class="mt-auto flex items-center justify-between gap-3 px-5 pb-4 pt-4">
                    <span class="flex min-w-0 items-center gap-1.5 text-xs font-medium text-slate-500 dark:text-slate-400">
                        <flux:icon.user class="size-3.5 shrink-0" />
                        <span class="truncate">{{ $tarea->usuario?->name ?? 'Sin asignar' }}</span>
                    </span>

                    <div class="flex shrink-0 items-center gap-1">
                @php
                    $user = auth()->user();
                    $esLimpieza = $user->hasRole('limpieza') || ($user->rol === 'limpieza' || $user->rol === 'Limpieza');
                @endphp
                @if(!$esLimpieza)
                <button
                    type="button"
                    wire:click="editar({{ $tarea->id }})"
                    title="Editar tarea"
                    aria-label="Editar tarea"
                    class="rounded-lg p-2 text-slate-400 transition-all duration-200 hover:bg-slate-100 hover:text-slate-700 focus:outline-none focus:ring-2 focus:ring-amber-500 active:scale-90 dark:hover:bg-zinc-700 dark:hover:text-zinc-100"
                >
                    <flux:icon.pencil-square class="size-4" />
                </button>
                <button
                    type="button"
                    x-on:click="$dispatch('eliminar-tarea', { id: {{ $tarea->id }}, numero: '{{ $numero }}' })"
                    title="Eliminar tarea"
                    aria-label="Eliminar tarea"
                    class="rounded-lg p-2 text-slate-400 transition-all duration-200 hover:bg-red-50 hover:text-red-600 focus:outline-none focus:ring-2 focus:ring-red-500 active:scale-90 dark:hover:bg-red-500/10 dark:hover:text-red-400"
                >
                    <flux:icon.trash class="size-4" />
                </button>
                @endif
                    </div>
                </div>

                {{-- Acción principal a todo el ancho inferior.
                     Hoy abre el formulario de la tarea; sustituye el `wire:click`
                     por `iniciarLimpieza({{ $tarea->id }})` / `marcarComoLista(...)`
                     en cuanto esos métodos existan en el componente. --}}
                @php
                    $user = auth()->user();
                    $esLimpieza = $user->hasRole('limpieza') || ($user->rol === 'limpieza' || $user->rol === 'Limpieza');
                @endphp
                <button
                    type="button"
                    @if(!$esLimpieza || $estado === 'Pendiente' || $estado === 'En Proceso')
                    wire:click="editar({{ $tarea->id }})"
                    @endif
                    @class([
                        'flex w-full items-center justify-center gap-2 px-5 py-3.5 text-sm font-semibold transition-colors duration-200 focus:outline-none focus:ring-2 focus:ring-inset',
                        $clasesAccion,
                    ])
                >
                    @if ($estado === 'Pendiente')
                        <flux:icon.arrow-path class="size-4" />
                    @elseif ($estado === 'En Proceso')
                        <flux:icon.check class="size-4" />
                    @else
                        <flux:icon.pencil-square class="size-4" />
                    @endif
                    {{ $etiquetaAccion }}
                </button>
            </article>
        @empty
            <div class="col-span-full flex min-h-64 flex-col items-center justify-center rounded-2xl border border-slate-100 bg-slate-50 px-6 py-16 text-center dark:border-zinc-700/70 dark:bg-slate-800/60">
                <span class="mb-4 flex size-14 items-center justify-center rounded-2xl bg-white text-slate-400 shadow-sm dark:bg-zinc-900 dark:text-zinc-500">
                    <flux:icon.building-office class="size-7" />
                </span>
                <p class="text-base font-semibold text-slate-700 dark:text-slate-200">
                    No hay tareas de limpieza registradas.
                </p>
                <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">
                    Crea una tarea para empezar a organizar el housekeeping.
                </p>
                @if(auth()->user()->hasRole('super-admin') || auth()->user()->hasRole('gerente') || auth()->user()->hasRole('recepcionista') || auth()->user()->rol === 'admin' || auth()->user()->rol === 'Administrador')
                <flux:button type="button" variant="primary" wire:click="crear" class="mt-5">
                    <flux:icon.plus class="size-4" />
                    Nueva tarea
                </flux:button>
                @endif
            </div>
        @endforelse
    </div>

    {{-- ======================================================
         DIÁLOGO DE CONFIRMACIÓN DE ELIMINACIÓN

         Vive en la raíz de la vista para no quedar recortado por el
         `overflow-hidden` de las tarjetas. El botón de la papelera solo
         publica la tarea con `$dispatch`; aquí se llama a `eliminar()` de
         Livewire únicamente cuando el usuario confirma. Se evita `wire:confirm`
         por ser la alerta nativa del navegador, sin estilo ni contexto.
         ====================================================== --}}
    <div
        x-show="pendiente !== null"
        x-cloak
        x-on:keydown.escape.window="pendiente = null"
        x-on:click.self="pendiente = null"
        class="fixed inset-0 z-[100] flex items-center justify-center bg-slate-900/60 p-4 backdrop-blur-sm"
        role="dialog"
        aria-modal="true"
        aria-labelledby="confirmar-eliminacion-titulo"
        x-transition:enter="transition ease-out duration-150"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
    >
        <div
            x-show="pendiente !== null"
            class="w-full max-w-md rounded-2xl border border-white/10 bg-white p-6 shadow-2xl dark:bg-slate-800"
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0 scale-95"
            x-transition:enter-end="opacity-100 scale-100"
            x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="opacity-100 scale-100"
            x-transition:leave-end="opacity-0 scale-95"
        >
            <div class="flex items-start gap-4">
                <span class="inline-flex size-10 shrink-0 items-center justify-center rounded-full bg-amber-50 text-amber-600 dark:bg-amber-500/15 dark:text-amber-400">
                    <flux:icon.exclamation-triangle class="size-5" />
                </span>

                <div class="min-w-0 flex-1">
                    <h3 id="confirmar-eliminacion-titulo" class="text-base font-semibold text-slate-900 dark:text-white">
                        Eliminar tarea
                    </h3>

                    <p class="mt-1 text-sm leading-relaxed text-slate-600 dark:text-slate-400">
                        ¿Estás seguro de eliminar esta tarea
                        <span
                            x-text="pendiente ? 'de la habitación #' + pendiente.numero : ''"
                            class="font-semibold text-slate-900 dark:text-white"
                        ></span>?
                        Esta acción es permanente y no se puede deshacer.
                    </p>
                </div>
            </div>

            <div class="mt-6 flex items-center justify-end gap-3">
                <button
                    type="button"
                    x-on:click="pendiente = null"
                    class="inline-flex items-center justify-center rounded-lg px-4 py-2 text-sm font-medium text-slate-500 transition-colors duration-200 hover:bg-slate-100 hover:text-slate-700 focus:outline-none focus:ring-2 focus:ring-slate-300 dark:hover:bg-zinc-700 dark:hover:text-zinc-200"
                >
                    Cancelar
                </button>

                <button
                    type="button"
                    x-on:click="confirmarEliminacion()"
                    class="inline-flex items-center justify-center gap-2 rounded-lg bg-red-600 px-4 py-2 text-sm font-medium text-white shadow-md transition-all duration-200 hover:bg-red-700 hover:shadow-lg focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 active:scale-95"
                >
                    <flux:icon.trash class="size-4" />
                    Sí, eliminar
                </button>
            </div>
        </div>
    </div>
</div>
