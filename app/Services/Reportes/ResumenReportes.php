<?php

namespace App\Services\Reportes;

use App\Models\Gasto;
use App\Models\Habitacion;
use App\Models\InformacionHotel;
use App\Models\Pago;
use App\Models\Reserva;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Calcula el resumen operativo y financiero de un periodo.
 *
 * Criterios de asignación de movimientos:
 *
 *  - Reservaciones realizadas: se cuentan por `reservas.created_at` dentro del
 *    periodo. Los cargos de habitación del periodo se calculan sobre esas
 *    mismas reservaciones.
 *  - Check-in y check-out: se cuentan por la fecha real capturada en
 *    `fecha_hora_checkin` y `fecha_hora_checkout`.
 *  - Ingresos: solo pagos en estado «Completado», por `pagos.fecha_pago`. Un
 *    pago anulado o pendiente nunca suma como dinero recibido.
 *  - Gastos: por `gastos.fecha_gasto`.
 *  - Servicios vendidos: por `reserva_servicio.created_at`; se suman las
 *    cantidades y los subtotales capturados en el folio.
 *  - Saldo pendiente: cargos de las reservaciones realizadas en el periodo
 *    (tarifa de habitación más consumos del folio) menos todos sus pagos
 *    completados. Se calcula por reservación para que un anticipo excedente no
 *    cancele la deuda de otra reservación.
 *  - Ocupación: noches-habitación de reservaciones no canceladas que se
 *    traslapan con el periodo, sobre la capacidad total del hotel en esas
 *    fechas.
 *
 * El resultado es un arreglo que comparten la pantalla y el PDF, de modo que
 * ambos muestran exactamente las mismas cifras.
 */
class ResumenReportes
{
    public const DIARIO = 'diario';

    public const SEMANAL = 'semanal';

    public const MENSUAL = 'mensual';

    /**
     * @var array<string, string>
     */
    public const PERIODOS = [
        self::DIARIO => 'Diario',
        self::SEMANAL => 'Semanal',
        self::MENSUAL => 'Mensual',
    ];

    /**
     * @return array<string, mixed>
     */
    public function generar(
        string $periodo,
        ?string $fecha = null,
        ?string $semana = null,
        ?int $mes = null,
        ?int $anio = null,
    ): array {
        $periodo = array_key_exists($periodo, self::PERIODOS) ? $periodo : self::MENSUAL;

        [$inicio, $fin, $archivo] = $this->rango($periodo, $fecha, $semana, $mes, $anio);

        $inicioDia = $inicio->copy()->startOfDay();
        $finDia = $fin->copy()->endOfDay();

        $reservasBase = Reserva::query()
            ->whereBetween('created_at', [$inicioDia, $finDia]);

        $totalReservaciones = (int) (clone $reservasBase)->count();
        $reservacionesCanceladas = (int) (clone $reservasBase)
            ->where('estado', Reserva::ESTADO_CANCELADA)
            ->count();

        $checkins = (int) Reserva::query()
            ->whereBetween('fecha_hora_checkin', [$inicioDia, $finDia])
            ->count();

        $checkouts = (int) Reserva::query()
            ->whereBetween('fecha_hora_checkout', [$inicioDia, $finDia])
            ->count();

        $pagosBase = Pago::query()
            ->where('estado', Pago::ESTADO_COMPLETADO)
            ->whereBetween('fecha_pago', [$inicioDia, $finDia]);

        $pagosCantidad = (int) (clone $pagosBase)->count();
        $ingresos = round((float) (clone $pagosBase)->sum('monto'), 2);

        $pagosPorMetodo = (clone $pagosBase)
            ->select('metodo_pago', DB::raw('COUNT(*) as total'), DB::raw('SUM(monto) as monto'))
            ->groupBy('metodo_pago')
            ->orderByDesc('monto')
            ->get();

        $gastosBase = Gasto::query()
            ->whereDate('fecha_gasto', '>=', $inicio->toDateString())
            ->whereDate('fecha_gasto', '<=', $fin->toDateString());

        $gastos = round((float) (clone $gastosBase)->sum('monto'), 2);

        $gastosPorCategoria = (clone $gastosBase)
            ->select('categoria', DB::raw('SUM(monto) as monto'))
            ->groupBy('categoria')
            ->orderByDesc('monto')
            ->get();

        $serviciosBase = DB::table('reserva_servicio as rs')
            ->whereBetween('rs.created_at', [$inicioDia, $finDia]);

        $serviciosCantidad = (int) (clone $serviciosBase)->sum('rs.cantidad');
        $serviciosTotal = round((float) (clone $serviciosBase)->sum('rs.subtotal'), 2);

        $serviciosTop = (clone $serviciosBase)
            ->join('servicios as s', 's.id', '=', 'rs.servicio_id')
            ->select(
                's.nombre',
                DB::raw('SUM(rs.cantidad) as cantidad'),
                DB::raw('SUM(rs.subtotal) as total')
            )
            ->groupBy('s.id', 's.nombre')
            ->orderByDesc('cantidad')
            ->limit(8)
            ->get();

        $reservasPorEstado = (clone $reservasBase)
            ->select('estado', DB::raw('COUNT(*) as total'))
            ->groupBy('estado')
            ->orderBy('estado')
            ->pluck('total', 'estado');

        $cargosReservaciones = $this->cargosHabitacion($inicioDia, $finDia);
        $cargosServicios = $serviciosTotal;
        $saldoPendiente = $this->saldoPendiente($inicioDia, $finDia);

        [$totalHabitaciones, $habitacionesOcupadas, $habitacionesDisponibles, $ocupacion, $desglose]
            = $this->ocupacionYDesglose($inicio, $fin, $inicioDia, $finDia, $pagosBase, $gastosBase, $serviciosBase);

        $resultadoNeto = round($ingresos - $gastos, 2);

        return [
            'periodo' => $periodo,
            'periodoEtiqueta' => self::PERIODOS[$periodo],
            'titulo' => 'Reporte '.strtolower(self::PERIODOS[$periodo]),
            'archivo' => $archivo,
            'inicio' => $inicio,
            'fin' => $fin,
            'desde' => $inicio->format('d/m/Y'),
            'hasta' => $fin->format('d/m/Y'),
            'rangoTexto' => $this->rangoTexto($periodo, $inicio, $fin),
            'hotel' => $this->nombreHotel(),
            'generadoEl' => now(),
            'indicadores' => [
                'totalReservaciones' => $totalReservaciones,
                'reservacionesCanceladas' => $reservacionesCanceladas,
                'checkins' => $checkins,
                'checkouts' => $checkouts,
                'habitacionesOcupadas' => $habitacionesOcupadas,
                'habitacionesDisponibles' => $habitacionesDisponibles,
                'totalHabitaciones' => $totalHabitaciones,
                'ocupacion' => $ocupacion,
                'ingresos' => $ingresos,
                'gastos' => $gastos,
                'resultadoNeto' => $resultadoNeto,
                'serviciosCantidad' => $serviciosCantidad,
                'serviciosTotal' => $serviciosTotal,
                'saldoPendiente' => $saldoPendiente,
                'cargosReservaciones' => $cargosReservaciones,
                'cargosServicios' => $cargosServicios,
                'cargosTotales' => round($cargosReservaciones + $cargosServicios, 2),
                'pagosCantidad' => $pagosCantidad,
            ],
            'reservasPorEstado' => $reservasPorEstado,
            'pagosPorMetodo' => $pagosPorMetodo,
            'gastosPorCategoria' => $gastosPorCategoria,
            'serviciosTop' => $serviciosTop,
            'desglose' => $desglose,
            'graficas' => [
                'etiquetas' => $desglose->pluck('fechaCorta')->all(),
                'ingresos' => $desglose->pluck('ingresos')->all(),
                'gastos' => $desglose->pluck('gastos')->all(),
                'reservaciones' => $desglose->pluck('reservaciones')->all(),
                'servicios' => $desglose->pluck('servicios')->all(),
                'ocupacion' => $desglose->pluck('ocupacion')->all(),
                'reservasEstado' => [
                    'etiquetas' => $reservasPorEstado->keys()->values()->all(),
                    'valores' => $reservasPorEstado->values()->map(fn ($total) => (int) $total)->all(),
                ],
                'pagosMetodo' => [
                    'etiquetas' => $pagosPorMetodo->pluck('metodo_pago')->values()->all(),
                    'valores' => $pagosPorMetodo->pluck('monto')->map(fn ($monto) => (float) $monto)->values()->all(),
                    'cantidades' => $pagosPorMetodo->pluck('total')->map(fn ($total) => (int) $total)->values()->all(),
                ],
                'gastosCategoria' => [
                    'etiquetas' => $gastosPorCategoria->pluck('categoria')->values()->all(),
                    'valores' => $gastosPorCategoria->pluck('monto')->map(fn ($monto) => (float) $monto)->values()->all(),
                ],
                'serviciosTop' => [
                    'etiquetas' => $serviciosTop->pluck('nombre')->values()->all(),
                    'valores' => $serviciosTop->pluck('cantidad')->map(fn ($cantidad) => (int) $cantidad)->values()->all(),
                    'montos' => $serviciosTop->pluck('total')->map(fn ($total) => (float) $total)->values()->all(),
                ],
            ],
        ];
    }

    /**
     * @return array{0: Carbon, 1: Carbon, 2: string}
     */
    private function rango(
        string $periodo,
        ?string $fecha,
        ?string $semana,
        ?int $mes,
        ?int $anio,
    ): array {
        if ($periodo === self::DIARIO) {
            $dia = now();

            if (filled($fecha)) {
                try {
                    $dia = Carbon::parse($fecha);
                } catch (\Throwable) {
                    $dia = now();
                }
            }

            return [$dia->copy()->startOfDay(), $dia->copy()->endOfDay(), 'diario-'.$dia->format('Y-m-d')];
        }

        if ($periodo === self::SEMANAL) {
            $referencia = now();

            if (filled($semana) && preg_match('/^(\d{4})-W(\d{1,2})$/', $semana, $partes) === 1) {
                $referencia = Carbon::now()->setISODate((int) $partes[1], (int) $partes[2]);
            }

            $inicio = $referencia->copy()->startOfWeek(Carbon::MONDAY)->startOfDay();
            $fin = $referencia->copy()->endOfWeek(Carbon::SUNDAY)->endOfDay();

            return [$inicio, $fin, 'semanal-'.$inicio->format('o-\WW')];
        }

        $mes = $mes !== null && $mes >= 1 && $mes <= 12 ? $mes : (int) now()->month;
        $anio = $anio !== null && $anio >= 2000 && $anio <= 2100 ? $anio : (int) now()->year;

        $inicio = Carbon::createFromDate($anio, $mes, 1)->startOfDay();
        $fin = $inicio->copy()->endOfMonth()->endOfDay();

        return [$inicio, $fin, 'mensual-'.$inicio->format('Y-m')];
    }

    private function rangoTexto(string $periodo, Carbon $inicio, Carbon $fin): string
    {
        if ($periodo === self::DIARIO) {
            return $inicio->copy()->locale('es')->translatedFormat('l, d \de F \de Y');
        }

        return $inicio->format('d/m/Y').' al '.$fin->format('d/m/Y');
    }

    private function nombreHotel(): string
    {
        return (string) (InformacionHotel::query()
            ->find(InformacionHotel::REGISTRO_UNICO)?->nombre
            ?: 'NovaStay');
    }

    private function cargosHabitacion(Carbon $inicio, Carbon $fin): float
    {
        $noches = $this->expresionNoches('r');

        $tarifa = DB::table('reserva_habitacion')
            ->select('reserva_id', DB::raw('SUM(precio_por_noche) as tarifa_noche'))
            ->groupBy('reserva_id');

        $total = DB::table('reservas as r')
            ->leftJoinSub($tarifa, 'tarifa', 'tarifa.reserva_id', '=', 'r.id')
            ->whereBetween('r.created_at', [$inicio, $fin])
            ->where('r.estado', '<>', Reserva::ESTADO_CANCELADA)
            ->selectRaw(
                'COALESCE(SUM(CASE WHEN COALESCE(tarifa.tarifa_noche, 0) > 0
                    THEN tarifa.tarifa_noche * CASE WHEN '.$noches.' > 0 THEN '.$noches.' ELSE 0 END
                    ELSE r.monto_total END), 0) as total'
            )
            ->value('total');

        return round((float) ($total ?? 0), 2);
    }

    private function saldoPendiente(Carbon $inicio, Carbon $fin): float
    {
        $noches = $this->expresionNoches('r');

        $tarifa = DB::table('reserva_habitacion')
            ->select('reserva_id', DB::raw('SUM(precio_por_noche) as tarifa_noche'))
            ->groupBy('reserva_id');

        $servicios = DB::table('reserva_servicio')
            ->select('reserva_id', DB::raw('SUM(subtotal) as total'))
            ->groupBy('reserva_id');

        $pagos = DB::table('pagos')
            ->select('reserva_id', DB::raw('SUM(monto) as total'))
            ->where('estado', Pago::ESTADO_COMPLETADO)
            ->groupBy('reserva_id');

        $sql = "
            SELECT COALESCE(SUM(CASE WHEN cargos > pagado THEN cargos - pagado ELSE 0 END), 0) AS saldo
            FROM (
                SELECT
                    (CASE WHEN COALESCE(tarifa.tarifa_noche, 0) > 0
                        THEN tarifa.tarifa_noche * CASE WHEN {$noches} > 0 THEN {$noches} ELSE 0 END
                        ELSE r.monto_total END)
                    + COALESCE(servicios.total, 0) AS cargos,
                    COALESCE(pagos.total, 0) AS pagado
                FROM reservas r
                LEFT JOIN (...tarifa...) tarifa ON tarifa.reserva_id = r.id
                LEFT JOIN (...servicios...) servicios ON servicios.reserva_id = r.id
                LEFT JOIN (...pagos...) pagos ON pagos.reserva_id = r.id
                WHERE r.created_at BETWEEN ? AND ?
                  AND r.estado <> ?
            ) AS resumen
        ";

        $sql = str_replace(
            ['...tarifa...', '...servicios...', '...pagos...'],
            [
                'SELECT reserva_id, SUM(precio_por_noche) AS tarifa_noche FROM reserva_habitacion GROUP BY reserva_id',
                'SELECT reserva_id, SUM(subtotal) AS total FROM reserva_servicio GROUP BY reserva_id',
                "SELECT reserva_id, SUM(monto) AS total FROM pagos WHERE estado = '".Pago::ESTADO_COMPLETADO."' GROUP BY reserva_id",
            ],
            $sql
        );

        $resultado = DB::selectOne($sql, [$inicio, $fin, Reserva::ESTADO_CANCELADA]);

        return round((float) ($resultado->saldo ?? 0), 2);
    }

    private function expresionNoches(string $alias): string
    {
        return match (DB::connection()->getDriverName()) {
            'sqlite' => "CAST(julianday({$alias}.check_out) - julianday({$alias}.check_in) AS INTEGER)",
            'mysql', 'mariadb' => "DATEDIFF({$alias}.check_out, {$alias}.check_in)",
            'pgsql' => "({$alias}.check_out::date - {$alias}.check_in::date)",
            default => "CAST(({$alias}.check_out - {$alias}.check_in) AS INTEGER)",
        };
    }

    /**
     * @return array{0: int, 1: int, 2: int, 3: float, 4: Collection<int, array<string, mixed>>}
     */
    private function ocupacionYDesglose(
        Carbon $inicio,
        Carbon $fin,
        Carbon $inicioDia,
        Carbon $finDia,
        $pagosBase,
        $gastosBase,
        $serviciosBase,
    ): array {
        $totalHabitaciones = (int) Habitacion::count();

        $reservasConHabitaciones = Reserva::query()
            ->where('estado', '<>', Reserva::ESTADO_CANCELADA)
            ->whereDate('check_in', '<=', $fin->toDateString())
            ->whereDate('check_out', '>', $inicio->toDateString())
            ->withCount('habitacionesAsignadas')
            ->get(['id', 'check_in', 'check_out']);

        $reservasPorDia = Reserva::query()
            ->whereBetween('created_at', [$inicioDia, $finDia])
            ->selectRaw('DATE(created_at) as dia, COUNT(*) as total')
            ->groupBy('dia')
            ->pluck('total', 'dia');

        $checkinsPorDia = Reserva::query()
            ->whereNotNull('fecha_hora_checkin')
            ->whereBetween('fecha_hora_checkin', [$inicioDia, $finDia])
            ->selectRaw('DATE(fecha_hora_checkin) as dia, COUNT(*) as total')
            ->groupBy('dia')
            ->pluck('total', 'dia');

        $checkoutsPorDia = Reserva::query()
            ->whereNotNull('fecha_hora_checkout')
            ->whereBetween('fecha_hora_checkout', [$inicioDia, $finDia])
            ->selectRaw('DATE(fecha_hora_checkout) as dia, COUNT(*) as total')
            ->groupBy('dia')
            ->pluck('total', 'dia');

        $pagosPorDia = (clone $pagosBase)
            ->selectRaw('DATE(fecha_pago) as dia, COUNT(*) as cantidad, SUM(monto) as total')
            ->groupBy('dia')
            ->get()
            ->keyBy('dia');

        $gastosPorDia = (clone $gastosBase)
            ->selectRaw('DATE(fecha_gasto) as dia, SUM(monto) as total')
            ->groupBy('dia')
            ->pluck('total', 'dia');

        $serviciosPorDia = (clone $serviciosBase)
            ->selectRaw('DATE(rs.created_at) as dia, SUM(rs.cantidad) as cantidad, SUM(rs.subtotal) as total')
            ->groupBy('dia')
            ->get()
            ->keyBy('dia');

        $desglose = collect();
        $nochesOcupadas = 0;

        for ($dia = $inicio->copy()->startOfDay(); $dia->lessThanOrEqualTo($fin); $dia->addDay()) {
            $clave = $dia->toDateString();
            $ocupadasDia = $this->habitacionesOcupadasEn($reservasConHabitaciones, $dia);
            $nochesOcupadas += $ocupadasDia;

            $pagoDia = $pagosPorDia->get($clave);
            $servicioDia = $serviciosPorDia->get($clave);

            $desglose->push([
                'fecha' => $dia->copy(),
                'fechaCorta' => $dia->format('d/m'),
                'fechaTexto' => $dia->copy()->locale('es')->translatedFormat('D d/m'),
                'reservaciones' => (int) ($reservasPorDia[$clave] ?? 0),
                'checkins' => (int) ($checkinsPorDia[$clave] ?? 0),
                'checkouts' => (int) ($checkoutsPorDia[$clave] ?? 0),
                'ingresos' => round((float) ($pagoDia->total ?? 0), 2),
                'pagos' => (int) ($pagoDia->cantidad ?? 0),
                'gastos' => round((float) ($gastosPorDia[$clave] ?? 0), 2),
                'servicios' => (int) ($servicioDia->cantidad ?? 0),
                'serviciosTotal' => round((float) ($servicioDia->total ?? 0), 2),
                'ocupadas' => $ocupadasDia,
                'disponibles' => max(0, $totalHabitaciones - $ocupadasDia),
                'ocupacion' => $totalHabitaciones > 0
                    ? round($ocupadasDia * 100 / $totalHabitaciones, 1)
                    : 0.0,
            ]);
        }

        $dias = max(1, $inicio->copy()->startOfDay()->diffInDays($fin->copy()->startOfDay()) + 1);
        $capacidad = $totalHabitaciones * $dias;
        $ocupacion = $capacidad > 0 ? round($nochesOcupadas * 100 / $capacidad, 1) : 0.0;

        if ($dias === 1) {
            $ocupadas = (int) ($desglose->first()['ocupadas'] ?? 0);
            $disponibles = max(0, $totalHabitaciones - $ocupadas);
        } else {
            $ocupadas = $nochesOcupadas;
            $disponibles = max(0, $capacidad - $nochesOcupadas);
        }

        return [$totalHabitaciones, $ocupadas, $disponibles, $ocupacion, $desglose];
    }

    private function habitacionesOcupadasEn(Collection $reservas, Carbon $dia): int
    {
        $ocupadas = 0;

        foreach ($reservas as $reserva) {
            if ($dia->greaterThanOrEqualTo($reserva->check_in)
                && $dia->lessThan($reserva->check_out)) {
                $ocupadas += (int) ($reserva->habitaciones_asignadas_count ?? 0);
            }
        }

        return $ocupadas;
    }
}
