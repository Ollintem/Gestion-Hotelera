<?php

use App\Livewire\Temporadas;
use App\Models\Temporada;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Livewire\Livewire;

beforeEach(function () {
    $this->seed(RolesAndPermissionsSeeder::class);

    $this->admin = User::factory()->create();
    $this->admin->assignRole('super-admin');
});

test('el precio efectivo del modelo es el precio base por el multiplicador', function () {
    $recargo = Temporada::factory()->create([
        'precio_base' => 850.00,
        'multiplicador_precio' => 1.25,
    ]);

    expect($recargo->precio_efectivo)->toBe(1062.50);

    $descuento = Temporada::factory()->create([
        'precio_base' => 850.00,
        'multiplicador_precio' => 0.85,
    ]);

    expect($descuento->precio_efectivo)->toBe(722.50);
});

test('el precio efectivo se redondea a dos decimales', function () {
    $temporada = Temporada::factory()->create([
        'precio_base' => 333.33,
        'multiplicador_precio' => 1.15,
    ]);

    expect($temporada->precio_efectivo)->toBe(383.33);
});

test('el modelo formatea el precio en pesos y el multiplicador con el signo de multiplicacion', function () {
    $temporada = Temporada::factory()->create([
        'precio_base' => 850.00,
        'multiplicador_precio' => 0.85,
    ]);

    expect($temporada->precioBaseEnPesos())->toBe('$850.00')
        ->and($temporada->precioEfectivoEnPesos())->toBe('$722.50')
        ->and($temporada->multiplicadorEnTexto())->toBe('×0.85');
});

test('el nivel de demanda se deduce del multiplicador', function () {
    $baja = Temporada::factory()->create(['multiplicador_precio' => 0.85]);
    $normal = Temporada::factory()->create(['multiplicador_precio' => 1.00]);
    $alta = Temporada::factory()->create(['multiplicador_precio' => 1.40]);

    expect($baja->nivelDemanda())->toBe('baja')
        ->and($baja->etiquetaNivelDemanda())->toBe('Baja demanda')
        ->and($normal->nivelDemanda())->toBe('media')
        ->and($normal->etiquetaNivelDemanda())->toBe('Demanda normal')
        ->and($alta->nivelDemanda())->toBe('alta')
        ->and($alta->etiquetaNivelDemanda())->toBe('Alta demanda');
});

test('la variacion del precio es positiva con recargo y negativa con descuento', function () {
    $recargo = Temporada::factory()->conRecargo(1.25)->create(['precio_base' => 100]);
    $descuento = Temporada::factory()->conDescuento()->create(['precio_base' => 100]);
    $sinCambio = Temporada::factory()->create(['precio_base' => 100, 'multiplicador_precio' => 1.00]);

    expect($recargo->variacionPrecio())->toBe(25.0)
        ->and($descuento->variacionPrecio())->toBe(-15.0)
        ->and($sinCambio->variacionPrecio())->toBe(0.0);
});

test('el listado se ordena por fecha de inicio', function () {
    $tardia = Temporada::factory()->create(['nombre' => 'Tardia', 'fecha_inicio' => '2026-12-01', 'fecha_fin' => '2026-12-05']);
    $temprana = Temporada::factory()->create(['nombre' => 'Temprana', 'fecha_inicio' => '2026-01-05', 'fecha_fin' => '2026-01-10']);

    $ids = Livewire::actingAs($this->admin)
        ->test(Temporadas::class)
        ->viewData('temporadas')
        ->pluck('id')
        ->all();

    expect($ids)->toBe([$temprana->id, $tardia->id]);
});

test('se puede crear una temporada con su precio base y su multiplicador', function () {
    Livewire::actingAs($this->admin)
        ->test(Temporadas::class)
        ->call('crear')
        ->set('nombre', 'Temporada Alta')
        ->set('fecha_inicio', '2026-12-15')
        ->set('fecha_fin', '2026-12-31')
        ->set('multiplicador_precio', '1.25')
        ->set('precio_base', '850.00')
        ->call('guardar')
        ->assertHasNoErrors()
        ->assertSet('modalAbierto', false)
        ->assertSet('pagina', 'index')
        ->assertDispatched('notificacion', mensaje: 'Temporada creada correctamente.');

    $temporada = Temporada::where('nombre', 'Temporada Alta')->first();

    expect($temporada)->not->toBeNull()
        ->and($temporada->fecha_inicio->toDateString())->toBe('2026-12-15')
        ->and($temporada->fecha_fin->toDateString())->toBe('2026-12-31')
        ->and((float) $temporada->precio_base)->toBe(850.00)
        ->and((float) $temporada->multiplicador_precio)->toBe(1.25)
        ->and($temporada->precio_efectivo)->toBe(1062.50)
        ->and($temporada->activo)->toBeTrue();
});

test('se puede editar una temporada existente', function () {
    $temporada = Temporada::factory()->conDescuento()->create([
        'nombre' => 'Temporada Baja',
        'precio_base' => 900.00,
        'multiplicador_precio' => 0.85,
    ]);

    Livewire::actingAs($this->admin)
        ->test(Temporadas::class)
        ->call('editar', $temporada->id)
        ->assertSet('nombre', 'Temporada Baja')
        ->assertSet('precio_base', '900.00')
        ->assertSet('multiplicador_precio', '0.85')
        ->assertSet('pagina', 'editar')
        ->set('nombre', 'Temporada Media')
        ->set('multiplicador_precio', '1.00')
        ->call('guardar')
        ->assertHasNoErrors()
        ->assertDispatched('notificacion', mensaje: 'Temporada actualizada correctamente.');

    $actualizada = $temporada->fresh();

    expect($actualizada->nombre)->toBe('Temporada Media')
        ->and((float) $actualizada->multiplicador_precio)->toBe(1.00)
        ->and($actualizada->precio_efectivo)->toBe(900.00);
});

test('guardar una edicion no crea una temporada nueva', function () {
    $temporada = Temporada::factory()->create(['nombre' => 'Original']);

    Livewire::actingAs($this->admin)
        ->test(Temporadas::class)
        ->call('editar', $temporada->id)
        ->set('nombre', 'Renombrada')
        ->call('guardar');

    expect(Temporada::count())->toBe(1)
        ->and(Temporada::first()->nombre)->toBe('Renombrada');
});

test('el alta se rechaza cuando falta el nombre', function () {
    Livewire::actingAs($this->admin)
        ->test(Temporadas::class)
        ->call('crear')
        ->set('nombre', '')
        ->set('fecha_inicio', '2026-03-01')
        ->set('fecha_fin', '2026-03-10')
        ->set('precio_base', '500')
        ->call('guardar')
        ->assertHasErrors('nombre')
        ->assertSet('modalAbierto', true);

    expect(Temporada::count())->toBe(0);
});

test('la fecha de fin no puede ser anterior a la fecha de inicio', function () {
    Livewire::actingAs($this->admin)
        ->test(Temporadas::class)
        ->call('crear')
        ->set('nombre', 'Rango invertido')
        ->set('fecha_inicio', '2026-03-10')
        ->set('fecha_fin', '2026-03-01')
        ->set('precio_base', '500')
        ->call('guardar')
        ->assertHasErrors(['fecha_fin' => 'after_or_equal']);

    expect(Temporada::count())->toBe(0);
});

test('una temporada de una sola noche es valida', function () {
    Livewire::actingAs($this->admin)
        ->test(Temporadas::class)
        ->call('crear')
        ->set('nombre', 'Una noche')
        ->set('fecha_inicio', '2026-03-10')
        ->set('fecha_fin', '2026-03-10')
        ->set('precio_base', '500')
        ->call('guardar')
        ->assertHasNoErrors();

    expect(Temporada::where('nombre', 'Una noche')->exists())->toBeTrue();
});

test('el precio base debe ser positivo', function () {
    Livewire::actingAs($this->admin)
        ->test(Temporadas::class)
        ->call('crear')
        ->set('nombre', 'Gratis')
        ->set('fecha_inicio', '2026-03-01')
        ->set('fecha_fin', '2026-03-10')
        ->set('precio_base', '0')
        ->call('guardar')
        ->assertHasErrors('precio_base');

    expect(Temporada::count())->toBe(0);
});

test('el multiplicador se mantiene dentro del recorrido del deslizador', function () {
    Livewire::actingAs($this->admin)
        ->test(Temporadas::class)
        ->call('crear')
        ->set('nombre', 'Fuera de rango')
        ->set('fecha_inicio', '2026-03-01')
        ->set('fecha_fin', '2026-03-10')
        ->set('precio_base', '500')
        ->set('multiplicador_precio', '3.50')
        ->call('guardar')
        ->assertHasErrors('multiplicador_precio')
        ->set('multiplicador_precio', '0.10')
        ->call('guardar')
        ->assertHasErrors('multiplicador_precio');

    expect(Temporada::count())->toBe(0);
});

test('el formulario muestra el precio efectivo mientras se escribe', function () {
    $componente = Livewire::actingAs($this->admin)
        ->test(Temporadas::class)
        ->call('crear')
        ->set('precio_base', '850.00')
        ->set('multiplicador_precio', '1.25');

    expect($componente->viewData('precioEfectivo'))->toBe(1062.50)
        ->and($componente->viewData('precioEfectivoEnPesos'))->toBe('$1,062.50')
        ->and($componente->viewData('variacionPrecio'))->toBe(25.0)
        ->and($componente->html())->toContain('$1,062.50');

    // Bajar el multiplicador se refleja en el recuadro sin guardar.
    $componente->set('multiplicador_precio', '0.85');

    expect($componente->viewData('precioEfectivoEnPesos'))->toBe('$722.50')
        ->and($componente->viewData('multiplicadorEnTexto'))->toBe('×0.85')
        ->and($componente->html())->toContain('$722.50');

    // Un campo a medio escribir vale cero, no un error: el recuadro no parpadea.
    $componente->set('precio_base', '');

    expect($componente->viewData('precioEfectivoEnPesos'))->toBe('$0.00');
});

test('el precio base y el multiplicador viajan en vivo', function () {
    $html = Livewire::actingAs($this->admin)->test(Temporadas::class)->call('crear')->html();

    expect($html)->toContain('wire:model.live="precio_base"')
        ->and($html)->toContain('wire:model.live="multiplicador_precio"');
});

test('el estado se alterna sin salir del listado y avisa del cambio', function () {
    $temporada = Temporada::factory()->create(['activo' => true]);

    Livewire::actingAs($this->admin)
        ->test(Temporadas::class)
        ->call('alternarActivo', $temporada->id)
        ->assertDispatched('notificacion', mensaje: 'Temporada desactivada correctamente.');

    expect($temporada->fresh()->activo)->toBeFalse();

    Livewire::actingAs($this->admin)
        ->test(Temporadas::class)
        ->call('alternarActivo', $temporada->id)
        ->assertDispatched('notificacion', mensaje: 'Temporada activada correctamente.');

    expect($temporada->fresh()->activo)->toBeTrue();
});

test('una temporada se elimina del calendario', function () {
    $temporada = Temporada::factory()->create();

    Livewire::actingAs($this->admin)
        ->test(Temporadas::class)
        ->call('eliminar', $temporada->id)
        ->assertDispatched('notificacion', mensaje: 'Temporada eliminada correctamente.');

    expect(Temporada::whereKey($temporada->id)->exists())->toBeFalse();
});

test('el boton de alta abre el modal y el de cerrar lo descarta', function () {
    $componente = Livewire::actingAs($this->admin)
        ->test(Temporadas::class)
        ->call('crear')
        ->assertSet('modalAbierto', true)
        ->assertSet('pagina', 'crear')
        ->assertSet('nombre', '');

    $componente->set('nombre', 'Sin guardar')->call('cerrarModal');

    expect($componente->get('modalAbierto'))->toBeFalse()
        ->and($componente->get('pagina'))->toBe('index')
        ->and($componente->get('nombre'))->toBe('');
});

test('el boton de editar abre el modal con los datos de la temporada', function () {
    $temporada = Temporada::factory()->create([
        'nombre' => 'Semana Santa',
        'fecha_inicio' => '2027-04-01',
        'fecha_fin' => '2027-04-10',
    ]);

    Livewire::actingAs($this->admin)
        ->test(Temporadas::class)
        ->call('editar', $temporada->id)
        ->assertSet('modalAbierto', true)
        ->assertSet('pagina', 'editar')
        ->assertSet('nombre', 'Semana Santa')
        ->assertSet('fecha_inicio', '2027-04-01')
        ->assertSet('fecha_fin', '2027-04-10');
});

test('la pantalla muestra el titulo, el boton de alta y el calendario de temporadas', function () {
    Temporada::factory()->create(['nombre' => 'Temporada Alta', 'fecha_inicio' => '2026-12-01', 'fecha_fin' => '2026-12-15']);

    $html = Livewire::actingAs($this->admin)->test(Temporadas::class)->html();

    expect($html)->toContain('Temporadas')
        ->toContain('Administra los períodos y precios de temporada')
        ->toContain('Nueva temporada')
        ->toContain('Calendario de temporadas')
        ->toContain('Gestión de temporadas')
        ->toContain('2026-12-01')
        ->toContain('2026-12-15')
        ->toContain('Temporada Alta');
});

test('la pantalla vacia invita a crear la primera temporada', function () {
    $html = Livewire::actingAs($this->admin)->test(Temporadas::class)->html();

    expect($html)->toContain('No hay temporadas registradas.');
});

test('el boton de alta es anaranjado y redondeado', function () {
    $html = Livewire::actingAs($this->admin)->test(Temporadas::class)->html();

    expect($html)->toContain('wire:click="crear"')
        ->toContain('rounded-xl bg-amber-500')
        ->toContain('hover:bg-amber-600');
});

test('las dos ventanas estan montadas para poder animar la salida', function () {
    $html = Livewire::actingAs($this->admin)->test(Temporadas::class)->html();

    // El velo desenfocado y el centrado son el contrato visual del modal.
    expect($html)->toContain('fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4 backdrop-blur-sm')
        ->toContain('x-show="abierto && pagina === \'crear\'"')
        ->toContain('x-show="abierto && pagina === \'editar\'"')
        ->toContain('x-transition:enter')
        ->toContain('x-transition:leave');
});

test('los formularios de las dos ventanas no repiten identificadores', function () {
    $html = Livewire::actingAs($this->admin)->test(Temporadas::class)->html();

    // Las ventanas comparten formulario y conviven en el DOM: sin prefijo, el
    // `for` de cada etiqueta apuntaría al input de la ventana oculta.
    expect($html)->toContain('id="nueva-nombre"')
        ->toContain('id="editar-nombre"')
        ->toContain('id="nueva-precio_base"')
        ->toContain('id="editar-precio_base"');
});

test('el formulario ofrece un deslizador de 0.5 a 2.0 con sus etiquetas', function () {
    $html = Livewire::actingAs($this->admin)->test(Temporadas::class)->call('crear')->html();

    expect($html)->toContain('type="range"')
        ->toContain('min="0.5"')
        ->toContain('max="2"')
        ->toContain('0.5× (50% descuento)')
        ->toContain('2.0× (doble precio)');
});

test('el aviso flotante se retira solo a los tres segundos', function () {
    $html = Livewire::actingAs($this->admin)->test(Temporadas::class)->html();

    expect($html)->toContain('x-on:notificacion.window')
        ->toContain('setTimeout(() => visible = false, 3000)');
});

test('la tarjeta y la fila pintan la demanda con el color que le corresponde', function () {
    Temporada::factory()->conDescuento()->create(['nombre' => 'Temporada Baja', 'precio_base' => 1000]);
    Temporada::factory()->create(['nombre' => 'Temporada Media', 'precio_base' => 1000, 'multiplicador_precio' => 1.00]);
    Temporada::factory()->conRecargo()->create(['nombre' => 'Temporada Alta', 'precio_base' => 1000]);

    $html = Livewire::actingAs($this->admin)->test(Temporadas::class)->html();

    expect($html)->toContain('border-l-emerald-500')
        ->toContain('border-l-amber-400')
        ->toContain('border-l-rose-500')
        ->toContain('Baja demanda')
        ->toContain('Demanda normal')
        ->toContain('Alta demanda')
        ->toContain('$850.00')
        ->toContain('$1,000.00')
        ->toContain('$1,400.00');
});

test('la tabla permite activar, editar y eliminar desde la propia fila', function () {
    $temporada = Temporada::factory()->create(['nombre' => 'Temporada Alta']);

    $html = Livewire::actingAs($this->admin)->test(Temporadas::class)->html();

    expect($html)->toContain('wire:click="alternarActivo('.$temporada->id.')"')
        ->toContain('wire:click="editar('.$temporada->id.')"')
        ->toContain('wire:click="eliminar('.$temporada->id.')"')
        ->toContain('¿Eliminar la temporada «Temporada Alta»?');
});
