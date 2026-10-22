<?php

use App\Livewire\Pagos;
use App\Models\Cliente;
use App\Models\Habitacion;
use App\Models\Limpieza as TareaLimpieza;
use App\Models\Pago;
use App\Models\Reserva;
use App\Models\TipoHabitacion;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Livewire\Livewire;

beforeEach(function () {
    $this->seed(RolesAndPermissionsSeeder::class);

    $this->admin = User::factory()->create();
    $this->admin->assignRole('super-admin');

    /**
     * Crea un folio con una habitación ocupada y una estancia de dos noches a
     * $800.00, es decir un total de $1,600.00.
     */
    $this->crearFolio = function (string $estado = 'Confirmada'): Reserva {
        $tipo = TipoHabitacion::create(['nombre' => 'Estándar', 'precio_base' => 800.00, 'capacidad' => 2]);

        $habitacion = Habitacion::create([
            'numero_habitacion' => '108',
            'tipo_habitacion_id' => $tipo->id,
            'estado' => 'Ocupada',
            'piso' => 1,
        ]);

        $cliente = Cliente::create([
            'nombre' => 'Lucía',
            'apellido' => 'Méndez',
            'email' => 'lucia.mendez@example.com',
        ]);

        $reserva = Reserva::create([
            'cliente_id' => $cliente->id,
            'user_id' => $this->admin->id,
            'check_in' => now()->subDays(2)->toDateString(),
            'check_out' => now()->toDateString(),
            'estado' => $estado,
            'monto_total' => 1600.00,
        ]);

        $reserva->habitaciones()->attach($habitacion->id, ['precio_por_noche' => 800.00]);

        return $reserva;
    };
});

test('el panel muestra el resumen financiero y el saldo pendiente del folio seleccionado', function () {
    $reserva = call_user_func($this->crearFolio);

    Livewire::actingAs($this->admin)
        ->test(Pagos::class)
        ->assertSet('reservaSeleccionadaId', $reserva->id)
        ->assertSee('Estado de cuenta')
        ->assertSee('Total hospedaje + consumos')
        ->assertSee('Saldo pendiente')
        ->assertSee('$1,600.00');
});

test('registrar un pago guarda el monto, el método y el recepcionista que lo registra', function () {
    $reserva = call_user_func($this->crearFolio);

    Livewire::actingAs($this->admin)
        ->test(Pagos::class)
        ->call('crear')
        ->assertSet('reserva_id', (string) $reserva->id)
        ->set('monto', '500.00')
        ->set('metodo_pago', 'Efectivo')
        ->call('guardar')
        ->assertHasNoErrors()
        ->assertDispatched('notificacion', mensaje: 'Pago registrado correctamente.');

    $pago = Pago::first();

    expect($pago)->not->toBeNull()
        ->and((float) $pago->monto)->toBe(500.00)
        ->and($pago->metodo_pago)->toBe('Efectivo')
        ->and($pago->estado)->toBe(Pago::ESTADO_COMPLETADO)
        ->and($pago->reserva_id)->toBe($reserva->id)
        ->and($pago->user_id)->toBe($this->admin->id);

    // El saldo refleja el abono recién capturado.
    expect($reserva->fresh()->totalPagado())->toBe(500.00)
        ->and($reserva->fresh()->saldoPendiente())->toBe(1100.00);
});

test('rechaza un abono mayor al saldo pendiente salvo que se marque como pago anticipado', function () {
    call_user_func($this->crearFolio);

    $componente = Livewire::actingAs($this->admin)
        ->test(Pagos::class)
        ->call('crear')
        ->set('monto', '2000.00')
        ->set('metodo_pago', 'Efectivo');

    $componente->call('guardar')->assertHasErrors(['monto']);

    expect(Pago::count())->toBe(0);

    $componente->set('pagoAnticipado', true)
        ->call('guardar')
        ->assertHasNoErrors();

    expect(Pago::count())->toBe(1);
});

test('una tarjeta o transferencia exigen capturar la referencia del comprobante', function () {
    call_user_func($this->crearFolio);

    Livewire::actingAs($this->admin)
        ->test(Pagos::class)
        ->call('crear')
        ->set('monto', '100.00')
        ->set('metodo_pago', 'Tarjeta')
        ->set('referencia', '')
        ->call('guardar')
        ->assertHasErrors(['referencia']);

    expect(Pago::count())->toBe(0);

    Livewire::actingAs($this->admin)
        ->test(Pagos::class)
        ->call('crear')
        ->set('monto', '100.00')
        ->set('metodo_pago', 'Transferencia')
        ->set('referencia', 'TR-9001')
        ->call('guardar')
        ->assertHasNoErrors();

    expect(Pago::first()->referencia)->toBe('TR-9001');
});

test('al liquidar el saldo de una reserva en curso se realiza el check-out automático', function () {
    $reserva = call_user_func($this->crearFolio);
    $habitacion = $reserva->habitaciones()->first();

    Livewire::actingAs($this->admin)
        ->test(Pagos::class)
        ->call('crear')
        ->set('monto', '1600.00')
        ->set('metodo_pago', 'Efectivo')
        ->call('guardar')
        ->assertHasNoErrors()
        ->assertDispatched(
            'notificacion',
            mensaje: 'Pago registrado. El saldo quedó en $0.00 y el check-out se realizó automáticamente.'
        );

    expect($reserva->refresh()->estado)->toBe(Reserva::ESTADO_FINALIZADA)
        ->and($habitacion->refresh()->estado)->toBe('Limpieza')
        ->and(TareaLimpieza::where('habitacion_id', $habitacion->id)->count())->toBe(1);
});

test('una reserva pendiente no se finaliza aunque su saldo quede en cero', function () {
    $reserva = call_user_func($this->crearFolio, 'Pendiente');

    Livewire::actingAs($this->admin)
        ->test(Pagos::class)
        ->call('crear')
        ->set('monto', '1600.00')
        ->set('metodo_pago', 'Efectivo')
        ->call('guardar')
        ->assertHasNoErrors()
        ->assertDispatched('notificacion', mensaje: 'Pago registrado correctamente.');

    expect($reserva->refresh()->estado)->toBe('Pendiente');
});

test('un pago anulado no reduce el saldo pendiente de la reserva', function () {
    $reserva = call_user_func($this->crearFolio);

    Pago::create([
        'reserva_id' => $reserva->id,
        'monto' => 1600.00,
        'metodo_pago' => 'Efectivo',
        'estado' => Pago::ESTADO_ANULADO,
        'fecha_pago' => now(),
    ]);

    expect($reserva->fresh()->totalPagado())->toBe(0.00)
        ->and($reserva->fresh()->saldoPendiente())->toBe(1600.00);
});

test('editar un pago actualiza su monto, método y referencia', function () {
    $reserva = call_user_func($this->crearFolio);

    $pago = Pago::create([
        'reserva_id' => $reserva->id,
        'monto' => 400.00,
        'metodo_pago' => 'Efectivo',
        'estado' => Pago::ESTADO_COMPLETADO,
        'fecha_pago' => now(),
    ]);

    Livewire::actingAs($this->admin)
        ->test(Pagos::class)
        ->call('editar', $pago->id)
        ->assertSet('metodo_pago', 'Efectivo')
        ->set('monto', '450.00')
        ->set('metodo_pago', 'Transferencia')
        ->set('referencia', 'TR-77')
        ->call('guardar')
        ->assertHasNoErrors()
        ->assertDispatched('notificacion', mensaje: 'Pago actualizado correctamente.');

    $pago->refresh();

    expect((float) $pago->monto)->toBe(450.00)
        ->and($pago->metodo_pago)->toBe('Transferencia')
        ->and($pago->referencia)->toBe('TR-77');
});

test('un pago puede eliminarse desde el módulo', function () {
    $reserva = call_user_func($this->crearFolio);

    $pago = Pago::create([
        'reserva_id' => $reserva->id,
        'monto' => 200.00,
        'metodo_pago' => 'Efectivo',
        'estado' => Pago::ESTADO_COMPLETADO,
        'fecha_pago' => now(),
    ]);

    Livewire::actingAs($this->admin)
        ->test(Pagos::class)
        ->call('eliminar', $pago->id)
        ->assertDispatched('notificacion', mensaje: 'Pago eliminado correctamente.');

    expect(Pago::whereKey($pago->id)->exists())->toBeFalse();
});

test('el formulario usa un select nativo estilizado y la referencia solo aparece para tarjeta o transferencia', function () {
    call_user_func($this->crearFolio);

    $html = Livewire::actingAs($this->admin)->test(Pagos::class)->html();

    expect($html)
        ->toContain('wire:model.live="metodo_pago"')
        ->toContain('w-full appearance-none rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-800 shadow-sm transition-all duration-200 focus:border-amber-500 focus:outline-none focus:ring-2 focus:ring-amber-500/20')
        ->toContain('pointer-events-none absolute right-3 top-1/2 size-4 -translate-y-1/2')
        ->toContain("\$wire.metodo_pago === 'Tarjeta' || \$wire.metodo_pago === 'Transferencia'")
        ->toContain('w-full max-w-lg rounded-2xl');
});

test('el modal de pago lista los folios de las reservaciones para poder abonar', function () {
    $reserva = call_user_func($this->crearFolio);

    $html = Livewire::actingAs($this->admin)->test(Pagos::class)->html();

    // El desplegable nativo trae la opción por defecto y el folio registrado,
    // de modo que recepción pueda elegir la reservación al registrar el pago.
    expect($html)
        ->toContain('<select id="reserva_id"')
        ->toContain('Selecciona un folio…')
        ->toContain('<option value="'.$reserva->id.'">#'.$reserva->id.' · Lucía Méndez · Confirmada</option>');
});

test('recepción descarga en PDF el estado de cuenta desde el módulo de pagos', function () {
    $reserva = call_user_func($this->crearFolio);

    $this->actingAs($this->admin)
        ->get(route('pagos.estado-cuenta', $reserva->id))
        ->assertOk()
        ->assertHeader('content-type', 'application/pdf');
});
