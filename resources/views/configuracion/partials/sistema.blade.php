{{-- ======================================================
     CONFIGURACIÓN GENERAL (SOLO LECTURA)

     Las tarjetas resumen el estado actual del sistema. La
     cabecera y el selector de apartado viven en el index.
     ====================================================== --}}

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
                <dt class="text-zinc-500">Zona horaria</dt>
                <dd class="font-medium">{{ $zonaHoraria }}</dd>
            </div>
            <div class="flex items-center justify-between gap-4">
                <dt class="text-zinc-500">Idioma</dt>
                <dd class="font-medium">{{ strtoupper($idioma) }}</dd>
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
