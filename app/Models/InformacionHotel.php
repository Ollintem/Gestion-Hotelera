<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Datos generales del hotel.
 *
 * La tabla existe para alojar un único registro: `current()` lo garantiza con
 * `firstOrCreate` sobre el id 1, de modo que el módulo de Configuración siempre
 * trabaja sobre la misma fila y nunca hay que buscar ni crear registros a mano.
 */
class InformacionHotel extends Model
{
    /**
     * Identificador fijo del registro único de configuración del hotel.
     */
    public const REGISTRO_UNICO = 1;

    protected $table = 'informacion_hotel';

    /**
     * La fila siempre nace con este id: es la única que leerá la aplicación.
     *
     * @var list<int>
     */
    protected $fillable = [
        'nombre',
        'rfc',
        'direccion',
        'ciudad',
        'telefono',
        'email',
        'sitio_web',
        'numero_habitaciones',
        'descripcion',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'numero_habitaciones' => 'integer',
        ];
    }

    /**
     * Registro único de la información del hotel. Si aún no existe lo crea con
     * un nombre por defecto para que el formulario siempre tenga dónde guardar.
     */
    public static function current(): self
    {
        return self::query()->firstOrCreate(
            ['id' => self::REGISTRO_UNICO],
            ['nombre' => 'Mi hotel']
        );
    }

    /**
     * Dato del hotel o el valor indicado cuando la columna está vacía. Sirve
     * para pintar «—» en los campos que aún no se han capturado.
     */
    public function valor(string $campo): string
    {
        $valor = $this->getAttribute($campo);

        if ($valor === null || $valor === '') {
            return '';
        }

        return (string) $valor;
    }

    /**
     * Habitaciones que declara el hotel frente a las que el sistema tiene
     * registradas. Al guardar una cifra distinta, la diferencia es visible y
     * no se pierde el dato real detrás de un número fijo.
     */
    public function habitacionesRegistradas(): int
    {
        return (int) Habitacion::count();
    }
}
