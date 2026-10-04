{{-- ======================================================
     MODAL: CREAR / EDITAR SERVICIO

     El nombre es texto libre: recepción escribe el concepto tal y como lo
     nombra en el mostrador, sin tener que encajarlo en una lista cerrada. La
     categoría, en cambio, se elige con el desplegable de `x-dropdown`, no con un
     <select> nativo. El componente lleva la clave `modelo`, que es lo que
     empuja el valor a la propiedad de Livewire con `$wire.set` en el mismo clic
     que lo elige: así el guardado no puede adelantarse a la petición del
     selector y enviar la propiedad vacía. La `clave` ata el bloque a un
     `wire:key` para que Morphdom reconstruya el control cuando el servidor
     cambia el valor y el botón muestre lo que devolvió.
     ====================================================== --}}

@php
    /*
     | Base compartida de los campos: fondo blanco, borde suave y anillo de
     | enfoque en el color de la marca. El icono usa `peer` para teñirse cuando
     | el campo toma el foco.
     */
    $campoBase = 'w-full rounded-lg border border-gray-300 bg-white px-3.5 py-2.5 text-sm text-gray-900 shadow-sm transition-colors duration-200 placeholder:text-gray-400 focus:border-amber-500 focus:outline-none focus:ring-2 focus:ring-amber-500';

    /*
     | Relleno izquierdo de los campos con icono: el icono mide 1.25rem y está
     | anclado a `left-3.5` (0.875rem), así que ocupa hasta 2.125rem. `pl-11`
     * (2.75rem) reserva ese hueco y deja el texto limpio.
     */
    $campo = $campoBase.' pl-11 pr-3.5';

    $icono = 'pointer-events-none absolute left-3.5 top-1/2 size-5 -translate-y-1/2 text-gray-400 transition-colors duration-200 peer-focus:text-amber-500';

    $etiqueta = 'mb-1.5 block text-xs font-semibold uppercase tracking-wider text-gray-500 transition-colors duration-200';

    /*
     | El desplegable toma el valor de la clave de cada opción, no de su
     | posición: los índices del bucle serían 0, 1, 2… y el backend recibiría un
     | id que no existe.
     */
    $categoriasOpciones = $categoriasModal->mapWithKeys(fn ($categoria) => [$categoria->id => $categoria->nombre])->all();
@endphp

<flux:modal
    name="servicio-form"
    wire:model="mostrarModal"
    class="w-full max-w-md rounded-2xl shadow-2xl"
>
    <form wire:submit="guardar">

        <div class="mb-6">
            <flux:heading size="lg" class="!font-semibold !text-gray-900">
                {{ $servicioId ? 'Editar servicio' : 'Nuevo servicio' }}
            </flux:heading>

            <flux:subheading class="!font-medium !text-gray-500">
                Completa los datos del servicio.
            </flux:subheading>
        </div>

        <div class="space-y-5">

            {{-- ==================================================
                 NOMBRE
                 ================================================== --}}

            <div>
                <label for="nombre" class="{{ $etiqueta }}">Nombre</label>

                <div class="relative">
                    <input
                        id="nombre"
                        type="text"
                        wire:model="nombre"
                        placeholder="Ejemplo: Desayuno buffet"
                        required
                        maxlength="100"
                        class="{{ $campo }} peer"
                    />

                    <flux:icon.tag class="{{ $icono }}" />
                </div>

                <flux:error name="nombre" class="mt-1.5" />
            </div>

            {{-- ==================================================
                 CATEGORÍA
                 ================================================== --}}

            <div>
                <label for="categoria_id" class="{{ $etiqueta }}">Categoría</label>

                <x-dropdown
                    id="categoria_id"
                    wire:model="categoria_id"
                    modelo="categoria_id"
                    clave="categoria_id-{{ $categoria_id }}"
                    required
                    :selected="$categoria_id"
                    placeholder="Selecciona una categoría..."
                    :options="$categoriasOpciones"
                    variant="soft"
                    class="w-full"
                >
                    <x-slot:leadingIcon>
                        <flux:icon.clipboard-document-list class="size-5" />
                    </x-slot:leadingIcon>
                </x-dropdown>

                <flux:error name="categoria_id" class="mt-1.5" />
            </div>

            {{-- ==================================================
                 PRECIO
                 ================================================== --}}

            <div>
                <label for="precio" class="{{ $etiqueta }}">Precio</label>

                <div class="relative">
                    <input
                        id="precio"
                        type="number"
                        step="0.01"
                        min="0"
                        wire:model="precio"
                        placeholder="0.00"
                        required
                        class="{{ $campo }} peer"
                    />

                    <flux:icon.banknotes class="{{ $icono }}" />
                </div>

                <flux:error name="precio" class="mt-1.5" />
            </div>

            {{-- ==================================================
                 DESCRIPCIÓN
                 ================================================== --}}

            <div>
                <label for="descripcion" class="{{ $etiqueta }}">Descripción</label>

                <textarea
                    id="descripcion"
                    wire:model="descripcion"
                    rows="3"
                    placeholder="Ejemplo: Incluye café, jugo y panadería caliente."
                    class="{{ $campoBase }} resize-none"
                ></textarea>

                <flux:error name="descripcion" class="mt-1.5" />
            </div>
        </div>

        <div class="mt-8 flex items-center justify-end gap-3">
            <button
                type="button"
                wire:click="cerrarModal"
                class="inline-flex items-center justify-center rounded-lg px-4 py-2 text-gray-500 transition-colors duration-200 hover:bg-gray-100 hover:text-gray-700 focus:outline-none focus:ring-2 focus:ring-gray-300"
            >
                Cancelar
            </button>

            <button
                type="submit"
                class="inline-flex items-center justify-center gap-2 rounded-lg bg-amber-600 px-4 py-2 font-medium text-white shadow-md transition-all duration-200 hover:bg-amber-700 hover:shadow-lg focus:outline-none focus:ring-2 focus:ring-amber-500 focus:ring-offset-2 active:scale-95"
            >
                <span wire:loading.remove wire:target="guardar" class="inline-flex items-center gap-2">
                    <flux:icon.check class="size-4" />
                    Guardar
                </span>

                <span wire:loading wire:target="guardar">
                    Guardando...
                </span>
            </button>
        </div>
    </form>
</flux:modal>
