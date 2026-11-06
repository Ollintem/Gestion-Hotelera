<?php

namespace App\Http\Controllers;

use App\Services\Habitaciones\DisponibilidadHabitaciones;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\View\View;

class RoomSearchController extends Controller
{
    public function __construct(private readonly DisponibilidadHabitaciones $disponibilidad) {}

    public function index(Request $request): View
    {
        $validado = $request->validate([
            'check_in' => ['nullable', 'date', 'after_or_equal:today'],
            'check_out' => ['nullable', 'date', 'after:check_in'],
            'guests' => ['nullable', 'integer', 'min:1', 'max:20'],
        ]);

        $checkIn = $validado['check_in'] ?? now()->toDateString();
        $checkOut = $validado['check_out'] ?? Carbon::parse($checkIn)->addDays(3)->toDateString();
        $guests = $validado['guests'] ?? null;

        $habitaciones = $this->disponibilidad
            ->consulta($checkIn, $checkOut, $guests)
            ->get();

        $noches = (int) Carbon::parse($checkIn)->diffInDays(Carbon::parse($checkOut));

        return view('rooms.search-results', compact('checkIn', 'checkOut', 'guests', 'habitaciones', 'noches'));
    }
}
