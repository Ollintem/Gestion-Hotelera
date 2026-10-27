{{-- ======================================================
     ENCABEZADO
     ====================================================== --}}

<div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
    <div>
        <flux:heading size="xl" class="!text-slate-900 !font-bold text-2xl">Gastos</flux:heading>
        <flux:subheading class="!text-slate-600 !font-medium">
            Controla los gastos operativos del hotel.
        </flux:subheading>
    </div>

    @can('gastos.crear')
        <flux:button type="button" variant="primary" wire:click="crear" class="shrink-0 transition-all duration-200 hover:scale-105 active:scale-95">
            <flux:icon.plus class="size-4" />
            Nuevo gasto
        </flux:button>
    @endcan
</div>
