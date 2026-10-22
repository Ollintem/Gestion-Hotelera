{{-- ======================================================
     CAMPOS DEL FORMULARIO: ALTA Y EDICIÓN

     Es el mismo cuerpo para los dos casos: lo que se ve aquí es lo que se valida
     al guardar.

     El precio efectivo se recalcula en el servidor en cada tecla y en cada
     movimiento del deslizador (`wire:model.live`), de modo que el recuadro
     refleja lo que hay escrito y no lo que se guardó hace un rato. `wire:key`
     sobre el importe es lo que dispara la animación: al cambiar la clave,
     Morphdom sustituye el nodo y el número vuelve a reproducirse.

     Los identificadores llevan `$prefijoIds` porque las dos ventanas están
     montadas a la vez en el DOM y comparten formulario: sin ese prefijo se
     repetirían los `id` y el `for` de cada etiqueta apuntaría al input de la
     ventana oculta.
     ====================================================== --}}

@php
    /*
     | Los dos modales comparten este cuerpo y conviven en el DOM, así que cada
     | campo necesita un identificador propio. Sin el prefijo se repetirían los
     | `id` y el `for` de cada etiqueta apuntaría al input de la ventana oculta.
     */
    $prefijoIds = $prefijoIds ?? '';

    /*
     | Base compartida de los campos: fondo blanco, borde suave y anillo de
     | enfoque en el color de la marca. El icono usa `peer` para teñirse cuando
     | el campo toma el foco.
     */
    $campoBase = 'w-full rounded-lg border border-gray-300 bg-white px-3.5 py-2.5 text-sm text-gray-900 shadow-sm transition-colors duration-200 placeholder:text-gray-400 focus:border-amber-500 focus:outline-none focus:ring-2 focus:ring-amber-500/30';

    $campo = $campoBase.' pl-11 pr-3.5';

    $icono = 'pointer-events-none absolute left-3.5 top-1/2 size-5 -translate-y-1/2 text-gray-400 transition-colors duration-200 peer-focus:text-amber-500';

    $etiqueta = 'mb-1.5 block text-xs font-semibold uppercase tracking-wider text-gray-500 transition-colors duration-200';

    $recargo = $variacionPrecio > 0;
    $descuento = $variacionPrecio < 0;
@endphp

<div class="space-y-6">

    {{-- ==================================================
         NOMBRE DE LA TEMPORADA
         ================================================== --}}

    <div>
        <label for="{{ $prefijoIds }}nombre" class="{{ $etiqueta }}">Nombre de la temporada</label>

        <div class="relative">
            <input
                id="{{ $prefijoIds }}nombre"
                type="text"
                wire:model="nombre"
                placeholder="Ejemplo: Semana Santa"
                required
                maxlength="100"
                class="{{ $campo }} peer"
            />

            <flux:icon.tag class="{{ $icono }}" />
        </div>

        <flux:error name="nombre" class="mt-1.5" />
    </div>

    {{-- ==================================================
         RANGO DE FECHAS

         La fecha de fin declara como mínimo la de inicio: el navegador impide
         elegir un rango al revés, y la validación lo comprueba igual porque el
         atributo `min` no es una garantía.
         ================================================== --}}

    <div class="grid gap-5 sm:grid-cols-2">
        <div>
            <label for="{{ $prefijoIds }}fecha_inicio" class="{{ $etiqueta }}">Fecha de inicio</label>

            <div class="relative">
                <input
                    id="{{ $prefijoIds }}fecha_inicio"
                    type="date"
                    wire:model="fecha_inicio"
                    required
                    class="{{ $campo }} peer"
                />

                <flux:icon.calendar-days class="{{ $icono }}" />
            </div>

            <flux:error name="fecha_inicio" class="mt-1.5" />
        </div>

        <div>
            <label for="{{ $prefijoIds }}fecha_fin" class="{{ $etiqueta }}">Fecha de fin</label>

            <div class="relative">
                <input
                    id="{{ $prefijoIds }}fecha_fin"
                    type="date"
                    wire:model="fecha_fin"
                    required
                    min="{{ $fecha_inicio !== '' ? $fecha_inicio : null }}"
                    class="{{ $campo }} peer"
                />

                <flux:icon.calendar-days class="{{ $icono }}" />
            </div>

            <flux:error name="fecha_fin" class="mt-1.5" />
        </div>
    </div>

    {{-- ==================================================
         MULTIPLICADOR DE PRECIO

         Deslizador y no campo de texto: el rango 0.5×–2.0× es corto y una
         escala lo recorre con un gesto. El color de la pista es el de la marca y
         el valor viaja en vivo, así que el precio efectivo del pie se mueve con
         el dedo.
         ================================================== --}}

    <div>
        <div class="flex items-baseline justify-between gap-4">
            <label for="{{ $prefijoIds }}multiplicador_precio" class="{{ $etiqueta }} mb-0">
                Multiplicador de precio
            </label>

            <span class="inline-flex items-center gap-2">
                <span class="text-lg font-bold text-gray-900 tabular-nums">
                    {{ $multiplicadorEnTexto }}
                </span>

                <span @class([
                    'inline-flex items-center rounded-full px-2.5 py-1 text-[11px] font-bold',
                    'bg-rose-100 text-rose-700' => $recargo,
                    'bg-emerald-100 text-emerald-700' => $descuento,
                    'bg-slate-100 text-slate-600' => ! $recargo && ! $descuento,
                ])>
                    {{ $variacionPrecio > 0 ? '+' : '' }}{{ number_format($variacionPrecio, 0) }}%
                </span>
            </span>
        </div>

        <input
            id="{{ $prefijoIds }}multiplicador_precio"
            type="range"
            min="{{ $multiplicadorMinimo }}"
            max="{{ $multiplicadorMaximo }}"
            step="0.05"
            wire:model.live="multiplicador_precio"
            class="mt-1 h-2 w-full cursor-pointer rounded-full accent-amber-500 focus:outline-none focus-visible:ring-2 focus-visible:ring-amber-500/40"
        />

        <div class="mt-1.5 flex items-center justify-between text-xs font-medium text-gray-500">
            <span>{{ number_format($multiplicadorMinimo, 1) }}× (50% descuento)</span>
            <span>1.0× (precio base)</span>
            <span>{{ number_format($multiplicadorMaximo, 1) }}× (doble precio)</span>
        </div>

        <flux:error name="multiplicador_precio" class="mt-1.5" />
    </div>

    {{-- ==================================================
         PRECIO BASE POR NOCHE
         ================================================== --}}

    <div>
        <label for="{{ $prefijoIds }}precio_base" class="{{ $etiqueta }}">Precio base por noche ($)</label>

        <div class="relative">
            <input
                id="{{ $prefijoIds }}precio_base"
                type="number"
                step="0.01"
                min="0.01"
                wire:model.live="precio_base"
                placeholder="0.00"
                required
                class="{{ $campoBase }} pl-11 pr-3.5 peer"
            />

            <flux:icon.banknotes class="{{ $icono }}" />
        </div>

        <flux:error name="precio_base" class="mt-1.5" />
    </div>

    {{-- ==================================================
         PRECIO EFECTIVO (TIEMPO REAL)

         Es la cuenta que se cobra de verdad: la base por el multiplicador. Vive
         al final porque es el resultado, no un dato más que rellenar, y se pinta
         en dorado para que recepción la lea como el número que importa.
         ================================================== --}}

    <div class="flex items-start justify-between gap-4 rounded-2xl border border-amber-200 bg-gradient-to-br from-amber-50 to-white p-5">
        <div class="min-w-0">
            <p class="text-xs font-semibold uppercase tracking-wider text-amber-800">
                Precio efectivo por noche
            </p>

            <span
                wire:key="precio-efectivo-{{ $precioEfectivo }}"
                class="animate-fade-in mt-1 block text-3xl font-bold text-amber-700 tabular-nums"
            >
                {{ $precioEfectivoEnPesos }}
            </span>

            <p class="mt-1 text-xs text-gray-500 tabular-nums">
                ${{ number_format((float) $precio_base, 2) }} × {{ $multiplicadorEnTexto }}
            </p>
        </div>

        <span @class([
            'inline-flex shrink-0 items-center rounded-full px-3 py-1 text-xs font-bold',
            'bg-rose-100 text-rose-700' => $recargo,
            'bg-emerald-100 text-emerald-700' => $descuento,
            'bg-slate-100 text-slate-600' => ! $recargo && ! $descuento,
        ])>
            @if ($recargo)
                Recargo
            @elseif ($descuento)
                Descuento
            @else
                Precio base
            @endif
        </span>
    </div>
</div>