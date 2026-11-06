<div>
    <x-aviso-temporal :mensaje="$mensajeExito" tipo="success" clave="roles-exito" />
    <x-aviso-temporal :mensaje="$mensajeError" tipo="error" clave="roles-error" />

    @include('roles.partials.header')

    @php
        $totalRoles = $this->roles->count();
    @endphp

    @include('roles.partials.table')

    @include('roles.partials.modal-eliminar')
</div>