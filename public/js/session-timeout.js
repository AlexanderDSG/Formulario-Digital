/**
 * Sistema de timeout de sesión para Formulario Digital
 * Configurado para 15 minutos con advertencia a 1 minuto
 */

class SessionTimeout {
    constructor() {
        // Configuración de tiempos en milisegundos
        this.TIMEOUT_DURATION = 30 * 60 * 1000; // 30 minutos
        this.WARNING_TIME = 1 * 60 * 1000; // 1 minuto antes

        this.warningTimer = null;
        this.logoutTimer = null;
        this.warningModal = null;
        this.lastActivity = Date.now();

        // Eventos que resetean el timer de inactividad
        this.activityEvents = [
            'mousedown', 'mousemove', 'keypress', 'scroll',
            'touchstart', 'click', 'keydown'
        ];

        // Solo inicializar en las áreas especificadas
        this.allowedAreas = ['admision', 'enfermeria', 'medicos', 'administrador',];

        this.init();
    }

    init() {
        // Verificar si estamos en una de las áreas permitidas
        if (!this.isAllowedArea()) {
            // console.log('SessionTimeout: Área no requiere timeout automático');
            return;
        }

        // Verificar si acabamos de hacer login (detectar nueva sesión)
        const urlParams = new URLSearchParams(window.location.search);
        const esNuevoLogin = urlParams.has('login') ||
                           sessionStorage.getItem('new_login') === 'true' ||
                           !localStorage.getItem('session_timeout_initialized');

        if (esNuevoLogin) {
            // console.log('SessionTimeout: Nueva sesión detectada - limpiando estado anterior');
            sessionStorage.removeItem('session_closed_by_reload');
            sessionStorage.removeItem('new_login');
            localStorage.setItem('session_timeout_initialized', 'true');
            localStorage.setItem('last_activity', Date.now().toString());
        }

        // Lógica de recarga eliminada - las recargas NO deben cerrar sesión

        // console.log('SessionTimeout: Inicializando sistema de timeout de sesión');

        // Crear modal de advertencia
        this.createWarningModal();

        // Configurar listeners de actividad
        this.setupActivityListeners();

        // Iniciar timers
        this.resetTimers();
    }

    isAllowedArea() {
        const currentPath = window.location.pathname;
        return this.allowedAreas.some(area => currentPath.includes(`/${area}/`) || currentPath.includes(`/${area}`));
    }

    getBaseUrl() {
        // Si existe window.base_url, usarlo
        if (window.base_url) {
            return window.base_url.replace(/\/$/, ''); // Remover slash final si existe
        }

        // Si no, extraer de la URL actual
        const pathname = window.location.pathname;

        // Buscar /Formulario-Digital/ en la ruta
        if (pathname.includes('/Formulario-Digital/')) {
            return window.location.origin + '/Formulario-Digital';
        }

        // Si no está en subdirectorio, usar origin
        return window.location.origin;
    }

    createWarningModal() {
        // Crear el HTML del modal
        const modalHTML = `
            <div id="sessionWarningModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden z-50 flex items-center justify-center">
                <div class="bg-white rounded-lg shadow-xl max-w-md w-full mx-4">
                    <div class="bg-yellow-500 text-white px-6 py-4 rounded-t-lg">
                        <div class="flex items-center">
                            <svg class="w-6 h-6 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.728-.833-2.498 0L4.316 15.5c-.77.833.192 2.5 1.732 2.5z"></path>
                            </svg>
                            <h3 class="text-lg font-semibold">⚠️ Advertencia de Sesión</h3>
                        </div>
                    </div>
                    <div class="p-6">
                        <p class="text-gray-700 mb-4">
                            Su sesión expirará en <strong id="countdownTimer">60</strong> segundos debido a inactividad.
                        </p>
                        <p class="text-gray-600 text-sm mb-6">
                            ¿Desea continuar con su sesión?
                        </p>
                        <div class="flex justify-end space-x-3">
                            <button id="extendSessionBtn" class="bg-green-500 hover:bg-green-600 text-white px-6 py-2 rounded font-medium transition-colors">
                                Sí, continuar
                            </button>
                            <button id="logoutNowBtn" class="bg-red-500 hover:bg-red-600 text-white px-6 py-2 rounded font-medium transition-colors">
                                Cerrar sesión
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        `;

        // Agregar el modal al body
        document.body.insertAdjacentHTML('beforeend', modalHTML);

        // Referencias a elementos
        this.warningModal = document.getElementById('sessionWarningModal');
        this.countdownElement = document.getElementById('countdownTimer');

        // Event listeners para botones
        document.getElementById('extendSessionBtn').addEventListener('click', () => {
            this.extendSession();
        });

        document.getElementById('logoutNowBtn').addEventListener('click', () => {
            this.logout();
        });
    }

    setupActivityListeners() {
        this.activityEvents.forEach(eventType => {
            document.addEventListener(eventType, () => {
                this.updateActivity();
            }, true);
        });

        // También escuchar cambios de visibilidad de la página
        document.addEventListener('visibilitychange', () => {
            if (!document.hidden) {
                this.updateActivity();
            }
        });
    }

    updateActivity() {
        this.lastActivity = Date.now();

        // Si el modal está visible, ocultarlo porque hubo actividad
        if (this.warningModal && !this.warningModal.classList.contains('hidden')) {
            this.hideWarningModal();
        }

        this.resetTimers();
    }

    resetTimers() {
        // Limpiar timers existentes
        if (this.warningTimer) clearTimeout(this.warningTimer);
        if (this.logoutTimer) clearTimeout(this.logoutTimer);

        // Configurar timer para mostrar advertencia
        this.warningTimer = setTimeout(() => {
            this.showWarning();
        }, this.TIMEOUT_DURATION - this.WARNING_TIME);

        // Configurar timer para logout automático
        this.logoutTimer = setTimeout(() => {
            this.logout(true); // Silencioso - sin alerta adicional
        }, this.TIMEOUT_DURATION);

        // console.log('SessionTimeout: Timers reseteados - Warning en', (this.TIMEOUT_DURATION - this.WARNING_TIME) / 1000 / 60, 'minutos');
    }

    showWarning() {
        // console.log('SessionTimeout: Mostrando advertencia de expiración');

        this.warningModal.classList.remove('hidden');

        // Iniciar countdown
        let secondsLeft = 60;
        this.countdownElement.textContent = secondsLeft;

        this.countdownInterval = setInterval(() => {
            secondsLeft--;
            this.countdownElement.textContent = secondsLeft;

            if (secondsLeft <= 0) {
                clearInterval(this.countdownInterval);
                this.logout(true); // Silencioso - sin alerta adicional
            }
        }, 1000);
    }

    hideWarningModal() {
        if (this.warningModal) {
            this.warningModal.classList.add('hidden');
        }

        if (this.countdownInterval) {
            clearInterval(this.countdownInterval);
        }
    }

    extendSession() {
        // console.log('SessionTimeout: Usuario eligió extender la sesión');

        this.hideWarningModal();
        this.updateActivity(); // Esto resetea los timers

        // Opcional: hacer ping al servidor para mantener la sesión activa
        this.pingServer();
    }

    async pingServer() {
        try {
            const baseUrl = this.getBaseUrl();
            const response = await fetch(baseUrl + '/ping-session', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });

            if (response.ok) {
                // console.log('SessionTimeout: Ping al servidor exitoso');
            }
        } catch (error) {
            // console.log('SessionTimeout: Error en ping al servidor:', error);
        }
    }

    logout(silencioso = false) {
        // console.log('SessionTimeout: Cerrando sesión por inactividad');

        // Limpiar timers
        if (this.warningTimer) clearTimeout(this.warningTimer);
        if (this.logoutTimer) clearTimeout(this.logoutTimer);
        if (this.countdownInterval) clearInterval(this.countdownInterval);

        // Solo mostrar mensaje si no es silencioso (para recargas)
        if (!silencioso) {
            alert('Su sesión ha expirado debido a inactividad. Será redirigido al login.');
        }

        // Cerrar sesión en el servidor
        this.cerrarSesionServidor();

        // Redirigir al login (usar URL absoluta)
        const baseUrl = this.getBaseUrl();
        window.location.href = baseUrl + '/login?timeout=1';
    }

    async cerrarSesionServidor() {
        try {
            const baseUrl = this.getBaseUrl();
            await fetch(baseUrl + '/logout', {
                method: 'GET',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });
            // console.log('SessionTimeout: Sesión cerrada en el servidor');

            // Limpiar cualquier estado local persistente
            sessionStorage.clear();
            localStorage.removeItem('last_activity');

        } catch (error) {
            // console.log('SessionTimeout: Error cerrando sesión en servidor:', error);
        }
    }

    // Método público para destruir la instancia
    destroy() {
        // console.log('SessionTimeout: Destruyendo instancia');

        if (this.warningTimer) clearTimeout(this.warningTimer);
        if (this.logoutTimer) clearTimeout(this.logoutTimer);
        if (this.countdownInterval) clearInterval(this.countdownInterval);

        this.activityEvents.forEach(eventType => {
            document.removeEventListener(eventType, this.updateActivity, true);
        });

        if (this.warningModal) {
            this.warningModal.remove();
        }

        // Limpiar referencias
        this.warningTimer = null;
        this.logoutTimer = null;
        this.countdownInterval = null;
        this.warningModal = null;
    }
}

// Inicializar automáticamente cuando el DOM esté listo
document.addEventListener('DOMContentLoaded', function() {
    // Destruir instancia anterior si existe
    if (window.sessionTimeout) {
        window.sessionTimeout.destroy();
        window.sessionTimeout = null;
    }

    // Crear nueva instancia
    window.sessionTimeout = new SessionTimeout();
});

// Solo limpiar instancia cuando se cierra la página (NO cerrar sesión por recarga)
window.addEventListener('beforeunload', function(e) {
    if (window.sessionTimeout) {
        // console.log('SessionTimeout: Limpiando instancia al cerrar página');
        window.sessionTimeout.destroy();
    }
});