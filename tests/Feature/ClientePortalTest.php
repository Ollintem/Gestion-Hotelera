<?php

use App\Models\Cliente;
use App\Models\Habitacion;
use App\Models\Pago;
use App\Models\Reserva;
use App\Models\ReservaHabitacion;
use App\Models\ReservaServicio;
use App\Models\Servicio;
use App\Models\TipoHabitacion;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;

beforeEach(function () {
    $this->seed(RolesAndPermissionsSeeder::class);
});

function crearClienteConCuenta(string $email, string $nombre = 'Cliente', string $apellido = 'Prueba'): array
{
    $usuario = User::factory()->create(['email' => $email]);
    $usuario->assignRole('cliente');

    $cliente = Cliente::create([
        'user_id' => $usuario->id,
        'nombre' => $nombre,
        'apellido' => $apellido,
        'email' => $email,
    ]);

    return [$usuario, $cliente];
}

function crearReservaDeCliente(Cliente $cliente, User $usuario, string $numeroHabitacion): Reserva
{
    $tipo = TipoHabitacion::create(['nombre' => 'Tipo '.$numeroHabitacion, 'precio_base' => 1000, 'capacidad' => 3]);
    $habitacion = Habitacion::create([
        'numero_habitacion' => $numeroHabitacion,
        'tipo_habitacion_id' => $tipo->id,
        'estado' => 'Disponible',
        'piso' => 1,
    ]);

    $reserva = Reserva::create([
        'cliente_id' => $cliente->id,
        'user_id' => $usuario->id,
        'check_in' => now()->addDays(3)->toDateString(),
        'check_out' => now()->addDays(5)->toDateString(),
        'estado' => 'Confirmada',
        'monto_total' => 2000,
        'num_huespedes' => 2,
    ]);

    ReservaHabitacion::create([
        'reserva_id' => $reserva->id,
        'habitacion_id' => $habitacion->id,
        'precio_por_noche' => 1000,
    ]);

    return $reserva;
}

test('el cliente solo ve sus propias reservaciones, pagos y servicios', function () {
    [$usuarioA, $clienteA] = crearClienteConCuenta('a@example.com', 'Ana', 'Uno');
    [$usuarioB, $clienteB] = crearClienteConCuenta('b@example.com', 'Beto', 'Dos');

    $reservaA = crearReservaDeCliente($clienteA, $usuarioA, '401');
    $reservaB = crearReservaDeCliente($clienteB, $usuarioB, '999');

    $servicio = Servicio::factory()->create(['nombre' => 'Desayuno exclusivo', 'precio' => 250]);
    $servicioAjeno = Servicio::factory()->create(['nombre' => 'Servicio ajeno', 'precio' => 999]);

    ReservaServicio::create([
        'reserva_id' => $reservaA->id,
        'servicio_id' => $servicio->id,
        'cantidad' => 1,
        'precio_aplicado' => 250,
        'subtotal' => 250,
    ]);

    ReservaServicio::create([
        'reserva_id' => $reservaB->id,
        'servicio_id' => $servicioAjeno->id,
        'cantidad' => 1,
        'precio_aplicado' => 999,
        'subtotal' => 999,
    ]);

    Pago::create([
        'reserva_id' => $reservaA->id,
        'monto' => 500,
        'metodo_pago' => 'Efectivo',
        'estado' => Pago::ESTADO_COMPLETADO,
        'fecha_pago' => now(),
        'notas' => 'Abono propio',
    ]);

    Pago::create([
        'reserva_id' => $reservaB->id,
        'monto' => 700,
        'metodo_pago' => 'Tarjeta',
        'estado' => Pago::ESTADO_COMPLETADO,
        'fecha_pago' => now(),
        'notas' => 'Abono ajeno',
    ]);

    $this->actingAs($usuarioA)
        ->get(route('mis-reservaciones'))
        ->assertOk()
        ->assertSee('401')
        ->assertSee('Desayuno exclusivo')
        ->assertSee('Abono propio')
        ->assertDontSee('999')
        ->assertDontSee('Servicio ajeno')
        ->assertDontSee('Abono ajeno');
});

test('el cliente puede actualizar su perfil y se sincronizan ambas tablas', function () {
    [$usuario, $cliente] = crearClienteConCuenta('perfil@example.com', 'Perfil', 'Antiguo');

    $this->actingAs($usuario)
        ->patch(route('cliente.perfil.update'), [
            'nombre' => 'Nombre',
            'apellido' => 'Nuevo',
            'email' => 'nuevo@example.com',
            'telefono' => '5512345678',
        ])
        ->assertRedirect(route('mis-reservaciones'))
        ->assertSessionHas('success');

    expect($usuario->fresh()->name)->toBe('Nombre Nuevo')
        ->and($usuario->fresh()->email)->toBe('nuevo@example.com')
        ->and($cliente->fresh()->nombre)->toBe('Nombre')
        ->and($cliente->fresh()->apellido)->toBe('Nuevo')
        ->and($cliente->fresh()->telefono)->toBe('5512345678');
});

test('el cliente no puede usar el correo de otra cuenta al editar su perfil', function () {
    [$usuario] = crearClienteConCuenta('mio@example.com');
    crearClienteConCuenta('ajeno@example.com');

    $this->actingAs($usuario)
        ->patch(route('cliente.perfil.update'), [
            'nombre' => 'Cliente',
            'apellido' => 'Prueba',
            'email' => 'ajeno@example.com',
            'telefono' => null,
        ])
        ->assertSessionHasErrors('email');

    expect($usuario->fresh()->email)->toBe('mio@example.com');
});

test('solo los clientes pueden acceder al panel y actualizar su perfil', function () {
    $staff = User::factory()->create();
    $staff->assignRole('recepcionista');

    $this->actingAs($staff)
        ->get(route('mis-reservaciones'))
        ->assertForbidden();

    $this->actingAs($staff)
        ->patch(route('cliente.perfil.update'), [
            'nombre' => 'No',
            'apellido' => 'Cliente',
            'email' => 'staff@example.com',
        ])
        ->assertForbidden();
});

test('un empleado no puede iniciar sesión desde la sección de clientes', function () {
    $staff = User::factory()->create();
    $staff->assignRole('gerente');

    $this->post(route('login'), [
        'email' => $staff->email,
        'password' => 'password',
        'role' => 'clientes',
    ])->assertSessionHasErrors('email');

    $this->assertGuest();
});

test('el registro reutiliza la ficha de cliente creada por recepción', function () {
    $existente = Cliente::create([
        'nombre' => 'Huésped',
        'apellido' => 'Existente',
        'email' => 'existente@example.com',
        'telefono' => '5511111111',
    ]);

    $this->post(route('register'), [
        'name' => 'Huésped Existente',
        'email' => 'existente@example.com',
        'telefono' => '5522222222',
        'password' => 'secret123',
        'password_confirmation' => 'secret123',
    ])->assertRedirect(route('mis-reservaciones'));

    $usuario = User::where('email', 'existente@example.com')->firstOrFail();

    expect(Cliente::where('email', 'existente@example.com')->count())->toBe(1)
        ->and($existente->fresh()->user_id)->toBe($usuario->id)
        ->and($existente->fresh()->telefono)->toBe('5522222222');
});

test('el periodo de visualización de notificaciones temporales es de 3000 ms', function () {
    $js = file_get_contents(resource_path('js/notificaciones.js'));
    $flash = file_get_contents(resource_path('views/components/flash-messages.blade.php'));
    $aviso = file_get_contents(resource_path('views/components/aviso-temporal.blade.php'));

    expect($js)->toContain('const DURACION_MS = 3000')
        ->and($flash)->toContain('data-notificacion-duracion="3000"')
        ->and($aviso)->toContain("'duracion' => 3000");
});
