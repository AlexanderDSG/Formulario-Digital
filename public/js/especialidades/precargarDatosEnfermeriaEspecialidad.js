// ========================================
// PRECARGAR DATOS ENFERMERÍA ESPECIALIDAD - SECCIONES E-N
// ========================================

$(document).ready(function () {

    // VERIFICAR QUE ESTAMOS EN CONTEXTO DE ENFERMERÍA DE ESPECIALIDAD
    if (!verificarSiEsEnfermeriaEspecialidad()) {
        return;
    }


    // PRECARGAR DATOS GUARDADOS DE LAS SECCIONES E HASTA N
    if (typeof window.datosFormularioGuardadoEspecialista !== 'undefined' && window.datosFormularioGuardadoEspecialista) {

        // SECCIÓN E - Antecedentes
        setTimeout(() => {
            if (window.datosFormularioGuardadoEspecialista.seccionE) {
                precargarSeccionEEnfermeria(window.datosFormularioGuardadoEspecialista.seccionE);
            }
        }, 500);

        // SECCIÓN F - Examen Físico
        setTimeout(() => {
            if (window.datosFormularioGuardadoEspecialista.seccionF) {
                precargarSeccionFEnfermeria(window.datosFormularioGuardadoEspecialista.seccionF);
            }
        }, 600);

        // SECCIÓN H - Exámenes Auxiliares
        setTimeout(() => {
            if (window.datosFormularioGuardadoEspecialista.seccionH) {
                precargarSeccionHEnfermeria(window.datosFormularioGuardadoEspecialista.seccionH);
            }
        }, 700);

        // SECCIÓN I - Diagnóstico Presuntivo
        setTimeout(() => {
            if (window.datosFormularioGuardadoEspecialista.seccionI) {
                precargarSeccionIEnfermeria(window.datosFormularioGuardadoEspecialista.seccionI);
            }
        }, 800);

        // SECCIÓN J - Tratamiento
        setTimeout(() => {
            if (window.datosFormularioGuardadoEspecialista.seccionJ) {
                precargarSeccionJEnfermeria(window.datosFormularioGuardadoEspecialista.seccionJ);
            }
        }, 900);

        // SECCIÓN K - Exámenes Complementarios
        setTimeout(() => {
            if (window.datosFormularioGuardadoEspecialista.seccionK) {
                precargarSeccionKEnfermeria(window.datosFormularioGuardadoEspecialista.seccionK);
            }
        }, 1000);

        // SECCIÓN L - Diagnósticos Presuntivos
        setTimeout(() => {
            if (window.datosFormularioGuardadoEspecialista.seccionL) {
                precargarSeccionLEnfermeria(window.datosFormularioGuardadoEspecialista.seccionL);
            }
        }, 1100);

        // SECCIÓN M - Diagnósticos Definitivos
        setTimeout(() => {
            if (window.datosFormularioGuardadoEspecialista.seccionM) {
                precargarSeccionMEnfermeria(window.datosFormularioGuardadoEspecialista.seccionM);
            }
        }, 1200);

        // SECCIÓN N - Plan de Tratamiento
        setTimeout(() => {
            if (window.datosFormularioGuardadoEspecialista.seccionN) {
                precargarSeccionNEnfermeria(window.datosFormularioGuardadoEspecialista.seccionN);
            }

            // CARGAR FIRMA Y SELLO DEL ESPECIALISTA
            setTimeout(() => {
                cargarFirmaYSelloEspecialista();
            }, 300);

            // Mostrar notificación al final
            setTimeout(() => {
                let seccionesCargadas = 0;
                ['E', 'F', 'H', 'I', 'J', 'K', 'L', 'M', 'N'].forEach(letra => {
                    if (window.datosFormularioGuardadoEspecialista[`seccion${letra}`]) {
                        seccionesCargadas++;
                    }
                });
                mostrarNotificacionEnfermeria(seccionesCargadas);
            }, 200);
        }, 1300);

    }
});

// FUNCIÓN: Verificar si es contexto de enfermería de especialidad
function verificarSiEsEnfermeriaEspecialidad() {
    // 1. Variable window específica para enfermería de especialidad
    if (typeof window.contextoEnfermeria !== 'undefined' && window.contextoEnfermeria &&
        typeof window.esEnfermeriaEspecialidad !== 'undefined' && window.esEnfermeriaEspecialidad) {
        return true;
    }

    // 2. Verificar si existe el input hidden de especialidad
    const inputEspecialidad = document.querySelector('input[name="esp_codigo"]');
    if (inputEspecialidad && inputEspecialidad.value) {
        return true;
    }

    // 3. Verificar si está en el formulario de especialidades
    const formEspecialidad = document.querySelector('#formEspecialidad');
    if (formEspecialidad) {
        return true;
    }

    // 4. Verificar URL contiene especialidades
    if (window.location.pathname.includes('/especialidades/')) {
        return true;
    }

    // 5. Verificar si existe el header de especialidad
    const headerEspecialidad = document.querySelector('.alert-info');
    if (headerEspecialidad && headerEspecialidad.textContent.includes('Especialidad')) {
        return true;
    }

    return false;
}

// ========================================
// FUNCIONES DE PRECARGA POR SECCIÓN (E-N)
// ========================================

// SECCIÓN E: ANTECEDENTES
function precargarSeccionEEnfermeria(datos) {

    if (!datos) return;

    if (Array.isArray(datos)) {
        // Marcar checkboxes de antecedentes
        datos.forEach(antecedente => {
            const checkbox = document.querySelector(`input[name="antecedentes[]"][value="${antecedente.tan_codigo}"]`);
            if (checkbox) {
                checkbox.checked = true;
                marcarComoEspecialista(checkbox);
            }
        });

        // Descripción del primer antecedente
        if (datos[0] && datos[0].ap_descripcion) {
            const descripcionField = document.querySelector('textarea[name="ant_descripcion"]');
            if (descripcionField) {
                descripcionField.value = datos[0].ap_descripcion;
                marcarComoEspecialista(descripcionField);
            }
        }

        // Verificar si alguno tiene no_aplica
        const noAplica = datos.some(ant => ant.ap_no_aplica == 1);
        if (noAplica) {
            const checkNoAplica = document.querySelector('input[name="ant_no_aplica"]');
            if (checkNoAplica) {
                checkNoAplica.checked = true;
                marcarComoEspecialista(checkNoAplica);
            }
        }
    }
}

// SECCIÓN F: PROBLEMA ACTUAL
function precargarSeccionFEnfermeria(datos) {

    if (!datos) return;

    if (Array.isArray(datos) && datos.length > 0) {
        datos = datos[0];
    }

    if (datos.pro_descripcion) {
        const descripcionField = document.querySelector('textarea[name="ep_descripcion_actual"]');
        if (descripcionField) {
            descripcionField.value = datos.pro_descripcion;
            marcarComoEspecialista(descripcionField);
        }
    }
}

// SECCIÓN H: EXAMEN FÍSICO
function precargarSeccionHEnfermeria(datos) {

    if (!datos) return;

    if (Array.isArray(datos)) {
        // Marcar zonas del examen físico
        datos.forEach(examen => {
            const checkbox = document.querySelector(`input[name="zonas_examen_fisico[]"][value="${examen.zef_codigo}"]`);
            if (checkbox) {
                checkbox.checked = true;
                marcarComoEspecialista(checkbox);
            }
        });

        // Descripción
        if (datos[0] && datos[0].ef_descripcion) {
            const descripcionField = document.querySelector('textarea[name="ef_descripcion"]');
            if (descripcionField) {
                descripcionField.value = datos[0].ef_descripcion;
                marcarComoEspecialista(descripcionField);
            }
        }
    }
}

// SECCIÓN I: EXAMEN FÍSICO DE TRAUMA
function precargarSeccionIEnfermeria(datos) {

    if (!datos) return;

    if (Array.isArray(datos) && datos.length > 0) {
        datos = datos[0];
    }

    if (datos.tra_descripcion) {
        const descripcionField = document.querySelector('textarea[name="eft_descripcion"]');
        if (descripcionField) {
            descripcionField.value = datos.tra_descripcion;
            marcarComoEspecialista(descripcionField);
        }
    }
}

// SECCIÓN J: EMBARAZO Y PARTO
function precargarSeccionJEnfermeria(datos) {

    if (!datos) return;

    if (Array.isArray(datos) && datos.length > 0) {
        datos = datos[0];
    }

    // No aplica
    if (datos.emb_no_aplica == 1) {
        const checkNoAplica = document.querySelector('input[name="emb_no_aplica"]');
        if (checkNoAplica) {
            checkNoAplica.checked = true;
            marcarComoEspecialista(checkNoAplica);
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
        'emb_plano': datos.emb_plano,
        'emb_score_mama': datos.emb_score_mama,
        'emb_observaciones': datos.emb_observaciones
    };

    Object.keys(camposEmbarazo).forEach(campo => {
        if (camposEmbarazo[campo] !== null && camposEmbarazo[campo] !== undefined) {
            const element = document.querySelector(`input[name="${campo}"], textarea[name="${campo}"]`);
            if (element) {
                element.value = camposEmbarazo[campo];
                marcarComoEspecialista(element);
            }
        }
    });

    // Radios
    function marcarRadio(name, valor) {
        if (!valor) return;
        const radio = document.querySelector(`input[name="${name}"][value="${valor.toLowerCase()}"]`);
        if (radio) {
            radio.checked = true;
            marcarComoEspecialista(radio);
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
            marcarComoEspecialista(fumInput);
        }
    }
}

// SECCIÓN K: EXÁMENES COMPLEMENTARIOS
function precargarSeccionKEnfermeria(datos) {
    if (!datos) return;


    if (Array.isArray(datos)) {
        // Verificar no aplica
        const noAplica = datos.some(exam => exam.exa_no_aplica == 1);
        if (noAplica) {
            const checkNoAplica = document.querySelector('input[name="exc_no_aplica"]');
            if (checkNoAplica) {
                checkNoAplica.checked = true;
                marcarComoEspecialista(checkNoAplica);
            }
        } else {
            // Marcar tipos de exámenes
            datos.forEach(examen => {
                const checkbox = document.querySelector(`input[name="tipos_examenes[]"][value="${examen.tipo_id}"]`);
                if (checkbox) {
                    checkbox.checked = true;
                    marcarComoEspecialista(checkbox);
                }
            });
        }

        // Observaciones
        if (datos[0] && datos[0].exa_observaciones && datos[0].exa_observaciones !== 'No aplica') {
            const observacionesField = document.querySelector('textarea[name="exc_observaciones"]');
            if (observacionesField) {
                observacionesField.value = datos[0].exa_observaciones;
                marcarComoEspecialista(observacionesField);
            }
        }
    }
}

// SECCIÓN L: DIAGNÓSTICOS PRESUNTIVOS
function precargarSeccionLEnfermeria(datos) {
    if (!datos) return;

    const diagnosticos = Array.isArray(datos) ? datos : [datos];

    diagnosticos.forEach((diag, index) => {
        if (index < 3) { // Máximo 3 diagnósticos
            const numDiag = index + 1;

            // Descripción
            if (diag.diagp_descripcion && diag.diagp_descripcion !== 'Sin diagnóstico presuntivo específico') {
                const descField = document.querySelector(`input[name="diag_pres_desc${numDiag}"], textarea[name="diag_pres_desc${numDiag}"]`);
                if (descField) {
                    descField.value = diag.diagp_descripcion;
                    marcarComoEspecialista(descField);
                }
            }

            // CIE
            if (diag.diagp_cie) {
                const cieField = document.querySelector(`input[name="diag_pres_cie${numDiag}"]`);
                if (cieField) {
                    cieField.value = diag.diagp_cie;
                    marcarComoEspecialista(cieField);
                }
            }
        }
    });
}

// SECCIÓN M: DIAGNÓSTICOS DEFINITIVOS
function precargarSeccionMEnfermeria(datos) {
    if (!datos) return;

    const diagnosticos = Array.isArray(datos) ? datos : [datos];

    diagnosticos.forEach((diag, index) => {
        if (index < 3) { // Máximo 3 diagnósticos
            const numDiag = index + 1;

            // Descripción
            if (diag.diagd_descripcion && diag.diagd_descripcion !== 'Sin diagnóstico definitivo específico') {
                const descField = document.querySelector(`input[name="diag_def_desc${numDiag}"], textarea[name="diag_def_desc${numDiag}"]`);
                if (descField) {
                    descField.value = diag.diagd_descripcion;
                    marcarComoEspecialista(descField);
                }
            }

            // CIE
            if (diag.diagd_cie) {
                const cieField = document.querySelector(`input[name="diag_def_cie${numDiag}"]`);
                if (cieField) {
                    cieField.value = diag.diagd_cie;
                    marcarComoEspecialista(cieField);
                }
            }
        }
    });
}

// SECCIÓN N: PLAN DE TRATAMIENTO
function precargarSeccionNEnfermeria(datos) {
    if (!datos) return;

    const tratamientos = Array.isArray(datos) ? datos : [datos];

    // Observaciones generales
    if (tratamientos[0] && tratamientos[0].trat_observaciones) {
        const observacionesField = document.querySelector('textarea[name="plan_tratamiento"]');
        if (observacionesField) {
            observacionesField.value = tratamientos[0].trat_observaciones;
            marcarComoEspecialista(observacionesField);
        }
    }

    // Tratamientos individuales
    let indexTratamiento = 0;
    tratamientos.forEach((trat) => {
        // Solo filtrar registros realmente vacíos, no por el nombre del medicamento
        const esRegistroVacio = (
            trat.trat_medicamento === 'Sin tratamiento específico' &&
            (!trat.trat_via || trat.trat_via.trim() === '') &&
            (!trat.trat_dosis || trat.trat_dosis.trim() === '') &&
            (!trat.trat_posologia || trat.trat_posologia.trim() === '') &&
            (!trat.trat_dias || trat.trat_dias === '0' || trat.trat_dias === 0)
        );

        if (trat.trat_medicamento &&
            trat.trat_medicamento !== 'Plan de tratamiento' &&
            !esRegistroVacio &&
            indexTratamiento < 7) {

            const numTrat = indexTratamiento + 1;

            // Medicamento
            const medField = document.querySelector(`input[name="trat_med${numTrat}"]`);
            if (medField) {
                medField.value = trat.trat_medicamento;
                marcarComoEspecialista(medField);
            }

            // Vía
            const viaField = document.querySelector(`select[name="trat_via${numTrat}"], input[name="trat_via${numTrat}"]`);
            if (viaField) {
                viaField.value = trat.trat_via || '';
                marcarComoEspecialista(viaField);
            }

            // Dosis
            const dosisField = document.querySelector(`input[name="trat_dosis${numTrat}"]`);
            if (dosisField) {
                dosisField.value = trat.trat_dosis || '';
                marcarComoEspecialista(dosisField);
            }

            // Posología
            const posologiaField = document.querySelector(`input[name="trat_posologia${numTrat}"]`);
            if (posologiaField) {
                posologiaField.value = trat.trat_posologia || '';
                marcarComoEspecialista(posologiaField);
            }

            // Días
            const diasField = document.querySelector(`input[name="trat_dias${numTrat}"]`);
            if (diasField) {
                diasField.value = trat.trat_dias || '';
                marcarComoEspecialista(diasField);
            }

            // ID del tratamiento (para mantener IDs consistentes)
            const tratIdField = document.querySelector(`input[name="trat_id${numTrat}"]`);
            if (tratIdField) {
                tratIdField.value = trat.trat_id || '';
            }

            // Estado de administrado
            const administradoField = document.querySelector(`input[name="trat_administrado${numTrat}"]`);
            const administradoButton = document.querySelector(`#btn_administrado${numTrat}`);

            if (administradoField && administradoButton) {
                // Configurar el estado según la base de datos
                if (trat.trat_administrado == 1) {
                    // Marcar como administrado
                    administradoField.value = '1';
                    administradoButton.classList.remove('bg-green-100', 'hover:bg-green-200', 'text-green-600', 'hover:text-green-700');
                    administradoButton.classList.add('bg-green-600', 'hover:bg-green-700', 'text-white');
                    administradoButton.setAttribute('title', 'Administrado por especialista');

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

                // Marcar el campo hidden como del especialista también
                marcarComoEspecialista(administradoField);
            }

            indexTratamiento++;
        }
    });
}
/**
 *Verificar si un registro "Sin tratamiento específico" está realmente vacío
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
// ========================================
// FUNCIONES DE UTILIDAD
// ========================================

/**
 * Marcar elemento como datos del especialista para enfermería
 */
function marcarComoEspecialista(elemento) {
    if (elemento.type === 'checkbox' || elemento.type === 'radio') {
        // Para checkboxes y radios, marcar el contenedor o label
        const label = elemento.closest('label') || elemento.parentElement;
        if (label) {
            label.style.backgroundColor = '#f0f9ff';
            label.style.borderRadius = '4px';
            label.style.padding = '2px 4px';
            label.style.border = '1px solid #0ea5e9';
        }
    } else {
        // Para inputs, selects y textareas
        elemento.style.backgroundColor = '#f0f9ff';
        elemento.style.borderColor = '#0ea5e9';
        elemento.style.borderWidth = '2px';
    }

    elemento.title = 'Datos del especialista - Visible para enfermería';
}

/**
 * Mostrar notificación de éxito para enfermería
 */
function mostrarNotificacionEnfermeria(seccionesCargadas) {
    const notificacion = document.createElement('div');
    notificacion.className = 'fixed top-4 right-4 z-50 bg-blue-500 text-white px-4 py-3 rounded-lg shadow-lg';
    notificacion.innerHTML = `
        <div class="flex items-center">
            <span class="mr-2">👩‍⚕️</span>
            <div>
                <div class="font-semibold">Enfermería de Especialidad</div>
                <div class="text-sm">A,B,C,D,G: Datos básicos cargados</div>
                <div class="text-sm">E-N: ${seccionesCargadas} secciones del especialista</div>
            </div>
        </div>
    `;

    document.body.appendChild(notificacion);

    setTimeout(() => {
        if (notificacion.parentNode) {
            notificacion.parentNode.removeChild(notificacion);
        }
    }, 7000);
}

/**
 * FUNCIÓN: Cargar firma y sello del especialista en Sección N
 */
function cargarFirmaYSelloEspecialista() {
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
 * FUNCIÓN: Manejar imagen profesional (firma/sello)
 */
function manejarImagenProfesionalEspecialista(fieldName, imagenBase64, existe, titulo) {
    const inputField = document.querySelector(`input[name="${fieldName}"]`) || document.getElementById(fieldName);

    if (!inputField) {
        return;
    }

    const container = inputField.parentElement;
    const fieldType = fieldName.replace('esp_', '').replace('prof_', '');
    const existingPreviewDiv = document.getElementById(`${fieldType}-n-preview`) || document.getElementById(`${fieldType}-preview`);

    // Limpiar elementos previos
    const existingMessage = container.querySelector('.image-message');
    const existingControls = container.querySelector('.image-controls');
    const existingCustomPreview = container.querySelector('.custom-image-preview');

    if (existingMessage) existingMessage.remove();
    if (existingControls) existingControls.remove();
    if (existingCustomPreview) existingCustomPreview.remove();

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

        img.onerror = function () {
            previewDiv.innerHTML = `<p class="text-red-500 text-sm">Error cargando ${titulo.toLowerCase()}</p>`;
        };

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

        const messageDiv = document.createElement('div');
        messageDiv.className = 'image-message mt-2 p-2 bg-blue-50 border border-blue-200 rounded text-xs text-blue-700';
        messageDiv.innerHTML = `
            <i class="fas fa-info-circle mr-1"></i>
            ${titulo} cargado/a del especialista.
        `;
        container.appendChild(messageDiv);
    }
}
