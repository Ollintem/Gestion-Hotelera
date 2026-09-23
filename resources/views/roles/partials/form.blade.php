{{-- ======================================================
     FORMULARIO COMPARTIDO: CREAR / EDITAR ROL
     ====================================================== --}}

<div x-data="{ salvando: false }" @submit.prevent="salvando = true">
    <div
        class="w-full max-w-md rounded-2xl bg-white p-6 shadow-2xl ring-1 ring-slate-200 dark:bg-zinc-900 dark:ring-zinc-700"
        x-transition:enter="transition-all duration-300 ease-out"
        x-transition:enter-start="opacity-0 -translate-y-4 scale-95"
        x-transition:enter-end="opacity-100 translate-y-0 scale-100"
    >
        <form wire:submit="{{ $rolIdEditar ? 'actualizarRol' : 'guardarRol' }}">
            <div class="mb-6 flex items-start justify-between gap-4">
                <div class="flex items-center gap-3">
                    <span class="flex size-10 shrink-0 items-center justify-center rounded-xl bg-amber-500/15">
                        @if ($rolIdEditar)
                            <flux:icon.pencil-square class="size-5 text-amber-600 dark:text-amber-400" />
                        @else
                            <flux:icon.user-plus class="size-5 text-amber-600 dark:text-amber-400" />
                        @endif
                    </span>
                    <flux:heading size="lg" class="!text-slate-900 !font-bold dark:!text-white">
                        {{ $rolIdEditar ? 'Editar rol' : 'Crear nuevo puesto' }}
                    </flux:heading>
                </div>
            </div>

            <div class="space-y-2">
                <label for="nombre-rol" class="block text-sm font-semibold text-slate-700 dark:text-zinc-200">
                    Nombre del puesto
                </label>
                <input
                    id="nombre-rol"
                    type="text"
                    wire:model="nombre"
                    placeholder="Ejemplo: recepción, mantenimiento..."
                    required
                    class="block w-full rounded-lg border-0 bg-white px-3.5 py-2.5 text-slate-900 shadow-sm ring-1 ring-inset ring-slate-300 transition-all duration-300 ease-out placeholder:text-slate-400 focus:ring-2 focus:ring-inset focus:ring-amber-500 dark:bg-zinc-900 dark:text-white dark:ring-zinc-700"
                />
                @error('nombre')
                    <p class="text-sm font-medium text-red-600 dark:text-red-400">{{ $message }}</p>
                @enderror
            </div>

            <div class="mt-8 flex items-center justify-end gap-3">
                <button
                    type="button"
                    wire:click="{{ $rolIdEditar ? 'cerrarModalEditar' : 'cerrarModalCrear' }}"
                    class="inline-flex items-center justify-center rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-semibold text-slate-700 shadow-sm transition-all duration-300 ease-out hover:scale-[1.02] hover:bg-slate-50 hover:shadow focus:outline-none focus:ring-2 focus:ring-slate-400 focus:ring-offset-2 active:scale-95 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-200 dark:hover:bg-zinc-700"
                >
                    Cancelar
                </button>

                <button
                    type="submit"
                    class="inline-flex items-center justify-center gap-2 rounded-lg bg-amber-500 px-4 py-2 text-sm font-semibold text-white shadow-sm transition-all duration-300 ease-out hover:scale-[1.02] hover:bg-amber-600 hover:shadow-lg focus:outline-none focus:ring-2 focus:ring-amber-500 focus:ring-offset-2 active:scale-95"
                >
                    <span wire:loading.remove wire:target="{{ $rolIdEditar ? 'actualizarRol' : 'guardarRol' }}">
                        <flux:icon.check class="size-4" />
                    </span>
                    <span wire:loading wire:target="{{ $rolIdEditar ? 'actualizarRol' : 'guardarRol' }}">Guardando...</span>
                    <span wire:loading.remove wire:target="{{ $rolIdEditar ? 'actualizarRol' : 'guardarRol' }}">
                        {{ $rolIdEditar ? 'Guardar cambio' : 'Guardar puesto' }}
                    </span>
                </button>
            </div>
        </form>
    </div>
</div>