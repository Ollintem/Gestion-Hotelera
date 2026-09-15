<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Novastay - Gestión Hotelera</title>
    <!-- Aquí le decimos a Laravel que compile Tailwind con Vite -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-slate-50 min-h-screen flex flex-col items-center justify-center font-sans antialiased">
    
    <!-- Contenedor Principal -->
    <div class="max-w-3xl w-full px-6 text-center">
        
        <!-- Logotipo / Título -->
        <h1 class="text-5xl md:text-6xl font-extrabold text-slate-900 tracking-tight mb-4">
            Bienvenido a <span class="text-indigo-600">Novastay</span>
        </h1>
        
        <!-- Subtítulo -->
        <p class="text-lg md:text-xl text-slate-600 mb-10">
            Experimenta el confort y la exclusividad que te mereces.
        </p>
        
        <!-- Botones de Acción -->
        <div class="flex flex-col sm:flex-row items-center justify-center gap-4">
            <!-- Botón de Reserva (Llamado a la acción principal) -->
            <a href="#" class="w-full sm:w-auto px-8 py-3 bg-indigo-600 text-white font-semibold rounded-lg shadow-md hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition-all">
                Hacer una Reserva
            </a>
            
            <!-- Botón de Login (Acceso a empleados / clientes registrados) -->
            <a href="{{ route('login') }}" class="w-full sm:w-auto px-8 py-3 bg-white text-slate-700 font-semibold rounded-lg shadow-sm border border-slate-300 hover:bg-slate-50 focus:outline-none focus:ring-2 focus:ring-slate-500 focus:ring-offset-2 transition-all">
                Iniciar Sesión
            </a>
        </div>

    </div>

</body>
</html>