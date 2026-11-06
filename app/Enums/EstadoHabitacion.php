<?php

namespace App\Enums;

/**
 * Estado operativo de una habitación. Es independiente de las reservaciones
 * futuras: una habitación ocupada hoy puede reservarse para una fecha posterior
 * si no existe un cruce de fechas.
 */
enum EstadoHabitacion: string
{
    case Disponible = 'Disponible';
    case Ocupada = 'Ocupada';
    case Mantenimiento = 'Mantenimiento';
    case Limpieza = 'Limpieza';

    /**
     * @return list<string>
     */
    public static function valores(): array
    {
        return array_map(static fn (self $estado): string => $estado->value, self::cases());
    }

    public function bloqueaDisponibilidadFutura(): bool
    {
        return $this === self::Mantenimiento;
    }
}
