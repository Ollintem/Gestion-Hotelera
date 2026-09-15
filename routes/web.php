<?php

use App\Http\Controllers\UsuarioController;
use App\Livewire\CheckInOut;
use App\Livewire\Clientes;
use App\Livewire\Configuracion;
use App\Livewire\Dashboard;
use App\Livewire\Empleados;
use App\Livewire\Gastos;
use App\Livewire\Habitaciones;
use App\Livewire\Limpieza;
use App\Livewire\Pagos;
use App\Livewire\Reportes;
use App\Livewire\Reservaciones;
use App\Livewire\RolesPermisos;
use App\Livewire\Servicios;
use App\Livewire\Temporadas;
use Illuminate\Support\Facades\Route;
use Livewire\Volt\Volt;

Route::get('/', function () {
    return view('welcome');
})->name('inicio');

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
    Route::get('/dashboard', Dashboard::class)->name('dashboard');
    Route::get('/reservaciones', Reservaciones::class)->name('reservaciones');
    Route::get('/habitaciones', Habitaciones::class)->name('habitaciones');
    Route::get('/clientes', Clientes::class)->name('clientes');
    Route::get('/checkin-checkout', CheckInOut::class)->name('checkin-checkout');
    Route::get('/limpieza', Limpieza::class)->name('limpieza');
    Route::get('/pagos', Pagos::class)->name('pagos');
    Route::get('/servicios', Servicios::class)->name('servicios');
    Route::get('/gastos', Gastos::class)->name('gastos');
    Route::get('/temporadas', Temporadas::class)->name('temporadas');
    Route::get('/reportes', Reportes::class)->name('reportes');
    Route::get('/configuracion', Configuracion::class)->name('configuracion');
});

Route::middleware(['auth', 'role:super-admin'])->group(function () {
    Route::get('/roles-permisos', RolesPermisos::class)->name('roles-permisos');
    Route::get('/empleados', Empleados::class)->name('empleados');
});
