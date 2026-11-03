<?php

use App\Livewire\Clientes;
use App\Livewire\Reportes;
use App\Models\Cliente;
use App\Models\Reserva;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Livewire\Livewire;

beforeEach(function () {
    $this->seed(RolesAndPermissionsSeeder::class);

    $this->admin = User::factory()->create();
    $this->admin->assignRole('super-admin');
});

test('the clientes module renders a grid with reservation counters and status', function () {
    $cliente = Cliente::factory()->create([
        'nombre' => 'Ana',
        'apellido' => 'López',
        'email' => 'ana@example.com',
    ]);

    Reserva::factory()->for($cliente)->count(2)->create(['estado' => 'Confirmada']);

    Livewire::actingAs($this->admin)
        ->test(Clientes::class)
        ->assertSee('Clientes')
        ->assertSee('Ana López')
        ->assertSee('ana@example.com')
        ->assertSee('En estancia')
        ->assertSee('Reservas')
        ->assertSee('Activas')
        ->assertSeeHtml('grid-cols-1 gap-5 md:grid-cols-2 xl:grid-cols-3');
});

test('the clientes search filters by name and preserves the query string in the paginator', function () {
    foreach (range(1, 10) as $indice) {
        Cliente::factory()->create(['nombre' => 'Ana '.$indice, 'apellido' => 'López']);
    }

    Cliente::factory()->create(['nombre' => 'Bruno', 'apellido' => 'Díaz']);

    Livewire::actingAs($this->admin)
        ->test(Clientes::class)
        ->set('search', 'ana')
        ->assertSee('Ana 1')
        ->assertDontSee('Bruno Díaz');

    $this->actingAs($this->admin)
        ->get(route('clientes', ['buscar' => 'ana']))
        ->assertOk()
        ->assertSee('Ana 1')
        ->assertDontSee('Bruno Díaz');
});

test('the clientes state filter separates clients with and without reservations', function () {
    $conReserva = Cliente::factory()->create(['nombre' => 'Con', 'apellido' => 'Reserva']);
    Cliente::factory()->create(['nombre' => 'Sin', 'apellido' => 'Reserva']);

    Reserva::factory()->for($conReserva)->create(['estado' => 'Pendiente']);

    Livewire::actingAs($this->admin)
        ->test(Clientes::class)
        ->set('filtroEstado', Clientes::FILTRO_CON_RESERVAS)
        ->assertSee('Con Reserva')
        ->assertDontSee('Sin Reserva')
        ->set('filtroEstado', Clientes::FILTRO_SIN_RESERVAS)
        ->assertSee('Sin Reserva')
        ->assertDontSee('Con Reserva');
});

test('a client can be created and updated from the module', function () {
    Livewire::actingAs($this->admin)
        ->test(Clientes::class)
        ->call('crear')
        ->assertSet('mostrarModal', true)
        ->set('nombre', 'Luis')
        ->set('apellido', 'Ramírez')
        ->set('email', 'luis@example.com')
        ->set('telefono', '5512345678')
        ->set('tipo_identificacion', 'INE')
        ->set('numero_identificacion', 'RAML900101')
        ->call('guardar')
        ->assertHasNoErrors()
        ->assertSet('mostrarModal', false)
        ->assertDispatched('modal-close');

    $cliente = Cliente::where('email', 'luis@example.com')->firstOrFail();

    Livewire::actingAs($this->admin)
        ->test(Clientes::class)
        ->call('editar', $cliente->id)
        ->assertSet('nombre', 'Luis')
        ->set('apellido', 'Ramírez Solís')
        ->call('guardar')
        ->assertHasNoErrors();

    expect($cliente->fresh()->apellido)->toBe('Ramírez Solís');
});

test('a client with reservations cannot be deleted', function () {
    $cliente = Cliente::factory()->create();
    Reserva::factory()->for($cliente)->create();

    Livewire::actingAs($this->admin)
        ->test(Clientes::class)
        ->call('eliminar', $cliente->id)
        ->assertSet('mensajeError', 'No se puede eliminar a este cliente porque tiene reservaciones registradas.');

    expect(Cliente::find($cliente->id))->not->toBeNull();
});

test('a client without reservations can be deleted', function () {
    $cliente = Cliente::factory()->create();

    Livewire::actingAs($this->admin)
        ->test(Clientes::class)
        ->call('eliminar', $cliente->id)
        ->assertSet('mensajeExito', 'Cliente eliminado correctamente.');

    expect(Cliente::find($cliente->id))->toBeNull();
});

test('the reportes dashboard renders the four kpis and ignored chart containers', function () {
    Livewire::actingAs($this->admin)
        ->test(Reportes::class)
        ->assertSee('Reportes')
        ->assertSee('Ocupación')
        ->assertSee('Ingresos totales')
        ->assertSee('Gastos totales')
        ->assertSee('Balance')
        ->assertSeeHtml('wire:ignore')
        ->assertSeeHtml('grafica-reservas-estado')
        ->assertSeeHtml('grafica-pagos-metodo')
        ->assertSeeHtml('grafica-gastos-categoria')
        ->assertSeeHtml('grafica-ingresos-gastos');
});
