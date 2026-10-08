<?php

namespace App\Livewire;

use App\Models\Habitacion;
use App\Models\Limpieza as TareaLimpieza;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.app')]
class Limpieza extends Component
{
    public string $pagina = 'index';

    public bool $mostrarModal = false;

    public ?int $tareaId = null;

    public string $habitacion_id = '';

    public string $user_id = '';

    public string $estado = 'Pendiente';

    public string $notas = '';

    public ?string $mensajeExito = null;

    public function render(): View
    {
        $user = Auth::user();
        $esAdministrador = $user?->hasRole('super-admin') || $user?->hasRole('gerente') || $user?->hasRole('recepcionista') || $user?->hasRole('admin') || ($user && $user->rol === 'admin') || ($user && $user->rol === 'Administrador');

        if ($user && ($user->hasRole('limpieza') || $user->rol === 'limpieza' || $user->rol === 'Limpieza')) {
            $esLimpieza = true;
            $esAdministrador = false;
        } else {
            $esLimpieza = false;
        }

        $tareasQuery = TareaLimpieza::with(['habitacion', 'usuario'])->latest();

        if ($esAdministrador && ! $esLimpieza) {
            // Admin can see all
        } elseif ($esLimpieza) {
            $tareasQuery->where('user_id', $user->id);
        } else {
            // Default: if not admin, only assigned
            $tareasQuery->where('user_id', $user?->id ?? 0);
        }

        $datos = [
            'tareas' => $tareasQuery->get(),
            'habitaciones' => Habitacion::orderBy('numero_habitacion')->get(),
            'usuarios' => User::role('limpieza')->orderBy('name')->get(),
            'estados' => ['Pendiente', 'En Proceso', 'Completado'],
        ];

        return match ($this->pagina) {
            'crear' => view('limpieza.create', $datos),
            'editar' => view('limpieza.edit', $datos),
            default => view('limpieza.index', $datos),
        };
    }

    public function crear(): void
    {
        $user = Auth::user();
        $esLimpieza = $user?->hasRole('limpieza') || ($user && ($user->rol === 'limpieza' || $user->rol === 'Limpieza'));

        if ($esLimpieza) {
            return;
        }

        $this->reset(['tareaId', 'habitacion_id', 'notas']);
        $this->user_id = (string) auth()->id();
        $this->estado = 'Pendiente';
        $this->resetValidation();
        $this->reset('mensajeExito');
        $this->pagina = 'crear';
    }

    public function editar(int $id): void
    {
        $tarea = TareaLimpieza::findOrFail($id);
        $user = Auth::user();
        $esLimpieza = $user?->hasRole('limpieza') || ($user && ($user->rol === 'limpieza' || $user->rol === 'Limpieza'));

        if ($esLimpieza && $tarea->user_id !== $user->id) {
            return;
        }

        $this->tareaId = $tarea->id;
        $this->habitacion_id = (string) $tarea->habitacion_id;
        $this->user_id = (string) $tarea->user_id;
        $this->estado = $tarea->estado;
        $this->notas = $tarea->notas ?? '';
        $this->resetValidation();
        $this->reset('mensajeExito');
        $this->pagina = 'editar';
    }

    public function cerrarModal(): void
    {
        $this->pagina = 'index';
        $this->reset(['tareaId', 'habitacion_id', 'user_id', 'notas']);
        $this->resetValidation();
    }

    public function guardar(): void
    {
        $user = Auth::user();
        $esLimpieza = $user?->hasRole('limpieza') || ($user && ($user->rol === 'limpieza' || $user->rol === 'Limpieza'));

        if ($this->tareaId) {
            $tarea = TareaLimpieza::findOrFail($this->tareaId);
            if ($esLimpieza) {
                if ($tarea->user_id !== $user->id) {
                    return;
                }

                $this->validate([
                    'estado' => ['required', 'in:Pendiente,En Proceso,Completado'],
                ]);

                $datos = [
                    'estado' => $this->estado,
                ];
            } else {
                $this->validate([
                    'habitacion_id' => ['required', 'exists:habitaciones,id'],
                    'user_id' => ['required', 'exists:users,id'],
                    'estado' => ['required', 'in:Pendiente,En Proceso,Completado'],
                    'notas' => ['nullable', 'string'],
                ]);

                $datos = [
                    'habitacion_id' => $this->habitacion_id,
                    'user_id' => $this->user_id,
                    'estado' => $this->estado,
                    'notas' => $this->notas ?: null,
                ];
            }

            $tarea->update($datos);
            $this->mensajeExito = 'Tarea de limpieza actualizada correctamente.';
            $this->habitacion_id = (string) $tarea->habitacion_id;
        } else {
            if ($esLimpieza) {
                return;
            }

            $this->validate([
                'habitacion_id' => ['required', 'exists:habitaciones,id'],
                'user_id' => ['required', 'exists:users,id'],
                'estado' => ['required', 'in:Pendiente,En Proceso,Completado'],
                'notas' => ['nullable', 'string'],
            ]);

            $datos = [
                'habitacion_id' => $this->habitacion_id,
                'user_id' => $this->user_id,
                'estado' => $this->estado,
                'notas' => $this->notas ?: null,
            ];

            TareaLimpieza::create($datos);
            $this->mensajeExito = 'Tarea de limpieza creada correctamente.';
        }

        $habitacion = Habitacion::find($this->habitacion_id);

        if ($habitacion) {
            if ($this->estado === 'Completado') {
                $habitacion->update(['estado' => 'Disponible']);
            } elseif ($this->estado === 'En Proceso' && $habitacion->estado === 'Disponible') {
                $habitacion->update(['estado' => 'Limpieza']);
            }
        }

        $this->cerrarModal();
    }

    public function eliminar(int $id): void
    {
        $user = Auth::user();
        $esLimpieza = $user?->hasRole('limpieza') || ($user && ($user->rol === 'limpieza' || $user->rol === 'Limpieza'));

        if ($esLimpieza) {
            return;
        }

        TareaLimpieza::findOrFail($id)->delete();

        $this->mensajeExito = 'Tarea de limpieza eliminada correctamente.';
    }
}
