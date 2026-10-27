<?php

namespace App\Livewire;

use App\Models\Cliente;
use App\Models\Habitacion;
use App\Models\InformacionHotel;
use App\Models\Reserva;
use App\Models\Temporada;
use App\Models\User;
use Illuminate\View\View;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Validate;
use Livewire\Component;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

/**
 * Módulo de Configuración.
 *
 * Dos apartados que se cambian con un segmented control:
 *
 *  - `general`: solo lectura. Resume lo que el sistema ya sabe (nombre de la
 *    aplicación, entorno, zona horaria, idioma, temporada vigente y la sesión
 *    abierta). Nada de esto se edita desde aquí.
 *  - `hotel`: consulta y actualización de los datos del establecimiento, que
 *    viven en la tabla `informacion_hotel` (un único registro obtenido con
 *    `InformacionHotel::current()`).
 *
 * Guardar exige el permiso `configuracion.editar`; la ruta solo pide
 * `configuracion.ver`, que alcanza para consultar.
 */
#[Layout('components.layouts.app')]
class Configuracion extends Component
{
    /**
     * Apartado visible: `general` o `hotel`.
     */
    public string $apartado = 'general';

    /**
     * Estado del formulario del hotel: en `false` se muestra la ficha de solo
     * lectura.
     */
    public bool $editandoHotel = false;

    #[Validate('required|string|max:150')]
    public string $nombre = '';

    #[Validate('nullable|string|max:20')]
    public string $rfc = '';

    #[Validate('nullable|string|max:255')]
    public string $direccion = '';

    #[Validate('nullable|string|max:100')]
    public string $ciudad = '';

    #[Validate('nullable|string|max:50')]
    public string $telefono = '';

    #[Validate('nullable|email|max:150')]
    public string $email = '';

    #[Validate('nullable|string|max:150')]
    public string $sitio_web = '';

    #[Validate('nullable|integer|min:0|max:9999')]
    public string $numero_habitaciones = '';

    #[Validate('nullable|string|max:1000')]
    public string $descripcion = '';

    public ?string $mensajeExito = null;

    public ?string $mensajeError = null;

    public function render(): View
    {
        $hoy = now()->toDateString();

        $temporadaVigente = Temporada::where('fecha_inicio', '<=', $hoy)
            ->where('fecha_fin', '>=', $hoy)
            ->orderBy('fecha_inicio')
            ->first();

        return view('configuracion.index', [
            'nombreApp' => config('app.name'),
            'entorno' => config('app.env'),
            'zonaHoraria' => config('app.timezone'),
            'idioma' => config('app.locale'),
            'temporadaVigente' => $temporadaVigente,
            'totalUsuarios' => User::count(),
            'totalRoles' => Role::count(),
            'totalPermisos' => Permission::count(),
            'totalHabitaciones' => Habitacion::count(),
            'totalClientes' => Cliente::count(),
            'totalReservas' => Reserva::count(),
            'hotel' => InformacionHotel::current(),
        ]);
    }

    /**
     * Cambia el apartado visible y cierra cualquier edición en curso.
     */
    public function verApartado(string $apartado): void
    {
        $this->apartado = in_array($apartado, ['general', 'hotel'], true) ? $apartado : 'general';
        $this->editandoHotel = false;
        $this->resetValidation();
        $this->reset(['mensajeExito', 'mensajeError']);
        $this->limpiarFormularioHotel();
    }

    /**
     * Pasa la ficha a modo edición con los datos actuales.
     */
    public function editarHotel(): void
    {
        if (! $this->puedeEditar()) {
            return;
        }

        $hotel = InformacionHotel::current();

        $this->nombre = $hotel->valor('nombre');
        $this->rfc = $hotel->valor('rfc');
        $this->direccion = $hotel->valor('direccion');
        $this->ciudad = $hotel->valor('ciudad');
        $this->telefono = $hotel->valor('telefono');
        $this->email = $hotel->valor('email');
        $this->sitio_web = $hotel->valor('sitio_web');
        $this->numero_habitaciones = $hotel->numero_habitaciones !== null
            ? (string) $hotel->numero_habitaciones
            : '';
        $this->descripcion = $hotel->valor('descripcion');

        $this->resetValidation();
        $this->reset(['mensajeExito', 'mensajeError']);
        $this->editandoHotel = true;
    }

    public function cerrarEdicionHotel(): void
    {
        $this->editandoHotel = false;
        $this->resetValidation();
        $this->reset(['mensajeExito', 'mensajeError']);
        $this->limpiarFormularioHotel();
    }

    /**
     * Vuelve el formulario a su estado vacío. Cancelar o cambiar de apartado
     * deja de mostrar la ficha, así que los campos no conservan lo que se
     * estaba capturando; al volver a editar se recargan desde la base.
     */
    private function limpiarFormularioHotel(): void
    {
        $this->reset([
            'nombre',
            'rfc',
            'direccion',
            'ciudad',
            'telefono',
            'email',
            'sitio_web',
            'numero_habitaciones',
            'descripcion',
        ]);
    }

    /**
     * Guarda los datos del hotel sobre el registro único. Los campos vacíos se
     * guardan como nulos para que la ficha muestre «—» en lugar de cadenas
     * vacías.
     */
    public function guardarHotel(): void
    {
        if (! $this->puedeEditar()) {
            return;
        }

        $this->validate();

        InformacionHotel::current()->update([
            'nombre' => $this->nombre,
            'rfc' => $this->rfc !== '' ? $this->rfc : null,
            'direccion' => $this->direccion !== '' ? $this->direccion : null,
            'ciudad' => $this->ciudad !== '' ? $this->ciudad : null,
            'telefono' => $this->telefono !== '' ? $this->telefono : null,
            'email' => $this->email !== '' ? $this->email : null,
            'sitio_web' => $this->sitio_web !== '' ? $this->sitio_web : null,
            'numero_habitaciones' => $this->numero_habitaciones !== '' ? (int) $this->numero_habitaciones : null,
            'descripcion' => $this->descripcion !== '' ? $this->descripcion : null,
        ]);

        $this->editandoHotel = false;
        $this->mensajeExito = 'Información del hotel actualizada correctamente.';
        $this->mensajeError = null;
        $this->resetValidation();
    }

    /**
     * Corta la operación cuando el usuario solo puede consultar. La ruta pide
     * `configuracion.ver`, que deja ver la ficha pero no modificarla.
     */
    private function puedeEditar(): bool
    {
        if (auth()->user()?->can('configuracion.editar')) {
            return true;
        }

        $this->mensajeExito = null;
        $this->mensajeError = 'No tienes permiso para modificar la configuración del hotel.';

        return false;
    }
}
