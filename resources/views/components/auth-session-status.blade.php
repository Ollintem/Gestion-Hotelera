@props([
    'status',
])

@if ($status)
    <div
        data-notificacion
        data-notificacion-duracion="3000"
        role="status"
        {{ $attributes->merge(['class' => 'flex items-start gap-2 font-medium text-sm text-green-600 transition duration-200']) }}
    >
        <span class="flex-1">{{ $status }}</span>

        <button
            type="button"
            data-notificacion-cerrar
            class="shrink-0 rounded-lg p-0.5 opacity-60 transition hover:opacity-100"
            aria-label="{{ __('Cerrar notificación') }}"
        >
            <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
            </svg>
        </button>
    </div>
@endif
