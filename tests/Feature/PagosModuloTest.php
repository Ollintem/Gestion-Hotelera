<?php

use App\Livewire\Pagos;
use App\Models\Pago;
use App\Models\Reserva;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Livewire\Livewire;

beforeEach(function () {
    $this->seed(RolesAndPermissionsSeeder::class);

    $this->admin = User::factory()->create();
    $this->admin->assignRole('super-admin');
});

test('registra un pago para la reservación seleccionada', function () {
    $reserva = Reserva::factory()->create();

    Livewire::actingAs($this->admin)
        ->test(Pagos::class)
        ->call('crear')
        ->set('reserva_id', (string) $reserva->id)
        ->set('monto', '750.50')
        ->set('metodo_pago', 'Tarjeta')
        ->call('guardar')
        ->assertHasNoErrors()
        ->assertSet('pagina', 'index')
        ->assertSee('Pago registrado correctamente.');

    $pago = Pago::firstOrFail();

    expect($pago->reserva_id)->toBe($reserva->id)
        ->and((float) $pago->monto)->toBe(750.50)
        ->and($pago->metodo_pago)->toBe('Tarjeta');
});

test('la vista de creación liga el selector al valor actual de reserva_id', function () {
    $reserva = Reserva::factory()->create();

    Livewire::actingAs($this->admin)
        ->test(Pagos::class)
        ->call('crear')
        ->set('reserva_id', (string) $reserva->id)
        ->assertSee('wire:key="pago-reserva-'.$reserva->id.'"', false);
});
