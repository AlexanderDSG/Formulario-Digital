// ========================================
// PRECARGARDATOSMODIFICACIONMEDICO.JS - PARA MODIFICACIONES DE MÉDICOS
// ========================================

$(document).ready(function () {
    // VERIFICAR QUE ESTAMOS EN CONTEXTO DE MÉDICOS Y MODIFICACIÓN
    if (typeof window.contextoMedico === 'undefined' || !window.contextoMedico) {
        return;
    }

    // VERIFICAR QUE ES UNA MODIFICACIÓN
    if (!verificarSiEsModificacionMedico()) {
        return;
    }


    // Precargar datos guardados de todas las secciones (C hasta P)
    if (typeof window.datosFormularioGuardadoMedico !== 'undefined' && window.datosFormularioGuardadoMedico) {
        
        // Sección C - Inicio de atención
        setTimeout(() => {
            if (window.datosFormularioGuardadoMedico.seccionC) {
                precargarSeccionC(window.datosFormularioGuardadoMedico.seccionC);
            }
        }, 500);

        // Sección D - Eventos/Accidentes
        setTimeout(() => {
            if (window.datosFormularioGuardadoMedico.seccionD) {
                precargarSeccionD(window.datosFormularioGuardadoMedico.seccionD);
            }
        }, 600);

        // Sección E - Antecedentes
        setTimeout(() => {
            if (window.datosFormularioGuardadoMedico.seccionE) {
                precargarSeccionE(window.datosFormularioGuardadoMedico.seccionE);
            }
        }, 700);

        // Sección F - Problema actual
        setTimeout(() => {
            if (window.datosFormularioGuardadoMedico.seccionF) {
                precargarSeccionF(window.datosFormularioGuardadoMedico.seccionF);
            }
        }, 800);

        // Sección H - Examen físico
        setTimeout(() => {
            if (window.datosFormularioGuardadoMedico.seccionH) {
                precargarSeccionH(window.datosFormularioGuardadoMedico.seccionH);
            }
        }, 900);

        // Sección I - Examen físico de trauma
        setTimeout(() => {
            if (window.datosFormularioGuardadoMedico.seccionI) {
                precargarSeccionI(window.datosFormularioGuardadoMedico.seccionI);
            }
        }, 1000);

        // Sección J - Embarazo y parto
        setTimeout(() => {
            if (window.datosFormularioGuardadoMedico.seccionJ) {
                precargarSeccionJ(window.datosFormularioGuardadoMedico.seccionJ);
            }
        }, 1100);

        // Sección K - Exámenes complementarios
        setTimeout(() => {
            if (window.datosFormularioGuardadoMedico.seccionK) {
                precargarSeccionK(window.datosFormularioGuardadoMedico.seccionK);
            }
        }, 1200);

        // Sección L - Diagnósticos presuntivos
        setTimeout(() => {
            if (window.datosFormularioGuardadoMedico.seccionL) {
                precargarSeccionL(window.datosFormularioGuardadoMedico.seccionL);
            }
        }, 1300);

        // Sección M - Diagnósticos definitivos
        setTimeout(() => {
            if (window.datosFormularioGuardadoMedico.seccionM) {
                precargarSeccionM(window.datosFormularioGuardadoMedico.seccionM);
            }
        }, 1400);

        // Sección N - Plan de tratamiento
        setTimeout(() => {
            if (window.datosFormularioGuardadoMedico.seccionN) {
                precargarSeccionN(window.datosFormularioGuardadoMedico.seccionN);
            }
        }, 1500);

        // Sección O - Alta y egreso
        setTimeout(() => {
            if (window.datosFormularioGuardadoMedico.seccionO) {
                precargarSeccionO(window.datosFormularioGuardadoMedico.seccionO);
            }
        }, 1600);

        // Sección P - Profesional responsable
        setTimeout(() => {
            if (window.datosFormularioGuardadoMedico.seccionP) {
                precargarSeccionP(window.datosFormularioGuardadoMedico.seccionP);
            }
        }, 1700);
    }
});

// FUNCIÓN: Verificar si es modificación para médicos
function verificarSiEsModificacionMedico() {
    // 1. Variable window específica
    if (typeof window.esModificacion !== 'undefined' && window.esModificacion === true) {
        return true;
    }
    
    // 2. Input hidden
    const inputModificacion = document.querySelector('input[name="es_modificacion"]');
    if (inputModificacion && inputModificacion.value === '1') {
        return true;
    }
    
    // 3. Verificar habilitado_por_admin
    const formularioUsuario = document.querySelector('input[name="habilitado_por_admin"]');
    if (formularioUsuario && formularioUsuario.value === '1') {
        return true;
    }
    
    return false;
}

// SECCIÓN C: INICIO DE ATENCIÓN
function precargarSeccionC(datos) {    
    if (!datos) return;

    // Si es array, tomar el primer elemento
    if (Array.isArray(datos) && datos.length > 0) {
        datos = datos[0];
    }

    // Fecha de inicio
    if (datos.iat_fecha) {
        const fechaField = document.querySelector('input[name="inicio_atencion_fecha"]');
        if (fechaField) fechaField.value = datos.iat_fecha;
    }

    // Hora de inicio
    if (datos.iat_hora) {
        const horaField = document.querySelector('input[name="inicio_atencion_hora"]');
        if (horaField) horaField.value = datos.iat_hora;
    }

    // Condición de llegada
    if (datos.col_codigo) {
        const condicionSelect = document.querySelector('select[name="inicio_atencion_condicion"]');
        if (condicionSelect) condicionSelect.value = datos.col_codigo;
    }

    // Motivo de atención
    if (datos.iat_motivo) {
        const motivoTextarea = document.querySelector('textarea[name="inicio_atencion_motivo"]');
        if (motivoTextarea) motivoTextarea.value = datos.iat_motivo;
    }
}

// SECCIÓN D: EVENTOS/ACCIDENTES - CORREGIDO MÚLTIPLES CHECKBOXES
function precargarSeccionD(datos) {
    if (!datos) return;

    // PRIMERA PARTE: Extraer datos básicos del evento y tipos de eventos
    let eventoBase = null;
    let tiposEventos = [];
    let datosAtencion = {};

    // Detectar si es un objeto con índices numéricos (similar a array)
    const esObjetoConIndices = typeof datos === 'object' && !Array.isArray(datos) &&
                               Object.keys(datos).some(key => !isNaN(key));

    if (Array.isArray(datos)) {
        // Array verdadero de eventos
        eventoBase = datos[0];
        tiposEventos = datos.map(evento => evento.tev_codigo).filter(codigo => codigo);

        datos.forEach(item => {
            if (item.ate_custodia_policial !== undefined) {
                datosAtencion.ate_custodia_policial = item.ate_custodia_policial;
            }
            if (item.ate_aliento_etilico !== undefined) {
                datosAtencion.ate_aliento_etilico = item.ate_aliento_etilico;
            }
        });

    } else if (esObjetoConIndices) {
        // Convertir a array los elementos con índices numéricos
        const arrayEventos = [];
        Object.keys(datos).forEach(key => {
            if (!isNaN(key)) {
                arrayEventos[parseInt(key)] = datos[key];
            }
        });

        // Filtrar elementos válidos
        const eventosValidos = arrayEventos.filter(evento => evento && evento.tev_codigo);

        if (eventosValidos.length > 0) {
            eventoBase = eventosValidos[0];
            tiposEventos = eventosValidos.map(evento => evento.tev_codigo).filter(codigo => codigo);
        }

        // Extraer datos de atención del objeto principal
        datosAtencion = {
            ate_custodia_policial: datos.ate_custodia_policial,
            ate_aliento_etilico: datos.ate_aliento_etilico
        };

    } else if (typeof datos === 'object') {
        // Objeto único tradicional
        eventoBase = datos;
        if (datos.tev_codigo) {
            tiposEventos.push(datos.tev_codigo);
        }
        datosAtencion = {
            ate_custodia_policial: datos.ate_custodia_policial,
            ate_aliento_etilico: datos.ate_aliento_etilico
        };

    }

    // SEGUNDA PARTE: Llenar campos de texto del evento
    if (eventoBase) {

        // Fecha evento
        if (eventoBase.eve_fecha) {
            const fechaInput = document.querySelector('input[name="acc_fecha_evento"]');
            if (fechaInput) {
                fechaInput.value = eventoBase.eve_fecha;
            }
        }

        // Hora evento
        if (eventoBase.eve_hora) {
            const horaInput = document.querySelector('input[name="acc_hora_evento"]');
            if (horaInput) {
                horaInput.value = eventoBase.eve_hora;
            }
        }

        // Lugar evento
        if (eventoBase.eve_lugar) {
            const lugarInput = document.querySelector('input[name="acc_lugar_evento"]');
            if (lugarInput) {
                lugarInput.value = eventoBase.eve_lugar;
            }
        }

        // Dirección evento
        if (eventoBase.eve_direccion) {
            const direccionInput = document.querySelector('input[name="acc_direccion_evento"]');
            if (direccionInput) {
                direccionInput.value = eventoBase.eve_direccion;
            }
        }

        // Observaciones evento
        if (eventoBase.eve_observacion) {
            const obsTextarea = document.querySelector('textarea[name="acc_observaciones"]');
            if (obsTextarea) {
                obsTextarea.value = eventoBase.eve_observacion;
            }
        }

        // Notificación
        if (eventoBase.eve_notificacion) {
            const valor = eventoBase.eve_notificacion.toLowerCase();
            if (valor === 'si') {
                const radioSi = document.querySelector('input[name="acc_notificacion_custodia"][value="si"]');
                if (radioSi) {
                    radioSi.checked = true;
                }
            } else if (valor === 'no') {
                const radioNo = document.querySelector('input[name="acc_notificacion_custodia"][value="no"]');
                if (radioNo) {
                    radioNo.checked = true;
                }
            }
        }
    }

    // TERCERA PARTE: Marcar checkboxes de tipos de evento
    if (tiposEventos.length > 0) {


        tiposEventos.forEach(codigo => {

            // Buscar el checkbox por value
            let checkbox = document.querySelector(`input[name="tipos_evento[]"][value="${codigo}"]`);

            if (checkbox) {
                checkbox.checked = true;
            }
        });
    }

    // CUARTA PARTE: Manejar radios y checkboxes especiales
    if (datosAtencion.ate_custodia_policial) {
        const valor = datosAtencion.ate_custodia_policial.toUpperCase();
        if (valor === 'SI') {
            const radioSi = document.querySelector('input[name="acc_custodia_policial"][value="si"]');
            if (radioSi) {
                radioSi.checked = true;
            }
        } else if (valor === 'NO') {
            const radioNo = document.querySelector('input[name="acc_custodia_policial"][value="no"]');
            if (radioNo) {
                radioNo.checked = true;
            }
        }
    }

    if (datosAtencion.ate_aliento_etilico === 'SI') {
        const checkAlcohol = document.querySelector('input[name="acc_sugestivo_alcohol"]');
        if (checkAlcohol) {
            checkAlcohol.checked = true;
        }
    }

}

// SECCIÓN E: ANTECEDENTES
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

// SECCIÓN J: EMBARAZO Y PARTO - CORREGIDO PARA SELECTS
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

    // Manejar SELECTS de Presentación y Plano

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

// SECCIÓN N: PLAN DE TRATAMIENTO - CORREGIDO PARA ADMINISTRADO
function precargarSeccionN(datos) {
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
        if (trat.trat_medicamento &&
            trat.trat_medicamento !== 'Plan general' &&
            trat.trat_medicamento !== 'Sin tratamiento específico' &&
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

            // Administrado (nuevo campo)
            if (trat.trat_administrado !== undefined) {
                const administradoHidden = document.querySelector(`input[name="trat_administrado${numTrat}"]`);
                const administradoButton = document.querySelector(`button[data-row="${numTrat}"]`);

                if (administradoHidden && administradoButton) {
                    const esAdministrado = trat.trat_administrado == '1' || trat.trat_administrado === 1;

                    // Establecer valor hidden
                    administradoHidden.value = esAdministrado ? '1' : '0';

                    if (esAdministrado) {
                        // Marcar como administrado (verde activo)
                        administradoButton.classList.remove('bg-green-100', 'hover:bg-green-200', 'text-green-600', 'hover:text-green-700');
                        administradoButton.classList.add('bg-green-600', 'hover:bg-green-700', 'text-white');
                        administradoButton.setAttribute('title', 'Administrado');

                        // Cambiar icono a check relleno
                        const svg = administradoButton.querySelector('svg path');
                        if (svg) svg.setAttribute('d', 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z');

                    }
                }
            }

            // ID del tratamiento (si existe)
            if (trat.trat_id) {
                const tratIdHidden = document.querySelector(`input[name="trat_id${numTrat}"]`);
                if (tratIdHidden) {
                    tratIdHidden.value = trat.trat_id;
                }
            }

            indexTratamiento++;
        }
    });

}

// SECCIÓN O: ALTA Y EGRESO - CORREGIDO PARA MÚLTIPLES CHECKBOXES
function precargarSeccionO(datos) {
    if (!datos) return;

    let datosEgreso = null;
    let estadosEgreso = [];
    let modalidadesEgreso = [];
    let tiposEgreso = [];

    // Manejar array de datos como las secciones E y K
    if (Array.isArray(datos)) {
        // Los datos vienen como array
        datosEgreso = datos[0]; // Datos básicos del primer elemento

        // Extraer códigos de estados, modalidades y tipos
        estadosEgreso = datos.map(item => item.ese_codigo).filter(codigo => codigo);
        modalidadesEgreso = datos.map(item => item.moe_codigo).filter(codigo => codigo);
        tiposEgreso = datos.map(item => item.tie_codigo).filter(codigo => codigo);

    } else if (typeof datos === 'object') {
        // Objeto único
        datosEgreso = datos;

        if (datos.ese_codigo) estadosEgreso.push(datos.ese_codigo);
        if (datos.moe_codigo) modalidadesEgreso.push(datos.moe_codigo);
        if (datos.tie_codigo) tiposEgreso.push(datos.tie_codigo);
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

    // Llenar campos de texto (del primer elemento)
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

}

// SECCIÓN P: PROFESIONAL RESPONSABLE - CORREGIDO PARA CARGAR ARCHIVOS
function precargarSeccionP(datos) {
    if (!datos) return;

    // Si es array, tomar el primer elemento
    if (Array.isArray(datos) && datos.length > 0) {
        datos = datos[0];
    }

    // Campos de texto del profesional
    const camposProfesional = {
        'prof_fecha': datos.pro_fecha,
        'prof_hora': datos.pro_hora,
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
                    baseUrl = window.location.origin + '/Formulario-Digital/';
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

        // Usar el div de preview existente o crear uno nuevo
        let previewDiv = existingPreviewDiv;
        if (!previewDiv) {
            previewDiv = document.createElement('div');
            previewDiv.className = 'custom-image-preview mt-2';
            previewDiv.id = `${fieldType}-preview`;
        }

        const img = document.createElement('img');
        img.src = imgSrc;
        img.alt = titulo;
        img.className = 'max-w-full max-h-24 border border-gray-300 rounded shadow-sm cursor-pointer';

        // Manejar errores de carga de imagen
        img.onerror = function() {
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

        img.onclick = function() {
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
        changeButton.onclick = function() {
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

// Exportar funciones para uso externo
window.precargarModificacionMedico = {
    verificarSiEsModificacion: verificarSiEsModificacionMedico,
    precargarSeccionC: precargarSeccionC,
    precargarSeccionD: precargarSeccionD,
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