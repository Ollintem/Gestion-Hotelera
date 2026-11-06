@use('Illuminate\Support\Str')

{{-- ======================================================
     TABLA DE EMPLEADOS
     ====================================================== --}}

<div class="animate-fade-in-up overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm [animation-delay:160ms] dark:border-slate-700 dark:bg-slate-800">
    <div class="flex items-center justify-between border-b border-slate-100 px-5 py-4 dark:border-slate-700/60">
        <div>
            <h2 class="text-base font-bold text-slate-900 dark:text-white">Lista del personal</h2>
            <p class="text-sm text-slate-500 dark:text-slate-400">Perfil, turno y nivel de acceso del equipo.</p>
        </div>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full min-w-[1320px] text-left text-sm">
            <thead>
                <tr class="border-b border-slate-100 bg-slate-50/80 dark:border-slate-700/60 dark:bg-slate-800/80">
                    <th scope="col" class="px-5 py-3 text-xs font-semibold uppercase tracking-wider text-slate-600 dark:text-slate-300">Nombre</th>
                    <th scope="col" class="px-4 py-3 text-xs font-semibold uppercase tracking-wider text-slate-600 dark:text-slate-300">Apellidos</th>
                    <th scope="col" class="px-4 py-3 text-xs font-semibold uppercase tracking-wider text-slate-600 dark:text-slate-300">Puesto</th>
                    <th scope="col" class="px-4 py-3 text-xs font-semibold uppercase tracking-wider text-slate-600 dark:text-slate-300">Teléfono</th>
                    <th scope="col" class="px-4 py-3 text-xs font-semibold uppercase tracking-wider text-slate-600 dark:text-slate-300">Rol de acceso</th>
                    <th scope="col" class="px-4 py-3 text-xs font-semibold uppercase tracking-wider text-slate-600 dark:text-slate-300">Salario</th>
                    <th scope="col" class="px-4 py-3 text-xs font-semibold uppercase tracking-wider text-slate-600 dark:text-slate-300">Turno</th>
                    <th scope="col" class="px-4 py-3 text-xs font-semibold uppercase tracking-wider text-slate-600 dark:text-slate-300">Correo electrónico</th>
                    <th scope="col" class="px-4 py-3 text-center text-xs font-semibold uppercase tracking-wider text-slate-600 dark:text-slate-300">Estado</th>
                    <th scope="col" class="px-4 py-3 text-center text-xs font-semibold uppercase tracking-wider text-slate-600 dark:text-slate-300">Permisos</th>
                    @if ($this->esSuperAdmin())
                        <th scope="col" class="px-5 py-3 text-right text-xs font-semibold uppercase tracking-wider text-slate-600 dark:text-slate-300">Acciones</th>
                    @endif
                </tr>
            </thead>

            <tbody class="divide-y divide-slate-100 dark:divide-slate-700/60">
                @forelse ($empleados as $empleado)
                    @php $rolEmpleado = $empleado->usuario?->roles->first()?->name; @endphp

                    <tr class="transition-colors duration-200 hover:bg-slate-50 dark:hover:bg-slate-700/30">
                        <td class="whitespace-nowrap px-5 py-4">
                            <div class="flex items-center gap-3">
                                <span class="flex size-9 shrink-0 items-center justify-center rounded-full bg-amber-500/10 text-xs font-bold text-amber-700 dark:bg-amber-500/20 dark:text-amber-400">
                                    {{ Str::of($empleado->nombre)->substr(0, 1)->upper() }}{{ Str::of($empleado->apellidos)->substr(0, 1)->upper() }}
                                </span>
                                <span class="font-semibold text-slate-900 dark:text-white">{{ $empleado->nombre }}</span>
                            </div>
                        </td>
                        <td class="whitespace-nowrap px-4 py-4 text-slate-700 dark:text-slate-300">{{ $empleado->apellidos }}</td>
                        <td class="whitespace-nowrap px-4 py-4">
                            @if ($empleado->puesto)
                                <span class="inline-flex items-center gap-1.5 rounded-full bg-slate-100 px-2.5 py-1 text-xs font-semibold text-slate-700 dark:bg-slate-700/60 dark:text-slate-200">
                                    <flux:icon.briefcase class="size-3.5 text-slate-400" />
                                    {{ $empleado->puesto }}
                                </span>
                            @else
                                <span class="text-slate-400 dark:text-slate-500">—</span>
                            @endif
                        </td>
                        <td class="whitespace-nowrap px-4 py-4 text-slate-600 dark:text-slate-400">{{ $empleado->telefono ?? '—' }}</td>
                        <td class="whitespace-nowrap px-4 py-4">
                            @if (! $empleado->id_usuario)
                                <flux:badge color="zinc" size="sm" class="!bg-zinc-100 !text-zinc-600 !font-semibold dark:!bg-zinc-700/60 dark:!text-zinc-300">
                                    Sin acceso
                                </flux:badge>
                            @elseif ($rolEmpleado)
                                <flux:badge color="amber" size="sm" class="!bg-amber-100 !text-amber-800 !font-semibold dark:!bg-amber-500/20 dark:!text-amber-400">
                                    {{ Str::headline($rolEmpleado) }}
                                </flux:badge>
                            @else
                                <span class="text-slate-400 dark:text-slate-500">Sin rol</span>
                            @endif
                        </td>
                        <td class="whitespace-nowrap px-4 py-4 text-slate-700 dark:text-slate-300">
                            {{ $empleado->salario !== null ? '$ '.number_format((float) $empleado->salario, 2) : '—' }}
                        </td>
                        <td class="whitespace-nowrap px-4 py-4">
                            @if ($empleado->turno)
                                <span class="inline-flex items-center gap-1.5 text-slate-700 dark:text-slate-300">
                                    <flux:icon.clock class="size-3.5 text-slate-400 dark:text-slate-500" />
                                    {{ $empleado->turno }}
                                </span>
                            @else
                                <span class="text-slate-400 dark:text-slate-500">—</span>
                            @endif
                        </td>
                        <td class="whitespace-nowrap px-4 py-4 text-slate-600 dark:text-slate-400">{{ $empleado->correo_electronico ?? $empleado->usuario?->email ?? '—' }}</td>
                        <td class="whitespace-nowrap px-4 py-4 text-center">
                            <x-estado-badge :estado="$empleado->esta_activo ? 'Activo' : 'Inactivo'" />
                        </td>
                        <td class="whitespace-nowrap px-4 py-4 text-center">
                            @if ($this->esSuperAdmin())
                                <flux:button
                                    type="button"
                                    size="sm"
                                    variant="outline"
                                    icon="cog-6-tooth"
                                    tooltip="Configurar permisos"
                                    aria-label="Configurar permisos del empleado {{ $empleado->nombre }} {{ $empleado->apellidos }}"
                                    wire:click="abrirModalPermisos({{ $empleado->id_empleado }})"
                                    class="transition-all duration-200 hover:scale-105 active:scale-95"
                                />
                            @else
                                <span class="inline-flex items-center text-slate-400 dark:text-slate-600" title="Solo el Super Admin puede configurar permisos">
                                    <flux:icon.lock-closed class="size-4" />
                                </span>
                            @endif
                        </td>

                        @if ($this->esSuperAdmin())
                            <td class="whitespace-nowrap px-5 py-4">
                                <div class="flex items-center justify-end gap-2">
                                    <flux:button type="button" size="sm" variant="outline" color="blue" icon="pencil-square" tooltip="Editar empleado" aria-label="Editar empleado" wire:click="editar({{ $empleado->id_empleado }})" class="transition-all duration-200 hover:scale-105 active:scale-95" />

                                    <flux:button type="button" size="sm" variant="outline" color="emerald" icon="power" tooltip="{{ $empleado->esta_activo ? 'Desactivar empleado' : 'Activar empleado' }}" aria-label="{{ $empleado->esta_activo ? 'Desactivar empleado' : 'Activar empleado' }}" wire:click="toggleActivo({{ $empleado->id_empleado }})" class="transition-all duration-200 hover:scale-105 active:scale-95" />

                                    <flux:button type="button" size="sm" variant="outline" color="red" icon="trash" tooltip="Eliminar empleado" aria-label="Eliminar empleado" wire:click="eliminar({{ $empleado->id_empleado }})" wire:confirm="¿Eliminar este empleado?" class="transition-all duration-200 hover:scale-105 active:scale-95" />
                                </div>
                            </td>
                        @endif
                    </tr>
                @empty
                    <tr>
                        <td colspan="{{ $this->esSuperAdmin() ? 11 : 10 }}" class="px-5 py-14 text-center">
                            <div class="flex flex-col items-center gap-3">
                                <span class="flex size-14 items-center justify-center rounded-2xl bg-slate-100 dark:bg-slate-700/40">
                                    <flux:icon.users class="size-7 text-slate-400 dark:text-slate-500" />
                                </span>
                                <p class="text-sm font-semibold text-slate-700 dark:text-slate-200">No hay empleados registrados</p>
                                <p class="text-sm text-slate-500 dark:text-slate-400">No se encontraron empleados que coincidan con la búsqueda.</p>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="flex flex-col border-t border-slate-100 px-5 py-4 sm:flex-row sm:items-center sm:justify-center dark:border-slate-700/60">
        <div class="flex w-full justify-center overflow-x-auto">
            {{ $empleados->links() }}
        </div>
    </div>
</div>