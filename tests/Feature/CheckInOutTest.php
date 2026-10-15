<?php

use App\Livewire\CheckInOut;
use App\Models\Cliente;
use App\Models\Habitacion;
use App\Models\Limpieza as TareaLimpieza;
use App\Models\Pago;
use App\Models\Reserva;
use App\Models\TipoHabitacion;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Livewire\Livewire;

beforeEach(function () {
    $this->seed(RolesAndPermissionsSeeder::class);

    $this->admin = User::factory()->create();
    $this->admin->assignRole('super-admin');

    $this->crearReserva = function (string $estadoReserva = 'Confirmada', string $estadoHabitacion = 'Ocupada'): array {
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
            'estado' => $estadoReserva,
            'monto_total' => 1798.00,
        ]);

        $reserva->habitaciones()->attach($habitacion->id, ['precio_por_noche' => 899.00]);

        return [$reserva, $habitacion];
    };

    // Deja la reservación sin saldo por cobrar para que el check-out esté habilitado.
    $this->liquidarSaldo = function (Reserva $reserva): void {
        Pago::create([
            'reserva_id' => $reserva->id,
            'monto' => 1798.00,
            'metodo_pago' => 'Efectivo',
            'estado' => Pago::ESTADO_COMPLETADO,
            'fecha_pago' => now(),
            'user_id' => $this->admin->id,
        ]);
    };
});

test('el check-in confirma la reserva, ocupa las habitaciones y registra la hora y el empleado', function () {
    [$reserva, $habitacion] = call_user_func($this->crearReserva, 'Pendiente', 'Disponible');

    Livewire::actingAs($this->admin)
        ->test(CheckInOut::class)
        ->call('abrirCheckIn', $reserva->id)
        ->assertSet('mostrarModal', true)
        ->call('confirmarCheckIn')
        ->assertSet('mensajeExito', 'Check-in registrado correctamente para la reserva #'.$reserva->id.'.');

    $reserva->refresh();

    expect($reserva->estado)->toBe('Confirmada')
        ->and($habitacion->refresh()->estado)->toBe('Ocupada')
        ->and($reserva->fecha_hora_checkin)->not->toBeNull()
        ->and($reserva->fecha_hora_checkout)->toBeNull()
        ->and($reserva->user_checkin_id)->toBe($this->admin->id);
});

test('un segundo check-in sobre la misma reserva no vuelve a registrarse', function () {
    [$reserva] = call_user_func($this->crearReserva, 'Pendiente', 'Disponible');

    $componente = Livewire::actingAs($this->admin)->test(CheckInOut::class);

    $componente->call('abrirCheckIn', $reserva->id)->call('confirmarCheckIn');
    $horaPrimerCheckIn = $reserva->refresh()->fecha_hora_checkin;

    $componente->call('abrirCheckIn', $reserva->id)
        ->assertSet('mostrarModal', false)
        ->assertSet('mensajeError', 'La reserva #'.$reserva->id.' ya tiene registrado su check-in. Actualiza la pantalla e inténtalo de nuevo.');

    expect($reserva->refresh()->fecha_hora_checkin)->toEqual($horaPrimerCheckIn);
});

test('no se puede hacer check-in a una reserva cancelada o finalizada', function () {
    [$reserva] = call_user_func($this->crearReserva, 'Cancelada', 'Disponible');

    Livewire::actingAs($this->admin)
        ->test(CheckInOut::class)
        ->call('abrirCheckIn', $reserva->id)
        ->assertSet('mostrarModal', false)
        ->assertSet('mensajeError', 'La reserva #'.$reserva->id.' ya tiene registrado su check-in. Actualiza la pantalla e inténtalo de nuevo.');

    expect($reserva->refresh()->estado)->toBe('Cancelada')
        ->and($reserva->fecha_hora_checkin)->toBeNull();
});

test('el check-out libera la habitación a limpieza y genera la tarea de limpieza pendiente', function () {
    [$reserva, $habitacion] = call_user_func($this->crearReserva);
    call_user_func($this->liquidarSaldo, $reserva);

    Livewire::actingAs($this->admin)
        ->test(CheckInOut::class)
        ->call('abrirCheckOut', $reserva->id)
        ->assertSet('mostrarModal', true)
        ->call('confirmarCheckOut')
        ->assertSet('mensajeExito', 'Check-out registrado correctamente para la reserva #'.$reserva->id.'. Las habitaciones pasaron a limpieza.');

    expect($habitacion->refresh()->estado)->toBe('Limpieza')
        ->and($reserva->refresh()->estado)->toBe(Reserva::ESTADO_FINALIZADA);

    $tarea = TareaLimpieza::first();

    expect($tarea)->not->toBeNull()
        ->and($tarea->habitacion_id)->toBe($habitacion->id)
        ->and($tarea->estado)->toBe('Pendiente')
        ->and($tarea->user_id)->toBe($this->admin->id)
        ->and($tarea->notas)->toContain('Check-out de la reserva #'.$reserva->id);
});

test('el check-out registra la hora y el empleado que lo realizó', function () {
    [$reserva] = call_user_func($this->crearReserva);
    call_user_func($this->liquidarSaldo, $reserva);

    Livewire::actingAs($this->admin)
        ->test(CheckInOut::class)
        ->call('abrirCheckOut', $reserva->id)
        ->call('confirmarCheckOut');

    $reserva->refresh();

    expect($reserva->fecha_hora_checkout)->not->toBeNull()
        ->and($reserva->user_checkout_id)->toBe($this->admin->id);
});

test('si falla la creación de la tarea, el cambio de estado de la habitación se revierte', function () {
    [$reserva, $habitacion] = call_user_func($this->crearReserva);
    call_user_func($this->liquidarSaldo, $reserva);

    TareaLimpieza::creating(function () {
        throw new RuntimeException('Fallo simulado al crear la tarea de limpieza');
    });

    $componente = Livewire::actingAs($this->admin)->test(CheckInOut::class);

    expect(fn () => $componente->call('abrirCheckOut', $reserva->id)->call('confirmarCheckOut'))
        ->toThrow(RuntimeException::class);

    expect($habitacion->refresh()->estado)->toBe('Ocupada')
        ->and($reserva->refresh()->estado)->toBe('Confirmada')
        ->and(TareaLimpieza::count())->toBe(0);

    TareaLimpieza::flushEventListeners();
});

test('un nuevo check-out no duplica una tarea de limpieza pendiente de la misma habitación', function () {
    [$reserva, $habitacion] = call_user_func($this->crearReserva);
    call_user_func($this->liquidarSaldo, $reserva);

    $componente = Livewire::actingAs($this->admin)->test(CheckInOut::class);

    $componente->call('abrirCheckOut', $reserva->id)->call('confirmarCheckOut');
    $componente->call('abrirCheckOut', $reserva->id)->call('confirmarCheckOut');

    expect(TareaLimpieza::where('habitacion_id', $habitacion->id)->count())->toBe(1);
});

test('no se puede hacer check-out mientras quede saldo pendiente', function () {
    [$reserva, $habitacion] = call_user_func($this->crearReserva);

    Livewire::actingAs($this->admin)
        ->test(CheckInOut::class)
        ->call('abrirCheckOut', $reserva->id)
        ->call('confirmarCheckOut')
        ->assertSet('mostrarModal', true)
        ->assertSet('mensajeError', 'No se puede registrar el check-out: la reserva #'.$reserva->id
            .' tiene un saldo pendiente de $1,798.00. Registra el pago en el módulo de Pagos antes de cerrar la estadía.');

    expect($reserva->refresh()->estado)->toBe('Confirmada')
        ->and($habitacion->refresh()->estado)->toBe('Ocupada')
        ->and($reserva->fecha_hora_checkout)->toBeNull()
        ->and(TareaLimpieza::count())->toBe(0);
});

test('un usuario sin permiso de creación no puede registrar el check-in', function () {
    [$reserva] = call_user_func($this->crearReserva, 'Pendiente', 'Disponible');

    // Usuario autenticado sin roles: no tiene checkin_checkout.crear.
    $sinPermiso = User::factory()->create();

    Livewire::actingAs($sinPermiso)
        ->test(CheckInOut::class)
        ->call('abrirCheckIn', $reserva->id)
        ->call('confirmarCheckIn')
        ->assertSet('mensajeError', 'No tienes permiso para registrar el check-in.');

    expect($reserva->refresh()->estado)->toBe('Pendiente')
        ->and($reserva->fecha_hora_checkin)->toBeNull();
});

test('solo se pueden cancelar reservaciones pendientes', function () {
    [$reservaConfirmada] = call_user_func($this->crearReserva, 'Confirmada');

    Livewire::actingAs($this->admin)
        ->test(CheckInOut::class)
        ->call('cancelar', $reservaConfirmada->id)
        ->assertSet('mensajeError', 'Solo se pueden cancelar reservaciones pendientes. La reserva #'.$reservaConfirmada->id
            .' ya tiene al huésped en el hotel.');

    expect($reservaConfirmada->refresh()->estado)->toBe('Confirmada');
});

test('cancelar una reserva pendiente la marca como cancelada y libera su habitación', function () {
    [$reserva, $habitacion] = call_user_func($this->crearReserva, 'Pendiente', 'Disponible');

    Livewire::actingAs($this->admin)
        ->test(CheckInOut::class)
        ->call('cancelar', $reserva->id)
        ->assertSet('mensajeExito', 'Reservación #'.$reserva->id.' cancelada correctamente.');

    expect($reserva->refresh()->estado)->toBe('Cancelada')
        ->and($habitacion->refresh()->estado)->toBe('Disponible');
});

test('el historial lista las llegadas y salidas ya registradas', function () {
    [$reserva] = call_user_func($this->crearReserva, 'Pendiente', 'Disponible');
    call_user_func($this->liquidarSaldo, $reserva);

    $componente = Livewire::actingAs($this->admin)->test(CheckInOut::class);

    // Sin movimientos registrados el historial está vacío.
    $componente->call('verHistorial', 'checkins')->assertSee('Aún no se han registrado check-ins.');

    $componente->call('abrirCheckIn', $reserva->id)->call('confirmarCheckIn');
    $componente->call('verHistorial', 'checkins')->assertSee('#'.$reserva->id);

    $componente->call('abrirCheckOut', $reserva->id)->call('confirmarCheckOut');
    $componente->call('verHistorial', 'salidas')->assertSee('#'.$reserva->id);
});

test('los filtros de búsqueda restringen las llegadas pendientes por folio', function () {
    [$reserva] = call_user_func($this->crearReserva, 'Pendiente', 'Disponible');

    Livewire::actingAs($this->admin)
        ->test(CheckInOut::class)
        ->set('filtroBusqueda', (string) $reserva->id)
        ->assertSee('#'.$reserva->id)
        ->set('filtroBusqueda', '999999')
        ->assertDontSee('#'.$reserva->id);
});
