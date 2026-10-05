<?php

use App\Livewire\Empleados;
use App\Livewire\GestionRoles;
use App\Livewire\MatrizPermisos;
use App\Models\Cliente;
use App\Models\Empleado;
use App\Models\Habitacion;
use App\Models\Reserva;
use App\Models\ReservaHabitacion;
use App\Models\TipoHabitacion;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Livewire\Livewire;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

test('guests are redirected to login on hotel module routes', function () {
    $uris = [
        '/dashboard', '/reservaciones', '/habitaciones', '/clientes',
        '/checkin-checkout', '/limpieza', '/pagos', '/servicios',
        '/gastos', '/empleados', '/temporadas', '/reportes',
        '/roles', '/configuracion', '/mis-reservaciones',
        '/mis-reservaciones/1/estado-cuenta',
    ];

    foreach ($uris as $uri) {
        $this->get($uri)->assertRedirect('/login');
    }
});

test('staff with module permissions can visit the admin module pages', function () {
    $this->seed(RolesAndPermissionsSeeder::class);

    $staff = User::factory()->create();
    $staff->assignRole('gerente');
    $staff->givePermissionTo([
        'habitaciones.ver', 'clientes.ver', 'checkin_checkout.ver',
        'limpieza.ver', 'servicios.ver', 'temporadas.ver', 'configuracion.ver',
    ]);

    $this->actingAs($staff);

    $uris = [
        '/dashboard', '/reservaciones', '/habitaciones', '/clientes',
        '/checkin-checkout', '/limpieza', '/pagos', '/servicios',
        '/gastos', '/temporadas', '/reportes', '/configuracion',
    ];

    foreach ($uris as $uri) {
        $this->get($uri)->assertOk();
    }
});

test('users without module permissions are forbidden from the admin module pages', function () {
    $this->seed(RolesAndPermissionsSeeder::class);

    $user = User::factory()->create();
    $this->actingAs($user);

    $uris = [
        '/dashboard', '/reservaciones', '/habitaciones', '/clientes',
        '/checkin-checkout', '/limpieza', '/pagos', '/servicios',
        '/gastos', '/temporadas', '/reportes', '/configuracion', '/roles',
    ];

    foreach ($uris as $uri) {
        $this->get($uri)->assertForbidden();
    }
});

test('only super-admins can access the roles and employees pages', function () {
    $this->seed(RolesAndPermissionsSeeder::class);

    $plano = User::factory()->create();
    $this->actingAs($plano);
    $this->get('/roles')->assertForbidden();
    $this->get('/empleados')->assertForbidden();

    $admin = User::factory()->create();
    $admin->assignRole('super-admin');
    $this->actingAs($admin);
    $this->get('/roles')->assertOk();
    $this->get('/empleados')->assertOk();
});

test('the permissions matrix is no longer available as a standalone route', function () {
    $this->seed(RolesAndPermissionsSeeder::class);

    $admin = User::factory()->create();
    $admin->assignRole('super-admin');
    $this->actingAs($admin);

    $this->get('/permisos')->assertNotFound();
});

test('the sidebar shows every section to the super-admin', function () {
    $this->seed(RolesAndPermissionsSeeder::class);

    $admin = User::factory()->create();
    $admin->assignRole('super-admin');

    $this->actingAs($admin)
        ->get('/dashboard')
        ->assertOk()
        ->assertSee('Reservaciones')
        ->assertSee('Reportes')
        ->assertSee('Empleados')
        ->assertSee('Roles')
        ->assertDontSee('Permisos')
        ->assertSee('Configuración');
});

test('the sidebar hides restricted sections from the recepcionista', function () {
    $this->seed(RolesAndPermissionsSeeder::class);

    $recepcionista = User::factory()->create();
    $recepcionista->assignRole('recepcionista');

    $this->actingAs($recepcionista)
        ->get('/reservaciones')
        ->assertOk()
        ->assertSee('Reservaciones')
        ->assertSee('Clientes')
        ->assertSee('Limpieza')
        ->assertDontSee('Dashboard')
        ->assertDontSee('Empleados')
        ->assertDontSee('Roles')
        ->assertDontSee('Permisos')
        ->assertDontSee('Reportes')
        ->assertDontSee('Pagos');
});

test('the hotel tables exist with their required columns', function () {
    expect(Schema::hasTable('tipos_habitacion'))->toBeTrue()
        ->and(Schema::hasColumns('tipos_habitacion', ['id', 'nombre', 'descripcion', 'precio_base', 'capacidad']))->toBeTrue()
        ->and(Schema::hasColumns('habitaciones', ['id', 'numero_habitacion', 'tipo_habitacion_id', 'estado', 'piso']))->toBeTrue()
        ->and(Schema::hasColumns('clientes', ['id', 'nombre', 'apellido', 'email', 'telefono', 'tipo_identificacion', 'numero_identificacion']))->toBeTrue()
        ->and(Schema::hasColumns('reservas', ['id', 'cliente_id', 'user_id', 'check_in', 'check_out', 'estado', 'monto_total']))->toBeTrue()
        ->and(Schema::hasColumns('reserva_habitacion', ['id', 'reserva_id', 'habitacion_id', 'precio_por_noche']))->toBeTrue()
        ->and(Schema::hasColumns('pagos', ['id', 'reserva_id', 'monto', 'metodo_pago', 'fecha_pago', 'notas']))->toBeTrue()
        ->and(Schema::hasColumns('servicios', ['id', 'nombre', 'descripcion', 'categoria', 'precio']))->toBeTrue()
        ->and(Schema::hasColumns('reserva_servicio', ['id', 'reserva_id', 'servicio_id', 'cantidad', 'precio_aplicado', 'empleado_id', 'subtotal']))->toBeTrue()
        ->and(Schema::hasColumns('limpieza', ['id', 'habitacion_id', 'user_id', 'estado', 'notas']))->toBeTrue()
        ->and(Schema::hasColumns('gastos', ['id', 'concepto', 'monto', 'categoria', 'fecha_gasto']))->toBeTrue()
        ->and(Schema::hasColumns('temporadas', [
            'id', 'nombre', 'fecha_inicio', 'fecha_fin',
            'multiplicador_precio', 'precio_base', 'activo',
        ]))->toBeTrue();
});

test('the seeder creates the admin user with the super-admin role and hotel data', function () {
    $this->seed();

    $admin = User::where('email', 'admin@example.com')->first();

    expect($admin)->not->toBeNull()
        ->and($admin->hasRole('super-admin'))->toBeTrue()
        ->and(TipoHabitacion::count())->toBeGreaterThan(0)
        ->and(Habitacion::count())->toBeGreaterThan(0)
        ->and(Cliente::count())->toBeGreaterThan(0);
});

test('a super-admin can create roles from the livewire component', function () {
    $this->seed(RolesAndPermissionsSeeder::class);

    $admin = User::factory()->create();
    $admin->assignRole('super-admin');

    Livewire::actingAs($admin)
        ->test(GestionRoles::class)
        ->call('abrirModalCrear')
        ->set('nombre', 'contabilidad')
        ->call('guardarRol')
        ->assertHasNoErrors()
        ->assertSee('creado correctamente');

    expect(Role::where('name', 'contabilidad')->exists())->toBeTrue();
});

test('the simple create flow only requires the name and does not assign permissions', function () {
    $this->seed(RolesAndPermissionsSeeder::class);

    $admin = User::factory()->create();
    $admin->assignRole('super-admin');

    Livewire::actingAs($admin)
        ->test(GestionRoles::class)
        ->call('abrirModalCrear')
        ->assertSee('Crear nuevo puesto')
        ->assertDontSee('El Super Admin ya posee todos los permisos del sistema')
        ->assertDontSee('Permisos iniciales')
        ->set('nombre', 'auditor')
        ->call('guardarRol')
        ->assertHasNoErrors()
        ->assertSee('creado correctamente');

    expect(Role::findByName('auditor')->permissions()->count())->toBe(0);
});

test('a super-admin cannot create two roles with the same name', function () {
    $this->seed(RolesAndPermissionsSeeder::class);

    $admin = User::factory()->create();
    $admin->assignRole('super-admin');

    Role::create(['name' => 'contabilidad', 'guard_name' => 'web']);

    Livewire::actingAs($admin)
        ->test(GestionRoles::class)
        ->call('abrirModalCrear')
        ->set('nombre', 'Contabilidad')
        ->call('guardarRol')
        ->assertHasErrors(['nombre'])
        ->assertSee('Ya existe un rol con ese nombre');

    expect(Role::where('name', 'contabilidad')->count())->toBe(1);
});

test('employees can be searched and filtered by status', function () {
    $this->seed(RolesAndPermissionsSeeder::class);

    $admin = User::factory()->create();
    $admin->assignRole('super-admin');

    Empleado::create([
        'nombre' => 'María', 'apellidos' => 'Torres', 'puesto' => 'Recepcionista', 'esta_activo' => true,
    ]);
    Empleado::create([
        'nombre' => 'Pedro', 'apellidos' => 'Sosa', 'puesto' => 'Mantenimiento', 'esta_activo' => false,
    ]);

    Livewire::actingAs($admin)
        ->test(Empleados::class)
        ->assertSee('María')
        ->assertSee('Pedro')
        ->set('busqueda', 'Recepcionista')
        ->assertSee('María')
        ->assertDontSee('Pedro')
        ->set('busqueda', '')
        ->set('filtroEstado', 'inactivos')
        ->assertSee('Pedro')
        ->assertDontSee('María');
});

test('employees can be toggled active or inactive', function () {
    $this->seed(RolesAndPermissionsSeeder::class);

    $admin = User::factory()->create();
    $admin->assignRole('super-admin');

    $empleado = Empleado::create([
        'nombre' => 'Laura', 'apellidos' => 'Vega', 'puesto' => 'Limpieza', 'esta_activo' => true,
    ]);

    Livewire::actingAs($admin)
        ->test(Empleados::class)
        ->call('toggleActivo', $empleado->id_empleado)
        ->assertSee('desactivado correctamente');

    expect($empleado->fresh()->esta_activo)->toBeFalse();
});

test('employees can be created with a system account, hashed password and role', function () {
    $this->seed(RolesAndPermissionsSeeder::class);

    $admin = User::factory()->create();
    $admin->assignRole('super-admin');

    Livewire::actingAs($admin)
        ->test(Empleados::class)
        ->call('crear')
        ->set('nombre', 'Jorge')
        ->set('apellidos', 'Ramos')
        ->set('correo_electronico', 'jorge.ramos@example.com')
        ->set('contrasena', 'secret123')
        ->set('rol', 'recepcionista')
        ->set('telefono', '5512345678')
        ->set('salario', '12500.00')
        ->set('turno', 'Tarde')
        ->set('acceso_sistema', true)
        ->call('guardar')
        ->assertHasNoErrors()
        ->assertSee('creado correctamente');

    $usuario = User::where('email', 'jorge.ramos@example.com')->first();
    $empleado = Empleado::where('id_usuario', $usuario->id)->first();

    expect($usuario)->not->toBeNull()
        ->and($usuario->hasRole('recepcionista'))->toBeTrue()
        ->and(Hash::check('secret123', $usuario->password))->toBeTrue()
        ->and($empleado)->not->toBeNull()
        ->and($empleado->nombre)->toBe('Jorge')
        ->and($empleado->apellidos)->toBe('Ramos')
        ->and($empleado->telefono)->toBe('5512345678')
        ->and($empleado->salario)->toBe('12500.00')
        ->and($empleado->turno)->toBe('Tarde')
        ->and($empleado->esta_activo)->toBeTrue();
});

test('employees without system access are registered without a user account', function () {
    $this->seed(RolesAndPermissionsSeeder::class);

    $admin = User::factory()->create();
    $admin->assignRole('super-admin');

    Livewire::actingAs($admin)
        ->test(Empleados::class)
        ->call('crear')
        ->set('nombre', 'Laura')
        ->set('apellidos', 'Ruiz')
        ->set('correo_electronico', 'laura.ruiz@example.com')
        ->set('acceso_sistema', false)
        ->call('guardar')
        ->assertHasNoErrors()
        ->assertSee('creado correctamente');

    expect(User::where('email', 'laura.ruiz@example.com')->exists())->toBeFalse()
        ->and(Empleado::where('correo_electronico', 'laura.ruiz@example.com')->exists())->toBeTrue();
});

test('only the super-admin can create employees', function () {
    $this->seed(RolesAndPermissionsSeeder::class);

    $recepcionista = User::factory()->create();
    $recepcionista->assignRole('recepcionista');

    Livewire::actingAs($recepcionista)
        ->test(Empleados::class)
        ->call('crear')
        ->set('nombre', 'Pedro')
        ->set('apellidos', 'Sosa')
        ->set('correo_electronico', 'pedro.sosa@example.com')
        ->set('acceso_sistema', false)
        ->call('guardar')
        ->assertStatus(403);

    expect(Empleado::where('correo_electronico', 'pedro.sosa@example.com')->exists())->toBeFalse();
});

test('the employee form excludes the super-admin role from the dropdown', function () {
    $this->seed(RolesAndPermissionsSeeder::class);

    $admin = User::factory()->create();
    $admin->assignRole('super-admin');

    Livewire::actingAs($admin)
        ->test(Empleados::class)
        ->call('crear')
        ->assertDontSee('value="super-admin"');

    expect(Role::where('name', 'super-admin')->exists())->toBeTrue();
});

test('employees cannot be created with the super-admin role', function () {
    $this->seed(RolesAndPermissionsSeeder::class);

    $admin = User::factory()->create();
    $admin->assignRole('super-admin');

    Livewire::actingAs($admin)
        ->test(Empleados::class)
        ->call('crear')
        ->set('nombre', 'Iván')
        ->set('apellidos', 'Soto')
        ->set('correo_electronico', 'ivan.soto@example.com')
        ->set('contrasena', 'secret123')
        ->set('rol', 'super-admin')
        ->set('acceso_sistema', true)
        ->call('guardar')
        ->assertHasErrors(['rol']);

    expect(User::where('email', 'ivan.soto@example.com')->exists())->toBeFalse()
        ->and(Empleado::where('correo_electronico', 'ivan.soto@example.com')->exists())->toBeFalse();
});

test('employees can be created as inactive and with a valid shift', function () {
    $this->seed(RolesAndPermissionsSeeder::class);

    $admin = User::factory()->create();
    $admin->assignRole('super-admin');

    Livewire::actingAs($admin)
        ->test(Empleados::class)
        ->call('crear')
        ->set('nombre', 'Carla')
        ->set('apellidos', 'Núñez')
        ->set('correo_electronico', 'carla.nunez@example.com')
        ->set('turno', 'Noche')
        ->set('esta_activo', '0')
        ->set('acceso_sistema', false)
        ->call('guardar')
        ->assertHasNoErrors()
        ->assertSee('creado correctamente');

    $empleado = Empleado::where('correo_electronico', 'carla.nunez@example.com')->first();

    expect($empleado)->not->toBeNull()
        ->and($empleado->turno)->toBe('Noche')
        ->and($empleado->esta_activo)->toBeFalse();
});

test('the employee form only accepts the predefined shifts', function () {
    $this->seed(RolesAndPermissionsSeeder::class);

    $admin = User::factory()->create();
    $admin->assignRole('super-admin');

    Livewire::actingAs($admin)
        ->test(Empleados::class)
        ->call('crear')
        ->set('nombre', 'Roberto')
        ->set('apellidos', 'Díaz')
        ->set('correo_electronico', 'roberto.diaz@example.com')
        ->set('turno', '8:00 - 16:00')
        ->set('acceso_sistema', false)
        ->call('guardar')
        ->assertHasErrors(['turno']);

    expect(Empleado::where('correo_electronico', 'roberto.diaz@example.com')->exists())->toBeFalse();
});

test('a super-admin can edit the employee personal data and its system role', function () {
    $this->seed(RolesAndPermissionsSeeder::class);

    $admin = User::factory()->create();
    $admin->assignRole('super-admin');

    $usuario = User::factory()->create(['email' => 'mario.lopez@example.com']);
    $usuario->assignRole('recepcionista');

    $empleado = Empleado::create([
        'nombre' => 'Mario',
        'apellidos' => 'López',
        'correo_electronico' => 'mario.lopez@example.com',
        'id_usuario' => $usuario->id,
        'puesto' => 'Recepcionista',
        'telefono' => '5512345678',
        'turno' => 'Tarde',
        'salario' => 9500.50,
    ]);

    Livewire::actingAs($admin)
        ->test(Empleados::class)
        ->call('editar', $empleado->id_empleado)
        ->set('telefono', '5598765432')
        ->set('turno', 'Noche')
        ->set('salario', '11000.00')
        ->set('rol', 'gerente')
        ->call('guardar')
        ->assertHasNoErrors()
        ->assertSee('actualizado correctamente');

    $empleado->refresh();

    expect($empleado->telefono)->toBe('5598765432')
        ->and($empleado->turno)->toBe('Noche')
        ->and($empleado->salario)->toBe('11000.00')
        ->and($usuario->fresh()->hasRole('gerente'))->toBeTrue();
});

test('editing an existing super-admin employee preserves their super-admin role', function () {
    $this->seed(RolesAndPermissionsSeeder::class);

    $admin = User::factory()->create();
    $admin->assignRole('super-admin');

    $usuario = User::factory()->create(['email' => 'sa.lopez@example.com']);
    $usuario->assignRole('super-admin');

    $empleado = Empleado::create([
        'nombre' => 'Saúl',
        'apellidos' => 'López',
        'correo_electronico' => 'sa.lopez@example.com',
        'id_usuario' => $usuario->id,
        'puesto' => 'Administrador',
    ]);

    Livewire::actingAs($admin)
        ->test(Empleados::class)
        ->call('editar', $empleado->id_empleado)
        ->assertSet('rol', '')
        ->set('turno', 'Rotativo')
        ->call('guardar')
        ->assertHasNoErrors();

    expect($usuario->fresh()->hasRole('super-admin'))->toBeTrue();
});

test('protected system roles cannot be deleted from the livewire component', function () {
    $this->seed(RolesAndPermissionsSeeder::class);

    $admin = User::factory()->create();
    $admin->assignRole('super-admin');

    $rol = Role::where('name', 'super-admin')->firstOrFail();

    Livewire::actingAs($admin)
        ->test(GestionRoles::class)
        ->call('eliminarRol', $rol->id)
        ->assertSee('no puede eliminarse');

    expect(Role::where('name', 'super-admin')->exists())->toBeTrue();
});

test('only the super-admin can toggle permissions in the matrix', function () {
    $this->seed(RolesAndPermissionsSeeder::class);

    $recepcionista = User::factory()->create();
    $recepcionista->assignRole('recepcionista');

    $rol = Role::findByName('recepcionista');
    $permiso = Permission::findByName('reportes.ver');

    Livewire::actingAs($recepcionista)
        ->test(MatrizPermisos::class)
        ->call('alternarPermiso', $rol->id, $permiso->id)
        ->assertStatus(403);

    expect($rol->fresh()->hasPermissionTo('reportes.ver'))->toBeFalse();
});

test('a super-admin can assign a permission to a non-system role in the matrix', function () {
    $this->seed(RolesAndPermissionsSeeder::class);

    $admin = User::factory()->create();
    $admin->assignRole('super-admin');

    $rol = Role::findByName('recepcionista');
    $permiso = Permission::findByName('reportes.ver');

    Livewire::actingAs($admin)
        ->test(MatrizPermisos::class)
        ->call('alternarPermiso', $rol->id, $permiso->id)
        ->assertHasNoErrors()
        ->assertSee('asignado');

    expect($rol->fresh()->hasPermissionTo('reportes.ver'))->toBeTrue();

    Livewire::actingAs($admin)
        ->test(MatrizPermisos::class)
        ->call('alternarPermiso', $rol->id, $permiso->id)
        ->assertSee('retirado');

    expect($rol->fresh()->hasPermissionTo('reportes.ver'))->toBeFalse();
});

test('the matrix never modifies the immutable super-admin role', function () {
    $this->seed(RolesAndPermissionsSeeder::class);

    $admin = User::factory()->create();
    $admin->assignRole('super-admin');

    $rol = Role::findByName('super-admin');
    $permiso = Permission::findByName('reportes.ver');

    Livewire::actingAs($admin)
        ->test(MatrizPermisos::class)
        ->call('alternarPermiso', $rol->id, $permiso->id)
        ->assertSee('no pueden modificarse');

    expect($rol->fresh()->hasPermissionTo('reportes.ver'))->toBeTrue();
});

test('the sidebar displays the NovaStay wordmark', function () {
    $this->seed(RolesAndPermissionsSeeder::class);

    $gerente = User::factory()->create();
    $gerente->assignRole('gerente');

    $this->actingAs($gerente)
        ->get('/dashboard')
        ->assertOk()
        ->assertSee('NovaStay');
});

test('a super-admin can rename a role from the simplified edit modal', function () {
    $this->seed(RolesAndPermissionsSeeder::class);

    $admin = User::factory()->create();
    $admin->assignRole('super-admin');

    $rol = Role::create(['name' => 'contabilidad', 'guard_name' => 'web']);
    $rol->givePermissionTo('reportes.ver');

    Livewire::actingAs($admin)
        ->test(GestionRoles::class)
        ->call('abrirModalEditar', $rol->id)
        ->assertSet('nombre', 'contabilidad')
        ->assertSee('Editar rol')
        ->assertDontSee('Permisos del rol')
        ->set('nombre', 'finanzas')
        ->call('actualizarRol')
        ->assertHasNoErrors()
        ->assertSee('actualizado correctamente');

    $rol->refresh();

    expect($rol->name)->toBe('finanzas')
        ->and($rol->hasPermissionTo('reportes.ver'))->toBeTrue();
});

test('only the super-admin can edit or delete roles from the component', function () {
    $this->seed(RolesAndPermissionsSeeder::class);

    $usuario = User::factory()->create();
    $usuario->givePermissionTo('roles_permisos.ver');

    $rol = Role::create(['name' => 'contabilidad', 'guard_name' => 'web']);

    Livewire::actingAs($usuario)
        ->test(GestionRoles::class)
        ->call('abrirModalEditar', $rol->id)
        ->assertStatus(403);

    Livewire::actingAs($usuario)
        ->test(GestionRoles::class)
        ->call('seleccionarRolAEliminar', $rol->id)
        ->assertStatus(403);
});

test('a super-admin can open the edit modal for the super-admin role itself', function () {
    $this->seed(RolesAndPermissionsSeeder::class);

    $admin = User::factory()->create();
    $admin->assignRole('super-admin');

    $rolSuperAdmin = Role::where('name', 'super-admin')->firstOrFail();

    Livewire::actingAs($admin)
        ->test(GestionRoles::class)
        ->call('abrirModalEditar', $rolSuperAdmin->id)
        ->assertSet('nombre', 'super-admin')
        ->assertSet('rolIdEditar', $rolSuperAdmin->id);
});

test('a super-admin can rename a system role without altering its permissions', function () {
    $this->seed(RolesAndPermissionsSeeder::class);

    $admin = User::factory()->create();
    $admin->assignRole('super-admin');

    $rolGerente = Role::where('name', 'gerente')->firstOrFail();

    Livewire::actingAs($admin)
        ->test(GestionRoles::class)
        ->call('abrirModalEditar', $rolGerente->id)
        ->assertSet('nombre', 'gerente')
        ->set('nombre', 'gerente general')
        ->call('actualizarRol')
        ->assertHasNoErrors()
        ->assertSee('actualizado correctamente');

    $rolGerente->refresh();

    expect($rolGerente->name)->toBe('gerente general')
        ->and($rolGerente->hasPermissionTo('reservaciones.ver'))->toBeTrue()
        ->and($rolGerente->hasPermissionTo('roles_permisos.ver'))->toBeFalse();
});

test('the super-admin role cannot be deleted from the component', function () {
    $this->seed(RolesAndPermissionsSeeder::class);

    $admin = User::factory()->create();
    $admin->assignRole('super-admin');

    $rol = Role::where('name', 'super-admin')->firstOrFail();

    Livewire::actingAs($admin)
        ->test(GestionRoles::class)
        ->call('eliminarRol', $rol->id)
        ->assertSee('no puede eliminarse');

    expect(Role::where('name', 'super-admin')->exists())->toBeTrue();
});

test('a role assigned to the current session user cannot be deleted', function () {
    $this->seed(RolesAndPermissionsSeeder::class);

    $admin = User::factory()->create();
    $admin->assignRole('super-admin');

    $rol = Role::where('name', 'super-admin')->firstOrFail();

    Livewire::actingAs($admin)
        ->test(GestionRoles::class)
        ->call('seleccionarRolAEliminar', $rol->id)
        ->assertSee('no puede eliminarse');

    expect(Role::where('name', 'super-admin')->exists())->toBeTrue();
});

test('a super-admin can delete the cliente role', function () {
    $this->seed(RolesAndPermissionsSeeder::class);

    $admin = User::factory()->create();
    $admin->assignRole('super-admin');

    $rol = Role::where('name', 'cliente')->firstOrFail();

    Livewire::actingAs($admin)
        ->test(GestionRoles::class)
        ->call('seleccionarRolAEliminar', $rol->id)
        ->assertSet('mostrarModalEliminar', true)
        ->call('eliminarRol')
        ->assertSee('eliminado correctamente');

    expect(Role::where('name', 'cliente')->exists())->toBeFalse();
});

test('a super-admin can assign direct permissions to an employee from the granular matrix', function () {
    $this->seed(RolesAndPermissionsSeeder::class);

    $admin = User::factory()->create();
    $admin->assignRole('super-admin');

    $usuario = User::factory()->create(['email' => 'granular@example.com']);
    $usuario->assignRole('recepcionista');

    $empleado = Empleado::create([
        'nombre' => 'Ana',
        'apellidos' => 'Paredes',
        'correo_electronico' => 'granular@example.com',
        'id_usuario' => $usuario->id,
    ]);

    Livewire::actingAs($admin)
        ->test(Empleados::class)
        ->call('abrirModalPermisos', $empleado->id_empleado)
        ->assertSet('mostrarModalPermisos', true)
        ->set('permisosUsuario', ['reportes.ver', 'pagos.ver'])
        ->call('guardarPermisosGranulares')
        ->assertHasNoErrors()
        ->assertSee('Permisos del empleado actualizados correctamente.');

    expect($usuario->fresh()->hasDirectPermission('reportes.ver'))->toBeTrue()
        ->and($usuario->fresh()->hasDirectPermission('pagos.ver'))->toBeTrue()
        ->and($usuario->fresh()->hasDirectPermission('gastos.ver'))->toBeFalse();
});

test('the permissions modal always opens for the exact employee clicked and resets its state', function () {
    $this->seed(RolesAndPermissionsSeeder::class);

    $admin = User::factory()->create();
    $admin->assignRole('super-admin');

    $usuarioSeguridad = User::factory()->create(['email' => 'seguridad@example.com']);
    $usuarioRecepcion = User::factory()->create(['email' => 'recepcion@example.com']);

    $seguridad = Empleado::create([
        'nombre' => 'Seguridad',
        'apellidos' => 'Noche',
        'correo_electronico' => 'seguridad@example.com',
        'id_usuario' => $usuarioSeguridad->id,
    ]);

    $recepcion = Empleado::create([
        'nombre' => 'Recepcionista',
        'apellidos' => 'Día',
        'correo_electronico' => 'recepcion@example.com',
        'id_usuario' => $usuarioRecepcion->id,
    ]);

    $usuarioSeguridad->givePermissionTo('reportes.ver');
    $usuarioRecepcion->givePermissionTo('pagos.ver');

    Livewire::actingAs($admin)
        ->test(Empleados::class)
        ->call('abrirModalPermisos', $recepcion->id_empleado)
        ->assertSet('empleadoIdPermisos', $recepcion->id_empleado)
        ->assertSet('permisosUsuario', ['pagos.ver'])
        ->call('abrirModalPermisos', $seguridad->id_empleado)
        ->assertSet('empleadoIdPermisos', $seguridad->id_empleado)
        ->assertSet('permisosUsuario', ['reportes.ver'])
        ->assertSet('mostrarModalPermisos', true);
});

test('only the super-admin can open the granular permissions matrix', function () {
    $this->seed(RolesAndPermissionsSeeder::class);

    $recepcionista = User::factory()->create();
    $recepcionista->assignRole('recepcionista');

    $usuario = User::factory()->create();
    $empleado = Empleado::create([
        'nombre' => 'Luis',
        'apellidos' => 'Quispe',
        'correo_electronico' => 'luis@example.com',
        'id_usuario' => $usuario->id,
    ]);

    Livewire::actingAs($recepcionista)
        ->test(Empleados::class)
        ->call('abrirModalPermisos', $empleado->id_empleado)
        ->assertStatus(403);
});

test('the granular permissions modal renders the module matrix with a TODOS column', function () {
    $this->seed(RolesAndPermissionsSeeder::class);

    $admin = User::factory()->create();
    $admin->assignRole('super-admin');

    $usuario = User::factory()->create(['email' => 'matriz.vista@example.com']);
    $empleado = Empleado::create([
        'nombre' => 'Vista',
        'apellidos' => 'Matriz',
        'correo_electronico' => 'matriz.vista@example.com',
        'id_usuario' => $usuario->id,
    ]);

    Livewire::actingAs($admin)
        ->test(Empleados::class)
        ->call('abrirModalPermisos', $empleado->id_empleado)
        ->assertSee('Seguridad Granular')
        ->assertSee('TODOS')
        ->assertSee('Mostrar')
        ->assertSee('Crear')
        ->assertSee('Editar')
        ->assertSee('Eliminar')
        ->assertSee('Pagos')
        ->assertSee('Empleados');
});

test('a super-admin can select all permissions of a module from the matrix', function () {
    $this->seed(RolesAndPermissionsSeeder::class);

    $admin = User::factory()->create();
    $admin->assignRole('super-admin');

    $usuario = User::factory()->create(['email' => 'matriz.todos@example.com']);
    $usuario->assignRole('recepcionista');

    $empleado = Empleado::create([
        'nombre' => 'Todos',
        'apellidos' => 'Matriz',
        'correo_electronico' => 'matriz.todos@example.com',
        'id_usuario' => $usuario->id,
    ]);

    Livewire::actingAs($admin)
        ->test(Empleados::class)
        ->call('abrirModalPermisos', $empleado->id_empleado)
        ->call('alternarTodosDelModulo', 'pagos')
        ->call('guardarPermisosGranulares')
        ->assertHasNoErrors()
        ->assertSee('Permisos del empleado actualizados correctamente.');

    expect($usuario->fresh()->getDirectPermissions()->pluck('name')->sort()->values()->all())
        ->toBe(['pagos.crear', 'pagos.editar', 'pagos.eliminar', 'pagos.ver'])
        ->and($usuario->fresh()->hasDirectPermission('gastos.ver'))->toBeFalse();
});

test('a super-admin can deselect all permissions of a module from the matrix', function () {
    $this->seed(RolesAndPermissionsSeeder::class);

    $admin = User::factory()->create();
    $admin->assignRole('super-admin');

    $usuario = User::factory()->create(['email' => 'matriz.vacio@example.com']);
    $usuario->givePermissionTo(['pagos.ver', 'pagos.crear', 'pagos.editar', 'pagos.eliminar']);

    $empleado = Empleado::create([
        'nombre' => 'Vacío',
        'apellidos' => 'Matriz',
        'correo_electronico' => 'matriz.vacio@example.com',
        'id_usuario' => $usuario->id,
    ]);

    Livewire::actingAs($admin)
        ->test(Empleados::class)
        ->call('abrirModalPermisos', $empleado->id_empleado)
        ->call('alternarTodosDelModulo', 'pagos')
        ->call('guardarPermisosGranulares')
        ->assertHasNoErrors();

    expect($usuario->fresh()->hasDirectPermission('pagos.ver'))->toBeFalse()
        ->and($usuario->fresh()->hasDirectPermission('pagos.eliminar'))->toBeFalse();
});

test('only the super-admin can toggle all permissions of a module in the matrix', function () {
    $this->seed(RolesAndPermissionsSeeder::class);

    $recepcionista = User::factory()->create();
    $recepcionista->assignRole('recepcionista');

    Livewire::actingAs($recepcionista)
        ->test(Empleados::class)
        ->call('alternarTodosDelModulo', 'pagos')
        ->assertStatus(403);
});

test('the employees table shows the permissions column with a configure button', function () {
    $this->seed(RolesAndPermissionsSeeder::class);

    $admin = User::factory()->create();
    $admin->assignRole('super-admin');

    Empleado::create([
        'nombre' => 'Sofía',
        'apellidos' => 'Mendoza',
        'esta_activo' => true,
    ]);

    Livewire::actingAs($admin)
        ->test(Empleados::class)
        ->assertSee('Rol del Sistema')
        ->assertSee('Permisos')
        ->assertSee('Configurar');
});

test('the employee form no longer shows the Puesto field', function () {
    $this->seed(RolesAndPermissionsSeeder::class);

    $admin = User::factory()->create();
    $admin->assignRole('super-admin');

    Livewire::actingAs($admin)
        ->test(Empleados::class)
        ->call('crear')
        ->assertDontSee('Puesto');
});

test('the employees module shows dynamic KPI cards grouped by position', function () {
    $this->seed(RolesAndPermissionsSeeder::class);

    $admin = User::factory()->create();
    $admin->assignRole('super-admin');

    foreach (['recep.uno@example.com', 'recep.dos@example.com'] as $email) {
        $usuario = User::factory()->create(['email' => $email]);
        $usuario->assignRole('recepcionista');
        Empleado::create([
            'nombre' => 'Recepcionista',
            'apellidos' => Str::after($email, '@'),
            'correo_electronico' => $email,
            'id_usuario' => $usuario->id,
            'esta_activo' => true,
        ]);
    }

    $limpieza = User::factory()->create(['email' => 'clean.team@example.com']);
    $limpieza->assignRole('limpieza');
    Empleado::create([
        'nombre' => 'Limpieza',
        'apellidos' => 'Turno',
        'correo_electronico' => $limpieza->email,
        'id_usuario' => $limpieza->id,
        'esta_activo' => true,
    ]);

    Empleado::create([
        'nombre' => 'Sín',
        'apellidos' => 'Rol',
        'correo_electronico' => 'sin.rol@example.com',
        'esta_activo' => true,
    ]);

    $componente = Livewire::actingAs($admin)->test(Empleados::class);

    $componente
        ->assertSee('Personal por puesto')
        ->assertSee('Recepción')
        ->assertSee('Limpieza')
        ->assertSee('Sin asignar');

    $html = $componente->html();

    expect(substr_count($html, '>2<'))->toBeGreaterThanOrEqual(1)
        ->and(substr_count($html, '>1<'))->toBeGreaterThanOrEqual(2);
});

test('guests can visit the public registration page', function () {
    $this->get(route('register'))
        ->assertOk()
        ->assertSee('Crea tu cuenta')
        ->assertSee('Nombre completo');
});

test('public registration creates a user with the cliente role and a linked customer record', function () {
    $this->seed(RolesAndPermissionsSeeder::class);

    $this->post(route('register'), [
        'name' => 'María González',
        'email' => 'maria.gonzalez@example.com',
        'telefono' => '5512345678',
        'password' => 'secret123',
        'password_confirmation' => 'secret123',
    ])->assertRedirect(route('home'));

    $usuario = User::where('email', 'maria.gonzalez@example.com')->first();

    expect($usuario)->not->toBeNull()
        ->and($usuario->hasRole('cliente'))->toBeTrue()
        ->and(Auth::check())->toBeTrue();

    $cliente = Cliente::where('user_id', $usuario->id)->first();

    expect($cliente)->not->toBeNull()
        ->and($cliente->nombre)->toBe('María')
        ->and($cliente->apellido)->toBe('González')
        ->and($cliente->email)->toBe('maria.gonzalez@example.com')
        ->and($cliente->telefono)->toBe('5512345678');
});

test('public registration redirects to the reservation confirmation when there is a pending reservation', function () {
    $this->seed(RolesAndPermissionsSeeder::class);

    $checkIn = now()->addDays(7)->toDateString();
    $checkOut = now()->addDays(9)->toDateString();

    $this->withSession([
        'reserva.pendiente' => [
            'habitacion_id' => 1,
            'check_in' => $checkIn,
            'check_out' => $checkOut,
            'guests' => 2,
        ],
    ])->post(route('register'), [
        'name' => 'Juan Pérez',
        'email' => 'juan.perez@example.com',
        'telefono' => '5576543210',
        'password' => 'secret123',
        'password_confirmation' => 'secret123',
    ])->assertRedirect(route('reserva.confirmar'));
});

test('guests who reserve a room are redirected to login with the search data kept in session', function () {
    $this->seed(RolesAndPermissionsSeeder::class);

    $tipo = TipoHabitacion::create(['nombre' => 'Estándar', 'precio_base' => 899.00, 'capacidad' => 2]);
    $habitacion = Habitacion::create(['numero_habitacion' => '101', 'tipo_habitacion_id' => $tipo->id, 'estado' => 'Disponible']);

    $checkIn = now()->addDays(7)->toDateString();
    $checkOut = now()->addDays(9)->toDateString();

    $this->post(route('reserva.iniciar'), [
        'habitacion_id' => $habitacion->id,
        'check_in' => $checkIn,
        'check_out' => $checkOut,
        'guests' => 2,
    ])->assertRedirect(route('login'));

    expect(session('reserva.pendiente'))->not->toBeNull()
        ->and(session('reserva.pendiente.habitacion_id'))->toBe($habitacion->id);
});

test('authenticated clients can see the reservation confirmation with the price breakdown', function () {
    $this->seed(RolesAndPermissionsSeeder::class);

    $tipo = TipoHabitacion::create(['nombre' => 'Deluxe', 'precio_base' => 1499.00, 'capacidad' => 3]);
    $habitacion = Habitacion::create(['numero_habitacion' => '201', 'tipo_habitacion_id' => $tipo->id, 'estado' => 'Disponible']);

    $cliente = User::factory()->create();
    $cliente->assignRole('cliente');

    $checkIn = now()->addDays(7)->toDateString();
    $checkOut = now()->addDays(10)->toDateString();

    $this->actingAs($cliente)
        ->withSession([
            'reserva.pendiente' => [
                'habitacion_id' => $habitacion->id,
                'check_in' => $checkIn,
                'check_out' => $checkOut,
                'guests' => 2,
            ],
        ])
        ->get(route('reserva.confirmar'))
        ->assertOk()
        ->assertSee('Confirmar tu reserva')
        ->assertSee($tipo->nombre)
        ->assertSee('4,497.00');
});

test('a client can confirm a reservation and it is stored with its price details', function () {
    $this->seed(RolesAndPermissionsSeeder::class);

    $tipo = TipoHabitacion::create(['nombre' => 'Suite Familiar', 'precio_base' => 2499.00, 'capacidad' => 5]);
    $habitacion = Habitacion::create(['numero_habitacion' => '301', 'tipo_habitacion_id' => $tipo->id, 'estado' => 'Disponible']);

    $clienteUsuario = User::factory()->create(['email' => 'ana.garcia@example.com']);
    $clienteUsuario->assignRole('cliente');

    Cliente::create([
        'user_id' => $clienteUsuario->id,
        'nombre' => 'Ana',
        'apellido' => 'García',
        'email' => 'ana.garcia@example.com',
    ]);

    $checkIn = now()->addDays(7)->toDateString();
    $checkOut = now()->addDays(9)->toDateString();

    $this->actingAs($clienteUsuario)
        ->withSession([
            'reserva.pendiente' => [
                'habitacion_id' => $habitacion->id,
                'check_in' => $checkIn,
                'check_out' => $checkOut,
                'guests' => 4,
            ],
        ])
        ->post(route('reserva.store'), [
            'habitacion_id' => $habitacion->id,
            'check_in' => $checkIn,
            'check_out' => $checkOut,
            'guests' => 4,
        ])
        ->assertRedirect(route('home'));

    expect(session('reserva.pendiente'))->toBeNull();

    $reserva = Reserva::first();

    expect($reserva)->not->toBeNull()
        ->and($reserva->user_id)->toBe($clienteUsuario->id)
        ->and($reserva->estado)->toBe('Confirmada')
        ->and($reserva->monto_total)->toBe('4998.00');

    expect(ReservaHabitacion::where('reserva_id', $reserva->id)->exists())->toBeTrue();

    $asignacion = ReservaHabitacion::where('reserva_id', $reserva->id)->first();

    expect($asignacion->habitacion_id)->toBe($habitacion->id)
        ->and($asignacion->precio_por_noche)->toBe('2499.00');
});

test('the room search only shows rooms available for the requested dates', function () {
    $this->seed(RolesAndPermissionsSeeder::class);

    $tipo = TipoHabitacion::create(['nombre' => 'Estándar', 'precio_base' => 899.00, 'capacidad' => 2]);
    $disponible = Habitacion::create(['numero_habitacion' => '101', 'tipo_habitacion_id' => $tipo->id, 'estado' => 'Disponible']);
    $ocupada = Habitacion::create(['numero_habitacion' => '102', 'tipo_habitacion_id' => $tipo->id, 'estado' => 'Disponible']);

    $cliente = Cliente::create(['nombre' => 'Laura', 'apellido' => 'López', 'email' => 'laura@example.com']);
    $usuario = User::factory()->create();

    $reserva = Reserva::create([
        'cliente_id' => $cliente->id,
        'user_id' => $usuario->id,
        'check_in' => now()->addDays(7)->toDateString(),
        'check_out' => now()->addDays(10)->toDateString(),
        'estado' => 'Confirmada',
        'monto_total' => 2697.00,
    ]);

    ReservaHabitacion::create([
        'reserva_id' => $reserva->id,
        'habitacion_id' => $ocupada->id,
        'precio_por_noche' => 899.00,
    ]);

    $this->get(route('rooms.search', [
        'check_in' => now()->addDays(7)->toDateString(),
        'check_out' => now()->addDays(9)->toDateString(),
        'guests' => 2,
    ]))
        ->assertOk()
        ->assertSee('#101')
        ->assertDontSee('#102');
});

test('guests cannot access the reservation confirmation page', function () {
    $this->get(route('reserva.confirmar'))->assertRedirect(route('login'));
});

test('clients are redirected to their private panel after login without a pending reservation', function () {
    $this->seed(RolesAndPermissionsSeeder::class);

    $cliente = User::factory()->create(['email' => 'cliente@example.com', 'password' => Hash::make('secret123')]);
    $cliente->assignRole('cliente');

    Cliente::create([
        'user_id' => $cliente->id,
        'nombre' => 'Cliente',
        'apellido' => 'Prueba',
        'email' => 'cliente@example.com',
    ]);

    $this->post(route('login'), [
        'email' => 'cliente@example.com',
        'password' => 'secret123',
    ])->assertRedirect(route('mis-reservaciones'));
});

test('the public home page shows login without the create-account button', function () {
    $this->get(route('home'))
        ->assertOk()
        ->assertDontSee('Crear cuenta')
        ->assertSee('Iniciar sesión');
});

test('the public services page shows login without the create-account button', function () {
    $this->get(route('servicios.public'))
        ->assertOk()
        ->assertDontSee('Crear cuenta')
        ->assertSee('Iniciar sesión');
});

test('clients are redirected to their private panel when they try to visit the dashboard', function () {
    $this->seed(RolesAndPermissionsSeeder::class);

    $cliente = User::factory()->create();
    $cliente->assignRole('cliente');

    $this->actingAs($cliente)
        ->get('/dashboard')
        ->assertRedirect(route('mis-reservaciones'));
});

test('non-manager staff are forbidden from the dashboard', function () {
    $this->seed(RolesAndPermissionsSeeder::class);

    $recepcionista = User::factory()->create();
    $recepcionista->assignRole('recepcionista');

    $this->actingAs($recepcionista)
        ->get('/dashboard')
        ->assertForbidden();
});

test('staff cannot access the client private panel', function () {
    $this->seed(RolesAndPermissionsSeeder::class);

    $recepcionista = User::factory()->create();
    $recepcionista->assignRole('recepcionista');

    $this->actingAs($recepcionista)
        ->get(route('mis-reservaciones'))
        ->assertForbidden();
});

test('clients can view their reservations in the private panel', function () {
    $this->seed(RolesAndPermissionsSeeder::class);

    $usuario = User::factory()->create(['email' => 'panel@example.com']);
    $usuario->assignRole('cliente');

    $cliente = Cliente::create([
        'user_id' => $usuario->id,
        'nombre' => 'Elena',
        'apellido' => 'Ríos',
        'email' => 'panel@example.com',
    ]);

    $tipo = TipoHabitacion::create(['nombre' => 'Deluxe', 'precio_base' => 1499.00, 'capacidad' => 3]);
    $habitacion = Habitacion::create(['numero_habitacion' => '205', 'tipo_habitacion_id' => $tipo->id, 'estado' => 'Disponible']);

    $reserva = Reserva::create([
        'cliente_id' => $cliente->id,
        'user_id' => $usuario->id,
        'check_in' => now()->addDays(7)->toDateString(),
        'check_out' => now()->addDays(10)->toDateString(),
        'estado' => 'Confirmada',
        'monto_total' => 4497.00,
    ]);

    ReservaHabitacion::create([
        'reserva_id' => $reserva->id,
        'habitacion_id' => $habitacion->id,
        'precio_por_noche' => 1499.00,
    ]);

    $this->actingAs($usuario)
        ->get(route('mis-reservaciones'))
        ->assertOk()
        ->assertSee('Mis reservaciones')
        ->assertSee('#205')
        ->assertSee('Confirmada')
        ->assertSee('Descargar estado de cuenta');
});

test('clients can download the estado de cuenta PDF of a reservation', function () {
    $this->seed(RolesAndPermissionsSeeder::class);

    $usuario = User::factory()->create(['email' => 'pdf@example.com']);
    $usuario->assignRole('cliente');

    $cliente = Cliente::create([
        'user_id' => $usuario->id,
        'nombre' => 'Pedro',
        'apellido' => 'Soto',
        'email' => 'pdf@example.com',
    ]);

    $tipo = TipoHabitacion::create(['nombre' => 'Deluxe', 'precio_base' => 1499.00, 'capacidad' => 3]);
    $habitacion = Habitacion::create(['numero_habitacion' => '210', 'tipo_habitacion_id' => $tipo->id, 'estado' => 'Disponible']);

    $reserva = Reserva::create([
        'cliente_id' => $cliente->id,
        'user_id' => $usuario->id,
        'check_in' => now()->addDays(7)->toDateString(),
        'check_out' => now()->addDays(10)->toDateString(),
        'estado' => 'Confirmada',
        'monto_total' => 4497.00,
    ]);

    ReservaHabitacion::create([
        'reserva_id' => $reserva->id,
        'habitacion_id' => $habitacion->id,
        'precio_por_noche' => 1499.00,
    ]);

    $this->actingAs($usuario)
        ->get(route('estado-cuenta.pdf', $reserva->id))
        ->assertOk()
        ->assertHeader('content-type', 'application/pdf');
});

test('a client cannot download the estado de cuenta PDF of another client', function () {
    $this->seed(RolesAndPermissionsSeeder::class);

    $dueño = User::factory()->create(['email' => 'dueno@example.com']);
    $dueño->assignRole('cliente');

    $otro = User::factory()->create(['email' => 'otro@example.com']);
    $otro->assignRole('cliente');

    $cliente = Cliente::create([
        'user_id' => $dueño->id,
        'nombre' => 'Dueño',
        'apellido' => 'Casa',
        'email' => 'dueno@example.com',
    ]);

    $reserva = Reserva::create([
        'cliente_id' => $cliente->id,
        'user_id' => $dueño->id,
        'check_in' => now()->addDays(7)->toDateString(),
        'check_out' => now()->addDays(8)->toDateString(),
        'estado' => 'Confirmada',
        'monto_total' => 1499.00,
    ]);

    $this->actingAs($otro)
        ->get(route('estado-cuenta.pdf', $reserva->id))
        ->assertForbidden();
});

test('guests who choose to create an account are sent to the registration page', function () {
    $this->seed(RolesAndPermissionsSeeder::class);

    $tipo = TipoHabitacion::create(['nombre' => 'Estándar', 'precio_base' => 899.00, 'capacidad' => 2]);
    $habitacion = Habitacion::create(['numero_habitacion' => '102', 'tipo_habitacion_id' => $tipo->id, 'estado' => 'Disponible']);

    $this->post(route('reserva.iniciar'), [
        'habitacion_id' => $habitacion->id,
        'check_in' => now()->addDays(7)->toDateString(),
        'check_out' => now()->addDays(9)->toDateString(),
        'guests' => 2,
        'accion' => 'registro',
    ])->assertRedirect(route('register'));

    expect(session('reserva.pendiente'))->not->toBeNull()
        ->and(session('reserva.pendiente.habitacion_id'))->toBe($habitacion->id);
});

test('the personal login page does not offer public registration', function () {
    $html = $this->get(route('login'))
        ->assertOk()
        ->assertSee('Acceso al sistema')
        ->assertSee('Ingresa tus credenciales de administrador')
        ->assertSee('Iniciar sesión')
        ->assertDontSee('Crea tu cuenta')
        ->assertDontSee('Nombre completo')
        ->getContent();

    preg_match('/id="extra-personal".*?<\/div>/s', $html, $personalBlock);
    preg_match('/id="extra-clientes".*?<\/div>/s', $html, $clientesBlock);

    expect($personalBlock[0] ?? '')->not->toContain('Registrarse')
        ->and($clientesBlock[0] ?? '')->toContain('Registrarse');
});

test('the employees table shows the system role assigned to the user', function () {
    $this->seed(RolesAndPermissionsSeeder::class);

    $admin = User::factory()->create();
    $admin->assignRole('super-admin');

    $usuario = User::factory()->create(['email' => 'rol.visible@example.com']);
    $usuario->assignRole('recepcionista');

    $empleado = Empleado::create([
        'nombre' => 'Rol',
        'apellidos' => 'Visible',
        'correo_electronico' => 'rol.visible@example.com',
        'id_usuario' => $usuario->id,
        'puesto' => 'Recepcionista',
        'esta_activo' => true,
    ]);

    Livewire::actingAs($admin)
        ->test(Empleados::class)
        ->assertSee('Recepcionista');
});

test('the roles table hides the actions column for non-super-admin users', function () {
    $this->seed(RolesAndPermissionsSeeder::class);

    $usuario = User::factory()->create();
    $usuario->givePermissionTo('roles_permisos.ver');

    Livewire::actingAs($usuario)
        ->test(GestionRoles::class)
        ->assertDontSee('Acciones');
});

test('the public services page shows prices in MXN', function () {
    $this->get(route('servicios.public'))
        ->assertOk()
        ->assertSee('$900.00 MXN')
        ->assertSee('$2,400.00 MXN')
        ->assertSee('$1,700.00 MXN')
        ->assertSee('$1,200.00 MXN')
        ->assertDontSee('USD');
});
