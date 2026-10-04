<?php

use App\Livewire\Servicios;
use App\Models\Categoria;
use App\Models\Reserva;
use App\Models\ReservaServicio;
use App\Models\Servicio;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Livewire\Livewire;

beforeEach(function () {
    $this->seed(RolesAndPermissionsSeeder::class);

    $this->admin = User::factory()->create();
    $this->admin->assignRole('super-admin');
});

/**
 * Recorta el cuerpo de la tabla para poder afirmar sobre las filas visibles sin
 * que el modal, que siempre se renderiza, contamine la comprobación.
 */
function cuerpoTabla(string $html): string
{
    preg_match('/<tbody.*?<\/tbody>/s', $html, $coincidencias);

    return $coincidencias[0] ?? '';
}

/**
 * Recorta el desplegable de categorías del filtro, para no confundirlo con el
 * del modal, que sí lista también las categorías inactivas.
 */
function opcionesFiltroCategoria(string $html): string
{
    preg_match('/Todas las categorías.*?<\/select>/s', $html, $coincidencias);

    return $coincidencias[0] ?? '';
}

/**
 * Devuelve la categoría del catálogo base sembrado por la migración, o crea una
 * nueva si el nombre no forma parte de ese catálogo.
 */
function categoriaDe(string $nombre): Categoria
{
    return Categoria::firstOrCreate(
        ['nombre' => $nombre],
        ['descripcion' => 'Categoría de prueba.', 'activo' => true]
    );
}

test('el panel se estructura en tarjetas KPI, barra de filtros y tabla de servicios', function () {
    $servicio = Servicio::factory()->create(['nombre' => 'Desayuno buffet', 'precio' => 320.00]);

    $html = Livewire::actingAs($this->admin)->test(Servicios::class)->html();

    expect($html)
        // Sección 1: rejilla de cuatro tarjetas KPI.
        ->toContain('grid grid-cols-1 md:grid-cols-4 gap-4 mb-6')
        ->toContain('relative overflow-hidden rounded-2xl border border-gray-200 bg-white p-4 shadow-sm transition-all duration-300 hover:border-amber-300 hover:shadow-md')
        ->toContain('CATÁLOGO')
        ->toContain('CATEGORÍAS')
        ->toContain('CARGOS REGISTRADOS')
        ->toContain('CONSUMOS FACTURADOS')
        // Sección 2: buscador a la izquierda, filtro a la derecha.
        ->toContain('flex gap-4 mb-6')
        ->toContain('placeholder="Buscar por nombre o descripción..."')
        // Los anillos de enfoque ya son ámbar, no naranjas.
        ->toContain('focus:border-amber-500 focus:outline-none focus:ring-2 focus:ring-amber-500/10')
        ->toContain('Todas las categorías')
        // Sección 3: contenedor de tabla sin bordes verticales.
        ->toContain('bg-white rounded-2xl border border-slate-100 overflow-hidden')
        ->toContain('divide-y divide-slate-50')
        ->toContain('SERVICIO')
        ->toContain('CATEGORÍA')
        ->toContain('PRECIO (MXN)')
        ->toContain('CARGOS')
        ->toContain('ACCIONES')
        ->toContain('text-xs font-semibold uppercase tracking-wider text-slate-600')
        ->toContain('Desayuno buffet')
        ->toContain('$320.00')
        ->toContain('wire:click="editar('.$servicio->id.')"');
});

test('las cabeceras de la tabla se muestran en mayúsculas y en gris', function () {
    $html = Livewire::actingAs($this->admin)->test(Servicios::class)->html();

    expect($html)->toContain('px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-600')
        ->and($html)->toContain('px-6 py-3 text-right text-xs font-semibold uppercase tracking-wider text-slate-600')
        // El encabezado se apoya en un fondo gris para despegarlo del cuerpo.
        ->and($html)->toContain('<thead class="bg-slate-100">');
});

test('el nombre va en negrita y la descripción debajo en gris claro', function () {
    Servicio::factory()->create([
        'nombre' => 'Desayuno buffet',
        'descripcion' => 'Café, jugo y panadería caliente.',
    ]);

    $html = Livewire::actingAs($this->admin)->test(Servicios::class)->html();

    expect($html)->toContain('text-slate-800 font-bold')
        ->toContain('text-sm text-slate-400')
        ->toContain('Café, jugo y panadería caliente.');
});

test('la categoría se muestra como badge ámbar y los cargos como total', function () {
    $categoria = categoriaDe('Alimentación');
    $servicio = Servicio::factory()->create(['nombre' => 'Desayuno buffet', 'categoria_id' => $categoria->id]);

    ReservaServicio::factory()->count(3)->create([
        'servicio_id' => $servicio->id,
        'reserva_id' => Reserva::factory()->create(['estado' => 'Finalizada'])->id,
    ]);

    $fila = cuerpoTabla(Livewire::actingAs($this->admin)->test(Servicios::class)->html());

    expect($fila)->toContain('inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-amber-100 text-amber-800')
        ->toContain('Alimentación')
        // El contador de cargos de la fila.
        ->toMatch('/text-slate-700 font-medium">\s*3\s*</')
        // La paleta del módulo es ámbar: el badge no debe volver al naranja.
        ->and($fila)->not->toContain('orange-');
});

test('un servicio heredado sin categoría muestra el texto de reemplazo', function () {
    Servicio::factory()->sinCategoria()->create(['nombre' => 'Traslado privado']);

    $html = Livewire::actingAs($this->admin)->test(Servicios::class)->html();

    expect($html)->toContain('Sin categoría');
});

test('un servicio sin descripción muestra el texto de reemplazo', function () {
    Servicio::factory()->create(['descripcion' => null]);

    $html = Livewire::actingAs($this->admin)->test(Servicios::class)->html();

    expect($html)->toContain('Sin descripción');
});

test('la tabla vacía muestra el estado vacío', function () {
    $html = Livewire::actingAs($this->admin)->test(Servicios::class)->html();

    expect($html)->toContain('No hay servicios registrados.');
});

test('los KPIs cuentan catálogo, categorías activas, cargos y consumos facturados', function () {
    $bienestar = categoriaDe('Bienestar');
    Categoria::query()->whereKey($bienestar->id)->update(['activo' => true]);
    Categoria::query()->where('nombre', '!=', $bienestar->nombre)->update(['activo' => false]);

    $desayuno = Servicio::factory()->create(['nombre' => 'Desayuno buffet', 'categoria_id' => $bienestar->id]);
    $spa = Servicio::factory()->create(['nombre' => 'Spa y masajes', 'categoria_id' => $bienestar->id]);

    $reservaFinalizada = Reserva::factory()->finalizada()->create();
    $reservaPendiente = Reserva::factory()->create(['estado' => 'Pendiente']);

    ReservaServicio::factory()->create([
        'servicio_id' => $desayuno->id,
        'reserva_id' => $reservaFinalizada->id,
        'subtotal' => 320.00,
    ]);
    ReservaServicio::factory()->create([
        'servicio_id' => $spa->id,
        'reserva_id' => $reservaFinalizada->id,
        'subtotal' => 680.00,
    ]);
    ReservaServicio::factory()->create([
        'servicio_id' => $spa->id,
        'reserva_id' => $reservaPendiente->id,
        'subtotal' => 999.00,
    ]);

    // El KPI se toma del componente, no de una expression regular sobre el HTML.
    $kpis = Livewire::actingAs($this->admin)->test(Servicios::class)->viewData('kpis');

    expect($kpis['totalServicios'])->toBe(2)
        // Las categorías desactivadas no cuentan como activas.
        ->and($kpis['categoriasActivas'])->toBe(1)
        ->and($kpis['cargosRegistrados'])->toBe(3)
        // Solo cuenta la reserva finalizada; el subtotal de la pendiente se excluye.
        ->and($kpis['consumosFacturados'])->toBe(1000.00);
});

test('el KPI de consumos facturados se renderiza formateado como moneda', function () {
    $servicio = Servicio::factory()->create();

    ReservaServicio::factory()->create([
        'servicio_id' => $servicio->id,
        'reserva_id' => Reserva::factory()->finalizada()->create()->id,
        'subtotal' => 1234.50,
    ]);

    $html = Livewire::actingAs($this->admin)->test(Servicios::class)->html();

    expect($html)->toContain('$1,234.50');
});

test('los KPIs describen el hotel completo y no la selección del filtro', function () {
    $alimentacion = categoriaDe('Alimentación');
    $bienestar = categoriaDe('Bienestar');
    $categoriasActivas = Categoria::query()->activas()->count();

    Servicio::factory()->create(['nombre' => 'Desayuno buffet', 'categoria_id' => $alimentacion->id]);
    Servicio::factory()->create(['nombre' => 'Spa y masajes', 'categoria_id' => $bienestar->id]);

    $kpis = Livewire::actingAs($this->admin)
        ->test(Servicios::class)
        ->set('categoriaFiltro', (string) $alimentacion->id)
        ->viewData('kpis');

    expect($kpis['totalServicios'])->toBe(2)
        ->and($kpis['categoriasActivas'])->toBe($categoriasActivas);
});

test('el buscador filtra por nombre o por descripción', function () {
    $categoria = Categoria::factory()->create();

    Servicio::factory()->create([
        'nombre' => 'Desayuno buffet',
        'descripcion' => 'Café, jugo y panadería caliente.',
        'categoria_id' => $categoria->id,
    ]);
    Servicio::factory()->create([
        'nombre' => 'Spa y masajes',
        'descripcion' => 'Masaje relajante de 60 minutos.',
        'categoria_id' => $categoria->id,
    ]);

    $componente = Livewire::actingAs($this->admin)->test(Servicios::class);

    expect(cuerpoTabla($componente->set('search', 'spa')->html()))
        ->toContain('Spa y masajes')
        ->not->toContain('Desayuno buffet')
        // La búsqueda también alcanza la descripción.
        ->and(cuerpoTabla($componente->set('search', 'panadería')->html()))->toContain('Desayuno buffet')
        ->and(cuerpoTabla($componente->set('search', 'zzzz')->html()))->toContain('No hay servicios registrados.');
});

test('el filtro de categoría acota los resultados', function () {
    $alimentacion = categoriaDe('Alimentación');
    $bienestar = categoriaDe('Bienestar');

    Servicio::factory()->create(['nombre' => 'Desayuno buffet', 'categoria_id' => $alimentacion->id]);
    Servicio::factory()->create(['nombre' => 'Spa y masajes', 'categoria_id' => $bienestar->id]);

    $componente = Livewire::actingAs($this->admin)->test(Servicios::class);

    expect(cuerpoTabla($componente->set('categoriaFiltro', (string) $bienestar->id)->html()))
        ->toContain('Spa y masajes')
        ->not->toContain('Desayuno buffet')
        // La opción por defecto recupera todo el catálogo.
        ->and(cuerpoTabla($componente->set('categoriaFiltro', '')->html()))
        ->toContain('Desayuno buffet')
        ->toContain('Spa y masajes');
});

test('el filtro solo ofrece categorías activas', function () {
    categoriaDe('Alimentación');
    Categoria::factory()->inactiva()->create(['nombre' => 'Categoría retirada']);

    $filtro = opcionesFiltroCategoria(Livewire::actingAs($this->admin)->test(Servicios::class)->html());

    expect($filtro)->toContain('Alimentación')
        ->not->toContain('Categoría retirada');
});

test('limpiarFiltros restablece el buscador y la categoría', function () {
    $alimentacion = categoriaDe('Alimentación');
    Servicio::factory()->create(['nombre' => 'Desayuno buffet', 'categoria_id' => $alimentacion->id]);
    Servicio::factory()->create(['nombre' => 'Spa y masajes']);

    Livewire::actingAs($this->admin)
        ->test(Servicios::class)
        ->set('search', 'spa')
        ->set('categoriaFiltro', (string) $alimentacion->id)
        ->call('limpiarFiltros')
        ->assertSet('search', '')
        ->assertSet('categoriaFiltro', '');
});

test('la tabla pagina el catálogo en bloques de diez', function () {
    $categoria = Categoria::factory()->create();

    Servicio::factory()
        ->count(23)
        ->create(['categoria_id' => $categoria->id]);

    $componente = Livewire::actingAs($this->admin)->test(Servicios::class);

    expect($componente->viewData('servicios')->total())->toBe(23)
        ->and($componente->viewData('servicios')->count())->toBe(10);

    $componente->call('gotoPage', 3);

    expect($componente->viewData('servicios')->count())->toBe(3);
});

test('el modal pide el nombre como texto libre etiquetado NOMBRE', function () {
    $html = Livewire::actingAs($this->admin)->test(Servicios::class)->html();

    expect($html)
        // La etiqueta del campo pasó de NÚMERO a NOMBRE.
        ->toContain('for="nombre"')
        ->toContain('>Nombre</label>')
        ->not->toContain('>Número</label>')
        ->not->toContain('NÚMERO')
        // Texto libre: un input de la misma familia visual que el precio.
        ->toContain('type="text"')
        ->toContain('wire:model="nombre"')
        ->toContain('placeholder="Ejemplo: Desayuno buffet"')
        ->toContain('maxlength="100"')
        ->toContain('required')
        // El desplegable del catálogo cerrado desaparece por completo.
        ->not->toContain('Selecciona un servicio...')
        ->not->toContain('wire:key="nombre-')
        ->not->toContain('data-value="Desayuno buffet"')
        ->not->toContain('data-value="Traslado al aeropuerto"');
});

test('el modal acepta un nombre libre que nunca estuvo en el catálogo cerrado', function () {
    $categoria = categoriaDe('Alimentación');

    Livewire::actingAs($this->admin)
        ->test(Servicios::class)
        ->call('crear')
        ->set('nombre', 'Barra de café de la terraza')
        ->set('categoria_id', $categoria->id)
        ->set('precio', '180.00')
        ->call('guardar')
        ->assertHasNoErrors()
        ->assertDispatched('notificacion', mensaje: 'Servicio creado correctamente.');

    expect(Servicio::where('nombre', 'Barra de café de la terraza')->exists())->toBeTrue();
});

test('el modal sigue rechazando un nombre vacío o demasiado largo', function () {
    $categoria = categoriaDe('Alimentación');

    $componente = Livewire::actingAs($this->admin)
        ->test(Servicios::class)
        ->call('crear')
        ->set('categoria_id', $categoria->id)
        ->set('precio', '320.00');

    $componente->set('nombre', '')
        ->call('guardar')
        ->assertHasErrors(['nombre']);

    $componente->set('nombre', str_repeat('a', 101))
        ->call('guardar')
        ->assertHasErrors(['nombre']);

    expect(Servicio::query()->count())->toBe(0);
});

test('el modal acepta dos servicios con el mismo nombre', function () {
    $categoria = categoriaDe('Alimentación');

    foreach ([320.00, 340.00] as $precio) {
        Livewire::actingAs($this->admin)
            ->test(Servicios::class)
            ->call('crear')
            ->set('nombre', 'Desayuno buffet')
            ->set('categoria_id', $categoria->id)
            ->set('precio', number_format($precio, 2, '.', ''))
            ->call('guardar')
            ->assertHasNoErrors();
    }

    $servicios = Servicio::where('nombre', 'Desayuno buffet')->get();

    expect($servicios)->toHaveCount(2)
        ->and($servicios->pluck('precio')->map(fn ($precio) => (float) $precio)->all())->toEqualCanonicalizing([320.00, 340.00]);
});

test('editar un servicio conservando su nombre no dispara el error de duplicado', function () {
    $servicio = Servicio::factory()->create(['nombre' => 'Lavandería', 'precio' => 180.00]);

    Livewire::actingAs($this->admin)
        ->test(Servicios::class)
        ->call('editar', $servicio->id)
        ->set('precio', '195.00')
        ->call('guardar')
        ->assertHasNoErrors();

    expect((float) $servicio->fresh()->precio)->toBe(195.00);
});

test('editar un servicio heredado conserva su nombre fuera del catálogo cerrado', function () {
    $servicio = Servicio::factory()->create(['nombre' => 'Traslado privado', 'precio' => 450.00]);

    Livewire::actingAs($this->admin)
        ->test(Servicios::class)
        ->call('editar', $servicio->id)
        ->assertSet('nombre', 'Traslado privado')
        ->set('precio', '480.00')
        ->call('guardar')
        ->assertHasNoErrors()
        ->assertDispatched('notificacion', mensaje: 'Servicio actualizado correctamente.');

    expect($servicio->fresh()->nombre)->toBe('Traslado privado')
        ->and((float) $servicio->fresh()->precio)->toBe(480.00);
});

test('abrir el modal de creación deja el nombre en blanco', function () {
    $servicio = Servicio::factory()->create(['nombre' => 'Traslado privado']);

    $componente = Livewire::actingAs($this->admin)->test(Servicios::class)
        ->call('editar', $servicio->id)
        ->assertSet('nombre', 'Traslado privado')
        ->call('crear');

    expect($componente->get('nombre'))->toBe('')
        ->and($componente->html())->not->toContain('value="Traslado privado"');
});

test('un super-admin crea un servicio con categoría desde el modal', function () {
    $categoria = categoriaDe('Alimentación');

    Livewire::actingAs($this->admin)
        ->test(Servicios::class)
        ->call('crear')
        ->set('nombre', 'Desayuno buffet')
        ->set('descripcion', 'Café, jugo y panadería caliente.')
        ->set('categoria_id', $categoria->id)
        ->set('precio', '320.00')
        ->call('guardar')
        ->assertHasNoErrors()
        ->assertDispatched('notificacion', mensaje: 'Servicio creado correctamente.')
        ->assertSet('mostrarModal', false);

    $servicio = Servicio::where('nombre', 'Desayuno buffet')->first();

    expect($servicio)->not->toBeNull()
        ->and($servicio->descripcion)->toBe('Café, jugo y panadería caliente.')
        ->and($servicio->categoria_id)->toBe($categoria->id)
        ->and((float) $servicio->precio)->toBe(320.00)
        ->and($servicio->activo)->toBeTrue();
});

test('una descripción vacía se guarda como nula con categoría obligatoria', function () {
    $categoria = categoriaDe('Lavandería');

    Livewire::actingAs($this->admin)
        ->test(Servicios::class)
        ->call('crear')
        ->set('nombre', 'Lavandería')
        ->set('descripcion', '')
        ->set('categoria_id', $categoria->id)
        ->set('precio', '180.00')
        ->call('guardar')
        ->assertHasNoErrors();

    $servicio = Servicio::where('nombre', 'Lavandería')->first();

    expect($servicio->descripcion)->toBeNull()
        ->and($servicio->categoria_id)->toBe($categoria->id);
});

test('el modal rechaza guardar sin categoría', function () {
    Livewire::actingAs($this->admin)
        ->test(Servicios::class)
        ->call('crear')
        ->set('nombre', 'Desayuno buffet')
        ->set('categoria_id', null)
        ->set('precio', '320.00')
        ->call('guardar')
        ->assertHasErrors(['categoria_id']);

    expect(Servicio::where('nombre', 'Desayuno buffet')->exists())->toBeFalse();
});

test('un servicio heredado sin categoría no puede volver a guardarse hasta elegir una', function () {
    $servicio = Servicio::factory()->sinCategoria()->create(['nombre' => 'Desayuno buffet']);

    Livewire::actingAs($this->admin)
        ->test(Servicios::class)
        ->call('editar', $servicio->id)
        ->assertSet('categoria_id', null)
        ->call('guardar')
        ->assertHasErrors(['categoria_id']);

    expect($servicio->fresh()->categoria_id)->toBeNull();
});

test('el modal rechaza una categoría que no existe', function () {
    Livewire::actingAs($this->admin)
        ->test(Servicios::class)
        ->call('crear')
        ->set('nombre', 'Desayuno buffet')
        ->set('categoria_id', 9999)
        ->set('precio', '320.00')
        ->call('guardar')
        ->assertHasErrors(['categoria_id']);

    expect(Servicio::where('nombre', 'Desayuno buffet')->exists())->toBeFalse();
});

test('abrir el modal de edición no altera el filtro de la tabla', function () {
    $alimentacion = categoriaDe('Alimentación');
    categoriaDe('Bienestar');

    $servicio = Servicio::factory()->create([
        'nombre' => 'Desayuno buffet',
        'categoria_id' => $alimentacion->id,
    ]);

    Livewire::actingAs($this->admin)
        ->test(Servicios::class)
        ->set('categoriaFiltro', (string) $alimentacion->id)
        ->call('editar', $servicio->id)
        // El modal toma la categoría del servicio...
        ->assertSet('categoria_id', $alimentacion->id)
        // ...y el filtro de la tabla queda intacto.
        ->assertSet('categoriaFiltro', (string) $alimentacion->id);
});

test('el modal lista todas las categorías, incluidas las inactivas', function () {
    $retirada = Categoria::factory()->inactiva()->create(['nombre' => 'Categoría retirada']);

    $html = Livewire::actingAs($this->admin)->test(Servicios::class)->html();

    // El modal sí la ofrece para no perder el valor de un servicio ya clasificado.
    expect($html)->toContain('value="'.$retirada->id.'"')
        ->toContain('Selecciona una categoría...');
});

test('el modal rechaza una descripción demasiado larga', function () {
    Livewire::actingAs($this->admin)
        ->test(Servicios::class)
        ->call('crear')
        ->set('nombre', 'Desayuno buffet')
        ->set('descripcion', str_repeat('a', 501))
        ->set('precio', '320.00')
        ->call('guardar')
        ->assertHasErrors(['descripcion']);

    expect(Servicio::where('nombre', 'Desayuno buffet')->exists())->toBeFalse();
});

test('editar carga la descripción y la guardada la actualiza', function () {
    $servicio = Servicio::factory()->create(['nombre' => 'Desayuno buffet', 'descripcion' => 'Café, jugo y panadería caliente.', 'precio' => 320.00]);

    Livewire::actingAs($this->admin)
        ->test(Servicios::class)
        ->call('editar', $servicio->id)
        ->assertSet('servicioId', $servicio->id)
        ->assertSet('nombre', 'Desayuno buffet')
        ->assertSet('descripcion', 'Café, jugo y panadería caliente.')
        ->set('descripcion', 'Café, jugo, fruta y panadería caliente.')
        ->call('guardar')
        ->assertHasNoErrors()
        ->assertDispatched('notificacion', mensaje: 'Servicio actualizado correctamente.');

    expect($servicio->fresh()->descripcion)->toBe('Café, jugo, fruta y panadería caliente.');
});

test('el modal ofrece la categoría como desplegable de Alpine con la opción de reemplazo', function () {
    $categoria = categoriaDe('Alimentación');

    $servicio = Servicio::factory()->create([
        'nombre' => 'Desayuno buffet',
        'categoria_id' => $categoria->id,
    ]);

    $componente = Livewire::actingAs($this->admin)->test(Servicios::class);

    expect($componente->html())
        ->toContain('wire:model="categoria_id"')
        ->toContain("modelo: 'categoria_id'")
        ->toContain('this.$wire.set(this.modelo, value)')
        // Opción de reemplazo: ya no está deshabilitada, solo sin seleccionar.
        ->toContain('<option value="" selected>Selecciona una categoría...</option>')
        ->toContain('<span class="block min-w-0 truncate">Selecciona una categoría...</span>')
        // El desplegable toma el identificador de la categoría como valor y no
        // el índice del bucle, que llegaría al backend como un id inexistente.
        ->toContain('data-value="'.$categoria->id.'"')
        ->toContain('>Alimentación</span>')
        ->toContain('<option value="'.$categoria->id.'" >Alimentación</option>')
        // Sin selección, la clave inyectada queda en el prefijo del campo.
        ->toContain('wire:key="categoria_id-"');

    $componente->call('editar', $servicio->id);

    // La clave sigue al valor devuelto por el servidor, de modo que el control
    // se reconstruye y el botón muestra lo que el servidor devolvió.
    expect($componente->html())
        ->toContain('wire:key="categoria_id-'.$categoria->id.'"')
        ->toContain('<option value="'.$categoria->id.'" selected>Alimentación</option>')
        ->toContain("selected: '".$categoria->id."'");
});

test('la alerta de éxito se pinta con Alpine escuchando el evento de Livewire', function () {
    $html = Livewire::actingAs($this->admin)->test(Servicios::class)->html();

    expect($html)
        ->toContain('x-data="{ show: false, mensaje: \'\' }"')
        ->toContain('x-on:notificacion.window="show = true; mensaje = $event.detail.mensaje; setTimeout(() => show = false, 3000)"')
        ->toContain('x-show="show"')
        ->toContain('x-transition')
        ->toContain('<span x-text="mensaje"')
        ->toContain('aria-live="polite"')
        // El toast arranca oculto: no debe quedar un mensaje de éxito quemado en
        // el HTML después de recargar la página.
        ->toContain('x-cloak')
        ->and($html)->not->toContain('Servicio eliminado correctamente.');
});

test('las acciones de la tabla emiten el evento de notificación', function () {
    $servicio = Servicio::factory()->create(['nombre' => 'Desayuno buffet']);

    Livewire::actingAs($this->admin)
        ->test(Servicios::class)
        ->call('eliminar', $servicio->id)
        ->assertDispatched('notificacion', mensaje: 'Servicio eliminado correctamente.');

    $otro = Servicio::factory()->create();

    Livewire::actingAs($this->admin)
        ->test(Servicios::class)
        ->call('alternarActivo', $otro->id)
        ->assertDispatched('notificacion', mensaje: 'Servicio desactivado correctamente.');

    Livewire::actingAs($this->admin)
        ->test(Servicios::class)
        ->call('alternarActivo', $otro->id)
        ->assertDispatched('notificacion', mensaje: 'Servicio activado correctamente.');
});

test('el componente ya no expone la propiedad mensajeExito', function () {
    expect(property_exists(Servicios::class, 'mensajeExito'))->toBeFalse();
});

test('alternarActivo desactiva y reactiva sin eliminar el servicio', function () {
    $servicio = Servicio::factory()->create();

    Livewire::actingAs($this->admin)
        ->test(Servicios::class)
        ->call('alternarActivo', $servicio->id)
        ->assertDispatched('notificacion', mensaje: 'Servicio desactivado correctamente.');

    expect($servicio->fresh()->activo)->toBeFalse();

    Livewire::actingAs($this->admin)
        ->test(Servicios::class)
        ->call('alternarActivo', $servicio->id)
        ->assertDispatched('notificacion', mensaje: 'Servicio activado correctamente.');

    expect($servicio->fresh()->activo)->toBeTrue();
});

test('un super-admin elimina un servicio desde la fila', function () {
    $servicio = Servicio::factory()->create(['nombre' => 'Desayuno buffet']);

    Livewire::actingAs($this->admin)
        ->test(Servicios::class)
        ->call('eliminar', $servicio->id)
        ->assertDispatched('notificacion', mensaje: 'Servicio eliminado correctamente.');

    expect(Servicio::whereKey($servicio->id)->exists())->toBeFalse();
});

test('eliminar una categoría deja al servicio sin categoría en vez de borrarlo', function () {
    $categoria = Categoria::factory()->create();
    $servicio = Servicio::factory()->create(['categoria_id' => $categoria->id]);

    $categoria->delete();

    expect($servicio->fresh())->not->toBeNull()
        ->and($servicio->fresh()->categoria_id)->toBeNull();
});
