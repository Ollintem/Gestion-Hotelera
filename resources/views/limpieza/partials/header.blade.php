{{-- ======================================================
     ENCABEZADO
     ====================================================== --}}

<div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
    <div>
        <flux:heading size="xl" class="!text-slate-900 !font-bold text-2xl">Limpieza</flux:heading>
        <flux:subheading class="!text-slate-600 !font-medium">
            Gestiona las tareas de limpieza de las habitaciones.
        </flux:subheading>
    </div>

    @if(auth()->user()->hasRole('super-admin') || auth()->user()->hasRole('gerente') || auth()->user()->hasRole('recepcionista') || auth()->user()->rol === 'admin' || auth()->user()->rol === 'Administrador')
    <flux:button type="button" variant="primary" wire:click="crear" class="shrink-0">
        <flux:icon.plus class="size-4" />
        Nueva tarea
    </flux:button>
    @endif
</div>