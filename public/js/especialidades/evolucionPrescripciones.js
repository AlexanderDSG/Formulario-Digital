/**
 * Evolución y Prescripciones - Script para especialidades
 * Maneja la creación del botón y funcionamiento del modal
 */

// =====================================================
// INICIALIZACIÓN DEL BOTÓN
// =====================================================

document.addEventListener('DOMContentLoaded', function() {
    if (!window.contextoEspecialidad) return;

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
        <div class="flex flex-col space-y-2">
            <button type="button"
                    id="btnEvolucionPrescripciones"
                    class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg shadow-md hover:shadow-lg transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                </svg>
                Evolución y Prescripciones
                <span class="hidden ml-2 px-2 py-1 bg-white text-blue-600 text-xs font-semibold rounded-full" id="badge-evolucion-count">0</span>
            </button>

            <button type="button"
                    id="btnEnviarEnfermeria"
                    class="inline-flex items-center px-4 py-2 bg-orange-600 hover:bg-orange-700 text-white text-sm font-medium rounded-lg shadow-md hover:shadow-lg transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-orange-500 focus:ring-offset-2">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                </svg>
                <span id="btnEnviarTexto">${window.contextoEnfermeria ? 'Enviar a Médico' : 'Enviar a Enfermería'}</span>
            </button>
            ${window.contextoEnfermeria ? '<div class="text-xs text-gray-500 text-center mt-1">Enviando desde: Enfermería</div>' : '<div class="text-xs text-gray-500 text-center mt-1">Enviando desde: Médico</div>'}
        </div>
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

    // Inicializar evento del botón "Enviar a Enfermería"
    const botonEnfermeria = document.getElementById('btnEnviarEnfermeria');
    if (botonEnfermeria) {
        botonEnfermeria.addEventListener('click', function(e) {
            e.preventDefault();
            enviarAEnfermeria();
        });
    }
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

    if (!datosModal.are_codigo) {
        mostrarAlerta('error', 'Error: No se pudo identificar la atención actual');
        restaurarBotonEvolucionPrescripciones();
        return;
    }

    cargarModalEvolucionPrescripciones(datosModal);
}

function cargarModalEvolucionPrescripciones(datos) {
    const urlModal = window.base_url + 'especialidades/modalEvolucionPrescripciones';

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
    // Limpiar cualquier modal existente y backdrop de Bootstrap
    limpiarModalesExistentes();

    document.body.insertAdjacentHTML('beforeend', html);

    setTimeout(() => {
        cargarDatosExistentes();
    }, 100);
}

function limpiarModalesExistentes() {
    // Remover modal existente
    const modalExistente = document.getElementById('modalEvolucionPrescripciones');
    if (modalExistente) {
        modalExistente.remove();
    }

    // Remover cualquier backdrop de Bootstrap
    const backdrops = document.querySelectorAll('.modal-backdrop');
    backdrops.forEach(backdrop => {
        backdrop.remove();
    });

    // Restaurar body
    document.body.style.overflow = '';
    document.body.classList.remove('modal-open');
    document.body.style.paddingRight = '';
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

    fetch(window.base_url + 'especialidades/guardarEvolucionPrescripciones', {
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

    fetch(window.base_url + 'especialidades/obtenerEvolucionesConUsuario', {
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

    // Cargar filas con datos existentes
    evoluciones.forEach((evolucion, index) => {
        const row = crearFilaConDatos(evolucion, index + 1);
        tbody.appendChild(row);
    });

    // No agregar filas automáticamente - el médico debe usar el botón "Agregar fila"
}

function crearFilaConDatos(evolucion, number) {
    const row = document.createElement('tr');
    row.className = 'border-b border-gray-200 hover:bg-gray-50';

    // COMPLETAR FECHA Y HORA AUTOMÁTICAMENTE SI ESTÁN VACÍAS
    // Usar zona horaria local para fecha
    const fechaActual = new Date();
    const fechaFormateada = `${fechaActual.getFullYear()}-${String(fechaActual.getMonth() + 1).padStart(2, '0')}-${String(fechaActual.getDate()).padStart(2, '0')}`;
    const horaActual = new Date().toTimeString().split(' ')[0].substring(0, 5); // HH:MM

    const fechaFinal = evolucion.ep_fecha || fechaFormateada;
    const horaFinal = formatearHora(evolucion.ep_hora) || horaActual;

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

    // OBTENER FECHA Y HORA ACTUAL AUTOMÁTICAMENTE
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
    if (!modal) return;

    // NO usar Bootstrap modal ya que está causando conflictos
    // Forzar el uso del modal manual siempre
    mostrarModalManual(modal);
}

function mostrarModalManual(modal) {

    // Asegurar que NO se usen los estilos de Bootstrap
    modal.style.display = 'block';
    modal.classList.remove('hidden');

    // Prevenir que Bootstrap agregue sus clases
    document.body.style.overflow = 'hidden';
    document.body.classList.remove('modal-open'); // Remover si Bootstrap la agrega

    // Remover backdrop de Bootstrap si existe
    const existingBootstrapBackdrop = document.querySelector('.modal-backdrop');
    if (existingBootstrapBackdrop) {
        existingBootstrapBackdrop.remove();
    }

    configurarEventosCierreModal(modal);
}

function configurarEventosCierreModal(modal) {

    // Remover eventos anteriores para evitar duplicados
    modal.removeAttribute('data-eventos-configurados');

    if (modal.hasAttribute('data-eventos-configurados')) {
        return;
    }

    // Marcar que ya se configuraron los eventos
    modal.setAttribute('data-eventos-configurados', 'true');

    // Configurar botones de cierre
    const closeButtons = modal.querySelectorAll('[data-dismiss="modal"], .close');

    closeButtons.forEach(btn => {
        btn.removeEventListener('click', cerrarModalHandler); // Remover eventos anteriores
        btn.addEventListener('click', cerrarModalHandler);
    });

    // Configurar backdrop - buscar por diferentes selectores
    let backdrop = modal.querySelector('.fixed.inset-0.bg-black.bg-opacity-50');
    if (!backdrop) {
        backdrop = modal.querySelector('.fixed.inset-0');
    }


    if (backdrop) {
        backdrop.removeEventListener('click', backdropClickHandler); // Remover eventos anteriores
        backdrop.addEventListener('click', backdropClickHandler);
    }

    // Handler para cerrar modal
    function cerrarModalHandler(e) {
        e.preventDefault();
        e.stopPropagation();
        cerrarModal(modal);
    }

    // Handler para clic en backdrop
    function backdropClickHandler(e) {
        if (e.target === backdrop) {
            cerrarModal(modal);
        }
    }

    // Configurar tecla Escape
    function escHandler(e) {
        if (e.key === 'Escape') {
            cerrarModal(modal);
            document.removeEventListener('keydown', escHandler);
        }
    }

    document.addEventListener('keydown', escHandler);

    // Almacenar referencia para poder remover el evento después
    modal._escHandler = escHandler;
}

function cerrarModal(modal) {

    // Restaurar scroll del body
    document.body.style.overflow = '';
    document.body.classList.remove('modal-open'); // Remover clase de Bootstrap

    // Limpiar todos los atributos que Bootstrap pudo haber agregado
    modal.removeAttribute('aria-modal');
    modal.removeAttribute('role');
    modal.style.display = 'none';
    modal.style.paddingRight = '';

    // Agregar clase hidden
    modal.classList.add('hidden');

    // Limpiar eventos de teclado
    if (modal._escHandler) {
        document.removeEventListener('keydown', modal._escHandler);
        modal._escHandler = null;
    }

    // Marcar que los eventos ya no están configurados
    modal.removeAttribute('data-eventos-configurados');

    // Remover backdrop si existe
    const existingBackdrop = document.querySelector('.modal-backdrop');
    if (existingBackdrop) {
        existingBackdrop.remove();
    }

    // Después de un pequeño delay, remover completamente el modal del DOM
    setTimeout(() => {
        if (modal && modal.parentNode) {
            modal.remove();
        }
    }, 300);

}

// =====================================================
// UTILIDADES Y DATOS
// =====================================================

/**
 * Formatear hora de SQL Server al formato HTML5 (HH:mm:ss)
 * Elimina los microsegundos que SQL Server agrega
 */
function formatearHora(hora) {
    if (!hora) return '';

    // Si viene en formato TIME de SQL Server (09:35:00.0000000)
    if (hora.includes('.')) {
        hora = hora.split('.')[0];
    }

    // Asegurar formato HH:mm:ss
    const parts = hora.split(':');
    if (parts.length >= 2) {
        const hh = parts[0].padStart(2, '0');
        const mm = parts[1].padStart(2, '0');
        const ss = (parts[2] || '00').substring(0, 2).padStart(2, '0');
        return `${hh}:${mm}:${ss}`;
    }

    return hora;
}

function obtenerDatosModalRobustos() {
    let are_codigo = window.are_codigo || obtenerValorCampo('are_codigo');

    if (!are_codigo) {
        const url = window.location.pathname;
        const matches = url.match(/\/formulario\/(\d+)$/);
        are_codigo = matches ? matches[1] : null;
    }

    if (Array.isArray(are_codigo)) {
        are_codigo = are_codigo.length > 0 ? are_codigo[0] : null;
    }

    if (typeof are_codigo === 'number') {
        are_codigo = are_codigo.toString();
    }

    return {
        are_codigo: are_codigo,
        ate_codigo: window.ate_codigo || obtenerValorCampo('ate_codigo'),
        esp_codigo: window.especialidad_codigo || obtenerValorCampo('esp_codigo'),
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

    fetch(window.base_url + 'especialidades/contarEvolucionPrescripciones', {
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
// FUNCIÓN ENVIAR A ENFERMERÍA
// =====================================================

function enviarAEnfermeria() {
    const ate_codigo = window.ate_codigo || obtenerValorCampo('ate_codigo');
    const are_codigo = window.are_codigo || obtenerValorCampo('are_codigo');

    // Determinar el contexto actual
    const esEnfermeria = window.contextoEnfermeria || false;
    const destino = esEnfermeria ? 'médico' : 'enfermería';
    const origen = esEnfermeria ? 'enfermería' : 'médico';

    if (!ate_codigo || !are_codigo) {
        console.error('❌ Códigos faltantes - ate_codigo:', ate_codigo, 'are_codigo:', are_codigo);
        mostrarAlerta('error', 'Error: No se encontraron los códigos necesarios para el envío');
        return;
    }

    // Confirmar acción con SweetAlert2
    const titulo = esEnfermeria ? '¿Enviar a médico?' : '¿Enviar a enfermería?';
    const mensaje = esEnfermeria
        ? `
            <div class="text-left">
                <p class="mb-3">El paciente será devuelto a la lista médica de esta especialidad:</p>
                <ul class="space-y-2 mb-3">
                    <li class="flex items-start">
                        <span class="text-green-500 mr-2">✅</span>
                        <span>El médico tendrá acceso a todas las secciones completadas</span>
                    </li>
                    <li class="flex items-start">
                        <span class="text-red-500 mr-2">🚫</span>
                        <span>Este paciente ya no aparecerá en la lista de enfermería</span>
                    </li>
                    <li class="flex items-start">
                        <span class="text-orange-500 mr-2">⚠️</span>
                        <span>Esta acción no se puede deshacer</span>
                    </li>
                </ul>
                <p class="font-semibold">¿Está seguro de continuar?</p>
            </div>
        `
        : `
            <div class="text-left">
                <p class="mb-3">El paciente será transferido a la lista de enfermería de esta especialidad:</p>
                <ul class="space-y-2 mb-3">
                    <li class="flex items-start">
                        <span class="text-green-500 mr-2">✅</span>
                        <span>La enfermería tendrá acceso a las secciones A-N completadas</span>
                    </li>
                    <li class="flex items-start">
                        <span class="text-red-500 mr-2">🚫</span>
                        <span>Este paciente ya no aparecerá en la lista médica</span>
                    </li>
                    <li class="flex items-start">
                        <span class="text-orange-500 mr-2">⚠️</span>
                        <span>Esta acción no se puede deshacar</span>
                    </li>
                </ul>
                <p class="font-semibold">¿Está seguro de continuar?</p>
            </div>
        `;

    Swal.fire({
        icon: 'question',
        title: titulo,
        html: mensaje,
        showCancelButton: true,
        confirmButtonText: 'Sí, enviar',
        cancelButtonText: 'Cancelar',
        confirmButtonColor: '#10b981',
        cancelButtonColor: '#6b7280',
        width: '600px'
    }).then((result) => {
        if (!result.isConfirmed) return;

        // Continuar con el envío si confirma
        ejecutarEnvioEnfermeria(ate_codigo, are_codigo, esEnfermeria);
    });
}

// Función auxiliar para ejecutar el envío
function ejecutarEnvioEnfermeria(ate_codigo, are_codigo, esEnfermeria) {
    // Determinar origen y destino
    const destino = esEnfermeria ? 'médico' : 'enfermería';
    const origen = esEnfermeria ? 'enfermería' : 'médico';

    // Cambiar estado del botón
    const boton = document.getElementById('btnEnviarEnfermeria');
    if (boton) {
        const textoOriginal = boton.innerHTML;
        boton.disabled = true;
        boton.classList.add('opacity-75', 'cursor-not-allowed');
        boton.innerHTML = `
            <svg class="animate-spin w-4 h-4 mr-2" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 0 1 4 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            ${esEnfermeria ? 'Enviando a médico...' : 'Enviando a enfermería...'}
        `;

        // Recopilar datos estructurados de las secciones E-N (igual que observación)
        const datosSeccionesEspecialista = recopilarDatosSeccionesEspecialista();

        // URL dinámica según el contexto (usar la misma URL pero diferente lógica en el backend)
        const urlEnvio = window.base_url + 'especialidades/enfermeria/recibirDatos';

        // Realizar envío
        fetch(urlEnvio, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify({
                ate_codigo: ate_codigo,
                are_codigo: are_codigo,
                datos_formulario: datosSeccionesEspecialista,
                origen: origen,
                destino: destino,
                // Para determinar el estado correcto cuando enfermería envía a médico
                esDevolucionEnfermeria: esEnfermeria,
                // Información del usuario que envía
                usuario_envia: {
                    rol: esEnfermeria ? 'enfermeria' : 'medico',
                    contexto: esEnfermeria ? 'Enfermería de Especialidad' : 'Médico Especialista'
                }
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const mensajeExito = esEnfermeria
                    ? (data.message || 'Paciente enviado a médico correctamente')
                    : (data.message || 'Paciente enviado a enfermería correctamente');

                mostrarAlerta('success', mensajeExito);

                // Redirigir a la lista de especialidades después de 2 segundos
                setTimeout(() => {
                    window.location.href = window.base_url + 'especialidades/lista';
                }, 2000);
            } else {
                const mensajeError = esEnfermeria
                    ? (data.message || 'Error al enviar paciente a médico')
                    : (data.message || 'Error al enviar paciente a enfermería');

                mostrarAlerta('error', mensajeError);

                // Restaurar botón
                boton.disabled = false;
                boton.classList.remove('opacity-75', 'cursor-not-allowed');
                boton.innerHTML = textoOriginal;
            }
        })
        .catch(error => {
            console.error('Error:', error);
            const mensajeErrorConexion = esEnfermeria
                ? 'Error de conexión al enviar paciente a médico'
                : 'Error de conexión al enviar paciente a enfermería';

            mostrarAlerta('error', mensajeErrorConexion);

            // Restaurar botón
            boton.disabled = false;
            boton.classList.remove('opacity-75', 'cursor-not-allowed');
            boton.innerHTML = textoOriginal;
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

/**
 * Recopilar datos de las secciones E-N del especialista (patrón observación)
 */
function recopilarDatosSeccionesEspecialista() {
    const datos = {};

    try {
        // Sección E - Antecedentes
        datos.seccionE = {
            no_aplica: document.getElementById('ant_no_aplica')?.checked || false,
            antecedentes: Array.from(document.querySelectorAll('input[name="antecedentes[]"]:checked')).map(cb => cb.value),
            descripcion: document.getElementById('ant_descripcion')?.value || ''
        };

        // Sección F - Problema actual
        datos.seccionF = {
            descripcion: document.getElementById('ep_descripcion_actual')?.value || ''
        };

        // Sección H - Examen físico
        datos.seccionH = {
            zonas: Array.from(document.querySelectorAll('input[name="zonas_examen_fisico[]"]:checked')).map(cb => cb.value),
            descripcion: document.getElementById('ef_descripcion')?.value || ''
        };

        // Sección I - Examen trauma
        datos.seccionI = {
            descripcion: document.getElementById('eft_descripcion')?.value || ''
        };

        // Sección J - Embarazo/Parto
        datos.seccionJ = {
            no_aplica: document.getElementById('emb_no_aplica')?.checked || false,
            gestas: document.getElementById('emb_gestas')?.value || '',
            partos: document.getElementById('emb_partos')?.value || '',
            abortos: document.getElementById('emb_abortos')?.value || '',
            cesareas: document.getElementById('emb_cesareas')?.value || '',
            fum: document.getElementById('emb_fum')?.value || '',
            semanas_gestacion: document.getElementById('emb_semanas_gestacion')?.value || '',
            movimiento_fetal: document.querySelector('input[name="emb_movimiento_fetal"]:checked')?.value || '',
            fcf: document.getElementById('emb_fcf')?.value || '',
            ruptura_membranas: document.querySelector('input[name="emb_ruptura_membranas"]:checked')?.value || '',
            tiempo_ruptura: document.getElementById('emb_tiempo_ruptura')?.value || '',
            afu: document.getElementById('emb_afu')?.value || '',
            presentacion: document.getElementById('emb_presentacion')?.value || '',
            sangrado_vaginal: document.querySelector('input[name="emb_sangrado_vaginal"]:checked')?.value || '',
            contracciones: document.querySelector('input[name="emb_contracciones"]:checked')?.value || '',
            dilatacion: document.getElementById('emb_dilatacion')?.value || '',
            borramiento: document.getElementById('emb_borramiento')?.value || '',
            plano: document.getElementById('emb_plano')?.value || '',
            pelvis_viable: document.querySelector('input[name="emb_pelvis_viable"]:checked')?.value || '',
            score_mama: document.getElementById('emb_score_mama')?.value || '',
            observaciones: document.getElementById('emb_observaciones')?.value || ''
        };

        // Sección K - Exámenes complementarios
        datos.seccionK = {
            no_aplica: document.getElementById('exc_no_aplica')?.checked || false,
            tipos: Array.from(document.querySelectorAll('input[name="tipos_examenes[]"]:checked')).map(cb => cb.value),
            observaciones: document.getElementById('exc_observaciones')?.value || ''
        };

        // Sección L - Diagnósticos presuntivos
        datos.seccionL = {};
        for (let i = 1; i <= 3; i++) {
            datos.seccionL[`diagnostico${i}`] = {
                descripcion: document.getElementById(`diag_pres_desc${i}`)?.value || '',
                cie: document.getElementById(`diag_pres_cie${i}`)?.value || ''
            };
        }

        // Sección M - Diagnósticos definitivos
        datos.seccionM = {};
        for (let i = 1; i <= 3; i++) {
            datos.seccionM[`diagnostico${i}`] = {
                descripcion: document.getElementById(`diag_def_desc${i}`)?.value || '',
                cie: document.getElementById(`diag_def_cie${i}`)?.value || ''
            };
        }

        // Sección N - Tratamiento
        datos.seccionN = {
            plan_general: document.getElementById('plan_tratamiento')?.value || '',
            tratamientos: []
        };

        for (let i = 1; i <= 7; i++) {
            const medicamento = document.getElementById(`trat_med${i}`)?.value || '';
            if (medicamento) {
                // Obtener el estado de administrado del input hidden
                const administrado = document.getElementById(`trat_administrado${i}`)?.value || '0';

                // INCLUIR trat_id si existe (para mantener el mismo ID al actualizar)
                const tratId = document.getElementById(`trat_id${i}`)?.value || '';

                datos.seccionN.tratamientos.push({
                    trat_id: tratId ? parseInt(tratId) : null,  // ← CLAVE: incluir el ID existente
                    medicamento: medicamento,
                    via: document.getElementById(`trat_via${i}`)?.value || '',
                    dosis: document.getElementById(`trat_dosis${i}`)?.value || '',
                    posologia: document.getElementById(`trat_posologia${i}`)?.value || '',
                    dias: document.getElementById(`trat_dias${i}`)?.value || '',
                    administrado: parseInt(administrado) || 0
                });
            }
        }

        return datos;

    } catch (error) {
        console.error('❌ Error recopilando datos de secciones E-N:', error);
        return {};
    }
}