{{-- ======================================================
     MODAL: CREAR / EDITAR HABITACIÓN

     El <dialog> de Flux llega transparente (variant="bare"), así que
     aquí se construyen el cristal de fondo, la tarjeta blanca y
     todas las animaciones de entrada, campos y acciones.
     ====================================================== --}}

@php
    /*
     | Base compartida de los campos: sin bordes duros, fondo sutil que reacciona
     | al hover y anillo de enfoque en el color de la marca. El icono usa `peer`
     | para teñirse cuando el campo toma el foco.
     */
    $campoBase = 'w-full rounded-xl border-0 border-transparent bg-slate-50 py-2.5 text-sm text-slate-900 shadow-sm ring-1 ring-inset ring-slate-200 transition-all duration-200 ease-out placeholder:text-slate-400 hover:bg-slate-100 hover:ring-slate-300 focus:border-transparent focus:bg-white focus:ring-2 focus:ring-inset focus:ring-amber-500 dark:bg-zinc-900 dark:text-white dark:ring-zinc-700 dark:hover:bg-zinc-800 dark:hover:ring-zinc-600 dark:focus:bg-zinc-900 dark:focus:ring-amber-500';

    /*
     | Relleno izquierdo de los campos con icono: el icono mide 1.25rem y está
     | anclado a `left-3.5` (0.875rem), así que ocupa hasta 2.125rem. `pl-11`
     | (2.75rem) reserva ese hueco y deja el texto limpio, sin encima del icono.
     */
    $campo = $campoBase.' pl-11 pr-3.5';

    $icono = 'pointer-events-none absolute left-3.5 top-1/2 size-5 -translate-y-1/2 text-slate-400 transition-colors duration-200 peer-focus:text-amber-500 dark:text-slate-500 dark:peer-focus:text-amber-400';

    $etiqueta = 'mb-1.5 block text-xs font-semibold uppercase tracking-wide text-slate-500 transition-colors duration-200 dark:text-slate-400';

    /*
     | Marca visual de campo obligatorio. La llevan numero, tipo, piso y estado.
     */
    $obligatorio = '<span class="text-red-500 font-extrabold text-sm ml-0.5">*</span>';

    /*
     | Catálogos del desplegable. `variant="soft"` hace que el botón replique el
     | campo del sistema y el componente calcula su propio `pl-10`, ya que el
     | icono vive dentro del botón en lugar de ser un hermano del control.
     */
    $tiposOpciones = $this->tipos
        ->mapWithKeys(fn ($tipo) => [$tipo->id => $tipo->nombre.' — $'.number_format($tipo->precio_base, 2)])
        ->all();

    $estadosOpciones = array_combine(\App\Models\Habitacion::ESTADOS, \App\Models\Habitacion::ESTADOS);
@endphp

<form wire:submit="guardar" class="w-full">
    {{-- ---------------------------------------------------
         TARJETA: escala 95 -> 100 con rebote sutil
         --------------------------------------------------- --}}
    <div class="w-full animate-modal-card-in overflow-hidden rounded-2xl bg-white shadow-2xl ring-1 ring-slate-900/5 dark:bg-zinc-900 dark:ring-white/10">
        {{-- Encabezado --}}
        <div class="flex items-start gap-4 border-b border-slate-100 bg-gradient-to-r from-amber-50/80 to-white px-6 py-5 dark:border-zinc-800 dark:from-amber-950/40 dark:to-zinc-900">
            <span class="flex size-11 shrink-0 items-center justify-center rounded-xl bg-amber-600 text-white shadow-lg shadow-amber-600/25">
                <flux:icon.key class="size-5" />
            </span>

            <div class="min-w-0 flex-1">
                <h2 class="text-lg font-semibold tracking-tight text-slate-900 dark:text-white">
                    {{ $habitacionId ? 'Editar habitación' : 'Agregar habitación' }}
                </h2>
                <p class="mt-0.5 text-sm text-slate-500 dark:text-slate-400">
                    Completa los datos de la habitación. Los campos marcados con <span class="text-amber-600">*</span> son obligatorios.
                </p>
            </div>

            {{-- El cierre lo resuelve Flux en el navegador; no requiere viaje
                 al servidor. El formulario se prepara de nuevo al reabrirse. --}}
            <button
                type="button"
                x-data
                x-on:click="$dispatch('modal-close', { name: 'habitacion-form' })"
                aria-label="Cerrar"
                class="-me-2 -mt-1 shrink-0 rounded-lg p-2 text-slate-400 transition-all duration-200 hover:rotate-90 hover:bg-slate-100 hover:text-slate-700 focus:outline-none focus:ring-2 focus:ring-amber-500 dark:hover:bg-zinc-800 dark:hover:text-white"
            >
                <flux:icon.x-mark class="size-5" />
            </button>
        </div>

        {{-- Campos. `wire:loading` atenúa el formulario mientras llega la
             preparación o el guardado, para que nunca se edite a ciegas. --}}
        <div
            wire:loading.class="opacity-60"
            wire:target="preparar,guardar"
            class="max-h-[65vh] space-y-5 overflow-y-auto px-6 py-6 transition-opacity duration-200"
        >
            <div class="grid gap-5 sm:grid-cols-2">
                <div>
                    <label for="numero_habitacion" class="{{ $etiqueta }}">Número {!! $obligatorio !!}</label>
                    <div class="relative">
                        <input
                            id="numero_habitacion"
                            type="text"
                            wire:model="numero_habitacion"
                            placeholder="101"
                            required
                            class="{{ $campo }} peer"
                        />
                        <flux:icon.hashtag class="{{ $icono }}" />
                    </div>
                    <flux:error name="numero_habitacion" class="mt-1.5" />
                </div>

                <div>
                    <label for="tipo_habitacion_id" class="{{ $etiqueta }}">Tipo {!! $obligatorio !!}</label>
                    <x-dropdown
                        id="tipo_habitacion_id"
                        wire:model="tipo_habitacion_id"
                        modelo="tipo_habitacion_id"
                        clave="tipo-habitacion-{{ $tipo_habitacion_id }}"
                        variant="soft"
                        required
                        :selected="$tipo_habitacion_id"
                        placeholder="Selecciona un tipo..."
                        :options="$tiposOpciones"
                        class="w-full"
                    >
                        <x-slot:leadingIcon>
                            <flux:icon.home-modern class="size-5" />
                        </x-slot:leadingIcon>
                    </x-dropdown>
                    <flux:error name="tipo_habitacion_id" class="mt-1.5" />
                </div>

                <div>
                    <label for="piso" class="{{ $etiqueta }}">Piso {!! $obligatorio !!}</label>
                    <div class="relative">
                        <input
                            id="piso"
                            type="number"
                            min="1"
                            wire:model="piso"
                            required
                            class="{{ $campo }} peer"
                        />
                        <flux:icon.building-office-2 class="{{ $icono }}" />
                    </div>
                    <flux:error name="piso" class="mt-1.5" />
                </div>

                <div>
                    <label for="estado" class="{{ $etiqueta }}">Estado inicial {!! $obligatorio !!}</label>
                    <x-dropdown
                        id="estado"
                        wire:model="estado"
                        variant="soft"
                        :selected="$estado"
                        :options="$estadosOpciones"
                        class="w-full"
                    >
                        <x-slot:leadingIcon>
                            <flux:icon.signal class="size-5" />
                        </x-slot:leadingIcon>
                    </x-dropdown>
                    <flux:error name="estado" class="mt-1.5" />
                </div>

                <div class="sm:col-span-2">
                    <label for="descripcion" class="{{ $etiqueta }}">Descripción</label>
                    <div class="relative">
                        <textarea
                            id="descripcion"
                            wire:model="descripcion"
                            rows="3"
                            placeholder="Habitación clásica con vistas al mar..."
                            class="{{ $campo }} peer resize-none"
                        ></textarea>
                        <flux:icon.document-text class="{{ $icono }} top-3.5 -translate-y-0" />
                    </div>
                    <flux:error name="descripcion" class="mt-1.5" />
                </div>
            </div>

            {{-- ---------------------------------------------------
                 FOTOGRAFÍA: zona arrastrable, el elemento más interactivo
                 --------------------------------------------------- --}}
            <div>
                <span class="{{ $etiqueta }}">Fotografía</span>

                <div
                    x-data="{
                        arrastrando: false,
                        soltar(archivos) {
                            if (! archivos || archivos.length === 0) return
                            const transferencia = new DataTransfer()
                            transferencia.items.add(archivos[0])
                            this.$refs.fotoInput.files = transferencia.files
                            this.$refs.fotoInput.dispatchEvent(new Event('change', { bubbles: true }))
                        },
                    }"
                    @dragover.prevent="arrastrando = true"
                    @dragleave.prevent="arrastrando = false"
                    @drop.prevent="arrastrando = false; soltar($event.dataTransfer.files)"
                    class="group relative overflow-hidden rounded-2xl transition-colors duration-300 ease-out"
                    :class="arrastrando
                        ? 'border-2 border-amber-500 bg-amber-50 dark:bg-amber-950/40'
                        : 'border-2 border-dashed border-slate-300 bg-slate-50 hover:border-amber-500 hover:bg-amber-50 dark:border-zinc-700 dark:bg-zinc-900/60 dark:hover:border-amber-500 dark:hover:bg-amber-950/30'"
                >
                    <input
                        x-ref="fotoInput"
                        type="file"
                        wire:model="foto"
                        accept="{{ implode(',', \App\Livewire\Habitaciones\FormModal::FORMATOS_FOTO) }}"
                        class="sr-only"
                        id="foto-habitacion"
                    />

                    @if ($foto)
                        <div wire:loading.remove wire:target="foto" class="animate-fade-in">
                            <div class="relative">
                                @if ($foto->isPreviewable())
                                    <img
                                        src="{{ $foto->temporaryUrl() }}"
                                        alt="Vista previa de la fotografía"
                                        class="h-56 w-full object-cover"
                                    />
                                @else
                                    <div class="flex h-56 w-full items-center justify-center">
                                        <flux:icon.document class="size-7 text-slate-400" />
                                    </div>
                                @endif

                                <label
                                    for="foto-habitacion"
                                    class="absolute inset-0 flex cursor-pointer items-center justify-center bg-slate-900/55 opacity-0 backdrop-blur-[2px] transition-opacity duration-300 group-hover:opacity-100"
                                >
                                    <span class="flex items-center gap-2 rounded-xl bg-white/95 px-4 py-2 text-sm font-semibold text-slate-900 shadow-lg">
                                        <flux:icon.arrow-path class="size-4 text-amber-600" />
                                        Cambiar fotografía
                                    </span>
                                </label>
                            </div>

                            <div class="flex items-center justify-between gap-3 px-4 py-3">
                                <p class="flex min-w-0 items-center gap-2 text-xs text-slate-600 dark:text-slate-400">
                                    <flux:icon.check-circle class="size-4 shrink-0 text-emerald-500" />
                                    <span class="truncate">{{ $foto->getClientOriginalName() }} · se guardará como WebP</span>
                                </p>
                            </div>
                        </div>
                    @elseif ($this->urlFotoGuardada)
                        <div class="animate-fade-in">
                            <div class="relative">
                                <img
                                    src="{{ $this->urlFotoGuardada }}"
                                    alt="Fotografía actual de la habitación"
                                    class="h-56 w-full object-cover"
                                />
                                <label
                                    for="foto-habitacion"
                                    class="absolute inset-0 flex cursor-pointer items-center justify-center bg-slate-900/55 opacity-0 backdrop-blur-[2px] transition-opacity duration-300 group-hover:opacity-100"
                                >
                                    <span class="flex items-center gap-2 rounded-xl bg-white/95 px-4 py-2 text-sm font-semibold text-slate-900 shadow-lg">
                                        <flux:icon.arrow-path class="size-4 text-amber-600" />
                                        Reemplazar fotografía
                                    </span>
                                </label>
                            </div>

                            <div class="flex items-center justify-between gap-3 px-4 py-3">
                                <p class="flex min-w-0 items-center gap-2 text-xs text-slate-600 dark:text-slate-400">
                                    <flux:icon.photo class="size-4 shrink-0 text-amber-600" />
                                    <span class="truncate">Fotografía actual del inventario.</span>
                                </p>
                            </div>
                        </div>
                    @else
                        <label
                            for="foto-habitacion"
                            class="flex cursor-pointer flex-col items-center justify-center gap-3 px-6 py-12 text-center"
                        >
                            <span class="flex size-16 items-center justify-center rounded-2xl bg-white text-amber-600 shadow-sm ring-1 ring-slate-200 transition-transform duration-300 ease-out group-hover:scale-110 group-hover:-rotate-3 dark:bg-zinc-800 dark:ring-zinc-700">
                                <flux:icon.photo class="size-7 animate-float-slow" />
                            </span>
                            <span class="text-sm font-semibold text-slate-700 dark:text-slate-200">
                                Arrastra la fotografía aquí o haz clic para elegirla
                            </span>
                            <span class="text-xs text-slate-500 dark:text-slate-400">
                                JPG, PNG o WebP, hasta 5 MB. Se convierte a WebP automáticamente.
                            </span>
                            <span class="mt-1 inline-flex items-center gap-1.5 rounded-full bg-amber-100 px-3 py-1 text-[11px] font-semibold text-amber-800 dark:bg-amber-950 dark:text-amber-300">
                                <flux:icon.sparkles class="size-3.5" />
                                Optimización automática
                            </span>
                        </label>
                    @endif

                    <div
                        wire:loading
                        wire:target="foto"
                        class="flex h-56 items-center justify-center gap-2 text-sm text-slate-500 dark:text-slate-400"
                    >
                        <flux:icon.arrow-path class="size-4 animate-spin" />
                        Subiendo fotografía...
                    </div>
                </div>

                <flux:error name="foto" class="mt-1.5" />
                <p class="mt-1.5 text-xs text-slate-500 dark:text-slate-400">
                    Capacidad, precio y descripción se toman del tipo de habitación seleccionado.
                </p>
            </div>
        </div>

        {{-- Acciones --}}
        <div class="flex items-center justify-end gap-3 border-t border-slate-100 bg-slate-50/70 px-6 py-4 dark:border-zinc-800 dark:bg-zinc-900/60">
            <button
                type="button"
                x-data
                x-on:click="$dispatch('modal-close', { name: 'habitacion-form' })"
                class="inline-flex items-center justify-center rounded-xl px-5 py-2.5 text-sm font-semibold text-slate-600 transition-all duration-200 hover:bg-slate-100 hover:text-slate-900 focus:outline-none focus:ring-2 focus:ring-amber-500 active:scale-95 dark:text-slate-300 dark:hover:bg-zinc-800 dark:hover:text-white"
            >
                Cancelar
            </button>

            {{-- `wire:loading.attr="disabled"` bloquea el doble clic mientras
                 el servidor procesa el guardado. --}}
            <button
                type="submit"
                wire:loading.attr="disabled"
                wire:target="guardar"
                class="inline-flex items-center justify-center gap-2 rounded-xl bg-amber-600 px-5 py-2.5 text-sm font-semibold text-white shadow-md shadow-amber-600/25 transition-all duration-200 ease-out hover:-translate-y-0.5 hover:bg-amber-700 hover:shadow-lg focus:outline-none focus:ring-2 focus:ring-amber-600 focus:ring-offset-2 active:translate-y-0 active:scale-95 disabled:pointer-events-none disabled:opacity-70 disabled:shadow-none disabled:hover:translate-y-0 disabled:hover:bg-amber-600 disabled:hover:shadow-md"
            >
                <span wire:loading.remove wire:target="guardar">
                    <flux:icon.check class="size-4" />
                </span>
                <span wire:loading wire:target="guardar" class="flex items-center gap-2">
                    <flux:icon.arrow-path class="size-4 animate-spin" />
                    Guardando...
                </span>
                <span wire:loading.remove wire:target="guardar">Guardar habitación</span>
            </button>
        </div>
    </div>
</form>
