{{-- ======================================================
     RESERVACIONES · FILA
     ====================================================== --}}

@php
    $cliente = $reserva->cliente;

    $iniciales = mb_strtoupper(
        mb_substr((string) $cliente?->nombre, 0, 1).mb_substr((string) $cliente?->apellido, 0, 1)
    ) ?: '—';

    $estadoClases = match ($reserva->estado) {
        'Confirmada' => 'bg-emerald-50 text-emerald-700 ring-emerald-600/20 dark:bg-emerald-500/10 dark:text-emerald-400 dark:ring-emerald-500/30',
        'Pendiente' => 'bg-amber-50 text-amber-700 ring-amber-600/20 dark:bg-amber-500/10 dark:text-amber-400 dark:ring-amber-500/30',
        'Cancelada' => 'bg-rose-50 text-rose-700 ring-rose-600/20 dark:bg-rose-500/10 dark:text-rose-400 dark:ring-rose-500/30',
        default => 'bg-zinc-100 text-zinc-600 ring-zinc-500/20 dark:bg-zinc-500/10 dark:text-zinc-300 dark:ring-zinc-500/30',
    };

    $pagada = $this->estaPagada($reserva);
    $pagoClases = $pagada
        ? 'bg-emerald-50 text-emerald-700 ring-emerald-600/20 dark:bg-emerald-500/10 dark:text-emerald-400 dark:ring-emerald-500/30'
        : 'bg-amber-50 text-amber-700 ring-amber-600/20 dark:bg-amber-500/10 dark:text-amber-400 dark:ring-amber-500/30';
@endphp

<tr class="transition-colors duration-150 hover:bg-slate-50/80 dark:hover:bg-zinc-700/30">
    {{-- CÓDIGO --}}
    <td class="whitespace-nowrap px-4 py-4">
        <span class="font-mono text-xs font-semibold tracking-tight text-slate-500 dark:text-slate-400">
            {{ $this->codigo($reserva) }}
        </span>
    </td>

    {{-- CLIENTE --}}
    <td class="px-4 py-4">
        <div class="flex items-center gap-3">
            <span class="flex size-9 shrink-0 items-center justify-center rounded-full bg-slate-100 text-xs font-semibold text-slate-600 dark:bg-zinc-700 dark:text-zinc-200">
                {{ $iniciales }}
            </span>
            <div class="min-w-0">
                <p class="truncate text-sm font-semibold text-slate-900 dark:text-white">
                    {{ $reserva->cliente?->nombreCompleto() ?? '—' }}
                </p>
                @if (filled($reserva->cliente?->email))
                    <p class="truncate text-xs text-slate-500 dark:text-slate-400">
                        {{ $reserva->cliente->email }}
                    </p>
                @endif
            </div>
        </div>
    </td>

    {{-- HABITACIÓN --}}
    <td class="px-4 py-4">
        <div class="flex flex-wrap items-center gap-1.5">
            @forelse ($reserva->habitaciones as $habitacion)
                <span class="inline-flex items-center rounded-lg bg-slate-100 px-2 py-1 text-xs font-semibold text-slate-700 dark:bg-zinc-700 dark:text-zinc-200">
                    #{{ $habitacion->numero_habitacion }}
                </span>
            @empty
                <span class="text-sm text-slate-400 dark:text-slate-500">—</span>
            @endforelse
        </div>
    </td>

    {{-- CHECK-IN --}}
    <td class="whitespace-nowrap px-4 py-4 text-sm text-slate-700 dark:text-slate-300">
        {{ $reserva->check_in?->format('d/m/Y') ?? '—' }}
    </td>

    {{-- CHECK-OUT --}}
    <td class="whitespace-nowrap px-4 py-4 text-sm text-slate-700 dark:text-slate-300">
        {{ $reserva->check_out?->format('d/m/Y') ?? '—' }}
    </td>

    {{-- HUÉSPEDES --}}
    <td class="whitespace-nowrap px-4 py-4 text-center">
        <span class="inline-flex items-center gap-1.5 text-sm font-medium text-slate-700 dark:text-slate-300">
            <flux:icon.user class="size-3.5 text-slate-400 dark:text-slate-500" />
            {{ $this->huespedes($reserva) }}
        </span>
    </td>

    {{-- TOTAL --}}
    <td class="whitespace-nowrap px-4 py-4 text-right text-sm font-semibold text-slate-900 dark:text-white">
        ${{ number_format((float) $reserva->monto_total, 2) }}
    </td>

    {{-- ESTADO --}}
    <td class="whitespace-nowrap px-4 py-4">
        <span class="inline-flex items-center gap-1.5 rounded-full px-2.5 py-1 text-xs font-semibold ring-1 ring-inset {{ $estadoClases }}">
            <span class="size-1.5 rounded-full bg-current"></span>
            {{ $reserva->estado }}
        </span>
    </td>

    {{-- PAGO --}}
    <td class="whitespace-nowrap px-4 py-4">
        <span class="inline-flex items-center gap-1.5 rounded-full px-2.5 py-1 text-xs font-semibold ring-1 ring-inset {{ $pagoClases }}">
            <span class="size-1.5 rounded-full bg-current"></span>
            {{ $pagada ? 'Pagado' : 'Pendiente' }}
        </span>
    </td>

    {{-- ACCIONES --}}
    <td class="whitespace-nowrap px-4 py-4 text-right">
        @include('livewire.reservaciones.partials.dropdown-acciones', ['reserva' => $reserva])
    </td>
</tr>
