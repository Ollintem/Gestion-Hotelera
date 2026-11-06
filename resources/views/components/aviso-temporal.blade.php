@props([
    'mensaje' => null,
    'tipo' => 'success',
    'clave' => null,
    'duracion' => 3000,
    'persistente' => false,
])

@php
    $variantes = [
        'success' => 'success',
        'error' => 'danger',
        'warning' => 'warning',
        'info' => 'secondary',
        'aviso' => 'warning',
    ];

    $iconos = [
        'success' => 'check-circle',
        'error' => 'x-circle',
        'warning' => 'exclamation-triangle',
        'info' => 'information-circle',
        'aviso' => 'exclamation-triangle',
    ];

    $papeles = [
        'success' => 'status',
        'error' => 'alert',
        'warning' => 'alert',
        'info' => 'status',
        'aviso' => 'alert',
    ];
@endphp

@if (filled($mensaje))
    <div
        @if ($clave !== null) wire:key="aviso-{{ $tipo }}-{{ $clave }}" @endif
        data-notificacion
        data-notificacion-duracion="{{ (int) $duracion }}"
        @if ($persistente) data-notificacion-persistente @endif
        role="{{ $papeles[$tipo] ?? 'status' }}"
        aria-live="polite"
        class="relative mb-4 animate-fade-in"
    >
        <flux:callout :variant="$variantes[$tipo] ?? 'success'" :icon="$iconos[$tipo] ?? 'check-circle'">
            <div class="flex items-start gap-3">
                <p class="min-w-0 flex-1 leading-snug">{{ $mensaje }}</p>

                <button
                    type="button"
                    data-notificacion-cerrar
                    class="-mr-1 -mt-1 shrink-0 rounded-lg p-1 opacity-60 transition hover:opacity-100 focus:outline-none focus-visible:ring-2 focus-visible:ring-current"
                    aria-label="{{ __('Cerrar notificación') }}"
                >
                    <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </flux:callout>
    </div>
@endif
