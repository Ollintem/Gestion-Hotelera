<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Gasto extends Model
{
    protected $table = 'gastos';

    protected $fillable = [
        'concepto',
        'monto',
        'categoria',
        'fecha_gasto',
        'observaciones',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'monto' => 'decimal:2',
            'fecha_gasto' => 'date',
        ];
    }
}
