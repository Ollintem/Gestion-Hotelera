<x-layouts.app>
    <div style="padding: 24px;">

        {{-- Encabezado --}}
        <div style="margin-bottom: 24px;">
            <h1 style="font-size: 24px; font-weight: 600; color: #111827; margin: 0;">
                Editar usuario
            </h1>

            <p style="margin-top: 6px; font-size: 14px; color: #6b7280;">
                Modifica la información, estado y rol del usuario.
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

            <form method="POST" action="{{ route('usuarios.update', $usuario) }}">
                @csrf
                @method('PUT')

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
                        value="{{ old('name', $usuario->name) }}"
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
                        value="{{ old('email', $usuario->email) }}"
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
                        value="{{ old('codigo_empleado', $usuario->codigo_empleado) }}"
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
                        :selected="(string) old('role', $roles->firstWhere(fn ($role) => $usuario->hasRole($role->name))?->name ?? '')"
                        :options="$roles->mapWithKeys(fn ($role) => [$role->name => $role->name])->all()"
                    />

                    @error('role')
                        <p style="margin-top: 5px; color: #dc2626; font-size: 13px;">
                            {{ $message }}
                        </p>
                    @enderror
                </div>

                {{-- Estado --}}
                <div style="margin-bottom: 20px;">
                    <label
                        for="activo"
                        style="display: block; margin-bottom: 6px; font-size: 14px; font-weight: 600; color: #374151;"
                    >
                        Estado
                    </label>

                    <x-dropdown
                        id="activo"
                        name="activo"
                        :selected="$usuario->activo ? '1' : '0'"
                        :options="['1' => 'Activo', '0' => 'Inactivo']"
                    />

                    @error('activo')
                        <p style="margin-top: 5px; color: #dc2626; font-size: 13px;">
                            {{ $message }}
                        </p>
                    @enderror
                </div>

                {{-- Nueva contraseña --}}
                <div style="margin-bottom: 20px;">
                    <label
                        for="password"
                        style="display: block; margin-bottom: 6px; font-size: 14px; font-weight: 600; color: #374151;"
                    >
                        Nueva contraseña
                    </label>

                    <input
                        type="password"
                        id="password"
                        name="password"
                        placeholder="Dejar vacío para conservar la actual"
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
                        Confirmar nueva contraseña
                    </label>

                    <input
                        type="password"
                        id="password_confirmation"
                        name="password_confirmation"
                        placeholder="Repite la nueva contraseña"
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
                        Guardar cambios
                    </button>

                </div>

            </form>

        </div>

    </div>
</x-layouts.app>