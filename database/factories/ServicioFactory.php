<?php

namespace Database\Factories;

use App\Models\Categoria;
use App\Models\Servicio;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Servicio>
 */
class ServicioFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'nombre' => fake()->unique()->words(2, true),
            'descripcion' => fake()->optional()->sentence(),
            'categoria_id' => Categoria::factory(),
            'precio' => fake()->randomFloat(2, 50, 1500),
            'activo' => true,
        ];
    }

    /**
     * Servicio retirado del catálogo pero conservado en el historial.
     */
    public function inactivo(): static
    {
        return $this->state(fn (array $attributes) => [
            'activo' => false,
        ]);
    }

    /**
     * Servicio heredado, dado de alta antes de que existiera la clasificación.
     */
    public function sinCategoria(): static
    {
        return $this->state(fn (array $attributes) => [
            'categoria_id' => null,
        ]);
    }
}
