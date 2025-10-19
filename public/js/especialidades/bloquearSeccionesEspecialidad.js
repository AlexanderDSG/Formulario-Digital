// ========================================
// BLOQUEAR SECCIONES ESPECIALIDAD - VERSIÓN ACTUALIZADA PARA INTERFAZ DINÁMICA
// ========================================

// Variables específicas para ESPECIALIDADES
window.contextoEspecialidad = true;
window.contextoMedico = !(window.contextoEnfermeria || false);
// No sobrescribir contextoEnfermeria si ya fue definido desde el controlador

// Inicializar cuando el DOM esté listo (después de interfazDinamica.js)
document.addEventListener('DOMContentLoaded', function() {
    // Esperar a que interfazDinamica.js termine de crear elementos
    setTimeout(() => {
        inicializarControlSecciones();
    }, 200);
});

/**
 * Inicializar control de secciones
 */
function inicializarControlSecciones() {
    // Detectar si es modificación o continuación de proceso
    const esModificacion = verificarSiEsModificacionEspecialidad();
    const esContinuacion = verificarSiEsContinuarProceso();
    
    if (esModificacion || esContinuacion) {
        // La interfaz dinámica ya maneja estos casos
        sessionStorage.setItem('estadoFormularioEspecialidad', 'completo');
    } else {
        aplicarConfiguracionInicial();
    }
    
    // Configurar event listener del formulario
    configurarFormulario();
    
}

/**
 * Aplicar configuración inicial
 */
function aplicarConfiguracionInicial() {
    // Limpiar estado previo
    sessionStorage.removeItem('estadoFormularioEspecialidad');
    
    // La interfaz dinámica ya oculta/muestra los elementos correctos
    // Solo necesitamos configurar algunos ajustes adicionales
    
    setTimeout(() => {
        // Configurar vista inicial
        const estadoActual = sessionStorage.getItem('estadoFormularioEspecialidad');
        
        // Verificar si es continuación de proceso, modificación o enfermería
        const esContinuacion = window.esContinuacionProceso || false;
        const esModificacion = window.esModificacionEspecialista || false;
        const esEnfermeria = window.contextoEnfermeria || false;

        if (esEnfermeria) {
            // PRIORIDAD: Si es enfermería -> mostrar vista especial de enfermería (solo A-N)
            if (typeof window.mostrarVistaEnfermeria === 'function') {
                window.mostrarVistaEnfermeria();
            }
        } else if (estadoActual === 'completo' || esContinuacion || esModificacion) {
            // Si ya está en estado completo, es continuación o modificación -> mostrar secciones finales
            if (typeof window.mostrarSeccionesFinales === 'function') {
                window.mostrarSeccionesFinales();
            }
        } else {
            // Estado inicial (En Atención) - mostrar botones de proceso
            if (typeof window.mostrarBotonesProceso === 'function') {
                window.mostrarBotonesProceso();
            } else if (typeof window.ocultarSeccionesFinales === 'function') {
                window.ocultarSeccionesFinales();
            }
        }
    }, 100);
}

/**
 * Verificar si es modificación
 */
function verificarSiEsModificacionEspecialidad() {
    // 1. Variable global (igual que médicos)
    if (typeof window.esModificacion !== 'undefined' && window.esModificacion === true) {
        // console.log('✅ ESPECIALISTA - Es modificación por window.esModificacion');
        return true;
    }

    // 2. Variable específica para especialistas
    if (typeof window.esModificacionEspecialista !== 'undefined' && window.esModificacionEspecialista === true) {
        // console.log('✅ ESPECIALISTA - Es modificación por window.esModificacionEspecialista');
        return true;
    }

    // 3. Input hidden
    const inputModificacion = document.querySelector('input[name="es_modificacion"]');
    if (inputModificacion && inputModificacion.value === '1') {
        // console.log('✅ ESPECIALISTA - Es modificación por input hidden');
        return true;
    }

    // 4. Input habilitado_por_admin
    const formularioUsuario = document.querySelector('input[name="habilitado_por_admin"]');
    if (formularioUsuario && formularioUsuario.value === '1') {
        // console.log('✅ ESPECIALISTA - Es modificación por habilitado_por_admin');
        return true;
    }

    // console.log('❌ ESPECIALISTA - No es modificación');
    return false;
}

/**
 * Verificar si es continuación de proceso
 */
function verificarSiEsContinuarProceso() {
    const urlParams = new URLSearchParams(window.location.search);
    return urlParams.get('continuar_proceso') === '1';
}

/**
 * Configurar formulario principal
 */
function configurarFormulario() {
    const formulario = document.getElementById('formEspecialidad');
    if (formulario && !formulario.hasAttribute('data-especialidad-configurado')) {
        formulario.setAttribute('data-especialidad-configurado', 'true');

        formulario.addEventListener('submit', function(e) {
            e.preventDefault(); // Prevenir envío por defecto

            // Llamar a la función de confirmación (retorna una promesa)
            confirmarEnvioFormularioEspecialidad().then((confirmado) => {
                if (confirmado) {
                    // Si confirma, enviar el formulario manualmente
                    formulario.submit();
                }
                // Si no confirma, no hacer nada
            });
        });
        
    }
}

/**
 * Confirmar envío del formulario de especialidad
 */
function confirmarEnvioFormularioEspecialidad() {
    
    const esModificacion = verificarSiEsModificacionEspecialidad();
    const esContinuacion = verificarSiEsContinuarProceso();
    
    // Verificar códigos necesarios
    const ateCodigoInput = document.querySelector('input[name="ate_codigo"]');
    const areCodigoInput = document.querySelector('input[name="are_codigo"]');
    
    if (!ateCodigoInput || !ateCodigoInput.value) {
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: 'No se encontró el código de atención.',
            confirmButtonText: 'Aceptar'
        });
        return false;
    }

    if (!areCodigoInput || !areCodigoInput.value) {
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: 'No se encontró el código del área de atención.',
            confirmButtonText: 'Aceptar'
        });
        return false;
    }
        
    // Verificar estado solo si no es caso especial
    if (!esModificacion && !esContinuacion) {
        const estadoActual = sessionStorage.getItem('estadoFormularioEspecialidad');

        if (estadoActual !== 'completo') {
            // Preguntar si desea terminar la atención
            return Swal.fire({
                icon: 'question',
                title: '¿Desea terminar la atención?',
                text: 'No ha completado todas las secciones del formulario',
                showCancelButton: true,
                confirmButtonText: 'Sí, terminar',
                cancelButtonText: 'Cancelar',
                confirmButtonColor: '#10b981',
                cancelButtonColor: '#6b7280'
            }).then((result) => {
                if (!result.isConfirmed) {
                    return false;
                }
                // Si confirma, continuar con la siguiente confirmación
                return mostrarConfirmacionFinalEspecialidad(esModificacion);
            });
        }
    }

    // Si no necesita la primera confirmación, ir directo a la confirmación final
    return mostrarConfirmacionFinalEspecialidad(esModificacion);
}

// Función auxiliar para mostrar la confirmación final
function mostrarConfirmacionFinalEspecialidad(esModificacion) {
    // Deshabilitar campos problemáticos del modal si existe
    const campoModal = document.getElementById('motivo_observacion');
    if (campoModal) {
        campoModal.removeAttribute('required');
        campoModal.disabled = true;
    }

    const titulo = esModificacion ? '¿Guardar modificaciones?' : '¿Completar atención de especialidad?';
    const mensaje = esModificacion ?
        'Esta acción actualizará la atención del paciente.' :
        'Esta acción finalizará la atención del paciente en esta especialidad.';

    return Swal.fire({
        icon: 'question',
        title: titulo,
        text: mensaje,
        showCancelButton: true,
        confirmButtonText: 'Sí, guardar',
        cancelButtonText: 'Cancelar',
        confirmButtonColor: '#10b981',
        cancelButtonColor: '#6b7280'
    }).then((result) => {
        if (result.isConfirmed) {
            sessionStorage.removeItem('estadoFormularioEspecialidad');

            // Mostrar estado de carga en el botón
            const btnSubmit = document.querySelector('button[type="submit"]');
            if (btnSubmit) {
                btnSubmit.disabled = true;
                btnSubmit.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Completando...';

                // Restaurar botón después de 30 segundos por si hay error
                setTimeout(() => {
                    btnSubmit.disabled = false;
                    btnSubmit.innerHTML = '<i class="fas fa-check-circle mr-2"></i>Completar Atención';
                }, 30000);
            }
            return true;
        }
        return false;
    });
}

/**
 * Precargar datos en el formulario de especialidad
 */
function precargarDatosFormularioEspecialidad() {
    try {
        if (window.precargarDatosEspecialidades) {
            if (window.datosPacienteEspecialidades) {
                precargarDatos(window.datosPacienteEspecialidades);
            }
            
            if (window.datosAtencionEspecialidades) {
                precargarDatos(window.datosAtencionEspecialidades);
            }
            
            if (window.datosConstantesVitalesEspecialidades) {
                precargarDatos(window.datosConstantesVitalesEspecialidades);
            }
            
        }
    } catch (error) {
        console.error('❌ Error precargando datos de especialidad:', error);
    }
}

/**
 * Precargar datos genérica
 */
function precargarDatos(datos) {
    if (!datos) return;
    
    Object.keys(datos).forEach(campo => {
        const elemento = document.querySelector(`input[name="${campo}"], select[name="${campo}"], textarea[name="${campo}"]`);
        if (elemento && datos[campo] !== null && datos[campo] !== undefined) {
            elemento.value = datos[campo];
        }
    });
}

// Funciones globales para compatibilidad
window.verificarSiEsModificacionEspecialidad = verificarSiEsModificacionEspecialidad;
window.confirmarEnvioFormularioEspecialidad = confirmarEnvioFormularioEspecialidad;
window.precargarDatosFormularioEspecialidad = precargarDatosFormularioEspecialidad;

// Función para limpiar estado (debugging)
window.limpiarEstadoFormularioEspecialidad = function() {
    sessionStorage.removeItem('estadoFormularioEspecialidad');
    location.reload();
};

// Función para forzar mostrar secciones finales (debugging)
window.forzarMostrarSeccionesFinales = function() {
    sessionStorage.setItem('estadoFormularioEspecialidad', 'completo');
    
    if (typeof window.mostrarSeccionesFinales === 'function') {
        window.mostrarSeccionesFinales();
    }
};

// Event listeners para advertencia al salir
window.addEventListener('beforeunload', function(e) {
    const estadoActual = sessionStorage.getItem('estadoFormularioEspecialidad');
    const esModificacion = verificarSiEsModificacionEspecialidad();
    
    // Solo advertir si está en modo completo y hay datos (excepto en modificaciones)
    if (estadoActual === 'completo' && !esModificacion) {
        const inputs = document.querySelectorAll('input:not([type="hidden"]), textarea, select');
        let hayDatos = false;
        
        for (let input of inputs) {
            if (input.value && input.value.trim() !== '') {
                hayDatos = true;
                break;
            }
        }
        
        if (hayDatos) {
            e.preventDefault();
            e.returnValue = 'Tienes cambios sin guardar en el formulario de especialidad.';
        }
    }
});
