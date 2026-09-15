<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar Sesión - Novastay</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-white font-sans text-slate-900 antialiased overflow-hidden">
    <div class="min-h-screen flex">
        
        <!-- Sección del Formulario (Izquierda) -->
        <div class="w-full md:w-1/2 flex items-center justify-center p-8 sm:p-12 lg:p-24 bg-white overflow-y-auto">
            <div class="w-full max-w-md">
                <div class="mb-10">
                    <h2 class="text-3xl font-extrabold tracking-tight text-slate-900 mb-2">Bienvenido de vuelta</h2>
                    <p class="text-slate-500">Ingresa tus credenciales para acceder a tu panel de administración.</p>
                </div>

                <!-- Formulario de Autenticación de Laravel -->
                <form method="POST" action="{{ route('login') }}" class="space-y-6">
                    @csrf
                    
                    <!-- Manejo de Errores de Laravel -->
                    @if ($errors->any())
                        <div class="bg-red-50 border-l-4 border-red-500 text-red-700 p-4 rounded text-sm mb-6">
                            <strong>Ups, hubo un problema:</strong> Las credenciales no coinciden.
                        </div>
                    @endif

                    <!-- Input: Correo Electrónico -->
                    <div>
                        <label for="email" class="block text-sm font-semibold text-slate-700 mb-2">Correo Electrónico</label>
                        <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus
                            class="w-full px-4 py-3 rounded-lg border border-slate-300 focus:ring-2 focus:ring-indigo-600 focus:border-indigo-600 outline-none transition-all text-slate-700 bg-slate-50 focus:bg-white" 
                            placeholder="admin@hotel.com">
                    </div>

                    <!-- Input: Contraseña -->
                    <div>
                        <label for="password" class="block text-sm font-semibold text-slate-700 mb-2">Contraseña</label>
                        <input id="password" type="password" name="password" required
                            class="w-full px-4 py-3 rounded-lg border border-slate-300 focus:ring-2 focus:ring-indigo-600 focus:border-indigo-600 outline-none transition-all text-slate-700 bg-slate-50 focus:bg-white" 
                            placeholder="••••••••">
                    </div>

                    <!-- Recordarme y Recuperar Contraseña -->
                    <div class="flex items-center justify-between">
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="checkbox" name="remember" class="w-4 h-4 text-indigo-600 border-slate-300 rounded focus:ring-indigo-600">
                            <span class="text-sm text-slate-600">Recordarme</span>
                        </label>

                        @if (Route::has('password.request'))
                            <a href="{{ route('password.request') }}" class="text-sm font-medium text-indigo-600 hover:text-indigo-800 transition-colors">
                                ¿Olvidaste tu contraseña?
                            </a>
                        @endif
                    </div>

                    <!-- Botón de Submit -->
                    <button type="submit" class="w-full bg-slate-900 text-white font-bold py-3 px-4 rounded-lg hover:bg-indigo-600 focus:ring-4 focus:ring-indigo-200 transition-all duration-300">
                        Iniciar Sesión
                    </button>
                </form>
            </div>
        </div>

        <!-- Sección de Imagen Inmersiva (Derecha - Solo visible en pantallas medianas o grandes) -->
        <div class="hidden md:block md:w-1/2 relative">
            <!-- Imagen dinámica desde Unsplash -->
            <img src="https://picsum.photos/id/1040/1000/1000" 
                      alt="Hotel Luxury" 
                   class="absolute inset-0 w-full h-full object-cover">
            
            <!-- Overlay oscuro para dar toque premium -->
            <div class="absolute inset-0 bg-slate-900/50 mix-blend-multiply"></div>
            
            <!-- Texto superpuesto sobre la imagen -->
            <div class="absolute inset-0 flex flex-col justify-end p-12 lg:p-24 text-white z-10">
                <h3 class="text-4xl lg:text-5xl font-bold mb-4">Control Total.</h3>
                <p class="text-lg text-slate-200 max-w-md">Supervisa reservaciones, asigna roles y gestiona la experiencia de tus huéspedes con eficiencia y precisión.</p>
            </div>
        </div>
        
    </div>
</body>
</html>