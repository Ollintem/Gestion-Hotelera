@props(['estado'])

@php
    $color = match ($estado) {
        'Disponible', 'Confirmada', 'Completado', 'Activo' => 'emerald',
        'Ocupada', 'Pendiente', 'En Proceso' => 'amber',
        'Limpieza', 'Mantenimiento' => 'sky',
        'Finalizada', 'Inactivo' => 'zinc',
        'Cancelada', 'Anulado' => 'rose',
        default => 'zinc',
    };

    $classes = match ($color) {
        'emerald' => '!bg-emerald-100 !text-emerald-800 [&_button]:!text-emerald-800',
        'amber' => '!bg-amber-100 !text-amber-800 [&_button]:!text-amber-800',
        'sky' => '!bg-sky-100 !text-sky-800 [&_button]:!text-sky-800',
        'rose' => '!bg-rose-100 !text-rose-800 [&_button]:!text-rose-800',
        default => '!bg-zinc-100 !text-zinc-800 [&_button]:!text-zinc-800',
    };
@endphp

<flux:badge :color="$color" size="sm" :class="$classes">{{ $estado }}</flux:badge>