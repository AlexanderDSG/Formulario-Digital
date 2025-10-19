// ========================================
// INTERFAZ DINÁMICA.JS - MANEJO COMPLETO DE LA UI
// ========================================

$(document).ready(function() {
    inicializarInterfazDinamica();
});

/**
 * Inicializar toda la interfaz dinámica
 */
function inicializarInterfazDinamica() {
    // 1. Crear elementos básicos de la interfaz
    crearElementosBasicos();

    // 2. Configurar eventos iniciales
    configurarEventosIniciales();

    // 3. Establecer estado inicial
    establecerEstadoInicial();

}

/**
 * Crear elementos básicos de la interfaz
 */
function crearElementosBasicos() {
    const contenedor = document.getElementById('contenedor-dinamico-js');
    if (!contenedor) {
        console.error('❌ No se encontró el contenedor dinámico');
        return;
    }

    // Limpiar contenedor
    contenedor.innerHTML = '';

    // 🔥 1. CREAR SECCIÓN DE FINALIZACIÓN (siempre visible)
    const seccionFinalizacion = crearSeccionFinalizacion();
    contenedor.appendChild(seccionFinalizacion);

    // 🔥 2. CREAR BOTONES FINALES (siempre visibles)
    const seccionBotones = crearSeccionBotones();
    contenedor.appendChild(seccionBotones);

    // 🔥 3. CREAR BOTONES DE PROCESO (inicialmente ocultos)
    const seccionGuardarProceso = crearSeccionGuardarProceso();
    seccionGuardarProceso.style.display = 'none'; // Ocultos por defecto
    contenedor.appendChild(seccionGuardarProceso);

    // 🔥 CORRECCIÓN: Crear sección de finalizar para especialistas normales (cuando O y P están ocultas)
    // Solo NO crear para enfermería
    if (!window.contextoEnfermeria) {
        const seccionContinuarEgreso = crearSeccionContinuarEgreso();
        seccionContinuarEgreso.style.display = 'none'; // Ocultos por defecto
        contenedor.appendChild(seccionContinuarEgreso);
    }

    // 🔥 5. CREAR MODAL DE OBSERVACIÓN
    const modalObservacion = crearModalObservacion();
    document.body.appendChild(modalObservacion);

}

/**
 * Crear sección de guardar proceso parcial
 */
function crearSeccionGuardarProceso() {
    const seccion = document.createElement('div');
    seccion.id = 'guardar-proceso-section';
    seccion.className = 'bg-yellow-50 border border-yellow-200 rounded-lg p-4 mt-4';

    seccion.innerHTML = `
        <div class="flex items-center justify-between">
            <div class="flex items-center">
                <i class="fas fa-save text-yellow-500 mr-2"></i>
                <div>
                    <span class="text-sm font-medium text-yellow-800">Cambio de turno</span>
                    <p class="text-xs text-yellow-700">Puede guardar el proceso parcial para continuar más tarde</p>
                </div>
            </div>
            <button type="button" id="btn-guardar-proceso"
                    class="bg-yellow-600 hover:bg-yellow-700 text-white px-4 py-2 rounded-lg flex items-center transition-colors">
                <i class="fas fa-save mr-2"></i>Guardar Proceso
            </button>
        </div>
    `;

    return seccion;
}

/**
 * Crear sección de continuar a egreso
 */
function crearSeccionContinuarEgreso() {
    const seccion = document.createElement('div');
    seccion.id = 'decision-especialidad';
    seccion.className = 'bg-white rounded-lg shadow-md mb-6 mt-4';

    seccion.innerHTML = `
        <div class="bg-blue-500 text-white px-6 py-4 rounded-t-lg">
            <h3 class="text-xl font-bold flex items-center">
                <i class="fas fa-user-md mr-3"></i>
                Finalizar Atención de Especialidad
            </h3>
        </div>
        <div class="p-6">
            <div class="bg-blue-50 border-l-4 border-blue-400 p-4 mb-4">
                <p class="text-blue-800">
                    <strong>Evaluación de especialidad completada.</strong>
                    ¿Desea proceder a completar el egreso del paciente?
                </p>
            </div>
            <div class="flex justify-center">
                <button type="button" id="btn-continuar-egreso"
                        class="bg-green-500 hover:bg-green-600 text-white px-8 py-4 rounded-lg font-semibold flex flex-col items-center justify-center">
                    <i class="fas fa-clipboard-check mb-2 text-2xl"></i>
                    <span>Continuar a Egreso</span>
                    <small class="text-green-100 mt-1">Completar secciones O y P</small>
                </button>
            </div>
        </div>
    `;

    return seccion;
}

/**
 * Crear sección de finalización
 */
function crearSeccionFinalizacion() {
    const seccion = document.createElement('div');
    seccion.id = 'seccion-finalizacion-especialidad';
    seccion.className = 'bg-white p-6 rounded-lg shadow-md mb-6 seccion-finalizacion-especialidad';
    seccion.style.display = 'none';

    seccion.innerHTML = `
        <div class="grid grid-cols-1 gap-4">
            <div class="bg-yellow-50 border border-yellow-200 rounded-md p-4">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <i class="fas fa-info-circle text-yellow-500"></i>
                    </div>
                    <div class="ml-3">
                        <h3 class="text-sm font-medium text-yellow-800">
                            Importante - Finalización de Atención
                        </h3>
                        <div class="mt-2 text-sm text-yellow-700">
                            <p>Al guardar este formulario:</p>
                            <ul class="list-disc pl-5 mt-1">
                                <li>Se completará la atención en esta especialidad</li>
                                <li>El paciente será dado de alta del área</li>
                                <li>Los datos quedarán registrados permanentemente</li>
                                <li>Esta acción no se puede deshacer</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    `;

    return seccion;
}

/**
 * Crear sección de botones finales
 */
function crearSeccionBotones() {
    const seccion = document.createElement('div');
    seccion.id = 'seccion-botones-finales';
    seccion.className = 'flex justify-center space-x-4 mt-8 mb-6 seccion-botones-finales';
    seccion.style.display = 'none';

    seccion.innerHTML = `
        <a href="${window.base_url}especialidades/lista"
           class="btn bg-gray-500 hover:bg-gray-600 text-white px-6 py-3 rounded-lg flex items-center">
            <i class="fas fa-arrow-left mr-2"></i> Volver a Especialidades
        </a>
        <button type="submit" id="btn-completar-atencion"
                class="btn bg-green-600 hover:bg-green-700 text-white px-6 py-3 rounded-lg flex items-center">
            <i class="fas fa-check-circle mr-2"></i> Completar Atención
        </button>
    `;

    return seccion;
}

/**
 * Crear modal de observación
 */
function crearModalObservacion() {
    const modal = document.createElement('div');
    modal.id = 'modalObservacion';
    modal.className = 'fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50';

    modal.innerHTML = `
        <div class="bg-white rounded-lg max-w-md w-full mx-4">
            <div class="bg-orange-600 text-white px-6 py-4 rounded-t-lg flex justify-between items-center">
                <h4 class="text-lg font-bold flex items-center">
                    <i class="fas fa-eye mr-2"></i>
                    Enviar a Observación
                </h4>
                <button type="button" class="text-white hover:text-gray-200" id="btn-cerrar-modal">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>
            <div class="p-6">
                <div class="bg-orange-50 border border-orange-200 text-orange-800 px-4 py-3 rounded-lg mb-4">
                    <i class="fas fa-info-circle mr-2"></i>
                    <strong>Esta acción enviará al paciente al área de Observación</strong>
                </div>
                <form id="form-enviar-observacion">
                    <div class="mb-4">
                        <label for="motivo_observacion" class="block text-sm font-medium text-gray-700 mb-2">
                            <i class="fas fa-comment mr-1"></i>
                            Motivo del envío a observación:
                        </label>
                        <textarea name="motivo_observacion" id="motivo_observacion" rows="4"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-orange-500"
                                placeholder="Describa el motivo por el cual envía al paciente a observación..."></textarea>
                        <div id="error-motivo" class="text-red-600 text-sm mt-1 hidden"></div>
                    </div>
                </form>
            </div>
            <div class="px-6 py-4 bg-gray-50 rounded-b-lg flex justify-end space-x-3">
                <button type="button" id="btn-cancelar-observacion"
                        class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-lg transition-colors flex items-center">
                    <i class="fas fa-times mr-2"></i> Cancelar
                </button>
                <button type="button" id="btn-confirmar-observacion"
                        class="bg-orange-600 hover:bg-orange-700 text-white px-4 py-2 rounded-lg transition-colors flex items-center">
                    <i class="fas fa-paper-plane mr-2"></i> Enviar a Observación
                </button>
            </div>
        </div>
    `;

    return modal;
}

/**
 * Configurar eventos iniciales
 */
function configurarEventosIniciales() {
    // 🔥 EVENTO: Guardar proceso parcial
    $(document).on('click', '#btn-guardar-proceso', function() {
        guardarProcesoParcialCompleto();
    });

    // 🔥 EVENTO: Continuar a egreso
    $(document).on('click', '#btn-continuar-egreso', function() {
        confirmarContinuarAEgreso();
    });

    // 🔥 EVENTO: Completar atención
    $(document).on('click', '#btn-completar-atencion', function() {
        // El evento submit del formulario se maneja automáticamente
    });

}

/**
 * Establecer estado inicial
 */
function establecerEstadoInicial() {

    const esModificacion = window.esModificacionEspecialista || false;
    const esContinuacion = window.esContinuacionProceso || false;
    const esEnfermeria = window.contextoEnfermeria || false;
    const ocultarOyP = window.ocultarSeccionesOyP || false;

    if (esEnfermeria) {
        // 👩‍⚕️ ENFERMERÍA - Mostrar solo secciones hasta N (ocultar O y P)
        // PRIORIDAD: Enfermería siempre ve solo A-N, sin importar si hay proceso guardado
        mostrarVistaEnfermeria();
    } else if (esContinuacion || esModificacion) {
        // 🔄 CONTINUANDO PROCESO o MODIFICACIÓN
        // Mostrar directamente secciones N, O, P sin botones
        mostrarSeccionesFinales();
    } else {
        // 🆕 EN ATENCIÓN (primera vez)
        // Mostrar botones de "Guardar Proceso" y "Continuar a Egreso"
        mostrarBotonesProceso();
    }
}

/**
 * Guardar proceso parcial completo
 */
async function guardarProcesoParcialCompleto() {
    // Validar campos requeridos del especialista
    const camposRequeridos = [
        { id: 'esp_primer_nombre_n', nombre: 'Primer Nombre del Especialista' },
        { id: 'esp_primer_apellido_n', nombre: 'Primer Apellido del Especialista' },
        { id: 'esp_documento_n', nombre: 'Documento del Especialista' },
        { id: 'esp_fecha_n', nombre: 'Fecha' },
        { id: 'esp_hora_n', nombre: 'Hora' }
    ];

    for (let campo of camposRequeridos) {
        const elemento = document.getElementById(campo.id);
        if (!elemento || !elemento.value.trim()) {
            Swal.fire({
                icon: 'warning',
                title: 'Campo requerido',
                text: `Por favor complete el campo: ${campo.nombre}`,
                confirmButtonText: 'Aceptar'
            }).then(() => {
                elemento?.focus();
            });
            return;
        }
    }

    const result = await Swal.fire({
        icon: 'question',
        title: 'Guardar Proceso Parcial',
        html: '✅ Se guardarán las secciones E-N completadas<br>' +
              '👥 Otro especialista podrá continuar la atención<br>' +
              '📝 Podrá volver a editar este proceso más tarde<br>' +
              '🔄 El estado cambiará a "EN_PROCESO"<br><br>' +
              '¿Desea continuar?',
        showCancelButton: true,
        confirmButtonText: 'Sí, guardar proceso',
        cancelButtonText: 'Cancelar'
    });

    if (!result.isConfirmed) return;

    const btn = document.getElementById('btn-guardar-proceso');
    const textoOriginal = btn.innerHTML;

    // Mostrar estado de carga
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Guardando proceso...';

    // Usar FormData para capturar TODO el formulario
    const form = document.getElementById('formEspecialidad');
    const formData = new FormData(form);

    // Agregar flag para indicar que es guardado parcial
    formData.append('accion', 'guardar_proceso_parcial');

    // Observaciones adicionales opcionales
    const observaciones = prompt('Observaciones del proceso (opcional):');
    if (observaciones !== null && observaciones.trim()) {
        formData.append('observaciones_proceso', observaciones.trim());
    }

    // Enviar petición AJAX
    fetch(window.base_url + 'especialidades/guardarProcesoParcial', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            Swal.fire({
                icon: 'success',
                title: 'Éxito',
                text: 'Proceso guardado exitosamente: ' + data.message,
                confirmButtonText: 'Aceptar'
            }).then(() => {
                window.location.href = data.redirect_url;
            });
        } else {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'Error al guardar: ' + data.error,
                confirmButtonText: 'Aceptar'
            });
        }
    })
    .catch(error => {
        console.error('Error:', error);
        Swal.fire({
            icon: 'error',
            title: 'Error de comunicación',
            text: 'Error de comunicación al guardar el proceso',
            confirmButtonText: 'Aceptar'
        });
    })
    .finally(() => {
        btn.disabled = false;
        btn.innerHTML = textoOriginal;
    });
}

/**
 * Confirmar continuar a egreso
 */
function confirmarContinuarAEgreso() {
    Swal.fire({
        icon: 'question',
        title: 'Continuar a egreso',
        html: `
            <div class="text-left">
                <p class="mb-3">Al continuar se realizarán los siguientes cambios:</p>
                <ul class="space-y-2 mb-3">
                    <li class="flex items-start">
                        <span class="text-green-500 mr-2">✅</span>
                        <span>Se mostrarán las secciones de egreso (O y P)</span>
                    </li>
                    <li class="flex items-start">
                        <span class="text-blue-500 mr-2">📝</span>
                        <span>Deberá completar los datos de egreso del paciente</span>
                    </li>
                    <li class="flex items-start">
                        <span class="text-orange-500 mr-2">⚠️</span>
                        <span>Esta acción finalizará la atención en esta especialidad</span>
                    </li>
                    <li class="flex items-start">
                        <span class="text-gray-500 mr-2">🙈</span>
                        <span>Se ocultará la información del especialista responsable</span>
                    </li>
                </ul>
                <p class="font-semibold">¿Está seguro de continuar?</p>
            </div>
        `,
        showCancelButton: true,
        confirmButtonText: 'Sí, continuar',
        cancelButtonText: 'Cancelar',
        confirmButtonColor: '#10b981',
        cancelButtonColor: '#6b7280',
        width: '600px'
    }).then((result) => {
        if (!result.isConfirmed) return;

        // Guardar estado
        sessionStorage.setItem('estadoFormularioEspecialidad', 'completo');

        // Mostrar secciones finales
        mostrarSeccionesFinales();
    });
}

/**
 * Mostrar interfaz de proceso parcial (para continuaciones)
 * NOTA: Ahora es igual a mostrarSeccionesFinales() ya que ambos casos muestran N, O, P
 */
function mostrarInterfaceProcesoParcial() {
    // 1. Mantener elementos de proceso parcial ocultos
    $('#guardar-proceso-section').hide();
    $('#decision-especialidad').hide();

    // 2. Mostrar sección del especialista responsable (la azul)
    $('.border-t-2.border-blue-500.bg-blue-50').show();

    // 3. Mostrar secciones O y P (disponibles para finalizar)
    $('.seccion-o, .seccion-p').show();

    // 4. Mostrar elementos de finalización
    $('#seccion-finalizacion-especialidad').show();
    $('#seccion-botones-finales').show();

}

/**
 * Mostrar botones de proceso parcial (solo si es necesario en casos específicos)
 */
function mostrarBotonesProceso() {
    $('#guardar-proceso-section').show();

    // 🔥 CORRECCIÓN: Mostrar botón de finalizar para especialistas (no enfermería)
    if (!window.contextoEnfermeria) {
        $('#decision-especialidad').show();
    }

}

/**
 * Mostrar secciones finales (para primera vez y modificaciones)
 */
function mostrarSeccionesFinales() {

    $('#guardar-proceso-section').hide();
    $('#decision-especialidad').hide();

    // 2. Mostrar sección del especialista responsable (la azul)
    $('.border-t-2.border-blue-500.bg-blue-50').show();

    // 3. Mostrar secciones O y P
    $('.seccion-o, .seccion-p').show();

    // 4. Mostrar elementos de finalización
    $('#seccion-finalizacion-especialidad').show();
    $('#seccion-botones-finales').show();

    // 5. Scroll hacia las secciones mostradas
    setTimeout(() => {
        const primeraSeccion = document.querySelector('.seccion-o');
        if (primeraSeccion) {
            primeraSeccion.scrollIntoView({
                behavior: 'smooth',
                block: 'start'
            });
        }
    }, 300);

}

/**
 * Ocultar secciones finales
 */
function ocultarSeccionesFinales() {
    // 1. Mostrar elementos de proceso parcial
    $('#guardar-proceso-section').show();

    // 🔥 CORRECCIÓN: Mostrar botón de finalizar para especialistas (no enfermería)
    if (!window.contextoEnfermeria) {
        $('#decision-especialidad').show();
    }

    // 2. Mostrar sección del especialista responsable (la azul)
    $('.border-t-2.border-blue-500.bg-blue-50').show();

    // 3. Ocultar secciones O y P
    $('.seccion-o, .seccion-p').hide();

    // 4. Ocultar elementos de finalización
    $('#seccion-finalizacion-especialidad').hide();
    $('#seccion-botones-finales').hide();

}

/**
 * Mostrar botones de proceso (para "En Atención")
 */
function mostrarBotonesProceso() {

    // 1. Mostrar botones de proceso
    $('#guardar-proceso-section').show();
    $('#decision-especialidad').show();

    // 2. Mostrar sección del especialista responsable (la azul)
    $('.border-t-2.border-blue-500.bg-blue-50').show();

    // 3. Ocultar secciones O y P inicialmente
    $('.seccion-o, .seccion-p').hide();

    // 4. Ocultar elementos de finalización
    $('#seccion-finalizacion-especialidad').hide();
    $('#seccion-botones-finales').hide();

}

/**
 * Mostrar vista de enfermería (solo secciones A-N)
 */
function mostrarVistaEnfermeria() {

    // 1. Ocultar elementos de proceso parcial

    $('#guardar-proceso-section').hide();
    $('#decision-especialidad').hide();

    // 2. Mostrar sección del especialista responsable (la azul)
    $('.border-t-2.border-blue-500.bg-blue-50').show();

    // 3. IMPORTANTE: Ocultar secciones O y P para enfermería
    $('.seccion-o, .seccion-p').hide();

    // 4. Ocultar elementos de finalización
    $('#seccion-finalizacion-especialidad').hide();
    $('#seccion-botones-finales').hide();

    // 5. Mostrar mensaje informativo para enfermería
    const contenedorDinamico = document.getElementById('contenedor-dinamico-js');
    if (contenedorDinamico) {
        const mensajeEnfermeria = document.createElement('div');
        mensajeEnfermeria.className = 'bg-blue-50 border border-blue-200 rounded-lg p-4 mt-4';
        mensajeEnfermeria.innerHTML = `
            <div class="flex items-center">
                <i class="fas fa-user-nurse text-blue-500 mr-3 text-xl"></i>
                <div>
                    <h4 class="text-blue-800 font-bold mb-1">Vista de Enfermería</h4>
                    <p class="text-blue-700 text-sm">
                        Puede visualizar las secciones A-N completadas por el especialista.
                        Las secciones de egreso (O y P) no están disponibles para enfermería.
                    </p>
                </div>
            </div>
        `;
        contenedorDinamico.appendChild(mensajeEnfermeria);
    }

}

// Funciones globales para compatibilidad con otros scripts
window.mostrarSeccionesFinales = mostrarSeccionesFinales;
window.ocultarSeccionesFinales = ocultarSeccionesFinales;
window.mostrarBotonesProceso = mostrarBotonesProceso;
window.mostrarVistaEnfermeria = mostrarVistaEnfermeria;