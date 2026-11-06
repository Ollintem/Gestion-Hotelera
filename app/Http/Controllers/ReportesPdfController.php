<?php

namespace App\Http\Controllers;

use App\Services\Reportes\ResumenReportes;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ReportesPdfController extends Controller
{
    public function descargar(Request $request, ResumenReportes $resumen): Response
    {
        $datos = $resumen->generar(
            (string) $request->query('periodo', ResumenReportes::MENSUAL),
            $request->query('fecha'),
            $request->query('semana'),
            $request->filled('mes') ? (int) $request->query('mes') : null,
            $request->filled('anio') ? (int) $request->query('anio') : null,
        );

        $pdf = Pdf::loadView('pdf.reporte', $datos)->setPaper('a4');

        return $pdf->download('reporte-'.$datos['archivo'].'.pdf');
    }
}
