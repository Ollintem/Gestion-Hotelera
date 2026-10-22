<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Servicio extends Model
{
    use HasFactory;

    protected $table = 'servicios';

    protected $fillable = [
        'nombre',
        'descripcion',
        'categoria_id',
        'precio',
        'activo',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'precio' => 'decimal:2',
            'activo' => 'boolean',
        ];
    }

    /**
     * Precio de catálogo con el formato de moneda en pesos mexicanos que usa
     * todo el panel.
     */
    public function precioEnPesos(): string
    {
        return '$'.number_format((float) $this->precio, 2);
    }

    /**
     * @return HasMany<ReservaServicio, $this>
     */
    public function reservasServicio(): HasMany
    {
        return $this->hasMany(ReservaServicio::class, 'servicio_id');
    }

    /**
     * Categoría del catálogo en la que se clasifica el servicio.
     *
     * No se llama `categoria` a propósito: la tabla conserva la columna de
     * texto `categoria` de una versión anterior y Eloquent resuelve primero los
     * atributos, de modo que una relación homónima quedaría tapada y
     * `$servicio->categoria` devolvería siempre el valor de la columna.
     *
     * @return BelongsTo<Categoria, $this>
     */
    public function clasificacion(): BelongsTo
    {
        return $this->belongsTo(Categoria::class, 'categoria_id');
    }
}
