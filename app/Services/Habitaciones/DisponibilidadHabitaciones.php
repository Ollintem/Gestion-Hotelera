<?php

namespace App\Services\Habitaciones;

use App\Enums\EstadoHabitacion;
use App\Enums\EstadoReserva;
use App\Models\Habitacion;
use App\Models\Reserva;
use App\Models\ReservaHabitacion;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;

/**
 * Fuente única de verdad para la disponibilidad de habitaciones.
 *
 * La disponibilidad se decide por fechas y reservaciones, no por el estado
 * operativo actual de la habitación: una habitación ocupada hoy puede venderse
 * para una fecha futura si no hay cruce con una reservación pendiente o en
 * curso. Solo el mantenimiento bloquea cualquier fecha.
 */
class DisponibilidadHabitaciones
{
    /**
     * Reservaciones que ocupan una habitación. Las canceladas y finalizadas
     * liberan sus fechas.
     *
     * @var list<string>
     */
    public const ESTADOS_QUE_OCUPAN = [
        EstadoReserva::Pendiente->value,
        EstadoReserva::Confirmada->value,
    ];

    /**
     * Consulta de habitaciones vendibles para un rango de fechas.
     *
     * @return Builder<Habitacion>
     */
    public function consulta(Carbon|string $checkIn, Carbon|string $checkOut, ?int $guests = null): Builder
    {
        $inicio = $this->fecha($checkIn);
        $fin = $this->fecha($checkOut);
        $hoy = Carbon::today();

        return Habitacion::query()
            ->with('tipoHabitacion')
            ->where('estado', '!=', EstadoHabitacion::Mantenimiento->value)
            ->when(
                $inicio->lessThanOrEqualTo($hoy),
                fn (Builder $query): Builder => $query->where('estado', '!=', EstadoHabitacion::Ocupada->value),
            )
            ->whereNotIn('id', $this->consultaHabitacionesOcupadas($inicio, $fin))
            ->when($guests !== null, fn (Builder $query): Builder => $query->whereHas(
                'tipoHabitacion',
                fn (Builder $tipo): Builder => $tipo->where('capacidad', '>=', $guests),
            ))
            ->orderBy('piso')
            ->orderBy('numero_habitacion');
    }

    /**
     * Comprueba si una habitación concreta puede venderse en el rango. El
     * mantenimiento siempre la excluye; el resto depende de los cruces.
     */
    public function estaDisponible(
        int $habitacionId,
        Carbon|string $checkIn,
        Carbon|string $checkOut,
        ?int $ignorarReservaId = null,
    ): bool {
        $habitacion = Habitacion::query()->find($habitacionId);

        if ($habitacion === null || $habitacion->estado === EstadoHabitacion::Mantenimiento->value) {
            return false;
        }

        if ($this->fecha($checkIn)->lessThanOrEqualTo(Carbon::today())
            && $habitacion->estado === EstadoHabitacion::Ocupada->value) {
            return false;
        }

        return ! $this->hayConflicto($habitacionId, $checkIn, $checkOut, $ignorarReservaId);
    }

    /**
     * Cruce de fechas con reservaciones vigentes. Se ignora opcionalmente una
     * reservación para permitir reprogramaciones.
     */
    public function hayConflicto(
        int $habitacionId,
        Carbon|string $checkIn,
        Carbon|string $checkOut,
        ?int $ignorarReservaId = null,
    ): bool {
        $inicio = $this->fecha($checkIn);
        $fin = $this->fecha($checkOut);

        return ReservaHabitacion::query()
            ->where('habitacion_id', $habitacionId)
            ->whereHas('reserva', fn (Builder $reserva): Builder => $this->aplicarConflicto(
                $reserva,
                $inicio,
                $fin,
                $ignorarReservaId,
            ))
            ->exists();
    }

    /**
     * Bloquea la fila de la habitación dentro de una transacción para que dos
     * reservaciones simultáneas no puedan superar la verificación de cruces.
     */
    public function bloquearHabitacion(int $habitacionId): Habitacion
    {
        return Habitacion::query()
            ->with('tipoHabitacion')
            ->lockForUpdate()
            ->findOrFail($habitacionId);
    }

    /**
     * @return Builder<ReservaHabitacion>
     */
    private function consultaHabitacionesOcupadas(Carbon $inicio, Carbon $fin): Builder
    {
        return ReservaHabitacion::query()
            ->select('habitacion_id')
            ->whereHas('reserva', fn (Builder $reserva): Builder => $this->aplicarConflicto($reserva, $inicio, $fin));
    }

    /**
     * @param  Builder<Reserva>  $query
     * @return Builder<Reserva>
     */
    private function aplicarConflicto(
        Builder $query,
        Carbon $inicio,
        Carbon $fin,
        ?int $ignorarReservaId = null,
    ): Builder {
        return $query
            ->whereIn('estado', self::ESTADOS_QUE_OCUPAN)
            ->where('check_in', '<', $fin->toDateString())
            ->where('check_out', '>', $inicio->toDateString())
            ->when(
                $ignorarReservaId !== null,
                fn (Builder $subconsulta): Builder => $subconsulta->where('id', '!=', $ignorarReservaId),
            );
    }

    private function fecha(Carbon|string $fecha): Carbon
    {
        return Carbon::parse($fecha)->startOfDay();
    }
}
