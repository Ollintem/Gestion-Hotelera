<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Periodo del calendario con su propio precio.
 *
 * El precio que se cobra nunca se escribe: se deriva de
 * `precio_base * multiplicador_precio`, de modo que corregir un multiplicador no
 * deja precios viejos repartidos por el módulo.
 *
 * El nivel de demanda tampoco se guarda. Es la lectura del multiplicador —por
 * debajo de la referencia abarata, por encima encarece, exactamente igual no
 * cambia— y guardarlo abriría la puerta a un factor y una etiqueta que se
 * contradicen: una temporada marcada como "alta demanda" con multiplicador
 * `0.85`. La regla vive en `nivelDemanda()` y el color en las vistas.
 */
class Temporada extends Model
{
    use HasFactory;

    protected $table = 'temporadas';

    /**
     * Cómo clasifica recepción el periodo, deducido del multiplicador. Es un
     * catálogo cerrado porque el módulo lo usa tanto para el texto de la tarjeta
     * y la tabla como para elegir el color del indicador, y las dos cosas tienen
     * que contar la misma historia.
     *
     * @var array<string, string>
     */
    public const NIVELES_DEMANDA = [
        'baja' => 'Baja demanda',
        'media' => 'Demanda normal',
        'alta' => 'Alta demanda',
    ];

    protected $fillable = [
        'nombre',
        'fecha_inicio',
        'fecha_fin',
        'multiplicador_precio',
        'precio_base',
        'activo',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'fecha_inicio' => 'date',
            'fecha_fin' => 'date',
            'multiplicador_precio' => 'decimal:2',
            'precio_base' => 'decimal:2',
            'activo' => 'boolean',
        ];
    }

    /**
     * Precio que realmente se cobra durante la temporada: el precio base por el
     * multiplicador. Se redondea a dos decimales porque es dinero, y se devuelve
     * como float para poder compararlo y sumarlo sin arrastrar la cadena que
     * produce el cast `decimal`.
     */
    protected function precioEfectivo(): Attribute
    {
        return Attribute::get(
            fn (): float => round((float) $this->precio_base * (float) $this->multiplicador_precio, 2)
        );
    }

    /**
     * Nivel de demanda que corresponde al multiplicador. `0.85` es una oferta
     * que hay que empujar, `1.40` un recargo que hay que justificar y `1.00` el
     * precio de referencia.
     */
    public function nivelDemanda(): string
    {
        return match (true) {
            (float) $this->multiplicador_precio < 1 => 'baja',
            (float) $this->multiplicador_precio > 1 => 'alta',
            default => 'media',
        };
    }

    /**
     * Nombre del nivel de demanda tal y como se muestra en la interfaz.
     */
    public function etiquetaNivelDemanda(): string
    {
        return self::NIVELES_DEMANDA[$this->nivelDemanda()];
    }

    /**
     * Variación que introduce el multiplicador sobre el precio base, en
     * porcentaje. `1.25` es `25` y `0.85` es `-15`: sirve para pintar el precio
     * efectivo como recargo o como descuento.
     */
    public function variacionPrecio(): float
    {
        return round(((float) $this->multiplicador_precio - 1) * 100, 2);
    }

    /**
     * Noches que abarca el periodo, contando el día de llegada. Un periodo de
     * una sola noche devuelve 1.
     */
    public function duracionNoches(): int
    {
        return (int) $this->fecha_inicio->diffInDays($this->fecha_fin) + 1;
    }

    /**
     * Precio base con el formato de moneda en pesos mexicanos que usa todo el
     * panel.
     */
    public function precioBaseEnPesos(): string
    {
        return '$'.number_format((float) $this->precio_base, 2);
    }

    /**
     * Precio efectivo con el formato de moneda del panel.
     */
    public function precioEfectivoEnPesos(): string
    {
        return '$'.number_format((float) $this->precio_efectivo, 2);
    }

    /**
     * Multiplicador con el signo de multiplicación delante, que es como lo lee
     * recepción en voz alta: `×0.85`, `×1.40`.
     */
    public function multiplicadorEnTexto(): string
    {
        return '×'.number_format((float) $this->multiplicador_precio, 2);
    }
}
