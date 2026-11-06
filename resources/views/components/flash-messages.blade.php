@php
    /**
     * Notificaciones flotantes globales (mensajes flash de sesión).
     *
     * Se renderizan en los layouts de la aplicación para que cualquier
     * `->with('success', ...)`, `->with('error', ...)` o `->with('aviso', ...)`
     * de los módulos (reservaciones, habitaciones, servicios, clientes, pagos,
     * gastos, empleados, roles, configuración, usuarios...) aparezca siempre
     * en el mismo lugar y se oculte automáticamente a los 3000 ms mediante el
     * mecanismo central de `resources/js/notificaciones.js`.
     */
    $estilos = [
        'success' => [
            'clases' => 'border-emerald-200 bg-emerald-50 text-emerald-900 dark:border-emerald-500/40 dark:bg-emerald-500/10 dark:text-emerald-100',
            'icono' => 'text-emerald-600 dark:text-emerald-400',
            'trazo' => 'M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z',
            'papel' => 'status',
        ],
        'error' => [
            'clases' => 'border-rose-200 bg-rose-50 text-rose-900 dark:border-rose-500/40 dark:bg-rose-500/10 dark:text-rose-100',
            'icono' => 'text-rose-600 dark:text-rose-400',
            'trazo' => 'M12 9v3.75m9-.75a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 3.75h.008v.008H12v-.008Z',
            'papel' => 'alert',
        ],
        'warning' => [
            'clases' => 'border-amber-200 bg-amber-50 text-amber-900 dark:border-amber-500/40 dark:bg-amber-500/10 dark:text-amber-100',
            'icono' => 'text-amber-600 dark:text-amber-400',
            'trazo' => 'M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z',
            'papel' => 'alert',
        ],
        'info' => [
            'clases' => 'border-sky-200 bg-sky-50 text-sky-900 dark:border-sky-500/40 dark:bg-sky-500/10 dark:text-sky-100',
            'icono' => 'text-sky-600 dark:text-sky-400',
            'trazo' => 'M11.25 11.25l.041-.02a.75.75 0 0 1 1.063.852l-.708 2.836a.75.75 0 0 0 1.063.853l.041-.021M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9-3.75h.008v.008H12V8.25Z',
            'papel' => 'status',
        ],
        'aviso' => [
            'clases' => 'border-amber-200 bg-amber-50 text-amber-900 dark:border-amber-500/40 dark:bg-amber-500/10 dark:text-amber-100',
            'icono' => 'text-amber-600 dark:text-amber-400',
            'trazo' => 'M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z',
            'papel' => 'status',
        ],
    ];

    $mensajes = [];

    foreach (array_keys($estilos) as $clave) {
        $mensajes[$clave] = session($clave);
    }

    $mensajes = array_filter($mensajes);
@endphp

@if ($mensajes)
    <div
        aria-live="polite"
        class="pointer-events-none fixed inset-x-0 top-20 z-[100] flex flex-col items-center gap-2 px-4 sm:top-6 sm:items-end sm:px-6"
    >
        @foreach ($mensajes as $clave => $mensaje)
            @php($estilo = $estilos[$clave])

            <div
                data-notificacion
                data-notificacion-duracion="3000"
                role="{{ $estilo['papel'] }}"
                class="pointer-events-auto flex w-full max-w-md items-start gap-3 rounded-xl border px-4 py-3 text-sm font-medium shadow-lg shadow-slate-900/10 transition duration-200 {{ $estilo['clases'] }}"
            >
                <svg class="mt-0.5 size-5 shrink-0 {{ $estilo['icono'] }}" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="{{ $estilo['trazo'] }}" />
                </svg>

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
        @endforeach
    </div>
@endif
