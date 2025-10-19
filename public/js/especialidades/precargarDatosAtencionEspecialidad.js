// ========================================
// PRECARGAR DATOS EN ATENCIÓN ESPECIALIDAD - SECCIONES E-N
// ========================================

$(document).ready(function () {
    // VERIFICAR QUE ESTAMOS EN CONTEXTO DE EN ATENCIÓN DE ESPECIALIDAD
    if (!verificarSiEsAtencionEspecialidad()) {
        return;
    }

    // PRECARGAR DATOS GUARDADOS DE LAS SECCIONES E HASTA N
    if (typeof window.datosFormularioGuardadoEspecialista !== 'undefined' && window.datosFormularioGuardadoEspecialista) {

        // SECCIÓN E - Antecedentes
        setTimeout(() => {
            if (window.datosFormularioGuardadoEspecialista.seccionE) {
                precargarSeccionEAtencion(window.datosFormularioGuardadoEspecialista.seccionE);
            }
        }, 500);

        // SECCIÓN F - Problema Actual
        setTimeout(() => {
            if (window.datosFormularioGuardadoEspecialista.seccionF) {
                precargarSeccionFAtencion(window.datosFormularioGuardadoEspecialista.seccionF);
            }
        }, 600);

        // SECCIÓN H - Examen Físico
        setTimeout(() => {
            if (window.datosFormularioGuardadoEspecialista.seccionH) {
                precargarSeccionHAtencion(window.datosFormularioGuardadoEspecialista.seccionH);
            }
        }, 700);

        // SECCIÓN I - Examen Físico de Trauma
        setTimeout(() => {
            if (window.datosFormularioGuardadoEspecialista.seccionI) {
                precargarSeccionIAtencion(window.datosFormularioGuardadoEspecialista.seccionI);
            }
        }, 800);

        // SECCIÓN J - Embarazo y Parto
        setTimeout(() => {
            if (window.datosFormularioGuardadoEspecialista.seccionJ) {
                precargarSeccionJAtencion(window.datosFormularioGuardadoEspecialista.seccionJ);
            }
        }, 900);

        // SECCIÓN K - Exámenes Complementarios
        setTimeout(() => {
            if (window.datosFormularioGuardadoEspecialista.seccionK) {
                precargarSeccionKAtencion(window.datosFormularioGuardadoEspecialista.seccionK);
            }
        }, 1000);

        // SECCIÓN L - Diagnósticos Presuntivos
        setTimeout(() => {
            if (window.datosFormularioGuardadoEspecialista.seccionL) {
                precargarSeccionLAtencion(window.datosFormularioGuardadoEspecialista.seccionL);
            }
        }, 1100);

        // SECCIÓN M - Diagnósticos Definitivos
        setTimeout(() => {
            if (window.datosFormularioGuardadoEspecialista.seccionM) {
                precargarSeccionMAtencion(window.datosFormularioGuardadoEspecialista.seccionM);
            }
        }, 1200);

        // SECCIÓN N - Plan de Tratamiento
        setTimeout(() => {
            if (window.datosFormularioGuardadoEspecialista.seccionN) {
                precargarSeccionNAtencion(window.datosFormularioGuardadoEspecialista.seccionN);
            }

            // Mostrar notificación al final
            setTimeout(() => {
                let seccionesCargadas = 0;
                ['E', 'F', 'H', 'I', 'J', 'K', 'L', 'M', 'N'].forEach(letra => {
                    if (window.datosFormularioGuardadoEspecialista[`seccion${letra}`]) {
                        seccionesCargadas++;
                    }
                });
                mostrarNotificacionAtencion(seccionesCargadas);
            }, 200);
        }, 1300);

    }
});

// FUNCIÓN: Verificar si es contexto de en atención de especialidad
function verificarSiEsAtencionEspecialidad() {
    // 1. NO debe ser enfermería
    if (typeof window.contextoEnfermeria !== 'undefined' && window.contextoEnfermeria) {
        return false;
    }

    // 2. NO debe ser continuación de proceso
    if (typeof window.esContinuacionProceso !== 'undefined' && window.esContinuacionProceso) {
        return false;
    }

    // 3. NO debe ser modificación
    if (typeof window.esModificacionEspecialista !== 'undefined' && window.esModificacionEspecialista) {
        return false;
    }

    // 4. Verificar si está en el formulario de especialidades
    if (window.location.pathname.includes('/especialidades/formulario/')) {
        return true;
    }

    // 5. Verificar si existe datos del especialista (indica que ya se trabajó)
    if (typeof window.datosFormularioGuardadoEspecialista !== 'undefined' &&
        window.datosFormularioGuardadoEspecialista &&
        Object.keys(window.datosFormularioGuardadoEspecialista).length > 0) {
        return true;
    }

    return false;
}

// ========================================
// FUNCIONES DE PRECARGA POR SECCIÓN (E-N)
// ========================================

// SECCIÓN E: ANTECEDENTES
function precargarSeccionEAtencion(datos) {

    if (!datos) return;

    if (Array.isArray(datos)) {
        // Marcar checkboxes de antecedentes
        datos.forEach(antecedente => {
            const checkbox = document.querySelector(`input[name="antecedentes[]"][value="${antecedente.tan_codigo}"]`);
            if (checkbox) {
                checkbox.checked = true;
            }
        });

        // Descripción del primer antecedente
        if (datos[0] && datos[0].ap_descripcion) {
            const descripcionField = document.querySelector('textarea[name="ant_descripcion"]');
            if (descripcionField) {
                descripcionField.value = datos[0].ap_descripcion;
            }
        }

        // Verificar si alguno tiene no_aplica
        const noAplica = datos.some(ant => ant.ap_no_aplica == 1);
        if (noAplica) {
            const checkNoAplica = document.querySelector('input[name="ant_no_aplica"]');
            if (checkNoAplica) {
                checkNoAplica.checked = true;
            }
        }
    }
}

// SECCIÓN F: PROBLEMA ACTUAL
function precargarSeccionFAtencion(datos) {

    if (!datos) return;

    if (Array.isArray(datos) && datos.length > 0) {
        datos = datos[0];
    }

    if (datos.pro_descripcion) {
        const descripcionField = document.querySelector('textarea[name="ep_descripcion_actual"]');
        if (descripcionField) {
            descripcionField.value = datos.pro_descripcion;
        }
    }
}

// SECCIÓN H: EXAMEN FÍSICO
function precargarSeccionHAtencion(datos) {

    if (!datos) return;

    if (Array.isArray(datos)) {
        // Marcar zonas del examen físico
        datos.forEach(examen => {
            const checkbox = document.querySelector(`input[name="zonas_examen_fisico[]"][value="${examen.zef_codigo}"]`);
            if (checkbox) {
                checkbox.checked = true;
            }
        });

        // Descripción
        if (datos[0] && datos[0].ef_descripcion) {
            const descripcionField = document.querySelector('textarea[name="ef_descripcion"]');
            if (descripcionField) {
                descripcionField.value = datos[0].ef_descripcion;
            }
        }
    }
}

// SECCIÓN I: EXAMEN FÍSICO DE TRAUMA
function precargarSeccionIAtencion(datos) {

    if (!datos) return;

    if (Array.isArray(datos) && datos.length > 0) {
        datos = datos[0];
    }

    if (datos.tra_descripcion) {
        const descripcionField = document.querySelector('textarea[name="eft_descripcion"]');
        if (descripcionField) {
            descripcionField.value = datos.tra_descripcion;
        }
    }
}

// SECCIÓN J: EMBARAZO Y PARTO
function precargarSeccionJAtencion(datos) {

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

    // Campos de texto/número/textarea/select
    const camposEmbarazo = {
        'emb_gestas': datos.emb_numero_gestas,
        'emb_partos': datos.emb_numero_partos,
        'emb_abortos': datos.emb_numero_abortos,
        'emb_cesareas': datos.emb_numero_cesareas,
        'emb_semanas_gestacion': datos.emb_semanas_gestacion,
        'emb_fcf': datos.emb_frecuencia_cardiaca_fetal,
        'emb_tiempo_ruptura': datos.emb_tiempo,
        'emb_afu': datos.emb_afu,
        'emb_presentacion': datos.emb_presentacion,
        'emb_dilatacion': datos.emb_dilatacion,
        'emb_borramiento': datos.emb_borramiento,
        'emb_plano': datos.emb_plano,
        'emb_score_mama': datos.emb_score_mama,
        'emb_observaciones': datos.emb_observaciones
    };

    Object.keys(camposEmbarazo).forEach(campo => {
        if (camposEmbarazo[campo] !== null && camposEmbarazo[campo] !== undefined) {
            const element = document.querySelector(`input[name="${campo}"], textarea[name="${campo}"], select[name="${campo}"]`);
            if (element) {
                element.value = camposEmbarazo[campo];
            }
        }
    });

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
function precargarSeccionKAtencion(datos) {
    if (!datos) return;

    if (Array.isArray(datos)) {
        // Verificar no aplica
        const noAplica = datos.some(exam => exam.exa_no_aplica == 1);
        if (noAplica) {
            const checkNoAplica = document.querySelector('input[name="exc_no_aplica"]');
            if (checkNoAplica) {
                checkNoAplica.checked = true;
            }
        } else {
            // Marcar tipos de exámenes
            datos.forEach(examen => {
                const checkbox = document.querySelector(`input[name="tipos_examenes[]"][value="${examen.tipo_id}"]`);
                if (checkbox) {
                    checkbox.checked = true;
                }
            });
        }

        // Observaciones
        if (datos[0] && datos[0].exa_observaciones && datos[0].exa_observaciones !== 'No aplica') {
            const observacionesField = document.querySelector('textarea[name="exc_observaciones"]');
            if (observacionesField) {
                observacionesField.value = datos[0].exa_observaciones;
            }
        }
    }
}

// SECCIÓN L: DIAGNÓSTICOS PRESUNTIVOS
function precargarSeccionLAtencion(datos) {
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
                }
            }

            // CIE
            if (diag.diagp_cie) {
                const cieField = document.querySelector(`input[name="diag_pres_cie${numDiag}"]`);
                if (cieField) {
                    cieField.value = diag.diagp_cie;
                }
            }
        }
    });
}

// SECCIÓN M: DIAGNÓSTICOS DEFINITIVOS
function precargarSeccionMAtencion(datos) {
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
                }
            }

            // CIE
            if (diag.diagd_cie) {
                const cieField = document.querySelector(`input[name="diag_def_cie${numDiag}"]`);
                if (cieField) {
                    cieField.value = diag.diagd_cie;
                }
            }
        }
    });
}

// SECCIÓN N: PLAN DE TRATAMIENTO
function precargarSeccionNAtencion(datos) {
    if (!datos) return;

    const tratamientos = Array.isArray(datos) ? datos : [datos];

    // Observaciones generales
    if (tratamientos[0] && tratamientos[0].trat_observaciones) {
        const observacionesField = document.querySelector('textarea[name="plan_tratamiento"]');
        if (observacionesField) {
            observacionesField.value = tratamientos[0].trat_observaciones;
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
            !esRegistroVacioSinTratamiento(trat) &&
            indexTratamiento < 7) {

            const numTrat = indexTratamiento + 1;

            // Medicamento
            const medField = document.querySelector(`input[name="trat_med${numTrat}"]`);
            if (medField) {
                medField.value = trat.trat_medicamento;
            }

            // Vía
            const viaField = document.querySelector(`select[name="trat_via${numTrat}"], input[name="trat_via${numTrat}"]`);
            if (viaField) {
                viaField.value = trat.trat_via || '';
            }

            // Dosis
            const dosisField = document.querySelector(`input[name="trat_dosis${numTrat}"]`);
            if (dosisField) {
                dosisField.value = trat.trat_dosis || '';
            }

            // Posología
            const posologiaField = document.querySelector(`input[name="trat_posologia${numTrat}"]`);
            if (posologiaField) {
                posologiaField.value = trat.trat_posologia || '';
            }

            // Días
            const diasField = document.querySelector(`input[name="trat_dias${numTrat}"]`);
            if (diasField) {
                diasField.value = trat.trat_dias || '';
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
/**
 * Mostrar notificación de éxito para en atención
 */
function mostrarNotificacionAtencion(seccionesCargadas) {
    const notificacion = document.createElement('div');
    notificacion.className = 'fixed top-4 right-4 z-50 bg-green-500 text-white px-4 py-3 rounded-lg shadow-lg';
    notificacion.innerHTML = `
        <div class="flex items-center">
            <span class="mr-2">👨‍⚕️</span>
            <div>
                <div class="font-semibold">Médico Especialista - En Atención</div>
                <div class="text-sm">A,B,C,D,G: Datos básicos cargados</div>
                <div class="text-sm">E-N: ${seccionesCargadas} secciones precargadas</div>
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