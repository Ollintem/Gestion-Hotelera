<div class="mb-6">
    <flux:heading size="xl" class="!text-slate-900 !font-bold text-2xl">Configuración</flux:heading>
    <flux:subheading class="!text-slate-600 !font-medium">
        Información general del sistema Novastay.
    </flux:subheading>
</div>

<div class="grid gap-4 lg:grid-cols-2">
    <flux:card>
        <flux:heading size="lg" class="mb-4 !text-slate-800 !font-semibold">Sistema</flux:heading>

        <dl class="space-y-3 text-sm">
            <div class="flex items-center justify-between gap-4">
                <dt class="text-zinc-500">Aplicación</dt>
                <dd class="font-medium">{{ $nombreApp }}</dd>
            </div>
            <div class="flex items-center justify-between gap-4">
                <dt class="text-zinc-500">Entorno</dt>
                <dd>
                    <flux:badge color="amber" size="sm" class="!bg-amber-100 !text-amber-800 !font-semibold">{{ $entorno }}</flux:badge>
                </dd>
            </div>
            <div class="flex items-center justify-between gap-4">
                <dt class="text-zinc-500">Temporada vigente</dt>
                <dd class="font-medium">
                    @if ($temporadaVigente)
                        {{ $temporadaVigente->nombre }} ({{ $temporadaVigente->multiplicadorEnTexto() }})
                    @else
                        Sin temporada vigente
                    @endif
                </dd>
            </div>
            @if ($temporadaVigente)
                <div class="flex items-center justify-between gap-4">
                    <dt class="text-zinc-500">Precio vigente</dt>
                    <dd class="font-medium text-emerald-600 tabular-nums">
                        {{ $temporadaVigente->precioEfectivoEnPesos() }}
                    </dd>
                </div>
            @endif
            <div class="flex items-center justify-between gap-4">
                <dt class="text-zinc-500">Sesión iniciada como</dt>
                <dd class="font-medium">{{ auth()->user()?->name }}</dd>
            </div>
        </dl>
    </flux:card>

    <flux:card>
        <flux:heading size="lg" class="mb-4 !text-slate-800 !font-semibold">Registros del sistema</flux:heading>

        <dl class="space-y-3 text-sm">
            <div class="flex items-center justify-between gap-4">
                <dt class="text-zinc-500">Usuarios</dt>
                <dd><flux:badge color="zinc" size="sm" class="!bg-zinc-100 !text-zinc-800 !font-semibold">{{ $totalUsuarios }}</flux:badge></dd>
            </div>
            <div class="flex items-center justify-between gap-4">
                <dt class="text-zinc-500">Roles</dt>
                <dd><flux:badge color="zinc" size="sm" class="!bg-zinc-100 !text-zinc-800 !font-semibold">{{ $totalRoles }}</flux:badge></dd>
            </div>
            <div class="flex items-center justify-between gap-4">
                <dt class="text-zinc-500">Permisos</dt>
                <dd><flux:badge color="zinc" size="sm" class="!bg-zinc-100 !text-zinc-800 !font-semibold">{{ $totalPermisos }}</flux:badge></dd>
            </div>
            <div class="flex items-center justify-between gap-4">
                <dt class="text-zinc-500">Habitaciones</dt>
                <dd><flux:badge color="zinc" size="sm" class="!bg-zinc-100 !text-zinc-800 !font-semibold">{{ $totalHabitaciones }}</flux:badge></dd>
            </div>
            <div class="flex items-center justify-between gap-4">
                <dt class="text-zinc-500">Clientes</dt>
                <dd><flux:badge color="zinc" size="sm" class="!bg-zinc-100 !text-zinc-800 !font-semibold">{{ $totalClientes }}</flux:badge></dd>
            </div>
            <div class="flex items-center justify-between gap-4">
                <dt class="text-zinc-500">Reservaciones</dt>
                <dd><flux:badge color="zinc" size="sm" class="!bg-zinc-100 !text-zinc-800 !font-semibold">{{ $totalReservas }}</flux:badge></dd>
            </div>
        </dl>
    </flux:card>
</div>

<flux:card class="mt-4">
    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <flux:heading size="lg" class="!text-slate-800 !font-semibold">Cuenta y apariencia</flux:heading>
            <flux:text size="sm" class="!text-slate-600 !font-semibold">Gestiona tu perfil, contraseña y tema desde los ajustes de cuenta.</flux:text>
        </div>

        <div class="flex shrink-0 flex-wrap gap-2">
            <flux:button variant="outline" :href="route('settings.profile')" wire:navigate>
                Mi perfil
            </flux:button>
            <flux:button variant="outline" :href="route('settings.appearance')" wire:navigate>
                Apariencia
            </flux:button>
        </div>
    </div>
</flux:card>
