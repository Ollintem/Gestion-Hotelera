<?php

namespace App\Livewire;

use App\Models\Temporada;
use Illuminate\View\View;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.app')]
class Temporadas extends Component
{
    public string $pagina = 'index';

    public bool $mostrarModal = false;

    public ?int $temporadaId = null;

    public string $nombre = '';

    public string $fecha_inicio = '';

    public string $fecha_fin = '';

    public string $multiplicador_precio = '1.00';

    public ?string $mensajeExito = null;

    public function render(): View
    {
        $temporadas = Temporada::orderBy('fecha_inicio')->get();

        return match ($this->pagina) {
            'crear' => view('temporadas.create', ['temporadas' => $temporadas]),
            'editar' => view('temporadas.edit', ['temporadas' => $temporadas]),
            default => view('temporadas.index', ['temporadas' => $temporadas]),
        };
    }

    public function crear(): void
    {
        $this->reset(['temporadaId', 'nombre', 'fecha_inicio', 'fecha_fin']);
        $this->multiplicador_precio = '1.00';
        $this->resetValidation();
        $this->reset('mensajeExito');
        $this->pagina = 'crear';
    }

    public function editar(int $id): void
    {
        $temporada = Temporada::findOrFail($id);

        $this->temporadaId = $temporada->id;
        $this->nombre = $temporada->nombre;
        $this->fecha_inicio = $temporada->fecha_inicio->format('Y-m-d');
        $this->fecha_fin = $temporada->fecha_fin->format('Y-m-d');
        $this->multiplicador_precio = (string) $temporada->multiplicador_precio;
        $this->resetValidation();
        $this->reset('mensajeExito');
        $this->pagina = 'editar';
    }

    public function cerrarModal(): void
    {
        $this->pagina = 'index';
        $this->reset(['temporadaId', 'nombre', 'fecha_inicio', 'fecha_fin']);
        $this->resetValidation();
    }

    public function guardar(): void
    {
        $this->validate([
            'nombre' => ['required', 'string', 'max:100'],
            'fecha_inicio' => ['required', 'date'],
            'fecha_fin' => ['required', 'date', 'after_or_equal:fecha_inicio'],
            'multiplicador_precio' => ['required', 'numeric', 'min:0.01'],
        ]);

        $datos = [
            'nombre' => $this->nombre,
            'fecha_inicio' => $this->fecha_inicio,
            'fecha_fin' => $this->fecha_fin,
            'multiplicador_precio' => $this->multiplicador_precio,
        ];

        if ($this->temporadaId) {
            Temporada::findOrFail($this->temporadaId)->update($datos);
            $this->mensajeExito = 'Temporada actualizada correctamente.';
        } else {
            Temporada::create($datos);
            $this->mensajeExito = 'Temporada creada correctamente.';
        }

        $this->cerrarModal();
    }

    public function eliminar(int $id): void
    {
        Temporada::findOrFail($id)->delete();

        $this->mensajeExito = 'Temporada eliminada correctamente.';
    }
}
