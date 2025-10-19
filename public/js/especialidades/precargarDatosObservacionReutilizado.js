// ========================================
// PRECARGAR DATOS OBSERVACION - CORREGIDO
// ========================================

document.addEventListener('DOMContentLoaded', function() {
    const esObservacion = window.contextoObservacion === true;
    const esObservacionEmergencia = window.esObservacionEmergencia === true;
    const especialidadCodigo5 = window.esp_codigo === 5 || window.especialidad_codigo === 5;

    if (!esObservacion && !esObservacionEmergencia && !especialidadCodigo5) {
        return;
    }

    let datosParaPrecargar = obtenerDatosParaObservacion();

    if (datosParaPrecargar && Object.keys(datosParaPrecargar).length > 0) {
        ejecutarPrecargaObservacion(datosParaPrecargar);

        setTimeout(() => {
            mostrarBannerObservacion();
        }, 1500);
    }

    // SIEMPRE intentar cargar firma y sello en observación
    setTimeout(() => {
        cargarFirmaYSelloObservacion();
    }, 1500);
});

/**
 * Obtener datos para precargar en observación desde múltiples fuentes
 */
function obtenerDatosParaObservacion() {
    let datos = null;
    
    // 1. Datos específicos de observación
    if (typeof window.datosObservacionGuardados !== 'undefined' && window.datosObservacionGuardados) {
        // CORRECCIÓN: Verificar si hay datos reales
        if (Object.keys(window.datosObservacionGuardados).length > 0) {
            datos = window.datosObservacionGuardados;
        }
    }
    
    // 2. Datos del formulario guardado de especialista (para pacientes enviados)
    if (!datos && typeof window.datosFormularioGuardadoEspecialista !== 'undefined' && 
        window.datosFormularioGuardadoEspecialista) {
        
        if (Object.keys(window.datosFormularioGuardadoEspecialista).length > 0) {
            datos = window.datosFormularioGuardadoEspecialista;
        }
    }
    
    // 3. SessionStorage
    if (!datos) {
        const datosStorage = sessionStorage.getItem('datosObservacion') || 
                           sessionStorage.getItem('datosProcesoParcialEspecialidad');
        if (datosStorage) {
            try {
                const parsedData = JSON.parse(datosStorage);
                if (parsedData && Object.keys(parsedData).length > 0) {
                    datos = parsedData;
                }
            } catch (e) {
                console.error('Error parseando datos de sessionStorage:', e);
            }
        }
    }
    
    return datos;
}

/**
 * Ejecutar la precarga completa con validaciones mejoradas
 */
function ejecutarPrecargaObservacion(datos) {
    
    // 1. Bloquear secciones básicas (reutilizar función existente)
    setTimeout(() => {
        if (typeof bloquearSeccionesBasicas === 'function') {
            bloquearSeccionesBasicas();
        } else {
            // Fallback si la función no está disponible
            bloquearSeccionesBasicasObservacion();
        }
    }, 100);

    // 2. Precargar datos básicos (reutilizar función existente)
    setTimeout(() => {
        if (typeof precargarDatosBasicos === 'function') {
            precargarDatosBasicos();
        } else {
            // Fallback
            precargarDatosBasicosObservacionFallback();
        }
    }, 300);

    // 3. Precargar secciones médicas E-N (reutilizar funciones existentes)
    const secciones = [
        { letra: 'E', funcion: 'precargarSeccionE', delay: 500 },
        { letra: 'F', funcion: 'precargarSeccionF', delay: 600 },
        { letra: 'H', funcion: 'precargarSeccionH', delay: 700 },
        { letra: 'I', funcion: 'precargarSeccionI', delay: 800 },
        { letra: 'J', funcion: 'precargarSeccionJ', delay: 900 },
        { letra: 'K', funcion: 'precargarSeccionK', delay: 1000 },
        { letra: 'L', funcion: 'precargarSeccionL', delay: 1100 },
        { letra: 'M', funcion: 'precargarSeccionM', delay: 1200 },
        { letra: 'N', funcion: 'precargarSeccionN', delay: 1300 },
        { letra: 'O', funcion: 'precargarSeccionO', delay: 1400 }
    ];
    
    secciones.forEach(seccion => {
        setTimeout(() => {
            // CORRECCIÓN: Manejar tanto estructura de proceso parcial como datos directos
            let datoSeccion = datos[`seccion${seccion.letra}`];
            
            // Si no encontramos con la estructura de proceso parcial, buscar directamente
            if (!datoSeccion && datos[seccion.letra.toLowerCase()]) {
                datoSeccion = datos[seccion.letra.toLowerCase()];
            }
            
            if (datoSeccion && (Array.isArray(datoSeccion) ? datoSeccion.length > 0 : Object.keys(datoSeccion).length > 0)) {
                
                // Intentar usar la función existente
                if (typeof window[seccion.funcion] === 'function') {
                    try {
                        window[seccion.funcion](datoSeccion);
                    } catch (error) {
                        console.error(`❌ Error precargando Sección ${seccion.letra}:`, error);
                        console.error('Datos que causaron el error:', datoSeccion);
                    }
                }
            }
        }, seccion.delay);
    });
}

/**
 * Fallback mejorado para bloquear secciones básicas
 */
function bloquearSeccionesBasicasObservacion() {
    
    const selectorsToBlock = [
        // Sección A
        'input[name="estab_institucion"]',
        'input[name="estab_unicode"]', 
        'input[name="estab_nombre"]',
        
        // Sección B
        'input[name="pac_apellido1"]',
        'input[name="pac_apellido2"]',
        'input[name="pac_nombre1"]',
        'input[name="pac_nombre2"]',
        'select[name="pac_tipo_documento"]',
        'select[name="pac_sexo"]',
        
        // Sección G
        'input[name="cv_presion_arterial"]',
        'input[name="cv_pulso"]',
        'input[name="cv_frec_resp"]',
        'select[name="cv_triaje_color"]'
    ];

    let elementosBloquedos = 0;
    selectorsToBlock.forEach(selector => {
        const elements = document.querySelectorAll(selector);
        elements.forEach(element => {
            if (element.type === 'checkbox' || element.type === 'radio') {
                element.disabled = true;
            } else {
                element.setAttribute('readonly', true);
                if (element.tagName === 'SELECT') {
                    element.disabled = true;
                }
            }
            
            element.style.backgroundColor = '#f8f9fa';
            element.style.borderColor = '#dee2e6';
            element.style.color = '#6c757d';
            element.style.cursor = 'not-allowed';
            element.title = 'Campo bloqueado - Datos de especialidad';
            elementosBloquedos++;
        });
    });
    
}

/**
 * Fallback mejorado para precargar datos básicos
 */
function precargarDatosBasicosObservacionFallback() {
    
    // Precargar datos del paciente si están disponibles
    if (typeof window.datosPacienteEspecialidades !== 'undefined' && window.datosPacienteEspecialidades) {
        if (typeof precargarDatosPaciente === 'function') {
            precargarDatosPaciente(window.datosPacienteEspecialidades);
        }
    }
    
    // Precargar constantes vitales si están disponibles
    if (typeof window.datosConstantesVitalesEspecialidades !== 'undefined' && window.datosConstantesVitalesEspecialidades) {
        if (typeof precargarYBloquearConstantesVitales === 'function') {
            precargarYBloquearConstantesVitales(window.datosConstantesVitalesEspecialidades);
        }
    }
}

/**
 * Banner informativo mejorado para observación
 */
function mostrarBannerObservacion() {
    // Verificar si ya existe un banner
    if (document.querySelector('.banner-observacion-datos')) {
        return;
    }
    
    const banner = document.createElement('div');
    banner.className = 'banner-observacion-datos bg-blue-50 border border-blue-200 rounded-lg p-4 mb-6';
    banner.innerHTML = `
        <div class="flex items-center">
            <div class="flex-shrink-0">
                <i class="fas fa-eye text-blue-500 text-xl"></i>
            </div>
            <div class="ml-3">
                <h3 class="text-sm font-medium text-blue-800">
                    📊 Datos Precargados de Especialidad - Observación
                </h3>
                <div class="mt-2 text-sm text-blue-700">
                    <p>Se han cargado automáticamente los datos guardados de la especialidad de origen.</p>
                    <p>En observación puede revisar, modificar y completar la evaluación según sea necesario.</p>
                    <p class="mt-1 text-blue-600">
                        <i class="fas fa-info-circle mr-1"></i>
                        Las secciones A, B, C, D y G están bloqueadas (datos previos).
                    </p>
                </div>
            </div>
        </div>
    `;
    
    // Insertar al inicio del formulario
    const form = document.getElementById('formEspecialidad') || document.querySelector('form');
    if (form) {
        form.insertBefore(banner, form.firstChild);
    } else {
        // Si no hay formulario, insertar en el contenedor principal
        const container = document.querySelector('.container') || document.querySelector('.max-w-7xl');
        if (container) {
            container.insertBefore(banner, container.firstChild);
        }
    }
}

/**
 * Mostrar notificación específica para observación
 */
function mostrarNotificacionObservacion(mensaje, tipo = 'info') {
    const notificacion = document.createElement('div');
    notificacion.className = `fixed top-4 right-4 z-50 px-4 py-3 rounded-lg shadow-lg transition-all duration-300`;
    
    const colores = {
        'success': 'bg-green-500 text-white',
        'error': 'bg-red-500 text-white',
        'info': 'bg-blue-500 text-white',
        'warning': 'bg-yellow-500 text-black'
    };
    
    notificacion.className += ` ${colores[tipo] || colores.info}`;
    notificacion.innerHTML = `
        <div class="flex items-center">
            <i class="fas fa-${tipo === 'success' ? 'check-circle' : tipo === 'error' ? 'exclamation-circle' : 'info-circle'} mr-2"></i>
            <span>${mensaje}</span>
        </div>
    `;
    
    document.body.appendChild(notificacion);
    
    setTimeout(() => {
        if (notificacion.parentNode) {
            notificacion.parentNode.removeChild(notificacion);
        }
    }, 4000);
}

// Exportar funciones para uso externo
window.precargarObservacion = {
    obtenerDatos: obtenerDatosParaObservacion,
    ejecutarPrecarga: ejecutarPrecargaObservacion,
    mostrarBanner: mostrarBannerObservacion,
    mostrarNotificacion: mostrarNotificacionObservacion
};

// ========================================
// FUNCIONES DE PRECARGA ESPECÍFICAS
// ========================================

/**
 * Precargar Sección E - Antecedentes
 */
function precargarSeccionE(datos) {
    
    try {
        // Checkbox "No aplica"
        const noAplicaCheckbox = document.getElementById('ant_no_aplica');
        if (noAplicaCheckbox) {
            noAplicaCheckbox.checked = datos.no_aplica || false;
            
            // Trigger change event para activar/desactivar campos
            const event = new Event('change', { bubbles: true });
            noAplicaCheckbox.dispatchEvent(event);
        }
        
        if (!datos.no_aplica) {
            // Precargar antecedentes seleccionados
            if (datos.antecedentes && Array.isArray(datos.antecedentes)) {
                datos.antecedentes.forEach(antecedente => {
                    const checkbox = document.querySelector(`input[name="antecedentes[]"][value="${antecedente}"]`);
                    if (checkbox) {
                        checkbox.checked = true;
                    }
                });
            }
            
            // Precargar descripción
            const descripcionField = document.getElementById('ant_descripcion');
            if (descripcionField && datos.descripcion) {
                descripcionField.value = datos.descripcion;
            }
        }
        
    } catch (error) {
        console.error('❌ Error precargando Sección E:', error);
    }
}

/**
 * Precargar Sección F - Problema Actual
 */
function precargarSeccionF(datos) {
    
    try {
        const descripcionField = document.getElementById('ep_descripcion_actual');
        if (descripcionField && datos.descripcion) {
            descripcionField.value = datos.descripcion;
        }
        
    } catch (error) {
        console.error('❌ Error precargando Sección F:', error);
    }
}

/**
 * Precargar Sección H - Examen Físico
 */
function precargarSeccionH(datos) {
    
    try {
        // Precargar zonas seleccionadas
        if (datos.zonas && Array.isArray(datos.zonas)) {
            datos.zonas.forEach(zona => {
                const checkbox = document.querySelector(`input[name="zonas_examen_fisico[]"][value="${zona}"]`);
                if (checkbox) {
                    checkbox.checked = true;
                }
            });
        }
        
        // Precargar descripción
        const descripcionField = document.getElementById('ef_descripcion');
        if (descripcionField && datos.descripcion) {
            descripcionField.value = datos.descripcion;
        }
        
    } catch (error) {
        console.error('❌ Error precargando Sección H:', error);
    }
}

/**
 * Precargar Sección I - Examen de Trauma
 */
function precargarSeccionI(datos) {
    
    try {
        const descripcionField = document.getElementById('eft_descripcion');
        if (descripcionField && datos.descripcion) {
            descripcionField.value = datos.descripcion;
        }
        
    } catch (error) {
        console.error('❌ Error precargando Sección I:', error);
    }
}

/**
 * Precargar Sección J - Con valores exactos de BD
 */
function precargarSeccionJ(datos) {

    try {
        // Verificar valor numérico 1 = no aplica, 0 = aplica
        const noAplica = datos.no_aplica == 1 || datos.no_aplica === '1' || datos.no_aplica === true;

        // Checkbox "No aplica"
        const noAplicaCheckbox = document.getElementById('emb_no_aplica');
        if (noAplicaCheckbox) {
            noAplicaCheckbox.checked = noAplica;

            // Trigger change event para activar/desactivar campos
            const event = new Event('change', { bubbles: true });
            noAplicaCheckbox.dispatchEvent(event);

        } else {
            console.warn('⚠️ No se encontró checkbox emb_no_aplica');
        }

        if (!noAplica) {
            // MAPEO COMPLETO: campos de texto
            const camposEmbarazo = {
                'emb_gestas': 'gestas',
                'emb_partos': 'partos', 
                'emb_abortos': 'abortos',
                'emb_cesareas': 'cesareas',
                'emb_fum': 'fum',
                'emb_semanas_gestacion': 'semanas_gestacion',
                'emb_fcf': 'fcf',
                'emb_tiempo_ruptura': 'tiempo_ruptura',
                'emb_afu': 'afu',
                'emb_presentacion': 'presentacion',
                'emb_dilatacion': 'dilatacion',
                'emb_borramiento': 'borramiento',
                'emb_plano': 'plano',
                'emb_score_mama': 'score_mama',
                'emb_observaciones': 'observaciones'
            };
            
            // CAMPOS DE RADIO BUTTONS (BD guarda "Si"/"No")
            const camposRadio = {
                'emb_movimiento_fetal': 'movimiento_fetal',
                'emb_ruptura_membranas': 'ruptura_membranas', 
                'emb_sangrado_vaginal': 'sangrado_vaginal',
                'emb_contracciones': 'contracciones',
                'emb_pelvis_viable': 'pelvis_viable'
            };
            
            // Precargar campos de texto
            Object.entries(camposEmbarazo).forEach(([fieldId, dataKey]) => {
                const field = document.getElementById(fieldId);
                if (field && datos[dataKey]) {
                    // VALIDACIÓN ESPECIAL PARA FECHAS
                    if (fieldId === 'emb_fum' && field.type === 'date') {
                        // Verificar que la fecha no sea inválida (0000-00-00)
                        if (datos[dataKey] && datos[dataKey] !== '0000-00-00' && datos[dataKey] !== '00-00-0000') {
                            const fechaLimpia = datos[dataKey].toString().trim();
                            // Si viene con formato datetime, extraer solo la fecha
                            const soloFecha = fechaLimpia.includes(' ') ? fechaLimpia.split(' ')[0] : fechaLimpia;

                            // Verificar que la fecha sea válida
                            const fecha = new Date(soloFecha);
                            if (!isNaN(fecha.getTime()) && fecha.getFullYear() > 1900) {
                                field.value = soloFecha;
                            }
                            // Si la fecha es inválida, no asignar valor (campo queda vacío)
                        }
                        // Si es 0000-00-00, no asignar valor (campo queda vacío)
                    } else {
                        // Para campos que no son fechas, asignar normalmente
                        field.value = datos[dataKey];
                    }
                }
            });
            
            // PRECARGAR RADIO BUTTONS
            Object.entries(camposRadio).forEach(([fieldName, dataKey]) => {
                const valor = datos[dataKey];
                if (valor) {
                    
                    // Buscar el radio button con el valor exacto de la BD
                    const radioButton = document.querySelector(`input[name="${fieldName}"][value="${valor}"]`);
                    if (radioButton) {
                        radioButton.checked = true;
                    } else {
                        console.warn(`⚠️ No se encontró radio button para ${fieldName} con valor "${valor}"`);
                                                
                        // INTENTO ALTERNATIVO: Buscar con valores comunes
                        let radioAlternativo = null;
                        if (valor.toLowerCase() === 'si' || valor === '1' || valor === 'true') {
                            radioAlternativo = document.querySelector(`input[name="${fieldName}"][value="Si"]`) ||
                                             document.querySelector(`input[name="${fieldName}"][value="si"]`) ||
                                             document.querySelector(`input[name="${fieldName}"][value="1"]`) ||
                                             document.querySelector(`input[name="${fieldName}"][value="true"]`);
                        } else if (valor.toLowerCase() === 'no' || valor === '0' || valor === 'false') {
                            radioAlternativo = document.querySelector(`input[name="${fieldName}"][value="No"]`) ||
                                             document.querySelector(`input[name="${fieldName}"][value="no"]`) ||
                                             document.querySelector(`input[name="${fieldName}"][value="0"]`) ||
                                             document.querySelector(`input[name="${fieldName}"][value="false"]`);
                        }
                        
                        if (radioAlternativo) {
                            radioAlternativo.checked = true;
                        }
                    }
                }
            });
        }
        
    } catch (error) {
        console.error('❌ Error precargando Sección J:', error);
    }
}

/**
 * Precargar Sección K - Exámenes Complementarios
 */
function precargarSeccionK(datos) {
    
    try {
        // Checkbox "No aplica"
        const noAplicaCheckbox = document.getElementById('exc_no_aplica');
        if (noAplicaCheckbox) {
            noAplicaCheckbox.checked = datos.no_aplica || false;
            
            // Trigger change event
            const event = new Event('change', { bubbles: true });
            noAplicaCheckbox.dispatchEvent(event);
        }
        
        if (!datos.no_aplica) {
            // Precargar tipos de exámenes
            if (datos.tipos && Array.isArray(datos.tipos)) {
                datos.tipos.forEach(tipo => {
                    const checkbox = document.querySelector(`input[name="tipos_examenes[]"][value="${tipo}"]`);
                    if (checkbox) {
                        checkbox.checked = true;
                    }
                });
            }
            
            // Precargar observaciones
            const observacionesField = document.getElementById('exc_observaciones');
            if (observacionesField && datos.observaciones) {
                observacionesField.value = datos.observaciones;
            }
        }
        
    } catch (error) {
        console.error('❌ Error precargando Sección K:', error);
    }
}

/**
 * Precargar Sección L - Diagnósticos Presuntivos
 */
function precargarSeccionL(datos) {
    
    try {
        // Precargar hasta 3 diagnósticos
        for (let i = 1; i <= 3; i++) {
            const diagnostico = datos[`diagnostico${i}`];
            if (diagnostico) {
                const descripcionField = document.getElementById(`diag_pres_desc${i}`);
                const cieField = document.getElementById(`diag_pres_cie${i}`);
                
                if (descripcionField && diagnostico.descripcion) {
                    descripcionField.value = diagnostico.descripcion;
                }
                
                if (cieField && diagnostico.cie) {
                    cieField.value = diagnostico.cie;
                }
            }
        }
        
    } catch (error) {
        console.error('❌ Error precargando Sección L:', error);
    }
}

/**
 * Precargar Sección M - Diagnósticos Definitivos
 */
function precargarSeccionM(datos) {
    
    try {
        // Precargar hasta 3 diagnósticos
        for (let i = 1; i <= 3; i++) {
            const diagnostico = datos[`diagnostico${i}`];
            if (diagnostico) {
                const descripcionField = document.getElementById(`diag_def_desc${i}`);
                const cieField = document.getElementById(`diag_def_cie${i}`);
                
                if (descripcionField && diagnostico.descripcion) {
                    descripcionField.value = diagnostico.descripcion;
                }
                
                if (cieField && diagnostico.cie) {
                    cieField.value = diagnostico.cie;
                }
            }
        }
        
    } catch (error) {
        console.error('❌ Error precargando Sección M:', error);
    }
}

/**
 * Precargar Sección N - Tratamiento
 */
function precargarSeccionN(datos) {
    
    try {
        // Precargar plan general
        const planField = document.getElementById('plan_tratamiento');
        if (planField && datos.plan_general) {
            planField.value = datos.plan_general;
        }
        
        // Precargar tratamientos específicos (hasta 7)
        if (datos.tratamientos && Array.isArray(datos.tratamientos)) {
            datos.tratamientos.forEach((tratamiento, index) => {
                if (index < 7) { // Máximo 7 tratamientos
                    const i = index + 1;
                    
                    const medicamentoField = document.getElementById(`trat_med${i}`);
                    const viaField = document.getElementById(`trat_via${i}`);
                    const dosisField = document.getElementById(`trat_dosis${i}`);
                    const posologiaField = document.getElementById(`trat_posologia${i}`);
                    const diasField = document.getElementById(`trat_dias${i}`);
                    
                    if (medicamentoField && tratamiento.medicamento) {
                        medicamentoField.value = tratamiento.medicamento;
                    }
                    if (viaField && tratamiento.via) {
                        viaField.value = tratamiento.via;
                    }
                    if (dosisField && tratamiento.dosis) {
                        dosisField.value = tratamiento.dosis;
                    }
                    if (posologiaField && tratamiento.posologia) {
                        posologiaField.value = tratamiento.posologia;
                    }
                    if (diasField && tratamiento.dias) {
                        diasField.value = tratamiento.dias;
                    }

                    // ID del tratamiento (para mantener IDs consistentes)
                    const tratIdField = document.querySelector(`input[name="trat_id${i}"]`);
                    if (tratIdField && tratamiento.trat_id) {
                        tratIdField.value = tratamiento.trat_id;
                    }

                    // AGREGADO: Estado de administrado
                    const administradoField = document.querySelector(`input[name="trat_administrado${i}"]`);
                    const administradoButton = document.querySelector(`#btn_administrado${i}`);
                    if (administradoField && administradoButton) {
                        // Configurar el estado según la base de datos
                        if (tratamiento.administrado == 1) {
                            // Marcar como administrado
                            administradoField.value = '1';
                            administradoButton.classList.remove('bg-green-100', 'hover:bg-green-200', 'text-green-600', 'hover:text-green-700');
                            administradoButton.classList.add('bg-green-600', 'hover:bg-green-700', 'text-white');
                            administradoButton.setAttribute('title', 'Administrado');
                            // Cambiar icono a check relleno
                            const svg = administradoButton.querySelector('svg path');
                            if (svg) svg.setAttribute('d', 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z');
                        } else {
                            // Desmarcar como no administrado
                            administradoField.value = '0';
                            administradoButton.classList.remove('bg-green-600', 'hover:bg-green-700', 'text-white');
                            administradoButton.classList.add('bg-green-100', 'hover:bg-green-200', 'text-green-600', 'hover:text-green-700');
                            administradoButton.setAttribute('title', 'Marcar como administrado');
                            // Cambiar icono a check simple
                            const svg = administradoButton.querySelector('svg path');
                            if (svg) svg.setAttribute('d', 'M5 13l4 4L19 7');
                        }
                    }
                }
            });
        }

        // CARGAR FIRMA Y SELLO DEL ESPECIALISTA
        setTimeout(() => {
            cargarFirmaYSelloObservacion();
        }, 200);

    } catch (error) {
        console.error('❌ Error precargando Sección N:', error);
    }
}

function precargarSeccionO(datos) {

    try {
        // Manejar múltiples checkboxes seleccionados

        // Estados de egreso - FILTRAR DUPLICADOS
        if (datos.estados_egreso && Array.isArray(datos.estados_egreso)) {
            const estadosUnicos = [...new Set(datos.estados_egreso)];

            estadosUnicos.forEach(codigo => {
                const checkbox = document.querySelector(`input[name="estados_egreso[]"][value="${codigo}"]`);
                if (checkbox) checkbox.checked = true;
            });
        }

        // Modalidades de egreso - FILTRAR DUPLICADOS
        if (datos.modalidades_egreso && Array.isArray(datos.modalidades_egreso)) {
            // Crear un Set para eliminar duplicados
            const modalidadesUnicas = [...new Set(datos.modalidades_egreso)];

            modalidadesUnicas.forEach(codigo => {
                const checkbox = document.querySelector(`input[name="modalidades_egreso[]"][value="${codigo}"]`);
                if (checkbox) checkbox.checked = true;
            });
        }

        // Tipos de egreso - FILTRAR DUPLICADOS
        if (datos.tipos_egreso && Array.isArray(datos.tipos_egreso)) {
            const tiposUnicos = [...new Set(datos.tipos_egreso)];

            tiposUnicos.forEach(codigo => {
                const checkbox = document.querySelector(`input[name="tipos_egreso[]"][value="${codigo}"]`);
                if (checkbox) checkbox.checked = true;
            });
        }

        // Si fue enviado a observación, marcar y deshabilitar
        if (datos.egr_observacion_emergencia == 1) {
            // Deshabilitar modalidad de observación
            const observacionCheckbox = document.querySelector(`input[name="modalidades_egreso[]"][value="3"]`);
            if (observacionCheckbox) {
                observacionCheckbox.checked = true;
                observacionCheckbox.disabled = true;
                // Agregar indicador visual
                const label = observacionCheckbox.closest('td');
                if (label) {
                    label.style.backgroundColor = '#fef3c7';
                    label.title = 'Marcado automáticamente - Paciente enviado a observación';
                }
            }

            // BLOQUEAR CHECKBOX DE OBSERVACIÓN DE EMERGENCIA
            const emergenciaCheckbox = document.getElementById('egreso_observacion_emergencia');
            if (emergenciaCheckbox) {
                emergenciaCheckbox.checked = true;
                emergenciaCheckbox.disabled = true;

                // AGREGAR CAMPO HIDDEN PARA QUE SE ENVÍE EL VALOR
                // Los inputs disabled no se envían con el formulario
                // name="modalidades_egreso[]" value="3" = Observación de emergencia
                let hiddenInput = document.getElementById('egreso_observacion_emergencia_hidden');
                if (!hiddenInput) {
                    hiddenInput = document.createElement('input');
                    hiddenInput.type = 'hidden';
                    hiddenInput.id = 'egreso_observacion_emergencia_hidden';
                    hiddenInput.name = 'modalidades_egreso[]';
                    hiddenInput.value = '3';
                    emergenciaCheckbox.parentElement.appendChild(hiddenInput);
                } else {
                    hiddenInput.value = '3';
                }

                // Agregar indicador visual al contenedor
                const contenedor = emergenciaCheckbox.closest('tr, td, div');
                if (contenedor) {
                    contenedor.style.backgroundColor = '#fef3c7';
                    contenedor.style.opacity = '0.7';
                    emergenciaCheckbox.title = 'Paciente ya enviado a observación - No se puede modificar';
                }

                // Agregar texto informativo
                const label = emergenciaCheckbox.nextElementSibling || emergenciaCheckbox.parentElement;
                if (label) {
                    const infoSpan = document.createElement('span');
                    infoSpan.className = 'text-xs text-amber-600 font-medium ml-2';
                    infoSpan.textContent = '(Ya enviado a observación)';
                    label.appendChild(infoSpan);
                }
            }
        }

        // Otros campos
        if (datos.egr_establecimiento) {
            const establecimiento = document.getElementById('egreso_establecimiento');
            if (establecimiento) establecimiento.value = datos.egr_establecimiento;
        }

        if (datos.egr_observaciones) {
            const observaciones = document.getElementById('egreso_observacion');
            if (observaciones) observaciones.value = datos.egr_observaciones;
        }

        if (datos.egr_dias_reposo) {
            const diasReposo = document.getElementById('egreso_dias_reposo');
            if (diasReposo) diasReposo.value = datos.egr_dias_reposo;
        }

    } catch (error) {
        console.error('❌ Error precargando Sección O:', error);
    }
}

// Registrar las funciones globalmente
/**
 * FUNCIÓN: Cargar firma y sello del especialista en Sección N (Observación)
 */
function cargarFirmaYSelloObservacion() {
    let medicoData = null;

    if (typeof window.medico_que_envio_observacion !== 'undefined' && window.medico_que_envio_observacion) {
        medicoData = window.medico_que_envio_observacion;
    } else if (typeof window.medico_que_guardo_especialidad !== 'undefined' && window.medico_que_guardo_especialidad) {
        medicoData = window.medico_que_guardo_especialidad;
    }

    if (!medicoData) {
        return;
    }

    const firmaUrl = medicoData.firma_url;
    const selloUrl = medicoData.sello_url;

    if (firmaUrl && firmaUrl.trim() !== '') {
        manejarImagenObservacion('esp_firma_n', firmaUrl, true, 'Firma');
    }

    if (selloUrl && selloUrl.trim() !== '') {
        manejarImagenObservacion('esp_sello_n', selloUrl, true, 'Sello');
    }
}

/**
 * FUNCIÓN: Cargar firma y sello cuando se marca checkbox de observación
 */
function cargarFirmaYSelloParaObservacion() {
    // Usar el médico que guardó el proceso (mismo que modificación)
    let medicoData = window.medico_que_guardo_proceso;

    if (!medicoData) {
        return;
    }

    const firmaUrl = medicoData.firma_url;
    const selloUrl = medicoData.sello_url;

    if (firmaUrl && firmaUrl.trim() !== '') {
        manejarImagenObservacion('esp_firma_n', firmaUrl, true, 'Firma');
    }

    if (selloUrl && selloUrl.trim() !== '') {
        manejarImagenObservacion('esp_sello_n', selloUrl, true, 'Sello');
    }
}

// Exponer función globalmente
window.cargarFirmaYSelloParaObservacion = cargarFirmaYSelloParaObservacion;
/**
 * FUNCIÓN: Manejar imagen profesional (firma/sello) para Observación
 */
function manejarImagenObservacion(fieldName, imagenBase64, existe, titulo) {
    const inputField = document.querySelector(`input[name="${fieldName}"]`) || document.getElementById(fieldName);

    if (!inputField) {
        return;
    }

    const container = inputField.parentElement;
    const fieldType = fieldName.replace('esp_', '').replace('prof_', '');
    const existingPreviewDiv = document.getElementById(`${fieldType}-n-preview`) || document.getElementById(`${fieldType}-preview`);

    if (existe && imagenBase64) {
        inputField.style.display = 'none';

        let previewDiv = existingPreviewDiv;
        if (!previewDiv) {
            previewDiv = document.createElement('div');
            previewDiv.className = 'custom-image-preview mt-2';
            previewDiv.id = `${fieldType}-n-preview`;
        }

        const img = document.createElement('img');
        let imgSrc = '';

        if (typeof imagenBase64 === 'string' && imagenBase64.trim()) {
            let imageData = imagenBase64.trim();

            if (imageData.startsWith('data:image/')) {
                imgSrc = imageData;
            } else if (imageData.includes('uploads/') || imageData.includes('firmas/') || imageData.includes('sellos/') ||
                /\.(jpg|jpeg|png|gif|webp)$/i.test(imageData)) {
                let baseUrl = (BASE_URL || window.location.origin + '/');

                if (imageData.includes('uploads/')) {
                    imgSrc = baseUrl + imageData;
                } else if (imageData.includes('firmas/')) {
                    imgSrc = baseUrl + 'uploads/' + imageData;
                } else if (imageData.includes('sellos/')) {
                    imgSrc = baseUrl + 'uploads/' + imageData;
                } else {
                    let carpeta = titulo.toLowerCase() === 'firma' ? 'firmas' : 'sellos';
                    imgSrc = baseUrl + 'uploads/' + carpeta + '/' + imageData;
                }
            } else if (/^[A-Za-z0-9+/]*={0,2}$/.test(imageData) && imageData.length > 100) {
                imgSrc = `data:image/png;base64,${imageData}`;
            }
        }

        img.src = imgSrc;
        img.alt = titulo;
        img.className = 'max-w-full max-h-24 border border-gray-300 rounded shadow-sm';

        img.onerror = () => previewDiv.innerHTML = `<p class="text-red-500 text-sm">Error cargando ${titulo.toLowerCase()}</p>`;

        previewDiv.innerHTML = '';
        previewDiv.appendChild(img);
        previewDiv.classList.add('has-image');

        if (!existingPreviewDiv) {
            container.appendChild(previewDiv);
        }

        const label = document.querySelector(`label[for="${fieldName}"]`);
        if (label) {
            label.innerHTML = `<i class="fas fa-check-circle mr-2 text-green-600"></i>${titulo} del Especialista`;
            label.style.color = '#059669';
        }
    }
}

window.precargarSeccionE = precargarSeccionE;
window.precargarSeccionF = precargarSeccionF;
window.precargarSeccionH = precargarSeccionH;
window.precargarSeccionI = precargarSeccionI;
window.precargarSeccionJ = precargarSeccionJ;
window.precargarSeccionK = precargarSeccionK;
window.precargarSeccionL = precargarSeccionL;
window.precargarSeccionM = precargarSeccionM;
window.precargarSeccionN = precargarSeccionN;
window.precargarSeccionO = precargarSeccionO;
window.cargarFirmaYSelloObservacion = cargarFirmaYSelloObservacion;
