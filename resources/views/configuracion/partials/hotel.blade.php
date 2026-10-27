{{-- ======================================================
     APARTADO: INFORMACIÓN DEL HOTEL

     Dos estados: la ficha de solo lectura y el formulario de
     edición. El registro es único (`InformacionHotel::current()`),
     así que el formulario siempre parte de los datos reales.
     Los campos vacíos se muestran con un guion.
     ====================================================== --}}

@php
    $etiqueta = 'mb-1.5 block text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400';
    $campo = 'w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-800 shadow-sm transition-all duration-200 placeholder:text-slate-400 focus:border-amber-500 focus:outline-none focus:ring-2 focus:ring-amber-500/20 dark:border-zinc-700 dark:bg-zinc-900 dark:text-white';

    // Fila de la ficha: etiqueta a la izquierda, valor a la derecha.
    $fila = 'flex items-baseline justify-between gap-4 py-2';

    $habitacionesDeclaradas = $hotel->numero_habitaciones;
    $habitacionesReales = $hotel->habitacionesRegistradas();
@endphp

@if (! $editandoHotel)
    {{-- ==================================================
         FICHA (SOLO LECTURA)
         ================================================== --}}
    <flux:card>
        <div class="mb-5 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <flux:heading size="lg" class="!text-slate-800 !font-semibold">Datos del establecimiento</flux:heading>
                <flux:subheading class="!text-slate-500 !font-medium">
                    Información general del hotel guardada en el sistema.
                </flux:subheading>
            </div>

            @can('configuracion.editar')
                <flux:button type="button" variant="outline" icon="pencil-square" wire:click="editarHotel" class="shrink-0 transition-all duration-200 hover:scale-105 active:scale-95">
                    Editar
                </flux:button>
            @endcan
        </div>

        <div class="grid gap-x-8 gap-y-1 lg:grid-cols-2">
            <div class="{{ $fila }} border-b border-slate-100 dark:border-zinc-800">
                <span class="{{ $etiqueta }} !mb-0">Nombre</span>
                <span class="text-right text-sm font-semibold text-slate-900 dark:text-white">
                    {{ $hotel->valor('nombre') ?: '—' }}
                </span>
            </div>

            <div class="{{ $fila }} border-b border-slate-100 dark:border-zinc-800">
                <span class="{{ $etiqueta }} !mb-0">RFC</span>
                <span class="text-right text-sm text-slate-700 dark:text-slate-300">
                    {{ $hotel->valor('rfc') ?: '—' }}
                </span>
            </div>

            <div class="{{ $fila }} border-b border-slate-100 dark:border-zinc-800">
                <span class="{{ $etiqueta }} !mb-0">Dirección</span>
                <span class="text-right text-sm text-slate-700 dark:text-slate-300">
                    {{ $hotel->valor('direccion') ?: '—' }}
                </span>
            </div>

            <div class="{{ $fila }} border-b border-slate-100 dark:border-zinc-800">
                <span class="{{ $etiqueta }} !mb-0">Ciudad</span>
                <span class="text-right text-sm text-slate-700 dark:text-slate-300">
                    {{ $hotel->valor('ciudad') ?: '—' }}
                </span>
            </div>

            <div class="{{ $fila }} border-b border-slate-100 dark:border-zinc-800">
                <span class="{{ $etiqueta }} !mb-0">Teléfono</span>
                <span class="text-right text-sm text-slate-700 dark:text-slate-300">
                    {{ $hotel->valor('telefono') ?: '—' }}
                </span>
            </div>

            <div class="{{ $fila }} border-b border-slate-100 dark:border-zinc-800">
                <span class="{{ $etiqueta }} !mb-0">Correo electrónico</span>
                <span class="text-right text-sm text-slate-700 dark:text-slate-300">
                    {{ $hotel->valor('email') ?: '—' }}
                </span>
            </div>

            <div class="{{ $fila }} border-b border-slate-100 dark:border-zinc-800">
                <span class="{{ $etiqueta }} !mb-0">Sitio web</span>
                <span class="text-right text-sm text-slate-700 dark:text-slate-300">
                    {{ $hotel->valor('sitio_web') ?: '—' }}
                </span>
            </div>

            <div class="{{ $fila }} border-b border-slate-100 dark:border-zinc-800">
                <span class="{{ $etiqueta }} !mb-0">Habitaciones declaradas</span>
                <span class="text-right">
                    @if ($habitacionesDeclaradas === null)
                        <span class="text-sm text-slate-400 dark:text-slate-500">—</span>
                    @elseif ($habitacionesDeclaradas === $habitacionesReales)
                        <flux:badge color="emerald" size="sm" class="!bg-emerald-100 !text-emerald-800 !font-semibold">
                            {{ $habitacionesDeclaradas }}
                        </flux:badge>
                    @else
                        <span class="inline-flex items-center gap-1.5" title="El sistema tiene registradas {{ $habitacionesReales }} habitaciones">
                            <flux:badge color="amber" size="sm" class="!bg-amber-100 !text-amber-800 !font-semibold">
                                {{ $habitacionesDeclaradas }}
                            </flux:badge>
                            <span class="text-xs text-slate-500 dark:text-slate-400">
                                (sistema: {{ $habitacionesReales }})
                            </span>
                        </span>
                    @endif
                </span>
            </div>
        </div>

        <div class="mt-5 border-t border-slate-100 pt-4 dark:border-zinc-800">
            <p class="{{ $etiqueta }}">Descripción</p>
            <p class="text-sm text-slate-700 dark:text-slate-300">
                {{ $hotel->valor('descripcion') ?: 'Sin descripción registrada.' }}
            </p>
        </div>
    </flux:card>
@else
    {{-- ==================================================
         FORMULARIO DE EDICIÓN
         ================================================== --}}
    <flux:card class="w-full max-w-3xl">
        <form wire:submit="guardarHotel">
            <div class="mb-6">
                <flux:heading size="lg" class="!text-slate-800 !font-semibold">Editar información del hotel</flux:heading>
                <flux:subheading class="!text-slate-500 !font-medium">
                    Los campos marcados con * son obligatorios.
                </flux:subheading>
            </div>

            <div class="grid gap-4 sm:grid-cols-2">
                <flux:field>
                    <flux:label>Nombre del hotel *</flux:label>
                    <flux:input wire:model="nombre" maxlength="150" placeholder="Ej. NovaStay Hotel" required />
                    <flux:error name="nombre" />
                </flux:field>

                <flux:field>
                    <flux:label>RFC</flux:label>
                    <flux:input wire:model="rfc" maxlength="20" placeholder="Ej. ABC123456789" />
                    <flux:error name="rfc" />
                </flux:field>

                <flux:field class="sm:col-span-2">
                    <flux:label>Dirección</flux:label>
                    <flux:input wire:model="direccion" maxlength="255" placeholder="Calle, número, colonia…" />
                    <flux:error name="direccion" />
                </flux:field>

                <flux:field>
                    <flux:label>Ciudad</flux:label>
                    <flux:input wire:model="ciudad" maxlength="100" placeholder="Ej. Cancún" />
                    <flux:error name="ciudad" />
                </flux:field>

                <flux:field>
                    <flux:label>Teléfono</flux:label>
                    <flux:input wire:model="telefono" maxlength="50" placeholder="Ej. 55 1234 5678" />
                    <flux:error name="telefono" />
                </flux:field>

                <flux:field>
                    <flux:label>Correo electrónico</flux:label>
                    <flux:input type="email" wire:model="email" maxlength="150" placeholder="contacto@hotel.com" />
                    <flux:error name="email" />
                </flux:field>

                <flux:field>
                    <flux:label>Sitio web</flux:label>
                    <flux:input wire:model="sitio_web" maxlength="150" placeholder="Ej. https://www.hotel.com" />
                    <flux:error name="sitio_web" />
                </flux:field>

                <flux:field>
                    <flux:label>Número de habitaciones</flux:label>
                    <flux:input type="number" min="0" max="9999" wire:model="numero_habitaciones" placeholder="0" />
                    <flux:error name="numero_habitaciones" />
                    <flux:text size="sm" class="!text-slate-400">
                        El sistema tiene registradas {{ $habitacionesReales }}.
                    </flux:text>
                </flux:field>

                <flux:field>
                    <flux:label>&nbsp;</flux:label>
                    <span aria-hidden="true"></span>
                </flux:field>

                <flux:field class="sm:col-span-2">
                    <flux:label>Descripción</flux:label>
                    <flux:textarea
                        wire:model="descripcion"
                        rows="3"
                        maxlength="1000"
                        placeholder="Breve descripción del establecimiento…"
                        class="resize-none"
                    />
                    <flux:error name="descripcion" />
                </flux:field>
            </div>

            <div class="mt-6 flex items-center justify-end gap-3">
                <flux:button type="button" variant="ghost" wire:click="cerrarEdicionHotel">Cancelar</flux:button>
                <flux:button type="submit" variant="primary" wire:loading.attr="disabled" wire:target="guardarHotel" class="transition-all duration-200 hover:scale-105 active:scale-95 disabled:pointer-events-none disabled:opacity-70">
                    <span wire:loading.remove wire:target="guardarHotel" class="inline-flex items-center gap-2">
                        <flux:icon.check class="size-4" />
                        Guardar
                    </span>
                    <span wire:loading wire:target="guardarHotel">Guardando…</span>
                </flux:button>
            </div>
        </form>
    </flux:card>
@endif
