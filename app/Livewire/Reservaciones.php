<?php

namespace App\Livewire;

use App\Models\Cliente;
use App\Models\Habitacion;
use App\Models\Reserva;
use App\Models\ReservaHabitacion;
use Illuminate\View\View;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.app')]
class Reservaciones extends Component
{
    public string $pagina = 'index';

    public bool $mostrarModal = false;

    public ?int $reservaId = null;

    public string $cliente_id = '';

    public string $check_in = '';

    public string $check_out = '';

    public string $estado = 'Pendiente';

    public string $monto_total = '0';

    /** @var list<int> */
    public array $habitacion_ids = [];

    public ?string $mensajeExito = null;

    public function render(): View
    {
        $datos = [
            'reservas' => Reserva::with(['cliente', 'habitaciones'])->latest()->get(),
            'clientes' => Cliente::orderBy('nombre')->get(),
            'habitaciones' => Habitacion::with('tipo')->orderBy('numero_habitacion')->get(),
            'estados' => ['Pendiente', 'Confirmada', 'Cancelada', 'Finalizada'],
        ];

        return match ($this->pagina) {
            'crear' => view('reservaciones.create', $datos),
            'editar' => view('reservaciones.edit', $datos),
            default => view('reservaciones.index', $datos),
        };
    }

    public function crear(): void
    {
        $this->reset(['reservaId', 'cliente_id', 'check_in', 'check_out', 'habitacion_ids']);
        $this->estado = 'Pendiente';
        $this->monto_total = '0';
        $this->resetValidation();
        $this->reset('mensajeExito');
        $this->pagina = 'crear';
    }

    public function editar(int $id): void
    {
        $reserva = Reserva::findOrFail($id);

        $this->reservaId = $reserva->id;
        $this->cliente_id = (string) $reserva->cliente_id;
        $this->check_in = $reserva->check_in->format('Y-m-d');
        $this->check_out = $reserva->check_out->format('Y-m-d');
        $this->estado = $reserva->estado;
        $this->monto_total = (string) $reserva->monto_total;
        $this->habitacion_ids = $reserva->habitaciones()->pluck('habitaciones.id')->all();
        $this->resetValidation();
        $this->reset('mensajeExito');
        $this->pagina = 'editar';
    }

    public function cerrarModal(): void
    {
        $this->pagina = 'index';
        $this->reset(['reservaId', 'cliente_id', 'check_in', 'check_out', 'habitacion_ids']);
        $this->resetValidation();
    }

    public function guardar(): void
    {
        $this->validate([
            'cliente_id' => ['required', 'exists:clientes,id'],
            'check_in' => ['required', 'date'],
            'check_out' => ['required', 'date', 'after_or_equal:check_in'],
            'estado' => ['required', 'in:Pendiente,Confirmada,Cancelada,Finalizada'],
            'monto_total' => ['required', 'numeric', 'min:0'],
            'habitacion_ids' => ['array'],
            'habitacion_ids.*' => ['exists:habitaciones,id'],
        ]);

        $datos = [
            'cliente_id' => $this->cliente_id,
            'check_in' => $this->check_in,
            'check_out' => $this->check_out,
            'estado' => $this->estado,
            'monto_total' => $this->monto_total,
        ];

        if ($this->reservaId) {
            $reserva = Reserva::findOrFail($this->reservaId);
            $reserva->update($datos);
            $this->mensajeExito = 'Reservación actualizada correctamente.';
        } else {
            $datos['user_id'] = auth()->id();
            $reserva = Reserva::create($datos);
            $this->mensajeExito = 'Reservación creada correctamente.';
        }

        $reserva->habitacionesAsignadas()->delete();

        foreach ($this->habitacion_ids as $habitacionId) {
            $habitacion = Habitacion::with('tipo')->find($habitacionId);

            if ($habitacion) {
                ReservaHabitacion::create([
                    'reserva_id' => $reserva->id,
                    'habitacion_id' => $habitacion->id,
                    'precio_por_noche' => $habitacion->tipo?->precio_base ?? 0,
                ]);
            }
        }

        $this->cerrarModal();
    }

    public function eliminar(int $id): void
    {
        $reserva = Reserva::findOrFail($id);
        $reserva->habitacionesAsignadas()->delete();
        $reserva->delete();

        $this->mensajeExito = 'Reservación eliminada correctamente.';
    }
}
