<?php

use App\Livewire\Gastos;
use App\Models\Gasto;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Livewire\Livewire;

beforeEach(function () {
    $this->seed(RolesAndPermissionsSeeder::class);

    $this->admin = User::factory()->create();
    $this->admin->assignRole('super-admin');

    $this->crearGasto = function (array $datos = []): Gasto {
        return Gasto::create(array_merge([
            'concepto' => 'Compra de blancos',
            'monto' => 1250.50,
            'categoria' => 'Insumos',
            'fecha_gasto' => now()->toDateString(),
            'observaciones' => null,
        ], $datos));
    };
});

test('la tarjeta de gastos totales suma todos los registros de la base de datos', function () {
    call_user_func($this->crearGasto, ['monto' => 100.00]);
    call_user_func($this->crearGasto, ['concepto' => 'Papelería', 'monto' => 250.75]);

    Livewire::actingAs($this->admin)
        ->test(Gastos::class)
        ->assertSee('Gastos totales')
        ->assertSee('$350.75');
});

test('la tarjeta muestra cero cuando no hay gastos registrados', function () {
    Livewire::actingAs($this->admin)
        ->test(Gastos::class)
        ->assertSee('Gastos totales')
        ->assertSee('$0.00')
        ->assertSee('No hay gastos registrados.');
});

test('el total no depende de la página visible cuando hay paginación', function () {
    // Conceptos con relleno de cero para que «Gasto 05» no se confunda con
    // «Gasto 15» al buscar subcadenas en el HTML.
    foreach (range(1, 15) as $i) {
        call_user_func($this->crearGasto, [
            'concepto' => sprintf('Gasto %02d', $i),
            'monto' => 100.00,
            'fecha_gasto' => now()->addDays($i)->toDateString(),
        ]);
    }

    // Se ordena por fecha descendente: la página 1 va del 15 al 06 y la
    // página 2 del 05 al 01.
    $componente = Livewire::actingAs($this->admin)->test(Gastos::class);

    $componente
        ->assertSee('$1,500.00')
        ->assertSee('Gasto 15')
        ->assertDontSee('Gasto 05');

    // La página 2 solo pinta 5 filas, pero la tarjeta sigue sumando los 15.
    $componente
        ->call('gotoPage', 2, 'paginaGastos')
        ->assertSee('$1,500.00')
        ->assertSee('Gasto 05')
        ->assertDontSee('Gasto 15');
});

test('el total se actualiza después de agregar un gasto', function () {
    Livewire::actingAs($this->admin)
        ->test(Gastos::class)
        ->call('crear')
        ->set('concepto', 'Mantenimiento de aire acondicionado')
        ->set('monto', '800')
        ->set('categoria', 'Mantenimiento')
        ->set('fecha_gasto', now()->toDateString())
        ->set('observaciones', 'Visita del técnico')
        ->call('guardar')
        ->assertSet('mensajeExito', 'Gasto registrado correctamente.')
        ->assertSee('$800.00')
        ->assertSee('Visita del técnico');

    expect(Gasto::count())->toBe(1);
});

test('el total se actualiza después de editar un gasto', function () {
    $gasto = call_user_func($this->crearGasto, ['monto' => 500.00]);

    Livewire::actingAs($this->admin)
        ->test(Gastos::class)
        ->call('editar', $gasto->id)
        ->assertSet('pagina', 'editar')
        ->assertSet('concepto', 'Compra de blancos')
        ->set('monto', '750')
        ->call('guardar')
        ->assertSet('mensajeExito', 'Gasto actualizado correctamente.')
        ->assertSee('$750.00');

    expect($gasto->refresh()->monto)->toBe('750.00');
});

test('el total se actualiza después de eliminar un gasto', function () {
    call_user_func($this->crearGasto, ['monto' => 300.00]);
    $otro = call_user_func($this->crearGasto, ['concepto' => 'Papelería', 'monto' => 200.00]);

    Livewire::actingAs($this->admin)
        ->test(Gastos::class)
        ->assertSee('$500.00')
        ->call('eliminar', $otro->id)
        ->assertSet('mensajeExito', 'Gasto eliminado correctamente.')
        ->assertSee('$300.00')
        ->assertDontSee('Papelería');

    expect(Gasto::count())->toBe(1);
});

test('el formulario valida que el monto sea numérico y mayor que cero', function () {
    Livewire::actingAs($this->admin)
        ->test(Gastos::class)
        ->call('crear')
        ->set('concepto', 'Sin monto')
        ->set('monto', '0')
        ->set('categoria', 'Otros')
        ->set('fecha_gasto', now()->toDateString())
        ->call('guardar')
        ->assertHasErrors(['monto']);

    Livewire::actingAs($this->admin)
        ->test(Gastos::class)
        ->call('crear')
        ->set('concepto', 'No numérico')
        ->set('monto', 'abc')
        ->set('categoria', 'Otros')
        ->set('fecha_gasto', now()->toDateString())
        ->call('guardar')
        ->assertHasErrors(['monto']);

    expect(Gasto::count())->toBe(0);
});

test('el formulario valida la fecha y la categoría obligatorias', function () {
    Livewire::actingAs($this->admin)
        ->test(Gastos::class)
        ->call('crear')
        ->set('concepto', 'Sin fecha')
        ->set('monto', '100')
        ->set('categoria', '')
        ->set('fecha_gasto', 'no-es-fecha')
        ->call('guardar')
        ->assertHasErrors(['fecha_gasto', 'categoria']);

    expect(Gasto::count())->toBe(0);
});

test('las observaciones son opcionales y se guardan vacías como nulas', function () {
    Livewire::actingAs($this->admin)
        ->test(Gastos::class)
        ->call('crear')
        ->set('concepto', 'Luz y agua')
        ->set('monto', '900')
        ->set('categoria', 'Servicios')
        ->set('fecha_gasto', now()->toDateString())
        ->call('guardar')
        ->assertSet('mensajeExito', 'Gasto registrado correctamente.');

    expect(Gasto::first()->observaciones)->toBeNull();
});

test('una persona sin gastos.crear no puede abrir el formulario de alta', function () {
    $soloVer = User::factory()->create();
    $soloVer->assignRole('recepcionista');
    // El recepcionista no tiene gastos.* en el seeder.

    Livewire::actingAs($soloVer)
        ->test(Gastos::class)
        ->call('crear')
        ->assertSet('pagina', 'index')
        ->assertSet('mensajeError', 'No tienes permiso para crear gastos.');
});

test('una persona sin gastos.editar no puede editar ni guardar un gasto', function () {
    $gasto = call_user_func($this->crearGasto, ['monto' => 500.00]);

    $soloVer = User::factory()->create();
    $soloVer->assignRole('recepcionista');

    Livewire::actingAs($soloVer)
        ->test(Gastos::class)
        ->call('editar', $gasto->id)
        ->assertSet('pagina', 'index')
        ->assertSet('mensajeError', 'No tienes permiso para editar gastos.');

    expect($gasto->refresh()->monto)->toBe('500.00');
});

test('una persona sin gastos.eliminar no puede eliminar un gasto', function () {
    $gasto = call_user_func($this->crearGasto);

    $soloVer = User::factory()->create();
    $soloVer->assignRole('recepcionista');

    Livewire::actingAs($soloVer)
        ->test(Gastos::class)
        ->call('eliminar', $gasto->id)
        ->assertSet('mensajeError', 'No tienes permiso para eliminar gastos.');

    expect(Gasto::count())->toBe(1);
});

test('el formulario de edición carga los datos actuales incluidas las observaciones', function () {
    $gasto = call_user_func($this->crearGasto, [
        'observaciones' => 'Se compró por oferta',
        'fecha_gasto' => '2026-10-20',
    ]);

    Livewire::actingAs($this->admin)
        ->test(Gastos::class)
        ->call('editar', $gasto->id)
        ->assertSet('gastoId', $gasto->id)
        ->assertSet('concepto', 'Compra de blancos')
        ->assertSet('categoria', 'Insumos')
        ->assertSet('fecha_gasto', '2026-10-20')
        ->assertSet('observaciones', 'Se compró por oferta');
});

/*
|--------------------------------------------------------------------------
| Cableado del desplegable de categoría
|--------------------------------------------------------------------------
*/

test('el desplegable de categoría escribe el valor con $wire.set y no con el evento del select oculto', function () {
    // Sin `modelo`, `select()` solo dispara los eventos del <select> oculto:
    // eso genera una segunda petición que `wire:submit` puede adelantar, de
    // modo que `guardar()` recibía `categoria` vacía y reventaba en `required`.
    Livewire::actingAs($this->admin)
        ->test(Gastos::class)
        ->call('crear')
        ->assertSee("modelo: 'categoria'", false);
});

test('el desplegable de categoría ata su clave al valor para refrescar la etiqueta visible', function () {
    Livewire::actingAs($this->admin)
        ->test(Gastos::class)
        ->call('crear')
        ->assertSee('wire:key="categoria-"', false)
        ->set('categoria', 'Insumos')
        ->assertSee('wire:key="categoria-Insumos"', false);
});

test('una categoría elegida en el desplegable llega íntegra a la consulta que guarda el gasto', function () {
    Livewire::actingAs($this->admin)
        ->test(Gastos::class)
        ->call('crear')
        ->set('concepto', 'Papelería de recepción')
        ->set('monto', '275.50')
        ->set('categoria', 'Insumos')
        ->set('fecha_gasto', now()->toDateString())
        ->call('guardar')
        ->assertSet('mensajeExito', 'Gasto registrado correctamente.')
        ->assertHasNoErrors();

    $this->assertDatabaseHas('gastos', [
        'concepto' => 'Papelería de recepción',
        'monto' => 275.50,
        'categoria' => 'Insumos',
    ]);
});

/*
|--------------------------------------------------------------------------
| Duración de las notificaciones (5000 ms)
|--------------------------------------------------------------------------
*/

test('la notificación de éxito al guardar un gasto se oculta exactamente a los 5000 ms', function () {
    Livewire::actingAs($this->admin)
        ->test(Gastos::class)
        ->call('crear')
        ->set('concepto', 'Compra de blancos')
        ->set('monto', '150')
        ->set('categoria', 'Insumos')
        ->set('fecha_gasto', now()->toDateString())
        ->call('guardar')
        ->assertSet('mensajeExito', 'Gasto registrado correctamente.')
        ->assertSee('Gasto registrado correctamente.')
        ->assertSee('this.temporizador = setTimeout(() => { this.visible = false }, 5000)', false);
});

test('la notificación de error al crear un gasto también se oculta a los 5000 ms', function () {
    $soloVer = User::factory()->create();
    $soloVer->assignRole('recepcionista');

    Livewire::actingAs($soloVer)
        ->test(Gastos::class)
        ->call('crear')
        ->assertSet('mensajeError', 'No tienes permiso para crear gastos.')
        ->assertSee('No tienes permiso para crear gastos.')
        ->assertSee('this.temporizador = setTimeout(() => { this.visible = false }, 5000)', false);
});
