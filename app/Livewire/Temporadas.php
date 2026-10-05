<?php

namespace App\Livewire;

use App\Models\Temporada;
use Illuminate\View\View;
use Livewire\Attributes\Layout;
use Livewire\Component;

/**
 * Contenedor del módulo de temporadas. Es el único dueño del estado del
 * formulario, del modal y de los mensajes, y el que decide el orden en que se
 * listan los periodos del calendario.
 *
 * El alta y la edición no son páginas: se montan en un modal sobre el propio
 * listado, así que `render()` siempre resuelve `temporadas/index.blade.php` y el
 * estado de la ventana viaja en `$modalAbierto`, que Alpine entrelaza para
 * animar tanto la entrada como la salida. `$pagina` conserva qué variante hay
 * abierta porque el título y el contenido del modal la necesitan.
 *
 * El precio efectivo no se envía en ningún sitio: se deriva de
 * `precio_base * multiplicador_precio` en el modelo. Aquí solo se calcula para
 * la vista previa del formulario, y esa vista previa se recalcula en cada
 * pulsación porque los dos campos viajan con `wire:model.live`.
 *
 * El precio base nunca puede quedar a cero y la fecha de fin nunca puede ser
 * anterior a la de inicio: un periodo sin precio no tiene nada que aplicar y un
 * periodo vacío tampoco. Dos periodos que comparten días, en cambio, son una
 * decisión de recepción y no un error, así que no se avisa de ello.
 *
 * Los mensajes viajan como evento `notificacion` y no como propiedad: Alpine
 * los pinta sin esperar al morph, de modo que el aviso aparece en el mismo clic
 * que lo disparó.
 */
#[Layout('components.layouts.app')]
class Temporadas extends Component
{
    /**
     * Recorrido del deslizador de precios. El formulario nunca ofrece un factor
     * fuera de esta horquilla: por debajo del mínimo el hotel regalaría la
     * habitación y por encima del doble ninguna tarifa de referencia lo
     * justifica.
     */
    public const MULTIPLICADOR_MINIMO = 0.5;

    public const MULTIPLICADOR_MAXIMO = 2.0;

    /**
     * Variante del modal que está abierta: `index` no muestra ninguna.
     */
    public string $pagina = 'index';

    /** Estado real de la ventana: Alpine lo entrelaza para animar la salida. */
    public bool $modalAbierto = false;

    public ?int $temporadaId = null;

    public string $nombre = '';

    public string $fecha_inicio = '';

    public string $fecha_fin = '';

    public string $multiplicador_precio = '1.00';

    public string $precio_base = '';

    public function render(): View
    {
        return view('temporadas.index', [
            'temporadas' => Temporada::orderBy('fecha_inicio')->orderBy('nombre')->get(),
            'precioEfectivo' => $this->precioEfectivo(),
            'precioEfectivoEnPesos' => $this->precioEfectivoEnPesos(),
            'variacionPrecio' => $this->variacionPrecio(),
            'multiplicadorEnTexto' => $this->multiplicadorEnTexto(),
            'multiplicadorMinimo' => self::MULTIPLICADOR_MINIMO,
            'multiplicadorMaximo' => self::MULTIPLICADOR_MAXIMO,
        ]);
    }

    /**
     * Abre el formulario vacío para dar de alta una temporada.
     */
    public function crear(): void
    {
        $this->resetFormulario();

        $this->fecha_inicio = now()->toDateString();
        $this->fecha_fin = now()->addMonth()->toDateString();

        $this->abrirModal('crear');
    }

    /**
     * Carga una temporada en el formulario. Cargar y mostrar ocurren en la misma
     * petición, que es lo que exige el botón de la tarjeta o de la fila: un
     * solo clic y el modal aparece con las fechas y el precio reales.
     */
    public function editar(int $id): void
    {
        $temporada = Temporada::findOrFail($id);

        $this->temporadaId = $temporada->id;
        $this->nombre = $temporada->nombre;
        $this->fecha_inicio = $temporada->fecha_inicio->format('Y-m-d');
        $this->fecha_fin = $temporada->fecha_fin->format('Y-m-d');
        $this->multiplicador_precio = number_format((float) $temporada->multiplicador_precio, 2, '.', '');
        $this->precio_base = number_format((float) $temporada->precio_base, 2, '.', '');

        $this->resetValidation();

        $this->abrirModal('editar');
    }

    /**
     * Cierra el modal descartando lo que hubiera en el formulario.
     */
    public function cerrarModal(): void
    {
        $this->resetFormulario();

        $this->pagina = 'index';
        $this->modalAbierto = false;
    }

    /**
     * Guarda el alta o la edición y avisa con el mismo evento que usa el resto
     * de la pantalla.
     */
    public function guardar(): void
    {
        $this->validate(
            [
                'nombre' => ['required', 'string', 'max:100'],
                'fecha_inicio' => ['required', 'date'],
                'fecha_fin' => ['required', 'date', 'after_or_equal:fecha_inicio'],
                'multiplicador_precio' => ['required', 'numeric', 'min:'.self::MULTIPLICADOR_MINIMO, 'max:'.self::MULTIPLICADOR_MAXIMO],
                'precio_base' => ['required', 'numeric', 'min:0.01', 'max:99999999.99'],
            ],
            [
                'fecha_fin.after_or_equal' => 'La fecha de fin debe ser igual o posterior a la fecha de inicio.',
                'multiplicador_precio.min' => 'El multiplicador no puede ser menor que '.number_format(self::MULTIPLICADOR_MINIMO, 1).'.',
                'multiplicador_precio.max' => 'El multiplicador no puede ser mayor que '.number_format(self::MULTIPLICADOR_MAXIMO, 1).'.',
                'precio_base.min' => 'El precio base debe ser mayor que cero.',
            ]
        );

        $esNuevo = $this->temporadaId === null;

        $temporada = $esNuevo
            ? new Temporada
            : Temporada::findOrFail($this->temporadaId);

        $temporada->fill([
            'nombre' => $this->nombre,
            'fecha_inicio' => $this->fecha_inicio,
            'fecha_fin' => $this->fecha_fin,
            'multiplicador_precio' => $this->multiplicador_precio,
            'precio_base' => $this->precio_base,
        ])->save();

        $this->cerrarModal();

        $this->dispatch(
            'notificacion',
            mensaje: $esNuevo
                ? 'Temporada creada correctamente.'
                : 'Temporada actualizada correctamente.'
        );
    }

    /**
     * Descarta una temporada del calendario.
     */
    public function eliminar(int $id): void
    {
        Temporada::findOrFail($id)->delete();

        $this->dispatch('notificacion', mensaje: 'Temporada eliminada correctamente.');
    }

    /**
     * Retira una temporada del calendario sin borrarla: el histórico sigue
     * señalando a un periodo real y su precio sigue siendo consultable. Volverla
     * a activar la devuelve a la lista.
     */
    public function alternarActivo(int $id): void
    {
        $temporada = Temporada::findOrFail($id);

        $temporada->update(['activo' => ! $temporada->activo]);

        $this->dispatch(
            'notificacion',
            mensaje: $temporada->activo
                ? 'Temporada activada correctamente.'
                : 'Temporada desactivada correctamente.'
        );
    }

    /**
     * Precio efectivo de lo que hay en el formulario ahora mismo. Es la misma
     * cuenta que hace el modelo, con los valores a medio escribir: un campo
     * vacío vale cero, no un error, para que el recuadro no parpadee mientras se
     * teclea.
     */
    public function precioEfectivo(): float
    {
        return round((float) $this->precio_base * (float) $this->multiplicador_precio, 2);
    }

    /**
     * El precio efectivo del formulario con el formato de moneda del panel.
     */
    public function precioEfectivoEnPesos(): string
    {
        return '$'.number_format($this->precioEfectivo(), 2);
    }

    /**
     * Variación que introduce el multiplicador sobre el precio base, en
     * porcentaje, para pintar el recuadro como recargo o como descuento.
     */
    public function variacionPrecio(): float
    {
        return round(((float) $this->multiplicador_precio - 1) * 100, 2);
    }

    /**
     * Multiplicador del formulario con el signo de multiplicación delante, tal y
     * como lo muestra el deslizador.
     */
    public function multiplicadorEnTexto(): string
    {
        return '×'.number_format((float) $this->multiplicador_precio, 2);
    }

    /**
     * Deja el formulario en blanco y sin errores de validación.
     */
    private function resetFormulario(): void
    {
        $this->resetValidation();

        $this->temporadaId = null;
        $this->nombre = '';
        $this->fecha_inicio = '';
        $this->fecha_fin = '';
        $this->multiplicador_precio = '1.00';
        $this->precio_base = '';
    }

    /**
     * Enciende la ventana en la variante indicada. El alta y la edición
     * comparten el gesto: una petición, la ventana abierta y el formulario
     * preparado.
     */
    private function abrirModal(string $variante): void
    {
        $this->pagina = $variante;
        $this->modalAbierto = true;
    }
}
