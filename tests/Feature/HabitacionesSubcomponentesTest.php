<?php

use App\Livewire\Habitaciones\Filtros;
use App\Livewire\Habitaciones\FormModal;
use App\Livewire\Habitaciones\HabitacionCard;
use App\Livewire\Habitaciones\Index as HabitacionesIndex;
use App\Models\Habitacion;
use App\Models\TipoHabitacion;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Livewire\Livewire;

beforeEach(function () {
    $this->seed(RolesAndPermissionsSeeder::class);

    $this->admin = User::factory()->create();
    $this->admin->assignRole('super-admin');

    $this->crearTipo = function (string $nombre, float $precio, int $capacidad, ?string $descripcion = null): TipoHabitacion {
        return TipoHabitacion::create([
            'nombre' => $nombre,
            'precio_base' => $precio,
            'capacidad' => $capacidad,
            'descripcion' => $descripcion,
        ]);
    };

    $this->crearHabitacion = function (string $numero, TipoHabitacion $tipo, string $estado = 'Disponible', int $piso = 1): Habitacion {
        return Habitacion::create([
            'numero_habitacion' => $numero,
            'tipo_habitacion_id' => $tipo->id,
            'estado' => $estado,
            'piso' => $piso,
        ]);
    };

    // Las fotografías se convierten con GD o Imagick; si el servidor no tiene
    // ninguna de las dos extensiones, esas pruebas se omiten.
    $this->requiereGD = function (): void {
        if (! extension_loaded('gd') && ! extension_loaded('imagick')) {
            $this->markTestSkipped('Requiere la extensión GD o Imagick de PHP en el servidor.');
        }
    };

    // Las tarjetas anidadas se reutilizan sin re-render cuando su clave no cambia,
    // así que el inventario se verifica sobre el paginador y no sobre el HTML.
    $this->numerosVisibles = fn ($componente): array => $componente->instance()->habitaciones
        ->pluck('numero_habitacion')
        ->all();
});

test('el contenedor renderiza el inventario con el total, los filtros y el alta de habitaciones', function () {
    $tipo = ($this->crearTipo)('Estándar', 899.00, 2);
    ($this->crearHabitacion)('101', $tipo);

    Livewire::actingAs($this->admin)
        ->test(HabitacionesIndex::class)
        ->assertSee('Habitaciones')
        ->assertSee('1 habitación registrada')
        ->assertSee('Agregar habitación')
        ->assertSee('Buscar por número o tipo...')
        // El recorte por estado lo hacen las tarjetas KPI: el desplegable
        // "Todos los estados" ya no forma parte de la cabecera.
        ->assertDontSee('Todos los estados')
        ->assertSee('#101')
        ->assertSee('Estándar')
        ->assertSee('$899')
        ->assertSee('/noche');
});

test('el inventario se pagina de doce en doce habitaciones', function () {
    $tipo = ($this->crearTipo)('Estándar', 899.00, 2);

    foreach (range(101, 115) as $numero) {
        ($this->crearHabitacion)((string) $numero, $tipo);
    }

    $componente = Livewire::actingAs($this->admin)->test(HabitacionesIndex::class);

    expect(substr_count($componente->html(), 'aria-label="Editar habitación"'))->toBe(HabitacionesIndex::POR_PAGINA)
        ->and($componente->html())->toContain('#101')
        ->and($componente->html())->not->toContain('#115');

    $componente->call('nextPage');

    expect(substr_count($componente->html(), 'aria-label="Editar habitación"'))->toBe(3)
        ->and($componente->html())->toContain('#115');
});

test('la búsqueda filtra por número y por tipo de habitación', function () {
    $estandar = ($this->crearTipo)('Estándar', 899.00, 2);
    $deluxe = ($this->crearTipo)('Deluxe', 1499.00, 3);
    ($this->crearHabitacion)('101', $estandar);
    ($this->crearHabitacion)('202', $deluxe);

    $componente = Livewire::actingAs($this->admin)->test(HabitacionesIndex::class);

    expect(($this->numerosVisibles)($componente))->toBe(['101', '202']);

    $componente->set('search', '101');
    expect(($this->numerosVisibles)($componente))->toBe(['101']);

    $componente->set('search', 'Deluxe');
    expect(($this->numerosVisibles)($componente))->toBe(['202']);

    $componente->set('search', '');
    expect(($this->numerosVisibles)($componente))->toBe(['101', '202']);
});

test('el filtro de estado agrupa Limpieza y Mantenimiento sin filtros legacy', function () {
    $tipo = ($this->crearTipo)('Estándar', 899.00, 2);
    ($this->crearHabitacion)('101', $tipo, 'Disponible');
    ($this->crearHabitacion)('104', $tipo, 'Limpieza');
    ($this->crearHabitacion)('203', $tipo, 'Mantenimiento');

    $componente = Livewire::actingAs($this->admin)->test(HabitacionesIndex::class);

    $componente->call('filtrarPor', 'Limpieza');
    expect($componente->get('filtroEstado'))->toBe('Limpieza')
        ->and(($this->numerosVisibles)($componente))->toBe(['104']);

    $componente->call('filtrarPor', 'Mantenimiento');
    expect(($this->numerosVisibles)($componente))->toBe(['203']);

    $componente->call('filtrarPor', 'todos');
    expect(($this->numerosVisibles)($componente))->toBe(['101', '104', '203']);
});

test('las claves legacy de filtro ya no son válidas y vuelven al total', function () {
    $tipo = ($this->crearTipo)('Estándar', 899.00, 2);
    ($this->crearHabitacion)('101', $tipo, 'Disponible');
    ($this->crearHabitacion)('104', $tipo, 'Limpieza');
    ($this->crearHabitacion)('203', $tipo, 'Mantenimiento');

    $componente = Livewire::actingAs($this->admin)->test(HabitacionesIndex::class);

    foreach (['requiere-limpieza', 'en-limpieza', 'lista', 'fuera-servicio'] as $claveLegacy) {
        $componente->call('filtrarPor', $claveLegacy);

        expect($componente->get('filtroEstado'))->toBe(HabitacionesIndex::FILTRO_TODOS)
            ->and(($this->numerosVisibles)($componente))->toBe(['101', '104', '203']);
    }
});

test('un filtro desconocido vuelve al estado todos sin romper la consulta', function () {
    $tipo = ($this->crearTipo)('Estándar', 899.00, 2);
    ($this->crearHabitacion)('101', $tipo);

    $componente = Livewire::actingAs($this->admin)
        ->test(HabitacionesIndex::class)
        ->call('filtrarPor', 'clave-inexistente');

    expect($componente->get('filtroEstado'))->toBe(HabitacionesIndex::FILTRO_TODOS)
        ->and(($this->numerosVisibles)($componente))->toBe(['101']);
});

test('las pills de estado muestran los contadores dinámicos', function () {
    $tipo = ($this->crearTipo)('Estándar', 899.00, 2);
    ($this->crearHabitacion)('101', $tipo, 'Disponible');
    ($this->crearHabitacion)('102', $tipo, 'Disponible');
    ($this->crearHabitacion)('103', $tipo, 'Ocupada');
    ($this->crearHabitacion)('104', $tipo, 'Limpieza');

    $componente = Livewire::actingAs($this->admin)->test(HabitacionesIndex::class);

    $tarjetas = collect($componente->instance()->tarjetasEstado())->keyBy('etiqueta');

    expect($tarjetas)->toHaveCount(6)
        ->and($tarjetas['Total']['conteo'])->toBe(4)
        ->and($tarjetas['Disponibles']['conteo'])->toBe(2)
        ->and($tarjetas['Ocupadas']['conteo'])->toBe(1)
        ->and($tarjetas['Reservadas']['conteo'])->toBe(0)
        ->and($tarjetas['Limpieza']['conteo'])->toBe(1)
        ->and($tarjetas['Mantenimiento']['conteo'])->toBe(0)
        // "Total" aplica el filtro "todos", por eso arranca activo.
        ->and($tarjetas['Total']['activa'])->toBeTrue()
        ->and($tarjetas['Disponibles']['activa'])->toBeFalse()
        ->and($componente->html())->toContain('Mantenimiento');
});

test('la tarjeta KPI activa se marca al filtrar por estado', function () {
    $tipo = ($this->crearTipo)('Estándar', 899.00, 2);
    ($this->crearHabitacion)('103', $tipo, 'Ocupada');

    $componente = Livewire::actingAs($this->admin)
        ->test(HabitacionesIndex::class)
        ->call('filtrarPor', 'Ocupada');

    $tarjetas = collect($componente->instance()->tarjetasEstado())->keyBy('etiqueta');

    expect($tarjetas['Ocupadas']['activa'])->toBeTrue()
        ->and($tarjetas['Disponibles']['activa'])->toBeFalse()
        ->and($tarjetas['Total']['activa'])->toBeFalse();
});

test('se puede cambiar entre la vista cuadrícula y la vista lista', function () {
    $tipo = ($this->crearTipo)('Estándar', 899.00, 2);
    ($this->crearHabitacion)('101', $tipo);

    $cuadricula = Livewire::actingAs($this->admin)->test(HabitacionesIndex::class);

    expect($cuadricula->get('vista'))->toBe(HabitacionesIndex::VISTA_CUADRICULA)
        ->and($cuadricula->html())->toContain('2xl:grid-cols-4');

    $lista = Livewire::actingAs($this->admin)->test(HabitacionesIndex::class, ['vista' => 'lista']);

    expect($lista->get('vista'))->toBe('lista')
        ->and($lista->html())->not->toContain('2xl:grid-cols-4')
        ->and($lista->html())->toContain('#101');

    $cuadricula
        ->call('cambiarVista', 'lista')
        ->assertSet('vista', 'lista')
        ->call('cambiarVista', 'cuadricula')
        ->assertSet('vista', 'cuadricula')
        ->call('cambiarVista', 'desconocida')
        ->assertSet('vista', HabitacionesIndex::VISTA_CUADRICULA);
});

test('el contenedor avisa cuando hay filtros activos y avisa cuando no', function () {
    $tipo = ($this->crearTipo)('Estándar', 899.00, 2);
    ($this->crearHabitacion)('101', $tipo);
    ($this->crearHabitacion)('103', $tipo, 'Ocupada');

    Livewire::actingAs($this->admin)
        ->test(HabitacionesIndex::class)
        ->assertDontSee('de 2')
        ->set('search', '101')
        ->assertSee('de 2')
        ->set('search', '')
        ->call('filtrarPor', 'Ocupada')
        ->assertSee('de 2')
        ->call('filtrarPor', 'todos')
        ->assertDontSee('de 2');
});

test('el modulo esta encarpetado en subcomponentes con su vista parcial de tarjetas', function () {
    $base = resource_path('views/livewire/habitaciones');

    // Un parcial anónimo por archivo, sin componente Livewire propio.
    expect(File::exists($base.'/lista-tarjetas.blade.php'))->toBeTrue()
        ->and(File::get($base.'/lista-tarjetas.blade.php'))
        ->toContain('@foreach ($habitaciones as $habitacion)')
        ->toContain('livewire:habitaciones.habitacion-card')
        ->not->toContain('class ');

    // La vista del contenedor delega la iteración, no la repite.
    expect(File::get($base.'/index.blade.php'))
        ->toContain("@include('livewire.habitaciones.lista-tarjetas'")
        ->not->toContain('@foreach ($this->habitaciones');

    foreach (['index', 'filtros', 'lista-tarjetas', 'form-modal'] as $vista) {
        expect(File::exists($base.'/'.$vista.'.blade.php'))->toBeTrue();
    }

    foreach (['Index', 'Filtros', 'FormModal'] as $componente) {
        expect(File::exists(app_path('Livewire/Habitaciones/'.$componente.'.php')))->toBeTrue();
    }
});

test('el inventario carga el tipo de habitacion con Eager Loading', function () {
    $tipo = ($this->crearTipo)('Estándar', 899.00, 2);
    ($this->crearHabitacion)('101', $tipo);
    ($this->crearHabitacion)('102', $tipo);

    $componente = Livewire::actingAs($this->admin)->test(HabitacionesIndex::class);
    $consultas = [];

    DB::listen(function ($query) use (&$consultas): void {
        $consultas[] = $query->sql;
    });

    $componente->call('$refresh');

    // 7 consultas fijas (paginación + total + KPIs + catálogo) y ninguna por
    // habitación: con N+1 serían 9.
    expect(count($consultas))->toBeLessThanOrEqual(9)
        ->and(collect($consultas)->filter(fn ($sql) => str_contains($sql, 'tipos_habitacion')))->toHaveCount(1);
});

test('el formulario del modal se carga de forma diferida dentro del diálogo', function () {
    ($this->crearTipo)('Estándar', 899.00, 2);

    $html = Livewire::actingAs($this->admin)->test(HabitacionesIndex::class)->html();

    expect($html)->toContain('__lazyLoad')
        ->and($html)->not->toContain('Guardar habitación')
        ->and($html)->toMatch('/<dialog[^>]*>.*__lazyLoad.*<\/dialog>/s');
});

test('el modal del inventario se monta como diálogo transparente con el cristal de la marca', function () {
    $tipo = ($this->crearTipo)('Estándar', 899.00, 2);
    ($this->crearHabitacion)('101', $tipo);

    $html = Livewire::actingAs($this->admin)->test(HabitacionesIndex::class)->html();

    expect($html)->toContain('novastay-habitacion-modal')
        ->and($html)->toMatch('/<dialog[^>]*class="[^"]*bg-transparent[^"]*"/s');
});

test('el cristal de fondo del modal de habitación vive en la hoja de estilos', function () {
    $css = file_get_contents(resource_path('css/app.css'));

    expect($css)->toContain('.novastay-habitacion-modal::backdrop')
        ->toContain('backdrop-filter: blur(4px)')
        ->toContain('rgb(15 23 42 / 0.6)')
        ->toContain('transition: opacity 300ms');
});

test('el modal se presenta como una tarjeta premium con fondo cristal y animaciones', function () {
    Livewire::withoutLazyLoading();

    $html = Livewire::actingAs($this->admin)->test(FormModal::class)->html();

    // Tarjeta blanca con entrada de escala y rebote, bordes y sombra profundos.
    expect($html)->toContain('animate-modal-card-in')
        ->toContain('rounded-2xl')
        ->toContain('shadow-2xl')
        // Desvanecido del cristal de fondo.
        ->toContain('duration-300')
        // Campos sin bordes duros, con fondo sutil y anillo ámbar al enfocar.
        ->toContain('bg-slate-50')
        ->toContain('hover:bg-slate-100')
        ->toContain('focus:ring-2')
        ->toContain('focus:ring-amber-500')
        ->toContain('focus:border-transparent')
        // Iconos dentro de los campos.
        ->toContain('peer-focus:text-amber-500')
        // Zona de fotografía punteada, ámbar al pasar el cursor y rebote del icono.
        ->toContain('border-2')
        ->toContain('border-dashed')
        ->toContain('hover:border-amber-500')
        ->toContain('hover:bg-amber-50')
        ->toContain('group-hover:scale-110')
        ->toContain('animate-float-slow')
        // Botones: elevación al pasar el cursor y presión al hacer clic.
        ->toContain('hover:-translate-y-0.5')
        ->toContain('hover:shadow-lg')
        ->toContain('active:scale-95')
        ->toContain('hover:bg-slate-100')
        // Subida de archivo nativa, sin URL externa.
        ->toContain('type="file"')
        ->toContain('wire:model="foto"')
        ->not->toContain('wire:model="foto_url"')
        ->not->toContain('type="url"');
});

test('el formulario no pide al servidor con cada tecla y bloquea el doble clic', function () {
    Livewire::withoutLazyLoading();

    $html = Livewire::actingAs($this->admin)->test(FormModal::class)->html();

    // Los campos de texto van diferidos: sin .live ni .blur, no hay peticiones
    // por pulsación de tecla.
    foreach (['numero_habitacion', 'descripcion'] as $campo) {
        expect($html)->toContain('wire:model="'.$campo.'"');
    }

    expect($html)->not->toContain('wire:model.live=')
        ->and($html)->not->toContain('wire:model.blur=')
        // Doble clic bloqueado mientras el servidor procesa el guardado.
        ->toContain('wire:loading.attr="disabled"')
        ->toContain('wire:target="guardar"')
        ->toContain('Guardando...');
});

test('los labels de campo obligatorio llevan el asterisco rojo', function () {
    Livewire::withoutLazyLoading();

    $html = Livewire::actingAs($this->admin)->test(FormModal::class)->html();

    $asterisco = '<span class="text-red-500 font-extrabold text-sm ml-0.5">*</span>';

    expect(substr_count($html, $asterisco))->toBe(4)
        ->and($html)->toContain('Número '.$asterisco)
        ->and($html)->toContain('Tipo '.$asterisco)
        ->and($html)->toContain('Piso '.$asterisco)
        ->and($html)->toContain('Estado inicial '.$asterisco)
        // Sin asterisco: siguen siendo opcionales.
        ->and($html)->toContain('>Descripción<')
        ->and($html)->toContain('>Fotografía<');
});

test('el formulario no pide capacidad ni precio porque se heredan del tipo de habitación', function () {
    Livewire::withoutLazyLoading();

    $html = Livewire::actingAs($this->admin)->test(FormModal::class)->html();

    expect($html)->not->toContain('wire:model="capacidad"')
        ->and($html)->not->toContain('wire:model="precio_por_noche"')
        ->and($html)->not->toContain('for="capacidad"')
        ->and($html)->not->toContain('for="precio_por_noche"')
        // La rejilla queda en dos columnas y la descripción las ocupa enteras.
        ->and($html)->toContain('grid gap-5 sm:grid-cols-2')
        ->and($html)->toContain('sm:col-span-2');
});

test('las tarjetas KPI usan la rejilla y el contrato visual acordado', function () {
    $tipo = ($this->crearTipo)('Estándar', 899.00, 2);
    ($this->crearHabitacion)('101', $tipo);

    $html = Livewire::actingAs($this->admin)
        ->test(Filtros::class, [
            'totalHabitaciones' => 1,
            'tarjetas' => app(HabitacionesIndex::class)->tarjetasEstado(),
        ])
        ->html();

    // Rejilla de 6 columnas.
    expect($html)->toContain('grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4 mb-8')
        // Estructura interna de la tarjeta.
        ->toContain('flex justify-between items-start')
        ->toContain('text-[10px] font-bold text-slate-400 uppercase tracking-wider')
        ->toContain('w-8 h-8 rounded-full flex items-center justify-center')
        ->toContain('text-4xl font-black text-slate-800 mt-2')
        // Interacción: elevación y presión.
        ->toContain('hover:shadow-md')
        ->toContain('hover:-translate-y-1')
        ->toContain('active:scale-95')
        ->toContain('rounded-2xl')
        ->toContain('border-t-4')
        // Paleta por estado.
        ->toContain('border-t-emerald-500')
        ->toContain('bg-emerald-50')
        ->toContain('text-emerald-500')
        ->toContain('border-t-rose-500')
        ->toContain('bg-rose-50')
        ->toContain('text-rose-500')
        ->toContain('border-t-amber-400')
        ->toContain('bg-amber-50')
        ->toContain('text-amber-500')
        ->toContain('border-t-orange-400')
        ->toContain('bg-orange-50')
        ->toContain('text-orange-500')
        ->toContain('border-t-slate-500')
        ->toContain('bg-slate-100')
        ->toContain('text-slate-500')
        // El diseño de píldoras desaparece por completo.
        ->not->toContain('rounded-full px-5 py-2.5');
});

test('la barra de filtros delega el estado global en el contenedor', function () {
    $tipo = ($this->crearTipo)('Estándar', 899.00, 2);
    ($this->crearHabitacion)('101', $tipo);

    $componente = Livewire::actingAs($this->admin)
        ->test(HabitacionesIndex::class, ['search' => '', 'filtroEstado' => 'Ocupada']);

    expect($componente->html())->toContain('$parent.cambiarVista')
        ->and($componente->html())->toContain('$parent.filtrarPor')
        // Agregar ya no delega en el contenedor: lo resuelve Alpine en el
        // navegador para que el morph no pueda cerrar el modal recién abierto.
        ->and($componente->html())->toContain("\$dispatch('abrir-formulario', { id: null })")
        ->and($componente->html())->not->toContain('$parent.crear')
        ->and($componente->html())->toContain('wire:model.live.debounce.400ms="search"');
});

test('el componente de filtros expone la búsqueda enlazada al contenedor', function () {
    $componente = Livewire::actingAs($this->admin)
        ->test(Filtros::class, ['search' => '101', 'totalHabitaciones' => 3]);

    expect($componente->get('search'))->toBe('101')
        ->and($componente->get('totalHabitaciones'))->toBe(3)
        ->and($componente->html())->toContain('wire:model.live.debounce.400ms="search"');
});

test('la tarjeta muestra los datos de la habitación y sus acciones', function () {
    $tipo = ($this->crearTipo)('Estándar', 899.00, 2, 'Habitación clásica con vistas.');
    $habitacion = ($this->crearHabitacion)('101', $tipo, 'Ocupada', 3);

    Livewire::actingAs($this->admin)
        ->test(HabitacionCard::class, ['habitacion' => $habitacion, 'variante' => 'cuadricula'])
        ->assertSee('#101')
        ->assertSee('Estándar')
        ->assertSee('Piso 3')
        ->assertSee('2 personas')
        ->assertSee('$899')
        ->assertSee('Habitación clásica con vistas.')
        ->assertSee('Ocupada')
        ->assertSee('En uso')
        // Editar abre el modal desde Alpine; eliminar sí necesita al contenedor.
        ->assertSeeHtml("\$dispatch('modal-show', { name: 'habitacion-form' }); \$dispatch('abrir-formulario', { id: ".$habitacion->id.' })')
        ->assertDontSeeHtml('wire:click="$parent.editar(')
        ->assertSeeHtml('$parent.eliminar('.$habitacion->id.')');
});

test('la tarjeta traduce el estado de limpieza y el estado mostrado', function () {
    $tipo = ($this->crearTipo)('Estándar', 899.00, 2);

    Livewire::actingAs($this->admin)
        ->test(HabitacionCard::class, ['habitacion' => ($this->crearHabitacion)('101', $tipo, 'Limpieza')])
        ->assertSee('Requiere Limpieza');

    Livewire::actingAs($this->admin)
        ->test(HabitacionCard::class, ['habitacion' => ($this->crearHabitacion)('202', $tipo, 'Mantenimiento')])
        ->assertSee('Fuera de Servicio');

    Livewire::actingAs($this->admin)
        ->test(HabitacionCard::class, ['habitacion' => ($this->crearHabitacion)('303', $tipo)])
        ->assertSee('Limpia');
});

test('la tarjeta cambia de maquetación según la variante', function () {
    $tipo = ($this->crearTipo)('Estándar', 899.00, 2, 'Habitación clásica con vistas.');
    $habitacion = ($this->crearHabitacion)('101', $tipo);

    Livewire::actingAs($this->admin)
        ->test(HabitacionCard::class, ['habitacion' => $habitacion, 'variante' => 'cuadricula'])
        ->assertSee('Habitación clásica con vistas.');

    Livewire::actingAs($this->admin)
        ->test(HabitacionCard::class, ['habitacion' => $habitacion, 'variante' => 'lista'])
        ->assertDontSee('Habitación clásica con vistas.')
        ->assertSee('#101')
        ->assertSee('$899');
});

test('el contenedor no guarda estado de visibilidad ni prepara el formulario', function () {
    // Ninguna propiedad booleana controla la visibilidad: el <dialog> lo abre
    // Flux desde el navegador con el evento `modal-show`.
    $propiedades = (new ReflectionClass(HabitacionesIndex::class))->getProperties();

    expect(array_map(fn ($p) => $p->getName(), $propiedades))
        ->not->toContain('mostrarModal')
        ->not->toContain('habitacionId')
        ->and(array_filter($propiedades, fn ($p) => $p->getType()?->getName() === 'bool'))->toBeEmpty();

    // Abrir el modal no pasa por el contenedor: si lo hiciera, su morph
    // recrearía el <dialog> y el modal parpadearía.
    expect(HabitacionesIndex::class)->not->toHaveMethods(['crear', 'editar']);
});

test('el modal se abre solo con Alpine y su raíz queda fuera del diffing', function () {
    Livewire::withoutLazyLoading();

    $html = Livewire::actingAs($this->admin)->test(HabitacionesIndex::class)->html();

    // Apertura en el navegador, sin ninguna ida al servidor.
    expect($html)->toContain("\$dispatch('modal-show', { name: 'habitacion-form' })")
        ->and($html)->toContain("\$dispatch('abrir-formulario', { id: null })")
        ->and($html)->not->toContain('wire:click="$parent.crear"')
        ->and($html)->not->toContain('wire:click="$parent.editar(')
        ->and($html)->not->toContain('wire:model="mostrarModal"')
        // `wire:ignore` completo: `.self` no protegería <ui-modal>, que es el
        // padre del <dialog> y el que realmente se recrea en el morph.
        ->toContain('<div wire:ignore>');
});

test('cada tarjeta abre el modal con su propio identificador', function () {
    $tipo = ($this->crearTipo)('Estándar', 899.00, 2);
    $habitacion = ($this->crearHabitacion)('101', $tipo);

    $html = Livewire::actingAs($this->admin)->test(HabitacionesIndex::class)->html();

    expect($html)
        ->toContain("\$dispatch('modal-show', { name: 'habitacion-form' }); \$dispatch('abrir-formulario', { id: ".$habitacion->id.' })')
        ->toContain('x-on:click="$dispatch(\'modal-show\'');
});

test('el formulario cierra el dialog en el navegador sin pedir permiso al servidor', function () {
    Livewire::withoutLazyLoading();

    Livewire::actingAs($this->admin)
        ->test(FormModal::class)
        ->assertSeeHtml("\$dispatch('modal-close', { name: 'habitacion-form' })")
        // Cancelar y la X no emiten peticiones: solo despachan el evento.
        ->assertDontSeeHtml('wire:click="cerrar"');
});

test('el contenedor cierra el modal y muestra el mensaje tras guardar', function () {
    Livewire::actingAs($this->admin)
        ->test(HabitacionesIndex::class)
        ->dispatch('habitacion-guardada', mensaje: 'Habitación creada correctamente.')
        ->assertDispatched('modal-close', name: 'habitacion-form')
        ->assertSet('mensajeExito', 'Habitación creada correctamente.');
});

test('el mensaje de éxito se retira solo a los tres segundos', function () {
    $componente = Livewire::actingAs($this->admin)
        ->test(HabitacionesIndex::class)
        ->dispatch('habitacion-guardada', mensaje: 'Habitación creada correctamente.');

    // El temporizador vive en la vista y avisa al contenedor: el servidor solo
    // obedece la señal.
    expect($componente->html())
        ->toContain('x-init="setTimeout(() => $dispatch(\'mensaje-exito-oculto\'), 3000)"')
        ->toContain('wire:key="mensaje-exito-1"');

    $componente
        ->dispatch('mensaje-exito-oculto')
        ->assertSet('mensajeExito', null)
        ->assertDontSee('Habitación creada correctamente.');
});

test('dos avisos seguidos reinician el temporizador aunque repitan el texto', function () {
    $tipo = ($this->crearTipo)('Estándar', 899.00, 2);
    $primera = ($this->crearHabitacion)('101', $tipo);
    $segunda = ($this->crearHabitacion)('202', $tipo);

    $componente = Livewire::actingAs($this->admin)
        ->test(HabitacionesIndex::class)
        ->call('eliminar', $primera->id);

    expect($componente->html())->toContain('wire:key="mensaje-exito-1"');

    $componente->call('eliminar', $segunda->id);

    // Mismo texto, clave distinta: Alpine vuelve a armar el temporizador en vez
    // de heredar el del aviso anterior.
    expect($componente->html())->toContain('wire:key="mensaje-exito-2"');
});

test('el contenedor elimina una habitación del inventario', function () {
    $tipo = ($this->crearTipo)('Estándar', 899.00, 2);
    $habitacion = ($this->crearHabitacion)('101', $tipo);

    Livewire::actingAs($this->admin)
        ->test(HabitacionesIndex::class)
        ->call('eliminar', $habitacion->id)
        ->assertSet('mensajeExito', 'Habitación eliminada correctamente.')
        ->assertDontSee('#101');

    expect(Habitacion::whereKey($habitacion->id)->exists())->toBeFalse();
});

test('el formulario del modal carga los datos de la habitación en edición', function () {
    Livewire::withoutLazyLoading();

    $tipo = ($this->crearTipo)('Estándar', 899.00, 2, 'Habitación clásica con vistas.');
    $habitacion = ($this->crearHabitacion)('101', $tipo, 'Ocupada', 3);

    Livewire::actingAs($this->admin)
        ->test(FormModal::class, ['habitacionId' => $habitacion->id])
        ->assertSet('numero_habitacion', '101')
        ->assertSet('tipo_habitacion_id', (string) $tipo->id)
        ->assertSet('estado', 'Ocupada')
        ->assertSet('piso', '3')
        ->assertSet('descripcion', 'Habitación clásica con vistas.')
        ->assertSee('Editar habitación')
        ->assertSee('Estándar — $899.00');
});

test('el formulario deriva la descripción del tipo seleccionado sin pedir capacidad ni precio', function () {
    Livewire::withoutLazyLoading();

    $tipo = ($this->crearTipo)('Estándar', 899.00, 2, 'Habitación clásica con vistas.');

    $componente = Livewire::actingAs($this->admin)
        ->test(FormModal::class)
        ->assertSee('Agregar habitación')
        ->set('tipo_habitacion_id', (string) $tipo->id);

    $componente
        ->assertSet('descripcion', 'Habitación clásica con vistas.')
        ->assertSet('estado', 'Disponible')
        ->assertSet('piso', '1')
        ->assertSet('foto', null)
        ->assertSet('fotoGuardada', null)
        ->assertNotSet('capacidad', '2')
        ->assertNotSet('precio_por_noche', '899.00');
});

test('el formulario convierte la fotografía subida a WebP y guarda solo su ruta', function () {
    ($this->requiereGD)();

    Livewire::withoutLazyLoading();
    Storage::fake('public');

    $tipo = ($this->crearTipo)('Estándar', 899.00, 2);

    Livewire::actingAs($this->admin)
        ->test(FormModal::class)
        ->set('numero_habitacion', '701')
        ->set('tipo_habitacion_id', (string) $tipo->id)
        ->set('piso', '7')
        ->set('foto', UploadedFile::fake()->image('fachada.jpg', 120, 90))
        ->call('guardar')
        ->assertHasNoErrors()
        ->assertDispatched('habitacion-guardada', mensaje: 'Habitación creada correctamente.');

    $habitacion = Habitacion::where('numero_habitacion', '701')->firstOrFail();

    expect($habitacion->foto)->toStartWith('habitaciones/')
        ->and($habitacion->foto)->toEndWith('.webp')
        ->and($habitacion->foto)->not->toContain('/storage/')
        ->and(Storage::disk('public')->exists($habitacion->foto))->toBeTrue();

    $contenido = Storage::disk('public')->get($habitacion->foto);

    expect(substr($contenido, 0, 4))->toBe('RIFF')
        ->and(substr($contenido, 8, 4))->toBe('WEBP')
        ->and($habitacion->imagen())->toBe(Storage::disk('public')->url($habitacion->foto));
});

test('el formulario convierte a WebP también las fotos que ya estaban en PNG', function () {
    ($this->requiereGD)();

    Livewire::withoutLazyLoading();
    Storage::fake('public');

    $tipo = ($this->crearTipo)('Estándar', 899.00, 2);
    $habitacion = ($this->crearHabitacion)('101', $tipo);

    Livewire::actingAs($this->admin)
        ->test(FormModal::class, ['habitacionId' => $habitacion->id])
        ->set('foto', UploadedFile::fake()->image('panorama.png', 200, 150))
        ->call('guardar')
        ->assertHasNoErrors();

    expect($habitacion->fresh()->foto)->toEndWith('.webp')
        ->and(Storage::disk('public')->exists($habitacion->fresh()->foto))->toBeTrue();
});

test('el formulario borra del disco la fotografía que fue reemplazada', function () {
    ($this->requiereGD)();

    Livewire::withoutLazyLoading();
    Storage::fake('public');

    $tipo = ($this->crearTipo)('Estándar', 899.00, 2);
    $habitacion = ($this->crearHabitacion)('101', $tipo);

    $rutaAnterior = 'habitaciones/101-anterior.webp';
    Storage::disk('public')->put($rutaAnterior, 'fotografia previa');
    $habitacion->forceFill(['foto' => $rutaAnterior])->save();

    Livewire::actingAs($this->admin)
        ->test(FormModal::class, ['habitacionId' => $habitacion->id])
        ->assertSet('fotoGuardada', $rutaAnterior)
        ->set('foto', UploadedFile::fake()->image('nueva.jpg'))
        ->call('guardar')
        ->assertHasNoErrors();

    $rutaNueva = $habitacion->fresh()->foto;

    expect($rutaNueva)->not->toBe($rutaAnterior)
        ->and($rutaNueva)->toEndWith('.webp')
        ->and(Storage::disk('public')->exists($rutaAnterior))->toBeFalse()
        ->and(Storage::disk('public')->exists($rutaNueva))->toBeTrue();
});

test('el formulario conserva la fotografía guardada cuando se guardan otros cambios', function () {
    ($this->requiereGD)();

    Livewire::withoutLazyLoading();
    Storage::fake('public');

    $tipo = ($this->crearTipo)('Estándar', 899.00, 2);
    $habitacion = ($this->crearHabitacion)('101', $tipo);

    $ruta = 'habitaciones/101-original.webp';
    Storage::disk('public')->put($ruta, 'fotografia original');
    $habitacion->forceFill(['foto' => $ruta])->save();

    Livewire::actingAs($this->admin)
        ->test(FormModal::class, ['habitacionId' => $habitacion->id])
        ->set('piso', '4')
        ->call('guardar')
        ->assertHasNoErrors();

    expect($habitacion->fresh()->piso)->toBe(4)
        ->and($habitacion->fresh()->foto)->toBe($ruta)
        ->and(Storage::disk('public')->exists($ruta))->toBeTrue();
});

test('el formulario no toca el disco cuando se guarda sin subir fotografía', function () {
    ($this->requiereGD)();

    Livewire::withoutLazyLoading();
    Storage::fake('public');

    $tipo = ($this->crearTipo)('Estándar', 899.00, 2);

    Livewire::actingAs($this->admin)
        ->test(FormModal::class)
        ->set('numero_habitacion', '801')
        ->set('tipo_habitacion_id', (string) $tipo->id)
        ->call('guardar')
        ->assertHasNoErrors();

    $habitacion = Habitacion::where('numero_habitacion', '801')->firstOrFail();

    expect($habitacion->foto)->toBeNull()
        ->and(Storage::disk('public')->allFiles(Habitacion::CARPETA_FOTOS))->toBe([])
        ->and($habitacion->imagen())->toBeIn(Habitacion::IMAGENES);
});

test('el formulario rechaza archivos que no son imágenes', function () {
    Livewire::withoutLazyLoading();
    Storage::fake('public');

    $tipo = ($this->crearTipo)('Estándar', 899.00, 2);

    Livewire::actingAs($this->admin)
        ->test(FormModal::class)
        ->set('numero_habitacion', '901')
        ->set('tipo_habitacion_id', (string) $tipo->id)
        ->set('foto', UploadedFile::fake()->create('contrato.pdf', 20, 'application/pdf'))
        ->call('guardar')
        ->assertHasErrors(['foto']);

    expect(Habitacion::where('numero_habitacion', '901')->exists())->toBeFalse();
});

test('el formulario muestra la fotografía guardada y la previsualiza al elegir una nueva', function () {
    Livewire::withoutLazyLoading();
    Storage::fake('public');

    $tipo = ($this->crearTipo)('Estándar', 899.00, 2);
    $habitacion = ($this->crearHabitacion)('101', $tipo);
    $habitacion->forceFill(['foto' => 'habitaciones/101-fija.webp'])->save();
    Storage::disk('public')->put('habitaciones/101-fija.webp', 'contenido');

    Livewire::actingAs($this->admin)
        ->test(FormModal::class, ['habitacionId' => $habitacion->id])
        ->assertSet('fotoGuardada', 'habitaciones/101-fija.webp')
        ->assertSet('foto', null)
        ->assertSee('101-fija.webp')
        // Velo cristalino que aparece al pasar el cursor sobre la fotografía.
        ->assertSeeHtml('backdrop-blur-[2px]')
        ->assertSee('Reemplazar fotografía');

    $componente = Livewire::actingAs($this->admin)
        ->test(FormModal::class, ['habitacionId' => $habitacion->id])
        ->set('foto', UploadedFile::fake()->create('nueva.jpg', 10, 'image/jpeg'));

    expect($componente->get('foto'))->toBeInstanceOf(TemporaryUploadedFile::class)
        ->and($componente->get('foto')->temporaryUrl())->toContain('preview-file/')
        ->and($componente->get('foto')->temporaryUrl())->toContain('signature=')
        ->and($componente->html())->toContain('preview-file/')
        ->and($componente->html())->toContain('se guardará como WebP');
});

test('el formulario crea una habitación y avisa al contenedor', function () {
    Livewire::withoutLazyLoading();

    $tipo = ($this->crearTipo)('Estándar', 899.00, 2);

    Livewire::actingAs($this->admin)
        ->test(FormModal::class)
        ->set('numero_habitacion', '501')
        ->set('tipo_habitacion_id', (string) $tipo->id)
        ->set('piso', '5')
        ->set('estado', 'Disponible')
        ->call('guardar')
        ->assertHasNoErrors()
        ->assertDispatched('habitacion-guardada', mensaje: 'Habitación creada correctamente.');

    $habitacion = Habitacion::where('numero_habitacion', '501')->first();

    expect($habitacion)->not->toBeNull()
        ->and($habitacion->tipo_habitacion_id)->toBe($tipo->id)
        ->and($habitacion->piso)->toBe(5)
        ->and($habitacion->estado)->toBe('Disponible');
});

test('el formulario actualiza la habitación en edición y avisa al contenedor', function () {
    Livewire::withoutLazyLoading();

    $tipo = ($this->crearTipo)('Estándar', 899.00, 2);
    $habitacion = ($this->crearHabitacion)('101', $tipo, 'Ocupada', 1);

    Livewire::actingAs($this->admin)
        ->test(FormModal::class, ['habitacionId' => $habitacion->id])
        ->set('piso', '2')
        ->set('estado', 'Disponible')
        ->call('guardar')
        ->assertHasNoErrors()
        ->assertDispatched('habitacion-guardada', mensaje: 'Habitación actualizada correctamente.');

    expect($habitacion->fresh()->piso)->toBe(2)
        ->and($habitacion->fresh()->estado)->toBe('Disponible');
});

test('el formulario rechaza estados que no pertenecen al enumerado de la base de datos', function () {
    Livewire::withoutLazyLoading();

    $tipo = ($this->crearTipo)('Estándar', 899.00, 2);

    Livewire::actingAs($this->admin)
        ->test(FormModal::class)
        ->set('numero_habitacion', '601')
        ->set('tipo_habitacion_id', (string) $tipo->id)
        ->set('piso', '6')
        ->set('estado', 'Reservada')
        ->call('guardar')
        ->assertHasErrors(['estado']);

    expect(Habitacion::where('numero_habitacion', '601')->exists())->toBeFalse();
});

test('el formulario rechaza un número de habitación duplicado', function () {
    Livewire::withoutLazyLoading();

    $tipo = ($this->crearTipo)('Estándar', 899.00, 2);
    ($this->crearHabitacion)('101', $tipo);

    Livewire::actingAs($this->admin)
        ->test(FormModal::class)
        ->set('numero_habitacion', '101')
        ->set('tipo_habitacion_id', (string) $tipo->id)
        ->set('piso', '1')
        ->call('guardar')
        ->assertHasErrors(['numero_habitacion']);
});

test('el formulario se vacía al recibir abrir-formulario en modo creación', function () {
    Livewire::withoutLazyLoading();

    $tipo = ($this->crearTipo)('Estándar', 899.00, 2);
    $habitacion = ($this->crearHabitacion)('101', $tipo);

    $componente = Livewire::actingAs($this->admin)
        ->test(FormModal::class, ['habitacionId' => $habitacion->id]);

    expect($componente->get('numero_habitacion'))->toBe('101');

    // Es el mismo camino que sigue el botón "Agregar habitación".
    $componente->set('numero_habitacion', '999')->dispatch('abrir-formulario', id: null);

    expect($componente->get('habitacionId'))->toBeNull()
        ->and($componente->get('numero_habitacion'))->toBe('')
        ->and($componente->get('tipo_habitacion_id'))->toBe('')
        ->and($componente->get('estado'))->toBe('Disponible');
});

test('el formulario se recarga con los datos al recibir abrir-formulario en modo edición', function () {
    Livewire::withoutLazyLoading();

    $tipo = ($this->crearTipo)('Estándar', 899.00, 2);
    $otra = ($this->crearHabitacion)('202', $tipo, 'Ocupada', 4);

    $componente = Livewire::actingAs($this->admin)->test(FormModal::class);
    $componente->dispatch('abrir-formulario', id: $otra->id);

    expect($componente->get('habitacionId'))->toBe($otra->id)
        ->and($componente->get('numero_habitacion'))->toBe('202')
        ->and($componente->get('estado'))->toBe('Ocupada')
        ->and($componente->get('piso'))->toBe('4');
});

test('la imagen de muestra es determinista y pertenece al catálogo', function () {
    expect(Habitacion::imagenPredeterminada('101'))->toBe(Habitacion::imagenPredeterminada('101'))
        ->and(Habitacion::imagenPredeterminada('101'))->toBeIn(Habitacion::IMAGENES)
        ->and(Habitacion::make(['numero_habitacion' => '101'])->imagen())->toBe(Habitacion::imagenPredeterminada('101'));
});
