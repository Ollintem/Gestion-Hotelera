<?php

use App\Livewire\Empleados;
use App\Models\Empleado;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Livewire\Livewire;

beforeEach(function () {
    $this->seed(RolesAndPermissionsSeeder::class);

    $this->admin = User::factory()->create();
    $this->admin->assignRole('super-admin');
});

test('el puesto laboral y el rol de acceso se guardan correctamente al crear', function () {
    Livewire::actingAs($this->admin)
        ->test(Empleados::class)
        ->call('crear')
        ->set('nombre', 'Jorge')
        ->set('apellidos', 'Ramos')
        ->set('correo_electronico', 'jorge.ramos@example.com')
        ->set('puesto', 'Recepción')
        ->set('contrasena', 'secret123')
        ->set('rol', 'recepcionista')
        ->set('acceso_sistema', true)
        ->call('guardar')
        ->assertHasNoErrors();

    $empleado = Empleado::where('correo_electronico', 'jorge.ramos@example.com')->firstOrFail();

    expect($empleado->puesto)->toBe('Recepción')
        ->and($empleado->usuario)->not->toBeNull()
        ->and($empleado->usuario->hasRole('recepcionista'))->toBeTrue();
});

test('el rol de acceso se actualiza al editar un empleado', function () {
    $usuario = User::factory()->create(['email' => 'mario@example.com']);
    $usuario->assignRole('recepcionista');

    $empleado = Empleado::create([
        'nombre' => 'Mario',
        'apellidos' => 'López',
        'correo_electronico' => 'mario@example.com',
        'id_usuario' => $usuario->id,
        'puesto' => 'Recepción',
    ]);

    Livewire::actingAs($this->admin)
        ->test(Empleados::class)
        ->call('editar', $empleado->id_empleado)
        ->assertSet('puesto', 'Recepción')
        ->set('puesto', 'Gerencia')
        ->set('rol', 'gerente')
        ->call('guardar')
        ->assertHasNoErrors();

    expect($empleado->fresh()->puesto)->toBe('Gerencia')
        ->and($usuario->fresh()->hasRole('gerente'))->toBeTrue();
});

test('un empleado sin acceso se registra y se edita sin crear credenciales', function () {
    Livewire::actingAs($this->admin)
        ->test(Empleados::class)
        ->call('crear')
        ->set('nombre', 'Laura')
        ->set('apellidos', 'Ruiz')
        ->set('correo_electronico', 'laura@example.com')
        ->set('puesto', 'Camarista')
        ->set('acceso_sistema', false)
        ->call('guardar')
        ->assertHasNoErrors();

    $empleado = Empleado::where('correo_electronico', 'laura@example.com')->firstOrFail();

    expect($empleado->id_usuario)->toBeNull()
        ->and(User::where('email', 'laura@example.com')->exists())->toBeFalse();

    Livewire::actingAs($this->admin)
        ->test(Empleados::class)
        ->call('editar', $empleado->id_empleado)
        ->set('telefono', '5512345678')
        ->call('guardar')
        ->assertHasNoErrors();

    expect($empleado->fresh()->telefono)->toBe('5512345678')
        ->and($empleado->fresh()->id_usuario)->toBeNull();
});

test('no se permiten dos empleados con el mismo correo', function () {
    Empleado::create([
        'nombre' => 'Uno',
        'apellidos' => 'Existente',
        'correo_electronico' => 'repetido@example.com',
        'puesto' => 'Camarista',
    ]);

    Livewire::actingAs($this->admin)
        ->test(Empleados::class)
        ->call('crear')
        ->set('nombre', 'Dos')
        ->set('apellidos', 'Duplicado')
        ->set('correo_electronico', 'repetido@example.com')
        ->set('puesto', 'Valet parking')
        ->set('acceso_sistema', false)
        ->call('guardar')
        ->assertHasErrors(['correo_electronico']);

    expect(Empleado::where('correo_electronico', 'repetido@example.com')->count())->toBe(1);
});

test('revocar el acceso desactiva la cuenta y reactivarla no duplica el usuario', function () {
    Livewire::actingAs($this->admin)
        ->test(Empleados::class)
        ->call('crear')
        ->set('nombre', 'Ana')
        ->set('apellidos', 'Paredes')
        ->set('correo_electronico', 'ana@example.com')
        ->set('puesto', 'Recepción')
        ->set('contrasena', 'secret123')
        ->set('rol', 'recepcionista')
        ->set('acceso_sistema', true)
        ->call('guardar')
        ->assertHasNoErrors();

    $empleado = Empleado::where('correo_electronico', 'ana@example.com')->firstOrFail();
    $usuario = $empleado->usuario;

    Livewire::actingAs($this->admin)
        ->test(Empleados::class)
        ->call('editar', $empleado->id_empleado)
        ->set('acceso_sistema', false)
        ->call('guardar')
        ->assertHasNoErrors();

    expect($usuario->fresh()->activo)->toBeFalse()
        ->and($usuario->fresh()->hasRole('recepcionista'))->toBeFalse()
        ->and(Empleado::find($empleado->id_empleado)->id_usuario)->toBe($usuario->id);

    Livewire::actingAs($this->admin)
        ->test(Empleados::class)
        ->call('editar', $empleado->id_empleado)
        ->set('acceso_sistema', true)
        ->set('rol', 'recepcionista')
        ->call('guardar')
        ->assertHasNoErrors();

    expect(User::where('email', 'ana@example.com')->count())->toBe(1)
        ->and($usuario->fresh()->activo)->toBeTrue()
        ->and($usuario->fresh()->hasRole('recepcionista'))->toBeTrue();
});

test('un rol inexistente se rechaza sin crear usuario ni empleado', function () {
    Livewire::actingAs($this->admin)
        ->test(Empleados::class)
        ->call('crear')
        ->set('nombre', 'Rol')
        ->set('apellidos', 'Inválido')
        ->set('correo_electronico', 'rol.invalido@example.com')
        ->set('puesto', 'Seguridad')
        ->set('contrasena', 'secret123')
        ->set('rol', 'rol-que-no-existe')
        ->set('acceso_sistema', true)
        ->call('guardar')
        ->assertHasErrors(['rol']);

    expect(User::where('email', 'rol.invalido@example.com')->exists())->toBeFalse()
        ->and(Empleado::where('correo_electronico', 'rol.invalido@example.com')->exists())->toBeFalse();
});

test('el correo de una cuenta existente no se reutiliza para otro empleado con acceso', function () {
    User::factory()->create(['email' => 'ocupado@example.com']);

    Livewire::actingAs($this->admin)
        ->test(Empleados::class)
        ->call('crear')
        ->set('nombre', 'Correo')
        ->set('apellidos', 'Ocupado')
        ->set('correo_electronico', 'ocupado@example.com')
        ->set('puesto', 'Recepción')
        ->set('contrasena', 'secret123')
        ->set('rol', 'recepcionista')
        ->set('acceso_sistema', true)
        ->call('guardar')
        ->assertHasErrors(['correo_electronico']);

    expect(Empleado::where('correo_electronico', 'ocupado@example.com')->exists())->toBeFalse();
});
