@props([
    'selected' => '',
    'placeholder' => null,
    'options' => [],
])

@php
    $opciones = [];

    foreach ($options as $clave => $opcion) {
        if (is_array($opcion)) {
            $valor = $opcion['value'] ?? $opcion['clave'] ?? $clave;
            $etiqueta = $opcion['label'] ?? $opcion['etiqueta'] ?? $valor;
        } elseif (is_object($opcion)) {
            $valor = $opcion->value ?? $opcion->id ?? $opcion->clave ?? $clave;
            $etiqueta = $opcion->label ?? $opcion->etiqueta ?? $opcion->nombre ?? $valor;
        } else {
            $valor = $clave;
            $etiqueta = (string) $opcion;
        }

        $opciones[] = [
            'value' => (string) $valor,
            'label' => (string) $etiqueta,
        ];
    }
@endphp

<div
    x-data="{
        open: false,
        selected: @js((string) $selected),
        options: @js($opciones),
        placeholder: @js($placeholder),
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
        toggle() {
            const native = this.$el.querySelector('select');

            if (native && String(native.value) !== String(this.selected)) {
                this.selected = native.value;
            }

            this.open = !this.open;
        },
        select(value) {
            this.selected = value;

            const native = this.$el.querySelector('select');

            if (native) {
                native.value = value;
                native.dispatchEvent(new Event('input', { bubbles: true }));
                native.dispatchEvent(new Event('change', { bubbles: true }));
            }

            this.open = false;
        },
    }"
    @keydown.escape.window="open = false"
    @click.outside="open = false"
    class="relative"
>
    <select {{ $attributes->except(['class']) }} class="sr-only" tabindex="-1" aria-hidden="true">
        @if ($placeholder !== null)
            <option value="">{{ $placeholder }}</option>
        @endif
        @foreach ($opciones as $opcion)
            <option value="{{ $opcion['value'] }}">{{ $opcion['label'] }}</option>
        @endforeach
    </select>

    <button
        type="button"
        @click="toggle()"
        :aria-expanded="open ? 'true' : 'false'"
        aria-haspopup="listbox"
        class="relative flex w-full min-w-0 items-center justify-between gap-3 rounded-xl border-0 bg-white py-2.5 shadow-sm ring-1 ring-inset ring-slate-300 transition-all duration-300 ease-out hover:ring-slate-400 focus:outline-none focus:ring-2 focus:ring-inset focus:ring-amber-500 dark:bg-zinc-900 dark:text-white dark:ring-zinc-700 dark:hover:ring-zinc-600 {{ isset($leadingIcon) ? 'pl-10' : 'pl-3.5' }} pr-9"
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