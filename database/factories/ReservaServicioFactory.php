<?php

namespace Database\Factories;

use App\Models\Reserva;
use App\Models\ReservaServicio;
use App\Models\Servicio;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ReservaServicio>
 */
class ReservaServicioFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $cantidad = fake()->numberBetween(1, 3);
        $precio = fake()->randomFloat(2, 50, 1500);

        return [
            'reserva_id' => Reserva::factory(),
            'servicio_id' => Servicio::factory(),
            'cantidad' => $cantidad,
            'subtotal' => $cantidad * $precio,
        ];
    }
}
