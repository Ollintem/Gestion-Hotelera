<?php

namespace App\Livewire;

use App\Models\Gasto;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\View\View;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Validate;
use Livewire\Component;
use Livewire\WithPagination;

/**
 * Módulo de gastos operativos del hotel.
 *
 * La pantalla lista los gastos registrados bajo la tarjeta de gastos totales.
 * El total se calcula con `Gasto::sum('monto')` sobre toda la tabla, nunca a
 * partir de la página visible: agregar, editar o eliminar un gasto actualiza
 * la cifra porque cada operación vuelve a renderizar el componente.
 *
 * Las acciones de alta, edición y borrado exigen los permisos sembrados
 * `gastos.crear`, `gastos.editar` y `gastos.eliminar` además del `gastos.ver`
 * que ya protege la ruta.
 */
#[Layout('components.layouts.app')]
class Gastos extends Component
{
    use WithPagination;

    public string $pagina = 'index';

    public ?int $gastoId = null;

    #[Validate('required|string|max:255')]
    public string $concepto = '';

    #[Validate('required|numeric|min:0.01|max:999999.99')]
    public string $monto = '';

    #[Validate('required|string|max:100')]
    public string $categoria = '';

    #[Validate('required|date')]
    public string $fecha_gasto = '';

    #[Validate('nullable|string|max:1000')]
    public string $observaciones = '';

    public ?string $mensajeExito = null;

    public ?string $mensajeError = null;

    /**
     * Categorías que el sistema admite para clasificar un gasto.
     *
     * @var list<string>
     */
    public const CATEGORIAS = ['Mantenimiento', 'Limpieza', 'Servicios', 'Personal', 'Insumos', 'Otros'];

    public function render(): View
    {
        $datos = [
            'gastos' => $this->gastos(),
            'total' => (float) Gasto::sum('monto'),
            'categorias' => self::CATEGORIAS,
        ];

        return match ($this->pagina) {
            'crear' => view('gastos.create', $datos),
            'editar' => view('gastos.edit', $datos),
            default => view('gastos.index', $datos),
        };
    }

    /**
     * Listado paginado del más reciente al más antiguo. El total de la
     * tarjeta no depende de esta consulta.
     *
     * @return LengthAwarePaginator<Gasto>
     */
    private function gastos(): LengthAwarePaginator
    {
        return Gasto::orderBy('fecha_gasto', 'desc')
            ->orderByDesc('id')
            ->paginate(10, ['*'], 'paginaGastos');
    }

    public function crear(): void
    {
        if (! $this->puede('gastos.crear', 'crear gastos')) {
            return;
        }

        $this->reset(['gastoId', 'concepto', 'monto', 'categoria', 'observaciones']);
        $this->fecha_gasto = now()->format('Y-m-d');
        $this->resetValidation();
        $this->reset(['mensajeExito', 'mensajeError']);
        $this->pagina = 'crear';
    }

    public function editar(int $id): void
    {
        if (! $this->puede('gastos.editar', 'editar gastos')) {
            return;
        }

        $gasto = Gasto::findOrFail($id);

        $this->gastoId = $gasto->id;
        $this->concepto = $gasto->concepto;
        $this->monto = (string) $gasto->monto;
        $this->categoria = $gasto->categoria;
        $this->fecha_gasto = $gasto->fecha_gasto->format('Y-m-d');
        $this->observaciones = (string) $gasto->observaciones;
        $this->resetValidation();
        $this->reset(['mensajeExito', 'mensajeError']);
        $this->pagina = 'editar';
    }

    public function cerrarModal(): void
    {
        $this->pagina = 'index';
        $this->reset(['gastoId', 'concepto', 'monto', 'categoria', 'fecha_gasto', 'observaciones']);
        $this->resetValidation();
    }

    /**
     * Guarda el alta o la edición. La validación de los atributos viene de
     * los atributos `#[Validate]` de cada propiedad.
     */
    public function guardar(): void
    {
        $this->validate();

        if ($this->gastoId !== null && ! $this->puede('gastos.editar', 'editar gastos')) {
            return;
        }

        if ($this->gastoId === null && ! $this->puede('gastos.crear', 'crear gastos')) {
            return;
        }

        $datos = [
            'concepto' => $this->concepto,
            'monto' => $this->monto,
            'categoria' => $this->categoria,
            'fecha_gasto' => $this->fecha_gasto,
            'observaciones' => $this->observaciones !== '' ? $this->observaciones : null,
        ];

        if ($this->gastoId !== null) {
            Gasto::findOrFail($this->gastoId)->update($datos);
            $this->mensajeExito = 'Gasto actualizado correctamente.';
        } else {
            Gasto::create($datos);
            $this->mensajeExito = 'Gasto registrado correctamente.';
        }

        $this->mensajeError = null;
        $this->cerrarModal();
    }

    public function eliminar(int $id): void
    {
        if (! $this->puede('gastos.eliminar', 'eliminar gastos')) {
            return;
        }

        Gasto::findOrFail($id)->delete();

        $this->mensajeExito = 'Gasto eliminado correctamente.';
        $this->mensajeError = null;
    }

    /**
     * Corta la operación cuando el usuario no tiene el permiso indicado.
     * La ruta ya exige `gastos.ver`, pero cada acción tiene el suyo.
     */
    private function puede(string $permiso, string $accion): bool
    {
        if (auth()->user()?->can($permiso)) {
            return true;
        }

        $this->mensajeExito = null;
        $this->mensajeError = 'No tienes permiso para '.$accion.'.';

        return false;
    }
}
