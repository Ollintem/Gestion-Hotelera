<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;

class Reserva extends Model
{
    use HasFactory;

    protected $table = 'reservas';

    /**
     * Estados admitidos por la columna `estado` de la tabla `reservas`.
     *
     * @var list<string>
     */
    public const ESTADOS = ['Pendiente', 'Confirmada', 'Cancelada', 'Finalizada'];

    /**
     * Estado en el que la reservación ya cerró y sus consumos extras pasaron a
     * ser facturados.
     */
    public const ESTADO_FINALIZADA = 'Finalizada';

    /**
     * Reservaciones cuyo huésped ya está en el hotel. Solo sobre ellas se admiten
     * consumos: un servicio se presta a una habitación ocupada.
     *
     * @var list<string>
     */
    public const ESTADOS_ACTIVOS = ['Confirmada'];

    protected $fillable = [
        'cliente_id',
        'user_id',
        'check_in',
        'check_out',
        'estado',
        'monto_total',
    ];

    /**
     * La reservación admite cargos de servicios adicionales.
     */
    public function estaActiva(): bool
    {
        return in_array($this->estado, self::ESTADOS_ACTIVOS, true);
    }

    /**
     * Noches ocupadas entre la llegada y la salida.
     */
    public function totalNoches(): float
    {
        return (float) $this->check_in->diffInDays($this->check_out);
    }

    /**
     * Importe de las habitaciones por las noches ocupadas. Cuando la reservación
     * no trae desglose por habitación se recurre al total que se cotizó al
     * confirmar.
     */
    public function tarifaHabitaciones(): float
    {
        $noches = $this->totalNoches();

        $tarifa = (float) $this->habitacionesAsignadas()
            ->get()
            ->reduce(
                fn (float $carry, $asignacion) => $carry + ((float) $asignacion->precio_por_noche * $noches),
                0.0
            );

        return $tarifa > 0 ? round($tarifa, 2) : round((float) $this->monto_total, 2);
    }

    /**
     * Suma de los cargos por consumos extras registrados en el folio.
     */
    public function subtotalServicios(): float
    {
        return round((float) $this->serviciosAsignados()->sum('subtotal'), 2);
    }

    /**
     * Importe ya abonado por el huésped. Solo cuentan los pagos completados:
     * los anulados y los que siguen pendientes no reducen el saldo.
     */
    public function totalPagado(): float
    {
        return round((float) $this->pagos()
            ->where('estado', Pago::ESTADO_COMPLETADO)
            ->sum('monto'), 2);
    }

    /**
     * Total que la reservación sumará al cerrar: tarifa de habitación más
     * consumos extras. Es la cifra que el estado de cuenta presenta al huésped.
     */
    public function totalConsumos(): float
    {
        return round($this->tarifaHabitaciones() + $this->subtotalServicios(), 2);
    }

    /**
     * Saldo que queda por cobrar al cerrar la reservación.
     */
    public function saldoPendiente(): float
    {
        return round(max(0.0, $this->totalConsumos() - $this->totalPagado()), 2);
    }

    /**
     * La estancia está liquidada: no hay saldo por cobrar. Se exige un consumo
     * mayor a cero para no declarar pagada una reservación sin cargos.
     */
    public function estaPagada(): bool
    {
        return $this->totalConsumos() > 0 && $this->saldoPendiente() <= 0.0;
    }

    /**
     * Cierra la estadía: libera sus habitaciones a limpieza, genera las tareas
     * pendientes y marca la reservación como finalizada. Lo comparten el
     * check-out manual de recepción y el cierre automático al liquidar el saldo.
     */
    public function realizarCheckOut(): void
    {
        DB::transaction(function (): void {
            $this->loadMissing('habitaciones');

            foreach ($this->habitaciones as $habitacion) {
                $habitacion->update(['estado' => 'Limpieza']);

                Limpieza::firstOrCreate(
                    ['habitacion_id' => $habitacion->id],
                    [
                        'user_id' => auth()->id(),
                        'estado' => 'Pendiente',
                        'notas' => 'Check-out de la reserva #'.$this->id,
                    ]
                );
            }

            $this->update(['estado' => self::ESTADO_FINALIZADA]);
        });
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'check_in' => 'date',
            'check_out' => 'date',
            'monto_total' => 'decimal:2',
        ];
    }

    /**
     * @return BelongsTo<Cliente, $this>
     */
    public function cliente(): BelongsTo
    {
        return $this->belongsTo(Cliente::class, 'cliente_id');
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * @return HasMany<ReservaHabitacion, $this>
     */
    public function habitacionesAsignadas(): HasMany
    {
        return $this->hasMany(ReservaHabitacion::class, 'reserva_id');
    }

    /**
     * @return BelongsToMany<Habitacion>
     */
    public function habitaciones(): BelongsToMany
    {
        return $this->belongsToMany(Habitacion::class, 'reserva_habitacion', 'reserva_id', 'habitacion_id')
            ->withPivot('precio_por_noche')
            ->withTimestamps();
    }

    /**
     * @return HasMany<ReservaServicio, $this>
     */
    public function serviciosAsignados(): HasMany
    {
        return $this->hasMany(ReservaServicio::class, 'reserva_id');
    }

    /**
     * @return HasMany<Pago, $this>
     */
    public function pagos(): HasMany
    {
        return $this->hasMany(Pago::class, 'reserva_id');
    }
}
