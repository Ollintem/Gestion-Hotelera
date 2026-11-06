<?php

use Illuminate\Support\Facades\Password;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('components.layouts.auth')] class extends Component {
    public string $email = '';

    /**
     * Send a password reset link to the provided email address.
     */
    public function sendPasswordResetLink(): void
    {
        $this->validate([
            'email' => ['required', 'string', 'email'],
        ]);

        Password::sendResetLink($this->only('email'));

        session()->flash('status', 'Si la cuenta existe, recibirás un enlace para restablecer tu contraseña.');
    }
}; ?>

<div class="flex flex-col gap-6">
    <x-auth-header title="Recuperar contraseña" description="Escribe tu correo y te enviaremos un enlace para restablecerla" />

    <!-- Session Status -->
    <x-auth-session-status class="text-center" :status="session('status')" />

    <form wire:submit="sendPasswordResetLink" class="flex flex-col gap-6">
        <!-- Email Address -->
        <div class="grid gap-2">
            <flux:input wire:model="email" label="{{ __('Correo electrónico') }}" type="email" name="email" required autofocus placeholder="tu@email.com" />
        </div>

        <flux:button variant="primary" type="submit" class="w-full">Enviar enlace de recuperación</flux:button>
    </form>

    <div class="space-x-1 text-center text-sm text-zinc-400">
        O vuelve a
        <x-text-link href="{{ route('login') }}">iniciar sesión</x-text-link>
    </div>
</div>
