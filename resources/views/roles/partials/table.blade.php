@use('Illuminate\Support\Str')

{{-- ======================================================
     TABLA DE ROLES
     ====================================================== --}}

<flux:card class="animate-fade-in-up !bg-white shadow-sm [animation-delay:120ms] dark:!bg-slate-800">
    <div class="mb-4 flex flex-col gap-1 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <flux:heading size="lg" class="!text-slate-800 !font-semibold dark:!text-slate-100">Roles registrados</flux:heading>
            <flux:text size="sm">
                {{ $totalRoles }} rol(es) configurados en el sistema.
            </flux:text>
        </div>
    </div>

    <div class="overflow-x-auto">
    <flux:table>
        <flux:table.columns>
            <flux:table.column class="!text-slate-700 !font-semibold uppercase !text-xs tracking-wider dark:!text-slate-300">Rol</flux:table.column>
            <flux:table.column class="!text-slate-700 !font-semibold uppercase !text-xs tracking-wider dark:!text-slate-300">Permisos asignados</flux:table.column>
            <flux:table.column align="center" class="!text-slate-700 !font-semibold uppercase !text-xs tracking-wider dark:!text-slate-300">Usuarios con este rol</flux:table.column>
            @if ($this->esSuperAdmin())
                <flux:table.column align="end" class="!text-slate-700 !font-semibold uppercase !text-xs tracking-wider dark:!text-slate-300">Acciones</flux:table.column>
            @endif
        </flux:table.columns>

        <flux:table.rows>
            @forelse ($this->roles as $rol)
                <flux:table.row :key="$rol->id" class="transition-colors duration-200 hover:bg-slate-50 dark:hover:bg-slate-700/30">
                    <flux:table.cell variant="strong" class="!text-slate-900 !font-medium dark:!text-slate-100">
                        <span class="inline-flex items-center gap-2">
                            <flux:icon.shield-check class="size-4 text-amber-600 dark:text-amber-400" />
                            {{ Str::headline($rol->name) }}
                        </span>
                    </flux:table.cell>

                    <flux:table.cell>
                        <flux:badge color="amber" size="sm" class="!bg-amber-100 !text-amber-800 !font-semibold dark:!bg-amber-500/20 dark:!text-amber-400">
                            {{ $rol->permissions_count }} permiso(s)
                        </flux:badge>
                    </flux:table.cell>

                    <flux:table.cell align="center">
                        <flux:badge color="emerald" size="sm" class="!bg-emerald-100 !text-emerald-800 !font-semibold dark:!bg-emerald-500/20 dark:!text-emerald-400">
                            {{ $rol->users_count }}
                        </flux:badge>
                    </flux:table.cell>

                    @if ($this->esSuperAdmin())
                        <flux:table.cell align="end">
                            <div class="flex items-center justify-end gap-2">
                                <flux:button type="button" size="sm" variant="outline" color="blue" icon="pencil-square" tooltip="Editar rol" aria-label="Editar rol" wire:click="abrirModalEditar({{ $rol->id }})" class="transition-all duration-200 ease-in-out hover:scale-105 hover:shadow-lg active:scale-95" />

                                @if ($this->esRolNoEliminable($rol->name, $rol->id))
                                    <span class="inline-flex items-center gap-1.5 rounded-md bg-slate-100 px-2 py-1 text-xs font-semibold text-slate-500 dark:bg-slate-700/50 dark:text-slate-400" title="{{ $this->motivoRolNoEliminable($rol->name, $rol->id) }}">
                                        <flux:icon.lock-closed class="size-4" />
                                        Protegido
                                    </span>
                                @else
                                    <flux:button type="button" size="sm" variant="outline" color="red" icon="trash" tooltip="Eliminar rol" aria-label="Eliminar rol" wire:click="seleccionarRolAEliminar({{ $rol->id }})" class="transition-all duration-200 ease-in-out hover:scale-105 hover:shadow-lg active:scale-95" />
                                @endif
                            </div>
                        </flux:table.cell>
                    @endif
                </flux:table.row>
            @empty
                <flux:table.row>
                    <flux:table.cell colspan="4" align="center">
                        <p class="py-8 text-slate-500 dark:text-slate-400">No hay roles registrados.</p>
                    </flux:table.cell>
                </flux:table.row>
            @endforelse
        </flux:table.rows>
    </flux:table>
    </div>
</flux:card>