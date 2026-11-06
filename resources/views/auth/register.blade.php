<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Crear Cuenta - NovaStay</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans antialiased text-slate-900 bg-slate-50 min-h-screen flex">

    <x-flash-messages />

    <!-- Columna Izquierda (Formulario) -->
    <div class="w-full lg:w-1/2 flex flex-col items-center justify-center p-8 md:p-12">

        <div class="w-full max-w-md">

            <!-- Logo Header -->
            <div class="flex flex-col items-center justify-center mb-8">
                <div class="flex aspect-square size-12 items-center justify-center rounded-xl bg-amber-600 shadow-md shadow-amber-950/20">
                    <x-app-logo-icon class="size-7 fill-current text-white" />
                </div>
                <h1 class="mt-4 font-serif text-2xl font-bold leading-none text-[#0f172a]">NovaStay</h1>
                <span class="mt-1 text-[11px] font-semibold uppercase tracking-widest text-amber-600">Hotel Management</span>
            </div>

            <!-- Tarjeta Blanca Centrada -->
            <div class="rounded-2xl border border-slate-100 bg-white p-8 shadow-sm sm:p-10">

                <div class="mb-8">
                    <h2 class="mb-2 font-serif text-3xl font-bold text-[#0f172a]">Crea tu cuenta</h2>
                    <p class="text-sm text-slate-500">Regístrate como huésped y comienza a reservar tu estancia en NovaStay.</p>
                </div>

                @if (session()->has('reserva.pendiente'))
                    <div class="mb-6 rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-800">
                        <p class="font-semibold text-slate-900">Reservación en espera</p>
                        <p>Tienes una habitación pendiente de confirmar. Al crear tu cuenta te llevaremos directo a completarla.</p>
                    </div>
                @endif

                <form action="{{ route('register') }}" method="POST" class="space-y-5">
                    @csrf

                    <div>
                        <label for="name" class="mb-2 block text-sm font-semibold text-slate-700">Nombre completo</label>
                        <input type="text" name="name" id="name" value="{{ old('name') }}" required autofocus
                            class="w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm shadow-xs transition placeholder:text-slate-400 focus:border-amber-400 focus:outline-none focus:ring-2 focus:ring-amber-500/40"
                            placeholder="Tu nombre y apellidos">
                        @error('name')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="email" class="mb-2 block text-sm font-semibold text-slate-700">Correo electrónico</label>
                        <input type="email" name="email" id="email" value="{{ old('email') }}" required
                            class="w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm shadow-xs transition placeholder:text-slate-400 focus:border-amber-400 focus:outline-none focus:ring-2 focus:ring-amber-500/40"
                            placeholder="usuario@novastay.com">
                        @error('email')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
                        <div>
                            <label for="telefono" class="mb-2 block text-sm font-semibold text-slate-700">Teléfono</label>
                            <input type="tel" name="telefono" id="telefono" value="{{ old('telefono') }}"
                                class="w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm shadow-xs transition placeholder:text-slate-400 focus:border-amber-400 focus:outline-none focus:ring-2 focus:ring-amber-500/40"
                                placeholder="55 1234 5678">
                            @error('telefono')
                                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label for="password" class="mb-2 block text-sm font-semibold text-slate-700">Contraseña</label>
                            <input type="password" name="password" id="password" required
                                class="w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm shadow-xs transition placeholder:text-slate-400 focus:border-amber-400 focus:outline-none focus:ring-2 focus:ring-amber-500/40"
                                placeholder="Mínimo 8 caracteres">
                            @error('password')
                                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <div>
                        <label for="password-confirm" class="mb-2 block text-sm font-semibold text-slate-700">Confirmar contraseña</label>
                        <input type="password" name="password_confirmation" id="password-confirm" required
                            class="w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm shadow-xs transition placeholder:text-slate-400 focus:border-amber-400 focus:outline-none focus:ring-2 focus:ring-amber-500/40"
                            placeholder="Repite la contraseña">
                    </div>

                    <button type="submit"
                        class="mt-2 w-full rounded-xl bg-amber-500 py-3.5 font-semibold text-white shadow-md transition hover:bg-amber-600 focus:outline-none focus:ring-2 focus:ring-amber-500 focus:ring-offset-2">
                        Crear cuenta
                    </button>
                </form>

                @if ($errors->any())
                    <div class="mt-6 rounded-lg border border-red-100 bg-red-50 px-4 py-3 text-sm text-red-700">
                        @foreach ($errors->all() as $error)
                            <p>{{ $error }}</p>
                        @endforeach
                    </div>
                @endif
            </div>

            <div class="text-center mt-8">
                <p class="text-sm text-slate-500">
                    ¿Ya tienes una cuenta?
                    <a href="{{ route('login') }}" class="font-medium text-amber-600 transition hover:text-amber-700">Inicia sesión</a>
                </p>
                <a href="{{ route('home') }}" class="mt-2 inline-block text-sm text-slate-400 transition hover:text-slate-600">&larr; Volver al inicio</a>
            </div>
        </div>
    </div>

    <!-- Columna Derecha (Imagen decorativa) -->
    <div class="relative hidden bg-slate-900 lg:flex lg:w-1/2">
        <div class="absolute inset-0 bg-cover bg-center opacity-80" style="background-image: url('{{ asset('images/bg-login.jpg') }}');"></div>
        <div class="absolute inset-0 bg-gradient-to-t from-[#0f172a] via-[#0f172a]/60 to-transparent"></div>

        <div class="relative z-10 flex h-full w-full flex-col justify-end p-16">
            <h2 class="mb-4 text-4xl font-bold text-white md:text-5xl">Tu escape te espera.</h2>
            <p class="max-w-lg text-lg text-slate-300">Crea tu cuenta de huésped, reserva habitaciones de lujo y prepárate para una experiencia inolvidable.</p>
        </div>
    </div>

</body>
</html>