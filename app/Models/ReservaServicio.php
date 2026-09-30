<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReservaServicio extends Model
{
    use HasFactory;

    protected $table = 'reserva_servicio';

    protected $fillable = [
        'reserva_id',
        'servicio_id',
        'cantidad',
        'precio_aplicado',
        'empleado_id',
        'subtotal',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'cantidad' => 'integer',
            'precio_aplicado' => 'decimal:2',
            'subtotal' => 'decimal:2',
        ];
    }

    /**
     * @return BelongsTo<Reserva, $this>
     */
    public function reserva(): BelongsTo
    {
        return $this->belongsTo(Reserva::class, 'reserva_id');
    }

    /**
     * @return BelongsTo<Servicio, $this>
     */
    public function servicio(): BelongsTo
    {
        return $this->belongsTo(Servicio::class, 'servicio_id');
    }

    /**
     * Empleado de recepción que registró el cargo en el folio.
     *
     * @return BelongsTo<Empleado, $this>
     */
    public function empleado(): BelongsTo
    {
        return $this->belongsTo(Empleado::class, 'empleado_id');
    }

    /**
     * Precio unitario que se cobró. Los cargos anteriores a la columna
     * `precio_aplicado` caen al precio de catálogo del servicio.
     */
    public function precioUnitario(): float
    {
        return round((float) ($this->precio_aplicado ?? $this->servicio?->precio ?? 0), 2);
    }
}
