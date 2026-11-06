<?php

namespace App\Livewire;

use App\Models\Habitacion;
use App\Models\Limpieza;
use App\Models\Pago;
use App\Models\Reserva;
use Illuminate\View\View;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.app')]
class Dashboard extends Component
{
    public function render(): View
    {
        $totalHabitaciones = Habitacion::count();
        $habitacionesOcupadas = Habitacion::where('estado', 'Ocupada')->count();
        $reservasActivas = Reserva::whereIn('estado', ['Pendiente', 'Confirmada'])->count();

        $ingresosMes = (float) Pago::where('estado', Pago::ESTADO_COMPLETADO)
            ->whereYear('fecha_pago', now()->year)
            ->whereMonth('fecha_pago', now()->month)
            ->sum('monto');

        $ocupacion = $totalHabitaciones > 0
            ? round($habitacionesOcupadas * 100 / $totalHabitaciones)
            : 0;

        $proximasLlegadas = Reserva::with('cliente')
            ->where('check_in', '>=', now()->toDateString())
            ->whereIn('estado', ['Pendiente', 'Confirmada'])
            ->orderBy('check_in')
            ->limit(5)
            ->get();

        $tareasPendientes = Limpieza::with('habitacion')
            ->whereIn('estado', ['Pendiente', 'En Proceso'])
            ->orderBy('created_at')
            ->limit(5)
            ->get();

        return view('dashboard.index', [
            'totalHabitaciones' => $totalHabitaciones,
            'habitacionesOcupadas' => $habitacionesOcupadas,
            'reservasActivas' => $reservasActivas,
            'ingresosMes' => $ingresosMes,
            'ocupacion' => $ocupacion,
            'proximasLlegadas' => $proximasLlegadas,
            'tareasPendientes' => $tareasPendientes,
        ]);
    }
}
