<?php

use App\Http\Controllers\EstadoCuentaController;
use App\Http\Controllers\ReservaController;
use App\Http\Controllers\RoomSearchController;
use App\Http\Controllers\UsuarioController;
use App\Livewire\CheckInOut;
use App\Livewire\Clientes;
use App\Livewire\Configuracion;
use App\Livewire\Dashboard;
use App\Livewire\Empleados;
use App\Livewire\Gastos;
use App\Livewire\GestionRoles;
use App\Livewire\Habitaciones\Index as HabitacionesIndex;
use App\Livewire\Limpieza;
use App\Livewire\Pagos;
use App\Livewire\Reportes;
use App\Livewire\Reservaciones;
use App\Livewire\Servicios;
use App\Livewire\Temporadas;
use Illuminate\Support\Facades\Route;
use Livewire\Volt\Volt;

Route::get('/', function () {
    return view('welcome');
})->name('home');

Route::get('/buscar-habitaciones', [RoomSearchController::class, 'index'])->name('rooms.search');
Route::view('/nuestros-servicios', 'public-servicios')->name('servicios.public');
Route::get('/nuestras-habitaciones', [RoomSearchController::class, 'index'])->name('habitaciones.public');

Route::post('/reserva/iniciar', [ReservaController::class, 'iniciar'])->name('reserva.iniciar');

Route::middleware(['auth'])->group(function () {
    Route::get('/reserva/confirmar', [ReservaController::class, 'confirmar'])->name('reserva.confirmar');
    Route::post('/reserva', [ReservaController::class, 'store'])->name('reserva.store');
});

Route::middleware(['auth'])->group(function () {
    Route::redirect('settings', 'settings/profile');

    Volt::route('settings/profile', 'settings.profile')->name('settings.profile');
    Volt::route('settings/password', 'settings.password')->name('settings.password');
    Volt::route('settings/appearance', 'settings.appearance')->name('settings.appearance');
});

require __DIR__.'/auth.php';

Route::get('/usuarios', [UsuarioController::class, 'index'])
    ->middleware('permission:usuarios.ver')
    ->name('usuarios.index');

Route::get('/usuarios/create', [UsuarioController::class, 'create'])
    ->middleware('permission:usuarios.crear')
    ->name('usuarios.create');

Route::post('/usuarios', [UsuarioController::class, 'store'])
    ->middleware('permission:usuarios.crear')
    ->name('usuarios.store');

Route::get('/usuarios/{usuario}/edit', [UsuarioController::class, 'edit'])
    ->middleware('permission:usuarios.editar')
    ->name('usuarios.edit');

Route::put('/usuarios/{usuario}', [UsuarioController::class, 'update'])
    ->middleware('permission:usuarios.editar')
    ->name('usuarios.update');

Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard', Dashboard::class)
        ->middleware(['cliente.redirect', 'role:super-admin|gerente'])
        ->name('dashboard');

    Route::get('/reservaciones', Reservaciones::class)
        ->middleware('permission:reservaciones.ver')
        ->name('reservaciones');

    Route::get('/habitaciones', HabitacionesIndex::class)
        ->middleware('permission:habitaciones.ver')
        ->name('habitaciones');

    Route::get('/clientes', Clientes::class)
        ->middleware('permission:clientes.ver')
        ->name('clientes');

    Route::get('/checkin-checkout', CheckInOut::class)
        ->middleware('permission:checkin_checkout.ver')
        ->name('checkin-checkout');

    Route::get('/limpieza', Limpieza::class)
        ->middleware('permission:limpieza.ver')
        ->name('limpieza');

    Route::get('/pagos', Pagos::class)
        ->middleware('permission:pagos.ver')
        ->name('pagos');

    Route::get('/servicios', Servicios::class)
        ->middleware('permission:servicios.ver')
        ->name('servicios');

    Route::get('/gastos', Gastos::class)
        ->middleware('permission:gastos.ver')
        ->name('gastos');

    Route::get('/temporadas', Temporadas::class)
        ->middleware('permission:temporadas.ver')
        ->name('temporadas');

    Route::get('/reportes', Reportes::class)
        ->middleware('permission:reportes.ver')
        ->name('reportes');

    Route::get('/configuracion', Configuracion::class)
        ->middleware('permission:configuracion.ver')
        ->name('configuracion');

    Route::get('/roles', GestionRoles::class)
        ->middleware('permission:roles_permisos.ver')
        ->name('roles');

    Route::get('/mis-reservaciones', [EstadoCuentaController::class, 'index'])
        ->middleware('role:cliente')
        ->name('mis-reservaciones');

    Route::get('/mis-reservaciones/{reserva}/estado-cuenta', [EstadoCuentaController::class, 'descargar'])
        ->middleware('role:cliente')
        ->name('estado-cuenta.pdf');
});

Route::middleware(['auth', 'role:super-admin'])->group(function () {
    Route::get('/empleados', Empleados::class)->name('empleados');
});
