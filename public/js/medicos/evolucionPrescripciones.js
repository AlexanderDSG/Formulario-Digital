/**
 * Evolución y Prescripciones - Script para médicos triaje
 * Solo maneja la funcionalidad del modal (sin enfermería)
 * VERSIÓN CON DEBUG PARA BOTONES DE CIERRE
 */

// =====================================================
// FUNCIONES AUXILIARES DE DETECCIÓN
// =====================================================

function esMedicoTriaje() {
    // Detectar si estamos en el contexto de médico triaje
    const url = window.location.href;
    const tieneSeccionN = document.querySelector('textarea[name="plan_tratamiento"]');
    const tieneFormMedico = document.querySelector('form[action*="medicos/guardarMedico"]');

    return (url.includes('/medicos/') || tieneFormMedico) && tieneSeccionN;
}

// =====================================================
// INICIALIZACIÓN DEL BOTÓN
// =====================================================

document.addEventListener('DOMContentLoaded', function() {
    // Solo ejecutar en contexto de médico triaje
    if (!esMedicoTriaje()) return;

    let intentos = 0;
    const maxIntentos = 10;

    function intentarCrearBoton() {
        intentos++;
        const resultado = crearBotonEvolucionPrescripciones();

        if (resultado) {
            inicializarEventosBotonEvolucionPrescripciones();
        } else if (intentos < maxIntentos) {
            setTimeout(intentarCrearBoton, 500);
        }
    }

    setTimeout(intentarCrearBoton, 500);
});

// =====================================================
// CREACIÓN DEL BOTÓN EN LA SECCIÓN N
// =====================================================

function crearBotonEvolucionPrescripciones() {
    if (document.getElementById('btnEvolucionPrescripciones')) return true;

    const seccionN = buscarSeccionN();
    if (!seccionN) return false;

    const observacionesContainer = buscarContenedorObservaciones(seccionN);
    if (!observacionesContainer) return false;

    const botonContainer = document.createElement('div');
    botonContainer.className = 'ml-3 inline-block';
    botonContainer.id = 'container-btn-evolucion';
    botonContainer.style.verticalAlign = 'bottom';
    botonContainer.innerHTML = `
        <button type="button"
                id="btnEvolucionPrescripciones"
                class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg shadow-md hover:shadow-lg transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
            </svg>
            Evolución y Prescripciones
            <span class="hidden ml-2 px-2 py-1 bg-white text-blue-600 text-xs font-semibold rounded-full" id="badge-evolucion-count">0</span>
        </button>
    `;

    observacionesContainer.style.width = 'calc(100% - 310px)';
    observacionesContainer.style.display = 'inline-block';
    observacionesContainer.style.verticalAlign = 'top';

    const celdaTextarea = observacionesContainer.closest('td');
    if (celdaTextarea) {
        celdaTextarea.appendChild(botonContainer);
    }

    setTimeout(() => cargarContadorExistente(), 100);
    return true;
}

function buscarSeccionN() {
    // Buscar por header "N. PLAN DE TRATAMIENTO"
    const thElements = document.querySelectorAll('th');
    for (const th of thElements) {
        const texto = th.textContent.trim().toUpperCase();
        if (texto.includes('N. PLAN DE TRATAMIENTO') || texto.includes('PLAN DE TRATAMIENTO')) {
            const tabla = th.closest('table');
            if (tabla) return tabla.closest('.diagnostico-table-container') || tabla;
        }
    }

    // Buscar por textarea plan_tratamiento
    const textareaPlan = document.querySelector('textarea[name="plan_tratamiento"]');
    if (textareaPlan) {
        return textareaPlan.closest('.diagnostico-table-container') || textareaPlan.closest('table');
    }

    return null;
}

function buscarContenedorObservaciones(seccionN) {
    const textareaPlan = seccionN.querySelector('textarea[name="plan_tratamiento"]');
    if (textareaPlan) return textareaPlan;

    const textareas = seccionN.querySelectorAll('textarea');
    return textareas.length > 0 ? textareas[textareas.length - 1] : null;
}

// =====================================================
// EVENTOS DEL BOTÓN Y MODAL
// =====================================================

function inicializarEventosBotonEvolucionPrescripciones() {
    const boton = document.getElementById('btnEvolucionPrescripciones');
    if (!boton) return;

    boton.addEventListener('click', function(e) {
        e.preventDefault();
        abrirModalEvolucionPrescripciones();
    });
}

function abrirModalEvolucionPrescripciones() {
    const boton = document.getElementById('btnEvolucionPrescripciones');

    if (boton) {
        boton.disabled = true;
        boton.classList.add('opacity-75', 'cursor-not-allowed');
        boton.innerHTML = `
            <svg class="animate-spin w-4 h-4 mr-2" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            Cargando...
        `;
    }

    const datosModal = obtenerDatosModalRobustos();

    if (!datosModal.ate_codigo) {
        mostrarAlerta('error', 'Error: No se pudo identificar la atención actual');
        restaurarBotonEvolucionPrescripciones();
        return;
    }

    cargarModalEvolucionPrescripciones(datosModal);
}

function cargarModalEvolucionPrescripciones(datos) {
    // URL para médicos
    const urlModal = window.base_url + 'medicos/modalEvolucionPrescripciones';

    fetch(urlModal, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: JSON.stringify(datos)
    })
    .then(response => response.ok ? response.text() : Promise.reject('Error al cargar el modal'))
    .then(html => {
        insertarModalEnDOM(html);
        mostrarModalCompatible();
        restaurarBotonEvolucionPrescripciones();
    })
    .catch(error => {
        mostrarAlerta('error', 'Error al cargar el modal: ' + error);
        restaurarBotonEvolucionPrescripciones();
    });
}

function insertarModalEnDOM(html) {
    const modalExistente = document.getElementById('modalEvolucionPrescripciones');
    if (modalExistente) modalExistente.remove();

    document.body.insertAdjacentHTML('beforeend', html);

    setTimeout(() => {
        cargarDatosExistentes();
    }, 100);
}

// =====================================================
// FUNCIONES DEL MODAL
// =====================================================

window.addNewRow = function() {
    const tbody = document.getElementById('tablaEvolucionPrescripcionesBody');
    if (!tbody) return;

    const newRowNumber = tbody.children.length + 1;
    const row = window.createRow(newRowNumber);
    tbody.appendChild(row);
};

window.clearTable = function() {
    if (confirm('¿Está seguro de que desea limpiar toda la tabla?')) {
        window.generateInitialRows(0); // Solo limpiar, no agregar filas
    }
};

window.removeRow = function(button) {
    if (confirm('¿Eliminar esta fila?')) {
        button.closest('tr').remove();
    }
};

window.toggleAdministrado = function(button) {
    if (button.classList.contains('bg-green-100')) {
        // Cambiar de desmarcado a marcado
        button.classList.remove('bg-green-100', 'hover:bg-green-200', 'text-green-600', 'hover:text-green-700');
        button.classList.add('bg-green-600', 'hover:bg-green-700', 'text-white');
        button.setAttribute('title', 'Administrado');

        const svg = button.querySelector('svg path');
        if (svg) svg.setAttribute('d', 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z');
    } else {
        // Cambiar de marcado a desmarcado
        button.classList.remove('bg-green-600', 'hover:bg-green-700', 'text-white');
        button.classList.add('bg-green-100', 'hover:bg-green-200', 'text-green-600', 'hover:text-green-700');
        button.setAttribute('title', 'Marcar como administrado');

        const svg = button.querySelector('svg path');
        if (svg) svg.setAttribute('d', 'M5 13l4 4L19 7');
    }
};

window.generateInitialRows = function(count) {
    const tbody = document.getElementById('tablaEvolucionPrescripcionesBody');
    if (!tbody) return;

    tbody.innerHTML = '';

    // No generar filas automáticamente - el médico debe usar el botón "Agregar fila"
    // Solo limpiar la tabla
};

window.guardarEvolucionPrescripciones = function() {
    const modal = document.getElementById('modalEvolucionPrescripciones');
    const btnGuardar = document.getElementById('btnGuardarEvolucionPrescripciones');

    if (!modal) return;

    btnGuardar.disabled = true;
    btnGuardar.innerHTML = `
        <svg class="animate-spin w-4 h-4 mr-2" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
        </svg>
        Guardando...
    `;

    const datos = recopilarDatosModal();
    if (!datos) {
        restaurarBotonGuardar();
        return;
    }

    // URL para médicos
    fetch(window.base_url + 'medicos/guardarEvolucionPrescripciones', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: JSON.stringify(datos)
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            mostrarAlerta('success', data.message || 'Datos guardados correctamente');
            actualizarIndicadorEvolucionPrescripciones(data.registros_guardados || 0);

            // Recargar los datos del modal para reflejar los cambios guardados
            setTimeout(() => {
                cargarDatosExistentes();
            }, 500);

            // Cerrar modal después de un breve delay para permitir que se recarguen los datos
            setTimeout(() => {
                cerrarModal(modal);
            }, 1000);
        } else {
            mostrarAlerta('error', data.error || 'Error al guardar los datos');
        }
    })
    .catch(error => {
        mostrarAlerta('error', 'Error de conexión al guardar');
    })
    .finally(() => {
        restaurarBotonGuardar();
    });
};

function recopilarDatosModal() {
    const tbody = document.getElementById('tablaEvolucionPrescripcionesBody');
    const ate_codigo = window.ate_codigo || obtenerValorCampo('ate_codigo');

    if (!tbody || !ate_codigo) {
        mostrarAlerta('error', 'Error: Datos insuficientes para guardar');
        return null;
    }

    const filas = [];
    const rows = tbody.querySelectorAll('tr');

    rows.forEach((row, index) => {
        const fecha = row.querySelector(`input[name*="fecha"]`)?.value;
        const hora = row.querySelector(`input[name*="hora"]`)?.value;
        const notasEvolucion = row.querySelector(`textarea[name*="notas_evolucion"]`)?.value;
        const farmacoterapia = row.querySelector(`textarea[name*="farmacoterapia"]`)?.value;
        const epCodigo = row.querySelector(`input[name*="ep_codigo"]`)?.value;

        // Debug para el botón de administrado
        const btnAdministrado = row.querySelector('.btn-administrado');
        const administrado = btnAdministrado?.classList.contains('bg-green-600') ? 'S' : 'N';
        const administradoValue = administrado === 'S' ? 1 : 0;

        if (fecha || hora || notasEvolucion || farmacoterapia) {
            // Obtener el número de hoja del campo readonly
            const numeroHoja = document.querySelector('input[name="ep_numero_hoja"]')?.value || '00001';

            const fila = {
                ep_fecha: fecha,
                ep_hora: hora,
                ep_notas_evolucion: notasEvolucion,
                ep_farmacoterapia: farmacoterapia,
                ep_administrado: administradoValue,
                ep_orden: index + 1,
                ep_numero_hoja: numeroHoja
            };

            // Solo agregar ep_codigo si existe (para registros existentes)
            if (epCodigo) {
                fila.ep_codigo = epCodigo;
            }

            filas.push(fila);
        }
    });

    if (filas.length === 0) {
        mostrarAlerta('warning', 'No hay datos para guardar');
        return null;
    }

    return {
        ate_codigo: ate_codigo,
        filas: filas
    };
}

function restaurarBotonGuardar() {
    const btnGuardar = document.getElementById('btnGuardarEvolucionPrescripciones');
    if (btnGuardar) {
        btnGuardar.disabled = false;
        btnGuardar.innerHTML = `
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4"></path>
            </svg>
            Guardar
        `;
    }
}

function cargarDatosExistentes() {
    const ate_codigo = window.ate_codigo || obtenerValorCampo('ate_codigo');

    if (!ate_codigo) {
        window.generateInitialRows(0); // No agregar filas automáticamente
        return;
    }

    // URL para médicos
    fetch(window.base_url + 'medicos/obtenerEvolucionesConUsuario', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: JSON.stringify({ ate_codigo: ate_codigo })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success && data.data && data.data.length > 0) {
            cargarFilasConDatos(data.data);
        } else {
            window.generateInitialRows(0); // No agregar filas automáticamente
        }
    })
    .catch(error => {
        window.generateInitialRows(0); // No agregar filas automáticamente
    });
}

function cargarFilasConDatos(evoluciones) {
    const tbody = document.getElementById('tablaEvolucionPrescripcionesBody');
    if (!tbody) return;

    tbody.innerHTML = '';

    evoluciones.forEach((evolucion, index) => {
        const row = crearFilaConDatos(evolucion, index + 1);
        tbody.appendChild(row);
    });

    // No agregar filas automáticamente - el médico debe usar el botón "Agregar fila"
}

function crearFilaConDatos(evolucion, number) {
    const row = document.createElement('tr');
    row.className = 'border-b border-gray-200 hover:bg-gray-50';

    // ✅ COMPLETAR FECHA Y HORA AUTOMÁTICAMENTE SI ESTÁN VACÍAS
    // Usar zona horaria local para fecha
    const fechaActual = new Date();
    const fechaFormateada = `${fechaActual.getFullYear()}-${String(fechaActual.getMonth() + 1).padStart(2, '0')}-${String(fechaActual.getDate()).padStart(2, '0')}`;
    const horaActual = new Date().toTimeString().split(' ')[0].substring(0, 5); // HH:MM

    const fechaFinal = evolucion.ep_fecha || fechaFormateada;
    const horaFinal = evolucion.ep_hora || horaActual;

    const administradoClass = evolucion.ep_administrado == 1
        ? 'bg-green-600 hover:bg-green-700 text-white'
        : 'bg-green-100 hover:bg-green-200 text-green-600 hover:text-green-700';

    const administradoTitle = evolucion.ep_administrado == 1
        ? 'Administrado'
        : 'Marcar como administrado';

    const administradoIcon = evolucion.ep_administrado == 1
        ? 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z'
        : 'M5 13l4 4L19 7';

    row.innerHTML = `
        <td class="border border-gray-300 p-1">
            <input type="date" class="w-full px-2 py-1 text-xs border border-gray-300 rounded focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                   name="ep_fecha_${number}" value="${fechaFinal}">
            <input type="hidden" name="ep_codigo_${number}" value="${evolucion.ep_codigo || ''}">
        </td>
        <td class="border border-gray-300 p-1">
            <input type="time" class="w-full px-2 py-1 text-xs border border-gray-300 rounded focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                   name="ep_hora_${number}" value="${horaFinal}">
        </td>
        <td class="border border-gray-300 p-1">
            <textarea class="w-full px-2 py-1 text-xs border border-gray-300 rounded resize-y focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                      name="ep_notas_evolucion_${number}"
                      rows="3"
                      placeholder="Escribir notas de evolución...">${evolucion.ep_notas_evolucion || ''}</textarea>
        </td>
        <td class="border border-gray-300 p-1">
            <textarea class="w-full px-2 py-1 text-xs border border-gray-300 rounded resize-y focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                      name="ep_farmacoterapia_${number}"
                      rows="3"
                      placeholder="Indicaciones para enfermería y otros profesionales...">${evolucion.ep_farmacoterapia || ''}</textarea>
        </td>
        <td class="border border-gray-300 p-1 text-center">
            <button type="button"
                    class="inline-flex items-center justify-center w-8 h-8 ${administradoClass} rounded-full transition-colors btn-administrado"
                    title="${administradoTitle}"
                    onclick="toggleAdministrado(this)">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="${administradoIcon}"></path>
                </svg>
            </button>
        </td>
        <td class="border border-gray-300 p-1 text-center">
            <button type="button"
                    class="inline-flex items-center justify-center w-8 h-8 bg-red-100 hover:bg-red-200 text-red-600 hover:text-red-700 rounded-full transition-colors"
                    onclick="removeRow(this)"
                    title="Eliminar esta fila">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                </svg>
            </button>
        </td>
    `;

    return row;
}

window.createRow = function(number) {
    const row = document.createElement('tr');
    row.className = 'border-b border-gray-200 hover:bg-gray-50';

    // ✅ OBTENER FECHA Y HORA ACTUAL AUTOMÁTICAMENTE
    // Usar zona horaria local para fecha
    const fechaActual = new Date();
    const fechaFormateada = `${fechaActual.getFullYear()}-${String(fechaActual.getMonth() + 1).padStart(2, '0')}-${String(fechaActual.getDate()).padStart(2, '0')}`;
    const horaActual = new Date().toTimeString().split(' ')[0].substring(0, 5); // HH:MM

    row.innerHTML = `
        <td class="border border-gray-300 p-1">
            <input type="date" class="w-full px-2 py-1 text-xs border border-gray-300 rounded focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                   name="ep_fecha_${number}" value="${fechaFormateada}">
        </td>
        <td class="border border-gray-300 p-1">
            <input type="time" class="w-full px-2 py-1 text-xs border border-gray-300 rounded focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                   name="ep_hora_${number}" value="${horaActual}">
        </td>
        <td class="border border-gray-300 p-1">
            <textarea class="w-full px-2 py-1 text-xs border border-gray-300 rounded resize-y focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                      name="ep_notas_evolucion_${number}"
                      rows="3"
                      placeholder="Escribir notas de evolución..."></textarea>
        </td>
        <td class="border border-gray-300 p-1">
            <textarea class="w-full px-2 py-1 text-xs border border-gray-300 rounded resize-y focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                      name="ep_farmacoterapia_${number}"
                      rows="3"
                      placeholder="Indicaciones para enfermería y otros profesionales..."></textarea>
        </td>
        <td class="border border-gray-300 p-1 text-center">
            <button type="button"
                    class="inline-flex items-center justify-center w-8 h-8 bg-green-100 hover:bg-green-200 text-green-600 hover:text-green-700 rounded-full transition-colors btn-administrado"
                    title="Marcar como administrado"
                    onclick="toggleAdministrado(this)">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                </svg>
            </button>
        </td>
        <td class="border border-gray-300 p-1 text-center">
            <button type="button"
                    class="inline-flex items-center justify-center w-8 h-8 bg-red-100 hover:bg-red-200 text-red-600 hover:text-red-700 rounded-full transition-colors"
                    onclick="removeRow(this)"
                    title="Eliminar esta fila">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                </svg>
            </button>
        </td>
    `;

    return row;
};

// =====================================================
// MANEJO DEL MODAL (MOSTRAR/CERRAR)
// =====================================================

function mostrarModalCompatible() {
    const modal = document.getElementById('modalEvolucionPrescripciones');
    if (!modal) {
        console.error('DEBUG: Modal no encontrado al intentar mostrar');
        return;
    }

    // NO usar Bootstrap modal ya que está causando conflictos
    // Forzar el uso del modal manual siempre
    mostrarModalManual(modal);
}

function mostrarModalManual(modal) {
    modal.classList.remove('hidden');
    document.body.style.overflow = 'hidden';
    configurarEventosCierreModal(modal);
}

function configurarEventosCierreModal(modal) {

    // Método directo sin variables
    modal.addEventListener('click', function(e) {
        if (e.target.getAttribute('data-dismiss') === 'modal' ||
            e.target.closest('[data-dismiss="modal"]')) {
            modal.classList.add('hidden');
            document.body.style.overflow = '';
        }
    });

    // Escape directo
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            modal.classList.add('hidden');
            document.body.style.overflow = '';
        }
    });
}

function cerrarModal(modal) {

    if (modal) {
        modal.classList.add('hidden');
        document.body.style.overflow = '';
    }
}
function cerrarConEscape(e) {
    if (e.key === 'Escape') {
        const modal = document.getElementById('modalEvolucionPrescripciones');
        if (modal && !modal.classList.contains('hidden')) {
            cerrarModal(modal);
        }
    }
}
// =====================================================
// UTILIDADES Y DATOS
// =====================================================

function obtenerDatosModalRobustos() {
    // Para médicos triaje usamos ate_codigo directamente
    return {
        ate_codigo: window.ate_codigo || obtenerValorCampo('ate_codigo'),
        pac_datos: {
            primer_apellido: obtenerValorCampo('apellido1') || obtenerValorCampo('pac_primer_apellido'),
            segundo_apellido: obtenerValorCampo('apellido2') || obtenerValorCampo('pac_segundo_apellido'),
            primer_nombre: obtenerValorCampo('nombre1') || obtenerValorCampo('pac_primer_nombre'),
            segundo_nombre: obtenerValorCampo('nombre2') || obtenerValorCampo('pac_segundo_nombre'),
            edad: obtenerValorCampo('pac_edad_valor') || obtenerValorCampo('edad'),
            numero_historia_clinica: obtenerValorCampo('pac_his_cli') || obtenerValorCampo('estab_historia_clinica'),
            numero_archivo: window.estabArchivo || obtenerValorCampo('numero_archivo')
        }
    };
}

function obtenerValorCampo(nombre) {
    const selectores = [
        `[name="${nombre}"]`,
        `[name*="${nombre}"]`,
        `#${nombre}`,
        `input[name="${nombre}"]`,
        `select[name="${nombre}"]`,
        `textarea[name="${nombre}"]`
    ];

    for (const selector of selectores) {
        const campo = document.querySelector(selector);
        if (campo) return campo.value || '';
    }

    return '';
}

function restaurarBotonEvolucionPrescripciones() {
    const boton = document.getElementById('btnEvolucionPrescripciones');

    if (boton) {
        boton.disabled = false;
        boton.classList.remove('opacity-75', 'cursor-not-allowed');
        boton.innerHTML = `
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
            </svg>
            Evolución y Prescripciones
            <span class="hidden ml-2 px-2 py-1 bg-white text-blue-600 text-xs font-semibold rounded-full" id="badge-evolucion-count">0</span>
        `;

        setTimeout(() => cargarContadorExistente(), 100);
    }
}

function cargarContadorExistente() {
    const ate_codigo = window.ate_codigo || obtenerValorCampo('ate_codigo');
    if (!ate_codigo) return;

    // URL para médicos
    fetch(window.base_url + 'medicos/contarEvolucionPrescripciones', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ ate_codigo: ate_codigo })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success && data.count > 0) {
            actualizarIndicadorEvolucionPrescripciones(data.count);
        }
    })
    .catch(() => {});
}

function actualizarIndicadorEvolucionPrescripciones(cantidad) {
    const boton = document.getElementById('btnEvolucionPrescripciones');
    const badge = document.getElementById('badge-evolucion-count');

    if (boton && badge && cantidad > 0) {
        badge.textContent = cantidad;
        badge.classList.remove('hidden');

        boton.classList.remove('bg-blue-600', 'hover:bg-blue-700', 'focus:ring-blue-500');
        boton.classList.add('bg-green-600', 'hover:bg-green-700', 'focus:ring-green-500');

        badge.classList.remove('bg-white', 'text-blue-600');
        badge.classList.add('bg-white', 'text-green-600');
    }
}

function mostrarAlerta(tipo, mensaje) {
    if (typeof mostrarMensaje === 'function') {
        mostrarMensaje(tipo, mensaje);
    } else if (typeof Swal !== 'undefined') {
        Swal.fire({
            icon: tipo === 'error' ? 'error' : tipo,
            title: mensaje,
            timer: 3000,
            showConfirmButton: false
        });
    } else {
        Swal.fire({
            icon: tipo === 'error' ? 'error' : 'info',
            title: tipo === 'error' ? 'Error' : 'Información',
            text: mensaje,
            confirmButtonText: 'Aceptar'
        });
    }
}

// =====================================================
// CALLBACK GLOBAL
// =====================================================
window.onEvolucionPrescripcionesGuardado = function(cantidad) {
    actualizarIndicadorEvolucionPrescripciones(cantidad);
    mostrarAlerta('success', 'Evolución y prescripciones guardadas correctamente');
};