<?php

namespace App\Livewire\Habitaciones;

use App\Models\Habitacion;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\On;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

/**
 * Contenedor del módulo de habitaciones. Es el único dueño del estado global
 * (filtros, vista, modal y mensajes) y delega la presentación en subcomponentes
 * que reciben únicamente los datos que necesitan.
 */
#[Layout('components.layouts.app')]
class Index extends Component
{
    use WithPagination;

    public const POR_PAGINA = 12;

    public const VISTA_CUADRICULA = 'cuadricula';

    public const VISTA_LISTA = 'lista';

    public const FILTRO_TODOS = 'todos';

    /**
     * Opciones de estado y tarjetas KPI del panel. Cada opción agrupa un conjunto
     * de estados reales de la tabla `habitaciones` y define la paleta de su
     * tarjeta de métrica, que es el control que recorta el inventario por
     * estado.
     *
     * @var array<int, array{clave: string, etiqueta: string, estados: list<string>, icono: string, borde: string, iconoFondo: string, iconoTexto: string}>
     */
    public const OPCIONES_ESTADO = [
        [
            'clave' => 'Disponible',
            'etiqueta' => 'Disponibles',
            'estados' => ['Disponible'],
            'icono' => 'check',
            'borde' => 'border-t-emerald-500',
            'iconoFondo' => 'bg-emerald-50',
            'iconoTexto' => 'text-emerald-500',
        ],
        [
            'clave' => 'Ocupada',
            'etiqueta' => 'Ocupadas',
            'estados' => ['Ocupada'],
            'icono' => 'lock-closed',
            'borde' => 'border-t-rose-500',
            'iconoFondo' => 'bg-rose-50',
            'iconoTexto' => 'text-rose-500',
        ],
        [
            'clave' => 'Reservada',
            'etiqueta' => 'Reservadas',
            'estados' => ['Reservada'],
            'icono' => 'calendar-days',
            'borde' => 'border-t-amber-400',
            'iconoFondo' => 'bg-amber-50',
            'iconoTexto' => 'text-amber-500',
        ],
        [
            'clave' => 'Limpieza',
            'etiqueta' => 'Limpieza',
            'estados' => ['Limpieza'],
            'icono' => 'sparkles',
            'borde' => 'border-t-orange-400',
            'iconoFondo' => 'bg-orange-50',
            'iconoTexto' => 'text-orange-500',
        ],
        [
            'clave' => 'Mantenimiento',
            'etiqueta' => 'Mantenimiento',
            'estados' => ['Mantenimiento'],
            'icono' => 'wrench-screwdriver',
            'borde' => 'border-t-slate-500',
            'iconoFondo' => 'bg-slate-100',
            'iconoTexto' => 'text-slate-500',
        ],
    ];

    /**
     * Tarjeta KPI de cierre: total de habitaciones, con filtro "todos".
     *
     * @var array{clave: string, etiqueta: string, estados: list<string>, icono: string, borde: string, iconoFondo: string, iconoTexto: string}
     */
    public const TARJETA_TOTAL = [
        'clave' => self::FILTRO_TODOS,
        'etiqueta' => 'Total',
        'estados' => [],
        'icono' => 'building-office-2',
        'borde' => 'border-t-slate-900',
        'iconoFondo' => 'bg-slate-100',
        'iconoTexto' => 'text-slate-900',
    ];

    #[Url(as: 'buscar', except: '')]
    public string $search = '';

    #[Url(as: 'estado', except: self::FILTRO_TODOS)]
    public string $filtroEstado = self::FILTRO_TODOS;

    #[Url(as: 'vista', except: self::VISTA_CUADRICULA)]
    public string $vista = self::VISTA_CUADRICULA;

    public ?string $mensajeExito = null;

    /**
     * Número de avisos de éxito mostrados. Solo identifica el bloque del aviso
     * en el DOM, de modo que un mensaje nuevo reinicie su temporizador aunque
     * repita el texto del anterior.
     */
    public int $secuenciaMensaje = 0;

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    /**
     * Aplica el filtro de una tarjeta KPI de estado.
     */
    public function filtrarPor(string $clave): void
    {
        $this->filtroEstado = $this->esFiltroValido($clave) ? $clave : self::FILTRO_TODOS;

        $this->resetPage();
        $this->reset('mensajeExito');
    }

    /**
     * Cambia entre la vista cuadrícula y la vista lista.
     */
    public function cambiarVista(string $vista): void
    {
        $this->vista = in_array($vista, [self::VISTA_CUADRICULA, self::VISTA_LISTA], true)
            ? $vista
            : self::VISTA_CUADRICULA;
    }

    /**
     * Elimina una habitación del inventario.
     */
    public function eliminar(int $id): void
    {
        Habitacion::findOrFail($id)->delete();

        $this->mostrarMensajeExito('Habitación eliminada correctamente.');
    }

    /**
     * Cierra el modal tras guardar y muestra el mensaje de éxito. El cierre se
     * emite con el nombre que Flux usa para localizar su <dialog>.
     */
    #[On('habitacion-guardada')]
    public function habitacionGuardada(string $mensaje): void
    {
        $this->dispatch('modal-close', name: 'habitacion-form');

        $this->mostrarMensajeExito($mensaje);
    }

    /**
     * Retira el mensaje de éxito del inventario. Lo dispara el temporizador de
     * Alpine que lo acompaña en la vista, para que no ocupe la cabecera más de
     * tres segundos.
     */
    #[On('mensaje-exito-oculto')]
    public function ocultarMensajeExito(): void
    {
        $this->reset('mensajeExito');
    }

    /**
     * Inventario paginado con la relación del tipo de habitación ya cargada.
     */
    #[Computed]
    public function habitaciones(): LengthAwarePaginator
    {
        return Habitacion::with('tipoHabitacion')
            ->when($this->search !== '', function ($query): void {
                $termino = '%'.trim($this->search).'%';
                $query->where(function ($sub) use ($termino): void {
                    $sub->where('numero_habitacion', 'like', $termino)
                        ->orWhereHas('tipoHabitacion', fn ($tipo) => $tipo->where('nombre', 'like', $termino));
                });
            })
            ->when($this->filtroEstado !== self::FILTRO_TODOS, function ($query): void {
                $query->whereIn('estado', $this->estadosDelFiltroActual());
            })
            ->orderBy('piso')
            ->orderBy('numero_habitacion')
            ->paginate(self::POR_PAGINA);
    }

    #[Computed]
    public function totalHabitaciones(): int
    {
        return Habitacion::count();
    }

    /**
     * Tarjetas KPI de estado con contador dinámico, icono y color propio.
     * La primera tarjeta es el total y aplica el filtro "todos".
     *
     * @return array<int, array{clave: string, etiqueta: string, conteo: int, icono: string, borde: string, iconoFondo: string, iconoTexto: string, activa: bool}>
     */
    #[Computed]
    public function tarjetasEstado(): array
    {
        $conteos = Habitacion::query()
            ->select('estado')
            ->selectRaw('COUNT(*) as total')
            ->groupBy('estado')
            ->pluck('total', 'estado');

        $tarjetas = array_map(function (array $opcion) use ($conteos): array {
            $conteo = array_sum(array_map(
                fn (string $estado): int => (int) $conteos->get($estado, 0),
                $opcion['estados'],
            ));

            return [
                ...$opcion,
                'conteo' => $conteo,
                'activa' => $this->filtroEstado === $opcion['clave'],
            ];
        }, self::OPCIONES_ESTADO);

        array_unshift($tarjetas, [
            ...self::TARJETA_TOTAL,
            'conteo' => $this->totalHabitaciones,
            'activa' => $this->filtroEstado === self::FILTRO_TODOS,
        ]);

        return $tarjetas;
    }

    /**
     * Hay filtros activos cuando la búsqueda no está vacía o el estado no es
     * "todos". Determina el mensaje del estado vacío del inventario.
     */
    #[Computed]
    public function hayFiltrosActivos(): bool
    {
        return $this->search !== '' || $this->filtroEstado !== self::FILTRO_TODOS;
    }

    /**
     * Firma de los datos que consume la barra de filtros. Cambia en cuanto
     * cambia el filtro aplicado, la vista, el total o cualquier contador, y
     * se usa como `wire:key` para remontar el subcomponente con datos frescos.
     */
    #[Computed]
    public function firmaFiltros(): string
    {
        return 'filtros-'.$this->totalHabitaciones.'-'.md5(json_encode([
            $this->filtroEstado,
            $this->vista,
            $this->tarjetasEstado,
        ]));
    }

    /**
     * Clave de la tarjeta de una habitación. Incluye todos los datos que la
     * tarjeta muestra para que se remonte cuando alguno de ellos cambia.
     */
    public function claveTarjeta(Habitacion $habitacion): string
    {
        return 'tarjeta-'.$habitacion->id.'-'.md5(json_encode([
            $habitacion->numero_habitacion,
            $habitacion->estado,
            $habitacion->piso,
            $habitacion->foto,
            $habitacion->tipoHabitacion?->nombre,
            $habitacion->tipoHabitacion?->capacidad,
            $habitacion->tipoHabitacion?->precio_base,
        ]));
    }

    /**
     * Publica un aviso de éxito y avanza la secuencia que lo ata a su
     * temporizador de tres segundos en la vista.
     */
    private function mostrarMensajeExito(string $mensaje): void
    {
        $this->mensajeExito = $mensaje;
        $this->secuenciaMensaje++;
    }

    /**
     * @return list<string>
     */
    private function estadosDelFiltroActual(): array
    {
        foreach (self::OPCIONES_ESTADO as $opcion) {
            if ($opcion['clave'] === $this->filtroEstado) {
                return $opcion['estados'];
            }
        }

        return [$this->filtroEstado];
    }

    private function esFiltroValido(string $clave): bool
    {
        return $clave === self::FILTRO_TODOS
            || in_array($clave, array_column(self::OPCIONES_ESTADO, 'clave'), true);
    }
}
