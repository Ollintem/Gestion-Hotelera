{{-- ======================================================
     ENCABEZADO
     ====================================================== --}}

<div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
    <div>
        <flux:heading size="xl" class="!text-2xl !font-bold !text-slate-900">Pagos y estado de cuenta</flux:heading>
        <flux:subheading class="!font-medium !text-slate-600">
            Registra abonos, consulta el saldo de cada folio y emite comprobantes. Total cobrado:
            <span class="font-semibold text-slate-800">${{ number_format($total, 2) }}</span>
        </flux:subheading>
    </div>

    <flux:button type="button" variant="primary" wire:click="crear" class="shrink-0">
        <flux:icon.plus class="size-4" />
        Nuevo pago
    </flux:button>
</div>
