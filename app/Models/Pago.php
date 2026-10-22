<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Pago extends Model
{
    protected $table = 'pagos';

    /**
     * Métodos de pago admitidos por la columna `metodo_pago`.
     *
     * @var list<string>
     */
    public const METODOS = ['Efectivo', 'Tarjeta', 'Transferencia'];

    /**
     * Métodos que exigen una referencia o folio del comprobante.
     *
     * @var list<string>
     */
    public const METODOS_CON_REFERENCIA = ['Tarjeta', 'Transferencia'];

    /**
     * Estados admitidos por la columna `estado`.
     *
     * @var list<string>
     */
    public const ESTADOS = ['Completado', 'Pendiente', 'Anulado'];

    public const ESTADO_COMPLETADO = 'Completado';

    public const ESTADO_PENDIENTE = 'Pendiente';

    public const ESTADO_ANULADO = 'Anulado';

    protected $fillable = [
        'reserva_id',
        'monto',
        'metodo_pago',
        'referencia',
        'estado',
        'fecha_pago',
        'notas',
        'user_id',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'monto' => 'decimal:2',
            'fecha_pago' => 'datetime',
        ];
    }

    /**
     * El método elegido obliga a capturar una referencia o folio.
     */
    public function requiereReferencia(): bool
    {
        return in_array($this->metodo_pago, self::METODOS_CON_REFERENCIA, true);
    }

    /**
     * Solo los pagos completados abonan al saldo de la reserva.
     */
    public function abona(): bool
    {
        return $this->estado === self::ESTADO_COMPLETADO;
    }

    /**
     * @return BelongsTo<Reserva, $this>
     */
    public function reserva(): BelongsTo
    {
        return $this->belongsTo(Reserva::class, 'reserva_id');
    }

    /**
     * Recepcionista que registró el pago.
     *
     * @return BelongsTo<User, $this>
     */
    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
