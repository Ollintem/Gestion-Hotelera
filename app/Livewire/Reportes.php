<?php

namespace App\Livewire;

use App\Models\Gasto;
use App\Models\Habitacion;
use App\Models\Pago;
use App\Models\Reserva;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.app')]
class Reportes extends Component
{
    public function render(): View
    {
        $reservasPorEstado = Reserva::select('estado', DB::raw('count(*) as total'))
            ->groupBy('estado')
            ->orderBy('estado')
            ->pluck('total', 'estado');

        $pagosPorMetodo = Pago::select('metodo_pago', DB::raw('count(*) as total'), DB::raw('sum(monto) as monto'))
            ->groupBy('metodo_pago')
            ->orderByDesc('monto')
            ->get();

        $gastosPorCategoria = Gasto::select('categoria', DB::raw('sum(monto) as monto'))
            ->groupBy('categoria')
            ->orderByDesc('monto')
            ->get();

        $totalHabitaciones = Habitacion::count();
        $habitacionesOcupadas = Habitacion::where('estado', 'Ocupada')->count();

        $ingresosTotales = (float) Pago::sum('monto');
        $gastosTotales = (float) Gasto::sum('monto');

        $ultimasReservas = Reserva::with(['cliente', 'habitaciones'])
            ->latest()
            ->limit(8)
            ->get();

        return view('livewire.reportes.index', [
            'reservasPorEstado' => $reservasPorEstado,
            'pagosPorMetodo' => $pagosPorMetodo,
            'gastosPorCategoria' => $gastosPorCategoria,
            'totalHabitaciones' => $totalHabitaciones,
            'habitacionesOcupadas' => $habitacionesOcupadas,
            'ocupacion' => $totalHabitaciones > 0 ? round($habitacionesOcupadas * 100 / $totalHabitaciones) : 0,
            'ingresosTotales' => $ingresosTotales,
            'gastosTotales' => $gastosTotales,
            'balance' => $ingresosTotales - $gastosTotales,
            'ultimasReservas' => $ultimasReservas,
        ]);
    }
}
