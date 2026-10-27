<?php

use App\Livewire\Reservaciones\Index;
use App\Models\Cliente;
use App\Models\Habitacion;
use App\Models\Pago;
use App\Models\Reserva;
use App\Models\TipoHabitacion;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Support\Facades\DB;
use Livewire\Livewire;

beforeEach(function () {
    $this->seed(RolesAndPermissionsSeeder::class);

    $this->admin = User::factory()->create();
    $this->admin->assignRole('super-admin');
});

function tipoReserva(string $nombre, float $precio, int $capacidad): TipoHabitacion
{
    return TipoHabitacion::create([
        'nombre' => $nombre,
        'precio_base' => $precio,
        'capacidad' => $capacidad,
    ]);
}

function habitacionReserva(string $numero, TipoHabitacion $tipo, string $estado = 'Disponible'): Habitacion
{
    return Habitacion::create([
        'numero_habitacion' => $numero,
        'tipo_habitacion_id' => $tipo->id,
        'estado' => $estado,
        'piso' => 1,
    ]);
}

test('el índice muestra la tabla con todas las columnas de la reservación', function () {
    $tipo = tipoReserva('Suite', 1500.00, 3);
    $habitacion = habitacionReserva('101', $tipo);

    $reserva = Reserva::factory()->create([
        'estado' => 'Confirmada',
        'monto_total' => 4500.00,
    ]);

    $reserva->habitaciones()->attach($habitacion->id, ['precio_por_noche' => 1500]);

    Pago::create([
        'reserva_id' => $reserva->id,
        'monto' => 4500.00,
        'metodo_pago' => 'Tarjeta',
        'fecha_pago' => now(),
    ]);

    Livewire::actingAs($this->admin)
        ->test(Index::class)
        ->assertSee('Reservaciones')
        ->assertSee('Código')
        ->assertSee('Cliente')
        ->assertSee('Habitación')
        ->assertSee('Check-in')
        ->assertSee('Check-out')
        ->assertSee('Huéspedes')
        ->assertSee('Total')
        ->assertSee('Estado')
        ->assertSee('Pago')
        ->assertSee('Acciones')
        ->assertSee('RES-'.str_pad((string) $reserva->id, 5, '0', STR_PAD_LEFT))
        ->assertSee($reserva->cliente->nombreCompleto())
        ->assertSee('#101')
        ->assertSee('4,500.00')
        ->assertSee('Confirmada')
        ->assertSee('Pagado');
});

test('marca el pago como pendiente mientras el total no esté cubierto', function () {
    $reserva = Reserva::factory()->create([
        'estado' => 'Confirmada',
        'monto_total' => 1000.00,
    ]);

    Pago::create([
        'reserva_id' => $reserva->id,
        'monto' => 400.00,
        'metodo_pago' => 'Efectivo',
        'fecha_pago' => now(),
    ]);

    Livewire::actingAs($this->admin)
        ->test(Index::class)
        ->assertSee('Confirmada')
        ->assertSee('Pendiente')
        ->assertDontSee('Pagado');
});

test('pagina las reservaciones de diez en diez', function () {
    Reserva::factory()->count(12)->create();

    $componente = Livewire::actingAs($this->admin)->test(Index::class);

    expect(substr_count($componente->html(), 'x-on:click="confirmarCancelacion('))->toBe(10);

    $componente->call('gotoPage', 2);

    expect(substr_count($componente->html(), 'x-on:click="confirmarCancelacion('))->toBe(2);
});

test('mantiene constante el número de consultas al crecer el listado (anti N+1)', function () {
    $tipo = tipoReserva('Doble', 1200.00, 2);
    $habitacion = habitacionReserva('201', $tipo);

    $crearReservas = function (int $cantidad) use ($habitacion): void {
        Reserva::factory()->count($cantidad)->create()->each(function (Reserva $reserva) use ($habitacion): void {
            $reserva->habitaciones()->attach($habitacion->id, ['precio_por_noche' => 1200]);

            Pago::create([
                'reserva_id' => $reserva->id,
                'monto' => 500.00,
                'metodo_pago' => 'Efectivo',
                'fecha_pago' => now(),
            ]);
        });
    };

    $contarConsultas = function (): int {
        $consultas = 0;

        DB::listen(function () use (&$consultas): void {
            $consultas++;
        });

        Livewire::actingAs($this->admin)->test(Index::class)->html();

        return $consultas;
    };

    $crearReservas(3);
    $consultasConTres = $contarConsultas();

    $crearReservas(4);
    $consultasConSiete = $contarConsultas();

    expect($consultasConSiete)->toBe($consultasConTres);
});

test('muestra el estado vacío cuando no hay reservaciones', function () {
    Livewire::actingAs($this->admin)
        ->test(Index::class)
        ->assertSee('Sin reservaciones')
        ->assertSee('Aún no hay reservaciones registradas.');
});

test('el botón superior abre el modal de creación', function () {
    Livewire::actingAs($this->admin)
        ->test(Index::class)
        ->assertSet('showModalCrear', false)
        ->dispatch('reserva.crear')
        ->assertSet('showModalCrear', true)
        ->assertSet('num_huespedes', 1)
        ->assertSee('Estado inicial');
});

test('el modal se cierra sin guardar', function () {
    Livewire::actingAs($this->admin)
        ->test(Index::class)
        ->call('abrirModalCrear')
        ->assertSet('showModalCrear', true)
        ->call('cerrarModalCrear')
        ->assertSet('showModalCrear', false);

    expect(Reserva::count())->toBe(0);
});

test('el selector de habitaciones solo muestra las disponibles', function () {
    $tipo = tipoReserva('Suite', 800.00, 2);
    habitacionReserva('401', $tipo, 'Disponible');
    habitacionReserva('402', $tipo, 'Ocupada');

    Livewire::actingAs($this->admin)
        ->test(Index::class)
        ->call('abrirModalCrear')
        ->assertSee('401')
        ->assertDontSee('402');
});

test('guarda la reservación confirmada, calcula el monto y ocupa la habitación', function () {
    $tipo = tipoReserva('Doble', 500.00, 2);
    $habitacion = habitacionReserva('201', $tipo);
    $cliente = Cliente::factory()->create();

    Livewire::actingAs($this->admin)
        ->test(Index::class)
        ->call('abrirModalCrear')
        ->set('cliente_id', (string) $cliente->id)
        ->set('habitacion_id', (string) $habitacion->id)
        ->set('check_in', now()->addDay()->toDateString())
        ->set('check_out', now()->addDays(4)->toDateString())
        ->set('num_huespedes', 2)
        ->call('guardarReserva')
        ->assertHasNoErrors()
        ->assertSet('showModalCrear', false)
        ->assertDispatched('reserva.creada');

    $reserva = Reserva::firstOrFail();

    expect($reserva->cliente_id)->toBe($cliente->id)
        ->and($reserva->user_id)->toBe($this->admin->id)
        ->and($reserva->estado)->toBe('Confirmada')
        ->and((float) $reserva->monto_total)->toBe(1500.00)
        ->and($reserva->habitacionesAsignadas()->pluck('habitacion_id')->all())->toBe([$habitacion->id]);

    expect($habitacion->fresh()->estado)->toBe('Ocupada');
});

test('rechaza guardar cuando la habitación ya no está disponible', function () {
    $tipo = tipoReserva('Simple', 300.00, 1);
    $habitacion = habitacionReserva('301', $tipo, 'Ocupada');
    $cliente = Cliente::factory()->create();

    Livewire::actingAs($this->admin)
        ->test(Index::class)
        ->call('abrirModalCrear')
        ->set('cliente_id', (string) $cliente->id)
        ->set('habitacion_id', (string) $habitacion->id)
        ->set('check_in', now()->addDay()->toDateString())
        ->set('check_out', now()->addDays(2)->toDateString())
        ->set('num_huespedes', 1)
        ->call('guardarReserva')
        ->assertHasErrors('habitacion_id')
        ->assertSet('showModalCrear', true);

    expect(Reserva::count())->toBe(0);
});

test('no permite más huéspedes que la capacidad de la habitación', function () {
    $tipo = tipoReserva('Individual', 250.00, 1);
    $habitacion = habitacionReserva('501', $tipo);
    $cliente = Cliente::factory()->create();

    Livewire::actingAs($this->admin)
        ->test(Index::class)
        ->call('abrirModalCrear')
        ->set('cliente_id', (string) $cliente->id)
        ->set('habitacion_id', (string) $habitacion->id)
        ->set('check_in', now()->addDay()->toDateString())
        ->set('check_out', now()->addDays(2)->toDateString())
        ->set('num_huespedes', 3)
        ->call('guardarReserva')
        ->assertHasErrors('num_huespedes');

    expect(Reserva::count())->toBe(0);
});

test('las acciones del menú emiten sus eventos', function () {
    $reserva = Reserva::factory()->create();

    $componente = Livewire::actingAs($this->admin)->test(Index::class);

    $componente->call('editar', $reserva->id)
        ->assertDispatched('reserva.editar', reservaId: $reserva->id)
        ->call('registrarPago', $reserva->id)
        ->assertDispatched('reserva.registrar-pago', reservaId: $reserva->id)
        ->call('checkIn', $reserva->id)
        ->assertDispatched('reserva.check-in', reservaId: $reserva->id)
        ->call('checkOut', $reserva->id)
        ->assertDispatched('reserva.check-out', reservaId: $reserva->id)
        ->call('cancelarReserva', $reserva->id)
        ->assertDispatched('reserva.cancelar', reservaId: $reserva->id);
});

test('el modal ofrece el alta rápida de un cliente', function () {
    Livewire::actingAs($this->admin)
        ->test(Index::class)
        ->call('abrirModalCrear')
        ->assertSee('Nombre completo')
        ->assertSee('Guardar cliente');
});

test('crea un cliente desde el modal y lo deja seleccionado', function () {
    $componente = Livewire::actingAs($this->admin)
        ->test(Index::class)
        ->call('abrirModalCrear')
        ->set('nuevoClienteNombre', 'María López García')
        ->set('nuevoClienteEmail', 'maria@example.com')
        ->set('nuevoClienteTelefono', '5512345678')
        ->call('guardarClienteYSeleccionar')
        ->assertHasNoErrors()
        ->assertDispatched('cliente.creado');

    $cliente = Cliente::firstOrFail();

    $componente
        ->assertSet('cliente_id', (string) $cliente->id)
        ->assertSet('nuevoClienteNombre', '')
        ->assertSet('nuevoClienteEmail', '')
        ->assertSet('nuevoClienteTelefono', '')
        ->assertSee($cliente->nombreCompleto());

    expect($cliente->nombre)->toBe('María')
        ->and($cliente->apellido)->toBe('López García')
        ->and($cliente->email)->toBe('maria@example.com')
        ->and($cliente->telefono)->toBe('5512345678');
});

test('valida los datos del cliente antes de crearlo desde el modal', function () {
    Livewire::actingAs($this->admin)
        ->test(Index::class)
        ->call('abrirModalCrear')
        ->set('nuevoClienteNombre', '')
        ->set('nuevoClienteEmail', 'esto-no-es-un-correo')
        ->call('guardarClienteYSeleccionar')
        ->assertHasErrors(['nuevoClienteNombre' => 'required', 'nuevoClienteEmail' => 'email']);

    expect(Cliente::count())->toBe(0);
});

test('el layout incluye el interceptor global de sesión expirada', function () {
    $this->actingAs($this->admin)
        ->get(route('reservaciones'))
        ->assertOk()
        ->assertSee("Livewire.hook('request'", false)
        ->assertSee('preventDefault', false);
});
