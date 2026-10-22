<?php

namespace App\Livewire;

use App\Models\Habitacion;
use App\Models\Reserva;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\View\View;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Validate;
use Livewire\Component;
use Livewire\WithPagination;

/**
 * Módulo de check-in y check-out.
 *
 * La pantalla tiene dos pestañas: los movimientos por realizar (llegadas y
 * salidas pendientes) y el historial de lo ya registrado. Cada acción pasa por
 * un diálogo de confirmación que muestra los datos de la reservación y su
 * estado de cuenta antes de ejecutarse.
 *
 * La lógica de negocio vive en el modelo (`Reserva::realizarCheckIn` y
 * `Reserva::realizarCheckOut`): el componente solo resuelve permisos, filtros
 * y el diálogo.
 */
#[Layout('components.layouts.app')]
class CheckInOut extends Component
{
    use WithPagination;

    /**
     * Pestaña activa del módulo: movimientos por atender o historial.
     */
    public string $pestana = 'pendientes';

    public bool $mostrarModal = false;

    /**
     * Acción que espera confirmación: `checkin` o `checkout`.
     */
    public string $accion = 'checkin';

    public ?int $reservaSeleccionadaId = null;

    /**
     * Observaciones opcionales que recepción anota al registrar el movimiento.
     */
    #[Validate('nullable|string|max:500')]
    public string $observaciones = '';

    /**
     * Filtros compartidos por ambas pestañas.
     */
    public string $filtroBusqueda = '';

    public string $filtroFecha = '';

    public string $filtroHabitacion = '';

    /**
     * Tipo de movimiento listado en el historial: llegadas o salidas.
     */
    public string $paginaHistorial = 'checkins';

    public ?string $mensajeExito = null;

    public ?string $mensajeError = null;

    public function render(): View
    {
        $reservaSeleccionada = $this->reservaSeleccionada();

        return view('checkin-checkout.index', [
            'llegadas' => $this->llegadasPendientes(),
            'salidas' => $this->salidasPendientes(),
            'historial' => $this->historial(),
            'habitaciones' => Habitacion::orderBy('numero_habitacion')->get(['id', 'numero_habitacion']),
            'reservaSeleccionada' => $reservaSeleccionada,
            'resumenCuenta' => $this->resumen($reservaSeleccionada),
        ]);
    }

    /**
     * Restringe la consulta a las reservaciones cuyo folio o nombre del
     * huésped coinciden con el texto escrito en el filtro.
     *
     * @param  Builder<Reserva>  $query
     * @return Builder<Reserva>
     */
    private function aplicarBusqueda($query)
    {
        if ($this->filtroBusqueda === '') {
            return $query;
        }

        $termino = '%'.$this->filtroBusqueda.'%';

        return $query->where(function ($subconsulta) use ($termino) {
            $subconsulta->where('id', 'like', $termino)
                ->orWhereHas('cliente', fn ($cliente) => $cliente
                    ->where('nombre', 'like', $termino)
                    ->orWhere('apellido', 'like', $termino)
                    ->orWhere('numero_identificacion', 'like', $termino));
        });
    }

    /**
     * Reservaciones aún no registradas: primero las de hoy y las atrasadas,
     * después las programadas para más adelante.
     *
     * @return LengthAwarePaginator<Reserva>
     */
    private function llegadasPendientes(): LengthAwarePaginator
    {
        return Reserva::with(['cliente', 'habitaciones'])
            ->where('estado', 'Pendiente')
            ->tap(fn ($query) => $this->aplicarBusqueda($query))
            ->when($this->filtroFecha !== '', fn ($query) => $query->whereDate('check_in', $this->filtroFecha))
            ->when($this->filtroHabitacion !== '', fn ($query) => $query->whereHas(
                'habitaciones',
                fn ($habitaciones) => $habitaciones->where('habitaciones.id', (int) $this->filtroHabitacion)
            ))
            ->orderByRaw('check_in < ? desc', [now()->toDateString()])
            ->orderBy('check_in')
            ->paginate(8, ['*'], 'paginaLlegadas');
    }

    /**
     * Huéspedes ya registrados que aún no han salido. Se antepone a la lista el
     * que tiene la salida programada vencida.
     *
     * @return LengthAwarePaginator<Reserva>
     */
    private function salidasPendientes(): LengthAwarePaginator
    {
        return Reserva::with(['cliente', 'habitaciones'])
            ->where('estado', 'Confirmada')
            ->tap(fn ($query) => $this->aplicarBusqueda($query))
            ->when($this->filtroFecha !== '', fn ($query) => $query->whereDate('check_out', $this->filtroFecha))
            ->when($this->filtroHabitacion !== '', fn ($query) => $query->whereHas(
                'habitaciones',
                fn ($habitaciones) => $habitaciones->where('habitaciones.id', (int) $this->filtroHabitacion)
            ))
            ->orderByRaw('check_out < ? desc', [now()->toDateString()])
            ->orderBy('check_out')
            ->paginate(8, ['*'], 'paginaSalidas');
    }

    /**
     * Movimientos ya realizados, separados en llegadas y salidas registradas.
     *
     * @return LengthAwarePaginator<Reserva>
     */
    private function historial(): LengthAwarePaginator
    {
        $columna = $this->paginaHistorial === 'salidas' ? 'fecha_hora_checkout' : 'fecha_hora_checkin';

        return Reserva::with(['cliente', 'habitaciones', 'usuarioCheckIn', 'usuarioCheckOut'])
            ->whereNotNull($columna)
            ->tap(fn ($query) => $this->aplicarBusqueda($query))
            ->when($this->filtroFecha !== '', fn ($query) => $query->whereDate($columna, $this->filtroFecha))
            ->when($this->filtroHabitacion !== '', fn ($query) => $query->whereHas(
                'habitaciones',
                fn ($habitaciones) => $habitaciones->where('habitaciones.id', (int) $this->filtroHabitacion)
            ))
            ->orderByDesc($columna)
            ->paginate(8, ['*'], 'paginaHistorialMovimientos');
    }

    /**
     * Filtra también al cambiar de pestaña de historial.
     */
    public function verHistorial(string $tipo): void
    {
        $this->paginaHistorial = in_array($tipo, ['checkins', 'salidas'], true) ? $tipo : 'checkins';
        $this->pestana = 'historial';
        $this->resetPage('paginaHistorialMovimientos');
    }

    public function verPendientes(): void
    {
        $this->pestana = 'pendientes';
    }

    public function limpiarFiltros(): void
    {
        $this->reset(['filtroBusqueda', 'filtroFecha', 'filtroHabitacion']);
        $this->reiniciarPaginas();
    }

    /**
     * Abre el diálogo de confirmación del check-in de una reservación.
     */
    public function abrirCheckIn(int $id): void
    {
        $this->abrirDialogo('checkin', $id);
    }

    /**
     * Abre el diálogo de confirmación del check-out de una reservación.
     */
    public function abrirCheckOut(int $id): void
    {
        $this->abrirDialogo('checkout', $id);
    }

    public function cerrarModal(): void
    {
        $this->mostrarModal = false;
        $this->resetValidation();
        $this->reset(['reservaSeleccionadaId', 'observaciones']);
    }

    /**
     * Registra la llegada del huésped. Solo reservaciones «Pendiente» y con
     * permiso `checkin_checkout.crear`.
     */
    public function confirmarCheckIn(): void
    {
        if (! $this->puedeRegistrar('checkin_checkout.crear', 'registrar el check-in')) {
            return;
        }

        $reserva = $this->reservaSeleccionada();

        if ($reserva === null || $reserva->estado !== 'Pendiente') {
            $this->mensajeError = 'La reservación ya no admite check-in. Actualiza la pantalla e inténtalo de nuevo.';

            return;
        }

        $this->validate();

        $reserva->realizarCheckIn($this->observaciones);

        $this->mensajeExito = 'Check-in registrado correctamente para la reserva #'.$reserva->id.'.';
        $this->mensajeError = null;
        $this->cerrarModal();
    }

    /**
     * Registra la salida del huésped. Solo reservaciones «Confirmada», sin
     * saldo pendiente y con permiso `checkin_checkout.crear`.
     */
    public function confirmarCheckOut(): void
    {
        if (! $this->puedeRegistrar('checkin_checkout.crear', 'registrar el check-out')) {
            return;
        }

        $reserva = $this->reservaSeleccionada();

        if ($reserva === null || $reserva->estado !== 'Confirmada') {
            $this->mensajeError = 'La reservación no está en estadía, por lo que no admite check-out. Actualiza la pantalla.';

            return;
        }

        if (! $reserva->estaPagada()) {
            $this->mensajeError = 'No se puede registrar el check-out: la reserva #'.$reserva->id
                .' tiene un saldo pendiente de $'.number_format($reserva->saldoPendiente(), 2)
                .'. Registra el pago en el módulo de Pagos antes de cerrar la estadía.';
            $this->mensajeExito = null;

            return;
        }

        $this->validate();

        $reserva->realizarCheckOut($this->observaciones);

        $this->mensajeExito = 'Check-out registrado correctamente para la reserva #'.$reserva->id
            .'. Las habitaciones pasaron a limpieza.';
        $this->mensajeError = null;
        $this->cerrarModal();
    }

    /**
     * Cancela una reservación que aún no ha llegado. Las confirmadas no se
     * cancelan desde aquí: el huésped ya está en el hotel.
     */
    public function cancelar(int $id): void
    {
        if (! $this->puedeRegistrar('checkin_checkout.editar', 'cancelar la reservación')) {
            return;
        }

        $reserva = Reserva::with('habitaciones')->findOrFail($id);

        if ($reserva->estado !== 'Pendiente') {
            $this->mensajeError = 'Solo se pueden cancelar reservaciones pendientes. La reserva #'.$reserva->id
                .' ya tiene al huésped en el hotel.';
            $this->mensajeExito = null;

            return;
        }

        $reserva->update(['estado' => 'Cancelada']);

        foreach ($reserva->habitaciones as $habitacion) {
            $habitacion->update(['estado' => 'Disponible']);
        }

        $this->mensajeExito = 'Reservación #'.$reserva->id.' cancelada correctamente.';
        $this->mensajeError = null;
    }

    /**
     * Cifras del estado de cuenta que el diálogo de confirmación muestra en
     * modo lectura antes de cerrar la estadía.
     *
     * @return array{tarifa: float, extras: float, total: float, pagado: float, pendiente: float}|null
     */
    public function resumen(?Reserva $reserva = null): ?array
    {
        $reserva ??= $this->reservaSeleccionada();

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
     * Reservación que el diálogo está confirmando, con todo lo necesario para
     * pintar la ficha y validar la acción.
     */
    public function reservaSeleccionada(): ?Reserva
    {
        if ($this->reservaSeleccionadaId === null) {
            return null;
        }

        return Reserva::with([
            'cliente',
            'habitaciones',
            'habitacionesAsignadas.habitacion',
            'usuarioCheckIn',
        ])->find($this->reservaSeleccionadaId);
    }

    private function abrirDialogo(string $accion, int $id): void
    {
        $reserva = Reserva::findOrFail($id);

        if ($accion === 'checkin' && $reserva->estado !== 'Pendiente') {
            $this->mensajeError = 'La reserva #'.$reserva->id
                .' ya tiene registrado su check-in. Actualiza la pantalla e inténtalo de nuevo.';
            $this->mensajeExito = null;

            return;
        }

        if ($accion === 'checkout' && $reserva->estado !== 'Confirmada') {
            $this->mensajeError = 'La reserva #'.$reserva->id
                .' no está en estadía, por lo que no admite check-out. Actualiza la pantalla.';
            $this->mensajeExito = null;

            return;
        }

        $this->accion = $accion;
        $this->reservaSeleccionadaId = $reserva->id;
        $this->observaciones = '';
        $this->resetValidation();
        $this->mensajeError = null;
        $this->mostrarModal = true;
    }

    /**
     * Corta la operación cuando el usuario no tiene el permiso indicado.
     */
    private function puedeRegistrar(string $permiso, string $accion): bool
    {
        if (auth()->user()?->can($permiso)) {
            return true;
        }

        $this->mensajeError = 'No tienes permiso para '.$accion.'.';
        $this->mensajeExito = null;

        return false;
    }

    /**
     * Vuelve a la primera página de cualquiera de las listas cuando cambia un
     * filtro, para no dejar al usuario en una página vacía.
     */
    public function updated(string $propiedad): void
    {
        if (str_starts_with($propiedad, 'filtro')) {
            $this->reiniciarPaginas();
        }
    }

    /**
     * Las tres listas usan nombre de página propio, de modo que cada una
     * necesita su propio reinicio al filtrar.
     */
    private function reiniciarPaginas(): void
    {
        $this->resetPage('paginaLlegadas');
        $this->resetPage('paginaSalidas');
        $this->resetPage('paginaHistorialMovimientos');
    }
}
