<div>
    <div class="mb-4">
        <flux:heading size="xl" class="!text-slate-900 !font-bold text-2xl">Matriz de Permisos</flux:heading>
        <flux:subheading class="!text-slate-600 !font-medium">
            Vista granular de permisos: cada fila es un permiso y cada columna un rol.
        </flux:subheading>
    </div>

    <x-aviso-temporal :mensaje="$mensajeExito" tipo="success" clave="matriz-exito" />
    <x-aviso-temporal :mensaje="$mensajeError" tipo="error" clave="matriz-error" />

    @if (! $this->puedeEditar())
        <flux:callout variant="warning" icon="lock-closed" class="mb-4">
            <p>
                Solo el rol <strong>Super Admin</strong> puede modificar los permisos.
                Los controles se muestran deshabilitados para el resto de roles.
            </p>
        </flux:callout>
    @endif

    @include('matriz-permisos.partials.tabla')
</div>
