<?php

namespace App\Livewire\Servicios;

use App\Models\Categoria;
use App\Models\Empleado;
use App\Models\Reserva;
use App\Models\ReservaServicio;
use App\Models\Servicio;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Lazy;
use Livewire\Component;

/**
 * Registro de un consumo extra contra el folio de una habitación ocupada.
 *
 * Es el camino que usa recepción: se elige la reservación que ya hizo check-in,
 * el servicio consumido, cuántas unidades y con qué precio unitario se cobra
 * (puede diferir del catálogo si hay descuento). El cargo se guarda en
 * `reserva_servicio` con el empleado responsable, y desde ese momento forma
 * parte del total de check-out que ve el huésped en su estado de cuenta.
 *
 * El <dialog> lo abre Flux desde el navegador, así que el componente no lleva
 * booleanos de visibilidad: se limita a leer y escribir el folio.
 */
#[Lazy]
class Cargos extends Component
{
    public string $reserva_id = '';

    public string $servicio_id = '';

    public string $cantidad = '1';

    public string $precio_aplicado = '';

    public string $empleado_id = '';

    public function mount(): void
    {
        $this->empleado_id = (string) (auth()->user()?->empleado?->id_empleado ?? '');
    }

    public function render()
    {
        return view('servicios.partials.cargo-form');
    }

    /**
     * Al elegir un servicio se propone su precio de catálogo. El campo queda
     * editable para que recepción pueda aplicar un descuento o un recargo.
     */
    public function updatedServicioId(): void
    {
        $servicio = $this->servicio_id !== ''
            ? Servicio::find($this->servicio_id)
            : null;

        $this->precio_aplicado = $servicio === null
            ? ''
            : number_format((float) $servicio->precio, 2, '.', '');
    }

    public function guardar(): void
    {
        $this->validate([
            'reserva_id' => [
                'required',
                Rule::exists('reservas', 'id')->whereIn('estado', Reserva::ESTADOS_ACTIVOS),
            ],
            'servicio_id' => ['required', 'exists:servicios,id'],
            'cantidad' => ['required', 'integer', 'min:1', 'max:99'],
            'precio_aplicado' => ['required', 'numeric', 'min:0.01', 'max:999999.99'],
            'empleado_id' => ['required', 'exists:empleados,id_empleado'],
        ]);

        $subtotal = round((float) $this->precio_aplicado * (int) $this->cantidad, 2);

        DB::transaction(function () use ($subtotal): void {
            ReservaServicio::create([
                'reserva_id' => (int) $this->reserva_id,
                'servicio_id' => (int) $this->servicio_id,
                'cantidad' => (int) $this->cantidad,
                'precio_aplicado' => number_format((float) $this->precio_aplicado, 2, '.', ''),
                'empleado_id' => (int) $this->empleado_id,
                'subtotal' => number_format($subtotal, 2, '.', ''),
            ]);
        });

        $servicio = Servicio::findOrFail($this->servicio_id);

        $this->reset(['servicio_id', 'cantidad', 'precio_aplicado']);

        $this->cantidad = '1';

        $this->dispatch(
            'cargo-registrado',
            mensaje: "Consumo de {$servicio->nombre} cargado al folio correctamente."
        );
    }

    /**
     * Reservaciones con huésped en el hotel. Solo estas admiten consumos.
     *
     * @return Collection<int, Reserva>
     */
    #[Computed]
    public function reservas(): Collection
    {
        return Reserva::with(['cliente', 'habitacionesAsignadas.habitacion'])
            ->whereIn('estado', Reserva::ESTADOS_ACTIVOS)
            ->orderBy('check_in')
            ->orderBy('id')
            ->get();
    }

    /**
     * Catálogo que recepción puede cargar al folio, agrupado por el nombre de su
     * categoría. La clasificación vive en `categoria_id`, así que el orden sale
     * de una subconsulta sobre `categorias` en lugar de la antigua columna de
     * texto, que hoy está vacía.
     *
     * @return Collection<int, Servicio>
     */
    #[Computed]
    public function servicios(): Collection
    {
        return Servicio::with('clasificacion')
            ->orderBy(
                Categoria::query()
                    ->select('nombre')
                    ->whereColumn('categorias.id', 'servicios.categoria_id')
            )
            ->orderBy('nombre')
            ->get();
    }

    /**
     * Empleados que pueden quedar como responsables de un cargo.
     *
     * @return Collection<int, Empleado>
     */
    #[Computed]
    public function empleados(): Collection
    {
        return Empleado::where('esta_activo', true)
            ->orderBy('nombre')
            ->orderBy('apellidos')
            ->get();
    }

    /**
     * Folio seleccionado con sus cargos, para mostrar el estado de cuenta antes
     * y después de cargar el consumo.
     */
    #[Computed]
    public function folio(): ?Reserva
    {
        if ($this->reserva_id === '') {
            return null;
        }

        return Reserva::with([
            'cliente',
            'habitacionesAsignadas.habitacion',
            'serviciosAsignados.servicio',
            'serviciosAsignados.empleado',
        ])
            ->whereIn('estado', Reserva::ESTADOS_ACTIVOS)
            ->find($this->reserva_id);
    }

    /**
     * Totales del folio seleccionado. `extras`, `total` y `pendiente` son
     * exactamente las cifras que el estado de cuenta presenta al huésped.
     *
     * @return array{tarifa: float, extras: float, total: float, pagado: float, pendiente: float}|null
     */
    #[Computed]
    public function resumen(): ?array
    {
        $reserva = $this->folio;

        if ($reserva === null) {
            return null;
        }

        return [
            'tarifa' => $reserva->tarifaHabitaciones(),
            'extras' => $reserva->subtotalServicios(),
            'total' => $reserva->totalConsumos(),
            'pagado' => $reserva->totalPagado(),
            'pendiente' => $reserva->saldoPendiente(),
        ];
    }

    /**
     * Importe del cargo que se está por registrar, para que recepción vea el
     * efecto en el folio antes de confirmar.
     */
    #[Computed]
    public function subtotalPropuesto(): float
    {
        return round((float) $this->precio_aplicado * (int) $this->cantidad, 2);
    }
}
