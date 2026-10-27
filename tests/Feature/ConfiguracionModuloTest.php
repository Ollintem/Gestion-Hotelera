<?php

use App\Livewire\Configuracion;
use App\Models\InformacionHotel;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Livewire\Livewire;

beforeEach(function () {
    $this->seed(RolesAndPermissionsSeeder::class);

    // El seeder sincroniza todos los permisos al super-admin, así que este
    // usuario nace con configuracion.editar sin asignación manual.
    $this->admin = User::factory()->create();
    $this->admin->assignRole('super-admin');
});

/*
|--------------------------------------------------------------------------
| Apartado general (solo lectura)
|--------------------------------------------------------------------------
*/

test('el apartado general resume la información del sistema en solo lectura', function () {
    Livewire::actingAs($this->admin)
        ->test(Configuracion::class)
        ->assertSet('apartado', 'general')
        ->assertSee('Configuración general')
        ->assertSee(config('app.name'))
        ->assertSee('Zona horaria')
        ->assertSee(config('app.timezone'))
        ->assertSee('Idioma')
        ->assertSee('Sesión iniciada como')
        ->assertSee('Temporada vigente');
});

test('el apartado general no ofrece enlaces a ajustes de cuenta ni contraseña', function () {
    Livewire::actingAs($this->admin)
        ->test(Configuracion::class)
        ->assertDontSee('Cuenta y apariencia')
        ->assertDontSee('settings.profile')
        ->assertDontSee('settings.appearance');
});

test('se puede alternar entre los dos apartados de configuración', function () {
    Livewire::actingAs($this->admin)
        ->test(Configuracion::class)
        ->assertSet('apartado', 'general')
        ->assertSee('Sistema')
        ->call('verApartado', 'hotel')
        ->assertSet('apartado', 'hotel')
        ->assertSee('Datos del establecimiento')
        ->call('verApartado', 'general')
        ->assertSet('apartado', 'general')
        ->assertSee('Sistema');
});

test('verApartado ignora valores desconocidos y vuelve al apartado general', function () {
    Livewire::actingAs($this->admin)
        ->test(Configuracion::class)
        ->call('verApartado', 'hotel')
        ->call('verApartado', 'seccion-inexistente')
        ->assertSet('apartado', 'general');
});

/*
|--------------------------------------------------------------------------
| Registro único de información del hotel
|--------------------------------------------------------------------------
*/

test('current crea el registro único con el id fijo y no se duplica', function () {
    expect(InformacionHotel::query()->count())->toBe(0);

    $primera = InformacionHotel::current();
    $segunda = InformacionHotel::current();

    expect($primera->id)->toBe(1)
        ->and($segunda->id)->toBe(1)
        ->and(InformacionHotel::query()->count())->toBe(1)
        ->and($primera->nombre)->toBe('Mi hotel');
});

test('la ficha del hotel muestra un guion en los campos aún sin capturar', function () {
    Livewire::actingAs($this->admin)
        ->test(Configuracion::class)
        ->call('verApartado', 'hotel')
        ->assertSee('Datos del establecimiento')
        ->assertSee('Sin descripción registrada.')
        ->assertDontSee('wire:submit="guardarHotel"');
});

/*
|--------------------------------------------------------------------------
| Edición con configuracion.editar
|--------------------------------------------------------------------------
*/

test('el formulario de edición carga los datos actuales del hotel', function () {
    InformacionHotel::current()->update([
        'nombre' => 'Hotel Viejo',
        'rfc' => 'XYZ987654321',
        'numero_habitaciones' => 25,
        'descripcion' => 'Descripción previa',
    ]);

    Livewire::actingAs($this->admin)
        ->test(Configuracion::class)
        ->call('verApartado', 'hotel')
        ->call('editarHotel')
        ->assertSet('editandoHotel', true)
        ->assertSet('nombre', 'Hotel Viejo')
        ->assertSet('rfc', 'XYZ987654321')
        ->assertSet('numero_habitaciones', '25')
        ->assertSet('descripcion', 'Descripción previa');
});

test('el super-admin guarda la información del hotel en la tabla', function () {
    Livewire::actingAs($this->admin)
        ->test(Configuracion::class)
        ->call('verApartado', 'hotel')
        ->call('editarHotel')
        ->set('nombre', 'Hotel NovaStay')
        ->set('rfc', 'ABC123456789')
        ->set('direccion', 'Av. Principal 123')
        ->set('ciudad', 'Cancún')
        ->set('telefono', '55 1234 5678')
        ->set('email', 'contacto@hotel.com')
        ->set('sitio_web', 'https://www.hotel.com')
        ->set('numero_habitaciones', '40')
        ->set('descripcion', 'Hotel frente al mar')
        ->call('guardarHotel')
        ->assertSet('editandoHotel', false)
        ->assertSet('mensajeExito', 'Información del hotel actualizada correctamente.');

    $this->assertDatabaseHas('informacion_hotel', [
        'id' => 1,
        'nombre' => 'Hotel NovaStay',
        'rfc' => 'ABC123456789',
        'direccion' => 'Av. Principal 123',
        'ciudad' => 'Cancún',
        'telefono' => '55 1234 5678',
        'email' => 'contacto@hotel.com',
        'sitio_web' => 'https://www.hotel.com',
        'numero_habitaciones' => 40,
        'descripcion' => 'Hotel frente al mar',
    ]);
});

test('los campos opcionales vacíos se guardan como nulos', function () {
    Livewire::actingAs($this->admin)
        ->test(Configuracion::class)
        ->call('editarHotel')
        ->set('nombre', 'Hotel Central')
        ->set('rfc', '')
        ->set('direccion', '')
        ->set('ciudad', '')
        ->set('telefono', '')
        ->set('email', '')
        ->set('sitio_web', '')
        ->set('numero_habitaciones', '')
        ->set('descripcion', '')
        ->call('guardarHotel')
        ->assertSet('mensajeExito', 'Información del hotel actualizada correctamente.');

    $hotel = InformacionHotel::current()->refresh();

    expect($hotel->nombre)->toBe('Hotel Central')
        ->and($hotel->rfc)->toBeNull()
        ->and($hotel->direccion)->toBeNull()
        ->and($hotel->ciudad)->toBeNull()
        ->and($hotel->telefono)->toBeNull()
        ->and($hotel->email)->toBeNull()
        ->and($hotel->sitio_web)->toBeNull()
        ->and($hotel->numero_habitaciones)->toBeNull()
        ->and($hotel->descripcion)->toBeNull();
});

test('el formulario valida el nombre obligatorio y el correo mal formado', function () {
    Livewire::actingAs($this->admin)
        ->test(Configuracion::class)
        ->call('editarHotel')
        ->set('nombre', '')
        ->set('email', 'no-es-un-correo')
        ->call('guardarHotel')
        ->assertHasErrors(['nombre', 'email']);
});

test('el formulario valida que el número de habitaciones sea entero', function () {
    Livewire::actingAs($this->admin)
        ->test(Configuracion::class)
        ->call('editarHotel')
        ->set('nombre', 'Hotel Central')
        ->set('numero_habitaciones', 'abc')
        ->call('guardarHotel')
        ->assertHasErrors(['numero_habitaciones']);
});

test('cancelar la edición vuelve a la ficha sin guardar cambios', function () {
    Livewire::actingAs($this->admin)
        ->test(Configuracion::class)
        ->call('editarHotel')
        ->set('nombre', 'Nombre que no se guarda')
        ->call('cerrarEdicionHotel')
        ->assertSet('editandoHotel', false)
        ->assertSet('nombre', '')
        ->assertDontSee('Nombre que no se guarda');

    expect(InformacionHotel::current()->nombre)->toBe('Mi hotel');
});

/*
|--------------------------------------------------------------------------
| Sin configuracion.editar
|--------------------------------------------------------------------------
*/

test('el botón de editar solo se muestra para quien tiene configuracion.editar', function () {
    $sinPermiso = User::factory()->create();
    $sinPermiso->assignRole('recepcionista');

    Livewire::actingAs($sinPermiso)
        ->test(Configuracion::class)
        ->call('verApartado', 'hotel')
        ->assertDontSee('wire:click="editarHotel"', false);

    Livewire::actingAs($this->admin)
        ->test(Configuracion::class)
        ->call('verApartado', 'hotel')
        ->assertSee('wire:click="editarHotel"', false);
});

test('una persona sin configuracion.editar no puede editar ni guardar', function () {
    InformacionHotel::current()->update(['nombre' => 'Hotel Original']);

    $sinPermiso = User::factory()->create();
    $sinPermiso->assignRole('recepcionista');

    Livewire::actingAs($sinPermiso)
        ->test(Configuracion::class)
        ->call('editarHotel')
        ->assertSet('editandoHotel', false)
        ->assertSet('mensajeError', 'No tienes permiso para modificar la configuración del hotel.');

    // Sin permiso ni siquiera se validan los campos: se corta antes.
    Livewire::actingAs($sinPermiso)
        ->test(Configuracion::class)
        ->call('guardarHotel')
        ->assertSet('mensajeError', 'No tienes permiso para modificar la configuración del hotel.')
        ->assertHasNoErrors();

    expect(InformacionHotel::current()->nombre)->toBe('Hotel Original');
});

test('un permiso denegado no deja mensaje de éxito', function () {
    $sinPermiso = User::factory()->create();
    $sinPermiso->assignRole('recepcionista');

    Livewire::actingAs($sinPermiso)
        ->test(Configuracion::class)
        ->call('guardarHotel')
        ->assertSet('mensajeExito', null);
});

/*
|--------------------------------------------------------------------------
| Duración de las notificaciones (5000 ms)
|--------------------------------------------------------------------------
*/

test('la notificación al guardar la información del hotel se oculta exactamente a los 5000 ms', function () {
    Livewire::actingAs($this->admin)
        ->test(Configuracion::class)
        ->call('editarHotel')
        ->set('nombre', 'Hotel NovaStay')
        ->call('guardarHotel')
        ->assertSet('mensajeExito', 'Información del hotel actualizada correctamente.')
        ->assertSee('Información del hotel actualizada correctamente.')
        ->assertSee('this.temporizador = setTimeout(() => { this.visible = false }, 5000)', false);
});

test('la notificación de error por permiso denegado también se oculta a los 5000 ms', function () {
    $sinPermiso = User::factory()->create();
    $sinPermiso->assignRole('recepcionista');

    Livewire::actingAs($sinPermiso)
        ->test(Configuracion::class)
        ->call('guardarHotel')
        ->assertSet('mensajeError', 'No tienes permiso para modificar la configuración del hotel.')
        ->assertSee('No tienes permiso para modificar la configuración del hotel.')
        ->assertSee('this.temporizador = setTimeout(() => { this.visible = false }, 5000)', false);
});
