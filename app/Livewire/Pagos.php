<?php

namespace App\Livewire;

use App\Models\Pago;
use App\Models\Reserva;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Component;

/**
 * Módulo de pagos y estado de cuenta. El componente es el único dueño del
 * estado: el folio que se consulta, el formulario del diálogo y el resumen
 * financiero que se pinta en el panel.
 *
 * El panel muestra el estado de cuenta de la reservación seleccionada (total
 * hospedaje más consumos, abonado y saldo pendiente). Al registrar un pago que
 * deja el saldo en cero sobre una reservación en curso, la estadía se cierra
 * automáticamente reutilizando `Reserva::realizarCheckOut`.
 */
#[Layout('components.layouts.app')]
class Pagos extends Component
{
    /**
     * Reservación cuyo estado de cuenta se consulta en el panel.
     */
    public ?int $reservaSeleccionadaId = null;

    public bool $mostrarModal = false;

    public ?int $pagoId = null;

    public string $reserva_id = '';

    public string $monto = '';

    public string $metodo_pago = 'Efectivo';

    public string $referencia = '';

    public string $estado = Pago::ESTADO_COMPLETADO;

    public string $fecha_pago = '';

    public string $notas = '';

    /**
     * Autoriza registrar un importe mayor al saldo pendiente (anticipo).
     */
    public bool $pagoAnticipado = false;

    public function mount(): void
    {
        $this->reservaSeleccionadaId = $this->reservaInicial();
    }

    public function render(): View
    {
        return view('pagos.index', [
            'pagos' => Pago::with(['reserva.cliente', 'usuario'])->orderByDesc('id')->get(),
            'reservas' => Reserva::with('cliente')->orderByDesc('check_in')->orderByDesc('id')->get(),
            'metodos' => Pago::METODOS,
            'estados' => Pago::ESTADOS,
            'total' => (float) Pago::where('estado', Pago::ESTADO_COMPLETADO)->sum('monto'),
        ]);
    }

    /**
     * Reservación que el panel resuelve cuando no hay selección explícita:
     * prefiere una estancia en curso y, si no la hay, la última registrada.
     */
    private function reservaInicial(): ?int
    {
        return Reserva::query()
            ->whereIn('estado', ['Confirmada', 'Pendiente'])
            ->orderByRaw("case when estado = 'Confirmada' then 0 else 1 end")
            ->orderByDesc('check_in')
            ->value('id')
            ?? Reserva::query()->orderByDesc('id')->value('id');
    }

    /**
     * Folio del panel con sus cargos y abonos.
     */
    #[Computed]
    public function reserva(): ?Reserva
    {
        if ($this->reservaSeleccionadaId === null) {
            return null;
        }

        return Reserva::with([
            'cliente',
            'habitacionesAsignadas.habitacion',
            'serviciosAsignados.servicio',
            'pagos.usuario',
        ])->find($this->reservaSeleccionadaId);
    }

    /**
     * Cifras del estado de cuenta del folio consultado.
     *
     * @return array{tarifa: float, extras: float, total: float, pagado: float, pendiente: float, estaPagada: bool}|null
     */
    #[Computed]
    public function resumen(): ?array
    {
        $reserva = $this->reserva;

        if ($reserva === null) {
            return null;
        }

        return [
            'tarifa' => $reserva->tarifaHabitaciones(),
            'extras' => $reserva->subtotalServicios(),
            'total' => $reserva->totalConsumos(),
            'pagado' => $reserva->totalPagado(),
            'pendiente' => $reserva->saldoPendiente(),
            'estaPagada' => $reserva->estaPagada(),
        ];
    }

    /**
     * Abre el diálogo con el formulario vacío. Si hay un folio en pantalla, se
     * precarga para no obligar a recepción a elegirlo de nuevo.
     */
    public function crear(): void
    {
        $this->resetFormulario();

        if ($this->reservaSeleccionadaId !== null) {
            $this->reserva_id = (string) $this->reservaSeleccionadaId;
        }

        $this->fecha_pago = now()->format('Y-m-d\TH:i');

        $this->mostrarModal = true;
    }

    public function editar(int $id): void
    {
        $pago = Pago::findOrFail($id);

        $this->resetValidation();

        $this->pagoId = $pago->id;
        $this->reserva_id = (string) $pago->reserva_id;
        $this->monto = number_format((float) $pago->monto, 2, '.', '');
        $this->metodo_pago = $pago->metodo_pago;
        $this->referencia = $pago->referencia ?? '';
        $this->estado = $pago->estado;
        $this->fecha_pago = $pago->fecha_pago?->format('Y-m-d\TH:i') ?? '';
        $this->notas = $pago->notas ?? '';
        $this->pagoAnticipado = false;

        $this->reservaSeleccionadaId = $pago->reserva_id;

        $this->mostrarModal = true;
    }

    public function cerrarModal(): void
    {
        $this->mostrarModal = false;

        $this->resetFormulario();
    }

    /**
     * Guarda el alta o la edición. Cuando el pago deja el saldo en cero sobre
     * una estancia en curso, se realiza el check-out automático.
     */
    public function guardar(): void
    {
        $this->validate([
            'reserva_id' => ['required', 'exists:reservas,id'],
            'monto' => ['required', 'numeric', 'min:0.01', 'max:999999.99'],
            'metodo_pago' => ['required', Rule::in(Pago::METODOS)],
            'estado' => ['required', Rule::in(Pago::ESTADOS)],
            'referencia' => [
                Rule::requiredIf(fn (): bool => in_array($this->metodo_pago, Pago::METODOS_CON_REFERENCIA, true)),
                'nullable',
                'string',
                'max:100',
            ],
            'fecha_pago' => ['nullable', 'date'],
            'notas' => ['nullable', 'string', 'max:500'],
        ]);

        $reserva = Reserva::findOrFail((int) $this->reserva_id);

        if (! $this->puedeCubrirElSaldo($reserva)) {
            return;
        }

        $datos = [
            'reserva_id' => (int) $this->reserva_id,
            'monto' => number_format((float) $this->monto, 2, '.', ''),
            'metodo_pago' => $this->metodo_pago,
            'referencia' => $this->referencia !== '' ? $this->referencia : null,
            'estado' => $this->estado,
            'fecha_pago' => $this->fecha_pago !== '' ? $this->fecha_pago : now(),
            'notas' => $this->notas !== '' ? $this->notas : null,
        ];

        $esNuevo = $this->pagoId === null;
        $checkOutAutomatico = false;

        DB::transaction(function () use ($datos, $esNuevo, &$checkOutAutomatico): void {
            if ($esNuevo) {
                $datos['user_id'] = auth()->id();

                Pago::create($datos);
            } else {
                Pago::findOrFail($this->pagoId)->update($datos);
            }

            $reserva = Reserva::with('habitaciones')->findOrFail((int) $datos['reserva_id']);

            if ($reserva->estado === 'Confirmada' && $reserva->estaPagada()) {
                $reserva->realizarCheckOut();

                $checkOutAutomatico = true;
            }
        });

        $this->reservaSeleccionadaId = (int) $this->reserva_id;

        $this->cerrarModal();

        $this->dispatch('notificacion', mensaje: match (true) {
            $checkOutAutomatico => 'Pago registrado. El saldo quedó en $0.00 y el check-out se realizó automáticamente.',
            $esNuevo => 'Pago registrado correctamente.',
            default => 'Pago actualizado correctamente.',
        });
    }

    public function eliminar(int $id): void
    {
        Pago::findOrFail($id)->delete();

        $this->dispatch('notificacion', mensaje: 'Pago eliminado correctamente.');
    }

    /**
     * Impide abonar más que el saldo pendiente salvo que se trate de un
     * anticipo autorizado. Al editar se descuenta el importe del propio pago
     * para no comparar contra un saldo que ya lo contempla.
     */
    private function puedeCubrirElSaldo(Reserva $reserva): bool
    {
        if ($this->estado !== Pago::ESTADO_COMPLETADO || $this->pagoAnticipado) {
            return true;
        }

        $pagadoVigente = $reserva->totalPagado();

        if ($this->pagoId !== null) {
            $pagoActual = Pago::find($this->pagoId);

            if ($pagoActual !== null && $pagoActual->estado === Pago::ESTADO_COMPLETADO) {
                $pagadoVigente -= (float) $pagoActual->monto;
            }
        }

        $saldoDisponible = round($reserva->totalConsumos() - $pagadoVigente, 2);

        if ((float) $this->monto > $saldoDisponible + 0.001) {
            $this->addError('monto', sprintf(
                'El monto excede el saldo pendiente ($%s). Marca «Pago anticipado» para registrarlo.',
                number_format(max(0.0, $saldoDisponible), 2)
            ));

            return false;
        }

        return true;
    }

    private function resetFormulario(): void
    {
        $this->resetValidation();

        $this->pagoId = null;
        $this->reserva_id = '';
        $this->monto = '';
        $this->metodo_pago = 'Efectivo';
        $this->referencia = '';
        $this->estado = Pago::ESTADO_COMPLETADO;
        $this->fecha_pago = '';
        $this->notas = '';
        $this->pagoAnticipado = false;
    }
}
