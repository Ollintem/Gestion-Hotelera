<?php

namespace App\Livewire;

use App\Models\Habitacion;
use App\Models\TipoHabitacion;
use Illuminate\View\View;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.app')]
class Habitaciones extends Component
{
    public bool $mostrarModal = false;

    public ?int $habitacionId = null;

    public string $search = '';

    public string $filtroEstado = 'todos';

    public string $vista = 'cuadricula';

    public string $numero_habitacion = '';

    public string $tipo_habitacion_id = '';

    public string $estado = 'Disponible';

    public string $piso = '1';

    public string $capacidad = '';

    public string $precio_por_noche = '';

    public string $descripcion = '';

    public string $foto_url = '';

    public ?string $mensajeExito = null;

    public const VISTAS = ['cuadricula', 'lista'];

    public const ESTADOS = ['Disponible', 'Ocupada', 'Mantenimiento', 'Limpieza'];

    public const IMAGENES = [
        'https://images.unsplash.com/photo-1611892440504-42a792e24d32?auto=format&fit=crop&w=800&q=80',
        'https://images.unsplash.com/photo-1582719478250-c89cae4dc85b?auto=format&fit=crop&w=800&q=80',
        'https://images.unsplash.com/photo-1631049307264-da0ec9d70304?auto=format&fit=crop&w=800&q=80',
    ];

    /**
     * Opciones del desplegable "Todos los estados". Cada opción agrupa un
     * conjunto de estados reales de la tabla `habitaciones`.
     *
     * @var array<int, array{clave: string, etiqueta: string, estados: list<string>}>
     */
    public const OPCIONES_ESTADO = [
        ['clave' => 'Disponible', 'etiqueta' => 'Disponible', 'estados' => ['Disponible']],
        ['clave' => 'Ocupada', 'etiqueta' => 'Ocupada', 'estados' => ['Ocupada']],
        ['clave' => 'Reservada', 'etiqueta' => 'Reservada', 'estados' => ['Reservada']],
        ['clave' => 'requiere-limpieza', 'etiqueta' => 'Requiere Limpieza', 'estados' => ['Limpieza']],
        ['clave' => 'en-limpieza', 'etiqueta' => 'En Limpieza', 'estados' => ['Limpieza']],
        ['clave' => 'lista', 'etiqueta' => 'Lista', 'estados' => ['Disponible']],
        ['clave' => 'fuera-servicio', 'etiqueta' => 'Fuera de Servicio', 'estados' => ['Mantenimiento']],
    ];

    public function render(): View
    {
        $habitaciones = Habitacion::with('tipo')
            ->when($this->search !== '', function ($query): void {
                $termino = '%'.trim($this->search).'%';
                $query->where(function ($sub) use ($termino): void {
                    $sub->where('numero_habitacion', 'like', $termino)
                        ->orWhereHas('tipo', fn ($tipo) => $tipo->where('nombre', 'like', $termino));
                });
            })
            ->when($this->filtroEstado !== 'todos', function ($query): void {
                $query->whereIn('estado', $this->estadosDelFiltroActual());
            })
            ->orderBy('piso')
            ->orderBy('numero_habitacion')
            ->get();

        return view('habitaciones.index', [
            'habitaciones' => $habitaciones,
            'totalHabitaciones' => Habitacion::count(),
            'tipos' => TipoHabitacion::orderBy('nombre')->get(),
            'estados' => self::ESTADOS,
        ]);
    }

    /**
     * Pills interactivas de estado con contador dinámico y colores.
     *
     * @return array<int, array{clave: string, etiqueta: string, conteo: int, punto: string, pill: string}>
     */
    #[Computed]
    public function pillsEstado(): array
    {
        $definiciones = [
            ['clave' => 'Disponible', 'etiqueta' => 'Disponible', 'estados' => ['Disponible'], 'punto' => 'bg-emerald-500'],
            ['clave' => 'Ocupada', 'etiqueta' => 'Ocupada', 'estados' => ['Ocupada'], 'punto' => 'bg-red-500'],
            ['clave' => 'Reservada', 'etiqueta' => 'Reservada', 'estados' => ['Reservada'], 'punto' => 'bg-amber-400'],
            ['clave' => 'requiere-limpieza', 'etiqueta' => 'Requiere Limpieza', 'estados' => ['Limpieza'], 'punto' => 'bg-red-400'],
            ['clave' => 'en-limpieza', 'etiqueta' => 'En Limpieza', 'estados' => ['Limpieza'], 'punto' => 'bg-sky-500'],
            ['clave' => 'fuera-servicio', 'etiqueta' => 'Fuera de Servicio', 'estados' => ['Mantenimiento'], 'punto' => 'bg-zinc-600'],
        ];

        $conteos = Habitacion::query()->select('estado')->get()->countBy('estado');

        return collect($definiciones)->map(function (array $definicion) use ($conteos): array {
            $definicion['conteo'] = collect($definicion['estados'])
                ->sum(fn (string $estado) => $conteos->get($estado, 0));
            $definicion['activa'] = $this->filtroEstado === $definicion['clave'];
            $definicion['pill'] = $definicion['activa']
                ? 'bg-slate-900 text-white ring-slate-900 dark:bg-white dark:text-slate-900 dark:ring-white'
                : 'bg-white text-slate-600 ring-slate-200 hover:bg-slate-50 hover:text-slate-900 dark:bg-slate-800 dark:text-zinc-300 dark:ring-zinc-700 dark:hover:bg-slate-700 dark:hover:text-white';

            return $definicion;
        })->all();
    }

    public function filtrarPor(string $clave): void
    {
        $this->filtroEstado = $clave;
    }

    public function updatedTipoHabitacionId(): void
    {
        $tipo = $this->tipo_habitacion_id !== ''
            ? TipoHabitacion::find($this->tipo_habitacion_id)
            : null;

        if ($tipo === null) {
            return;
        }

        $this->capacidad = (string) $tipo->capacidad;
        $this->precio_por_noche = number_format((float) $tipo->precio_base, 2, '.', '');
        $this->descripcion = $tipo->descripcion ?? '';
        $this->foto_url = $this->imagenPredeterminada($tipo->nombre);
    }

    public function crear(): void
    {
        $this->reset(['habitacionId', 'numero_habitacion', 'tipo_habitacion_id', 'capacidad', 'precio_por_noche', 'descripcion']);
        $this->estado = 'Disponible';
        $this->piso = '1';
        $this->foto_url = self::IMAGENES[0];
        $this->resetValidation();
        $this->reset('mensajeExito');
        $this->mostrarModal = true;
    }

    public function editar(int $id): void
    {
        $habitacion = Habitacion::with('tipo')->findOrFail($id);

        $this->habitacionId = $habitacion->id;
        $this->numero_habitacion = $habitacion->numero_habitacion;
        $this->tipo_habitacion_id = (string) $habitacion->tipo_habitacion_id;
        $this->estado = $habitacion->estado;
        $this->piso = (string) $habitacion->piso;
        $this->capacidad = (string) ($habitacion->tipo?->capacidad ?? '');
        $this->precio_por_noche = number_format((float) ($habitacion->tipo?->precio_base ?? 0), 2, '.', '');
        $this->descripcion = $habitacion->tipo?->descripcion ?? '';
        $this->foto_url = $this->imagenPara($habitacion);
        $this->resetValidation();
        $this->reset('mensajeExito');
        $this->mostrarModal = true;
    }

    public function cerrarModal(): void
    {
        $this->mostrarModal = false;
        $this->reset(['habitacionId', 'numero_habitacion', 'tipo_habitacion_id']);
        $this->resetValidation();
    }

    public function guardar(): void
    {
        $this->validate([
            'numero_habitacion' => ['required', 'string', 'max:10', 'unique:habitaciones,numero_habitacion,'.$this->habitacionId],
            'tipo_habitacion_id' => ['required', 'exists:tipos_habitacion,id'],
            'estado' => ['required', 'in:Disponible,Ocupada,Mantenimiento,Limpieza'],
            'piso' => ['required', 'integer', 'min:1'],
        ]);

        $datos = [
            'numero_habitacion' => $this->numero_habitacion,
            'tipo_habitacion_id' => $this->tipo_habitacion_id,
            'estado' => $this->estado,
            'piso' => $this->piso,
        ];

        if ($this->habitacionId) {
            Habitacion::findOrFail($this->habitacionId)->update($datos);
            $this->mensajeExito = 'Habitación actualizada correctamente.';
        } else {
            Habitacion::create($datos);
            $this->mensajeExito = 'Habitación creada correctamente.';
        }

        $this->cerrarModal();
    }

    public function eliminar(int $id): void
    {
        Habitacion::findOrFail($id)->delete();

        $this->mensajeExito = 'Habitación eliminada correctamente.';
    }

    public function imagenPara(Habitacion $habitacion): string
    {
        return $this->imagenPredeterminada($habitacion->numero_habitacion);
    }

    public function etiquetaEstado(string $estado): string
    {
        return match ($estado) {
            'Limpieza' => 'Requiere Limpieza',
            'Mantenimiento' => 'Fuera de Servicio',
            default => $estado,
        };
    }

    public function etiquetaLimpieza(string $estado): string
    {
        return match ($estado) {
            'Disponible' => 'Limpia',
            'Ocupada' => 'En uso',
            'Limpieza' => 'Requiere Limpieza',
            default => 'Fuera de servicio',
        };
    }

    public function clasesPuntoEstado(string $estado): string
    {
        return match ($estado) {
            'Disponible' => 'bg-emerald-500',
            'Ocupada' => 'bg-red-500',
            'Limpieza' => 'bg-amber-500',
            'Mantenimiento' => 'bg-zinc-500',
            default => 'bg-slate-400',
        };
    }

    public function clasesTextoEstado(string $estado): string
    {
        return match ($estado) {
            'Disponible' => 'text-emerald-700 dark:text-emerald-300',
            'Ocupada' => 'text-red-700 dark:text-red-300',
            'Limpieza' => 'text-amber-700 dark:text-amber-300',
            'Mantenimiento' => 'text-slate-600 dark:text-slate-300',
            default => 'text-slate-600 dark:text-slate-300',
        };
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

    private function imagenPredeterminada(string $semilla): string
    {
        $indice = abs(crc32($semilla)) % count(self::IMAGENES);

        return self::IMAGENES[$indice];
    }

    public function cambiarVista(string $vista): void
    {
        $this->vista = in_array($vista, self::VISTAS, true) ? $vista : 'cuadricula';
    }
}
