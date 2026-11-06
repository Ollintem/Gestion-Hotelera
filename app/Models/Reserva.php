<?php

namespace App\Models;

use App\Enums\EstadoHabitacion;
use App\Enums\EstadoReserva;
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

    public const ESTADO_PENDIENTE = 'Pendiente';

    public const ESTADO_CONFIRMADA = 'Confirmada';

    public const ESTADO_CANCELADA = 'Cancelada';

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
    public const ESTADOS_ACTIVOS = [self::ESTADO_CONFIRMADA];

    protected $fillable = [
        'cliente_id',
        'user_id',
        'user_checkin_id',
        'user_checkout_id',
        'check_in',
        'check_out',
        'fecha_hora_checkin',
        'fecha_hora_checkout',
        'observaciones_checkin',
        'observaciones_checkout',
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
    public function estadoEnum(): EstadoReserva
    {
        return EstadoReserva::from($this->estado);
    }

    /**
     * Una reservacion admite check-in mientras no se haya registrado la llegada.
     * Las reservaciones antiguas creadas como Confirmada sin fecha real de
     * llegada se tratan como pendientes para no perder compatibilidad.
     */
    public function puedeHacerCheckIn(): bool
    {
        return $this->estadoEnum()->permiteCheckIn()
            || ($this->estado === self::ESTADO_CONFIRMADA && $this->fecha_hora_checkin === null);
    }

    public function puedeHacerCheckOut(): bool
    {
        return $this->estadoEnum()->permiteCheckOut() && $this->fecha_hora_checkout === null;
    }

    public function puedeCancelarse(): bool
    {
        return $this->estadoEnum()->permiteCancelacion();
    }

    public function totalNoches(): float
    {
        return (float) $this->check_in->copy()->startOfDay()->diffInDays($this->check_out->copy()->startOfDay());
    }

    /**
     * Importe de las habitaciones por las noches ocupadas. Cuando la reservación
     * no trae desglose por habitación se recurre al total que se cotizó al
     * confirmar.
     */
    public function tarifaHabitaciones(): float
    {
        $noches = $this->totalNoches();

        $asignaciones = $this->relationLoaded('habitacionesAsignadas')
            ? $this->habitacionesAsignadas
            : $this->habitacionesAsignadas()->get();

        $tarifa = (float) $asignaciones->reduce(
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
        $servicios = $this->relationLoaded('serviciosAsignados')
            ? $this->serviciosAsignados
            : $this->serviciosAsignados()->get();

        return round((float) $servicios->sum('subtotal'), 2);
    }

    /**
     * Importe ya abonado por el huésped. Solo cuentan los pagos completados:
     * los anulados y los que siguen pendientes no reducen el saldo.
     */
    public function totalPagado(): float
    {
        $pagos = $this->relationLoaded('pagos')
            ? $this->pagos
            : $this->pagos()->get();

        return round((float) $pagos
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
     *
     * El motivo opcional queda registrado en `observaciones_checkout`; cuando no
     * se envía, la columna se guarda vacía.
     */
    public function realizarCheckOut(string $motivo = ''): void
    {
        if (! $this->puedeHacerCheckOut()) {
            return;
        }

        DB::transaction(function () use ($motivo): void {
            $this->loadMissing('habitaciones');

            foreach ($this->habitaciones as $habitacion) {
                $habitacion->update(['estado' => EstadoHabitacion::Limpieza->value]);

                Limpieza::firstOrCreate(
                    ['habitacion_id' => $habitacion->id],
                    [
                        'user_id' => auth()->id(),
                        'estado' => 'Pendiente',
                        'notas' => 'Check-out de la reserva #'.$this->id,
                    ]
                );
            }

            $this->update([
                'estado' => self::ESTADO_FINALIZADA,
                'fecha_hora_checkout' => now(),
                'user_checkout_id' => auth()->id(),
                'observaciones_checkout' => $motivo !== '' ? $motivo : null,
            ]);
        });
    }

    /**
     * Registra la llegada del huésped: fija la fecha y hora reales, deja
     * constancia de quién lo hizo, pasa la reservación a «Confirmada» y marca
     * sus habitaciones como ocupadas. Solo se admite sobre reservas aún
     * pendientes, de modo que un segundo intento queda sin efecto.
     */
    public function realizarCheckIn(string $motivo = ''): void
    {
        if (! $this->puedeHacerCheckIn()) {
            return;
        }

        DB::transaction(function () use ($motivo): void {
            $this->update([
                'estado' => self::ESTADO_CONFIRMADA,
                'fecha_hora_checkin' => now(),
                'user_checkin_id' => auth()->id(),
                'observaciones_checkin' => $motivo !== '' ? $motivo : null,
            ]);

            $this->loadMissing('habitaciones');

            foreach ($this->habitaciones as $habitacion) {
                $habitacion->update(['estado' => EstadoHabitacion::Ocupada->value]);
            }
        });
    }

    /**
     * Cancela una reservacion que aun no registro su llegada. No toca el estado
     * de las habitaciones salvo que una version anterior las hubiera marcado
     * como "Reservada"; nunca libera una habitacion ocupada por otra estancia.
     */
    public function cancelar(): bool
    {
        if (! $this->puedeCancelarse()) {
            return false;
        }

        DB::transaction(function (): void {
            $this->loadMissing('habitaciones');

            foreach ($this->habitaciones as $habitacion) {
                if ($habitacion->estado === 'Reservada') {
                    $habitacion->update(['estado' => EstadoHabitacion::Disponible->value]);
                }
            }

            $this->update(['estado' => self::ESTADO_CANCELADA]);
        });

        return true;
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'check_in' => 'date',
            'check_out' => 'date',
            'fecha_hora_checkin' => 'datetime',
            'fecha_hora_checkout' => 'datetime',
            'monto_total' => 'decimal:2',
        ];
    }

    /**
     * Empleado que registró la llegada.
     *
     * @return BelongsTo<User, $this>
     */
    public function usuarioCheckIn(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_checkin_id');
    }

    /**
     * Empleado que registró la salida.
     *
     * @return BelongsTo<User, $this>
     */
    public function usuarioCheckOut(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_checkout_id');
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
