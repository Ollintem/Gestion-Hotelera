<?php

namespace App\Livewire\Reservaciones;

use App\Enums\EstadoHabitacion;
use App\Enums\EstadoReserva;
use App\Models\Cliente;
use App\Models\Habitacion;
use App\Models\Reserva;
use App\Services\Habitaciones\DisponibilidadHabitaciones;
use App\Services\Reservaciones\CrearReserva;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithPagination;

/**
 * Índice del módulo de reservaciones.
 *
 * Además del listado, este componente es dueño del flujo de creación: captura
 * el evento `reserva.crear` del botón superior, abre el modal, valida y guarda
 * la reservación dejando la habitación ocupada. Las demás acciones (editar,
 * pago, check-in/out, cancelar) siguen delegándose por eventos.
 */
#[Layout('components.layouts.app')]
class Index extends Component
{
    use WithPagination;

    public const POR_PAGINA = 10;

    /**
     * Estado de la habitacion que puede reservarse.
     */
    public const ESTADO_DISPONIBLE = EstadoHabitacion::Disponible->value;

    /**
     * Estado que toma la habitacion al registrar la llegada del huesped.
     */
    public const ESTADO_OCUPADA = EstadoHabitacion::Ocupada->value;

    /**
     * Toda reservacion nueva nace pendiente de check-in. La confirmacion ocurre
     * cuando recepcion registra la llegada, no al capturar la reserva.
     */
    public const ESTADO_PENDIENTE = EstadoReserva::Pendiente->value;

    public bool $showModalCrear = false;

    public string $cliente_id = '';

    public string $habitacion_id = '';

    public string $check_in = '';

    public string $check_out = '';

    public int $num_huespedes = 1;

    public ?string $mensajeExito = null;

    public ?string $mensajeError = null;

    public string $nuevoClienteNombre = '';

    public string $nuevoClienteEmail = '';

    public string $nuevoClienteTelefono = '';

    public function render(): View
    {
        $reservas = Reserva::with([
            'cliente',
            'habitaciones.tipoHabitacion',
            'habitacionesAsignadas',
            'serviciosAsignados',
            'pagos',
        ])
            ->latest()
            ->paginate(self::POR_PAGINA);

        return view('livewire.reservaciones.index', [
            'reservas' => $reservas,
        ]);
    }

    /**
     * Captura el evento `reserva.crear` (botón superior) y muestra el modal de
     * creación con el formulario en blanco.
     */
    #[On('reserva.crear')]
    public function abrirModalCrear(): void
    {
        $this->reset(
            'cliente_id',
            'habitacion_id',
            'check_in',
            'check_out',
            'nuevoClienteNombre',
            'nuevoClienteEmail',
            'nuevoClienteTelefono',
        );

        $this->num_huespedes = 1;

        $this->resetValidation();
        $this->showModalCrear = true;
    }

    /**
     * Cierra el modal de creación sin guardar.
     */
    public function cerrarModalCrear(): void
    {
        $this->showModalCrear = false;
        $this->resetValidation();
    }

    /**
     * Da de alta un cliente desde el modal de reservación y lo deja
     * seleccionado para continuar con el flujo.
     */
    public function guardarClienteYSeleccionar(): void
    {
        if (! auth()->user()?->can('clientes.crear')) {
            $this->addError('nuevoClienteNombre', 'No tienes permiso para crear clientes.');

            return;
        }

        $datos = $this->validate([
            'nuevoClienteNombre' => ['required', 'string', 'max:100'],
            'nuevoClienteEmail' => ['nullable', 'email', 'max:255'],
            'nuevoClienteTelefono' => ['nullable', 'string', 'max:20'],
        ], [
            'nuevoClienteNombre.required' => 'Escribe el nombre del cliente.',
            'nuevoClienteEmail.email' => 'El correo no tiene un formato válido.',
            'nuevoClienteEmail.max' => 'El correo no puede superar los 255 caracteres.',
            'nuevoClienteTelefono.max' => 'El teléfono no puede superar los 20 caracteres.',
        ], [
            'nuevoClienteNombre' => 'nombre',
            'nuevoClienteEmail' => 'correo',
            'nuevoClienteTelefono' => 'teléfono',
        ]);

        $partes = preg_split('/\s+/', trim($datos['nuevoClienteNombre']), 2) ?: [];

        $cliente = Cliente::create([
            'nombre' => $partes[0] ?? '',
            'apellido' => $partes[1] ?? '',
            'email' => $datos['nuevoClienteEmail'] ?: null,
            'telefono' => $datos['nuevoClienteTelefono'] ?: null,
        ]);

        $this->cliente_id = (string) $cliente->id;
        $this->reset('nuevoClienteNombre', 'nuevoClienteEmail', 'nuevoClienteTelefono');
        $this->resetValidation();

        unset($this->clientes);

        $this->dispatch('cliente.creado');
    }

    /**
     * Valida y persiste la reservación: calcula noches y monto, la registra
     * como "Confirmada" y deja la habitación en "Ocupada".
     */
    public function guardarReserva(): void
    {
        if (! auth()->user()?->can('reservaciones.crear')) {
            $this->addError('cliente_id', 'No tienes permiso para crear reservaciones.');

            return;
        }

        $datos = $this->validate([
            'cliente_id' => ['required', 'exists:clientes,id'],
            'habitacion_id' => ['required', 'exists:habitaciones,id'],
            'check_in' => ['required', 'date', 'after_or_equal:today'],
            'check_out' => ['required', 'date', 'after:check_in'],
            'num_huespedes' => ['required', 'integer', 'min:1'],
        ], [
            'cliente_id.required' => 'Selecciona un cliente.',
            'cliente_id.exists' => 'El cliente seleccionado no es válido.',
            'habitacion_id.required' => 'Selecciona una habitación.',
            'habitacion_id.exists' => 'La habitación ya no está disponible.',
            'check_in.required' => 'Indica la fecha de check-in.',
            'check_in.after_or_equal' => 'El check-in no puede ser anterior a hoy.',
            'check_out.required' => 'Indica la fecha de check-out.',
            'check_out.after' => 'El check-out debe ser posterior al check-in.',
            'num_huespedes.required' => 'Indica el número de huéspedes.',
            'num_huespedes.integer' => 'El número de huéspedes debe ser un entero.',
            'num_huespedes.min' => 'Debe haber al menos un huésped.',
        ]);

        try {
            app(CrearReserva::class)->ejecutar(
                (int) $datos['cliente_id'],
                (int) $datos['habitacion_id'],
                $datos['check_in'],
                $datos['check_out'],
                (int) $datos['num_huespedes'],
                auth()->id(),
            );
        } catch (ValidationException $excepcion) {
            foreach ($excepcion->errors() as $campo => $mensajes) {
                $this->addError($campo, $mensajes[0]);
            }

            return;
        }

        $this->cerrarModalCrear();
        $this->resetPage();

        $this->mensajeExito = 'Reservación creada correctamente.';
        $this->dispatch('reserva.creada');
    }

    /**
     * Abre el flujo de edición de una reservación.
     */
    public function editar(int $id): void
    {
        $this->dispatch('reserva.editar', reservaId: $id);
    }

    /**
     * Abre el registro de un pago sobre la reservación.
     */
    public function registrarPago(int $id): void
    {
        $this->dispatch('reserva.registrar-pago', reservaId: $id);
        $this->redirect(route('pagos', ['reserva' => $id]), navigate: true);
    }

    /**
     * Inicia el Check-in de la reservación.
     */
    public function checkIn(int $id): void
    {
        if (! auth()->user()?->can('checkin_checkout.crear')) {
            $this->mensajeError = 'No tienes permiso para registrar el check-in.';

            return;
        }

        $reserva = Reserva::findOrFail($id);

        if (! $reserva->puedeHacerCheckIn()) {
            $this->mensajeError = 'La reserva #'.$reserva->id.' ya no admite check-in.';

            return;
        }

        $reserva->realizarCheckIn();

        $this->mensajeError = null;
        $this->mensajeExito = 'Check-in registrado correctamente para la reserva #'.$reserva->id.'.';
    }

    /**
     * Cierra la estadía de la reservación.
     */
    public function checkOut(int $id): void
    {
        if (! auth()->user()?->can('checkin_checkout.crear')) {
            $this->mensajeError = 'No tienes permiso para registrar el check-out.';

            return;
        }

        $reserva = Reserva::findOrFail($id);

        if ($reserva->puedeHacerCheckOut() && ! $reserva->estaPagada()) {
            $this->mensajeError = 'No se puede registrar el check-out: la reserva #'.$reserva->id
                .' tiene un saldo pendiente de $'.number_format($reserva->saldoPendiente(), 2).'.';

            return;
        }

        if (! $reserva->puedeHacerCheckOut()) {
            $this->mensajeError = 'La reserva #'.$reserva->id.' no se encuentra en estadia.';

            return;
        }

        $reserva->realizarCheckOut();

        $this->mensajeError = null;
        $this->mensajeExito = 'Check-out registrado correctamente para la reserva #'.$reserva->id.'.';
    }

    /**
     * Cancela una reservación.
     */
    public function cancelarReserva(int $id): void
    {
        if (! auth()->user()?->can('reservaciones.editar')) {
            $this->mensajeError = 'No tienes permiso para cancelar reservaciones.';

            return;
        }

        $reserva = Reserva::findOrFail($id);

        if (! $reserva->cancelar()) {
            $this->mensajeError = 'Solo se pueden cancelar reservaciones pendientes.';

            return;
        }

        $this->mensajeError = null;
        $this->mensajeExito = 'Reservacion #'.$reserva->id.' cancelada correctamente.';
    }

    /**
     * Catálogo de clientes para el selector del modal.
     *
     * @return Collection<int, Cliente>
     */
    #[Computed]
    public function clientes(): Collection
    {
        return Cliente::orderBy('nombre')->orderBy('apellido')->get();
    }

    /**
     * Habitaciones reservables: solo las que están en estado "Disponible".
     *
     * @return Collection<int, Habitacion>
     */
    #[Computed]
    public function habitacionesDisponibles(): Collection
    {
        if ($this->check_in === '' || $this->check_out === '') {
            return Habitacion::with('tipoHabitacion')
                ->where('estado', self::ESTADO_DISPONIBLE)
                ->orderBy('numero_habitacion')
                ->get();
        }

        try {
            $checkIn = Carbon::parse($this->check_in);
            $checkOut = Carbon::parse($this->check_out);
        } catch (\Throwable) {
            return collect();
        }

        if ($checkOut->lessThanOrEqualTo($checkIn)) {
            return collect();
        }

        return app(DisponibilidadHabitaciones::class)
            ->consulta($checkIn, $checkOut, max(1, $this->num_huespedes))
            ->get();
    }

    /**
     * Resumen en vivo de la estadía seleccionada (noches, precio y total). Es
     * null mientras falten datos o la habitación ya no esté disponible.
     *
     * @return array{noches: int, precio: float, total: float}|null
     */
    #[Computed]
    public function resumenEstadia(): ?array
    {
        if ($this->habitacion_id === '' || $this->check_in === '' || $this->check_out === '') {
            return null;
        }

        $habitacion = $this->habitacionesDisponibles->firstWhere('id', (int) $this->habitacion_id);

        if ($habitacion === null) {
            return null;
        }

        try {
            $checkIn = Carbon::parse($this->check_in);
            $checkOut = Carbon::parse($this->check_out);
        } catch (\Throwable) {
            return null;
        }

        if ($checkOut->lte($checkIn)) {
            return null;
        }

        $noches = (int) $checkIn->diffInDays($checkOut);
        $precio = (float) ($habitacion->tipoHabitacion?->precio_base ?? 0);

        return [
            'noches' => $noches,
            'precio' => round($precio, 2),
            'total' => round($precio * $noches, 2),
        ];
    }

    /**
     * Código legible de la reservación, derivado de su clave primaria.
     */
    public function codigo(Reserva $reserva): string
    {
        return 'RES-'.str_pad((string) $reserva->id, 5, '0', STR_PAD_LEFT);
    }

    /**
     * Huéspedes que admite la reservación: suma de la capacidad de sus
     * habitaciones.
     */
    public function huespedes(Reserva $reserva): int
    {
        return $reserva->huespedes();
    }

    /**
     * Importe ya abonado por el huésped.
     */
    public function totalPagado(Reserva $reserva): float
    {
        return $reserva->totalPagado();
    }

    /**
     * Indica si la reservación ya cubrió su total con pagos registrados.
     */
    public function estaPagada(Reserva $reserva): bool
    {
        return $reserva->estaPagada();
    }
}
