<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Completa el calendario con las dos columnas que el módulo necesita para
     * funcionar.
     *
     * `precio_base` es la tarifa de referencia de la que parte el multiplicador:
     * el precio que se cobra nunca se guarda, se deriva de esta columna, así que
     * llega con cero y no nula para que el accessor nunca reciba un null.
     *
     * `activo` permite retirar una temporada del calendario sin borrarla: su
     * histórico sigue siendo consultable y volver a activarla la devuelve al
     * listado.
     */
    public function up(): void
    {
        Schema::table('temporadas', function (Blueprint $table) {
            $table->decimal('precio_base', 10, 2)->default(0)->after('multiplicador_precio');
            $table->boolean('activo')->default(true)->after('precio_base');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('temporadas', function (Blueprint $table) {
            $table->dropColumn(['precio_base', 'activo']);
        });
    }
};
