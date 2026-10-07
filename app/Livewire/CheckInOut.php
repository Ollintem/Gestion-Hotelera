<?php

namespace App\Livewire;

use App\Models\Limpieza as TareaLimpieza;
use App\Models\Reserva;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.app')]
class CheckInOut extends Component
{
    public ?string $mensajeExito = null;

    public function render(): View
    {
        return view('checkin-checkout.index', [
            'porAtender' => Reserva::with(['cliente', 'habitaciones'])
                ->whereIn('estado', ['Pendiente', 'Confirmada'])
                ->orderBy('check_in')
                ->get(),
            'historial' => Reserva::with(['cliente', 'habitaciones'])
                ->whereIn('estado', ['Finalizada', 'Cancelada'])
                ->latest()
                ->limit(10)
                ->get(),
        ]);
    }

    public function checkIn(int $id): void
    {
        $reserva = Reserva::with('habitaciones')->findOrFail($id);
        $reserva->update(['estado' => 'Confirmada']);

        foreach ($reserva->habitaciones as $habitacion) {
            $habitacion->update(['estado' => 'Ocupada']);
        }

        $this->mensajeExito = 'Check-in registrado correctamente.';
    }

    public function checkOut(int $id): void
    {
        DB::transaction(function () use ($id) {
            $reserva = Reserva::with('habitaciones')->findOrFail($id);

            foreach ($reserva->habitaciones as $habitacion) {
                $habitacion->update(['estado' => 'Limpieza']);

                TareaLimpieza::firstOrCreate(
                    ['habitacion_id' => $habitacion->id],
                    [
                        'user_id' => auth()->id(),
                        'estado' => 'Pendiente',
                        'notas' => 'Check-out de la reserva #'.$reserva->id,
                    ]
                );
            }

            $reserva->update(['estado' => Reserva::ESTADO_FINALIZADA]);
        });

        $this->mensajeExito = 'Check-out registrado correctamente. Las habitaciones pasaron a limpieza.';
    }

    public function cancelar(int $id): void
    {
        $reserva = Reserva::with('habitaciones')->findOrFail($id);
        $reserva->update(['estado' => 'Cancelada']);

        foreach ($reserva->habitaciones as $habitacion) {
            $habitacion->update(['estado' => 'Disponible']);
        }

        $this->mensajeExito = 'Reservación cancelada correctamente.';
    }
}
