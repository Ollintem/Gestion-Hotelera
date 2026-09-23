```blade
<x-layouts.app>
    <div style="padding: 24px;">

        {{-- Encabezado --}}
        <div style="margin-bottom: 24px;">
            <h1 style="font-size: 24px; font-weight: 600; color: #111827; margin: 0;">
                Nuevo usuario
            </h1>

            <p style="margin-top: 6px; font-size: 14px; color: #6b7280;">
                Registra un nuevo usuario y asigna su rol dentro del sistema.
            </p>
        </div>

        {{-- Formulario --}}
        <div
            style="
                max-width: 700px;
                background: white;
                padding: 24px;
                border-radius: 12px;
                box-shadow: 0 1px 3px rgba(0,0,0,0.1);
            "
        >

            <form method="POST" action="{{ route('usuarios.store') }}">
                @csrf

                {{-- Nombre --}}
                <div style="margin-bottom: 20px;">
                    <label
                        for="name"
                        style="display: block; margin-bottom: 6px; font-size: 14px; font-weight: 600; color: #374151;"
                    >
                        Nombre
                    </label>

                    <input
                        type="text"
                        id="name"
                        name="name"
                        value="{{ old('name') }}"
                        placeholder="Nombre completo"
                        style="
                            display: block;
                            width: 100%;
                            box-sizing: border-box;
                            padding: 10px 12px;
                            border: 1px solid #d1d5db;
                            border-radius: 8px;
                            font-size: 14px;
                        "
                    >

                    @error('name')
                        <p style="margin-top: 5px; color: #dc2626; font-size: 13px;">
                            {{ $message }}
                        </p>
                    @enderror
                </div>

                {{-- Correo --}}
                <div style="margin-bottom: 20px;">
                    <label
                        for="email"
                        style="display: block; margin-bottom: 6px; font-size: 14px; font-weight: 600; color: #374151;"
                    >
                        Correo electrónico
                    </label>

                    <input
                        type="email"
                        id="email"
                        name="email"
                        value="{{ old('email') }}"
                        placeholder="correo@ejemplo.com"
                        style="
                            display: block;
                            width: 100%;
                            box-sizing: border-box;
                            padding: 10px 12px;
                            border: 1px solid #d1d5db;
                            border-radius: 8px;
                            font-size: 14px;
                        "
                    >

                    @error('email')
                        <p style="margin-top: 5px; color: #dc2626; font-size: 13px;">
                            {{ $message }}
                        </p>
                    @enderror
                </div>

                {{-- Código de empleado --}}
                <div style="margin-bottom: 20px;">
                    <label
                        for="codigo_empleado"
                        style="display: block; margin-bottom: 6px; font-size: 14px; font-weight: 600; color: #374151;"
                    >
                        Código de empleado
                    </label>

                    <input
                        type="text"
                        id="codigo_empleado"
                        name="codigo_empleado"
                        value="{{ old('codigo_empleado') }}"
                        placeholder="Ej. EMP-001"
                        style="
                            display: block;
                            width: 100%;
                            box-sizing: border-box;
                            padding: 10px 12px;
                            border: 1px solid #d1d5db;
                            border-radius: 8px;
                            font-size: 14px;
                        "
                    >

                    @error('codigo_empleado')
                        <p style="margin-top: 5px; color: #dc2626; font-size: 13px;">
                            {{ $message }}
                        </p>
                    @enderror
                </div>

                {{-- Rol --}}
                <div style="margin-bottom: 20px;">
                    <label
                        for="role"
                        style="display: block; margin-bottom: 6px; font-size: 14px; font-weight: 600; color: #374151;"
                    >
                        Rol
                    </label>

                    <x-dropdown
                        id="role"
                        name="role"
                        :selected="(string) old('role', '')"
                        placeholder="Selecciona un rol"
                        :options="$roles->mapWithKeys(fn ($role) => [$role->name => $role->name])->all()"
                    />

                    @error('role')
                        <p style="margin-top: 5px; color: #dc2626; font-size: 13px;">
                            {{ $message }}
                        </p>
                    @enderror
                </div>

                {{-- Contraseña --}}
                <div style="margin-bottom: 20px;">
                    <label
                        for="password"
                        style="display: block; margin-bottom: 6px; font-size: 14px; font-weight: 600; color: #374151;"
                    >
                        Contraseña
                    </label>

                    <input
                        type="password"
                        id="password"
                        name="password"
                        placeholder="Contraseña"
                        style="
                            display: block;
                            width: 100%;
                            box-sizing: border-box;
                            padding: 10px 12px;
                            border: 1px solid #d1d5db;
                            border-radius: 8px;
                            font-size: 14px;
                        "
                    >

                    @error('password')
                        <p style="margin-top: 5px; color: #dc2626; font-size: 13px;">
                            {{ $message }}
                        </p>
                    @enderror
                </div>

                {{-- Confirmar contraseña --}}
                <div style="margin-bottom: 24px;">
                    <label
                        for="password_confirmation"
                        style="display: block; margin-bottom: 6px; font-size: 14px; font-weight: 600; color: #374151;"
                    >
                        Confirmar contraseña
                    </label>

                    <input
                        type="password"
                        id="password_confirmation"
                        name="password_confirmation"
                        placeholder="Repite la contraseña"
                        style="
                            display: block;
                            width: 100%;
                            box-sizing: border-box;
                            padding: 10px 12px;
                            border: 1px solid #d1d5db;
                            border-radius: 8px;
                            font-size: 14px;
                        "
                    >
                </div>

                {{-- Botones --}}
                <div
                    style="
                        display: flex;
                        align-items: center;
                        gap: 12px;
                        padding-top: 8px;
                        border-top: 1px solid #e5e7eb;
                    "
                >

                    {{-- Cancelar --}}
                    <a
                        href="{{ route('usuarios.index') }}"
                        style="
                            display: inline-block;
                            padding: 10px 20px;
                            background: white;
                            color: #374151;
                            border: 1px solid #d1d5db;
                            border-radius: 8px;
                            font-size: 14px;
                            font-weight: 600;
                            text-decoration: none;
                        "
                    >
                        Cancelar
                    </a>

                    {{-- Crear usuario --}}
                    <button
                        type="submit"
                        style="
                            display: inline-block;
                            padding: 10px 20px;
                            background: #111827;
                            color: white;
                            border: none;
                            border-radius: 8px;
                            font-size: 14px;
                            font-weight: 600;
                            cursor: pointer;
                        "
                    >
                        Crear usuario
                    </button>

                </div>

            </form>

        </div>

    </div>
</x-layouts.app>
```
