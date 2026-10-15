<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>Estado de cuenta</title>
    <style>
        * { box-sizing: border-box; }
        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 12px;
            color: #1e293b;
            line-height: 1.5;
            margin: 0;
            padding: 24px;
        }
        .encabezado {
            border-bottom: 3px solid #d97706;
            padding-bottom: 14px;
            margin-bottom: 20px;
        }
        .encabezado h1 {
            margin: 0;
            font-size: 22px;
            color: #0f172a;
        }
        .encabezado p {
            margin: 2px 0 0;
            font-size: 11px;
            color: #64748b;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        .titulo-azul {
            background: #f1f5f9;
            border-left: 4px solid #d97706;
            padding: 6px 10px;
            font-size: 13px;
            font-weight: bold;
            color: #0f172a;
            margin: 18px 0 8px;
        }
        table.datos {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 4px;
        }
        table.datos td {
            padding: 4px 8px;
            vertical-align: top;
            font-size: 11px;
        }
        table.datos td.etiqueta {
            width: 32%;
            color: #64748b;
            font-weight: 600;
        }
        table.detalle {
            width: 100%;
            border-collapse: collapse;
            margin-top: 6px;
        }
        table.detalle th {
            background: #0f172a;
            color: #ffffff;
            text-align: left;
            font-size: 11px;
            padding: 6px 8px;
        }
        table.detalle td {
            padding: 6px 8px;
            font-size: 11px;
            border-bottom: 1px solid #e2e8f0;
        }
        table.detalle .num { text-align: right; }
        .totales {
            margin-top: 14px;
            width: 100%;
            border-collapse: collapse;
        }
        .totales td {
            padding: 5px 8px;
            font-size: 12px;
        }
        .totales .total-final {
            background: #fef3c7;
            font-weight: bold;
            color: #0f172a;
            font-size: 14px;
        }
        .pie {
            margin-top: 30px;
            padding-top: 10px;
            border-top: 1px solid #e2e8f0;
            font-size: 10px;
            color: #94a3b8;
            text-align: center;
        }
        .marca-agua {
            text-align: center;
            margin-top: 40px;
            font-size: 10px;
            color: #cbd5e1;
            letter-spacing: 2px;
            text-transform: uppercase;
        }
    </style>
</head>
<body>

    <div class="encabezado">
        <h1>NovaStay Hotel Management</h1>
        <p>Estado de cuenta &middot; Comprobante de reservaci&oacute;n</p>
    </div>

    <div class="titulo-azul">Datos del cliente</div>
    <table class="datos">
        <tr>
            <td class="etiqueta">Nombre completo</td>
            <td>{{ $reserva->cliente?->nombreCompleto() ?? $reserva->usuario?->name ?? '—' }}</td>
            <td class="etiqueta">Email</td>
            <td>{{ $reserva->cliente?->email ?? $reserva->usuario?->email ?? '—' }}</td>
        </tr>
        <tr>
            <td class="etiqueta">Tel&eacute;fono</td>
            <td>{{ $reserva->cliente?->telefono ?? '—' }}</td>
            <td class="etiqueta">Identificaci&oacute;n</td>
            <td>{{ $reserva->cliente && $reserva->cliente->tipo_identificacion ? $reserva->cliente->tipo_identificacion.' · '.$reserva->cliente->numero_identificacion : '—' }}</td>
        </tr>
    </table>

    <div class="titulo-azul">Datos de la reservaci&oacute;n</div>
    <table class="datos">
        <tr>
            <td class="etiqueta">Folio</td>
            <td>#{{ str_pad($reserva->id, 5, '0', STR_PAD_LEFT) }}</td>
            <td class="etiqueta">Estado</td>
            <td>{{ $reserva->estado }}</td>
        </tr>
        <tr>
            <td class="etiqueta">Check-in</td>
            <td>{{ $reserva->check_in->format('d/m/Y') }}</td>
            <td class="etiqueta">Check-out</td>
            <td>{{ $reserva->check_out->format('d/m/Y') }}</td>
        </tr>
        <tr>
            <td class="etiqueta">Noches</td>
            <td>{{ $noches }}</td>
            <td class="etiqueta">Habitaci&oacute;n asignada</td>
            <td>
                @forelse ($asignaciones as $asignacion)
                    {{ $asignacion->habitacion?->numero_habitacion }}
                    @if ($asignacion->habitacion?->tipoHabitacion)
                        ({{ $asignacion->habitacion->tipoHabitacion->nombre }}{{ $asignacion->habitacion->piso ? ', Piso '.$asignacion->habitacion->piso : '' }})
                    @endif
                    @if (! $loop->last), @endif
                @empty
                    —
                @endforelse
            </td>
        </tr>
    </table>

    <div class="titulo-azul">Desglose de costos</div>
    <table class="detalle">
        <thead>
            <tr>
                <th>Concepto</th>
                <th class="num">Cantidad</th>
                <th class="num">Importe</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($asignaciones as $asignacion)
                <tr>
                    <td>Tarifa por noche · Hab. #{{ $asignacion->habitacion?->numero_habitacion }}</td>
                    <td class="num">{{ $noches }} {{ $noches === 1 ? 'noche' : 'noches' }}</td>
                    <td class="num">${{ number_format((float) $asignacion->precio_por_noche * $noches, 2) }}</td>
                </tr>
            @endforeach
            @forelse ($gastosExtra as $gasto)
                <tr>
                    <td>Consumo extra · {{ $gasto->servicio?->nombre ?? 'Servicio' }}</td>
                    <td class="num">{{ $gasto->cantidad }}</td>
                    <td class="num">${{ number_format((float) $gasto->subtotal, 2) }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="3">Sin consumos adicionales durante la estancia.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="titulo-azul">Abonos realizados</div>
    <table class="detalle">
        <thead>
            <tr>
                <th>Fecha</th>
                <th>M&eacute;todo</th>
                <th>Referencia</th>
                <th class="num">Importe</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($pagos as $pago)
                <tr>
                    <td>{{ $pago->fecha_pago?->format('d/m/Y H:i') ?? '—' }}</td>
                    <td>{{ $pago->metodo_pago }}</td>
                    <td>{{ $pago->referencia ?: '—' }}</td>
                    <td class="num">${{ number_format((float) $pago->monto, 2) }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="4">A&uacute;n no se registran abonos para esta reservaci&oacute;n.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <table class="totales">
        <tr>
            <td><strong>Tarifa de la habitaci&oacute;n</strong></td>
            <td class="num">${{ number_format($tarifa, 2) }}</td>
        </tr>
        <tr>
            <td>Servicios y gastos extra</td>
            <td class="num">${{ number_format($subtotalExtras, 2) }}</td>
        </tr>
        <tr>
            <td>Total consumido</td>
            <td class="num">${{ number_format($totalConsumos, 2) }}</td>
        </tr>
        <tr>
            <td>Total pagado</td>
            <td class="num">${{ number_format($pagado, 2) }}</td>
        </tr>
        <tr class="total-final">
            <td>Total pendiente</td>
            <td class="num">${{ number_format($pendiente, 2) }}</td>
        </tr>
    </table>

    <div class="pie">
        Documento generado el {{ now()->format('d/m/Y H:i') }} &middot; NovaStay Hotel Management &middot; Gracias por hospedarte con nosotros.
    </div>

    <div class="marca-agua">NovaStay</div>

</body>
</html>