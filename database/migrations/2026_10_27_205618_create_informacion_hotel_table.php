<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Datos generales del establecimiento que consulta y edita el módulo de
 * Configuración.
 *
 * La tabla guarda un único registro (el modelo lo obtiene con
 * `InformacionHotel::current()`); por eso no hay claves ni pares clave-valor.
 * Cada columna es anulable salvo el nombre, para que el registro pueda nacer
 * vacío e irse completando.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('informacion_hotel', function (Blueprint $table): void {
            $table->id();
            $table->string('nombre', 150);
            $table->string('rfc', 20)->nullable();
            $table->string('direccion', 255)->nullable();
            $table->string('ciudad', 100)->nullable();
            $table->string('telefono', 50)->nullable();
            $table->string('email', 150)->nullable();
            $table->string('sitio_web', 150)->nullable();
            $table->unsignedInteger('numero_habitaciones')->nullable();
            $table->text('descripcion')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('informacion_hotel');
    }
};
