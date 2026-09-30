<?php

namespace Database\Factories;

use App\Models\Cliente;
use App\Models\Reserva;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Reserva>
 */
class ReservaFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $checkIn = fake()->dateTimeBetween('-10 days', '+10 days');

        return [
            'cliente_id' => Cliente::factory(),
            'user_id' => User::factory(),
            'check_in' => $checkIn,
            'check_out' => (clone $checkIn)->modify('+'.fake()->numberBetween(1, 5).' days'),
            'estado' => 'Pendiente',
            'monto_total' => fake()->randomFloat(2, 1000, 15000),
        ];
    }

    /**
     * Reserva whose stay already ended, so its cargos count as facturados.
     */
    public function finalizada(): static
    {
        return $this->state(fn (array $attributes) => [
            'estado' => 'Finalizada',
        ]);
    }
}
