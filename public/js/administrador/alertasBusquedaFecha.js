/**
 * SISTEMA DE ALERTAS PARA BÚSQUEDA POR FECHA ESPECÍFICA
 * Maneja los mensajes de carga y estado para formularios 008 y 005
 */

// === CONFIGURACIÓN ===
const ALERTAS_CONFIG = {
    DEBUG: true,
    TIMEOUT_LOADING: 30000, // 30 segundos timeout
    DURACION_MENSAJE_EXITO: 3000, // 3 segundos para mensajes de éxito
    DURACION_MENSAJE_ERROR: 5000, // 5 segundos para mensajes de error
    VERSION: 'ALERTAS_FECHA_v1.0'
};

// === ESTADO GLOBAL ===
window.alertasFechaState = {
    formulario008: {
        cargando: false,
        cargado: false,
        error: false
    },
    formulario005: {
        cargando: false,
        cargado: false,
        error: false
    },
    alertaActual: null,
    timeoutLoading: null
};

// === FUNCIÓN DE LOGGING ===
function logAlertas(nivel, mensaje, datos = null) {
    if (ALERTAS_CONFIG.DEBUG && console) {
        const timestamp = new Date().toLocaleTimeString();
        const prefijo = `[ALERTAS-FECHA ${timestamp}]`;

        switch (nivel) {
            case 'error':
                console.error(`${prefijo} ❌`, mensaje, datos || '');
                break;
            case 'warn':
                console.warn(`${prefijo} ⚠️`, mensaje, datos || '');
                break;
            default:
        }
    }
}

// === FUNCIÓN PARA CREAR ALERTA VISUAL ===
function crearAlertaFecha(tipo, mensaje, duracion = null) {
    // Remover alerta anterior si existe
    removerAlertaFecha();

    const alertaContainer = document.createElement('div');
    alertaContainer.id = 'alerta-busqueda-fecha';
    alertaContainer.className = 'fixed top-4 right-4 z-50 max-w-sm';

    let colorClasses = '';
    let icono = '';

    switch (tipo) {
        case 'loading':
            colorClasses = 'bg-blue-50 border-blue-200 text-blue-800';
            icono = '<div class="animate-spin rounded-full h-5 w-5 border-b-2 border-blue-600"></div>';
            break;
        case 'success':
            colorClasses = 'bg-green-50 border-green-200 text-green-800';
            icono = '<svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>';
            break;
        case 'error':
            colorClasses = 'bg-red-50 border-red-200 text-red-800';
            icono = '<svg class="w-5 h-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>';
            break;
        case 'warning':
            colorClasses = 'bg-yellow-50 border-yellow-200 text-yellow-800';
            icono = '<svg class="w-5 h-5 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"></path></svg>';
            break;
        default:
            colorClasses = 'bg-gray-50 border-gray-200 text-gray-800';
            icono = '<svg class="w-5 h-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>';
    }

    alertaContainer.innerHTML = `
        <div class="border rounded-lg p-4 shadow-lg backdrop-blur-sm ${colorClasses} transform transition-all duration-300 ease-in-out">
            <div class="flex items-start space-x-3">
                <div class="flex-shrink-0">
                    ${icono}
                </div>
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-medium">${mensaje}</p>
                </div>
                ${tipo !== 'loading' ? `
                <div class="flex-shrink-0">
                    <button onclick="removerAlertaFecha()" class="text-gray-400 hover:text-gray-600 transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>
                ` : ''}
            </div>
        </div>
    `;

    document.body.appendChild(alertaContainer);
    window.alertasFechaState.alertaActual = alertaContainer;

    // Animación de entrada
    setTimeout(() => {
        alertaContainer.style.opacity = '1';
        alertaContainer.style.transform = 'translateX(0)';
    }, 10);

    // Auto-remover después de la duración especificada
    if (duracion && tipo !== 'loading') {
        setTimeout(() => {
            removerAlertaFecha();
        }, duracion);
    }

    logAlertas('info', `Alerta creada: ${tipo} - ${mensaje}`);
}

// === FUNCIÓN PARA REMOVER ALERTA ===
function removerAlertaFecha() {
    const alertaActual = window.alertasFechaState.alertaActual;
    if (alertaActual && alertaActual.parentNode) {
        alertaActual.style.opacity = '0';
        alertaActual.style.transform = 'translateX(100%)';
        setTimeout(() => {
            if (alertaActual.parentNode) {
                alertaActual.parentNode.removeChild(alertaActual);
            }
        }, 300);
        window.alertasFechaState.alertaActual = null;
    }

    // Limpiar timeout si existe
    if (window.alertasFechaState.timeoutLoading) {
        clearTimeout(window.alertasFechaState.timeoutLoading);
        window.alertasFechaState.timeoutLoading = null;
    }
}

// === FUNCIÓN PARA INICIAR BÚSQUEDA ===
function iniciarBusquedaFecha() {
    logAlertas('info', '🔍 Iniciando búsqueda por fecha');

    // Resetear estado
    window.alertasFechaState.formulario008.cargando = true;
    window.alertasFechaState.formulario008.cargado = false;
    window.alertasFechaState.formulario008.error = false;

    window.alertasFechaState.formulario005.cargando = true;
    window.alertasFechaState.formulario005.cargado = false;
    window.alertasFechaState.formulario005.error = false;

    // Mostrar alerta de carga inicial
    mostrarAlertaCargandoFormularios();

    // Timeout de seguridad
    window.alertasFechaState.timeoutLoading = setTimeout(() => {
        logAlertas('warn', '⏰ Timeout en búsqueda de formularios');
        crearAlertaFecha('warning', 'Búsqueda tomando más tiempo del esperado...', ALERTAS_CONFIG.DURACION_MENSAJE_ERROR);
    }, ALERTAS_CONFIG.TIMEOUT_LOADING);
}

// === FUNCIÓN PARA MOSTRAR CARGA DE FORMULARIOS ===
function mostrarAlertaCargandoFormularios() {
    const form008Estado = window.alertasFechaState.formulario008.cargando ? 'Cargando' : (window.alertasFechaState.formulario008.cargado ? 'Cargado' : 'Pendiente');
    const form005Estado = window.alertasFechaState.formulario005.cargando ? 'Cargando' : (window.alertasFechaState.formulario005.cargado ? 'Cargado' : 'Pendiente');

    let mensaje = '';
    if (window.alertasFechaState.formulario008.cargando && window.alertasFechaState.formulario005.cargando) {
        mensaje = 'Cargando datos Formulario 008 y Formulario 005...';
    } else if (window.alertasFechaState.formulario008.cargando) {
        mensaje = 'Cargando datos Formulario 008...';
    } else if (window.alertasFechaState.formulario005.cargando) {
        mensaje = 'Cargando datos Formulario 005...';
    }

    if (mensaje) {
        crearAlertaFecha('loading', mensaje);
    }
}

// === FUNCIÓN CUANDO SE COMPLETA CARGA DE FORMULARIO 008 ===
function completarCargaFormulario008(exito = true, tienedatos = true) {
    window.alertasFechaState.formulario008.cargando = false;
    window.alertasFechaState.formulario008.cargado = exito;
    window.alertasFechaState.formulario008.error = !exito;

    logAlertas('info', `📄 Formulario 008 completado: exito=${exito}, datos=${tienedatos}`);

    if (exito && tienedatos) {
        // Si el 005 ya está cargado también, mostrar mensaje final
        if (window.alertasFechaState.formulario005.cargado) {
            mostrarMensajeFinalExito();
        } else if (window.alertasFechaState.formulario005.cargando) {
            // Solo mostrar que el 008 se cargó y continuar con 005
            crearAlertaFecha('success', 'Datos cargados Formulario 008 ✓', 2000);
            setTimeout(() => {
                if (window.alertasFechaState.formulario005.cargando) {
                    mostrarAlertaCargandoFormularios();
                }
            }, 2100);
        } else {
            crearAlertaFecha('success', 'Datos cargados Formulario 008 ✓', ALERTAS_CONFIG.DURACION_MENSAJE_EXITO);
        }
    } else if (exito && !tienedatos) {
        // Formulario se procesó pero no hay datos
        verificarEstadoFinalSinDatos();
    } else {
        // Error en carga
        crearAlertaFecha('error', 'Error al cargar Formulario 008', ALERTAS_CONFIG.DURACION_MENSAJE_ERROR);
    }
}

// === FUNCIÓN CUANDO SE COMPLETA CARGA DE FORMULARIO 005 ===
function completarCargaFormulario005(exito = true, tienedatos = true) {
    window.alertasFechaState.formulario005.cargando = false;
    window.alertasFechaState.formulario005.cargado = exito;
    window.alertasFechaState.formulario005.error = !exito;

    logAlertas('info', `📋 Formulario 005 completado: exito=${exito}, datos=${tienedatos}`);

    if (exito && tienedatos) {
        // Si el 008 ya está cargado también, mostrar mensaje final
        if (window.alertasFechaState.formulario008.cargado) {
            mostrarMensajeFinalExito();
        } else if (window.alertasFechaState.formulario008.cargando) {
            // Solo mostrar que el 005 se cargó y continuar con 008
            crearAlertaFecha('success', 'Datos cargados Formulario 005 ✓', 2000);
            setTimeout(() => {
                if (window.alertasFechaState.formulario008.cargando) {
                    mostrarAlertaCargandoFormularios();
                }
            }, 2100);
        } else {
            crearAlertaFecha('success', 'Datos cargados Formulario 005 ✓', ALERTAS_CONFIG.DURACION_MENSAJE_EXITO);
        }
    } else if (exito && !tienedatos) {
        // Formulario se procesó pero no hay datos
        verificarEstadoFinalSinDatos();
    } else {
        // Error en carga
        crearAlertaFecha('error', 'Error al cargar Formulario 005', ALERTAS_CONFIG.DURACION_MENSAJE_ERROR);
    }
}

// === FUNCIÓN PARA VERIFICAR ESTADO FINAL SIN DATOS ===
function verificarEstadoFinalSinDatos() {
    // Verificar si ambos formularios terminaron de procesar
    const ambosTerminados = !window.alertasFechaState.formulario008.cargando && !window.alertasFechaState.formulario005.cargando;

    if (ambosTerminados) {
        const fecha = document.getElementById('filtro-fecha')?.value || 'la fecha seleccionada';
        crearAlertaFecha('warning', `Datos no encontrados para ${fecha}`, ALERTAS_CONFIG.DURACION_MENSAJE_ERROR);
        limpiarTimeoutLoading();
    }
}

// === FUNCIÓN PARA MOSTRAR MENSAJE FINAL DE ÉXITO ===
function mostrarMensajeFinalExito() {
    limpiarTimeoutLoading();
    crearAlertaFecha('success', 'Todos los datos cargados exitosamente ✓', ALERTAS_CONFIG.DURACION_MENSAJE_EXITO);
    logAlertas('success', '🎉 Búsqueda completada exitosamente');
}

// === FUNCIÓN PARA LIMPIAR TIMEOUT ===
function limpiarTimeoutLoading() {
    if (window.alertasFechaState.timeoutLoading) {
        clearTimeout(window.alertasFechaState.timeoutLoading);
        window.alertasFechaState.timeoutLoading = null;
    }
}

// === FUNCIÓN PARA RESETEAR ESTADO ===
function resetearEstadoAlertas() {
    logAlertas('info', '🔄 Reseteando estado de alertas');

    window.alertasFechaState.formulario008.cargando = false;
    window.alertasFechaState.formulario008.cargado = false;
    window.alertasFechaState.formulario008.error = false;

    window.alertasFechaState.formulario005.cargando = false;
    window.alertasFechaState.formulario005.cargado = false;
    window.alertasFechaState.formulario005.error = false;

    limpiarTimeoutLoading();
    removerAlertaFecha();
}

// === FUNCIÓN PARA MANEJAR ERROR GENERAL ===
function manejarErrorBusqueda(mensaje = 'Error en la búsqueda por fecha') {
    logAlertas('error', '💥 Error general en búsqueda:', mensaje);
    limpiarTimeoutLoading();
    crearAlertaFecha('error', mensaje, ALERTAS_CONFIG.DURACION_MENSAJE_ERROR);
    resetearEstadoAlertas();
}

// === EXPORTAR FUNCIONES GLOBALES ===
window.alertasBusquedaFecha = {
    iniciarBusqueda: iniciarBusquedaFecha,
    completarFormulario008: completarCargaFormulario008,
    completarFormulario005: completarCargaFormulario005,
    manejarError: manejarErrorBusqueda,
    resetear: resetearEstadoAlertas,
    remover: removerAlertaFecha
};

// === FUNCIONES GLOBALES INDIVIDUALES ===
window.iniciarBusquedaFecha = iniciarBusquedaFecha;
window.completarCargaFormulario008 = completarCargaFormulario008;
window.completarCargaFormulario005 = completarCargaFormulario005;
window.manejarErrorBusqueda = manejarErrorBusqueda;
window.resetearEstadoAlertas = resetearEstadoAlertas;
window.removerAlertaFecha = removerAlertaFecha;

logAlertas('info', '✅ Sistema de alertas de búsqueda por fecha inicializado');