<?php

use App\Livewire\CheckInOut;
use App\Models\Cliente;
use App\Models\Habitacion;
use App\Models\Limpieza as TareaLimpieza;
use App\Models\Reserva;
use App\Models\TipoHabitacion;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Livewire\Livewire;

beforeEach(function () {
    $this->seed(RolesAndPermissionsSeeder::class);

    $this->admin = User::factory()->create();
    $this->admin->assignRole('super-admin');

    $this->crearReserva = function (string $estadoHabitacion = 'Ocupada'): array {
        $tipo = TipoHabitacion::create(['nombre' => 'Estándar', 'precio_base' => 899.00, 'capacidad' => 2]);

        $habitacion = Habitacion::create([
            'numero_habitacion' => '102',
            'tipo_habitacion_id' => $tipo->id,
            'estado' => $estadoHabitacion,
            'piso' => 1,
        ]);

        $cliente = Cliente::create([
            'user_id' => $this->admin->id,
            'nombre' => 'María',
            'apellido' => 'González',
            'email' => 'maria.gonzalez@example.com',
        ]);

        $reserva = Reserva::create([
            'cliente_id' => $cliente->id,
            'user_id' => $this->admin->id,
            'check_in' => now()->toDateString(),
            'check_out' => now()->addDays(2)->toDateString(),
            'estado' => 'Confirmada',
            'monto_total' => 1798.00,
        ]);

        $reserva->habitaciones()->attach($habitacion->id, ['precio_por_noche' => 899.00]);

        return [$reserva, $habitacion];
    };
});

test('el check-out libera la habitación a limpieza y genera la tarea de limpieza pendiente', function () {
    [$reserva, $habitacion] = call_user_func($this->crearReserva);

    Livewire::actingAs($this->admin)
        ->test(CheckInOut::class)
        ->call('checkOut', $reserva->id)
        ->assertSet('mensajeExito', 'Check-out registrado correctamente. Las habitaciones pasaron a limpieza.');

    expect($habitacion->refresh()->estado)->toBe('Limpieza')
        ->and($reserva->refresh()->estado)->toBe(Reserva::ESTADO_FINALIZADA);

    $tarea = TareaLimpieza::first();

    expect($tarea)->not->toBeNull()
        ->and($tarea->habitacion_id)->toBe($habitacion->id)
        ->and($tarea->estado)->toBe('Pendiente')
        ->and($tarea->user_id)->toBe($this->admin->id)
        ->and($tarea->notas)->toContain('Check-out de la reserva #'.$reserva->id);
});

test('si falla la creación de la tarea, el cambio de estado de la habitación se revierte', function () {
    [$reserva, $habitacion] = call_user_func($this->crearReserva);

    TareaLimpieza::creating(function () {
        throw new RuntimeException('Fallo simulado al crear la tarea de limpieza');
    });

    $componente = Livewire::actingAs($this->admin)->test(CheckInOut::class);

    expect(fn () => $componente->call('checkOut', $reserva->id))
        ->toThrow(RuntimeException::class);

    expect($habitacion->refresh()->estado)->toBe('Ocupada')
        ->and($reserva->refresh()->estado)->toBe('Confirmada')
        ->and(TareaLimpieza::count())->toBe(0);

    TareaLimpieza::flushEventListeners();
});

test('un nuevo check-out no duplica una tarea de limpieza pendiente de la misma habitación', function () {
    [$reserva, $habitacion] = call_user_func($this->crearReserva);

    $componente = Livewire::actingAs($this->admin)->test(CheckInOut::class);

    $componente->call('checkOut', $reserva->id);
    $componente->call('checkOut', $reserva->id);

    expect(TareaLimpieza::where('habitacion_id', $habitacion->id)->count())->toBe(1);
});
