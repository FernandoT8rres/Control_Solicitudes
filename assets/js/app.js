/**
 * COMECyT Control de Solicitudes
 * JavaScript del lado del cliente
 *
 * Responsabilidad: Manejar unicamente la capa de presentacion (UI):
 *   - Sidebar movil
 *   - Modales
 *   - Confirmaciones antes de acciones criticas
 *   - Notificaciones toast efimeras
 *
 * NO contiene logica de negocio ni llamadas a API.
 * Todos los datos son obtenidos y procesados por PHP (server-side).
 */

'use strict';

/* ==============================================================
   Sidebar movil
   ============================================================== */
(function initSidebar() {
    const toggle = document.getElementById('menuToggle');
    const sidebar = document.getElementById('sidebar');
    const overlay = document.getElementById('sidebarOverlay');

    if (!toggle || !sidebar) return;

    function abrirSidebar() {
        sidebar.classList.add('open');
        overlay.classList.add('active');
        toggle.setAttribute('aria-expanded', 'true');
    }

    function cerrarSidebar() {
        sidebar.classList.remove('open');
        overlay.classList.remove('active');
        toggle.setAttribute('aria-expanded', 'false');
    }

    toggle.addEventListener('click', () => {
        sidebar.classList.contains('open') ? cerrarSidebar() : abrirSidebar();
    });

    overlay.addEventListener('click', cerrarSidebar);

    // Cerrar con Escape
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape' && sidebar.classList.contains('open')) {
            cerrarSidebar();
        }
    });
}());

/* ==============================================================
   Modales
   ============================================================== */
/**
 * Abrir un modal por ID.
 * @param {string} modalId
 */
function abrirModal(modalId) {
    const backdrop = document.getElementById(modalId);
    if (!backdrop) return;
    backdrop.classList.add('open');
    document.body.style.overflow = 'hidden';

    // Foco en el primer campo interactivo
    const primer = backdrop.querySelector('input, select, textarea, button');
    if (primer) setTimeout(() => primer.focus(), 100);
}

/**
 * Cerrar un modal por ID.
 * @param {string} modalId
 */
function cerrarModal(modalId) {
    const backdrop = document.getElementById(modalId);
    if (!backdrop) return;
    backdrop.classList.remove('open');
    document.body.style.overflow = '';
}

// Cerrar al hacer clic fuera del panel del modal
document.addEventListener('click', (e) => {
    if (e.target.classList.contains('modal-backdrop')) {
        e.target.classList.remove('open');
        document.body.style.overflow = '';
    }
});

// Cerrar con Escape
document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape') {
        document.querySelectorAll('.modal-backdrop.open').forEach((m) => {
            m.classList.remove('open');
            document.body.style.overflow = '';
        });
    }
});

/* ==============================================================
   Toast (notificaciones efimeras)
   ============================================================== */
/**
 * Mostrar una notificacion toast.
 *
 * @param {string} mensaje  Texto a mostrar.
 * @param {'success'|'error'|'info'} tipo  Variante visual.
 * @param {number} duracion  Milisegundos antes de ocultarse (default 3500).
 */
function mostrarToast(mensaje, tipo = 'success', duracion = 3500) {
    let contenedor = document.querySelector('.toast-container');
    if (!contenedor) {
        contenedor = document.createElement('div');
        contenedor.className = 'toast-container';
        document.body.appendChild(contenedor);
    }

    const iconos = {
        success: 'fa-circle-check',
        error: 'fa-circle-xmark',
        info: 'fa-circle-info',
    };

    const toast = document.createElement('div');
    toast.className = `toast toast-${tipo}`;
    toast.innerHTML = `
        <i class="fa-solid ${iconos[tipo] || iconos.info}"></i>
        <span>${mensaje}</span>
    `;

    contenedor.appendChild(toast);

    setTimeout(() => {
        toast.style.transition = 'opacity 300ms ease, transform 300ms ease';
        toast.style.opacity = '0';
        toast.style.transform = 'translateX(20px)';
        setTimeout(() => toast.remove(), 300);
    }, duracion);
}

/* ==============================================================
   Confirmacion antes de acciones criticas
   ============================================================== */
/**
 * Agregar dialogo de confirmacion nativo a botones con data-confirm.
 * Uso en HTML: <button data-confirm="¿Desea cancelar esta solicitud?">Cancelar</button>
 */
document.addEventListener('click', (e) => {
    const btn = e.target.closest('[data-confirm]');
    if (!btn) return;
    const msj = btn.getAttribute('data-confirm');
    if (msj && !window.confirm(msj)) {
        e.preventDefault();
        e.stopPropagation();
    }
});

/* ==============================================================
   Auto-cierre de alertas con [data-auto-close]
   ============================================================== */
document.querySelectorAll('[data-auto-close]').forEach((el) => {
    const ms = parseInt(el.getAttribute('data-auto-close'), 10) || 4000;
    setTimeout(() => {
        el.style.transition = 'opacity 300ms ease';
        el.style.opacity = '0';
        setTimeout(() => el.remove(), 300);
    }, ms);
});
