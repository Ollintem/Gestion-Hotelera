<?php

use App\Livewire\Reportes;
use App\Models\Cliente;
use App\Models\Gasto;
use App\Models\Habitacion;
use App\Models\InformacionHotel;
use App\Models\Pago;
use App\Models\Reserva;
use App\Models\ReservaServicio;
use App\Models\Servicio;
use App\Models\TipoHabitacion;
use App\Models\User;
use Carbon\Carbon;
use Database\Seeders\RolesAndPermissionsSeeder;
use Livewire\Livewire;

beforeEach(function () {
    $this->seed(RolesAndPermissionsSeeder::class);

    $this->admin = User::factory()->create();
    $this->admin->assignRole('super-admin');

    InformacionHotel::query()->create([
        'id' => InformacionHotel::REGISTRO_UNICO,
        'nombre' => 'Hotel NovaStay Centro',
    ]);

    $this->tipo = TipoHabitacion::create([
        'nombre' => 'Estándar',
        'precio_base' => 500,
        'capacidad' => 2,
        'descripcion' => 'Habitación estándar.',
    ]);

    $this->habitacionA = Habitacion::create([
        'numero_habitacion' => '101',
        'tipo_habitacion_id' => $this->tipo->id,
        'estado' => 'Disponible',
        'piso' => 1,
    ]);

    $this->habitacionB = Habitacion::create([
        'numero_habitacion' => '102',
        'tipo_habitacion_id' => $this->tipo->id,
        'estado' => 'Disponible',
        'piso' => 1,
    ]);

    $this->cliente = Cliente::factory()->create();

    $this->reserva = Reserva::factory()->for($this->cliente)->create([
        'user_id' => $this->admin->id,
        'check_in' => '2026-03-10',
        'check_out' => '2026-03-12',
        'fecha_hora_checkin' => '2026-03-10 14:00:00',
        'fecha_hora_checkout' => '2026-03-12 11:00:00',
        'estado' => 'Finalizada',
        'monto_total' => 1000,
    ]);

    $this->reserva->habitaciones()->attach($this->habitacionA->id, ['precio_por_noche' => 500]);

    $this->reserva->forceFill([
        'created_at' => '2026-03-10 09:00:00',
        'updated_at' => '2026-03-10 09:00:00',
    ])->saveQuietly();

    $servicio = Servicio::create([
        'nombre' => 'Desayuno buffet',
        'precio' => 100,
        'activo' => true,
    ]);

    $cargo = ReservaServicio::create([
        'reserva_id' => $this->reserva->id,
        'servicio_id' => $servicio->id,
        'cantidad' => 2,
        'precio_aplicado' => 100,
        'subtotal' => 200,
    ]);

    $cargo->forceFill([
        'created_at' => '2026-03-10 12:00:00',
        'updated_at' => '2026-03-10 12:00:00',
    ])->saveQuietly();

    Pago::create([
        'reserva_id' => $this->reserva->id,
        'monto' => 700,
        'metodo_pago' => 'Efectivo',
        'estado' => Pago::ESTADO_COMPLETADO,
        'fecha_pago' => '2026-03-10 13:00:00',
        'user_id' => $this->admin->id,
    ]);

    Pago::create([
        'reserva_id' => $this->reserva->id,
        'monto' => 300,
        'metodo_pago' => 'Tarjeta',
        'estado' => Pago::ESTADO_PENDIENTE,
        'fecha_pago' => '2026-03-10 13:30:00',
        'user_id' => $this->admin->id,
    ]);

    Gasto::create([
        'concepto' => 'Insumos de limpieza',
        'monto' => 150,
        'categoria' => 'Operación',
        'fecha_gasto' => '2026-03-10',
    ]);
});

test('el reporte diario calcula los indicadores del día seleccionado', function () {
    Livewire::actingAs($this->admin)
        ->test(Reportes::class)
        ->set('periodo', 'diario')
        ->set('fecha', '2026-03-10')
        ->assertSee('10 de marzo de 2026')
        ->assertViewHas('indicadores', function (array $indicadores): bool {
            return $indicadores['totalReservaciones'] === 1
                && $indicadores['checkins'] === 1
                && $indicadores['checkouts'] === 0
                && $indicadores['ingresos'] === 700.0
                && $indicadores['gastos'] === 150.0
                && $indicadores['resultadoNeto'] === 550.0
                && $indicadores['serviciosCantidad'] === 2
                && $indicadores['serviciosTotal'] === 200.0
                && $indicadores['cargosReservaciones'] === 1000.0
                && $indicadores['saldoPendiente'] === 500.0
                && $indicadores['habitacionesOcupadas'] === 1
                && $indicadores['habitacionesDisponibles'] === 1
                && $indicadores['ocupacion'] === 50.0;
        });
});

test('el reporte semanal empieza en lunes y termina en domingo con desglose diario', function () {
    $semana = Carbon::parse('2026-03-10')->format('o-\WW');

    $componente = Livewire::actingAs($this->admin)
        ->test(Reportes::class)
        ->set('periodo', 'semanal')
        ->set('semana', $semana)
        ->assertSee('09/03/2026 al 15/03/2026');

    $inicio = $componente->viewData('inicio');
    $fin = $componente->viewData('fin');
    $desglose = $componente->viewData('desglose');
    $indicadores = $componente->viewData('indicadores');

    expect($inicio->toDateString())->toBe('2026-03-09')
        ->and($fin->toDateString())->toBe('2026-03-15')
        ->and($desglose)->toHaveCount(7)
        ->and($indicadores['totalReservaciones'])->toBe(1)
        ->and($indicadores['checkins'])->toBe(1)
        ->and($indicadores['checkouts'])->toBe(1)
        ->and($indicadores['habitacionesOcupadas'])->toBe(1 * 2)
        ->and($indicadores['ingresos'])->toBe(700.0)
        ->and($indicadores['gastos'])->toBe(150.0)
        ->and($indicadores['serviciosCantidad'])->toBe(2)
        ->and($indicadores['saldoPendiente'])->toBe(500.0)
        ->and($desglose[1]['reservaciones'])->toBe(1)
        ->and($desglose[1]['ingresos'])->toBe(700.0)
        ->and($desglose[1]['gastos'])->toBe(150.0)
        ->and($desglose[1]['servicios'])->toBe(2)
        ->and($desglose[1]['ocupadas'])->toBe(1);
});

test('el reporte mensual resume el mes completo y su desglose diario', function () {
    $componente = Livewire::actingAs($this->admin)
        ->test(Reportes::class)
        ->set('periodo', 'mensual')
        ->set('mes', 3)
        ->set('anio', 2026)
        ->assertSee('01/03/2026 al 31/03/2026');

    $desglose = $componente->viewData('desglose');
    $indicadores = $componente->viewData('indicadores');

    expect($desglose)->toHaveCount(31)
        ->and($indicadores['totalReservaciones'])->toBe(1)
        ->and($indicadores['ingresos'])->toBe(700.0)
        ->and($indicadores['gastos'])->toBe(150.0)
        ->and($indicadores['resultadoNeto'])->toBe(550.0)
        ->and($indicadores['serviciosCantidad'])->toBe(2)
        ->and($indicadores['saldoPendiente'])->toBe(500.0)
        ->and($indicadores['habitacionesOcupadas'])->toBe(2);
});

test('el PDF del reporte incluye el hotel y coincide con el periodo seleccionado', function () {
    $this->actingAs($this->admin)
        ->get(route('reportes.pdf', [
            'periodo' => 'diario',
            'fecha' => '2026-03-10',
        ]))
        ->assertOk()
        ->assertDownload('reporte-diario-2026-03-10.pdf');
});
