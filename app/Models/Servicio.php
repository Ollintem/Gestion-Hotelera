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

    /**
     * Catálogo cerrado de servicios que el hotel presta. El alta y la edición
     * eligen de esta lista en lugar de escribir un nombre libre, de modo que el
     * mismo concepto no se registre dos veces con dos grafías. El orden es el
     * que usa el desplegable del formulario.
     *
     * Un servicio ya dado de alta con otro nombre se conserva: al editarlo,
     * `nombreFueraDeCatalogo` lo mantiene como opción y la validación lo acepta.
     *
     * @var list<string>
     */
    public const CATALOGO = [
        'Servicio a la habitación',
        'Desayuno buffet',
        'Lavandería',
        'Spa y masajes',
        'Traslado al aeropuerto',
    ];

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
     * El nombre del servicio pertenece al catálogo cerrado del hotel. Un nombre
     * fuera de él es heredado: se conserva al editar, pero no se admite en el
     * alta.
     */
    public function estaEnCatalogo(): bool
    {
        return in_array($this->nombre, self::CATALOGO, true);
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
