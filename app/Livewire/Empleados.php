<?php

namespace App\Livewire;

use App\Models\Empleado;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

#[Layout('components.layouts.app')]
class Empleados extends Component
{
    use WithPagination;

    public string $pagina = 'index';

    public bool $mostrarModal = false;

    public ?int $empleadoId = null;

    public string $busqueda = '';

    public string $filtroEstado = 'todos';

    public string $nombre = '';

    public string $apellidos = '';

    public string $correo_electronico = '';

    public string $contrasena = '';

    public string $rol = '';

    public bool $acceso_sistema = false;

    public string $telefono = '';

    public string $salario = '';

    public string $turno = '';

    public bool $esta_activo = true;

    public ?string $mensajeExito = null;

    public ?string $mensajeError = null;

    public bool $mostrarModalPermisos = false;

    public ?int $empleadoIdPermisos = null;

    public ?string $empleadoNombrePermisos = null;

    /** @var list<string> */
    public array $permisosUsuario = [];

    public const TURNOS = ['Mañana', 'Tarde', 'Noche', 'Rotativo'];

    public function updatedBusqueda(): void
    {
        $this->resetPage();
    }

    public function updatedFiltroEstado(): void
    {
        $this->resetPage();
    }

    /**
     * Solo el Super Admin puede administrar al personal.
     */
    public function esSuperAdmin(): bool
    {
        return auth()->user()?->hasRole('super-admin') ?? false;
    }

    public function render(): View
    {
        $empleados = Empleado::with('usuario.roles')
            ->when($this->busqueda !== '', function ($query): void {
                $termino = '%'.trim($this->busqueda).'%';
                $query->where(function ($sub) use ($termino): void {
                    $sub->where('nombre', 'like', $termino)
                        ->orWhere('apellidos', 'like', $termino)
                        ->orWhere('puesto', 'like', $termino)
                        ->orWhere('correo_electronico', 'like', $termino);
                });
            })
            ->when($this->filtroEstado === 'activos', fn ($query) => $query->where('esta_activo', true))
            ->when($this->filtroEstado === 'inactivos', fn ($query) => $query->where('esta_activo', false))
            ->orderBy('nombre')
            ->paginate(10);

        $datos = [
            'empleados' => $empleados,
            'turnos' => self::TURNOS,
        ];

        return match ($this->pagina) {
            'crear' => view('empleados.create', $datos),
            'editar' => view('empleados.edit', $datos),
            default => view('empleados.index', $datos),
        };
    }

    /**
     * Roles del sistema asignables desde el formulario (sin Super Admin).
     */
    #[Computed]
    public function rolesDisponibles()
    {
        return Role::where('name', '!=', 'super-admin')->orderBy('name')->get();
    }

    /**
     * Conteo dinámico de empleados según su puesto, para las tarjetas KPI.
     *
     * @return array<int, array{etiqueta: string, icono: string, conteo: int, color_fondo: string, color_texto: string}>
     */
    #[Computed]
    public function kpisPuestos(): array
    {
        $definiciones = collect([
            ['clave' => 'recepcionista', 'etiqueta' => 'Recepción', 'icono' => 'building-office-2', 'color_fondo' => 'bg-sky-500/10 dark:bg-sky-500/20', 'color_texto' => 'text-sky-600 dark:text-sky-400'],
            ['clave' => 'gerente', 'etiqueta' => 'Gerencia', 'icono' => 'briefcase', 'color_fondo' => 'bg-violet-500/10 dark:bg-violet-500/20', 'color_texto' => 'text-violet-600 dark:text-violet-400'],
            ['clave' => 'limpieza', 'etiqueta' => 'Limpieza', 'icono' => 'sparkles', 'color_fondo' => 'bg-emerald-500/10 dark:bg-emerald-500/20', 'color_texto' => 'text-emerald-600 dark:text-emerald-400'],
            ['clave' => 'seguridad', 'etiqueta' => 'Seguridad', 'icono' => 'shield-check', 'color_fondo' => 'bg-red-500/10 dark:bg-red-500/20', 'color_texto' => 'text-red-600 dark:text-red-400'],
            ['clave' => 'mantenimiento', 'etiqueta' => 'Mantenimiento', 'icono' => 'wrench-screwdriver', 'color_fondo' => 'bg-amber-500/10 dark:bg-amber-500/20', 'color_texto' => 'text-amber-600 dark:text-amber-400'],
        ]);

        $porRol = Empleado::with('usuario.roles')->get()
            ->groupBy(fn (Empleado $empleado) => Str::lower((string) ($empleado->usuario?->roles->first()?->name ?? '')));

        $kpis = $definiciones
            ->map(function (array $definicion) use ($porRol): array {
                $definicion['conteo'] = $porRol->get($definicion['clave'])?->count() ?? 0;
                unset($definicion['clave']);

                return $definicion;
            });

        $otros = $porRol
            ->filter(fn ($empleados, string $clave) => $clave !== '' && ! $definiciones->contains('clave', $clave))
            ->sum->count();

        if ($otros > 0) {
            $kpis->push([
                'etiqueta' => 'Otros puestos',
                'icono' => 'users',
                'conteo' => $otros,
                'color_fondo' => 'bg-slate-500/10 dark:bg-slate-500/20',
                'color_texto' => 'text-slate-600 dark:text-slate-400',
            ]);
        }

        $sinAsignar = $porRol->get('')?->count() ?? 0;

        if ($sinAsignar > 0) {
            $kpis->push([
                'etiqueta' => 'Sin asignar',
                'icono' => 'user-minus',
                'conteo' => $sinAsignar,
                'color_fondo' => 'bg-zinc-400/10 dark:bg-zinc-400/20',
                'color_texto' => 'text-zinc-500 dark:text-zinc-400',
            ]);
        }

        return $kpis->values()->all();
    }

    /**
     * Permisos del sistema agrupados por módulo para la matriz granular.
     */
    #[Computed]
    public function permisosPorModulo()
    {
        return Permission::orderBy('name')
            ->get()
            ->groupBy(fn (Permission $permiso) => Str::before($permiso->name, '.'));
    }

    public function etiquetaModulo(string $modulo): string
    {
        return match ($modulo) {
            'dashboard' => 'Dashboard',
            'checkin_checkout' => 'Check-in / Check-out',
            'roles_permisos' => 'Roles y permisos',
            'configuracion' => 'Configuración',
            default => Str::headline($modulo),
        };
    }

    public function etiquetaAccion(string $accion): string
    {
        return match ($accion) {
            'ver' => 'Ver',
            'crear' => 'Crear',
            'editar' => 'Editar',
            'eliminar' => 'Eliminar',
            default => Str::headline($accion),
        };
    }

    /**
     * Acciones canónicas de la matriz de seguridad granular.
     *
     * @return array<int, array{clave: string, etiqueta: string}>
     */
    public function accionesMatriz(): array
    {
        return [
            ['clave' => 'ver', 'etiqueta' => 'Mostrar'],
            ['clave' => 'crear', 'etiqueta' => 'Crear'],
            ['clave' => 'editar', 'etiqueta' => 'Editar'],
            ['clave' => 'eliminar', 'etiqueta' => 'Eliminar'],
            ['clave' => 'gestionar', 'etiqueta' => 'Gestionar'],
        ];
    }

    /**
     * Icono de la interfaz para cada módulo de la matriz.
     */
    public function iconoModulo(string $modulo): string
    {
        return match ($modulo) {
            'dashboard' => 'home',
            'reservaciones' => 'calendar-days',
            'habitaciones' => 'building-office-2',
            'clientes' => 'users',
            'checkin_checkout' => 'arrow-right-start-on-rectangle',
            'limpieza' => 'sparkles',
            'pagos' => 'credit-card',
            'servicios' => 'wrench-screwdriver',
            'gastos' => 'banknotes',
            'empleados' => 'identification',
            'temporadas' => 'sun',
            'reportes' => 'chart-bar',
            'usuarios' => 'user-group',
            'roles_permisos' => 'shield-check',
            'configuracion' => 'cog-6-tooth',
            default => 'puzzle-piece',
        };
    }

    /**
     * El usuario ya posee todas las acciones del módulo.
     */
    public function todosSeleccionadosDelModulo(string $modulo): bool
    {
        $nombres = $this->permisosPorModulo->get($modulo)?->pluck('name')->all() ?? [];

        if ($nombres === []) {
            return false;
        }

        return count(array_intersect($nombres, $this->permisosUsuario)) === count($nombres);
    }

    /**
     * Marca o desmarca todas las acciones de un módulo de la matriz.
     */
    public function alternarTodosDelModulo(string $modulo): void
    {
        abort_unless($this->esSuperAdmin(), 403);

        $nombres = $this->permisosPorModulo->get($modulo)?->pluck('name')->all() ?? [];

        if ($nombres === []) {
            return;
        }

        $todosActivos = $this->todosSeleccionadosDelModulo($modulo);

        $this->permisosUsuario = $todosActivos
            ? array_values(array_diff($this->permisosUsuario, $nombres))
            : array_values(array_unique(array_merge($this->permisosUsuario, $nombres)));
    }

    /**
     * Clases del input del formulario con su estado de validación visual.
     */
    public function claseInput(string $campo): string
    {
        $base = 'block w-full rounded-xl border-0 bg-white px-3.5 py-2.5 text-sm text-slate-900 shadow-sm ring-1 ring-inset transition-all duration-300 ease-out placeholder:text-slate-400 focus:outline-none focus:ring-2 focus:ring-inset dark:bg-zinc-900 dark:text-white';

        return $this->getErrorBag()->has($campo)
            ? $base.' !ring-red-400 focus:!ring-red-500'
            : $base.' !ring-slate-300 focus:!ring-amber-500';
    }

    public function crear(): void
    {
        $this->reset([
            'empleadoId',
            'nombre',
            'apellidos',
            'correo_electronico',
            'contrasena',
            'rol',
            'telefono',
            'salario',
            'turno',
        ]);
        $this->acceso_sistema = true;
        $this->esta_activo = true;
        $this->turno = 'Mañana';
        $this->resetValidation();
        $this->reset('mensajeExito', 'mensajeError');
        $this->pagina = 'crear';
    }

    public function editar(int $id): void
    {
        $empleado = Empleado::with('usuario.roles')->where('id_empleado', $id)->firstOrFail();

        $this->empleadoId = $empleado->id_empleado;
        $this->nombre = $empleado->nombre;
        $this->apellidos = $empleado->apellidos;
        $this->correo_electronico = $empleado->correo_electronico ?? $empleado->usuario?->email ?? '';
        $this->telefono = $empleado->telefono ?? '';
        $this->salario = (string) ($empleado->salario ?? '');
        $this->turno = $empleado->turno ?? '';
        $this->esta_activo = (bool) $empleado->esta_activo;
        $this->acceso_sistema = (bool) $empleado->id_usuario;
        $rolActual = $empleado->usuario?->roles->first()?->name ?? '';
        $this->rol = $rolActual === 'super-admin' ? '' : $rolActual;
        $this->contrasena = '';
        $this->resetValidation();
        $this->reset('mensajeExito');
        $this->pagina = 'editar';
    }

    public function cerrarModal(): void
    {
        $this->pagina = 'index';
        $this->reset([
            'empleadoId',
            'nombre',
            'apellidos',
            'correo_electronico',
            'contrasena',
            'rol',
            'telefono',
            'salario',
            'turno',
        ]);
        $this->resetValidation();
    }

    public function guardar(): void
    {
        abort_unless($this->esSuperAdmin(), 403);

        $this->validate($this->reglasDeValidacion());

        $datos = [
            'nombre' => trim($this->nombre),
            'apellidos' => trim($this->apellidos),
            'correo_electronico' => $this->correo_electronico,
            'telefono' => $this->telefono ?: null,
            'salario' => $this->salario !== '' ? $this->salario : null,
            'turno' => $this->turno ?: null,
            'esta_activo' => $this->esta_activo,
        ];

        if ($this->acceso_sistema) {
            $usuario = $this->sincronizarUsuario();
            $datos['id_usuario'] = $usuario->id;
        } elseif ($this->empleadoId) {
            $this->revocarAccesoUsuario();
            $datos['id_usuario'] = null;
        }

        if ($this->empleadoId) {
            Empleado::where('id_empleado', $this->empleadoId)->firstOrFail()->update($datos);
            $this->mensajeExito = 'Empleado actualizado correctamente.';
        } else {
            Empleado::create($datos);
            $this->mensajeExito = 'Empleado creado correctamente.';
        }

        $this->cerrarModal();
    }

    /**
     * @return array<string, array>
     */
    protected function reglasDeValidacion(): array
    {
        $rules = [
            'nombre' => ['required', 'string', 'max:100'],
            'apellidos' => ['required', 'string', 'max:100'],
            'correo_electronico' => ['required', 'email', 'max:255'],
            'telefono' => ['nullable', 'string', 'max:20'],
            'salario' => ['nullable', 'numeric', 'min:0'],
            'turno' => ['nullable', Rule::in(self::TURNOS)],
            'rol' => ['nullable', Rule::exists('roles', 'name'), Rule::notIn(['super-admin'])],
            'esta_activo' => ['boolean'],
            'acceso_sistema' => ['boolean'],
        ];

        if ($this->requiereNuevaContrasena()) {
            $rules['contrasena'] = ['required', 'string', 'min:8'];
        }

        if ($this->acceso_sistema) {
            $rules['correo_electronico'][] = Rule::unique('users', 'email')
                ->ignore($this->idUsuarioVinculado() ?? 0);
        }

        return $rules;
    }

    /**
     * La contraseña inicial solo es obligatoria cuando se dará de alta un usuario del sistema.
     */
    protected function requiereNuevaContrasena(): bool
    {
        if (! $this->acceso_sistema) {
            return false;
        }

        $id = $this->idUsuarioVinculado();

        return $id === null || User::whereKey($id)->doesntExist();
    }

    /**
     * Valor actual del vínculo con un usuario del sistema para el empleado en edición.
     */
    protected function idUsuarioVinculado(): ?int
    {
        return $this->empleadoId
            ? Empleado::where('id_empleado', $this->empleadoId)->value('id_usuario')
            : null;
    }

    /**
     * Crea el usuario del sistema o actualiza el vinculado, con su contraseña y rol.
     */
    protected function sincronizarUsuario(): User
    {
        $usuario = $this->idUsuarioVinculado() ? User::find($this->idUsuarioVinculado()) : null;

        $nombreCompleto = trim($this->nombre.' '.$this->apellidos);

        if ($usuario) {
            $usuario->name = $nombreCompleto;
            $usuario->email = $this->correo_electronico;
            $usuario->activo = true;

            if ($this->contrasena !== '') {
                $usuario->password = Hash::make($this->contrasena);
            }

            $usuario->save();
        } else {
            $usuario = User::create([
                'name' => $nombreCompleto,
                'email' => $this->correo_electronico,
                'password' => Hash::make($this->contrasena),
                'activo' => true,
            ]);
        }

        $this->asignarRol($usuario);

        return $usuario;
    }

    /**
     * Asigna el rol seleccionado al usuario. Super Admin nunca se asigna desde
     * este formulario; si el usuario ya lo posee y no hay cambio, se conserva.
     */
    protected function asignarRol(User $usuario): void
    {
        if ($this->rol !== '') {
            $usuario->syncRoles([$this->rol]);

            return;
        }

        if (! $usuario->hasRole('super-admin')) {
            $usuario->syncRoles([]);
        }
    }

    /**
     * Sin acceso al sistema: se bloquea el login del usuario vinculado, si existe.
     */
    protected function revocarAccesoUsuario(): void
    {
        $id = $this->idUsuarioVinculado();

        if ($id) {
            User::whereKey($id)->update(['activo' => false]);
        }
    }

    public function toggleActivo(int $id): void
    {
        abort_unless($this->esSuperAdmin(), 403);

        $empleado = Empleado::where('id_empleado', $id)->firstOrFail();
        $empleado->update(['esta_activo' => ! $empleado->esta_activo]);

        $this->mensajeExito = $empleado->esta_activo
            ? 'Empleado activado correctamente.'
            : 'Empleado desactivado correctamente.';
    }

    public function eliminar(int $id): void
    {
        abort_unless($this->esSuperAdmin(), 403);

        $empleado = Empleado::where('id_empleado', $id)->firstOrFail();

        if ($empleado->id_usuario) {
            User::whereKey($empleado->id_usuario)->update(['activo' => false]);
        }

        $empleado->delete();

        $this->mensajeExito = 'Empleado eliminado correctamente.';
    }

    /**
     * Abre la matriz de seguridad granular del empleado. Solo el Super Admin
     * puede configurar los permisos directos de un usuario.
     */
    public function abrirModalPermisos(int $id): void
    {
        abort_unless($this->esSuperAdmin(), 403);

        // Estado limpio antes de abrir el modal: evita heredar permisos de otro empleado.
        $this->cerrarModalPermisos();

        $empleado = Empleado::with('usuario')->where('id_empleado', $id)->firstOrFail();

        if ($empleado->usuario === null) {
            $this->mensajeError = 'Este empleado no tiene acceso al sistema; asígnale una cuenta para configurar sus permisos.';
            $this->reset('mensajeExito');

            return;
        }

        $this->empleadoIdPermisos = $empleado->id_empleado;
        $this->empleadoNombrePermisos = trim($empleado->nombre.' '.$empleado->apellidos);
        $this->permisosUsuario = $empleado->usuario->getDirectPermissions()
            ->pluck('name')
            ->all();
        $this->resetValidation();
        $this->mostrarModalPermisos = true;
        $this->reset('mensajeError');
    }

    public function cerrarModalPermisos(): void
    {
        $this->mostrarModalPermisos = false;
        $this->reset('empleadoIdPermisos', 'empleadoNombrePermisos', 'permisosUsuario');
        $this->resetValidation();
    }

    /**
     * Sincroniza los permisos directos (Spatie) del usuario vinculado al empleado.
     */
    public function guardarPermisosGranulares(): void
    {
        abort_unless($this->esSuperAdmin(), 403);

        $empleado = Empleado::with('usuario')->where('id_empleado', $this->empleadoIdPermisos)->firstOrFail();

        abort_unless($empleado->usuario !== null, 403);

        $nombresValidos = Permission::pluck('name')->all();

        $nombres = array_values(array_intersect($this->permisosUsuario, $nombresValidos));

        $empleado->usuario->syncPermissions($nombres);

        $this->mensajeExito = 'Permisos del empleado actualizados correctamente.';
        $this->cerrarModalPermisos();
    }
}
