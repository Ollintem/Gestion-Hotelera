@props([
    'selected' => '',
    'placeholder' => null,
    'options' => [],
    'variant' => 'solid',
    'clave' => null,
    'modelo' => null,
])

@php
    /*
     | Normaliza cualquier forma de opciones a pares {value, label}: mapas
     | indice => etiqueta, listas de objetos o de arrays con las mismas claves
     | que usan los selects que este componente reemplaza.
     |
     | El índice del bucle se llama `$indice` y no `$clave`: cualquier variable
     | que el bloque reutilice sobreescribe la propiedad del componente que
     | comparta nombre, y `$clave` es la que decide la clave del control.
     */
    $opciones = [];

    foreach ($options as $indice => $opcion) {
        if (is_array($opcion)) {
            $valor = $opcion['value'] ?? $opcion['clave'] ?? $indice;
            $etiqueta = $opcion['label'] ?? $opcion['etiqueta'] ?? $valor;
        } elseif (is_object($opcion)) {
            $valor = $opcion->value ?? $opcion->id ?? $opcion->clave ?? $indice;
            $etiqueta = $opcion->label ?? $opcion->etiqueta ?? $opcion->nombre ?? $valor;
        } else {
            $valor = $indice;
            $etiqueta = (string) $opcion;
        }

        $opciones[] = [
            'value' => (string) $valor,
            'label' => (string) $etiqueta,
        ];
    }

    /*
     | `solid` conserva el panel blanco de los módulos que ya lo usaban. `soft`
     | replica el campo del sistema (fondo sutil que reacciona al hover y anillo
     | ámbar al enfocar) para que el desplegable conviva con los inputs y
     | textareas de un mismo formulario sin parecer un control ajeno.
     */
    $varianteClase = match ($variant) {
        'soft' => 'bg-slate-50 ring-slate-200 hover:bg-slate-100 hover:ring-slate-300 focus:bg-white dark:bg-zinc-900 dark:ring-zinc-700 dark:hover:bg-zinc-800 dark:hover:ring-zinc-600 dark:focus:bg-zinc-900',
        default => 'bg-white ring-slate-300 hover:ring-slate-400 dark:bg-zinc-900 dark:ring-zinc-700 dark:hover:ring-zinc-600',
    };

    /*
     | El icono vive en el interior del botón, así que el texto debe empezar
     | después de él: `pl-10` deja aire suficiente para un icono de 1.25rem
     | anclado a `left-3.5`.
     */
    $iconoEspacio = isset($leadingIcon) ? 'pl-10' : 'pl-3.5';

    /*
     | El control visible es el botón: `id` y `aria-label` viajan ahí para que el
     | <label for> enfoque algo real. El resto de atributos (wire:model, name,
     | required) se quedan en el <select> oculto, que es quien alimenta al estado
     | de Livewire y al formulario.
     */
    $atributosBoton = $attributes->only(['id', 'aria-label']);
    $atributosSelect = $attributes->except(['class', 'required', 'id', 'aria-label']);
@endphp

{{--
    `clave` se ofrece para los desplegables que están enlazados a `wire:model`.
    El morph de Livewire conserva el estado de Alpine aunque el servidor cambie
    el valor, de modo que el botón puede seguir mostrando la etiqueta anterior
    mientras el <select> ya vale otra cosa. Al atar la raíz a una clave que
    depende del valor, Livewire reconstruye el control entero cuando ese valor
    cambia y el botón vuelve a mostrar lo que el servidor devolvió.
--}}
<div
    @if ($clave !== null) wire:key="{{ $clave }}" @endif
    x-data="{
        open: false,
        selected: @js((string) $selected),
        options: @js($opciones),
        placeholder: @js($placeholder),
        modelo: @js($modelo),
        get selectedLabel() {
            if (this.selected === '' && this.placeholder !== null) {
                return this.placeholder;
            }

            const option = this.options.find((item) => String(item.value) === String(this.selected));

            return option ? option.label : '';
        },
        isSelected(value) {
            return String(value) === String(this.selected);
        },
        /*
         | Cuando el desplegable tiene `modelo`, la verdad es la propiedad de
         | Livewire y el <select> oculto pasa a ser su espejo en el DOM. Antes de
         | que llegue el morph, el navegador puede haber movido el control por su
         | cuenta: el botón se pone al día en cuanto se abre el menú.
         */
        adoptarValorNativo() {
            const native = this.$el.querySelector('select');

            if (native && String(native.value) !== String(this.selected)) {
                this.selected = native.value;
            }
        },
        toggle() {
            this.adoptarValorNativo();

            this.open = !this.open;
        },
        /*
         | El valor viaja en la misma petición que el clic cuando hay `modelo`:
         | antes relied en el evento del <select> oculto, que es una segunda
         | petición asíncrona que `wire:submit` puede adelantar y enviar el
         | guardado con el valor anterior. Sin `modelo` sigue el camino antiguo.
         */
        select(value) {
            this.selected = value;

            const native = this.$el.querySelector('select');

            if (native) {
                native.value = value;

                if (this.modelo === null) {
                    native.dispatchEvent(new Event('input', { bubbles: true }));
                    native.dispatchEvent(new Event('change', { bubbles: true }));
                }
            }

            if (this.modelo !== null) {
                this.$wire.set(this.modelo, value);
            }

            this.open = false;
        },
    }"
    @keydown.escape.window="open = false"
    @click.outside="open = false"
    class="relative"
>
    {{--
        El <select> nativo permanece oculto para conservar el enlace con
        `wire:model`, los `name` del formulario y la lectura de valores. Como no
        es enfocable, `required` se traduce a `aria-required`: dejarlo en el
        select bloquearía el envío del formulario con un control inválido que el
        navegador no puede enfocar. La obligatoriedad la aplica la validación del
        servidor y se muestra con `flux:error`.

        El <option> que corresponde a `$selected` va marcado con `selected` para
        que el valor del control coincida con el del estado. Sin esa marca el
        navegador se queda con la primera opción de la lista y, al llegar un
        `change`, sobrescribe el valor real con ésa.
    --}}
    <select
        {{ $atributosSelect }}
        @if ($attributes->has('required')) aria-required="true" @endif
        class="sr-only"
        tabindex="-1"
        aria-hidden="true"
    >
        @if ($placeholder !== null)
            <option value="" @selected((string) $selected === '')>{{ $placeholder }}</option>
        @endif
        @foreach ($opciones as $opcion)
            <option value="{{ $opcion['value'] }}" @selected((string) $selected === $opcion['value'])>{{ $opcion['label'] }}</option>
        @endforeach
    </select>

    <button
        type="button"
        {{ $atributosBoton }}
        @click="toggle()"
        :aria-expanded="open ? 'true' : 'false'"
        aria-haspopup="listbox"
        class="relative flex w-full min-w-0 cursor-pointer items-center justify-between gap-3 rounded-xl border-0 py-2.5 pr-9 text-sm shadow-sm ring-1 ring-inset transition-all duration-300 ease-out focus:outline-none focus:ring-2 focus:ring-inset focus:ring-amber-500 {{ $iconoEspacio }} {{ $varianteClase }} {{ $attributes->get('class') }}"
    >
        @isset($leadingIcon)
            <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3.5 text-slate-400 dark:text-slate-500">
                {{ $leadingIcon }}
            </span>
        @endisset

        <span
            x-text="selectedLabel"
            :class="selected !== '' ? 'text-slate-900 dark:text-white' : 'text-slate-400 dark:text-zinc-500'"
            class="block min-w-0 truncate text-sm"
        ></span>

        <span class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-3 text-slate-400 transition-transform duration-300 ease-out" :class="open ? 'rotate-180' : ''">
            <flux:icon.chevron-down class="size-4" />
        </span>
    </button>

    <div
        x-show="open"
        x-cloak
        role="listbox"
        x-transition:enter="transition ease-out duration-150"
        x-transition:enter-start="opacity-0 -translate-y-1 scale-95"
        x-transition:enter-end="opacity-100 translate-y-0 scale-100"
        x-transition:leave="transition ease-in duration-100"
        x-transition:leave-start="opacity-100 translate-y-0 scale-100"
        x-transition:leave-end="opacity-0 -translate-y-1 scale-95"
        class="absolute left-0 right-0 z-30 mt-2 max-h-64 origin-top overflow-y-auto rounded-xl border border-slate-200/80 bg-white p-1.5 shadow-xl shadow-slate-950/5 focus:outline-none dark:border-zinc-700/80 dark:bg-zinc-800"
    >
        @if ($placeholder !== null)
            <button
                type="button"
                role="option"
                @click="select('')"
                :aria-selected="isSelected('').toString()"
                :class="isSelected('') ? 'bg-amber-50 font-semibold text-amber-700 dark:bg-amber-500/15 dark:text-amber-300' : 'font-medium text-slate-700 hover:bg-slate-100 dark:text-zinc-200 dark:hover:bg-zinc-700'"
                class="flex w-full min-w-0 items-center gap-2 rounded-lg px-3 py-2.5 text-left text-sm transition-colors duration-150 focus:outline-none"
            >
                <span class="flex shrink-0 items-center" :class="isSelected('') ? 'opacity-100' : 'opacity-0'">
                    <flux:icon.check class="size-4" />
                </span>
                <span class="block min-w-0 truncate">{{ $placeholder }}</span>
            </button>
        @endif

        @foreach ($opciones as $opcion)
            <button
                type="button"
                role="option"
                data-value="{{ $opcion['value'] }}"
                @click="select($event.currentTarget.dataset.value)"
                :aria-selected="isSelected($el.dataset.value).toString()"
                :class="isSelected($el.dataset.value) ? 'bg-amber-50 font-semibold text-amber-700 dark:bg-amber-500/15 dark:text-amber-300' : 'font-medium text-slate-700 hover:bg-slate-100 dark:text-zinc-200 dark:hover:bg-zinc-700'"
                class="flex w-full min-w-0 items-center gap-2 rounded-lg px-3 py-2.5 text-left text-sm transition-colors duration-150 focus:outline-none"
            >
                <span class="flex shrink-0 items-center" :class="isSelected($el.dataset.value) ? 'opacity-100' : 'opacity-0'">
                    <flux:icon.check class="size-4" />
                </span>
                <span class="block min-w-0 truncate">{{ $opcion['label'] }}</span>
            </button>
        @endforeach
    </div>
</div>
