<?php

namespace App\Livewire;

use App\Models\Cliente;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.app')]
class Clientes extends Component
{
    use WithPagination;

    public const POR_PAGINA = 9;

    public const FILTRO_TODOS = 'todos';

    public const FILTRO_CON_RESERVAS = 'con-reservas';

    public const FILTRO_ESTANCIA_ACTIVA = 'estancia-activa';

    public const FILTRO_SIN_RESERVAS = 'sin-reservas';

    /**
     * @var array<string, string>
     */
    public const OPCIONES_ESTADO = [
        self::FILTRO_TODOS => 'Todos los huéspedes',
        self::FILTRO_CON_RESERVAS => 'Con reservas',
        self::FILTRO_ESTANCIA_ACTIVA => 'Con estancia activa',
        self::FILTRO_SIN_RESERVAS => 'Sin reservas',
    ];

    #[Url(as: 'buscar', except: '')]
    public string $search = '';

    #[Url(as: 'estado', except: self::FILTRO_TODOS)]
    public string $filtroEstado = self::FILTRO_TODOS;

    public bool $mostrarModal = false;

    public ?int $clienteId = null;

    public string $nombre = '';

    public string $apellido = '';

    public string $email = '';

    public string $telefono = '';

    public string $tipo_identificacion = '';

    public string $numero_identificacion = '';

    public ?string $mensajeExito = null;

    public ?string $mensajeError = null;

    public function mount(): void
    {
        if (! $this->esFiltroValido($this->filtroEstado)) {
            $this->filtroEstado = self::FILTRO_TODOS;
        }
    }

    public function render(): View
    {
        return view('livewire.clientes.index');
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
        $this->reset('mensajeExito', 'mensajeError');
    }

    public function updatedFiltroEstado(): void
    {
        if (! $this->esFiltroValido($this->filtroEstado)) {
            $this->filtroEstado = self::FILTRO_TODOS;
        }

        $this->resetPage();
        $this->reset('mensajeExito', 'mensajeError');
    }

    public function limpiarFiltros(): void
    {
        $this->search = '';
        $this->filtroEstado = self::FILTRO_TODOS;
        $this->resetPage();
        $this->reset('mensajeExito', 'mensajeError');
    }

    public function crear(): void
    {
        $this->resetFormulario();
        $this->mostrarModal = true;
        $this->dispatch('modal-show', name: 'cliente-form');
    }

    public function editar(int $id): void
    {
        $cliente = Cliente::findOrFail($id);

        $this->clienteId = $cliente->id;
        $this->nombre = $cliente->nombre;
        $this->apellido = $cliente->apellido;
        $this->email = $cliente->email ?? '';
        $this->telefono = $cliente->telefono ?? '';
        $this->tipo_identificacion = $cliente->tipo_identificacion ?? '';
        $this->numero_identificacion = $cliente->numero_identificacion ?? '';

        $this->resetValidation();
        $this->reset('mensajeExito', 'mensajeError');

        $this->mostrarModal = true;
        $this->dispatch('modal-show', name: 'cliente-form');
    }

    public function cerrarModal(): void
    {
        $this->resetFormulario();
        $this->mostrarModal = false;
        $this->dispatch('modal-close', name: 'cliente-form');
    }

    public function guardar(): void
    {
        $permiso = $this->clienteId === null ? 'clientes.crear' : 'clientes.editar';

        if (! auth()->user()?->can($permiso)) {
            $this->mensajeError = 'No tienes permiso para guardar clientes.';
            $this->mensajeExito = null;

            return;
        }

        $this->validate([
            'nombre' => ['required', 'string', 'max:100'],
            'apellido' => ['required', 'string', 'max:100'],
            'email' => ['nullable', 'email', 'max:255'],
            'telefono' => ['nullable', 'string', 'max:20'],
            'tipo_identificacion' => ['nullable', 'in:INE,Pasaporte'],
            'numero_identificacion' => ['nullable', 'string', 'max:50'],
        ]);

        $datos = [
            'nombre' => trim($this->nombre),
            'apellido' => trim($this->apellido),
            'email' => $this->email !== '' ? trim($this->email) : null,
            'telefono' => $this->telefono !== '' ? trim($this->telefono) : null,
            'tipo_identificacion' => $this->tipo_identificacion !== '' ? $this->tipo_identificacion : null,
            'numero_identificacion' => $this->numero_identificacion !== '' ? trim($this->numero_identificacion) : null,
        ];

        if ($this->clienteId !== null) {
            Cliente::findOrFail($this->clienteId)->update($datos);
            $this->mensajeExito = 'Cliente actualizado correctamente.';
        } else {
            Cliente::create($datos);
            $this->mensajeExito = 'Cliente creado correctamente.';
        }

        $this->reset('mensajeError');
        $this->resetFormulario();
        $this->mostrarModal = false;
        $this->dispatch('modal-close', name: 'cliente-form');
    }

    public function eliminar(int $id): void
    {
        if (! auth()->user()?->can('clientes.eliminar')) {
            $this->mensajeError = 'No tienes permiso para eliminar clientes.';
            $this->mensajeExito = null;

            return;
        }

        $cliente = Cliente::withCount('reservas')->findOrFail($id);

        if (($cliente->reservas_count ?? 0) > 0) {
            $this->mensajeError = 'No se puede eliminar a este cliente porque tiene reservaciones registradas.';
            $this->mensajeExito = null;

            return;
        }

        $cliente->delete();

        $this->mensajeExito = 'Cliente eliminado correctamente.';
        $this->mensajeError = null;
    }

    /**
     * @return LengthAwarePaginator<int, Cliente>
     */
    #[Computed]
    public function clientes(): LengthAwarePaginator
    {
        return Cliente::query()
            ->withCount([
                'reservas',
                'reservas as reservas_activas_count' => fn (Builder $query) => $query
                    ->whereIn('estado', ['Pendiente', 'Confirmada']),
                'reservas as estancias_activas_count' => fn (Builder $query) => $query
                    ->where('estado', 'Confirmada'),
            ])
            ->when($this->search !== '', function (Builder $query): void {
                $palabras = preg_split('/\s+/u', trim($this->search)) ?: [];

                $query->where(function (Builder $busqueda) use ($palabras): void {
                    foreach ($palabras as $palabra) {
                        $termino = '%'.$palabra.'%';

                        $busqueda->where(function (Builder $campo) use ($termino): void {
                            $campo->where('nombre', 'like', $termino)
                                ->orWhere('apellido', 'like', $termino)
                                ->orWhere('email', 'like', $termino)
                                ->orWhere('telefono', 'like', $termino)
                                ->orWhere('numero_identificacion', 'like', $termino);
                        });
                    }
                });
            })
            ->when($this->filtroEstado !== self::FILTRO_TODOS, function (Builder $query): void {
                match ($this->filtroEstado) {
                    self::FILTRO_CON_RESERVAS => $query->has('reservas'),
                    self::FILTRO_ESTANCIA_ACTIVA => $query->whereHas(
                        'reservas',
                        fn (Builder $reserva) => $reserva->where('estado', 'Confirmada'),
                    ),
                    self::FILTRO_SIN_RESERVAS => $query->doesntHave('reservas'),
                    default => null,
                };
            })
            ->orderBy('nombre')
            ->orderBy('apellido')
            ->paginate(self::POR_PAGINA)
            ->withQueryString();
    }

    #[Computed]
    public function totalClientes(): int
    {
        return Cliente::count();
    }

    #[Computed]
    public function hayFiltrosActivos(): bool
    {
        return $this->search !== '' || $this->filtroEstado !== self::FILTRO_TODOS;
    }

    /**
     * @return array{etiqueta: string, clases: string, punto: string}
     */
    public function estadoCliente(Cliente $cliente): array
    {
        if (($cliente->estancias_activas_count ?? 0) > 0) {
            return [
                'etiqueta' => 'En estancia',
                'clases' => 'bg-emerald-50 text-emerald-700 ring-emerald-200 dark:bg-emerald-500/10 dark:text-emerald-300 dark:ring-emerald-500/30',
                'punto' => 'bg-emerald-500',
            ];
        }

        if (($cliente->reservas_activas_count ?? 0) > 0) {
            return [
                'etiqueta' => 'Reserva activa',
                'clases' => 'bg-amber-50 text-amber-700 ring-amber-200 dark:bg-amber-500/10 dark:text-amber-300 dark:ring-amber-500/30',
                'punto' => 'bg-amber-500',
            ];
        }

        if (($cliente->reservas_count ?? 0) > 0) {
            return [
                'etiqueta' => 'Con historial',
                'clases' => 'bg-sky-50 text-sky-700 ring-sky-200 dark:bg-sky-500/10 dark:text-sky-300 dark:ring-sky-500/30',
                'punto' => 'bg-sky-500',
            ];
        }

        return [
            'etiqueta' => 'Sin reservas',
            'clases' => 'bg-slate-100 text-slate-600 ring-slate-200 dark:bg-zinc-700/60 dark:text-zinc-300 dark:ring-zinc-600',
            'punto' => 'bg-slate-400',
        ];
    }

    public function iniciales(Cliente $cliente): string
    {
        $partes = preg_split('/\s+/u', trim($cliente->nombre.' '.$cliente->apellido)) ?: [];

        $iniciales = collect($partes)
            ->filter()
            ->take(2)
            ->map(fn (string $parte): string => Str::upper(Str::substr($parte, 0, 1)))
            ->implode('');

        return $iniciales !== '' ? $iniciales : '?';
    }

    public function clasesAvatar(Cliente $cliente): string
    {
        $paletas = [
            'bg-amber-100 text-amber-700 ring-amber-200 dark:bg-amber-500/15 dark:text-amber-300 dark:ring-amber-500/30',
            'bg-emerald-100 text-emerald-700 ring-emerald-200 dark:bg-emerald-500/15 dark:text-emerald-300 dark:ring-emerald-500/30',
            'bg-sky-100 text-sky-700 ring-sky-200 dark:bg-sky-500/15 dark:text-sky-300 dark:ring-sky-500/30',
            'bg-violet-100 text-violet-700 ring-violet-200 dark:bg-violet-500/15 dark:text-violet-300 dark:ring-violet-500/30',
            'bg-rose-100 text-rose-700 ring-rose-200 dark:bg-rose-500/15 dark:text-rose-300 dark:ring-rose-500/30',
        ];

        return $paletas[$cliente->id % count($paletas)];
    }

    public function claveTarjeta(Cliente $cliente): string
    {
        return 'cliente-'.$cliente->id.'-'.md5(json_encode([
            $cliente->nombre,
            $cliente->apellido,
            $cliente->email,
            $cliente->telefono,
            $cliente->tipo_identificacion,
            $cliente->numero_identificacion,
            $cliente->reservas_count,
            $cliente->reservas_activas_count,
            $cliente->estancias_activas_count,
        ]));
    }

    public function puedeEliminarse(Cliente $cliente): bool
    {
        return ($cliente->reservas_count ?? 0) === 0;
    }

    private function resetFormulario(): void
    {
        $this->reset([
            'clienteId',
            'nombre',
            'apellido',
            'email',
            'telefono',
            'tipo_identificacion',
            'numero_identificacion',
        ]);

        $this->resetValidation();
    }

    private function esFiltroValido(string $filtro): bool
    {
        return array_key_exists($filtro, self::OPCIONES_ESTADO);
    }
}
