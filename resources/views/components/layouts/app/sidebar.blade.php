<!DOCTYPE html>

<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

    <head>
        @include('partials.head')
    </head>

    <body class="min-h-screen bg-slate-100 text-slate-700 transition-colors duration-300 dark:bg-slate-900 dark:text-slate-100">

        {{-- ==========================================================
             NOTIFICACIONES FLOTANTES
             Mensajes flash de sesión; se ocultan solos a los 5 s.
             ========================================================== --}}

        <x-flash-messages />

        {{-- ==========================================================
             MENÚ LATERAL
             Fondo claro (blanco) con acentos ámbar en modo Light.
             ========================================================== --}}

        <flux:sidebar
            sticky
            stashable
            class="border-r border-slate-200 bg-white shadow-sm shadow-slate-200/50 dark:border-slate-700/70 dark:bg-slate-900 dark:shadow-none"
        >

            {{-- Botón para cerrar el sidebar en dispositivos pequeños --}}
            <flux:sidebar.toggle
                class="lg:hidden !text-slate-500 hover:!text-slate-900 dark:!text-slate-400 dark:hover:!text-slate-100"
                icon="x-mark"
            />

            {{-- ======================================================
                 LOGOTIPO
                 ====================================================== --}}

            <a
                href="{{ route('dashboard') }}"
                class="mb-7 flex items-center gap-3 rounded-lg px-2 transition hover:bg-slate-100"
                wire:navigate
            >

                <div
                    class="flex aspect-square size-9 shrink-0 items-center justify-center rounded-lg bg-amber-600 shadow-md shadow-amber-600/30"
                >
                    <x-app-logo-icon class="size-5 fill-current text-white" />
                </div>

                <div class="flex flex-col leading-tight">
                    <span class="font-serif text-sm font-bold tracking-wide text-slate-900 dark:text-white">
                        NovaStay
                    </span>
                    <span class="text-[10px] font-semibold uppercase tracking-widest text-amber-600 dark:text-amber-400">
                        Hotel Management
                    </span>
                </div>

            </a>

            {{-- ======================================================
                 MENÚ PRINCIPAL
                 ====================================================== --}}

            <flux:navlist>

                {{-- ==================================================
                     PRINCIPAL
                     ================================================== --}}

                <flux:navlist.group
                    heading="Principal"
                    class="grid"
                >

                    {{-- Dashboard --}}
                    @can('dashboard.ver')

                        <flux:navlist.item
                            icon="home"
                            :href="route('dashboard')"
                            :current="request()->routeIs('dashboard')"
                            wire:navigate
                            class="rounded-lg border border-transparent !text-slate-700 hover:!bg-amber-50 hover:!text-amber-600 data-current:!border-amber-200 data-current:!bg-amber-100 data-current:!text-amber-700 data-current:!font-semibold dark:!text-slate-300 dark:hover:!bg-slate-800 dark:hover:!text-amber-400 dark:data-current:!border-amber-500/50 dark:data-current:!bg-slate-800 dark:data-current:!text-amber-400"
                        >
                            Dashboard
                        </flux:navlist.item>

                    @endcan

                </flux:navlist.group>

                {{-- ==================================================
                     OPERACIÓN
                     ================================================== --}}

                <flux:navlist.group
                    heading="Operación"
                    class="mt-5 grid"
                >

                    {{-- Reservaciones --}}
                    @can('reservaciones.ver')

                        <flux:navlist.item
                            icon="calendar-days"
                            :href="route('reservaciones')"
                            :current="request()->routeIs('reservaciones')"
                            wire:navigate
                            class="rounded-lg border border-transparent !text-slate-700 hover:!bg-amber-50 hover:!text-amber-600 data-current:!border-amber-200 data-current:!bg-amber-100 data-current:!text-amber-700 data-current:!font-semibold dark:!text-slate-300 dark:hover:!bg-slate-800 dark:hover:!text-amber-400 dark:data-current:!border-amber-500/50 dark:data-current:!bg-slate-800 dark:data-current:!text-amber-400"
                        >
                            Reservaciones
                        </flux:navlist.item>

                    @endcan

                    {{-- Habitaciones --}}
                    @can('habitaciones.ver')

                        <flux:navlist.item
                            icon="building-office-2"
                            :href="route('habitaciones')"
                            :current="request()->routeIs('habitaciones')"
                            wire:navigate
                            class="rounded-lg border border-transparent !text-slate-700 hover:!bg-amber-50 hover:!text-amber-600 data-current:!border-amber-200 data-current:!bg-amber-100 data-current:!text-amber-700 data-current:!font-semibold dark:!text-slate-300 dark:hover:!bg-slate-800 dark:hover:!text-amber-400 dark:data-current:!border-amber-500/50 dark:data-current:!bg-slate-800 dark:data-current:!text-amber-400"
                        >
                            Habitaciones
                        </flux:navlist.item>

                    @endcan

                    {{-- Clientes --}}
                    @can('clientes.ver')

                        <flux:navlist.item
                            icon="users"
                            :href="route('clientes')"
                            :current="request()->routeIs('clientes')"
                            wire:navigate
                            class="rounded-lg border border-transparent !text-slate-700 hover:!bg-amber-50 hover:!text-amber-600 data-current:!border-amber-200 data-current:!bg-amber-100 data-current:!text-amber-700 data-current:!font-semibold dark:!text-slate-300 dark:hover:!bg-slate-800 dark:hover:!text-amber-400 dark:data-current:!border-amber-500/50 dark:data-current:!bg-slate-800 dark:data-current:!text-amber-400"
                        >
                            Clientes
                        </flux:navlist.item>

                    @endcan

                    {{-- Check-in / Check-out --}}
                    @can('checkin_checkout.ver')

                        <flux:navlist.item
                            icon="arrow-right-start-on-rectangle"
                            :href="route('checkin-checkout')"
                            :current="request()->routeIs('checkin-checkout')"
                            wire:navigate
                            class="rounded-lg border border-transparent !text-slate-700 hover:!bg-amber-50 hover:!text-amber-600 data-current:!border-amber-200 data-current:!bg-amber-100 data-current:!text-amber-700 data-current:!font-semibold dark:!text-slate-300 dark:hover:!bg-slate-800 dark:hover:!text-amber-400 dark:data-current:!border-amber-500/50 dark:data-current:!bg-slate-800 dark:data-current:!text-amber-400"
                        >
                            Check-in / Check-out
                        </flux:navlist.item>

                    @endcan

                    {{-- Limpieza --}}
                    @can('limpieza.ver')

                        <flux:navlist.item
                            icon="sparkles"
                            :href="route('limpieza')"
                            :current="request()->routeIs('limpieza')"
                            wire:navigate
                            class="rounded-lg border border-transparent !text-slate-700 hover:!bg-amber-50 hover:!text-amber-600 data-current:!border-amber-200 data-current:!bg-amber-100 data-current:!text-amber-700 data-current:!font-semibold dark:!text-slate-300 dark:hover:!bg-slate-800 dark:hover:!text-amber-400 dark:data-current:!border-amber-500/50 dark:data-current:!bg-slate-800 dark:data-current:!text-amber-400"
                        >
                            Limpieza
                        </flux:navlist.item>

                    @endcan

                    {{-- Pagos --}}
                    @can('pagos.ver')

                        <flux:navlist.item
                            icon="credit-card"
                            :href="route('pagos')"
                            :current="request()->routeIs('pagos')"
                            wire:navigate
                            class="rounded-lg border border-transparent !text-slate-700 hover:!bg-amber-50 hover:!text-amber-600 data-current:!border-amber-200 data-current:!bg-amber-100 data-current:!text-amber-700 data-current:!font-semibold dark:!text-slate-300 dark:hover:!bg-slate-800 dark:hover:!text-amber-400 dark:data-current:!border-amber-500/50 dark:data-current:!bg-slate-800 dark:data-current:!text-amber-400"
                        >
                            Pagos
                        </flux:navlist.item>

                    @endcan

                    {{-- Servicios --}}
                    @can('servicios.ver')

                        <flux:navlist.item
                            icon="gift"
                            :href="route('servicios')"
                            :current="request()->routeIs('servicios')"
                            wire:navigate
                            class="rounded-lg border border-transparent !text-slate-700 hover:!bg-amber-50 hover:!text-amber-600 data-current:!border-amber-200 data-current:!bg-amber-100 data-current:!text-amber-700 data-current:!font-semibold dark:!text-slate-300 dark:hover:!bg-slate-800 dark:hover:!text-amber-400 dark:data-current:!border-amber-500/50 dark:data-current:!bg-slate-800 dark:data-current:!text-amber-400"
                        >
                            Servicios
                        </flux:navlist.item>

                    @endcan

                </flux:navlist.group>

                {{-- ==================================================
                     ADMINISTRACIÓN
                     ================================================== --}}

                <flux:navlist.group
                    heading="Administración"
                    class="mt-5 grid"
                >

                    {{-- Gastos --}}
                    @can('gastos.ver')

                        <flux:navlist.item
                            icon="banknotes"
                            :href="route('gastos')"
                            :current="request()->routeIs('gastos')"
                            wire:navigate
                            class="rounded-lg border border-transparent !text-slate-700 hover:!bg-amber-50 hover:!text-amber-600 data-current:!border-amber-200 data-current:!bg-amber-100 data-current:!text-amber-700 data-current:!font-semibold dark:!text-slate-300 dark:hover:!bg-slate-800 dark:hover:!text-amber-400 dark:data-current:!border-amber-500/50 dark:data-current:!bg-slate-800 dark:data-current:!text-amber-400"
                        >
                            Gastos
                        </flux:navlist.item>

                    @endcan

                    {{-- Empleados --}}
                    @can('empleados.ver')

                        <flux:navlist.item
                            icon="identification"
                            :href="route('empleados')"
                            :current="request()->routeIs('empleados')"
                            wire:navigate
                            class="rounded-lg border border-transparent !text-slate-700 hover:!bg-amber-50 hover:!text-amber-600 data-current:!border-amber-200 data-current:!bg-amber-100 data-current:!text-amber-700 data-current:!font-semibold dark:!text-slate-300 dark:hover:!bg-slate-800 dark:hover:!text-amber-400 dark:data-current:!border-amber-500/50 dark:data-current:!bg-slate-800 dark:data-current:!text-amber-400"
                        >
                            Empleados
                        </flux:navlist.item>

                    @endcan

                    {{-- Temporadas --}}
                    @can('temporadas.ver')

                        <flux:navlist.item
                            icon="sun"
                            :href="route('temporadas')"
                            :current="request()->routeIs('temporadas')"
                            wire:navigate
                            class="rounded-lg border border-transparent !text-slate-700 hover:!bg-amber-50 hover:!text-amber-600 data-current:!border-amber-200 data-current:!bg-amber-100 data-current:!text-amber-700 data-current:!font-semibold dark:!text-slate-300 dark:hover:!bg-slate-800 dark:hover:!text-amber-400 dark:data-current:!border-amber-500/50 dark:data-current:!bg-slate-800 dark:data-current:!text-amber-400"
                        >
                            Temporadas
                        </flux:navlist.item>

                    @endcan

                    {{-- Reportes --}}
                    @can('reportes.ver')

                        <flux:navlist.item
                            icon="chart-bar"
                            :href="route('reportes')"
                            :current="request()->routeIs('reportes')"
                            wire:navigate
                            class="rounded-lg border border-transparent !text-slate-700 hover:!bg-amber-50 hover:!text-amber-600 data-current:!border-amber-200 data-current:!bg-amber-100 data-current:!text-amber-700 data-current:!font-semibold dark:!text-slate-300 dark:hover:!bg-slate-800 dark:hover:!text-amber-400 dark:data-current:!border-amber-500/50 dark:data-current:!bg-slate-800 dark:data-current:!text-amber-400"
                        >
                            Reportes
                        </flux:navlist.item>

                    @endcan

                    {{-- Roles --}}
                    @can('roles_permisos.ver')

                        <flux:navlist.item
                            icon="shield-check"
                            :href="route('roles')"
                            :current="request()->routeIs('roles')"
                            wire:navigate
                            class="rounded-lg border border-transparent !text-slate-700 hover:!bg-amber-50 hover:!text-amber-600 data-current:!border-amber-200 data-current:!bg-amber-100 data-current:!text-amber-700 data-current:!font-semibold dark:!text-slate-300 dark:hover:!bg-slate-800 dark:hover:!text-amber-400 dark:data-current:!border-amber-500/50 dark:data-current:!bg-slate-800 dark:data-current:!text-amber-400"
                        >
                            Roles
                        </flux:navlist.item>

                    @endcan

                    {{-- Configuración --}}
                    @can('configuracion.ver')

                        <flux:navlist.item
                            icon="cog-6-tooth"
                            :href="route('configuracion')"
                            :current="request()->routeIs('configuracion')"
                            wire:navigate
                            class="rounded-lg border border-transparent !text-slate-700 hover:!bg-amber-50 hover:!text-amber-600 data-current:!border-amber-200 data-current:!bg-amber-100 data-current:!text-amber-700 data-current:!font-semibold dark:!text-slate-300 dark:hover:!bg-slate-800 dark:hover:!text-amber-400 dark:data-current:!border-amber-500/50 dark:data-current:!bg-slate-800 dark:data-current:!text-amber-400"
                        >
                            Configuración
                        </flux:navlist.item>

                    @endcan

                </flux:navlist.group>

            </flux:navlist>

            {{-- ======================================================
                 ESPACIO FLEXIBLE
                 ====================================================== --}}

            <flux:spacer />

            {{-- ======================================================
                 CAMBIO DE TEMA (claro / oscuro)
                 ====================================================== --}}

            <div class="mb-2 flex items-center justify-between gap-3 rounded-xl border border-slate-200 bg-slate-50/80 px-3 py-2.5 transition-colors duration-300 dark:border-slate-700/60 dark:bg-slate-800/60">
                <span class="inline-flex items-center gap-2 text-xs font-semibold text-slate-600 dark:text-slate-300">
                    <flux:icon.moon class="size-4 text-slate-500 dark:hidden" />
                    <flux:icon.sun class="hidden size-4 text-amber-400 dark:block" />
                    Tema
                </span>
                <x-tema-toggle />
            </div>

            {{-- ======================================================
                 PERFIL DEL USUARIO - ESCRITORIO
                 ====================================================== --}}

            <flux:dropdown
                position="bottom"
                align="start"
            >

                <flux:profile
                    :name="auth()->user()->name"
                    :initials="auth()->user()->initials()"
                    icon-trailing="chevrons-up-down"
                    class="hover:!bg-slate-100 [&>span]:!text-slate-700 [&>span]:group-hover:!text-slate-900 dark:hover:!bg-slate-800 [&>span]:dark:!text-slate-300"
                />

                <flux:menu class="w-[240px]">

                    {{-- Información del usuario --}}
                    <flux:menu.radio.group>

                        <div class="p-2 text-sm">

                            <div class="flex items-center gap-3">

                                {{-- Iniciales --}}
                                <span
                                    class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-amber-600/15 text-sm font-semibold text-amber-600"
                                >
                                    {{ auth()->user()->initials() }}
                                </span>

                                {{-- Datos --}}
                                <div class="min-w-0 flex-1">

                                    <span class="block truncate font-semibold text-slate-800 dark:text-white">
                                        {{ auth()->user()->name }}
                                    </span>

                                    <span class="block truncate text-xs text-slate-500 dark:text-zinc-400">
                                        {{ auth()->user()->email }}
                                    </span>

                                    <span class="mt-1 block truncate text-[10px] font-semibold uppercase tracking-wider text-amber-600 dark:text-amber-400">
                                        {{ auth()->user()->getRoleNames()->implode(', ') }}
                                    </span>

                                </div>

                            </div>

                        </div>

                    </flux:menu.radio.group>

                    <flux:menu.separator />

                    {{-- Cerrar sesión --}}
                    <form
                        method="POST"
                        action="{{ route('logout') }}"
                        class="w-full"
                    >

                        @csrf

                        <flux:menu.item
                            as="button"
                            type="submit"
                            icon="arrow-right-start-on-rectangle"
                            class="w-full"
                        >
                            Cerrar sesión
                        </flux:menu.item>

                    </form>

                </flux:menu>

            </flux:dropdown>

        </flux:sidebar>

        {{-- ==========================================================
             MENÚ DEL USUARIO - MÓVIL
             ========================================================== --}}

        <flux:header class="lg:hidden border-b border-slate-200/60 bg-white/80 backdrop-blur-md dark:border-slate-700/60 dark:bg-slate-900/80">

            <flux:sidebar.toggle
                class="lg:hidden !text-slate-500 hover:!text-slate-900 dark:!text-slate-400 dark:hover:!text-slate-100"
                icon="bars-2"
                inset="left"
            />

            <flux:spacer />

            <x-tema-toggle />

            <flux:dropdown
                position="top"
                align="end"
            >

                <flux:profile
                    :initials="auth()->user()->initials()"
                    icon-trailing="chevron-down"
                    class="hover:!bg-slate-100 dark:hover:!bg-slate-800"
                />

                <flux:menu>

                    <flux:menu.radio.group>

                        <div class="p-2 text-sm">

                            <div class="flex items-center gap-2">

                                <span
                                    class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-amber-600/15 text-sm font-semibold text-amber-600 dark:text-amber-400"
                                >
                                    {{ auth()->user()->initials() }}
                                </span>

                                <div class="min-w-0 flex-1">

                                    <span class="block truncate font-semibold text-slate-800 dark:text-white">
                                        {{ auth()->user()->name }}
                                    </span>

                                    <span class="block truncate text-xs text-slate-500 dark:text-zinc-400">
                                        {{ auth()->user()->email }}
                                    </span>

                                    <span class="mt-1 block truncate text-[10px] font-semibold uppercase tracking-wider text-amber-600 dark:text-amber-400">
                                        {{ auth()->user()->getRoleNames()->implode(', ') }}
                                    </span>

                                </div>

                            </div>

                        </div>

                    </flux:menu.radio.group>

                    <flux:menu.separator />

                    {{-- Cerrar sesión --}}
                    <form
                        method="POST"
                        action="{{ route('logout') }}"
                        class="w-full"
                    >

                        @csrf

                        <flux:menu.item
                            as="button"
                            type="submit"
                            icon="arrow-right-start-on-rectangle"
                            class="w-full"
                        >
                            Cerrar sesión
                        </flux:menu.item>

                    </form>

                </flux:menu>

            </flux:dropdown>

        </flux:header>

        {{-- ==========================================================
             CONTENIDO DE LA PÁGINA
             ========================================================== --}}

        {{ $slot }}

        @stack('scripts')

        @fluxScripts

    </body>

</html>