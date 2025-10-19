// ========================================
// PRECARGARDATOSMODIFICACIONESPECIALISTA.JS - PARA MODIFICACIONES DE ESPECIALISTAS
// ========================================

/**
 * FUNCIÓN: Formatear hora para input HTML5 (HH:mm:ss)
 * Elimina microsegundos de SQL Server TIME
 */
function formatearHoraHTML(hora) {
    if (!hora) return '';

    // Si la hora viene con microsegundos de SQL Server, eliminarlos
    if (typeof hora === 'string' && hora.includes('.')) {
        hora = hora.split('.')[0];
    }

    // Si es un string, parsearlo
    if (typeof hora === 'string') {
        const parts = hora.split(':');
        if (parts.length >= 2) {
            const hh = parts[0].padStart(2, '0');
            const mm = parts[1].padStart(2, '0');
            const ss = (parts[2] || '00').substring(0, 2).padStart(2, '0');
            return `${hh}:${mm}:${ss}`;
        }
    }

    return hora;
}

/**
 * FUNCIÓN: Verificar si un registro "Sin tratamiento específico" está realmente vacío
 */
function esRegistroVacioSinTratamiento(trat) {
    return (
        trat.trat_medicamento === 'Sin tratamiento específico' &&
        (!trat.trat_via || trat.trat_via.trim() === '') &&
        (!trat.trat_dosis || trat.trat_dosis.trim() === '') &&
        (!trat.trat_posologia || trat.trat_posologia.trim() === '') &&
        (!trat.trat_dias || trat.trat_dias === '0' || trat.trat_dias === 0)
    );
}

$(document).ready(function () {
    // VERIFICAR QUE ESTAMOS EN CONTEXTO DE ESPECIALISTAS Y MODIFICACIÓN
    if (typeof window.contextoEspecialidad === 'undefined' || !window.contextoEspecialidad) {
        return;
    }

    // VERIFICAR QUE ES UNA MODIFICACIÓN
    if (!verificarSiEsModificacionEspecialista()) {
        return;
    }


    // Precargar datos guardados de las secciones E hasta P (especialistas no manejan C y D)
    if (typeof window.datosFormularioGuardadoEspecialista !== 'undefined' && window.datosFormularioGuardadoEspecialista) {

        // SECCIÓN E - Antecedentes
        setTimeout(() => {
            if (window.datosFormularioGuardadoEspecialista.seccionE) {
                precargarSeccionE(window.datosFormularioGuardadoEspecialista.seccionE);
            }
        }, 500);

        // SECCIÓN F - Problema actual
        setTimeout(() => {
            if (window.datosFormularioGuardadoEspecialista.seccionF) {
                precargarSeccionF(window.datosFormularioGuardadoEspecialista.seccionF);
            }
        }, 600);

        // SECCIÓN H - Examen físico
        setTimeout(() => {
            if (window.datosFormularioGuardadoEspecialista.seccionH) {
                precargarSeccionH(window.datosFormularioGuardadoEspecialista.seccionH);
            }
        }, 700);

        // SECCIÓN I - Examen físico de trauma
        setTimeout(() => {
            if (window.datosFormularioGuardadoEspecialista.seccionI) {
                precargarSeccionI(window.datosFormularioGuardadoEspecialista.seccionI);
            }
        }, 800);

        // SECCIÓN J - Embarazo y parto
        setTimeout(() => {
            if (window.datosFormularioGuardadoEspecialista.seccionJ) {
                precargarSeccionJ(window.datosFormularioGuardadoEspecialista.seccionJ);
            }
        }, 900);

        // SECCIÓN K - Exámenes complementarios
        setTimeout(() => {
            if (window.datosFormularioGuardadoEspecialista.seccionK) {
                precargarSeccionK(window.datosFormularioGuardadoEspecialista.seccionK);
            }
        }, 1000);

        // SECCIÓN L - Diagnósticos presuntivos
        setTimeout(() => {
            if (window.datosFormularioGuardadoEspecialista.seccionL) {
                precargarSeccionL(window.datosFormularioGuardadoEspecialista.seccionL);
            }
        }, 1100);

        // SECCIÓN M - Diagnósticos definitivos
        setTimeout(() => {
            if (window.datosFormularioGuardadoEspecialista.seccionM) {
                precargarSeccionM(window.datosFormularioGuardadoEspecialista.seccionM);
            }
        }, 1200);

        // SECCIÓN N - Plan de tratamiento
        setTimeout(() => {
            if (window.datosFormularioGuardadoEspecialista.seccionN) {
                precargarSeccionN(window.datosFormularioGuardadoEspecialista.seccionN);
            }
            // CARGAR FIRMA Y SELLO SIEMPRE (independiente de si hay datos en seccionN)
            setTimeout(() => {
                cargarFirmaYSelloModificacion();
            }, 200);
        }, 1300);

        // SECCIÓN O - Alta y egreso
        setTimeout(() => {
            if (window.datosFormularioGuardadoEspecialista.seccionO) {
                precargarSeccionO(window.datosFormularioGuardadoEspecialista.seccionO);
            }
        }, 1400);

        // SECCIÓN P - Profesional responsable
        setTimeout(() => {
            if (window.datosFormularioGuardadoEspecialista.seccionP) {
                precargarSeccionP(window.datosFormularioGuardadoEspecialista.seccionP);
            }
        }, 1500);
    }
});

// FUNCIÓN: Verificar si es modificación para especialistas
function verificarSiEsModificacionEspecialista() {
    // 1. Variable window global (igual que médicos)
    if (typeof window.esModificacion !== 'undefined' && window.esModificacion === true) {
        // console.log('✅ Es modificación detectado por window.esModificacion');
        return true;
    }

    // 2. Variable window específica para especialistas
    if (typeof window.esModificacionEspecialista !== 'undefined' && window.esModificacionEspecialista === true) {
        // console.log('✅ Es modificación detectado por window.esModificacionEspecialista');
        return true;
    }

    // 3. Input hidden
    const inputModificacion = document.querySelector('input[name="es_modificacion"]');
    if (inputModificacion && inputModificacion.value === '1') {
        // console.log('✅ Es modificación detectado por input hidden');
        return true;
    }

    // 4. Verificar habilitado_por_admin
    const formularioUsuario = document.querySelector('input[name="habilitado_por_admin"]');
    if (formularioUsuario && formularioUsuario.value === '1') {
        // console.log('✅ Es modificación detectado por habilitado_por_admin');
        return true;
    }

    // 5. Verificar si está en el DOM el indicador de modificación
    const alertaModificacion = document.querySelector('.bg-orange-100');
    if (alertaModificacion && alertaModificacion.textContent.includes('Formulario Habilitado para Modificación')) {
        // console.log('✅ Es modificación detectado por alerta visual');
        return true;
    }

    // console.log('❌ No es modificación');
    return false;
}

// SECCIÓN E: ANTECEDENTES (misma lógica que médicos)
function precargarSeccionE(datos) {

    if (!datos) return;

    if (Array.isArray(datos)) {
        // Marcar checkboxes de antecedentes
        datos.forEach(antecedente => {
            const checkbox = document.querySelector(`input[name="antecedentes[]"][value="${antecedente.tan_codigo}"]`);
            if (checkbox) checkbox.checked = true;
        });

        // Descripción del primer antecedente
        if (datos[0] && datos[0].ap_descripcion) {
            const descripcionField = document.querySelector('textarea[name="ant_descripcion"]');
            if (descripcionField) descripcionField.value = datos[0].ap_descripcion;
        }

        // Verificar si alguno tiene no_aplica
        const noAplica = datos.some(ant => ant.ap_no_aplica == 1);
        if (noAplica) {
            const checkNoAplica = document.querySelector('input[name="ant_no_aplica"]');
            if (checkNoAplica) checkNoAplica.checked = true;
        }
    }
}

// SECCIÓN F: PROBLEMA ACTUAL
function precargarSeccionF(datos) {

    if (!datos) return;

    if (Array.isArray(datos) && datos.length > 0) {
        datos = datos[0];
    }

    if (datos.pro_descripcion) {
        const descripcionField = document.querySelector('textarea[name="ep_descripcion_actual"]');
        if (descripcionField) descripcionField.value = datos.pro_descripcion;
    }
}

// SECCIÓN H: EXAMEN FÍSICO
function precargarSeccionH(datos) {

    if (!datos) return;

    if (Array.isArray(datos)) {
        // Marcar zonas del examen físico
        datos.forEach(examen => {
            const checkbox = document.querySelector(`input[name="zonas_examen_fisico[]"][value="${examen.zef_codigo}"]`);
            if (checkbox) checkbox.checked = true;
        });

        // Descripción
        if (datos[0] && datos[0].ef_descripcion) {
            const descripcionField = document.querySelector('textarea[name="ef_descripcion"]');
            if (descripcionField) descripcionField.value = datos[0].ef_descripcion;
        }
    }
}

// SECCIÓN I: EXAMEN FÍSICO DE TRAUMA
function precargarSeccionI(datos) {

    if (!datos) return;

    if (Array.isArray(datos) && datos.length > 0) {
        datos = datos[0];
    }

    if (datos.tra_descripcion) {
        const descripcionField = document.querySelector('textarea[name="eft_descripcion"]');
        if (descripcionField) descripcionField.value = datos.tra_descripcion;
    }
}

// SECCIÓN J: EMBARAZO Y PARTO
function precargarSeccionJ(datos) {
    if (!datos) return;


    if (Array.isArray(datos) && datos.length > 0) {
        datos = datos[0];
    }

    // No aplica
    if (datos.emb_no_aplica == 1) {
        const checkNoAplica = document.querySelector('input[name="emb_no_aplica"]');
        if (checkNoAplica) {
            checkNoAplica.checked = true;
        }
        return;
    }

    // Campos de texto/número/textarea
    const camposEmbarazo = {
        'emb_gestas': datos.emb_numero_gestas,
        'emb_partos': datos.emb_numero_partos,
        'emb_abortos': datos.emb_numero_abortos,
        'emb_cesareas': datos.emb_numero_cesareas,
        'emb_semanas_gestacion': datos.emb_semanas_gestacion,
        'emb_fcf': datos.emb_frecuencia_cardiaca_fetal,
        'emb_tiempo_ruptura': datos.emb_tiempo,
        'emb_afu': datos.emb_afu,
        'emb_dilatacion': datos.emb_dilatacion,
        'emb_borramiento': datos.emb_borramiento,
        'emb_score_mama': datos.emb_score_mama,
        'emb_observaciones': datos.emb_observaciones
    };

    Object.keys(camposEmbarazo).forEach(campo => {
        if (camposEmbarazo[campo] !== null && camposEmbarazo[campo] !== undefined) {
            const element = document.querySelector(`input[name="${campo}"], textarea[name="${campo}"]`);
            if (element) {
                element.value = camposEmbarazo[campo];
            }
        }
    });

    // Select de Presentación
    if (datos.emb_presentacion) {
        const presentacionSelect = document.querySelector('select[name="emb_presentacion"]');
        if (presentacionSelect) {
            presentacionSelect.value = datos.emb_presentacion;
        }
    }

    // Select de Plano
    if (datos.emb_plano) {
        const planoSelect = document.querySelector('select[name="emb_plano"]');
        if (planoSelect) {
            planoSelect.value = datos.emb_plano;
        }
    }

    // Radios
    function marcarRadio(name, valor) {
        if (!valor) return;
        const radio = document.querySelector(`input[name="${name}"][value="${valor.toLowerCase()}"]`);
        if (radio) {
            radio.checked = true;
        }
    }

    marcarRadio('emb_ruptura_membranas', datos.emb_ruptura_menbranas);
    marcarRadio('emb_sangrado_vaginal', datos.emb_sangrado_vaginal);
    marcarRadio('emb_contracciones', datos.emb_contracciones);
    marcarRadio('emb_movimiento_fetal', datos.emb_movimiento_fetal);
    marcarRadio('emb_pelvis_viable', datos.emb_pelvis_viable);

    // Fecha FUM
    if (datos.emb_fum && datos.emb_fum !== "0000-00-00") {
        const fumInput = document.querySelector('input[name="emb_fum"]');
        if (fumInput) {
            fumInput.value = datos.emb_fum;
        }
    }
}


// SECCIÓN K: EXÁMENES COMPLEMENTARIOS
function precargarSeccionK(datos) {

    if (!datos) return;

    if (Array.isArray(datos)) {
        // Verificar no aplica
        const noAplica = datos.some(exam => exam.exa_no_aplica == 1);
        if (noAplica) {
            const checkNoAplica = document.querySelector('input[name="exc_no_aplica"]');
            if (checkNoAplica) checkNoAplica.checked = true;
        } else {
            // Marcar tipos de exámenes
            datos.forEach(examen => {
                const checkbox = document.querySelector(`input[name="tipos_examenes[]"][value="${examen.tipo_id}"]`);
                if (checkbox) checkbox.checked = true;
            });
        }

        // Observaciones
        if (datos[0] && datos[0].exa_observaciones && datos[0].exa_observaciones !== 'No aplica') {
            const observacionesField = document.querySelector('textarea[name="exc_observaciones"]');
            if (observacionesField) observacionesField.value = datos[0].exa_observaciones;
        }
    }
}

// SECCIÓN L: DIAGNÓSTICOS PRESUNTIVOS
function precargarSeccionL(datos) {

    if (!datos) return;

    const diagnosticos = Array.isArray(datos) ? datos : [datos];

    diagnosticos.forEach((diag, index) => {
        if (index < 3) { // Máximo 3 diagnósticos
            const numDiag = index + 1;

            // Descripción
            if (diag.diagp_descripcion && diag.diagp_descripcion !== 'Sin diagnóstico presuntivo específico') {
                const descField = document.querySelector(`input[name="diag_pres_desc${numDiag}"], textarea[name="diag_pres_desc${numDiag}"]`);
                if (descField) descField.value = diag.diagp_descripcion;
            }

            // CIE
            if (diag.diagp_cie) {
                const cieField = document.querySelector(`input[name="diag_pres_cie${numDiag}"]`);
                if (cieField) cieField.value = diag.diagp_cie;
            }
        }
    });
}

// SECCIÓN M: DIAGNÓSTICOS DEFINITIVOS
function precargarSeccionM(datos) {

    if (!datos) return;

    const diagnosticos = Array.isArray(datos) ? datos : [datos];

    diagnosticos.forEach((diag, index) => {
        if (index < 3) { // Máximo 3 diagnósticos
            const numDiag = index + 1;

            // Descripción
            if (diag.diagd_descripcion && diag.diagd_descripcion !== 'Sin diagnóstico definitivo específico') {
                const descField = document.querySelector(`input[name="diag_def_desc${numDiag}"], textarea[name="diag_def_desc${numDiag}"]`);
                if (descField) descField.value = diag.diagd_descripcion;
            }

            // CIE
            if (diag.diagd_cie) {
                const cieField = document.querySelector(`input[name="diag_def_cie${numDiag}"]`);
                if (cieField) cieField.value = diag.diagd_cie;
            }
        }
    });
}

// SECCIÓN N: PLAN DE TRATAMIENTO
function precargarSeccionN(datos) {

    if (!datos) return;

    const tratamientos = Array.isArray(datos) ? datos : [datos];

    // Observaciones generales
    if (tratamientos[0] && tratamientos[0].trat_observaciones) {
        const observacionesField = document.querySelector('textarea[name="plan_tratamiento"]');
        if (observacionesField) observacionesField.value = tratamientos[0].trat_observaciones;
    }

    // Tratamientos individuales
    let indexTratamiento = 0;
    tratamientos.forEach((trat) => {
        if (trat.trat_medicamento &&
            trat.trat_medicamento !== 'Plan de tratamiento' &&
            !esRegistroVacioSinTratamiento(trat) &&
            indexTratamiento < 7) {

            const numTrat = indexTratamiento + 1;

            // Medicamento
            const medField = document.querySelector(`input[name="trat_med${numTrat}"]`);
            if (medField) medField.value = trat.trat_medicamento;

            // Vía
            const viaField = document.querySelector(`select[name="trat_via${numTrat}"], input[name="trat_via${numTrat}"]`);
            if (viaField) viaField.value = trat.trat_via || '';

            // Dosis
            const dosisField = document.querySelector(`input[name="trat_dosis${numTrat}"]`);
            if (dosisField) dosisField.value = trat.trat_dosis || '';

            // Posología
            const posologiaField = document.querySelector(`input[name="trat_posologia${numTrat}"]`);
            if (posologiaField) posologiaField.value = trat.trat_posologia || '';

            // Días
            const diasField = document.querySelector(`input[name="trat_dias${numTrat}"]`);
            if (diasField) diasField.value = trat.trat_dias || '';

            // ID del tratamiento (para mantener IDs consistentes)
            const tratIdField = document.querySelector(`input[name="trat_id${numTrat}"]`);
            if (tratIdField) tratIdField.value = trat.trat_id || '';

            // Estado de administrado
            const administradoField = document.querySelector(`input[name="trat_administrado${numTrat}"]`);
            const administradoButton = document.querySelector(`#btn_administrado${numTrat}`);

            if (administradoField && administradoButton) {
                if (trat.trat_administrado == 1) {
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

            indexTratamiento++;
        }
    });


}

// SECCIÓN O: ALTA Y EGRESO
function precargarSeccionO(datos) {
    if (!datos) return;

    let datosEgreso = null;
    let estadosEgreso = [];
    let modalidadesEgreso = [];
    let tiposEgreso = [];

    try {
        // AHORA ESPECIALISTAS ENVÍA ARRAY DE REGISTROS (igual que médicos)
        if (Array.isArray(datos)) {
            // Los datos vienen como array de registros
            datosEgreso = datos[0]; // Datos básicos del primer elemento

            // Extraer códigos de estados, modalidades y tipos
            estadosEgreso = datos.map(item => item.ese_codigo).filter(codigo => codigo);
            modalidadesEgreso = datos.map(item => item.moe_codigo).filter(codigo => codigo);
            tiposEgreso = datos.map(item => item.tie_codigo).filter(codigo => codigo);

        } else if (typeof datos === 'object') {
            // Formato objeto individual (fallback)
            datosEgreso = datos;

            // Verificar si tiene el formato procesado (arrays separados) - LEGACY
            if (datos.estados_egreso || datos.modalidades_egreso || datos.tipos_egreso) {
                estadosEgreso = datos.estados_egreso || [];
                modalidadesEgreso = datos.modalidades_egreso || [];
                tiposEgreso = datos.tipos_egreso || [];

            } else {
                // Objeto individual simple
                if (datos.ese_codigo) estadosEgreso.push(datos.ese_codigo);
                if (datos.moe_codigo) modalidadesEgreso.push(datos.moe_codigo);
                if (datos.tie_codigo) tiposEgreso.push(datos.tie_codigo);

            }
        }

        // Marcar MÚLTIPLES estados de egreso
        estadosEgreso.forEach(codigo => {
            const checkbox = document.querySelector(`input[name="estados_egreso[]"][value="${codigo}"]`);
            if (checkbox) {
                checkbox.checked = true;
            }
        });

        // Marcar MÚLTIPLES modalidades de egreso
        modalidadesEgreso.forEach(codigo => {
            const checkbox = document.querySelector(`input[name="modalidades_egreso[]"][value="${codigo}"]`);
            if (checkbox) {
                checkbox.checked = true;
            }
        });

        // Marcar MÚLTIPLES tipos de egreso
        tiposEgreso.forEach(codigo => {
            const checkbox = document.querySelector(`input[name="tipos_egreso[]"][value="${codigo}"]`);
            if (checkbox) {
                checkbox.checked = true;
            }
        });

        // Llenar campos de texto (del primer elemento o datos únicos)
        if (datosEgreso) {
            // Establecimiento
            if (datosEgreso.egr_establecimiento) {
                const estabField = document.querySelector('input[name="egreso_establecimiento"]');
                if (estabField) {
                    estabField.value = datosEgreso.egr_establecimiento;
                }
            }

            // Observaciones
            if (datosEgreso.egr_observaciones) {
                const obsField = document.querySelector('textarea[name="egreso_observacion"]');
                if (obsField) {
                    obsField.value = datosEgreso.egr_observaciones;
                }
            }

            // Días de reposo
            if (datosEgreso.egr_dias_reposo) {
                const reposoField = document.querySelector('input[name="egreso_dias_reposo"]');
                if (reposoField) {
                    reposoField.value = datosEgreso.egr_dias_reposo;
                }
            }
        }

    } catch (error) {
        console.error('❌ Error precargando Sección O:', error);
    }
}

// SECCIÓN P: PROFESIONAL RESPONSABLE - CORREGIDO PARA CARGAR ARCHIVOS (ESPECIALISTAS)
function precargarSeccionP(datos) {
    if (!datos) return;

    // Si es array, tomar el primer elemento
    if (Array.isArray(datos) && datos.length > 0) {
        datos = datos[0];
    }

    // Campos de texto del profesional
    const camposProfesional = {
        'prof_fecha': datos.pro_fecha,
        'prof_hora': formatearHoraHTML(datos.pro_hora),
        'prof_primer_nombre': datos.pro_primer_nombre,
        'prof_primer_apellido': datos.pro_primer_apellido,
        'prof_segundo_apellido': datos.pro_segundo_apellido,
        'prof_documento': datos.pro_nro_documento
    };

    // Llenar campos de texto
    Object.keys(camposProfesional).forEach(campo => {
        if (camposProfesional[campo]) {
            const element = document.querySelector(`input[name="${campo}"]`);
            if (element) {
                element.value = camposProfesional[campo];
            }
        }
    });


    // Manejar firma profesional
    if (datos.pro_firma) {
        manejarImagenProfesional('prof_firma', datos.pro_firma, true, 'Firma');
    }

    // Manejar sello profesional
    if (datos.pro_sello) {
        manejarImagenProfesional('prof_sello', datos.pro_sello, true, 'Sello');
    }
}

// FUNCIÓN: Manejar imagen profesional (firma/sello) - Actualizado según patrón de buscarPorFecha.js
function manejarImagenProfesional(fieldName, imagenBase64, existe, titulo) {
    const inputField = document.querySelector(`input[name="${fieldName}"]`);

    if (!inputField) {
        return;
    }

    const container = inputField.parentElement;

    // Buscar el div de preview existente por ID específico
    const fieldType = fieldName.replace('prof_', '');
    const existingPreviewDiv = document.getElementById(`${fieldType}-preview`);

    // Limpiar elementos previos creados por nuestra función
    const existingMessage = container.querySelector('.image-message');
    const existingControls = container.querySelector('.image-controls');
    const existingCustomPreview = container.querySelector('.custom-image-preview');

    if (existingMessage) existingMessage.remove();
    if (existingControls) existingControls.remove();
    if (existingCustomPreview) existingCustomPreview.remove();

    if (existe && imagenBase64) {
        // La imagen existe - ocultar input y mostrar preview
        inputField.style.display = 'none';

        // Usar el div de preview existente o crear uno nuevo
        let previewDiv = existingPreviewDiv;
        if (!previewDiv) {
            previewDiv = document.createElement('div');
            previewDiv.className = 'custom-image-preview mt-2';
            previewDiv.id = `${fieldType}-preview`;
        }

        const img = document.createElement('img');

        // Determinar el tipo de dato y construir la URL de imagen
        let imgSrc = '';
        if (typeof imagenBase64 === 'string' && imagenBase64.trim()) {
            let imageData = imagenBase64.trim();

            // Si ya tiene prefijo data:image (base64), usarlo directamente
            if (imageData.startsWith('data:image/')) {
                imgSrc = imageData;
            }
            // Si es una ruta de archivo (contiene uploads/ o termina en extensión de imagen)
            else if (imageData.includes('uploads/') || /\.(jpg|jpeg|png|gif|webp)$/i.test(imageData)) {
                // Construir URL completa para archivo (sin /public/)
                let baseUrl = '';
                if (typeof window.base_url !== 'undefined' && window.base_url) {
                    baseUrl = window.base_url;
                    // Quitar /public/ si está al final
                    baseUrl = baseUrl.replace(/\/public\/$/, '/');
                } else {
                    baseUrl = (BASE_URL || window.location.origin + '/');
                }
                imgSrc = baseUrl + imageData;
            }
            // Si parece ser base64 puro (solo caracteres base64)
            else if (/^[A-Za-z0-9+/]*={0,2}$/.test(imageData) && imageData.length > 100) {
                imgSrc = `data:image/png;base64,${imageData}`;
            }
            else {
                console.error('❌ Formato de imagen no reconocido:', imageData.substring(0, 100) + '...');
                return;
            }
        } else {
            console.error('❌ Dato de imagen no válido:', imagenBase64);
            return;
        }

        img.src = imgSrc;
        img.alt = titulo;
        img.className = 'max-w-full max-h-24 border border-gray-300 rounded shadow-sm cursor-pointer';

        // Manejar errores de carga de imagen
        img.onerror = function () {
            console.error('❌ Error cargando imagen para:', titulo, 'URL:', imgSrc);
            this.style.display = 'none';

            // Mostrar mensaje de error
            const errorDiv = document.createElement('div');
            errorDiv.className = 'text-xs text-red-600 bg-red-50 p-2 rounded border border-red-200';

            const isFilePath = imgSrc && !imgSrc.startsWith('data:image/');
            errorDiv.innerHTML = `
                <i class="fas fa-exclamation-triangle mr-1"></i>
                ${isFilePath ?
                    'Error al cargar el archivo de imagen. Verifique que el archivo existe.' :
                    'Error al cargar la imagen. Los datos pueden estar corruptos.'}
            `;
            this.parentElement.appendChild(errorDiv);
        };
        img.onclick = function () {
            // Abrir imagen en nueva ventana con validación
            if (imgSrc && (imgSrc.startsWith('data:image/') || imgSrc.startsWith('http'))) {
                const newWindow = window.open();
                if (newWindow) {
                    newWindow.document.write(`<img src="${imgSrc}" alt="${titulo}" style="max-width:100%; height:auto;">`);
                    newWindow.document.title = titulo;
                }
            } else {
                console.error('❌ No se puede abrir imagen inválida:', imgSrc);
            }
        };

        // Limpiar contenido previo del preview y agregar la imagen
        previewDiv.innerHTML = '';
        previewDiv.appendChild(img);
        previewDiv.classList.add('has-image');

        // Crear mensaje informativo
        const messageDiv = document.createElement('div');
        messageDiv.className = 'image-message bg-green-50 border border-green-200 rounded-lg p-3 mt-2';
        messageDiv.innerHTML = `
            <div class="flex items-start space-x-3">
                <div class="flex-shrink-0">
                    <i class="fas fa-check-circle text-green-600 text-lg"></i>
                </div>
                <div class="flex-1 min-w-0">
                    <h4 class="text-sm font-medium text-green-800">
                        ${titulo} cargado
                    </h4>
                    <p class="text-xs text-green-600 mt-1">
                        <i class="fas fa-image mr-1"></i>
                        Imagen previa guardada
                    </p>
                    <p class="text-xs text-green-700 mt-2">
                        <i class="fas fa-info-circle mr-1"></i>
                        Seleccione un nuevo archivo para reemplazar el existente
                    </p>
                </div>
            </div>
        `;

        // Crear controles
        const controlsDiv = document.createElement('div');
        controlsDiv.className = 'image-controls flex space-x-2 mt-2';

        const changeButton = document.createElement('button');
        changeButton.type = 'button';
        changeButton.className = 'px-3 py-1 text-xs bg-blue-500 text-white rounded hover:bg-blue-600';
        changeButton.innerHTML = '<i class="fas fa-edit mr-1"></i>Cambiar';
        changeButton.onclick = function () {
            // Mostrar input file y restaurar preview original
            inputField.style.display = 'block';

            // Limpiar el preview y restaurar estado original
            if (existingPreviewDiv) {
                existingPreviewDiv.innerHTML = '<i class="fas fa-image text-gray-400"></i>';
                existingPreviewDiv.classList.remove('has-image');
            }

            messageDiv.style.display = 'none';
            controlsDiv.style.display = 'none';
        };

        controlsDiv.appendChild(changeButton);

        // Insertar elementos en el container (solo si no existían)
        if (!existingPreviewDiv) {
            container.appendChild(previewDiv);
        }
        container.appendChild(messageDiv);
        container.appendChild(controlsDiv);

        // Crear hidden input para mantener referencia (valor original, no la URL construida)
        let hiddenInput = container.querySelector(`input[name="${fieldName}_actual"]`);
        if (!hiddenInput) {
            hiddenInput = document.createElement('input');
            hiddenInput.type = 'hidden';
            hiddenInput.name = `${fieldName}_actual`;
            container.appendChild(hiddenInput);
        }
        hiddenInput.value = imagenBase64; // Mantener el valor original (ruta o base64)

    } else {
        // La imagen no existe - mostrar input normal
        inputField.style.display = 'block';

        // Mensaje informativo
        const messageDiv = document.createElement('div');
        messageDiv.className = 'image-message bg-gray-50 border border-gray-200 rounded-lg p-3 mt-2';
        messageDiv.innerHTML = `
            <div class="flex items-start space-x-3">
                <div class="flex-shrink-0">
                    <i class="fas fa-info-circle text-gray-500 text-lg"></i>
                </div>
                <div class="flex-1 min-w-0">
                    <h4 class="text-sm font-medium text-gray-700">
                        ${titulo} no disponible
                    </h4>
                    <p class="text-xs text-gray-600 mt-1">
                        <i class="fas fa-upload mr-1"></i>
                        Seleccione un archivo para cargar
                    </p>
                </div>
            </div>
        `;

        container.appendChild(messageDiv);
    }
}

/**
 * FUNCIÓN: Cargar firma y sello del especialista en Sección N (Modificación)
 */
function cargarFirmaYSelloModificacion() {
     if (typeof window.medico_que_guardo_especialidad === 'undefined' || !window.medico_que_guardo_especialidad) {
        return;
    }

    const firmaUrl = window.medico_que_guardo_especialidad.firma_url;
    const selloUrl = window.medico_que_guardo_especialidad.sello_url;

    if (firmaUrl && firmaUrl.trim() !== '') {
        manejarImagenProfesionalEspecialista('esp_firma_n', firmaUrl, true, 'Firma');
    }

    if (selloUrl && selloUrl.trim() !== '') {
        manejarImagenProfesionalEspecialista('esp_sello_n', selloUrl, true, 'Sello');
    }
}

/**
 * FUNCIÓN: Manejar imagen especialista (firma/sello) para Sección N
 * Copiada de precargarDatosProcesoParcial.js que funciona correctamente
 */
function manejarImagenEspecialista(fieldName, imagenBase64, existe, titulo) {
    const inputField = document.querySelector(`input[name="${fieldName}"]`) || document.getElementById(fieldName);

    if (!inputField) {
        return;
    }

    const container = inputField.parentElement;

    // Buscar el div de preview existente por ID específico
    const fieldType = fieldName.replace('esp_', '').replace('prof_', '');
    const existingPreviewDiv = document.getElementById(`${fieldType}-n-preview`) || document.getElementById(`${fieldType}-preview`);

    // Limpiar elementos previos creados por nuestra función
    const existingMessage = container.querySelector('.image-message');
    const existingControls = container.querySelector('.image-controls');
    const existingCustomPreview = container.querySelector('.custom-image-preview');

    if (existingMessage) existingMessage.remove();
    if (existingControls) existingControls.remove();
    if (existingCustomPreview) existingCustomPreview.remove();

    if (existe && imagenBase64) {
        // La imagen existe - ocultar input y mostrar preview
        inputField.style.display = 'none';

        // Usar el div de preview existente o crear uno nuevo
        let previewDiv = existingPreviewDiv;
        if (!previewDiv) {
            previewDiv = document.createElement('div');
            previewDiv.className = 'custom-image-preview mt-2';
            previewDiv.id = `${fieldType}-n-preview`;
        }

        const img = document.createElement('img');

        // Determinar el tipo de dato y construir la URL de imagen
        let imgSrc = '';
        if (typeof imagenBase64 === 'string' && imagenBase64.trim()) {
            let imageData = imagenBase64.trim();

            // Si ya tiene prefijo data:image (base64), usarlo directamente
            if (imageData.startsWith('data:image/')) {
                imgSrc = imageData;
            }
            // Si es una ruta de archivo (contiene uploads/ o termina en extensión de imagen)
            else if (imageData.includes('uploads/') || imageData.includes('firmas/') || imageData.includes('sellos/') ||
                imageData.includes('firmas_proceso/') || imageData.includes('sellos_proceso/') ||
                /\.(jpg|jpeg|png|gif|webp)$/i.test(imageData)) {
                // Construir URL completa para archivo apuntando a /uploads/
                let baseUrl = (BASE_URL || window.location.origin + '/');

                // Si imageData ya incluye 'uploads/', usarlo directamente
                if (imageData.includes('uploads/')) {
                    imgSrc = baseUrl + imageData;
                }
                // LEGACY: Si contiene firmas_proceso/ pero no uploads/, agregar uploads/
                else if (imageData.includes('firmas_proceso/')) {
                    imgSrc = baseUrl + 'uploads/' + imageData;
                }
                // LEGACY: Si contiene sellos_proceso/ pero no uploads/, agregar uploads/
                else if (imageData.includes('sellos_proceso/')) {
                    imgSrc = baseUrl + 'uploads/' + imageData;
                }
                // Si contiene firmas/ pero no uploads/, agregar uploads/
                else if (imageData.includes('firmas/')) {
                    imgSrc = baseUrl + 'uploads/' + imageData;
                }
                // Si contiene sellos/ pero no uploads/, agregar uploads/
                else if (imageData.includes('sellos/')) {
                    imgSrc = baseUrl + 'uploads/' + imageData;
                }
                // Si es solo un nombre de archivo, determinar la carpeta según el tipo
                else {
                    // Determinar carpeta según el tipo de imagen (firma o sello)
                    let carpeta = titulo.toLowerCase() === 'firma' ? 'firmas' : 'sellos';
                    imgSrc = baseUrl + 'uploads/' + carpeta + '/' + imageData;
                }
            }
            // Si parece ser base64 puro (solo caracteres base64)
            else if (/^[A-Za-z0-9+/]*={0,2}$/.test(imageData) && imageData.length > 100) {
                imgSrc = `data:image/png;base64,${imageData}`;
            }
            else {
                return;
            }
        } else {
            return;
        }

        img.src = imgSrc;
        img.alt = titulo;
        img.className = 'max-w-full max-h-24 border border-gray-300 rounded shadow-sm cursor-pointer';

        // Manejar errores de carga de imagen
        img.onerror = function () {
            previewDiv.innerHTML = `<p class="text-red-500 text-sm">Error cargando ${titulo.toLowerCase()}</p>`;
        };

        // Limpiar contenido previo del preview
        previewDiv.innerHTML = '';
        previewDiv.appendChild(img);
        previewDiv.classList.add('has-image');

        // Si no estaba en el DOM, agregarlo después del input
        if (!existingPreviewDiv) {
            container.appendChild(previewDiv);
        }

        // Cambiar el label para indicar que ya hay imagen
        const label = document.querySelector(`label[for="${fieldName}"]`);
        if (label) {
            label.innerHTML = `<i class="fas fa-check-circle mr-2 text-green-600"></i>${titulo} Guardado/a`;
            label.style.color = '#059669';
        }

        // Crear mensaje informativo
        const messageDiv = document.createElement('div');
        messageDiv.className = 'image-message text-xs text-gray-600 mt-1';
        messageDiv.innerHTML = `<i class="fas fa-info-circle mr-1"></i>${titulo} cargado/a previamente`;

        container.appendChild(messageDiv);
    }
}

// Exportar funciones para uso externo
window.precargarModificacionEspecialista = {
    verificarSiEsModificacion: verificarSiEsModificacionEspecialista,
    precargarSeccionE: precargarSeccionE,
    precargarSeccionF: precargarSeccionF,
    precargarSeccionH: precargarSeccionH,
    precargarSeccionI: precargarSeccionI,
    precargarSeccionJ: precargarSeccionJ,
    precargarSeccionK: precargarSeccionK,
    precargarSeccionL: precargarSeccionL,
    precargarSeccionM: precargarSeccionM,
    precargarSeccionN: precargarSeccionN,
    precargarSeccionO: precargarSeccionO,
    precargarSeccionP: precargarSeccionP,
    manejarImagenProfesional: manejarImagenProfesional,
};
