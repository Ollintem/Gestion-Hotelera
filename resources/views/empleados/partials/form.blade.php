@use('Illuminate\Support\Str')

{{-- ======================================================
     FORMULARIO COMPARTIDO: CREAR / EDITAR EMPLEADO
     ====================================================== --}}

<div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm dark:border-slate-700 dark:bg-slate-800">
    <div class="border-b border-slate-100 px-6 py-5 dark:border-slate-700/60 sm:px-8">
        <div class="flex items-center gap-3">
            <span class="flex size-10 shrink-0 items-center justify-center rounded-xl bg-amber-500/15">
                <flux:icon.user-plus class="size-5 text-amber-600 dark:text-amber-400" />
            </span>
            <div>
                <flux:heading size="lg" class="!text-slate-800 !font-semibold dark:!text-slate-100">
                    {{ $empleadoId ? 'Editar Empleado' : 'Crear Nuevo Empleado' }}
                </flux:heading>
                <flux:subheading class="!text-slate-600 !font-medium dark:!text-slate-400">
                    {{ $empleadoId
                        ? 'Actualiza los datos personales del empleado y su acceso al sistema.'
                        : 'Registra al empleado y define si podrá acceder al sistema.' }}
                </flux:subheading>
            </div>
        </div>
    </div>

    <div class="px-6 py-6 sm:px-8">
        <form wire:submit="guardar">
            <div class="grid gap-4 sm:grid-cols-2">
                <div>
                    <label for="nombre" class="mb-1.5 block text-sm font-semibold text-slate-700 dark:text-zinc-200">Nombre</label>
                    <div class="relative">
                        <input
                            id="nombre"
                            type="text"
                            wire:model.blur="nombre"
                            placeholder="Ejemplo: Juan"
                            required
                            autocomplete="off"
                            @class([$this->claseInput('nombre')])
                        />
                        @if (! $errors->has('nombre') && trim($nombre) !== '')
                            <span class="pointer-events-none absolute inset-y-0 right-3 flex items-center">
                                <flux:icon.check-circle class="size-5 text-emerald-500" />
                            </span>
                        @endif
                    </div>
                    @error('nombre')
                        <p class="mt-1.5 text-sm font-medium text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="apellidos" class="mb-1.5 block text-sm font-semibold text-slate-700 dark:text-zinc-200">Apellidos</label>
                    <div class="relative">
                        <input
                            id="apellidos"
                            type="text"
                            wire:model.blur="apellidos"
                            placeholder="Ejemplo: Pérez López"
                            required
                            autocomplete="off"
                            @class([$this->claseInput('apellidos')])
                        />
                        @if (! $errors->has('apellidos') && trim($apellidos) !== '')
                            <span class="pointer-events-none absolute inset-y-0 right-3 flex items-center">
                                <flux:icon.check-circle class="size-5 text-emerald-500" />
                            </span>
                        @endif
                    </div>
                    @error('apellidos')
                        <p class="mt-1.5 text-sm font-medium text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="telefono" class="mb-1.5 block text-sm font-semibold text-slate-700 dark:text-zinc-200">Teléfono</label>
                    <div class="relative">
                        <input
                            id="telefono"
                            type="tel"
                            wire:model.blur="telefono"
                            placeholder="Ejemplo: 5512345678"
                            @class([$this->claseInput('telefono')])
                        />
                        @if (! $errors->has('telefono') && trim($telefono) !== '')
                            <span class="pointer-events-none absolute inset-y-0 right-3 flex items-center">
                                <flux:icon.check-circle class="size-5 text-emerald-500" />
                            </span>
                        @endif
                    </div>
                    @error('telefono')
                        <p class="mt-1.5 text-sm font-medium text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="rol" class="mb-1.5 block text-sm font-semibold text-slate-700 dark:text-zinc-200">Rol del Sistema</label>
                    <x-dropdown
                        id="rol"
                        wire:model.blur="rol"
                        :selected="$rol"
                        placeholder="Sin rol asignado…"
                        :options="$this->rolesDisponibles->mapWithKeys(fn ($rolDisponible) => [$rolDisponible->name => Str::headline($rolDisponible->name)])->all()"
                    />
                    <p class="mt-1.5 text-xs text-slate-500">Categoría principal del empleado. No incluye a Super Admin.</p>
                    @error('rol')
                        <p class="mt-1.5 text-sm font-medium text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                <div class="sm:col-span-2">
                    <label for="correo_electronico" class="mb-1.5 block text-sm font-semibold text-slate-700 dark:text-zinc-200">Correo Electrónico</label>
                    <div class="relative">
                        <input
                            id="correo_electronico"
                            type="email"
                            wire:model.blur="correo_electronico"
                            placeholder="empleado@hotel.com"
                            required
                            @class([$this->claseInput('correo_electronico')])
                        />
                        @if (! $errors->has('correo_electronico') && trim($correo_electronico) !== '')
                            <span class="pointer-events-none absolute inset-y-0 right-3 flex items-center">
                                <flux:icon.check-circle class="size-5 text-emerald-500" />
                            </span>
                        @endif
                    </div>
                    @error('correo_electronico')
                        <p class="mt-1.5 text-sm font-medium text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="salario" class="mb-1.5 block text-sm font-semibold text-slate-700 dark:text-zinc-200">Salario</label>
                    <div class="relative">
                        <span class="pointer-events-none absolute inset-y-0 left-3.5 flex items-center text-sm text-slate-400">$</span>
                        <input
                            id="salario"
                            type="number"
                            step="0.01"
                            min="0"
                            wire:model.blur="salario"
                            placeholder="0.00"
                            class="block w-full rounded-xl border-0 bg-white py-2.5 pl-8 pr-3.5 text-sm text-slate-900 shadow-sm ring-1 ring-inset ring-slate-300 transition-all duration-300 ease-out placeholder:text-slate-400 focus:ring-2 focus:ring-inset focus:ring-amber-500 dark:bg-zinc-900 dark:text-white dark:ring-zinc-700"
                        />
                    </div>
                    @error('salario')
                        <p class="mt-1.5 text-sm font-medium text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="turno" class="mb-1.5 block text-sm font-semibold text-slate-700 dark:text-zinc-200">Turno</label>
                    <x-dropdown
                        id="turno"
                        wire:model.blur="turno"
                        :selected="$turno"
                        placeholder="Selecciona un turno…"
                        :options="array_combine($turnos, $turnos)"
                    />
                    @error('turno')
                        <p class="mt-1.5 text-sm font-medium text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="esta_activo" class="mb-1.5 block text-sm font-semibold text-slate-700 dark:text-zinc-200">Estado</label>
                    <x-dropdown
                        id="esta_activo"
                        wire:model.blur="esta_activo"
                        :selected="$esta_activo ? '1' : '0'"
                        :options="['1' => 'Activo', '0' => 'Inactivo']"
                    />
                    @error('esta_activo')
                        <p class="mt-1.5 text-sm font-medium text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                <div class="sm:col-span-2">
                    <div class="flex items-start justify-between gap-4 rounded-xl border border-slate-200 bg-slate-50 p-4 dark:border-zinc-700 dark:bg-zinc-800/50">
                        <div>
                            <p class="text-sm font-semibold text-slate-800 dark:text-zinc-200">Acceso al sistema</p>
                            <p class="mt-0.5 text-sm text-slate-500">
                                {{ $acceso_sistema ? 'El empleado podrá iniciar sesión en la plataforma.' : 'El empleado no podrá iniciar sesión en la plataforma.' }}
                            </p>
                        </div>

                        <label class="inline-flex shrink-0 cursor-pointer items-center">
                            <input type="checkbox" wire:model="acceso_sistema" class="peer sr-only" />
                            <span class="relative h-6 w-11 rounded-full bg-slate-300 transition-colors duration-300 ease-out after:absolute after:left-1 after:top-1 after:size-4 after:rounded-full after:bg-white after:shadow after:transition-transform after:duration-300 after:ease-out peer-checked:bg-amber-500 peer-checked:after:translate-x-5 peer-focus-visible:ring-2 peer-focus-visible:ring-amber-500 peer-focus-visible:ring-offset-1 dark:bg-slate-600"></span>
                        </label>
                    </div>
                    @error('acceso_sistema')
                        <p class="mt-1.5 text-sm font-medium text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                @if ($acceso_sistema)
                    <div class="sm:col-span-2">
                        <div class="animate-fade-in flex items-start justify-between gap-4 rounded-xl border border-slate-200 bg-slate-50 p-4 dark:border-zinc-700 dark:bg-zinc-800/50">
                            <div>
                                <label for="contrasena" class="text-sm font-semibold text-slate-800 dark:text-zinc-200">
                                    Contraseña {{ $empleadoId ? '(opcional)' : 'inicial' }}
                                </label>
                                <p class="mt-0.5 text-sm text-slate-500">
                                    @if ($empleadoId)
                                        Déjala vacía para conservar la contraseña actual.
                                    @else
                                        Mínimo 8 caracteres.
                                    @endif
                                </p>
                            </div>

                            <input
                                id="contrasena"
                                type="password"
                                wire:model.blur="contrasena"
                                placeholder="••••••••"
                                autocomplete="new-password"
                                class="block w-44 rounded-xl border-0 bg-white px-3.5 py-2 text-sm text-slate-900 shadow-sm ring-1 ring-inset ring-slate-300 transition-all duration-300 ease-out placeholder:text-slate-400 focus:ring-2 focus:ring-inset focus:ring-amber-500 dark:bg-zinc-900 dark:text-white dark:ring-zinc-700"
                            />
                        </div>
                        @error('contrasena')
                            <p class="mt-1.5 text-sm font-medium text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>
                @endif
            </div>

            <div class="mt-8 flex items-center justify-end gap-3">
                <button
                    type="button"
                    wire:click="cerrarModal"
                    class="inline-flex items-center justify-center rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-semibold text-slate-700 shadow-sm transition-all duration-300 ease-out hover:scale-[1.02] hover:bg-slate-50 hover:shadow focus:outline-none focus:ring-2 focus:ring-slate-400 focus:ring-offset-2 active:scale-95 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-200 dark:hover:bg-zinc-700"
                >
                    Cancelar
                </button>

                <button
                    type="submit"
                    class="inline-flex items-center justify-center gap-2 rounded-lg bg-amber-500 px-4 py-2 text-sm font-semibold text-white shadow-sm transition-all duration-300 ease-out hover:scale-[1.02] hover:bg-amber-600 hover:shadow-lg focus:outline-none focus:ring-2 focus:ring-amber-500 focus:ring-offset-2 active:scale-95"
                >
                    <span wire:loading.remove wire:target="guardar">
                        <flux:icon.check class="size-4" />
                    </span>
                    <span wire:loading wire:target="guardar">Guardando...</span>
                    <span wire:loading.remove wire:target="guardar">{{ $empleadoId ? 'Guardar cambios' : 'Crear empleado' }}</span>
                </button>
            </div>
        </form>
    </div>
</div>