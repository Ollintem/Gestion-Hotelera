<?php

namespace App\Models;

use App\Enums\EstadoHabitacion;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;

class Habitacion extends Model
{
    protected $table = 'habitaciones';

    protected $fillable = [
        'numero_habitacion',
        'tipo_habitacion_id',
        'estado',
        'piso',
        'foto',
    ];

    /**
     * Disco de Laravel donde se guardan las fotografías de las habitaciones.
     */
    public const DISCO_FOTOS = 'public';

    /**
     * Carpeta dentro del disco donde se guardan las fotografías de las
     * habitaciones, ya convertidas a WebP.
     */
    public const CARPETA_FOTOS = 'habitaciones';

    /**
     * Estados admitidos por la columna `estado` de la tabla `habitaciones`.
     *
     * @var list<string>
     */
    public const ESTADOS = ['Disponible', 'Ocupada', 'Mantenimiento', 'Limpieza'];

    public const ESTADO_DISPONIBLE = 'Disponible';

    public const ESTADO_OCUPADA = 'Ocupada';

    public const ESTADO_MANTENIMIENTO = 'Mantenimiento';

    public const ESTADO_LIMPIEZA = 'Limpieza';

    public function estadoEnum(): EstadoHabitacion
    {
        return EstadoHabitacion::from($this->estado);
    }

    /**
     * Fotografías de muestra para las habitaciones sin imagen propia. La imagen
     * se elige de forma determinista a partir de una semilla.
     *
     * @var list<string>
     */
    public const IMAGENES = [
        'https://images.unsplash.com/photo-1611892440504-42a792e24d32?auto=format&fit=crop&w=800&q=80',
        'https://images.unsplash.com/photo-1582719478250-c89cae4dc85b?auto=format&fit=crop&w=800&q=80',
        'https://images.unsplash.com/photo-1631049307264-da0ec9d70304?auto=format&fit=crop&w=800&q=80',
    ];

    /**
     * Fotografía de esta habitación: la que se subió al inventario o, en su
     * defecto, una fotografía de muestra elegida de forma determinista.
     */
    public function imagen(): string
    {
        return $this->urlFoto() ?? self::imagenPredeterminada($this->numero_habitacion);
    }

    /**
     * URL pública de la fotografía subida, o `null` si la habitación todavía no
     * tiene una fotografía propia.
     */
    public function urlFoto(): ?string
    {
        if (blank($this->foto)) {
            return null;
        }

        return Storage::disk(self::DISCO_FOTOS)->url($this->foto);
    }

    /**
     * Elige la fotografía de muestra correspondiente a una semilla arbitraria.
     */
    public static function imagenPredeterminada(string $semilla): string
    {
        return self::IMAGENES[abs(crc32($semilla)) % count(self::IMAGENES)];
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'piso' => 'integer',
        ];
    }

    /**
     * @return BelongsTo<TipoHabitacion, $this>
     */
    public function tipoHabitacion(): BelongsTo
    {
        return $this->belongsTo(TipoHabitacion::class, 'tipo_habitacion_id');
    }

    /**
     * @return HasMany<ReservaHabitacion, $this>
     */
    public function reservasHabitacion(): HasMany
    {
        return $this->hasMany(ReservaHabitacion::class, 'habitacion_id');
    }

    /**
     * @return BelongsToMany<Reserva>
     */
    public function reservas(): BelongsToMany
    {
        return $this->belongsToMany(Reserva::class, 'reserva_habitacion', 'habitacion_id', 'reserva_id')
            ->withPivot('precio_por_noche')
            ->withTimestamps();
    }

    /**
     * @return HasMany<Limpieza, $this>
     */
    public function tareasLimpieza(): HasMany
    {
        return $this->hasMany(Limpieza::class, 'habitacion_id');
    }
}
