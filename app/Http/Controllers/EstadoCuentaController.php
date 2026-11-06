<?php

namespace App\Http\Controllers;

use App\Models\Cliente;
use App\Models\Pago;
use App\Models\Reserva;
use App\Models\ReservaServicio;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\Response;

class EstadoCuentaController extends Controller
{
    use AuthorizesRequests;

    /**
     * Panel personal del cliente: perfil, reservaciones, pagos, servicios y
     * estado de cuenta. Todas las consultas se limitan a la ficha del cliente
     * autenticado; el identificador de la URL nunca decide el propietario.
     */
    public function index(): View|RedirectResponse
    {
        $cliente = $this->clienteActual();

        if (! $cliente) {
            return redirect()->route('home')->with(
                'info',
                'Crea tu primera reservación para ver tu panel de cliente.'
            );
        }

        $reservas = $cliente->reservas()
            ->with([
                'habitacionesAsignadas.habitacion.tipoHabitacion',
                'serviciosAsignados.servicio',
                'pagos',
            ])
            ->orderByDesc('check_in')
            ->orderByDesc('id')
            ->get();

        $reservaIds = $reservas->pluck('id');

        $pagos = Pago::query()
            ->whereIn('reserva_id', $reservaIds)
            ->with('reserva')
            ->orderByDesc('fecha_pago')
            ->orderByDesc('id')
            ->get();

        $servicios = ReservaServicio::query()
            ->whereIn('reserva_id', $reservaIds)
            ->with(['servicio', 'reserva'])
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->get();

        return view('cliente.mis-reservaciones', [
            'cliente' => $cliente,
            'reservas' => $reservas,
            'pagos' => $pagos,
            'servicios' => $servicios,
            'resumen' => $this->resumenEstadoCuenta($reservas),
        ]);
    }

    /**
     * Actualiza los datos de contacto del cliente y mantiene sincronizada su
     * cuenta de usuario, sin permitir correos duplicados en ninguna de las dos
     * tablas.
     */
    public function actualizarPerfil(Request $request): RedirectResponse
    {
        $cliente = $this->clienteActual();

        abort_unless($cliente !== null, 403);

        $usuario = $request->user();

        $datos = $request->validate([
            'nombre' => ['required', 'string', 'max:100'],
            'apellido' => ['required', 'string', 'max:100'],
            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($usuario->id),
                Rule::unique('clientes', 'email')->ignore($cliente->id),
            ],
            'telefono' => ['nullable', 'string', 'max:20'],
        ]);

        DB::transaction(function () use ($cliente, $usuario, $datos): void {
            $usuario->name = trim($datos['nombre'].' '.$datos['apellido']);
            $usuario->email = $datos['email'];
            $usuario->save();

            $cliente->update([
                'nombre' => $datos['nombre'],
                'apellido' => $datos['apellido'],
                'email' => $datos['email'],
                'telefono' => $datos['telefono'] ?? null,
            ]);
        });

        return redirect()
            ->route('mis-reservaciones')
            ->with('success', 'Tus datos se actualizaron correctamente.');
    }

    /**
     * Comprobante solicitado por el propio cliente. La policy valida que la
     * reservación le pertenezca antes de generar el PDF.
     */
    public function descargar(Reserva $reserva): Response
    {
        $this->authorize('view', $reserva);

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
     * @return array<string, float|int>
     */
    private function resumenEstadoCuenta($reservas): array
    {
        $vigentes = $reservas->where('estado', '!=', Reserva::ESTADO_CANCELADA);

        $cargos = round((float) $vigentes->sum(
            fn (Reserva $reserva): float => $reserva->totalConsumos(),
        ), 2);

        $pagado = round((float) $reservas->sum(
            fn (Reserva $reserva): float => $reserva->totalPagado(),
        ), 2);

        $pendiente = round((float) $vigentes->sum(
            fn (Reserva $reserva): float => $reserva->saldoPendiente(),
        ), 2);

        return [
            'cargos' => $cargos,
            'pagado' => $pagado,
            'pendiente' => $pendiente,
            'reservaciones' => $reservas->count(),
        ];
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

    private function clienteActual(): ?Cliente
    {
        return Cliente::where('user_id', auth()->id())->first();
    }
}
