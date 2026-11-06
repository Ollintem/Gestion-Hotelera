<?php

namespace App\Policies;

use App\Models\Reserva;
use App\Models\User;

class ReservaPolicy
{
    /**
     * Un cliente solo puede consultar las reservaciones que le pertenecen,
     * ya sea por el vínculo directo `user_id` o por su ficha de cliente.
     */
    public function view(User $user, Reserva $reserva): bool
    {
        if (! $user->hasRole('cliente')) {
            return false;
        }

        return (int) $reserva->user_id === $user->id
            || (int) $reserva->cliente?->user_id === $user->id;
    }
}
