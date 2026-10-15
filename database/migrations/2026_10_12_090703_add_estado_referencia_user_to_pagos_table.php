<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('pagos', function (Blueprint $table) {
            $table->string('referencia', 100)->nullable()->after('metodo_pago');
            $table->enum('estado', ['Completado', 'Pendiente', 'Anulado'])
                ->default('Completado')
                ->after('referencia');
            $table->foreignId('user_id')
                ->nullable()
                ->after('estado')
                ->constrained('users')
                ->cascadeOnUpdate()
                ->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pagos', function (Blueprint $table) {
            $table->dropConstrainedForeignId('user_id');
            $table->dropColumn(['referencia', 'estado']);
        });
    }
};
