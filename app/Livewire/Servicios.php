<?php

namespace App\Livewire;

use App\Models\Categoria;
use App\Models\Reserva;
use App\Models\ReservaServicio;
use App\Models\Servicio;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\View\View;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

/**
 * Contenedor del módulo de servicios. Es el único dueño del estado (búsqueda,
 * filtro de categoría, formulario del diálogo y mensajes) y de los dos diálogos
 * de la pantalla: el alta y edición del catálogo, y el cargo de un consumo al
 * folio de una habitación ocupada.
 *
 * El diálogo se abre con `mostrarModal` y no con un evento de Flux: el
 * componente carga los datos y levanta el diálogo en la misma respuesta, de modo
 * que nunca se edita a ciegas ni se pierde el valor de un servicio ya
 * clasificado en una categoría que hoy está inactiva.
 *
 * La clasificación del catálogo vive en la tabla `categorias` y se filtra por
 * `categoria_id`, no por nombre: una categoría se puede desactivar y renombrar
 * sin reescribir los servicios que la usan.
 */
#[Layout('components.layouts.app')]
class Servicios extends Component
{
    use WithPagination;

    public const POR_PAGINA = 10;

    #[Url(as: 'buscar', except: '')]
    public string $search = '';

    /**
     * Identificador de la categoría aplicada, o cadena vacía para el catálogo
     * completo. Un identificador que ya no existe devuelve el catálogo entero.
     */
    #[Url(as: 'categoria', except: '')]
    public string $categoriaFiltro = '';

    public bool $mostrarModal = false;

    public ?int $servicioId = null;

    public string $nombre = '';

    public string $descripcion = '';

    public ?int $categoria_id = null;

    public string $precio = '';

    public function render(): View
    {
        return view('servicios.index', [
            'servicios' => $this->serviciosPaginados(),
            'categorias' => $this->categorias(),
            'categoriasModal' => $this->categoriasModal(),
            'kpis' => $this->kpis(),
        ]);
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedCategoriaFiltro(): void
    {
        $this->resetPage();
    }

    /**
     * Devuelve el buscador y el filtro de categoría a su estado inicial.
     */
    public function limpiarFiltros(): void
    {
        $this->reset('search', 'categoriaFiltro');

        $this->resetPage();
    }

    /**
     * Abre el diálogo con el formulario vacío para dar de alta un servicio.
     */
    public function crear(): void
    {
        $this->resetFormulario();

        $this->mostrarModal = true;
    }

    /**
     * Abre el diálogo con los datos del servicio indicado. Cargar y mostrar
     * ocurren en la misma petición, que es lo que exige el botón de la fila: un
     * solo clic y el formulario aparece con la categoría y el precio reales.
     */
    public function editar(int $id): void
    {
        $servicio = Servicio::findOrFail($id);

        $this->resetValidation();

        $this->servicioId = $servicio->id;
        $this->nombre = $servicio->nombre;
        $this->descripcion = $servicio->descripcion ?? '';
        $this->categoria_id = $servicio->categoria_id;
        $this->precio = number_format((float) $servicio->precio, 2, '.', '');

        $this->mostrarModal = true;
    }

    public function cerrarModal(): void
    {
        $this->resetFormulario();

        $this->mostrarModal = false;
    }

    /**
     * Guarda el alta o la edición. El nombre es texto libre: lo que recepción
     * escribe en el mostrador es lo que queda registrado.
     */
    public function guardar(): void
    {
        $this->validate([
            'nombre' => ['required', 'string', 'max:100'],
            'descripcion' => ['nullable', 'string', 'max:500'],
            'categoria_id' => ['required', 'integer', 'exists:categorias,id'],
            'precio' => ['required', 'numeric', 'min:0.01', 'max:999999.99'],
        ]);

        $servicio = $this->servicioId === null
            ? new Servicio
            : Servicio::findOrFail($this->servicioId);

        $esNuevo = ! $servicio->exists;

        $servicio->fill([
            'nombre' => $this->nombre,
            'descripcion' => $this->descripcion !== '' ? $this->descripcion : null,
            'categoria_id' => $this->categoria_id,
            'precio' => $this->precio,
        ])->save();

        $this->resetFormulario();

        $this->mostrarModal = false;

        $this->dispatch(
            'notificacion',
            mensaje: $esNuevo
                ? 'Servicio creado correctamente.'
                : 'Servicio actualizado correctamente.'
        );
    }

    /**
     * Descarta un servicio del catálogo. Un servicio con cargos en folios se
     * conserva: los cargos históricos deben seguir señalando a un servicio real,
     * y la llave foránea lo impide.
     */
    public function eliminar(int $id): void
    {
        $servicio = Servicio::findOrFail($id);

        if ($servicio->reservasServicio()->exists()) {
            $this->dispatch(
                'notificacion',
                mensaje: 'No se puede eliminar: el servicio ya tiene cargos registrados en folios.'
            );

            return;
        }

        $servicio->delete();

        $this->dispatch('notificacion', mensaje: 'Servicio eliminado correctamente.');
    }

    /**
     * Retira un servicio del catálogo sin borrarlo, porque los cargos que ya lo
     * tienen siguen necesitando un servicio real. Volverlo a activar lo devuelve
     * a la lista.
     */
    public function alternarActivo(int $id): void
    {
        $servicio = Servicio::findOrFail($id);

        $servicio->update(['activo' => ! $servicio->activo]);

        $this->dispatch(
            'notificacion',
            mensaje: $servicio->activo
                ? 'Servicio activado correctamente.'
                : 'Servicio desactivado correctamente.'
        );
    }

    /**
     * Catálogo paginado. La búsqueda cubre el nombre y la descripción para que
     * recepción encuentre un servicio por como lo escribe en el mostrador.
     */
    private function serviciosPaginados(): LengthAwarePaginator
    {
        return Servicio::with('clasificacion')
            ->withCount('reservasServicio')
            ->when($this->search !== '', function ($query): void {
                $termino = '%'.trim($this->search).'%';

                $query->where(function ($sub) use ($termino): void {
                    $sub->where('nombre', 'like', $termino)
                        ->orWhere('descripcion', 'like', $termino);
                });
            })
            ->when($this->categoriaFiltro !== '', function ($query): void {
                $query->where('categoria_id', $this->categoriaFiltro);
            })
            ->orderBy('nombre')
            ->paginate(self::POR_PAGINA);
    }

    /**
     * Categorías que ofrece el filtro. Las inactivas se omiten: filtrar por una
     * categoría retirada llevaría a un catálogo que ya no se administra.
     *
     * @return Collection<int, Categoria>
     */
    private function categorias(): Collection
    {
        return Categoria::query()
            ->activas()
            ->ordenadasPorNombre()
            ->get();
    }

    /**
     * El formulario sí lista las inactivas, para no perder la clasificación de
     * un servicio que ya está dado de alta bajo una de ellas.
     *
     * @return Collection<int, Categoria>
     */
    private function categoriasModal(): Collection
    {
        return Categoria::query()
            ->ordenadasPorNombre()
            ->get();
    }

    /**
     * Cifras de la cabecera. Describen el hotel completo y no la selección del
     * filtro: acotar el catálogo no debe cambiar lo que el hotel tiene dado de
     * alta.
     *
     * @return array{totalServicios: int, categoriasActivas: int, cargosRegistrados: int, consumosFacturados: float}
     */
    private function kpis(): array
    {
        return [
            'totalServicios' => Servicio::count(),
            'categoriasActivas' => Categoria::query()->activas()->count(),
            'cargosRegistrados' => ReservaServicio::count(),
            'consumosFacturados' => (float) round((float) ReservaServicio::query()
                ->whereHas('reserva', fn ($query) => $query->where('estado', Reserva::ESTADO_FINALIZADA))
                ->sum('subtotal'), 2),
        ];
    }

    private function resetFormulario(): void
    {
        $this->resetValidation();

        $this->servicioId = null;
        $this->nombre = '';
        $this->descripcion = '';
        $this->categoria_id = null;
        $this->precio = '';
    }
}
