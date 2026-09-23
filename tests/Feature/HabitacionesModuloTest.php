<?php

use App\Livewire\Habitaciones;
use App\Models\Habitacion;
use App\Models\TipoHabitacion;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Livewire\Livewire;

beforeEach(function () {
    $this->seed(RolesAndPermissionsSeeder::class);

    $this->admin = User::factory()->create();
    $this->admin->assignRole('super-admin');
});

function crearTipo(string $nombre, float $precio, int $capacidad, ?string $descripcion = null): TipoHabitacion
{
    return TipoHabitacion::create([
        'nombre' => $nombre,
        'precio_base' => $precio,
        'capacidad' => $capacidad,
        'descripcion' => $descripcion,
    ]);
}

function crearHabitacion(string $numero, TipoHabitacion $tipo, string $estado = 'Disponible', int $piso = 1): Habitacion
{
    return Habitacion::create([
        'numero_habitacion' => $numero,
        'tipo_habitacion_id' => $tipo->id,
        'estado' => $estado,
        'piso' => $piso,
    ]);
}

test('a super-admin sees the room dashboard with the total count and controls', function () {
    $tipo = crearTipo('Estándar', 899.00, 2);
    crearHabitacion('101', $tipo);

    Livewire::actingAs($this->admin)
        ->test(Habitaciones::class)
        ->assertSee('Habitaciones')
        ->assertSee('1 habitación registrada')
        ->assertSee('Agregar habitación')
        ->assertSee('Buscar por número o tipo...')
        ->assertSee('Todos los estados')
        ->assertSee('#101')
        ->assertSee('Estándar')
        ->assertSee('$899')
        ->assertSee('/noche');
});

test('the search filters rooms by number and room type', function () {
    $estandar = crearTipo('Estándar', 899.00, 2);
    $deluxe = crearTipo('Deluxe', 1499.00, 3);
    crearHabitacion('101', $estandar);
    crearHabitacion('202', $deluxe);

    Livewire::actingAs($this->admin)
        ->test(Habitaciones::class)
        ->set('search', '101')
        ->assertSee('#101')
        ->assertDontSee('#202')
        ->set('search', 'Deluxe')
        ->assertSee('#202')
        ->assertDontSee('#101')
        ->set('search', '')
        ->assertSee('#101')
        ->assertSee('#202');
});

test('the estado filter maps Requiere Limpieza and En Limpieza to the Limpieza value', function () {
    $tipo = crearTipo('Estándar', 899.00, 2);
    crearHabitacion('101', $tipo, 'Disponible');
    $limpieza = crearHabitacion('104', $tipo, 'Limpieza');
    crearHabitacion('203', $tipo, 'Mantenimiento');

    Livewire::actingAs($this->admin)
        ->test(Habitaciones::class)
        ->set('filtroEstado', 'en-limpieza')
        ->assertSee('#104')
        ->assertDontSee('#101')
        ->assertDontSee('#203')
        ->set('filtroEstado', 'requiere-limpieza')
        ->assertSee('#104')
        ->assertDontSee('#101');

    expect(Habitacion::whereKey($limpieza->id)->exists())->toBeTrue();
});

test('the Fuera de Servicio filter maps to the Mantenimiento value', function () {
    $tipo = crearTipo('Estándar', 899.00, 2);
    crearHabitacion('101', $tipo, 'Disponible');
    crearHabitacion('203', $tipo, 'Mantenimiento');

    Livewire::actingAs($this->admin)
        ->test(Habitaciones::class)
        ->set('filtroEstado', 'fuera-servicio')
        ->assertSee('#203')
        ->assertDontSee('#101');
});

test('the state pills show dynamic counters', function () {
    $tipo = crearTipo('Estándar', 899.00, 2);
    crearHabitacion('101', $tipo, 'Disponible');
    crearHabitacion('102', $tipo, 'Disponible');
    crearHabitacion('103', $tipo, 'Ocupada');
    crearHabitacion('104', $tipo, 'Limpieza');

    $html = Livewire::actingAs($this->admin)
        ->test(Habitaciones::class)
        ->html();

    $compacto = preg_replace('/\s+/', '', $html) ?? '';

    expect(str_contains($html, 'Disponible'))->toBeTrue()
        ->and(str_contains($html, 'Ocupada'))->toBeTrue()
        ->and(str_contains($html, 'Reservada'))->toBeTrue()
        ->and(str_contains($html, 'Requiere Limpieza'))->toBeTrue()
        ->and(str_contains($html, 'En Limpieza'))->toBeTrue()
        ->and(str_contains($html, 'Fuera de Servicio'))->toBeTrue()
        ->and(substr_count($compacto, '>2<'))->toBeGreaterThanOrEqual(1)
        ->and(substr_count($compacto, '>1<'))->toBeGreaterThanOrEqual(2);
});

test('a super-admin can switch between grid and list views', function () {
    $tipo = crearTipo('Estándar', 899.00, 2);
    crearHabitacion('101', $tipo);

    Livewire::actingAs($this->admin)
        ->test(Habitaciones::class)
        ->assertSet('vista', 'cuadricula')
        ->call('cambiarVista', 'lista')
        ->assertSet('vista', 'lista')
        ->assertSee('#101')
        ->call('cambiarVista', 'cuadricula')
        ->assertSet('vista', 'cuadricula');
});

test('the modal derives capacity, price and description from the selected room type', function () {
    $tipo = crearTipo('Estándar', 899.00, 2, 'Habitación clásica con vistas.');

    $componente = Livewire::actingAs($this->admin)
        ->test(Habitaciones::class)
        ->call('crear')
        ->set('tipo_habitacion_id', (string) $tipo->id);

    $componente
        ->assertSet('capacidad', '2')
        ->assertSet('precio_por_noche', '899.00')
        ->assertSet('descripcion', 'Habitación clásica con vistas.')
        ->assertSet('estado', 'Disponible')
        ->assertSet('piso', '1');

    expect($componente->get('foto_url'))->toBeString()
        ->and($componente->get('foto_url'))->toBeIn(Habitaciones::IMAGENES);
});

test('a super-admin can create a room from the modal', function () {
    $tipo = crearTipo('Estándar', 899.00, 2);

    Livewire::actingAs($this->admin)
        ->test(Habitaciones::class)
        ->call('crear')
        ->set('numero_habitacion', '501')
        ->set('tipo_habitacion_id', (string) $tipo->id)
        ->set('piso', '5')
        ->set('estado', 'Disponible')
        ->call('guardar')
        ->assertHasNoErrors()
        ->assertSee('creada correctamente')
        ->assertSet('mostrarModal', false);

    $habitacion = Habitacion::where('numero_habitacion', '501')->first();

    expect($habitacion)->not->toBeNull()
        ->and($habitacion->tipo_habitacion_id)->toBe($tipo->id)
        ->and($habitacion->piso)->toBe(5)
        ->and($habitacion->estado)->toBe('Disponible');
});

test('the modal rejects estados that are not part of the enumerated database values', function () {
    $tipo = crearTipo('Estándar', 899.00, 2);

    Livewire::actingAs($this->admin)
        ->test(Habitaciones::class)
        ->call('crear')
        ->set('numero_habitacion', '601')
        ->set('tipo_habitacion_id', (string) $tipo->id)
        ->set('piso', '6')
        ->set('estado', 'Reservada')
        ->call('guardar')
        ->assertHasErrors(['estado']);

    expect(Habitacion::where('numero_habitacion', '601')->exists())->toBeFalse();
});

test('a super-admin can edit an existing room', function () {
    $tipo = crearTipo('Estándar', 899.00, 2);
    $habitacion = crearHabitacion('101', $tipo, 'Ocupada', 1);

    Livewire::actingAs($this->admin)
        ->test(Habitaciones::class)
        ->call('editar', $habitacion->id)
        ->assertSet('habitacionId', $habitacion->id)
        ->assertSet('numero_habitacion', '101')
        ->assertSet('estado', 'Ocupada')
        ->assertSet('capacidad', '2')
        ->assertSet('piso', '1')
        ->set('piso', '2')
        ->set('estado', 'Disponible')
        ->call('guardar')
        ->assertHasNoErrors()
        ->assertSee('actualizada correctamente');

    expect($habitacion->fresh()->piso)->toBe(2)
        ->and($habitacion->fresh()->estado)->toBe('Disponible');
});

test('a super-admin can delete a room from the inventory', function () {
    $tipo = crearTipo('Estándar', 899.00, 2);
    $habitacion = crearHabitacion('101', $tipo);

    Livewire::actingAs($this->admin)
        ->test(Habitaciones::class)
        ->call('eliminar', $habitacion->id)
        ->assertSee('eliminada correctamente');

    expect(Habitacion::whereKey($habitacion->id)->exists())->toBeFalse();
});
