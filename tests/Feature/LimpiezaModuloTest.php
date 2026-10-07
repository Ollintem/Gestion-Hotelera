<?php

use App\Livewire\Limpieza;
use App\Models\Habitacion;
use App\Models\Limpieza as TareaLimpieza;
use App\Models\TipoHabitacion;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Livewire\Livewire;

beforeEach(function () {
    $this->seed(RolesAndPermissionsSeeder::class);

    $this->admin = User::factory()->create();
    $this->admin->assignRole('super-admin');
});

test('el panel de housekeeping muestra los KPIs y las tarjetas por estado', function () {
    $tipo = TipoHabitacion::create([
        'nombre' => 'Estándar',
        'precio_base' => 899.00,
        'capacidad' => 2,
        'descripcion' => 'Habitación estándar.',
    ]);

    $pendiente = Habitacion::create([
        'numero_habitacion' => '102',
        'tipo_habitacion_id' => $tipo->id,
        'estado' => 'Disponible',
        'piso' => 1,
    ]);

    $enProceso = Habitacion::create([
        'numero_habitacion' => '203',
        'tipo_habitacion_id' => $tipo->id,
        'estado' => 'Limpieza',
        'piso' => 2,
    ]);

    $lista = Habitacion::create([
        'numero_habitacion' => '304',
        'tipo_habitacion_id' => $tipo->id,
        'estado' => 'Disponible',
        'piso' => 3,
    ]);

    TareaLimpieza::create([
        'habitacion_id' => $pendiente->id,
        'user_id' => $this->admin->id,
        'estado' => 'Pendiente',
        'notas' => 'Salida tardía, revisar minibar.',
    ]);
    TareaLimpieza::create([
        'habitacion_id' => $enProceso->id,
        'user_id' => $this->admin->id,
        'estado' => 'En Proceso',
    ]);
    TareaLimpieza::create([
        'habitacion_id' => $lista->id,
        'user_id' => $this->admin->id,
        'estado' => 'Completado',
    ]);

    $componente = Livewire::actingAs($this->admin)->test(Limpieza::class);

    $componente
        ->assertSee('Requiere limpieza')
        ->assertSee('En limpieza')
        ->assertSee('Listas')
        ->assertSee('#102')
        ->assertSee('#203')
        ->assertSee('#304')
        ->assertSee('Estándar')
        ->assertSee('Piso 1')
        ->assertSee('Iniciar limpieza')
        ->assertSee('Marcar como lista')
        ->assertSee('Ver detalle')
        ->assertSee('Salida tardía, revisar minibar.');

    $html = $componente->html();

    expect($html)
        ->toContain('grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-3')
        ->toContain('border-rose-200 bg-rose-50')
        ->toContain('border-sky-200 bg-sky-50')
        ->toContain('border-emerald-200 bg-emerald-50')
        ->toContain('border-rose-200 bg-rose-100 text-rose-700')
        ->toContain('border-sky-200 bg-sky-100 text-sky-700')
        ->toContain('border-emerald-200 bg-emerald-100 text-emerald-700')
        ->toContain('bg-slate-800 text-white hover:bg-slate-700')
        ->toContain('bg-amber-500 text-white hover:bg-amber-600')
        ->toMatch('/leading-none text-rose-700 dark:text-rose-400">1</')
        ->toMatch('/leading-none text-sky-700 dark:text-sky-400">1</')
        ->toMatch('/leading-none text-emerald-700 dark:text-emerald-400">1</');
});

test('el panel muestra el estado vacío cuando no hay tareas de limpieza', function () {
    $componente = Livewire::actingAs($this->admin)->test(Limpieza::class);

    $componente
        ->assertSee('No hay tareas de limpieza registradas.')
        ->assertSee('Nueva tarea')
        ->assertDontSee('Iniciar limpieza')
        ->assertDontSee('#102');

    expect($componente->html())
        ->toContain('flex min-h-64 flex-col items-center justify-center')
        ->toContain('rounded-2xl border border-slate-100 bg-slate-50')
        ->not->toContain('border-dashed');
});

test('el formulario de nueva tarea enlaza los tres desplegables con modelo y clave', function () {
    $html = Livewire::actingAs($this->admin)
        ->test(Limpieza::class)
        ->call('crear')
        ->html();

    expect($html)
        // El <select> oculto conserva el enlace con `wire:model`.
        ->toContain('wire:model="habitacion_id"')
        ->toContain('wire:model="user_id"')
        ->toContain('wire:model="estado"')
        // `modelo` hace que Alpine empuje el valor con `$wire.set` en el mismo
        // clic, antes de que `wire:submit` pueda enviar la propiedad vacía.
        ->toContain("modelo: 'habitacion_id'")
        ->toContain("modelo: 'user_id'")
        ->toContain("modelo: 'estado'")
        ->toContain('this.$wire.set(this.modelo, value)')
        // La clave ata cada control a un `wire:key` dependiente del valor para
        // que Morphdom reconstruya el desplegable al actualizarse.
        ->toContain('wire:key="habitacion_id-"')
        ->toContain('wire:key="user_id-')
        ->toContain('wire:key="estado-Pendiente"')
        ->toContain('aria-required="true"');
});

test('el formulario de nueva tarea guarda cuando los tres valores llegan enlazados', function () {
    $tipo = TipoHabitacion::create([
        'nombre' => 'Estándar',
        'precio_base' => 899.00,
        'capacidad' => 2,
        'descripcion' => 'Habitación estándar.',
    ]);

    $habitacion = Habitacion::create([
        'numero_habitacion' => '102',
        'tipo_habitacion_id' => $tipo->id,
        'estado' => 'Disponible',
        'piso' => 1,
    ]);

    Livewire::actingAs($this->admin)
        ->test(Limpieza::class)
        ->call('crear')
        ->set('habitacion_id', (string) $habitacion->id)
        ->set('user_id', (string) $this->admin->id)
        ->set('estado', 'En Proceso')
        ->call('guardar')
        ->assertHasNoErrors();

    expect(TareaLimpieza::count())->toBe(1)
        ->and(TareaLimpieza::first()->estado)->toBe('En Proceso');
});

test('el panel muestra un toast flotante de éxito en lugar del callout antiguo', function () {
    $tipo = TipoHabitacion::create([
        'nombre' => 'Estándar',
        'precio_base' => 899.00,
        'capacidad' => 2,
        'descripcion' => 'Habitación estándar.',
    ]);

    $habitacion = Habitacion::create([
        'numero_habitacion' => '102',
        'tipo_habitacion_id' => $tipo->id,
        'estado' => 'Disponible',
        'piso' => 1,
    ]);

    $componente = Livewire::actingAs($this->admin)->test(Limpieza::class);

    $componente
        ->call('crear')
        ->set('habitacion_id', (string) $habitacion->id)
        ->set('user_id', (string) $this->admin->id)
        ->set('estado', 'En Proceso')
        ->call('guardar');

    $html = $componente->html();

    expect($html)
        ->toContain('Tarea de limpieza creada correctamente.')
        // Posicionamiento y convención del toast del proyecto.
        ->toContain('fixed right-4 top-4 z-[100]')
        ->toContain('aria-live="polite"')
        // Reacciona a `$mensajeExito` vía `@entangle` (sin eventos del backend).
        ->toContain("entangle('mensajeExito')")
        ->toContain('setTimeout(() => this.visible = false, 3000)')
        // El callout estático desapareció.
        ->not->toContain('flux:callout');
});

test('el botón de eliminar abre el diálogo Alpine y solo confirma llama a eliminar', function () {
    $tipo = TipoHabitacion::create([
        'nombre' => 'Estándar',
        'precio_base' => 899.00,
        'capacidad' => 2,
        'descripcion' => 'Habitación estándar.',
    ]);

    $habitacion = Habitacion::create([
        'numero_habitacion' => '102',
        'tipo_habitacion_id' => $tipo->id,
        'estado' => 'Limpieza',
        'piso' => 1,
    ]);

    $tarea = TareaLimpieza::create([
        'habitacion_id' => $habitacion->id,
        'user_id' => $this->admin->id,
        'estado' => 'Pendiente',
    ]);

    $componente = Livewire::actingAs($this->admin)->test(Limpieza::class);

    $html = $componente->html();

    expect($html)
        // La alerta nativa del navegador quedó fuera del botón.
        ->not->toContain('wire:confirm')
        // El clic solo publica el evento; el diálogo confirma la llamada.
        ->toContain("x-on:click=\"\$dispatch('eliminar-tarea'")
        ->toContain('¿Estás seguro de eliminar esta tarea')
        ->toContain('confirmarEliminacion()')
        ->toContain('$wire.eliminar(this.pendiente.id)')
        ->toContain('x-on:click="confirmarEliminacion()"');

    // El flujo de backend sigue intacto al confirmar (borrado real).
    $componente->call('eliminar', $tarea->id);

    expect(TareaLimpieza::find($tarea->id))->toBeNull()
        ->and($componente->get('mensajeExito'))->toBe('Tarea de limpieza eliminada correctamente.');
});
