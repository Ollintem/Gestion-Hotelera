<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Validation\ValidationException;

class LoginController extends Controller implements HasMiddleware
{
    use AuthenticatesUsers;

    public static function middleware(): array
    {
        return [
            new Middleware('guest', except: ['logout']),
            new Middleware('auth', only: ['logout']),
        ];
    }

    /**
     * The user has been authenticated.
     *
     * Block clients who try to sign in through the Personal/Staff login section:
     * the session is invalidated and an error is shown on the email field.
     *
     * @return mixed
     *
     * @throws ValidationException
     */
    protected function authenticated(Request $request, $user)
    {
        $apartado = $request->input('role');

        $esCliente = $user->hasRole('cliente');

        $accesoIncorrecto = ($apartado === 'personal' && $esCliente)
            || ($apartado === 'clientes' && ! $esCliente);

        if (! $accesoIncorrecto) {
            return;
        }

        $this->guard()->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        throw ValidationException::withMessages([
            $this->username() => $apartado === 'clientes'
                ? 'Este correo no está registrado como cliente.'
                : 'El usuario no está registrado',
        ]);
    }

    protected function redirectTo(): string
    {
        if (session()->has('reserva.pendiente')) {
            return route('reserva.confirmar');
        }

        $usuario = auth()->user();

        if ($usuario?->hasRole('cliente')) {
            return route('mis-reservaciones');
        }

        if ($usuario?->can('dashboard.ver')) {
            return '/dashboard';
        }

        if ($usuario?->can('reservaciones.ver')) {
            return route('reservaciones');
        }

        if ($usuario?->can('checkin_checkout.ver')) {
            return route('checkin-checkout');
        }

        if ($usuario?->can('limpieza.ver')) {
            return route('limpieza');
        }

        return '/';
    }
}
