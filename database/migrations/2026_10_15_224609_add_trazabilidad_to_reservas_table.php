<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Trazabilidad del check-in y del check-out: cuándo ocurrió realmente la
 * llegada y la salida, quién las registró y qué observaciones dejó recepción.
 *
 * Todas las columnas son anulables para no afectar las reservaciones ya
 * registradas antes de este módulo.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('reservas', function (Blueprint $table): void {
            $table->timestamp('fecha_hora_checkin')->nullable()->after('check_out');
            $table->timestamp('fecha_hora_checkout')->nullable()->after('fecha_hora_checkin');
            $table->foreignId('user_checkin_id')->nullable()->after('user_id')
                ->constrained('users')->nullOnDelete();
            $table->foreignId('user_checkout_id')->nullable()->after('user_checkin_id')
                ->constrained('users')->nullOnDelete();
            $table->text('observaciones_checkin')->nullable()->after('fecha_hora_checkout');
            $table->text('observaciones_checkout')->nullable()->after('observaciones_checkin');
        });
    }

    public function down(): void
    {
        Schema::table('reservas', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('user_checkin_id');
            $table->dropConstrainedForeignId('user_checkout_id');
            $table->dropColumn([
                'fecha_hora_checkin',
                'fecha_hora_checkout',
                'observaciones_checkin',
                'observaciones_checkout',
            ]);
        });
    }
};
