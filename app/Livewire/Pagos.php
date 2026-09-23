<?php

namespace App\Livewire;

use App\Models\Pago;
use App\Models\Reserva;
use Illuminate\View\View;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.app')]
class Pagos extends Component
{
    public string $pagina = 'index';

    public bool $mostrarModal = false;

    public ?int $pagoId = null;

    public string $reserva_id = '';

    public string $monto = '';

    public string $metodo_pago = 'Efectivo';

    public string $fecha_pago = '';

    public string $notas = '';

    public ?string $mensajeExito = null;

    public function render(): View
    {
        $datos = [
            'pagos' => Pago::with(['reserva.cliente'])->latest()->get(),
            'reservas' => Reserva::with('cliente')->latest()->get(),
            'metodos' => ['Efectivo', 'Tarjeta', 'Transferencia'],
            'total' => (float) Pago::sum('monto'),
        ];

        return match ($this->pagina) {
            'crear' => view('pagos.create', $datos),
            'editar' => view('pagos.edit', $datos),
            default => view('pagos.index', $datos),
        };
    }

    public function crear(): void
    {
        $this->reset(['pagoId', 'reserva_id', 'monto', 'fecha_pago', 'notas']);
        $this->metodo_pago = 'Efectivo';
        $this->resetValidation();
        $this->reset('mensajeExito');
        $this->pagina = 'crear';
    }

    public function editar(int $id): void
    {
        $pago = Pago::findOrFail($id);

        $this->pagoId = $pago->id;
        $this->reserva_id = (string) $pago->reserva_id;
        $this->monto = (string) $pago->monto;
        $this->metodo_pago = $pago->metodo_pago;
        $this->fecha_pago = $pago->fecha_pago?->format('Y-m-d\TH:i') ?? '';
        $this->notas = $pago->notas ?? '';
        $this->resetValidation();
        $this->reset('mensajeExito');
        $this->pagina = 'editar';
    }

    public function cerrarModal(): void
    {
        $this->pagina = 'index';
        $this->reset(['pagoId', 'reserva_id', 'monto', 'fecha_pago', 'notas']);
        $this->resetValidation();
    }

    public function guardar(): void
    {
        $this->validate([
            'reserva_id' => ['required', 'exists:reservas,id'],
            'monto' => ['required', 'numeric', 'min:0.01'],
            'metodo_pago' => ['required', 'in:Efectivo,Tarjeta,Transferencia'],
            'fecha_pago' => ['nullable', 'date'],
            'notas' => ['nullable', 'string'],
        ]);

        $datos = [
            'reserva_id' => $this->reserva_id,
            'monto' => $this->monto,
            'metodo_pago' => $this->metodo_pago,
            'fecha_pago' => $this->fecha_pago ?: now(),
            'notas' => $this->notas ?: null,
        ];

        if ($this->pagoId) {
            Pago::findOrFail($this->pagoId)->update($datos);
            $this->mensajeExito = 'Pago actualizado correctamente.';
        } else {
            Pago::create($datos);
            $this->mensajeExito = 'Pago registrado correctamente.';
        }

        $this->cerrarModal();
    }

    public function eliminar(int $id): void
    {
        Pago::findOrFail($id)->delete();

        $this->mensajeExito = 'Pago eliminado correctamente.';
    }
}
