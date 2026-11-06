<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Conserva el número de huéspedes capturado al reservar. Las reservaciones
     * anteriores a esta columna quedan en null y se muestran con la capacidad
     * de sus habitaciones, por lo que no se altera ningún registro existente.
     */
    public function up(): void
    {
        Schema::table('reservas', function (Blueprint $table) {
            $table->unsignedSmallInteger('num_huespedes')->nullable()->after('monto_total');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('reservas', function (Blueprint $table) {
            $table->dropColumn('num_huespedes');
        });
    }
};
