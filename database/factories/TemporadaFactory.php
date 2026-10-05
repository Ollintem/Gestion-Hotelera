<?php

namespace Database\Factories;

use App\Models\Temporada;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Temporada>
 */
class TemporadaFactory extends Factory
{
    /**
     * Las fechas siempre son coherentes entre sí: el rango se sortea a partir de
     * la fecha de inicio para que ninguna temporada nazca con la fecha de fin
     * antes que la de inicio, que el formulario no permite guardar.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $inicio = fake()->dateTimeBetween('-2 months', '+6 months');

        return [
            'nombre' => fake()->unique()->words(2, true),
            'fecha_inicio' => $inicio->format('Y-m-d'),
            'fecha_fin' => fake()->dateTimeBetween($inicio, (clone $inicio)->modify('+3 months'))->format('Y-m-d'),
            'multiplicador_precio' => fake()->randomFloat(2, 0.5, 2.0),
            'precio_base' => fake()->randomFloat(2, 500, 2500),
            'activo' => true,
        ];
    }

    /**
     * Temporada retirada del calendario pero conservada en el histórico.
     */
    public function inactiva(): static
    {
        return $this->state(fn (array $attributes): array => [
            'activo' => false,
        ]);
    }

    /**
     * Temporada con descuento sobre el precio base: se pinta de verde.
     */
    public function conDescuento(float $multiplicador = 0.85): static
    {
        return $this->state(fn (array $attributes): array => [
            'multiplicador_precio' => $multiplicador,
        ]);
    }

    /**
     * Temporada con recargo sobre el precio base: se pinta de rojo.
     */
    public function conRecargo(float $multiplicador = 1.40): static
    {
        return $this->state(fn (array $attributes): array => [
            'multiplicador_precio' => $multiplicador,
        ]);
    }
}
