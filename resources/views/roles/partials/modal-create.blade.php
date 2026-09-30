{{-- ======================================================
     MODAL: CREAR ROL
     ====================================================== --}}

<div x-data="{ abierto: @entangle('mostrarModalCrear') }" @keydown.escape.window="abierto = false">
    <div
        x-cloak
        x-show="abierto"
        aria-modal="true"
        role="dialog"
        class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/60 p-4 backdrop-blur-sm"
        x-transition:enter="transition-all duration-300 ease-out"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition-all duration-200 ease-in"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        @click.self="abierto = false"
    >
        <div
            class="w-full max-w-md rounded-2xl bg-white p-6 shadow-2xl ring-1 ring-slate-200 dark:bg-zinc-900 dark:ring-zinc-700"
            x-transition:enter="transition-all duration-300 ease-out"
            x-transition:enter-start="opacity-0 -translate-y-4 scale-95"
            x-transition:enter-end="opacity-100 translate-y-0 scale-100"
            x-transition:leave="transition-all duration-200 ease-in"
            x-transition:leave-start="opacity-100 translate-y-0 scale-100"
            x-transition:leave-end="opacity-0 -translate-y-4 scale-95"
        >
            <form wire:submit="guardarRol">
                <div class="mb-6 flex items-start justify-between gap-4">
                    <div class="flex items-center gap-3">
                        <span class="flex size-10 shrink-0 items-center justify-center rounded-xl bg-amber-500/15">
                            <flux:icon.user-plus class="size-5 text-amber-600 dark:text-amber-400" />
                        </span>
                        <flux:heading size="lg" class="!text-slate-900 !font-bold dark:!text-white">Crear nuevo puesto</flux:heading>
                    </div>

                    <button
                        type="button"
                        aria-label="Cerrar"
                        @click="abierto = false"
                        class="shrink-0 rounded-lg p-1.5 text-slate-400 transition-all duration-300 ease-out hover:bg-slate-100 hover:text-slate-600 focus:outline-none focus:ring-2 focus:ring-amber-500 active:scale-90 dark:hover:bg-zinc-800 dark:hover:text-zinc-200"
                    >
                        <flux:icon.x-mark class="size-5" />
                    </button>
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
                        @click="abierto = false"
                        class="inline-flex items-center justify-center rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-semibold text-slate-700 shadow-sm transition-all duration-300 ease-out hover:bg-slate-50 hover:shadow hover:scale-[1.02] focus:outline-none focus:ring-2 focus:ring-slate-400 focus:ring-offset-2 active:scale-95 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-200 dark:hover:bg-zinc-700"
                    >
                        Cancelar
                    </button>

                    <button
                        type="submit"
                        class="inline-flex items-center justify-center gap-2 rounded-lg bg-amber-500 px-4 py-2 text-sm font-semibold text-white shadow-sm transition-all duration-300 ease-out hover:bg-amber-600 hover:shadow-lg hover:scale-[1.02] focus:outline-none focus:ring-2 focus:ring-amber-500 focus:ring-offset-2 active:scale-95"
                    >
                        <span wire:loading.remove wire:target="guardarRol">
                            <flux:icon.check class="size-4" />
                        </span>
                        <span wire:loading wire:target="guardarRol">Guardando...</span>
                        <span wire:loading.remove wire:target="guardarRol">Guardar puesto</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>