<?php

namespace App\Http\Controllers;

use App\Http\Requests\Reservaciones\IniciarReservaRequest;
use App\Http\Requests\Reservaciones\StoreReservaRequest;
use App\Models\Cliente;
use App\Models\Habitacion;
use App\Services\Habitaciones\DisponibilidadHabitaciones;
use App\Services\Reservaciones\CrearReserva;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Carbon;
use Illuminate\View\View;

class ReservaController extends Controller
{
    public function __construct(
        private readonly CrearReserva $crearReserva,
        private readonly DisponibilidadHabitaciones $disponibilidad,
    ) {}

    public function iniciar(IniciarReservaRequest $request): RedirectResponse
    {
        $validado = $request->validated();

        if (! $this->disponibilidad->estaDisponible(
            (int) $validado['habitacion_id'],
            $validado['check_in'],
            $validado['check_out'],
        )) {
            return back()
                ->withErrors(['habitacion_id' => 'La habitacion ya no esta disponible para las fechas seleccionadas.'])
                ->withInput();
        }

        session()->put('reserva.pendiente', $validado);

        if (! auth()->check()) {
            $accion = $validado['accion'] ?? 'login';

            return redirect($accion === 'registro'
                ? route('register')
                : route('login'))
                ->with('aviso', 'Inicia sesion o crea una cuenta para completar tu reservacion.');
        }

        return redirect()->route('reserva.confirmar');
    }

    public function confirmar(): View|RedirectResponse
    {
        $datos = session('reserva.pendiente');

        abort_unless($datos, 404);

        $habitacion = Habitacion::with('tipoHabitacion')->findOrFail($datos['habitacion_id']);
        $checkIn = Carbon::parse($datos['check_in']);
        $checkOut = Carbon::parse($datos['check_out']);
        $noches = (int) $checkIn->diffInDays($checkOut);
        $huespedes = (int) ($datos['guests'] ?? 1);
        $capacidad = (int) ($habitacion->tipoHabitacion?->capacidad ?? 0);

        if ($capacidad > 0 && $huespedes > $capacidad) {
            return redirect()
                ->route('habitaciones.public')
                ->with('error', "La habitacion admite hasta {$capacidad} huespedes.");
        }

        if (! $this->disponibilidad->estaDisponible($habitacion->id, $checkIn, $checkOut)) {
            session()->forget('reserva.pendiente');

            return redirect()
                ->route('habitaciones.public')
                ->with('error', 'La habitacion ya no esta disponible para las fechas seleccionadas.');
        }

        $precioPorNoche = (float) $habitacion->tipoHabitacion->precio_base;
        $total = round($precioPorNoche * $noches, 2);

        return view('reserva.confirmar', compact(
            'datos',
            'habitacion',
            'checkIn',
            'checkOut',
            'noches',
            'precioPorNoche',
            'total'
        ));
    }

    public function store(StoreReservaRequest $request): RedirectResponse
    {
        $datos = $request->validated();

        $usuario = auth()->user();
        $cliente = Cliente::firstOrCreate(
            ['email' => $usuario->email],
            [
                'user_id' => $usuario->id,
                'nombre' => $usuario->name,
                'apellido' => '',
            ]
        );

        if ($cliente->user_id !== $usuario->id) {
            $cliente->user_id = $usuario->id;
            $cliente->save();
        }

        $this->crearReserva->ejecutar(
            $cliente->id,
            (int) $datos['habitacion_id'],
            $datos['check_in'],
            $datos['check_out'],
            (int) ($datos['guests'] ?? 1),
            $usuario->id,
        );

        session()->forget('reserva.pendiente');

        return redirect()->route('home')->with(
            'success',
            'Tu reservacion ha sido registrada. El hotel confirmara tu llegada.'
        );
    }
}
