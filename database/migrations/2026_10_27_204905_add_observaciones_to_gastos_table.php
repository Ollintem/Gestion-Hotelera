<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Observaciones del gasto: información adicional opcional que recepción deja
 * al registrar o editar el gasto. Anulable para no afectar los registros
 * existentes.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('gastos', function (Blueprint $table): void {
            $table->text('observaciones')->nullable()->after('fecha_gasto');
        });
    }

    public function down(): void
    {
        Schema::table('gastos', function (Blueprint $table): void {
            $table->dropColumn('observaciones');
        });
    }
};
