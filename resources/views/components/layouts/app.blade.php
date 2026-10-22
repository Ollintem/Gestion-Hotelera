@push('scripts')
    <script>
        (() => {
            const interceptarSesionExpirada = () => {
                Livewire.hook('request', ({ fail }) => {
                    fail(({ status, preventDefault }) => {
                        if (status !== 419) {
                            return;
                        }

                        preventDefault();

                        const recargar = () => window.location.reload();

                        if (!window.Swal) {
                            recargar();

                            return;
                        }

                        window.Swal.fire({
                            icon: 'warning',
                            title: 'Sesión expirada',
                            text: 'Tu sesión ha expirado. Recargaremos la página para continuar.',
                            timer: 2200,
                            timerProgressBar: true,
                            showConfirmButton: false,
                            allowOutsideClick: false,
                            allowEscapeKey: false,
                        }).then(recargar);
                    });
                });
            };

            if (window.Livewire) {
                interceptarSesionExpirada();

                return;
            }

            document.addEventListener('livewire:init', interceptarSesionExpirada, { once: true });
        })();
    </script>
@endpush

<x-layouts.app.sidebar>
    <flux:main class="!bg-slate-100 transition-colors duration-300 dark:!bg-slate-900">
        {{ $slot }}
    </flux:main>
</x-layouts.app.sidebar>
