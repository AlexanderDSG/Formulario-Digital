/**
 * COORDINADOR DEL PANEL DE ADMINISTRADOR
 * Este archivo coordina las diferentes vistas sin duplicar funcionalidad
 */

// Variables globales compartidas
window.PanelState = {
    originalDashboardContent: null,
    currentView: 'dashboard',
    patientsDataTable: null
};

// ===== FUNCIONES DE COORDINACIÓN =====

/**
 * Limpiar vista actual antes de cambiar
 */function cleanupCurrentView() {

    // Limpiar estadísticas según la vista actual
    switch(window.PanelState.currentView) {
        case 'dashboard':
            if (typeof limpiarEstadisticasUsuarios === 'function') {
                limpiarEstadisticasUsuarios();
            }
            if (typeof limpiarEstadisticasAtenciones === 'function') {
                limpiarEstadisticasAtenciones();
            }
            break;
        case 'modificaciones':
            if (typeof limpiarEstadisticasModificaciones === 'function') {
                limpiarEstadisticasModificaciones();
            }
            // Destruir tabla de modificaciones si existe
            if (typeof destroyModificacionesTable === 'function') {
                destroyModificacionesTable();
            }
            break;
        case 'reportes':
            if (typeof limpiarEstadisticasReportes === 'function') {
                limpiarEstadisticasReportes();
            }
            // Destruir tabla de reportes si existe
            if (typeof destroyReportesTable === 'function') {
                destroyReportesTable();
            }
            break;
        case 'patients':
            // Limpiar estadísticas de pacientes si existen
            break;
    }
    
    // Destruir tabla de pacientes si existe
    if (window.PanelState.patientsDataTable) {
        try {
            window.PanelState.patientsDataTable.destroy();
            window.PanelState.patientsDataTable = null;
        } catch (error) {
            window.PanelState.patientsDataTable = null;
        }
    }
    
    // Destruir tabla de usuarios si existe
    if (window.patientsDataTable) {
        try {
            window.patientsDataTable.destroy();
            window.patientsDataTable = null;
        } catch (error) {
            window.patientsDataTable = null;
        }
    }
}

/**
 * Preparar contenedores para nueva vista
 */
function prepareContainers() {
    const dashboardContent = document.getElementById('dashboard-content');
    const userTableContent = document.getElementById('user-table-content');

    // Guardar contenido original del dashboard si no se ha guardado Y estamos en el dashboard inicial
    if (dashboardContent && !window.PanelState.originalDashboardContent &&
        window.PanelState.currentView === 'dashboard') {
        window.PanelState.originalDashboardContent = dashboardContent.innerHTML;
    }

    return { dashboardContent, userTableContent };
}

/**
 * Función principal para cambiar de vista
 */
function switchToView(viewType, ...args) {

    // Limpiar vista actual
    cleanupCurrentView();

    // Preparar contenedores
    const containers = prepareContainers();

    // Cambiar según el tipo de vista
    switch(viewType) {
        case 'dashboard':
            showDashboard();
            break;
        case 'users':
            if (typeof window.showUsers === 'function') {
                window.showUsers(args[0]); // tipo de usuario
            } else {
                console.error('showUsers no está disponible');
            }
            break;
        case 'patients':
            if (typeof showPatients === 'function') {
                showPatients();
            }
            break;
        case 'modificaciones':
            if (typeof showModificaciones === 'function') {
                showModificaciones();
            }
            break;
        default:
            console.error(`Vista desconocida: ${viewType}`);
    }
    
    // Actualizar estado después del cambio
    window.PanelState.currentView = viewType;
}
// ===== FUNCIONES DE VISTA =====

/**
 * Mostrar dashboard principal con estadísticas
 * Llama a la función interna y carga las estadísticas
 */
function showDashboard() {
    const dashboardContent = document.getElementById('dashboard-content');
    const userTableContent = document.getElementById('user-table-content');

    // Mostrar dashboard content
    if (dashboardContent) {
        dashboardContent.classList.remove('hidden');

        // Restaurar contenido original si existe
        if (window.PanelState && window.PanelState.originalDashboardContent) {
            dashboardContent.innerHTML = window.PanelState.originalDashboardContent;
        }
    }

    // Ocultar tabla de usuarios
    if (userTableContent) {
        userTableContent.classList.add('hidden');
    }

    // Actualizar el estado
    if (window.PanelState) {
        window.PanelState.currentView = 'dashboard';
    }

    // Cargar estadísticas después de restaurar el contenido
    setTimeout(() => {
        if (typeof cargarEstadisticas === 'function') {
            cargarEstadisticas();
        }
        if (typeof inicializarDashboardEstadisticas === 'function') {
            inicializarDashboardEstadisticas();
        }
    }, 100);
}

// Exportar showDashboard globalmente
window.showDashboard = showDashboard;

// ===== FUNCIONES PÚBLICAS DEL SIDEBAR =====

/**
 * Mostrar dashboard (llamada desde sidebar)
 */
function goToDashboard() {
    switchToView('dashboard');
}

/**
 * Mostrar usuarios por tipo (llamada desde sidebar)
 */
function goToUsers(tipo) {
    switchToView('users', tipo);
}

/**
 * Mostrar pacientes (llamada desde sidebar)
 */
function goToPatients() {
    switchToView('patients');
}

/**
 * Mostrar modificaciones dinámicamente
 */
function goToModificaciones() {
    // Limpiar vista actual
    cleanupCurrentView();

    // Preparar contenedores
    const containers = prepareContainers();

    if (containers.dashboardContent) {
        // Mostrar indicador de carga
        containers.dashboardContent.innerHTML = `
            <div class="flex items-center justify-center py-20">
                <div class="text-center">
                    <div class="animate-spin rounded-full h-16 w-16 border-b-2 border-orange-600 mx-auto mb-4"></div>
                    <p class="text-gray-600">Cargando control de modificaciones...</p>
                </div>
            </div>
        `;

        containers.dashboardContent.classList.remove('hidden');

        if (containers.userTableContent) {
            containers.userTableContent.classList.add('hidden');
        }

        // Cargar contenido de modificaciones
        cargarContenidoModificaciones();
    }

    // Actualizar estado
    window.PanelState.currentView = 'modificaciones';
}

/**
 * Cargar contenido de modificaciones via AJAX
 */
function cargarContenidoModificaciones() {
    const baseUrl = BASE_URL || window.location.origin + '/';
    const url = baseUrl + 'administrador/modificaciones/obtenerVistaModificaciones';

    fetch(url, {
        method: 'GET',
        headers: {
            'Content-Type': 'application/json',
        }
    })
    .then(response => {
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        return response.json();
    })
    .then(data => {
        if (data.success) {
            // Cargar el HTML de modificaciones
            const dashboardContent = document.getElementById('dashboard-content');
            if (dashboardContent) {
                dashboardContent.innerHTML = data.html;

                // Inicializar las modificaciones después de cargar el HTML
                setTimeout(() => {
                    inicializarModificacionesAdmin();
                }, 100);
            }
        } else {
            mostrarErrorCargaModificaciones(data.error || 'Error al cargar modificaciones');
        }
    })
    .catch(error => {
        console.error('Error cargando modificaciones:', error);
        mostrarErrorCargaModificaciones('Error de conexión al cargar modificaciones');
    });
}

/**
 * Inicializar funcionalidad de modificaciones
 */
function inicializarModificacionesAdmin() {

    // Cargar el script de modificaciones si no está ya cargado
    if (!window.modificacionesAdminCargado) {
        cargarScriptModificaciones();
    } else {
        // Si ya está cargado, inicializar directamente
        if (typeof inicializarTablaModificaciones === 'function') {
            inicializarTablaModificaciones();
        }
    }
}

/**
 * Cargar script de modificaciones dinámicamente
 */
function cargarScriptModificaciones() {
    const script = document.createElement('script');
    script.src = BASE_URL + 'public/js/administrador/dashboard-modificaciones.js';
    script.onload = function() {
        window.modificacionesAdminCargado = true;
        if (typeof inicializarTablaModificaciones === 'function') {
            inicializarTablaModificaciones();
        }
    };
    script.onerror = function() {
        console.error('Error al cargar el script de modificaciones');
        mostrarErrorCargaModificaciones('Error al cargar funcionalidad de modificaciones');
    };
    document.head.appendChild(script);
}

/**
 * Mostrar error al cargar modificaciones
 */
function mostrarErrorCargaModificaciones(mensaje) {
    const dashboardContent = document.getElementById('dashboard-content');
    if (dashboardContent) {
        dashboardContent.innerHTML = `
            <div class="bg-white rounded-lg shadow-md p-8 text-center">
                <div class="text-red-600 mb-4">
                    <i class="fas fa-exclamation-triangle text-4xl"></i>
                </div>
                <h3 class="text-xl font-semibold text-gray-800 mb-2">Error al cargar modificaciones</h3>
                <p class="text-gray-600 mb-6">${mensaje}</p>
                <button onclick="goToDashboard()"
                        class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-lg">
                    <i class="fas fa-arrow-left mr-2"></i>
                    Volver al Panel
                </button>
            </div>
        `;
    }
}

/**
 * Mostrar reportes administrativos dinámicamente
 */
function abrirReportesAdmin() {
    // Limpiar vista actual
    cleanupCurrentView();

    // Preparar contenedores
    const containers = prepareContainers();

    if (containers.dashboardContent) {
        // Mostrar indicador de carga
        containers.dashboardContent.innerHTML = `
            <div class="flex items-center justify-center py-20">
                <div class="text-center">
                    <div class="animate-spin rounded-full h-16 w-16 border-b-2 border-green-600 mx-auto mb-4"></div>
                    <p class="text-gray-600">Cargando reportes administrativos...</p>
                </div>
            </div>
        `;

        containers.dashboardContent.classList.remove('hidden');

        if (containers.userTableContent) {
            containers.userTableContent.classList.add('hidden');
        }

        // Cargar contenido de reportes
        cargarContenidoReportes();
    }

    // Actualizar estado
    window.PanelState.currentView = 'reportes';
}

/**
 * Cargar contenido de reportes via AJAX
 */
function cargarContenidoReportes() {
    const baseUrl = BASE_URL || window.location.origin + '/';
    const url = baseUrl + 'administrador/reportes/obtenerVistaReportes';

    fetch(url, {
        method: 'GET',
        headers: {
            'Content-Type': 'application/json',
        }
    })
    .then(response => {
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        return response.json();
    })
    .then(data => {
        if (data.success) {
            // Cargar el HTML de reportes
            const dashboardContent = document.getElementById('dashboard-content');
            if (dashboardContent) {
                dashboardContent.innerHTML = data.html;

                // Inicializar los reportes después de cargar el HTML
                setTimeout(() => {
                    inicializarReportesAdmin();
                }, 100);
            }
        } else {
            mostrarErrorCargaReportes(data.error || 'Error al cargar reportes');
        }
    })
    .catch(error => {
        console.error('Error cargando reportes:', error);
        mostrarErrorCargaReportes('Error de conexión al cargar reportes');
    });
}

/**
 * Inicializar funcionalidad de reportes
 */
function inicializarReportesAdmin() {

    // Cargar el script de reportes si no está ya cargado
    if (!window.reportesAdminCargado) {
        cargarScriptReportes();
    } else {
        // Si ya está cargado, inicializar directamente
        if (typeof inicializarTablaReportesAdmin === 'function') {
            inicializarTablaReportesAdmin();
        }
    }
}

/**
 * Cargar script de reportes dinámicamente
 */
function cargarScriptReportes() {
    const script = document.createElement('script');
    script.src = BASE_URL + 'public/js/administrador/dashboard-reportes.js';
    script.onload = function() {
        window.reportesAdminCargado = true;
        if (typeof inicializarTablaReportesAdmin === 'function') {
            inicializarTablaReportesAdmin();
        }
    };
    script.onerror = function() {
        console.error('Error al cargar el script de reportes');
        mostrarErrorCargaReportes('Error al cargar funcionalidad de reportes');
    };
    document.head.appendChild(script);
}

/**
 * Mostrar error al cargar reportes
 */
function mostrarErrorCargaReportes(mensaje) {
    const dashboardContent = document.getElementById('dashboard-content');
    if (dashboardContent) {
        dashboardContent.innerHTML = `
            <div class="bg-white rounded-lg shadow-md p-8 text-center">
                <div class="text-red-600 mb-4">
                    <i class="fas fa-exclamation-triangle text-4xl"></i>
                </div>
                <h3 class="text-xl font-semibold text-gray-800 mb-2">Error al cargar reportes</h3>
                <p class="text-gray-600 mb-6">${mensaje}</p>
                <button onclick="goToDashboard()"
                        class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-lg">
                    <i class="fas fa-arrow-left mr-2"></i>
                    Volver al Panel
                </button>
            </div>
        `;
    }
}

/**
 * Mostrar notificación temporal
 */
function mostrarNotificacionAdmin(mensaje, tipo = 'info') {
    const alertClass = tipo === 'success' ? 'bg-green-500' : tipo === 'error' ? 'bg-red-500' : 'bg-blue-500';
    const iconClass = tipo === 'success' ? 'fa-check-circle' : tipo === 'error' ? 'fa-exclamation-circle' : 'fa-info-circle';

    const notification = document.createElement('div');
    notification.className = `fixed top-4 right-4 ${alertClass} text-white px-6 py-4 rounded-lg shadow-lg z-50 max-w-sm`;
    notification.innerHTML = `
        <div class="flex items-center">
            <i class="fas ${iconClass} mr-3"></i>
            <span>${mensaje}</span>
        </div>
    `;

    document.body.appendChild(notification);

    // Remover después de 3 segundos
    setTimeout(() => {
        notification.remove();
    }, 3000);
}

/**
 * Toggle del menú de usuarios en sidebar
 */
function toggleUsersMenu() {
    const dropdown = document.getElementById('users-dropdown');
    const arrow = document.getElementById('users-arrow');

    if (dropdown && arrow) {
        dropdown.classList.toggle('hidden');
        arrow.classList.toggle('rotate-90');
    }
}

/**
 * Toggle del dropdown de usuario
 */
function toggleUserDropdown() {
    const dropdown = document.getElementById('user-dropdown');
    if (dropdown) {
        dropdown.classList.toggle('hidden');
    }
}

/**
 * Toggle del sidebar (mostrar/ocultar menú lateral)
 */
function toggleSidebar() {
    const sidebar = document.getElementById('admin-sidebar');
    if (sidebar) {
        // Toggle clases para colapsar/expandir
        sidebar.classList.toggle('collapsed');

        // Guardar estado en localStorage
        const isCollapsed = sidebar.classList.contains('collapsed');
        localStorage.setItem('adminSidebarCollapsed', isCollapsed);
    }
}

// ===== INICIALIZACIÓN =====
document.addEventListener('DOMContentLoaded', function () {

    // Asegurar que el estado inicial esté configurado
    if (!window.PanelState.currentView) {
        window.PanelState.currentView = 'dashboard';
    }

    // Restaurar estado del sidebar desde localStorage
    const sidebar = document.getElementById('admin-sidebar');
    const sidebarCollapsed = localStorage.getItem('adminSidebarCollapsed') === 'true';
    if (sidebar && sidebarCollapsed) {
        sidebar.classList.add('collapsed');
    }

    // Guardar contenido original del dashboard al cargar la página
    setTimeout(() => {
        const dashboardContent = document.getElementById('dashboard-content');
        if (dashboardContent && !window.PanelState.originalDashboardContent) {
            window.PanelState.originalDashboardContent = dashboardContent.innerHTML;
        }
    }, 500); // Esperar a que se cargue completamente

    // Event listener para cerrar dropdowns al hacer clic fuera
    document.addEventListener('click', function (event) {
        const userDropdown = document.getElementById('user-dropdown');
        const userButton = event.target.closest('[onclick="toggleUserDropdown()"]');

        if (!userButton && userDropdown && !userDropdown.contains(event.target)) {
            userDropdown.classList.add('hidden');
        }
    });
});

// Exportar funciones globalmente
window.PanelCoordinator = {
    switchToView,
    goToDashboard,
    goToUsers,
    goToPatients,
    goToModificaciones,
    toggleUsersMenu,
    toggleUserDropdown,
    toggleSidebar
};