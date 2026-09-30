
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Iniciar Sesión - NovaStay</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-[#fdfbf7] font-sans antialiased text-slate-900 flex items-center justify-center min-h-screen p-6">

    <div class="w-full max-w-md">
        <!-- Logo -->
        <div class="flex flex-col items-center justify-center mb-8">
            <div class="bg-amber-600 text-white p-2 rounded-lg mb-3">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path></svg>
            </div>
            <h1 class="text-2xl font-bold leading-none text-slate-900">NovaStay</h1>
            <span class="text-xs text-amber-600 uppercase tracking-widest font-semibold mt-1">Hotel Management</span>
        </div>

        <!-- Selector de Rol (Toggle) -->
        <div class="bg-slate-100 p-1 rounded-xl flex mb-8">
            <button id="btn-personal" onclick="setRole('personal')" class="flex-1 py-2 text-sm font-semibold rounded-lg transition-all duration-200 bg-white shadow text-slate-900">
                Personal
            </button>
            <button id="btn-clientes" onclick="setRole('clientes')" class="flex-1 py-2 text-sm font-semibold rounded-lg transition-all duration-200 text-slate-500 hover:text-slate-700">
                Clientes
            </button>
        </div>

        <!-- Formulario -->
        <div>
            <div class="mb-6">
                <h2 id="form-title" class="text-2xl font-bold text-[#0f172a] mb-1 font-serif">Acceso al sistema</h2>
                <p id="form-subtitle" class="text-sm text-slate-500">Ingresa tus credenciales para iniciar sesión</p>
            </div>

            <form action="#" method="POST" class="space-y-5">
                @csrf
                <!-- Rol oculto para el backend -->
                <input type="hidden" name="role" id="role-input" value="personal">

                <div>
                    <label class="block text-sm font-bold text-slate-800 mb-1.5">Correo electrónico</label>
                    <input type="email" id="email-input" class="w-full px-4 py-3 rounded-lg border border-slate-200 bg-white text-sm focus:outline-none focus:border-amber-500 focus:ring-1 focus:ring-amber-500 transition" placeholder="usuario@novastay.com" required>
                </div>

                <div>
                    <label class="block text-sm font-bold text-slate-800 mb-1.5">Contraseña</label>
                    <div class="relative">
                        <input type="password" class="w-full px-4 py-3 rounded-lg border border-slate-200 bg-white text-sm focus:outline-none focus:border-amber-500 focus:ring-1 focus:ring-amber-500 transition" placeholder="&bull;&bull;&bull;&bull;&bull;&bull;&bull;&bull;" required>
                        <button type="button" class="absolute right-3 top-3.5 text-slate-400 hover:text-slate-600">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                        </button>
                    </div>
                </div>

                <!-- Elementos dinámicos (Recordarme vs Crear cuenta) -->
                <div class="flex items-center justify-between pt-1">
                    <div id="extra-personal" class="flex items-center">
                        <input type="checkbox" id="remember" class="w-4 h-4 rounded border-slate-300 text-amber-600 focus:ring-amber-500">
                        <label for="remember" class="ml-2 text-sm text-slate-600">Recordarme</label>
                    </div>
                    
                    <a href="#" class="text-sm font-medium text-amber-600 hover:text-amber-700 ml-auto">¿Olvidaste tu contraseña?</a>
                </div>

                <button type="submit" class="w-full bg-[#0f172a] hover:bg-slate-800 text-white font-semibold py-3 rounded-lg transition shadow-md mt-4">
                    Iniciar sesión
                </button>

                <div id="extra-clientes" class="text-center mt-6 hidden">
                    <p class="text-sm text-slate-600">¿Primera vez aquí? <a href="{{ route('register') }}" class="text-amber-600 font-semibold hover:underline">Crear cuenta</a></p>
                </div>
            </form>
            
            <div class="text-center mt-8">
                <a href="{{ route('home') }}" class="text-sm text-slate-400 hover:text-slate-600 transition">&larr; Volver al inicio</a>
            </div>
        </div>
    </div>

    <script>
        // Lógica de manipulación del DOM para el Toggle
        function setRole(role) {
            const btnPersonal = document.getElementById('btn-personal');
            const btnClientes = document.getElementById('btn-clientes');
            const title = document.getElementById('form-title');
            const subtitle = document.getElementById('form-subtitle');
            const emailInput = document.getElementById('email-input');
            const extraPersonal = document.getElementById('extra-personal');
            const extraClientes = document.getElementById('extra-clientes');
            const roleInput = document.getElementById('role-input');

            // Actualizar valor oculto para el backend
            roleInput.value = role;

            if (role === 'personal') {
                // Estilos del botón activo
                btnPersonal.className = 'flex-1 py-2 text-sm font-semibold rounded-lg transition-all duration-200 bg-white shadow text-slate-900';
                btnClientes.className = 'flex-1 py-2 text-sm font-semibold rounded-lg transition-all duration-200 text-slate-500 hover:text-slate-700';

                // Textos
                title.innerText = 'Acceso al sistema';
                subtitle.innerText = 'Ingresa tus credenciales de administrador';
                emailInput.placeholder = 'usuario@novastay.com';
                
                // Mostrar/Ocultar extras
                extraPersonal.classList.remove('hidden');
                extraClientes.classList.add('hidden');
            } else {
                // Estilos del botón activo
                btnClientes.className = 'flex-1 py-2 text-sm font-semibold rounded-lg transition-all duration-200 bg-white shadow text-slate-900';
                btnPersonal.className = 'flex-1 py-2 text-sm font-semibold rounded-lg transition-all duration-200 text-slate-500 hover:text-slate-700';

                // Textos
                title.innerText = 'Bienvenido de vuelta';
                subtitle.innerText = 'Accede a tu cuenta para gestionar tus reservaciones';
                emailInput.placeholder = 'tu@email.com';
                
                // Mostrar/Ocultar extras
                extraPersonal.classList.add('hidden');
                extraClientes.classList.remove('hidden');
            }
        }
    </script>
</body>
</html>