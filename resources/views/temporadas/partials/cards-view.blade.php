{{-- ======================================================
     SECCIÓN 1: CALENDARIO DE TEMPORADAS

     Vista resumida de los periodos: una tarjeta por temporada con el indicador
     vertical de color a la izquierda —verde si abarata, dorado si es el precio de
     referencia, rojo si encarece— que es el mismo nivel de demanda que pinta la
     tabla de abajo. La paleta la declara `temporadas/index.blade.php` para que
     ninguna de las dos secciones cuente cosas distintas de la misma temporada.

     El rango se muestra en `YYYY-MM-DD` a propósito: es el formato con el que se
     comparan dos periodos de un vistazo, sin ambigüedad entre día y mes. En el
     `title` va la fecha larga para quien necesite leerla sin ambigüedad de
     contrato.
     ====================================================== --}}

<div class="mt-8">
    <h2 class="font-serif text-lg font-bold tracking-tight text-slate-800 dark:text-white">
        Calendario de temporadas
    </h2>

    <div class="mt-4 grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-3">
        @forelse ($temporadas as $temporada)
            @php($tono = $tonosDemanda[$temporada->nivelDemanda()])

            <article
                wire:key="temporada-tarjeta-{{ $temporada->id }}"
                class="group animate-fade-in-up rounded-2xl border border-slate-200 border-l-4 bg-white p-5 shadow-sm transition-all duration-300 ease-in-out hover:-translate-y-0.5 hover:shadow-lg {{ $tono['borde'] }} {{ $temporada->activo ? '' : 'opacity-70' }}"
            >
                <div class="flex items-start justify-between gap-3">
                    <h3 class="min-w-0 truncate text-base font-bold text-slate-800 transition-colors duration-200 group-hover:text-slate-900">
                        {{ $temporada->nombre }}
                    </h3>

                    <span @class([
                        'inline-flex shrink-0 items-center gap-1.5 rounded-full px-2.5 py-1 text-[11px] font-bold uppercase tracking-wide',
                        'bg-emerald-100 text-emerald-700' => $temporada->activo,
                        'bg-slate-100 text-slate-500' => ! $temporada->activo,
                    ])>
                        {{ $temporada->activo ? 'Activa' : 'Inactiva' }}
                    </span>
                </div>

                <p class="mt-2 inline-flex items-center gap-1.5 rounded-full px-2.5 py-1 text-[11px] font-bold {{ $tono['pastilla'] }}">
                    <span class="inline-flex size-2 shrink-0 rounded-full {{ $tono['punto'] }}"></span>
                    {{ $temporada->etiquetaNivelDemanda() }}
                </p>

                {{-- ==============================================
                     RANGO DE FECHAS
                     ============================================== --}}

                <p
                    class="mt-4 flex flex-wrap items-center gap-2 rounded-lg px-3 py-2 text-xs font-semibold tabular-nums text-slate-600 {{ $tono['fondo'] }}"
                    title="{{ $temporada->fecha_inicio->format('d/m/Y') }} a {{ $temporada->fecha_fin->format('d/m/Y') }}"
                >
                    <span>{{ $temporada->fecha_inicio->format('Y-m-d') }}</span>
                    <span class="text-slate-400">&rarr;</span>
                    <span>{{ $temporada->fecha_fin->format('Y-m-d') }}</span>
                </p>

                {{-- ==============================================
                     MULTIPLICADOR Y PRECIO EFECTIVO

                     El precio efectivo es el número por el que se reserva, así
                     que va en dorado y con más tamaño que el factor que lo
                     produce.
                     ============================================== --}}

                <div class="mt-4 flex items-end justify-between gap-3">
                    <div>
                        <p class="text-[11px] font-semibold uppercase tracking-wider text-slate-400">
                            Multiplicador
                        </p>

                        <p class="mt-0.5 text-xl font-bold tabular-nums text-slate-800">
                            {{ $temporada->multiplicadorEnTexto() }}
                        </p>
                    </div>

                    <div class="text-right">
                        <p class="text-[11px] font-semibold uppercase tracking-wider text-slate-400">
                            Precio por noche
                        </p>

                        <p class="mt-0.5 text-xl font-bold tabular-nums text-amber-700">
                            {{ $temporada->precioEfectivoEnPesos() }}
                        </p>
                    </div>
                </div>
            </article>
        @empty
            <div class="sm:col-span-2 xl:col-span-3">
                <div class="flex flex-col items-center rounded-2xl border border-dashed border-slate-300 bg-white px-6 py-12 text-center">
                    <span class="mb-3 flex size-12 items-center justify-center rounded-full bg-slate-100 text-slate-400">
                        <flux:icon.calendar-days class="size-6" />
                    </span>

                    <p class="text-sm font-semibold text-slate-700">No hay temporadas registradas.</p>
                    <p class="mt-1 text-sm text-slate-500">Crea la primera para empezar a aplicar precios por periodo.</p>
                </div>
            </div>
        @endforelse
    </div>
</div>