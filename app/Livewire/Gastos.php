<?php

namespace App\Livewire;

use App\Models\Gasto;
use Illuminate\View\View;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.app')]
class Gastos extends Component
{
    public string $pagina = 'index';

    public bool $mostrarModal = false;

    public ?int $gastoId = null;

    public string $concepto = '';

    public string $monto = '';

    public string $categoria = '';

    public string $fecha_gasto = '';

    public ?string $mensajeExito = null;

    public function render(): View
    {
        $datos = [
            'gastos' => Gasto::orderBy('fecha_gasto', 'desc')->get(),
            'total' => (float) Gasto::sum('monto'),
            'categorias' => ['Mantenimiento', 'Limpieza', 'Servicios', 'Personal', 'Insumos', 'Otros'],
        ];

        return match ($this->pagina) {
            'crear' => view('gastos.create', $datos),
            'editar' => view('gastos.edit', $datos),
            default => view('gastos.index', $datos),
        };
    }

    public function crear(): void
    {
        $this->reset(['gastoId', 'concepto', 'monto', 'categoria']);
        $this->fecha_gasto = now()->format('Y-m-d');
        $this->resetValidation();
        $this->reset('mensajeExito');
        $this->pagina = 'crear';
    }

    public function editar(int $id): void
    {
        $gasto = Gasto::findOrFail($id);

        $this->gastoId = $gasto->id;
        $this->concepto = $gasto->concepto;
        $this->monto = (string) $gasto->monto;
        $this->categoria = $gasto->categoria;
        $this->fecha_gasto = $gasto->fecha_gasto->format('Y-m-d');
        $this->resetValidation();
        $this->reset('mensajeExito');
        $this->pagina = 'editar';
    }

    public function cerrarModal(): void
    {
        $this->pagina = 'index';
        $this->reset(['gastoId', 'concepto', 'monto', 'categoria', 'fecha_gasto']);
        $this->resetValidation();
    }

    public function guardar(): void
    {
        $this->validate([
            'concepto' => ['required', 'string', 'max:255'],
            'monto' => ['required', 'numeric', 'min:0.01'],
            'categoria' => ['required', 'string', 'max:100'],
            'fecha_gasto' => ['required', 'date'],
        ]);

        $datos = [
            'concepto' => $this->concepto,
            'monto' => $this->monto,
            'categoria' => $this->categoria,
            'fecha_gasto' => $this->fecha_gasto,
        ];

        if ($this->gastoId) {
            Gasto::findOrFail($this->gastoId)->update($datos);
            $this->mensajeExito = 'Gasto actualizado correctamente.';
        } else {
            Gasto::create($datos);
            $this->mensajeExito = 'Gasto registrado correctamente.';
        }

        $this->cerrarModal();
    }

    public function eliminar(int $id): void
    {
        Gasto::findOrFail($id)->delete();

        $this->mensajeExito = 'Gasto eliminado correctamente.';
    }
}
