<?php

namespace App\Services\Reservaciones;

use App\Enums\EstadoHabitacion;
use App\Enums\EstadoReserva;
use App\Models\Reserva;
use App\Services\Habitaciones\DisponibilidadHabitaciones;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * Alta de reservaciones compartida por el módulo administrativo y el público.
 *
 * Centraliza las reglas de capacidad, disponibilidad y cálculo del monto, y
 * usa una transacción con bloqueo de la habitación para evitar que dos
 * peticiones simultáneas reserven la misma habitación para fechas cruzadas.
 */
class CrearReserva
{
    public function __construct(private readonly DisponibilidadHabitaciones $disponibilidad) {}

    /**
     * @throws ValidationException
     */
    public function ejecutar(
        int $clienteId,
        int $habitacionId,
        Carbon|string $checkIn,
        Carbon|string $checkOut,
        int $huespedes,
        ?int $userId = null,
        EstadoReserva|string $estado = EstadoReserva::Pendiente,
    ): Reserva {
        $inicio = Carbon::parse($checkIn)->startOfDay();
        $fin = Carbon::parse($checkOut)->startOfDay();

        if ($fin->lessThanOrEqualTo($inicio)) {
            throw ValidationException::withMessages([
                'check_out' => 'El check-out debe ser posterior al check-in.',
            ]);
        }

        $noches = (int) $inicio->diffInDays($fin);

        if ($noches < 1) {
            throw ValidationException::withMessages([
                'check_out' => 'La estancia debe incluir al menos una noche.',
            ]);
        }

        $estadoReserva = $estado instanceof EstadoReserva ? $estado : EstadoReserva::from($estado);

        return DB::transaction(function () use (
            $clienteId,
            $habitacionId,
            $inicio,
            $fin,
            $huespedes,
            $userId,
            $estadoReserva,
            $noches,
        ): Reserva {
            $habitacion = $this->disponibilidad->bloquearHabitacion($habitacionId);

            if ($habitacion->estado === EstadoHabitacion::Mantenimiento->value) {
                throw ValidationException::withMessages([
                    'habitacion_id' => 'La habitación está en mantenimiento y no puede reservarse.',
                ]);
            }

            $capacidad = (int) ($habitacion->tipoHabitacion?->capacidad ?? 0);

            if ($capacidad > 0 && $huespedes > $capacidad) {
                throw ValidationException::withMessages([
                    'num_huespedes' => "La habitación admite hasta {$capacidad} huéspedes.",
                ]);
            }

            if ($this->disponibilidad->hayConflicto($habitacion->id, $inicio, $fin)) {
                throw ValidationException::withMessages([
                    'habitacion_id' => 'La habitación ya no está disponible para las fechas seleccionadas.',
                ]);
            }

            $precioPorNoche = (float) ($habitacion->tipoHabitacion?->precio_base ?? 0);

            $reserva = Reserva::create([
                'cliente_id' => $clienteId,
                'user_id' => $userId,
                'check_in' => $inicio->toDateString(),
                'check_out' => $fin->toDateString(),
                'estado' => $estadoReserva->value,
                'monto_total' => round($precioPorNoche * $noches, 2),
                'num_huespedes' => $huespedes,
            ]);

            $reserva->habitacionesAsignadas()->create([
                'habitacion_id' => $habitacion->id,
                'precio_por_noche' => $precioPorNoche,
            ]);

            return $reserva;
        });
    }
}
