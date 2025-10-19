/**
 * JAVASCRIPT ESPECÍFICO PARA FORMULARIO 005 - EVOLUCIÓN Y PRESCRIPCIONES
 * Maneja la funcionalidad específica del formulario 005
 */

// Configuración específica para formulario 005
const CONFIG_005 = {
    DEBUG: true,
    TIMEOUT: 30000,
    VERSION: 'FORMULARIO_005_v1.0'
};

// === UTILIDADES ESPECÍFICAS PARA 005 ===
function log005(nivel, mensaje, datos = null) {
    if (CONFIG_005.DEBUG && console) {
        const timestamp = new Date().toLocaleTimeString();
        const prefijo = `[FORM-005 ${timestamp}]`;

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

// ✅ USAR SISTEMA GLOBAL DE MENSAJES
function mostrarMensaje005(tipo, mensaje) {
    log005('info', `Mostrando mensaje ${tipo}: ${mensaje}`);
    // Usar el sistema global de mensajes del buscarPorFecha.js
    if (typeof mostrarMensaje === 'function') {
        mostrarMensaje(tipo, mensaje);
    } else {
        console.warn('Sistema global de mensajes no disponible');
    }
}

// === FUNCIÓN PRINCIPAL DE BÚSQUEDA PARA FORMULARIO 005 ===
async function buscarEvolucionPorFecha(fecha, identificador) {

    log005('info', `🔍 Iniciando búsqueda evolución: Fecha=${fecha}, Identificador=${identificador}`);

    if (!fecha) {
        return;
    }

    if (!identificador) {
        return;
    }


    try {
        const url = window.APP_URLS?.buscarEvolucionPorFecha || `${window.APP_URLS?.baseUrl}administrador/datos-pacientes/buscar-evolucion-por-fecha`;

        const payload = {
            fecha: fecha,
            identificador: identificador
        };

        const response = await fetch(url, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify(payload)
        });

        if (!response.ok) {
            throw new Error(`HTTP ${response.status}: ${response.statusText}`);
        }

        const json = await response.json();

        if (json.success && json.data) {

            // Cargar datos del paciente en sección A
            if (json.data.paciente) {
                cargarDatosPacienteFormulario005(json.data.paciente);
            }

            // Cargar datos de evolución
            cargarDatosFormulario005(json.data);

            // Habilitar botón PDF 005 después de cargar datos
            habilitarBotonPDF005();

            // Notificar que el formulario 005 se cargó exitosamente
            if (typeof completarCargaFormulario005 === 'function') {
                completarCargaFormulario005(true, true);
            }


        } else {

            limpiarFormulario005();
            deshabilitarBotonPDF005(); // Deshabilitar botón PDF cuando no hay datos

            // Notificar que el formulario 005 se procesó pero sin datos
            if (typeof completarCargaFormulario005 === 'function') {
                completarCargaFormulario005(true, false);
            }
        }

    } catch (error) {

        log005('error', '💥 Error en búsqueda de evolución:', error);
        limpiarFormulario005();
        deshabilitarBotonPDF005(); // Deshabilitar botón PDF en caso de error

        // Notificar error en el formulario 005
        if (typeof completarCargaFormulario005 === 'function') {
            completarCargaFormulario005(false, false);
        }
    }

}

// === FUNCIÓN PARA CARGAR DATOS DEL PACIENTE EN SECCIÓN A ===
function cargarDatosPacienteFormulario005(datosPaciente) {

    try {
        const contenedor = document.getElementById('contenedor-formulario-005');
        if (!contenedor) {
            return;
        }

        // Mapeo de campos del paciente usando IDs específicos
        const camposMapeo = {
            // Historia clínica (usar cedula como historia clínica)
            'ep_historia_clinica': datosPaciente.cedula || datosPaciente.historia_clinica || '',
            'ep_numero_archivo': datosPaciente.numero_archivo || '',
            'ep_numero_hoja': datosPaciente.numero_hoja || '',
            // Nombres y apellidos con IDs específicos
            'ep_primer_apellido': datosPaciente.primer_apellido || '',
            'ep_segundo_apellido': datosPaciente.segundo_apellido || '',
            'ep_primer_nombre': datosPaciente.primer_nombre || '',
            'ep_segundo_nombre': datosPaciente.segundo_nombre || '',
            // Datos básicos
            'ep_sexo': datosPaciente.sexo || '',
            'ep_edad': datosPaciente.edad || ''
        };


        // Llenar campos por ID específico
        Object.keys(camposMapeo).forEach(idCampo => {
            const valor = camposMapeo[idCampo];

            // Buscar el campo por ID
            const campo = document.getElementById(idCampo);

            if (campo && valor) {
                campo.value = valor;
                log005('success', `✅ Campo ${idCampo} llenado: ${valor}`);
            } else if (campo && !valor) {
                // Limpiar el campo si no hay valor
                campo.value = '';
            } else if (!campo) {
            }
        });

        // Manejar condición de edad si existe
        if (datosPaciente.condicion_edad) {
            const radioCondicion = contenedor.querySelector(`input[name="ep_condicion_edad"][value="${datosPaciente.condicion_edad}"]`);
            if (radioCondicion) {
                radioCondicion.checked = true;
            }
        }


    } catch (error) {
        log005('error', '💥 Error cargando datos del paciente:', error);
    }
}

// === FUNCIÓN PARA CARGAR DATOS EN EL FORMULARIO 005 ===
function cargarDatosFormulario005(datos) {

    try {
        // Limpiar tabla actual
        const tbody = document.querySelector('#contenedor-formulario-005 tbody');

        if (tbody) {
            tbody.innerHTML = '';
        }

        // Si hay evoluciones, cargarlas
        if (datos.evoluciones && datos.evoluciones.length > 0) {

            datos.evoluciones.forEach((evolucion, index) => {
                agregarFilaEvolucion(evolucion);
            });

        } else {
            // Mostrar mensaje de no datos
            if (tbody) {
                tbody.innerHTML = `
                    <tr>
                        <td colspan="5" class="border border-gray-400 px-2 py-4 text-center text-gray-500">
                            <div class="flex flex-col items-center">
                                <i class="fas fa-info-circle text-2xl mb-2 text-gray-300"></i>
                                <p class="text-xs">No hay registros de evolución para ${datos.fecha || 'esta fecha'}</p>
                                <p class="text-xs mt-1">Los registros aparecerán cuando se agreguen datos</p>
                            </div>
                        </td>
                    </tr>
                `;
            }
        }

    } catch (error) {
        log005('error', 'Error al mostrar los datos de evolución:', error);
    }

}

// === FUNCIÓN PARA AGREGAR FILA DE EVOLUCIÓN ===
function agregarFilaEvolucion(evolucion) {
    const tbody = document.querySelector('#contenedor-formulario-005 tbody');

    if (!tbody) {
        return;
    }

    const fila = document.createElement('tr');
    const htmlContent = `
        <td class="border border-gray-400 px-2 py-1 text-xs font-mono">
            ${evolucion.fecha || ''}
        </td>
        <td class="border border-gray-400 px-2 py-1 text-xs font-mono">
            ${evolucion.hora || ''}
        </td>
        <td class="border border-gray-400 px-2 py-1 text-xs">
            ${evolucion.notas_evolucion || ''}
        </td>
        <td class="border border-gray-400 px-2 py-1 text-xs">
            ${evolucion.farmacoterapia || ''}
        </td>
        <td class="border border-gray-400 px-2 py-1 text-center">
            ${evolucion.administrado == 1 || evolucion.administrado === true ?
                '<svg class="w-4 h-4 text-green-600 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>' :
                '<svg class="w-4 h-4 text-yellow-600 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>'
            }
        </td>
    `;

    fila.innerHTML = htmlContent;
    tbody.appendChild(fila);
}

// === FUNCIÓN PARA LIMPIAR FORMULARIO 005 ===
function limpiarFormulario005() {
    log005('info', '🧹 Limpiando formulario 005');

    // Limpiar tabla de evoluciones
    const tbody = document.querySelector('#contenedor-formulario-005 tbody');
    if (tbody) {
        tbody.innerHTML = `
            <tr>
                <td colspan="5" class="border border-gray-400 px-2 py-6 text-center text-gray-500">
                    <div class="flex flex-col items-center">
                        <i class="fas fa-file-medical text-2xl mb-2 text-gray-300"></i>
                        <p class="text-xs">No hay registros de evolución</p>
                        <p class="text-xs mt-1">Buscar por fecha para cargar datos</p>
                    </div>
                </td>
            </tr>
        `;
    }

    // Limpiar campos del paciente (mantenerlos vacíos para ser llenados por búsqueda)
    limpiarCamposPacienteFormulario005();

    // Asegurar que el botón PDF se deshabilite al limpiar
    deshabilitarBotonPDF005();
}

// === FUNCIÓN PARA LIMPIAR CAMPOS DEL PACIENTE ===
function limpiarCamposPacienteFormulario005() {

    const camposLimpiar = [
        'ep_historia_clinica',
        'ep_numero_archivo',
        'ep_primer_apellido',
        'ep_segundo_apellido',
        'ep_primer_nombre',
        'ep_segundo_nombre',
        'ep_sexo',
        'ep_edad'
    ];

    camposLimpiar.forEach(idCampo => {
        const campo = document.getElementById(idCampo);
        if (campo) {
            campo.value = '';
        }
    });

    // Limpiar radio buttons de condición edad
    const radiosCondicion = document.querySelectorAll('input[name="ep_condicion_edad"]');
    radiosCondicion.forEach(radio => {
        radio.checked = false;
    });

    log005('info', '🧹 Campos del paciente limpiados');
}

// === FUNCIÓN PARA HABILITAR BOTÓN PDF 005 ===
function habilitarBotonPDF005() {
    const btnPDF005 = document.getElementById('btn-generar-pdf-005');
    if (btnPDF005) {
        btnPDF005.disabled = false;
        btnPDF005.classList.remove('opacity-50', 'cursor-not-allowed');
    }
}

// === FUNCIÓN PARA DESHABILITAR BOTÓN PDF 005 ===
function deshabilitarBotonPDF005() {
    const btnPDF005 = document.getElementById('btn-generar-pdf-005');
    if (btnPDF005) {
        btnPDF005.disabled = true;
        btnPDF005.classList.add('opacity-50', 'cursor-not-allowed');
    }
}

// === INICIALIZACIÓN ===
document.addEventListener('DOMContentLoaded', function() {

    // Solo ejecutar si estamos en una vista que tiene el formulario 005
    const contenedorForm005 = document.getElementById('contenedor-formulario-005');

    if (contenedorForm005) {

        // Asegurar que el botón PDF esté deshabilitado inicialmente
        deshabilitarBotonPDF005();

        // Si hay un botón de consultar fecha, interceptar también para el 005
        const btnConsultar = document.getElementById('btn-consultar-fecha');

        if (btnConsultar) {

            btnConsultar.addEventListener('click', function(e) {
                // Esperar un momento para que se ejecute primero la búsqueda del 008
                setTimeout(() => {

                    const fecha = document.getElementById('filtro-fecha')?.value;
                    const identificador = window.formularioInfo?.identificador;

                    if (fecha && identificador) {
                        buscarEvolucionPorFecha(fecha, identificador);
                    }
                }, 500);
            });

        }
    }
});

// === BACKUP: INICIALIZACIÓN ALTERNATIVA ===
// En caso de que DOMContentLoaded ya haya pasado
if (document.readyState === 'complete' || document.readyState === 'interactive') {
    setTimeout(() => {
        const btnConsultar = document.getElementById('btn-consultar-fecha');
        if (btnConsultar && !btnConsultar.dataset.form005Added) {
            btnConsultar.dataset.form005Added = 'true';

            btnConsultar.addEventListener('click', function(e) {
                setTimeout(() => {
                    const fecha = document.getElementById('filtro-fecha')?.value;
                    const identificador = window.formularioInfo?.identificador;

                    if (fecha && identificador) {
                        buscarEvolucionPorFecha(fecha, identificador);
                    }
                }, 500);
            });
        }
    }, 100);
}


// === EXPORTAR FUNCIONES GLOBALES ===
window.buscarEvolucionPorFecha = buscarEvolucionPorFecha;
window.cargarDatosPacienteFormulario005 = cargarDatosPacienteFormulario005;
window.cargarDatosFormulario005 = cargarDatosFormulario005;
window.limpiarFormulario005 = limpiarFormulario005;
window.limpiarCamposPacienteFormulario005 = limpiarCamposPacienteFormulario005;
window.habilitarBotonPDF005 = habilitarBotonPDF005;
window.deshabilitarBotonPDF005 = deshabilitarBotonPDF005;