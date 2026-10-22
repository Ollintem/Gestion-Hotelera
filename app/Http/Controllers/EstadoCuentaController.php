<?php

namespace App\Http\Controllers;

use App\Models\Cliente;
use App\Models\Pago;
use App\Models\Reserva;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\Response;

class EstadoCuentaController extends Controller
{
    public function index(): View|RedirectResponse
    {
        $cliente = Cliente::where('user_id', auth()->id())
            ->with('reservas')
            ->first();

        if (! $cliente) {
            return redirect()->route('home')->with(
                'info',
                'Crea tu primera reservación para ver su estado de cuenta.'
            );
        }

        $reservas = $cliente->reservas()
            ->with([
                'habitacionesAsignadas.habitacion.tipoHabitacion',
                'serviciosAsignados.servicio',
                'pagos',
            ])
            ->orderByDesc('check_in')
            ->get()
            ->map(function (Reserva $reserva): array {
                return $this->datosEstadoCuenta($reserva) + ['reserva' => $reserva];
            });

        return view('cliente.mis-reservaciones', [
            'cliente' => $cliente,
            'reservas' => $reservas,
        ]);
    }

    public function descargar(Reserva $reserva): Response
    {
        abort_unless($this->perteneceAlUsuario($reserva), 403);

        return $this->respuestaPdf($reserva);
    }

    /**
     * Comprobante solicitado por recepción desde el módulo de pagos.
     */
    public function descargarGestion(Reserva $reserva): Response
    {
        return $this->respuestaPdf($reserva);
    }

    private function respuestaPdf(Reserva $reserva): Response
    {
        $datos = $this->datosEstadoCuenta($reserva) + ['reserva' => $reserva];

        $pdf = Pdf::loadView('pdf.estado-cuenta', $datos)
            ->setPaper('a4');

        return $pdf->download("estado-cuenta-{$reserva->id}.pdf");
    }

    /**
     * @return array<string, mixed>
     */
    private function datosEstadoCuenta(Reserva $reserva): array
    {
        $noches = $reserva->totalNoches();
        $asignaciones = $reserva->habitacionesAsignadas()->with('habitacion.tipoHabitacion')->get();

        $tarifa = $reserva->tarifaHabitaciones();
        $gastosExtra = $reserva->serviciosAsignados()->with('servicio')->get();
        $subtotalExtras = $reserva->subtotalServicios();
        $totalConsumos = $reserva->totalConsumos();
        $pagado = $reserva->totalPagado();
        $pendiente = $reserva->saldoPendiente();
        $pagos = $reserva->pagos()
            ->where('estado', '!=', Pago::ESTADO_ANULADO)
            ->orderBy('fecha_pago')
            ->orderBy('id')
            ->get();

        return compact(
            'noches',
            'asignaciones',
            'tarifa',
            'gastosExtra',
            'subtotalExtras',
            'totalConsumos',
            'pagado',
            'pendiente',
            'pagos'
        );
    }

    private function perteneceAlUsuario(Reserva $reserva): bool
    {
        return $reserva->user_id === auth()->id()
            || $reserva->cliente?->user_id === auth()->id();
    }
}
