<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Indices para las consultas mas frecuentes: disponibilidad por fechas,
     * estado de reservaciones, folios, pagos y tareas de limpieza.
     *
     * @var array<string, array<string, list<string>>>
     */
    private array $indices = [
        'reservas' => [
            'reservas_estado_fechas_index' => ['estado', 'check_in', 'check_out'],
            'reservas_cliente_estado_index' => ['cliente_id', 'estado'],
        ],
        'reserva_habitacion' => [
            'reserva_habitacion_habitacion_reserva_index' => ['habitacion_id', 'reserva_id'],
        ],
        'pagos' => [
            'pagos_reserva_estado_index' => ['reserva_id', 'estado'],
            'pagos_fecha_estado_index' => ['fecha_pago', 'estado'],
        ],
        'limpieza' => [
            'limpieza_estado_habitacion_index' => ['estado', 'habitacion_id'],
            'limpieza_user_estado_index' => ['user_id', 'estado'],
        ],
        'clientes' => [
            'clientes_email_index' => ['email'],
            'clientes_nombre_apellido_index' => ['nombre', 'apellido'],
        ],
        'habitaciones' => [
            'habitaciones_estado_piso_index' => ['estado', 'piso'],
        ],
        'reserva_servicio' => [
            'reserva_servicio_reserva_servicio_index' => ['reserva_id', 'servicio_id'],
        ],
        'servicios' => [
            'servicios_categoria_activo_index' => ['categoria_id', 'activo'],
        ],
    ];

    public function up(): void
    {
        foreach ($this->indices as $tabla => $indices) {
            foreach ($indices as $nombre => $columnas) {
                if (Schema::hasIndex($tabla, $columnas)) {
                    continue;
                }

                Schema::table($tabla, function (Blueprint $table) use ($columnas, $nombre): void {
                    $table->index($columnas, $nombre);
                });
            }
        }
    }

    public function down(): void
    {
        foreach ($this->indices as $tabla => $indices) {
            foreach ($indices as $nombre => $columnas) {
                if (! Schema::hasIndex($tabla, $nombre)) {
                    continue;
                }

                Schema::table($tabla, function (Blueprint $table) use ($nombre): void {
                    $table->dropIndex($nombre);
                });
            }
        }
    }
};
