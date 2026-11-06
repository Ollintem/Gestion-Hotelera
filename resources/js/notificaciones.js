/*
|--------------------------------------------------------------------------
| Notificaciones temporales de NovaStay
|--------------------------------------------------------------------------
|
| Un único mecanismo para todos los módulos:
|
|  - Cualquier elemento marcado con `data-notificacion` se oculta solo a los
|    3000 ms. Se puede ajustar con `data-notificacion-duracion` y blindar con
|    `data-notificacion-persistente` para alertas que exigen intervención.
|  - Cada elemento tiene su propio temporizador, así que un aviso anterior
|    nunca cierra a uno nuevo.
|  - Los eventos `notificacion` / `novastay:notificacion` lanzados por Livewire
|    se dibujan en un contenedor flotante global, también temporal.
|  - Un MutationObserver vuelve a armar el temporizador cuando Livewire cambia
|    el texto del aviso, sin añadir código repetido en cada componente.
|
*/

const DURACION_MS = 3000;
const temporizadores = new WeakMap();
const registrados = new WeakSet();

const ESTILOS = {
    success: {
        clases: 'border-emerald-200 bg-emerald-50 text-emerald-900 dark:border-emerald-500/40 dark:bg-emerald-500/10 dark:text-emerald-100',
        icono: 'text-emerald-600 dark:text-emerald-400',
        trazo: 'M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z',
        papel: 'status',
    },
    error: {
        clases: 'border-rose-200 bg-rose-50 text-rose-900 dark:border-rose-500/40 dark:bg-rose-500/10 dark:text-rose-100',
        icono: 'text-rose-600 dark:text-rose-400',
        trazo: 'M12 9v3.75m9-.75a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 3.75h.008v.008H12v-.008Z',
        papel: 'alert',
    },
    warning: {
        clases: 'border-amber-200 bg-amber-50 text-amber-900 dark:border-amber-500/40 dark:bg-amber-500/10 dark:text-amber-100',
        icono: 'text-amber-600 dark:text-amber-400',
        trazo: 'M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z',
        papel: 'alert',
    },
    info: {
        clases: 'border-sky-200 bg-sky-50 text-sky-900 dark:border-sky-500/40 dark:bg-sky-500/10 dark:text-sky-100',
        icono: 'text-sky-600 dark:text-sky-400',
        trazo: 'M11.25 11.25l.041-.02a.75.75 0 0 1 1.063.852l-.708 2.836a.75.75 0 0 0 1.063.853l.041-.021M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9-3.75h.008v.008H12V8.25Z',
        papel: 'status',
    },
};

ESTILOS.aviso = ESTILOS.warning;

function claseContenedor() {
    let contenedor = document.getElementById('novastay-notificaciones');

    if (!contenedor) {
        contenedor = document.createElement('div');
        contenedor.id = 'novastay-notificaciones';
        contenedor.setAttribute('aria-live', 'polite');
        contenedor.className = 'pointer-events-none fixed inset-x-0 top-20 z-[120] flex flex-col items-center gap-2 px-4 sm:top-6 sm:items-end sm:px-6';
        document.body.appendChild(contenedor);
    }

    return contenedor;
}

function crearToast(mensaje, tipo = 'success') {
    const estilo = ESTILOS[tipo] || ESTILOS.success;

    const toast = document.createElement('div');
    toast.dataset.notificacion = 'true';
    toast.dataset.notificacionDinamica = 'true';
    toast.setAttribute('role', estilo.papel);
    toast.className = `pointer-events-auto flex w-full max-w-md items-start gap-3 rounded-xl border px-4 py-3 text-sm font-medium shadow-lg shadow-slate-900/10 transition duration-200 ${estilo.clases}`;

    const icono = document.createElementNS('http://www.w3.org/2000/svg', 'svg');
    icono.setAttribute('class', `mt-0.5 size-5 shrink-0 ${estilo.icono}`);
    icono.setAttribute('fill', 'none');
    icono.setAttribute('viewBox', '0 0 24 24');
    icono.setAttribute('stroke-width', '1.5');
    icono.setAttribute('stroke', 'currentColor');
    icono.setAttribute('aria-hidden', 'true');

    const trazo = document.createElementNS('http://www.w3.org/2000/svg', 'path');
    trazo.setAttribute('stroke-linecap', 'round');
    trazo.setAttribute('stroke-linejoin', 'round');
    trazo.setAttribute('d', estilo.trazo);
    icono.appendChild(trazo);

    const texto = document.createElement('p');
    texto.className = 'min-w-0 flex-1 leading-snug';
    texto.textContent = mensaje;

    const cerrar = document.createElement('button');
    cerrar.type = 'button';
    cerrar.dataset.notificacionCerrar = 'true';
    cerrar.setAttribute('aria-label', 'Cerrar notificación');
    cerrar.className = '-mr-1 -mt-1 shrink-0 rounded-lg p-1 opacity-60 transition hover:opacity-100 focus:outline-none focus-visible:ring-2 focus-visible:ring-current';
    cerrar.innerHTML = '<svg class="size-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" /></svg>';

    toast.append(icono, texto, cerrar);
    claseContenedor().appendChild(toast);
    registrar(toast);
    programar(toast);

    return toast;
}

function cerrar(elemento) {
    if (!elemento) {
        return;
    }

    const temporizador = temporizadores.get(elemento);

    if (temporizador) {
        window.clearTimeout(temporizador);
        temporizadores.delete(elemento);
    }

    elemento.classList.add('opacity-0', '-translate-y-1');

    window.setTimeout(() => {
        if (elemento.dataset.notificacionDinamica === 'true') {
            elemento.remove();
            return;
        }

        elemento.hidden = true;
        elemento.style.display = 'none';
    }, 200);
}

function programar(elemento) {
    if (!elemento || elemento.dataset.notificacionPersistente === 'true') {
        return;
    }

    const anterior = temporizadores.get(elemento);

    if (anterior) {
        window.clearTimeout(anterior);
    }

    elemento.hidden = false;
    elemento.style.display = '';
    elemento.classList.remove('opacity-0', '-translate-y-1');

    const duracion = Number.parseInt(elemento.dataset.notificacionDuracion || '', 10);
    const milisegundos = Number.isFinite(duracion) && duracion > 0 ? duracion : DURACION_MS;

    const temporizador = window.setTimeout(() => cerrar(elemento), milisegundos);
    temporizadores.set(elemento, temporizador);
}

function registrar(elemento) {
    if (!elemento || registrados.has(elemento)) {
        return;
    }

    registrados.add(elemento);

    const boton = elemento.querySelector('[data-notificacion-cerrar]');

    if (boton) {
        boton.addEventListener('click', () => cerrar(elemento));
    }

    programar(elemento);
}

function elementosAfectados(mutaciones) {
    const pendientes = new Set();

    mutaciones.forEach((mutacion) => {
        if (mutacion.type === 'characterData') {
            const elemento = mutacion.target.parentElement?.closest('[data-notificacion]');

            if (elemento) {
                pendientes.add(elemento);
            }

            return;
        }

        mutacion.addedNodes.forEach((nodo) => {
            if (nodo.nodeType === Node.TEXT_NODE) {
                const elemento = nodo.parentElement?.closest('[data-notificacion]');

                if (elemento) {
                    pendientes.add(elemento);
                }

                return;
            }

            if (nodo.nodeType !== Node.ELEMENT_NODE) {
                return;
            }

            if (nodo.matches('[data-notificacion]')) {
                pendientes.add(nodo);
            }

            nodo.querySelectorAll?.('[data-notificacion]').forEach((elemento) => pendientes.add(elemento));
        });
    });

    return pendientes;
}

function iniciar() {
    document.querySelectorAll('[data-notificacion]').forEach(registrar);

    const observador = new MutationObserver((mutaciones) => {
        elementosAfectados(mutaciones).forEach((elemento) => {
            registrar(elemento);
            programar(elemento);
        });
    });

    observador.observe(document.body, {
        childList: true,
        subtree: true,
        characterData: true,
    });
}

function escucharEventos() {
    const mostrar = (evento) => {
        const detalle = evento.detail || {};

        if (detalle.mensaje) {
            crearToast(String(detalle.mensaje), detalle.tipo || 'success');
        }
    };

    window.addEventListener('notificacion', mostrar);
    window.addEventListener('novastay:notificacion', mostrar);
}

escucharEventos();

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', iniciar, { once: true });
} else {
    iniciar();
}

window.NovaStayNotificaciones = {
    duracion: DURACION_MS,
    mostrar: crearToast,
    cerrar,
    programar,
};
