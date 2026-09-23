<?php

namespace App\Livewire;

use App\Models\Cliente;
use Illuminate\View\View;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.app')]
class Clientes extends Component
{
    public string $pagina = 'index';

    public bool $mostrarModal = false;

    public ?int $clienteId = null;

    public string $nombre = '';

    public string $apellido = '';

    public string $email = '';

    public string $telefono = '';

    public string $tipo_identificacion = '';

    public string $numero_identificacion = '';

    public ?string $mensajeExito = null;

    public function render(): View
    {
        $clientes = Cliente::withCount('reservas')->orderBy('nombre')->get();

        return match ($this->pagina) {
            'crear' => view('clientes.create', ['clientes' => $clientes]),
            'editar' => view('clientes.edit', ['clientes' => $clientes]),
            default => view('clientes.index', ['clientes' => $clientes]),
        };
    }

    public function crear(): void
    {
        $this->reset(['clienteId', 'nombre', 'apellido', 'email', 'telefono', 'tipo_identificacion', 'numero_identificacion']);
        $this->resetValidation();
        $this->reset('mensajeExito');
        $this->pagina = 'crear';
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
        $this->reset('mensajeExito');
        $this->pagina = 'editar';
    }

    public function cerrarModal(): void
    {
        $this->pagina = 'index';
        $this->reset(['clienteId', 'nombre', 'apellido', 'email', 'telefono', 'tipo_identificacion', 'numero_identificacion']);
        $this->resetValidation();
    }

    public function guardar(): void
    {
        $this->validate([
            'nombre' => ['required', 'string', 'max:100'],
            'apellido' => ['required', 'string', 'max:100'],
            'email' => ['nullable', 'email', 'max:255'],
            'telefono' => ['nullable', 'string', 'max:20'],
            'tipo_identificacion' => ['nullable', 'in:INE,Pasaporte'],
            'numero_identificacion' => ['nullable', 'string', 'max:50'],
        ]);

        $datos = [
            'nombre' => $this->nombre,
            'apellido' => $this->apellido,
            'email' => $this->email ?: null,
            'telefono' => $this->telefono ?: null,
            'tipo_identificacion' => $this->tipo_identificacion ?: null,
            'numero_identificacion' => $this->numero_identificacion ?: null,
        ];

        if ($this->clienteId) {
            Cliente::findOrFail($this->clienteId)->update($datos);
            $this->mensajeExito = 'Cliente actualizado correctamente.';
        } else {
            Cliente::create($datos);
            $this->mensajeExito = 'Cliente creado correctamente.';
        }

        $this->cerrarModal();
    }

    public function eliminar(int $id): void
    {
        Cliente::findOrFail($id)->delete();

        $this->mensajeExito = 'Cliente eliminado correctamente.';
    }
}
