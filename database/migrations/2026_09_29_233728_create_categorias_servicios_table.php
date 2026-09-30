<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('categorias', function (Blueprint $table) {
            $table->id();
            $table->string('nombre', 60)->unique();
            $table->string('descripcion', 160)->nullable();
            $table->boolean('activo')->default(true);
            $table->timestamps();
        });

        Schema::table('servicios', function (Blueprint $table) {
            $table->foreignId('categoria_id')
                ->nullable()
                ->after('descripcion')
                ->constrained('categorias')
                ->cascadeOnUpdate()
                ->nullOnDelete();
        });

        $this->sembrarCategoriasIniciales();
        $this->asignarCategoriasIniciales();
    }

    public function down(): void
    {
        Schema::table('servicios', function (Blueprint $table) {
            $table->dropForeign(['categoria_id']);
            $table->dropColumn('categoria_id');
        });

        Schema::dropIfExists('categorias');
    }

    /**
     * El catálogo base del hotel. Vive en la migración para que el backfill
     * funcione aunque el proyecto todavía no se haya sembrado.
     */
    private function sembrarCategoriasIniciales(): void
    {
        $categorias = [
            'Alimentación' => 'Comidas, bebidas y desayuno.',
            'Habitación' => 'Atención dentro de la habitación.',
            'Lavandería' => 'Ropa de cama, toallas y lavandería express.',
            'Bienestar' => 'Spa, masajes y tratamientos.',
            'Transporte' => 'Traslados desde y hacia el aeropuerto.',
        ];

        $ahora = now();

        foreach ($categorias as $nombre => $descripcion) {
            DB::table('categorias')->insertOrIgnore([
                'nombre' => $nombre,
                'descripcion' => $descripcion,
                'activo' => true,
                'created_at' => $ahora,
                'updated_at' => $ahora,
            ]);
        }
    }

    /**
     * Backfill: reparte el catálogo de servicios ya existente entre las
     * categorías del hotel para que el panel arranque con datos coherentes en
     * lugar de una columna entera en nulos.
     */
    private function asignarCategoriasIniciales(): void
    {
        $categorias = [
            'Servicio a la habitación' => 'Habitación',
            'Desayuno buffet' => 'Alimentación',
            'Lavandería' => 'Lavandería',
            'Spa y masajes' => 'Bienestar',
            'Traslado al aeropuerto' => 'Transporte',
        ];

        foreach ($categorias as $servicio => $categoria) {
            $categoriaId = DB::table('categorias')->where('nombre', $categoria)->value('id');

            if ($categoriaId === null) {
                continue;
            }

            DB::table('servicios')
                ->where('nombre', $servicio)
                ->whereNull('categoria_id')
                ->update(['categoria_id' => $categoriaId]);
        }
    }
};
