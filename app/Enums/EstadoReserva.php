<?php

namespace App\Enums;

/**
 * Ciclo de vida de una reservación.
 *
 * Pendiente -> Confirmada (check-in) -> Finalizada (check-out).
 * Pendiente -> Cancelada.
 */
enum EstadoReserva: string
{
    case Pendiente = 'Pendiente';
    case Confirmada = 'Confirmada';
    case Cancelada = 'Cancelada';
    case Finalizada = 'Finalizada';

    /**
     * @return list<string>
     */
    public static function valores(): array
    {
        return array_map(static fn (self $estado): string => $estado->value, self::cases());
    }

    public function permiteCheckIn(): bool
    {
        return $this === self::Pendiente;
    }

    public function permiteCheckOut(): bool
    {
        return $this === self::Confirmada;
    }

    public function permiteCancelacion(): bool
    {
        return $this === self::Pendiente;
    }
}
