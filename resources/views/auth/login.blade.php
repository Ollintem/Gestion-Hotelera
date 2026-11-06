<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Iniciar Sesión - NovaStay</title>
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

                <!-- Selector de Rol (Toggle) -->
                <div class="mb-8 flex rounded-xl bg-slate-100 p-1.5">
                    <button id="btn-personal" onclick="setRole('personal')" class="flex-1 py-2 text-sm font-semibold rounded-lg transition-all duration-200 bg-white shadow-sm text-slate-900 border border-slate-200">
                        Personal
                    </button>
                    <button id="btn-clientes" onclick="setRole('clientes')" class="flex-1 py-2 text-sm font-semibold rounded-lg transition-all duration-200 text-slate-500 hover:text-slate-700">
                        Clientes
                    </button>
                </div>

                <!-- Cabecera Dinámica del Formulario -->
                <div class="mb-8">
                    <h2 id="form-title" class="mb-2 font-serif text-3xl font-bold text-[#0f172a]">Acceso al sistema</h2>
                    <p id="form-subtitle" class="text-sm text-slate-500">Ingresa tus credenciales de administrador</p>
                </div>

                <!-- Formulario Real -->
                <form action="{{ route('login') }}" method="POST" class="space-y-6">
                    @csrf
                    <input type="hidden" name="role" id="role-input" value="personal">

                    <div>
                        <label for="email-input" class="mb-2 block text-sm font-semibold text-slate-700">Correo electrónico</label>
                        <div class="relative">
                            <span class="pointer-events-none absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400">
                                <svg class="size-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path></svg>
                            </span>
                            <input type="email" name="email" id="email-input" class="w-full rounded-xl border border-slate-200 bg-white py-3 pl-10 pr-4 text-sm shadow-xs transition placeholder:text-slate-400 focus:border-amber-400 focus:outline-none focus:ring-2 focus:ring-amber-500/40" placeholder="usuario@novastay.com" required autofocus>
                        </div>
                    </div>

                    <div>
                        <label for="password-input" class="mb-2 block text-sm font-semibold text-slate-700">Contraseña</label>
                        <div class="relative">
                            <span class="pointer-events-none absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400">
                                <svg class="size-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path></svg>
                            </span>
                            <input type="password" name="password" id="password-input" class="w-full rounded-xl border border-slate-200 bg-white py-3 pl-10 pr-11 text-sm shadow-xs transition placeholder:text-slate-400 focus:border-amber-400 focus:outline-none focus:ring-2 focus:ring-amber-500/40" placeholder="••••••••" required autocomplete="current-password">
                            <button type="button" onclick="togglePassword()" aria-label="Mostrar contraseña" class="absolute right-3.5 top-1/2 -translate-y-1/2 text-slate-400 transition hover:text-slate-600">
                                <svg id="eye-open" class="size-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                                <svg id="eye-closed" class="size-5 hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"></path></svg>
                            </button>
                        </div>
                    </div>

                    <div class="flex items-center justify-between pt-2">
                        <div id="extra-personal" class="flex items-center">
                            <input type="checkbox" name="remember" id="remember" class="size-4 rounded border-slate-300 text-amber-600 focus:ring-amber-500 focus:ring-offset-0">
                            <label for="remember" class="ml-2 text-sm text-slate-600">Recordarme</label>
                        </div>
                        <div id="extra-clientes" class="hidden items-center">
                            <span class="text-sm text-slate-500">¿Primera vez aquí? <a href="{{ route('register') }}" class="font-semibold text-amber-600 transition hover:text-amber-700">Registrarse</a></span>
                        </div>
                        <a href="{{ route('password.request') }}" class="ml-auto text-sm font-medium text-amber-600 transition hover:text-amber-700">¿Olvidaste tu contraseña?</a>
                    </div>

                    <button type="submit" class="mt-2 w-full rounded-xl bg-amber-500 py-3.5 font-semibold text-white shadow-md transition hover:bg-amber-600 focus:outline-none focus:ring-2 focus:ring-amber-500 focus:ring-offset-2">
                        Iniciar sesión
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
                <a href="{{ route('home') }}" class="text-sm text-slate-400 transition hover:text-slate-600">&larr; Volver al inicio</a>
            </div>
        </div>
    </div>

    <!-- Columna Derecha (Imagen decorativa) -->
    <div class="relative hidden bg-slate-900 lg:flex lg:w-1/2">
        <!-- Capa 1: La imagen local conectada con asset() -->
        <div class="absolute inset-0 bg-cover bg-center opacity-80" style="background-image: url('{{ asset('images/bg-login.jpg') }}');"></div>

        <!-- Capa 2: Degradado oscuro para dar contraste -->
        <div class="absolute inset-0 bg-gradient-to-t from-[#0f172a] via-[#0f172a]/60 to-transparent"></div>

        <!-- Capa 3: Textos dinámicos -->
        <div class="relative z-10 flex h-full w-full flex-col justify-end p-16">
            <h2 id="hero-title" class="mb-4 text-4xl font-bold text-white md:text-5xl">Control Total.</h2>
            <p id="hero-subtitle" class="max-w-lg text-lg text-slate-300">Supervisa reservaciones, asigna roles y gestiona la experiencia de tus huéspedes con eficiencia y precisión.</p>
        </div>
    </div>

    <!-- Script de Mutación del DOM -->
    <script>
        function setRole(role) {
            // Elementos del formulario
            const btnPersonal = document.getElementById('btn-personal');
            const btnClientes = document.getElementById('btn-clientes');
            const title = document.getElementById('form-title');
            const subtitle = document.getElementById('form-subtitle');
            const emailInput = document.getElementById('email-input');
            const extraPersonal = document.getElementById('extra-personal');
            const extraClientes = document.getElementById('extra-clientes');
            const roleInput = document.getElementById('role-input');

            // Elementos de la imagen derecha
            const heroTitle = document.getElementById('hero-title');
            const heroSubtitle = document.getElementById('hero-subtitle');

            roleInput.value = role;

            const btnActive = 'flex-1 py-2 text-sm font-semibold rounded-lg transition-all duration-200 bg-white shadow-sm text-slate-900 border border-slate-200';
            const btnInactive = 'flex-1 py-2 text-sm font-semibold rounded-lg transition-all duration-200 text-slate-500 hover:text-slate-700';

            btnPersonal.className = role === 'personal' ? btnActive : btnInactive;
            btnClientes.className = role === 'clientes' ? btnActive : btnInactive;

            if (role === 'personal') {
                // Textos Formulario
                title.innerText = 'Acceso al sistema';
                subtitle.innerText = 'Ingresa tus credenciales de administrador';
                emailInput.placeholder = 'usuario@novastay.com';

                // Mostrar/Ocultar
                extraPersonal.classList.remove('hidden');
                extraPersonal.classList.add('flex');
                extraClientes.classList.add('hidden');

                // Textos Imagen
                heroTitle.innerText = 'Control Total.';
                heroSubtitle.innerText = 'Supervisa reservaciones, asigna roles y gestiona la experiencia de tus huéspedes con eficiencia y precisión.';
            } else {
                // Textos Formulario
                title.innerText = 'Bienvenido de vuelta';
                subtitle.innerText = 'Accede a tu cuenta para gestionar tus reservaciones';
                emailInput.placeholder = 'tu@email.com';

                // Mostrar/Ocultar
                extraPersonal.classList.add('hidden');
                extraClientes.classList.remove('hidden');
                extraClientes.classList.add('flex');

                // Textos Imagen
                heroTitle.innerText = 'Tu escape te espera.';
                heroSubtitle.innerText = 'Reserva habitaciones de lujo, revisa tu historial y prepárate para una experiencia inolvidable.';
            }
        }

        function togglePassword() {
            const input = document.getElementById('password-input');
            const open = document.getElementById('eye-open');
            const closed = document.getElementById('eye-closed');
            const show = input.type === 'password';
            input.type = show ? 'text' : 'password';
            open.classList.toggle('hidden', show);
            closed.classList.toggle('hidden', !show);
        }
    </script>
</body>
</html>