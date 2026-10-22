{{-- ======================================================
     CABECERA, CONTROLES Y FILTROS
     ====================================================== --}}

<div class="animate-fade-in">
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-2xl font-bold tracking-tight text-slate-900 dark:text-white">Habitaciones</h1>
            <p class="mt-1 text-sm font-medium text-slate-500 dark:text-slate-400">
                {{ $totalHabitaciones }} {{ $totalHabitaciones === 1 ? 'habitación registrada' : 'habitaciones registradas' }}
            </p>
        </div>

        <div class="flex items-center gap-3">
            <div class="flex items-center rounded-xl border border-slate-200 bg-white p-1 shadow-sm dark:border-zinc-700 dark:bg-slate-800">
                <button
                    type="button"
                    wire:click="$parent.cambiarVista('cuadricula')"
                    title="Vista cuadrícula"
                    aria-label="Vista cuadrícula"
                    class="rounded-lg p-2 transition-all duration-200 active:scale-90 focus:outline-none focus:ring-2 focus:ring-amber-500 {{ $vista === 'cuadricula' ? 'bg-slate-900 text-white shadow-sm dark:bg-white dark:text-slate-900' : 'text-slate-400 hover:bg-slate-100 hover:text-slate-700 dark:hover:bg-zinc-700 dark:hover:text-zinc-200' }}"
                >
                    <flux:icon.squares-2x2 class="size-4" />
                </button>
                <button
                    type="button"
                    wire:click="$parent.cambiarVista('lista')"
                    title="Vista lista"
                    aria-label="Vista lista"
                    class="rounded-lg p-2 transition-all duration-200 active:scale-90 focus:outline-none focus:ring-2 focus:ring-amber-500 {{ $vista === 'lista' ? 'bg-slate-900 text-white shadow-sm dark:bg-white dark:text-slate-900' : 'text-slate-400 hover:bg-slate-100 hover:text-slate-700 dark:hover:bg-zinc-700 dark:hover:text-zinc-200' }}"
                >
                    <flux:icon.list-bullet class="size-4" />
                </button>
            </div>

            {{-- Sin `wire:click`: el <dialog> se abre con `modal-show` en el
                 navegador y `abrir-formulario` carga los datos. Al no pasar
                 por el contenedor, ningún morph puede cerrar el modal. --}}
            <button
                type="button"
                x-data
                x-on:click="$dispatch('modal-show', { name: 'habitacion-form' }); $dispatch('abrir-formulario', { id: null })"
                class="inline-flex items-center gap-2 rounded-xl bg-amber-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition-all duration-300 ease-out hover:scale-[1.02] hover:bg-amber-700 hover:shadow-lg focus:outline-none focus:ring-2 focus:ring-amber-600 focus:ring-offset-2 active:scale-95 dark:bg-amber-600 dark:hover:bg-amber-700"
            >
                <flux:icon.plus class="size-4" />
                Agregar habitación
            </button>
        </div>
    </div>

    <div class="relative mt-6">
        <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-4 text-slate-400 dark:text-zinc-500">
            <flux:icon.magnifying-glass class="size-5" />
        </span>
        <input
            type="text"
            wire:model.live.debounce.400ms="search"
            placeholder="Buscar por número o tipo..."
            class="block w-full rounded-2xl border-0 bg-white py-3.5 pl-12 pr-4 text-sm text-slate-900 shadow-sm ring-1 ring-inset ring-slate-200 transition-all duration-300 ease-out placeholder:text-slate-400 focus:ring-2 focus:ring-inset focus:ring-amber-500 dark:bg-slate-800 dark:text-white dark:ring-zinc-700 dark:placeholder:text-zinc-500"
        />
    </div>

    <div class="mt-6">
        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4 mb-8">
            @foreach ($tarjetas as $tarjeta)
                <button
                    type="button"
                    wire:click="$parent.filtrarPor('{{ $tarjeta['clave'] }}')"
                    aria-pressed="{{ $tarjeta['activa'] ? 'true' : 'false' }}"
                    @class([
                        'w-full rounded-2xl border-t-4 bg-white p-4 text-left shadow-sm transition-all duration-200 ease-out hover:-translate-y-1 hover:shadow-md active:scale-95 focus:outline-none focus:ring-2 focus:ring-amber-500 dark:bg-slate-800',
                        $tarjeta['borde'],
                        'ring-2 ring-slate-900 ring-offset-2 dark:ring-white dark:ring-offset-slate-900' => $tarjeta['activa'],
                    ])
                >
                    <div class="flex justify-between items-start">
                        <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">{{ $tarjeta['etiqueta'] }}</span>
                        <div class="w-8 h-8 rounded-full flex items-center justify-center {{ $tarjeta['iconoFondo'] }} {{ $tarjeta['iconoTexto'] }}">
                            <flux:icon :name="$tarjeta['icono']" class="h-4 w-4" />
                        </div>
                    </div>
                    <div class="text-4xl font-black text-slate-800 mt-2 dark:text-slate-100">{{ $tarjeta['conteo'] }}</div>
                </button>
            @endforeach
        </div>
    </div>
</div>
