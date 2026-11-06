<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>{{ $titulo }} · {{ $hotel }}</title>
    <style>
        * { box-sizing: border-box; }
        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 11px;
            color: #1e293b;
            line-height: 1.45;
            margin: 0;
            padding: 22px;
        }
        .encabezado {
            border-bottom: 3px solid #d97706;
            padding-bottom: 12px;
            margin-bottom: 18px;
        }
        .encabezado h1 {
            margin: 0;
            font-size: 21px;
            color: #0f172a;
        }
        .encabezado p {
            margin: 3px 0 0;
            font-size: 10px;
            color: #64748b;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        .meta {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 16px;
        }
        .meta td {
            padding: 4px 8px;
            font-size: 10px;
            vertical-align: top;
        }
        .meta .etiqueta {
            width: 18%;
            color: #64748b;
            font-weight: bold;
        }
        .titulo-seccion {
            background: #f1f5f9;
            border-left: 4px solid #d97706;
            padding: 6px 10px;
            font-size: 12px;
            font-weight: bold;
            color: #0f172a;
            margin: 16px 0 8px;
        }
        table.kpis {
            width: 100%;
            border-collapse: collapse;
        }
        table.kpis td {
            width: 25%;
            border: 1px solid #e2e8f0;
            padding: 8px;
            vertical-align: top;
        }
        .kpi-etiqueta {
            font-size: 9px;
            color: #64748b;
            text-transform: uppercase;
            letter-spacing: .5px;
            font-weight: bold;
        }
        .kpi-valor {
            display: block;
            margin-top: 4px;
            font-size: 16px;
            font-weight: bold;
            color: #0f172a;
        }
        .kpi-detalle {
            display: block;
            margin-top: 3px;
            font-size: 9px;
            color: #64748b;
        }
        table.detalle {
            width: 100%;
            border-collapse: collapse;
            margin-top: 4px;
        }
        table.detalle th {
            background: #0f172a;
            color: #ffffff;
            text-align: left;
            font-size: 9px;
            padding: 6px 6px;
            text-transform: uppercase;
            letter-spacing: .4px;
        }
        table.detalle td {
            padding: 5px 6px;
            font-size: 9px;
            border-bottom: 1px solid #e2e8f0;
        }
        table.detalle .num { text-align: right; }
        table.detalle tfoot td {
            background: #f8fafc;
            font-weight: bold;
            color: #0f172a;
        }
        .ingreso { color: #047857; font-weight: bold; }
        .egreso { color: #be123c; font-weight: bold; }
        .pie {
            margin-top: 26px;
            padding-top: 10px;
            border-top: 1px solid #e2e8f0;
            font-size: 9px;
            color: #94a3b8;
            text-align: center;
        }
        .marca-agua {
            text-align: center;
            margin-top: 38px;
            font-size: 9px;
            color: #cbd5e1;
            letter-spacing: 2px;
            text-transform: uppercase;
        }
        .dos-columnas {
            width: 100%;
            border-collapse: collapse;
        }
        .dos-columnas > tbody > tr > td {
            width: 50%;
            vertical-align: top;
            padding-right: 10px;
        }
        .dos-columnas > tbody > tr > td:last-child {
            padding-right: 0;
            padding-left: 10px;
        }
    </style>
</head>
<body>
    <div class="encabezado">
        <h1>{{ $hotel }}</h1>
        <p>{{ $titulo }} &middot; Generado el {{ $generadoEl->format('d/m/Y H:i') }}</p>
    </div>

    <table class="meta">
        <tr>
            <td class="etiqueta">Tipo de reporte</td>
            <td>{{ $titulo }}</td>
            <td class="etiqueta">Periodo consultado</td>
            <td>{{ $rangoTexto }}</td>
        </tr>
        <tr>
            <td class="etiqueta">Desde</td>
            <td>{{ $desde }}</td>
            <td class="etiqueta">Hasta</td>
            <td>{{ $hasta }}</td>
        </tr>
    </table>

    <div class="titulo-seccion">Indicadores del periodo</div>
    <table class="kpis">
        <tr>
            <td>
                <span class="kpi-etiqueta">Ocupación</span>
                <span class="kpi-valor">{{ number_format((float) $indicadores['ocupacion'], 1) }}%</span>
                <span class="kpi-detalle">{{ number_format($indicadores['habitacionesOcupadas']) }} ocupadas · {{ number_format($indicadores['habitacionesDisponibles']) }} disponibles</span>
            </td>
            <td>
                <span class="kpi-etiqueta">Ingresos por pagos</span>
                <span class="kpi-valor ingreso">${{ number_format((float) $indicadores['ingresos'], 2) }}</span>
                <span class="kpi-detalle">{{ number_format($indicadores['pagosCantidad']) }} pagos completados</span>
            </td>
            <td>
                <span class="kpi-etiqueta">Gastos registrados</span>
                <span class="kpi-valor egreso">${{ number_format((float) $indicadores['gastos'], 2) }}</span>
                <span class="kpi-detalle">Egresos del periodo</span>
            </td>
            <td>
                <span class="kpi-etiqueta">Resultado neto</span>
                <span class="kpi-valor {{ (float) $indicadores['resultadoNeto'] >= 0 ? 'ingreso' : 'egreso' }}">${{ number_format((float) $indicadores['resultadoNeto'], 2) }}</span>
                <span class="kpi-detalle">Ingresos menos gastos</span>
            </td>
        </tr>
        <tr>
            <td>
                <span class="kpi-etiqueta">Reservaciones realizadas</span>
                <span class="kpi-valor">{{ number_format($indicadores['totalReservaciones']) }}</span>
                <span class="kpi-detalle">{{ number_format($indicadores['reservacionesCanceladas']) }} canceladas</span>
            </td>
            <td>
                <span class="kpi-etiqueta">Check-in / Check-out</span>
                <span class="kpi-valor">{{ number_format($indicadores['checkins']) }} / {{ number_format($indicadores['checkouts']) }}</span>
                <span class="kpi-detalle">Llegadas y salidas reales</span>
            </td>
            <td>
                <span class="kpi-etiqueta">Servicios vendidos</span>
                <span class="kpi-valor">{{ number_format($indicadores['serviciosCantidad']) }}</span>
                <span class="kpi-detalle">${{ number_format((float) $indicadores['serviciosTotal'], 2) }} en cargos</span>
            </td>
            <td>
                <span class="kpi-etiqueta">Saldo pendiente de cobro</span>
                <span class="kpi-valor {{ (float) $indicadores['saldoPendiente'] > 0 ? 'egreso' : 'ingreso' }}">${{ number_format((float) $indicadores['saldoPendiente'], 2) }}</span>
                <span class="kpi-detalle">Cargos del periodo: ${{ number_format((float) $indicadores['cargosTotales'], 2) }}</span>
            </td>
        </tr>
    </table>

    <div class="titulo-seccion">Resumen financiero</div>
    <table class="detalle">
        <thead>
            <tr>
                <th>Concepto</th>
                <th class="num">Importe</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>Cargos por habitaciones de reservaciones del periodo</td>
                <td class="num">${{ number_format((float) $indicadores['cargosReservaciones'], 2) }}</td>
            </tr>
            <tr>
                <td>Cargos por servicios vendidos</td>
                <td class="num">${{ number_format((float) $indicadores['cargosServicios'], 2) }}</td>
            </tr>
            <tr>
                <td><strong>Total de cargos del periodo</strong></td>
                <td class="num"><strong>${{ number_format((float) $indicadores['cargosTotales'], 2) }}</strong></td>
            </tr>
            <tr>
                <td>Ingresos efectivamente recibidos (pagos completados)</td>
                <td class="num ingreso">${{ number_format((float) $indicadores['ingresos'], 2) }}</td>
            </tr>
            <tr>
                <td>Gastos registrados</td>
                <td class="num egreso">${{ number_format((float) $indicadores['gastos'], 2) }}</td>
            </tr>
        </tbody>
        <tfoot>
            <tr>
                <td>Resultado neto del periodo</td>
                <td class="num">${{ number_format((float) $indicadores['resultadoNeto'], 2) }}</td>
            </tr>
            <tr>
                <td>Saldo pendiente de cobro</td>
                <td class="num">${{ number_format((float) $indicadores['saldoPendiente'], 2) }}</td>
            </tr>
        </tfoot>
    </table>

    <div class="titulo-seccion">Desglose por día</div>
    <table class="detalle">
        <thead>
            <tr>
                <th>Fecha</th>
                <th class="num">Reservas</th>
                <th class="num">Check-in</th>
                <th class="num">Check-out</th>
                <th class="num">Ingresos</th>
                <th class="num">Gastos</th>
                <th class="num">Servicios</th>
                <th class="num">Ocupación</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($desglose as $dia)
                <tr>
                    <td>{{ $dia['fechaTexto'] }}</td>
                    <td class="num">{{ $dia['reservaciones'] }}</td>
                    <td class="num">{{ $dia['checkins'] }}</td>
                    <td class="num">{{ $dia['checkouts'] }}</td>
                    <td class="num">${{ number_format((float) $dia['ingresos'], 2) }}</td>
                    <td class="num">${{ number_format((float) $dia['gastos'], 2) }}</td>
                    <td class="num">{{ $dia['servicios'] }}</td>
                    <td class="num">{{ number_format((float) $dia['ocupacion'], 1) }}%</td>
                </tr>
            @empty
                <tr>
                    <td colspan="8">Sin movimientos en el periodo.</td>
                </tr>
            @endforelse
        </tbody>
        <tfoot>
            <tr>
                <td>Totales</td>
                <td class="num">{{ $indicadores['totalReservaciones'] }}</td>
                <td class="num">{{ $indicadores['checkins'] }}</td>
                <td class="num">{{ $indicadores['checkouts'] }}</td>
                <td class="num">${{ number_format((float) $indicadores['ingresos'], 2) }}</td>
                <td class="num">${{ number_format((float) $indicadores['gastos'], 2) }}</td>
                <td class="num">{{ $indicadores['serviciosCantidad'] }}</td>
                <td class="num">{{ number_format((float) $indicadores['ocupacion'], 1) }}%</td>
            </tr>
        </tfoot>
    </table>

    <div class="titulo-seccion">Detalle por categorías</div>
    <table class="dos-columnas">
        <tr>
            <td>
                <table class="detalle">
                    <thead>
                        <tr>
                            <th>Pagos por método</th>
                            <th class="num">Pagos</th>
                            <th class="num">Monto</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($pagosPorMetodo as $pago)
                            <tr>
                                <td>{{ $pago->metodo_pago }}</td>
                                <td class="num">{{ $pago->total }}</td>
                                <td class="num">${{ number_format((float) $pago->monto, 2) }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3">Sin pagos completados.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>

                <table class="detalle">
                    <thead>
                        <tr>
                            <th>Reservaciones por estado</th>
                            <th class="num">Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($reservasPorEstado as $estado => $total)
                            <tr>
                                <td>{{ $estado }}</td>
                                <td class="num">{{ $total }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="2">Sin reservaciones.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </td>
            <td>
                <table class="detalle">
                    <thead>
                        <tr>
                            <th>Gastos por categoría</th>
                            <th class="num">Monto</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($gastosPorCategoria as $gasto)
                            <tr>
                                <td>{{ $gasto->categoria ?: 'Sin categoría' }}</td>
                                <td class="num">${{ number_format((float) $gasto->monto, 2) }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="2">Sin gastos registrados.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>

                <table class="detalle">
                    <thead>
                        <tr>
                            <th>Servicios más solicitados</th>
                            <th class="num">Cant.</th>
                            <th class="num">Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($serviciosTop as $servicio)
                            <tr>
                                <td>{{ $servicio->nombre }}</td>
                                <td class="num">{{ $servicio->cantidad }}</td>
                                <td class="num">${{ number_format((float) $servicio->total, 2) }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3">Sin servicios vendidos.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </td>
        </tr>
    </table>

    <div class="pie">
        Reporte generado el {{ $generadoEl->format('d/m/Y H:i') }} &middot; {{ $hotel }} &middot; NovaStay Hotel Management
    </div>

    <div class="marca-agua">NovaStay</div>
</body>
</html>
