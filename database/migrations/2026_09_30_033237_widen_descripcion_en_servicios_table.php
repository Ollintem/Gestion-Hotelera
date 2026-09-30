<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * La descripción del servicio pasó de 255 a 500 caracteres: es el texto que
     * recepción lee en el mostrador para saber qué incluye el servicio, y a 255
     * se cortaban a la mitad las descripciones largas. La validación del
     * formulario admite 500, así que la columna debe poder guardarlos; si no, el
     * error aparecería en la base y no en el formulario.
     */
    public function up(): void
    {
        Schema::table('servicios', function (Blueprint $table) {
            $table->string('descripcion', 500)->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('servicios', function (Blueprint $table) {
            $table->string('descripcion', 255)->nullable()->change();
        });
    }
};
