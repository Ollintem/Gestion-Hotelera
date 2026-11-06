<?php

use App\Models\Cliente;
use App\Models\Habitacion;
use App\Models\Reserva;
use App\Models\TipoHabitacion;
use App\Models\User;
use App\Services\Habitaciones\DisponibilidadHabitaciones;
use App\Services\Reservaciones\CrearReserva;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

function tipoDisponibilidad(string $nombre, float $precio, int $capacidad): TipoHabitacion
{
    return TipoHabitacion::create([
        'nombre' => $nombre,
        'precio_base' => $precio,
        'capacidad' => $capacidad,
    ]);
}

function habitacionDisponibilidad(string $numero, TipoHabitacion $tipo, string $estado = 'Disponible'): Habitacion
{
    return Habitacion::create([
        'numero_habitacion' => $numero,
        'tipo_habitacion_id' => $tipo->id,
        'estado' => $estado,
        'piso' => 1,
    ]);
}

test('una habitacion ocupada hoy se puede vender para fechas futuras', function () {
    $tipo = tipoDisponibilidad('Estandar', 900, 2);
    $habitacion = habitacionDisponibilidad('101', $tipo, 'Ocupada');

    $disponibilidad = app(DisponibilidadHabitaciones::class);

    expect($disponibilidad->estaDisponible($habitacion->id, now(), now()->addDay()))->toBeFalse()
        ->and($disponibilidad->estaDisponible($habitacion->id, now()->addDays(3), now()->addDays(5)))->toBeTrue();
});

test('el mantenimiento bloquea la venta en cualquier fecha', function () {
    $tipo = tipoDisponibilidad('Estandar', 900, 2);
    $habitacion = habitacionDisponibilidad('102', $tipo, 'Mantenimiento');

    expect(app(DisponibilidadHabitaciones::class)->estaDisponible(
        $habitacion->id,
        now()->addDays(10),
        now()->addDays(12),
    ))->toBeFalse();
});

test('una reservacion cancelada no bloquea fechas futuras', function () {
    $tipo = tipoDisponibilidad('Estandar', 900, 2);
    $habitacion = habitacionDisponibilidad('103', $tipo);
    $cliente = Cliente::factory()->create();
    $usuario = User::factory()->create();

    $reserva = Reserva::create([
        'cliente_id' => $cliente->id,
        'user_id' => $usuario->id,
        'check_in' => now()->addDays(5)->toDateString(),
        'check_out' => now()->addDays(8)->toDateString(),
        'estado' => 'Cancelada',
        'monto_total' => 2700,
    ]);

    $reserva->habitaciones()->attach($habitacion->id, ['precio_por_noche' => 900]);

    expect(app(DisponibilidadHabitaciones::class)->estaDisponible(
        $habitacion->id,
        now()->addDays(6),
        now()->addDays(7),
    ))->toBeTrue();
});

test('crear una reservacion pendiente no ocupa la habitacion y evita cruces', function () {
    $tipo = tipoDisponibilidad('Doble', 500, 2);
    $habitacion = habitacionDisponibilidad('201', $tipo);
    $cliente = Cliente::factory()->create();
    $usuario = User::factory()->create();

    $reserva = app(CrearReserva::class)->ejecutar(
        $cliente->id,
        $habitacion->id,
        now()->addDays(10),
        now()->addDays(12),
        2,
        $usuario->id,
    );

    expect($reserva->estado)->toBe('Pendiente')
        ->and($habitacion->fresh()->estado)->toBe('Disponible')
        ->and($reserva->habitacionesAsignadas()->count())->toBe(1);

    expect(fn () => app(CrearReserva::class)->ejecutar(
        $cliente->id,
        $habitacion->id,
        now()->addDays(11),
        now()->addDays(13),
        2,
        $usuario->id,
    ))->toThrow(ValidationException::class);
});

test('la capacidad se valida en el servicio de reservaciones', function () {
    $tipo = tipoDisponibilidad('Individual', 300, 1);
    $habitacion = habitacionDisponibilidad('301', $tipo);
    $cliente = Cliente::factory()->create();
    $usuario = User::factory()->create();

    try {
        app(CrearReserva::class)->ejecutar(
            $cliente->id,
            $habitacion->id,
            now()->addDays(5),
            now()->addDays(6),
            2,
            $usuario->id,
        );

        $this->fail('Se esperaba un error de capacidad.');
    } catch (ValidationException $excepcion) {
        expect($excepcion->errors())->toHaveKey('num_huespedes');
    }

    expect(Reserva::count())->toBe(0);
});

test('la busqueda publica filtra por capacidad y excluye mantenimiento', function () {
    $individual = tipoDisponibilidad('Individual', 300, 1);
    $familiar = tipoDisponibilidad('Familiar', 800, 4);

    habitacionDisponibilidad('401', $individual);
    habitacionDisponibilidad('402', $familiar);
    habitacionDisponibilidad('403', $familiar, 'Mantenimiento');

    $this->get(route('rooms.search', [
        'check_in' => now()->addDays(7)->toDateString(),
        'check_out' => now()->addDays(9)->toDateString(),
        'guests' => 3,
    ]))
        ->assertOk()
        ->assertSee('#402')
        ->assertDontSee('#401')
        ->assertDontSee('#403');
});

test('la reservacion publica nace pendiente y respeta la capacidad', function () {
    $this->seed(RolesAndPermissionsSeeder::class);

    $tipo = tipoDisponibilidad('Familiar', 800, 4);
    $habitacion = habitacionDisponibilidad('501', $tipo);
    $usuario = User::factory()->create(['email' => 'huesped@example.com']);
    $usuario->assignRole('cliente');

    $this->actingAs($usuario)
        ->post(route('reserva.store'), [
            'habitacion_id' => $habitacion->id,
            'check_in' => now()->addDays(7)->toDateString(),
            'check_out' => now()->addDays(9)->toDateString(),
            'guests' => 4,
        ])
        ->assertRedirect(route('home'));

    $reserva = Reserva::firstOrFail();

    expect($reserva->estado)->toBe('Pendiente')
        ->and($reserva->user_id)->toBe($usuario->id)
        ->and($habitacion->fresh()->estado)->toBe('Disponible');
});

test('las transiciones de estado respetan el ciclo de vida', function () {
    $tipo = tipoDisponibilidad('Estandar', 900, 2);
    $habitacion = habitacionDisponibilidad('601', $tipo);
    $cliente = Cliente::factory()->create();
    $usuario = User::factory()->create();

    $reserva = app(CrearReserva::class)->ejecutar(
        $cliente->id,
        $habitacion->id,
        now()->addDays(2),
        now()->addDays(4),
        2,
        $usuario->id,
    );

    expect($reserva->cancelar())->toBeTrue()
        ->and($reserva->refresh()->estado)->toBe('Cancelada');

    $this->actingAs($usuario);

    $reserva->update(['estado' => 'Pendiente']);
    $reserva->realizarCheckIn();

    expect($reserva->refresh()->estado)->toBe('Confirmada')
        ->and($habitacion->fresh()->estado)->toBe('Ocupada')
        ->and($reserva->cancelar())->toBeFalse();

    $reserva->realizarCheckOut();

    expect($reserva->refresh()->estado)->toBe('Finalizada')
        ->and($habitacion->fresh()->estado)->toBe('Limpieza');
});

test('la consulta de disponibilidad no genera consultas por cada habitacion', function () {
    $tipo = tipoDisponibilidad('Estandar', 900, 2);

    foreach (range(1, 8) as $numero) {
        habitacionDisponibilidad((string) (700 + $numero), $tipo);
    }

    $consultas = 0;

    DB::listen(function () use (&$consultas): void {
        $consultas++;
    });

    app(DisponibilidadHabitaciones::class)
        ->consulta(now()->addDays(3), now()->addDays(5), 2)
        ->get();

    expect($consultas)->toBeLessThanOrEqual(2);
});
