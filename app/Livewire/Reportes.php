<?php

namespace App\Livewire;

use App\Services\Reportes\ResumenReportes;
use Carbon\Carbon;
use Illuminate\View\View;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.app')]
class Reportes extends Component
{
    public string $periodo = ResumenReportes::MENSUAL;

    public string $fecha = '';

    public string $semana = '';

    public int $mes = 1;

    public int $anio = 1;

    public function mount(): void
    {
        $hoy = now();

        $this->fecha = $hoy->toDateString();
        $this->semana = $hoy->format('o-\WW');
        $this->mes = (int) $hoy->month;
        $this->anio = (int) $hoy->year;
    }

    public function render(): View
    {
        $datos = app(ResumenReportes::class)->generar(
            $this->periodo,
            $this->fecha,
            $this->semana,
            $this->mes,
            $this->anio,
        );

        $datos['periodos'] = ResumenReportes::PERIODOS;
        $datos['meses'] = collect(range(1, 12))
            ->mapWithKeys(fn (int $mes): array => [
                $mes => Carbon::createFromDate(now()->year, $mes, 1)->locale('es')->translatedFormat('F'),
            ])
            ->all();
        $datos['anios'] = range((int) now()->year - 2, (int) now()->year + 1);
        $datos['urlPdf'] = route('reportes.pdf', [
            'periodo' => $this->periodo,
            'fecha' => $this->fecha,
            'semana' => $this->semana,
            'mes' => $this->mes,
            'anio' => $this->anio,
        ]);

        return view('livewire.reportes.index', $datos);
    }

    /**
     * Avisa al navegador que el periodo cambió para que las gráficas se
     * reconstruyan con los datos recién pintados.
     */
    public function rendered(): void
    {
        $this->dispatch('reporte-actualizado');
    }
}
