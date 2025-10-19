// ========================================
// PRECARGAR DATOS PROCESO PARCIAL - ESPECIALIDADES (CORREGIDO COMPLETO)
// ========================================

document.addEventListener('DOMContentLoaded', function () {

    // VERIFICAR SI ES CONTINUACIÓN DE PROCESO O ENFERMERÍA CON DATOS DEL ESPECIALISTA
    const esContinuacionProceso = window.esContinuacionProceso === true;
    const esEnfermeriaConDatos = window.contextoEnfermeria === true &&
        window.datosFormularioGuardadoEspecialista &&
        Object.keys(window.datosFormularioGuardadoEspecialista).length > 0;

    // NO EJECUTAR en contexto de OBSERVACIÓN (tiene su propio script de precarga)
    const esObservacion = window.contextoObservacion === true ||
        window.esObservacionEmergencia === true ||
        window.esp_codigo === 5 ||
        window.especialidad_codigo === 5;

    if (esObservacion) {
        // console.log('⏭️ precargarDatosProcesoParcial.js: Saltando ejecución - Contexto de Observación detectado');
        return;
    }

    if (!esContinuacionProceso && !esEnfermeriaConDatos) {
        return;
    }

    // VERIFICAR MÚLTIPLES FUENTES DE DATOS
    let datosParaPrecargar = null;

    // Primera prioridad: datos del proceso parcial específico
    if (typeof window.datosFormularioGuardadoEspecialista !== 'undefined' &&
        window.datosFormularioGuardadoEspecialista) {
        datosParaPrecargar = window.datosFormularioGuardadoEspecialista;
    }

    // Segunda prioridad: sessionStorage
    if (!datosParaPrecargar) {
        const datosSessionStorage = sessionStorage.getItem('datosProcesoParcialEspecialidad');
        if (datosSessionStorage) {
            try {
                datosParaPrecargar = JSON.parse(datosSessionStorage);
            } catch (e) {
                // Error parseando datos de sessionStorage
            }
        }
    }

    // Si hay datos, precargar con delay apropiado
    const tieneDatos = datosParaPrecargar &&
        ((Array.isArray(datosParaPrecargar) && datosParaPrecargar.length > 0) ||
            (typeof datosParaPrecargar === 'object' && Object.keys(datosParaPrecargar).length > 0));

    if (tieneDatos) {
        // Solo precargar datos del proceso parcial (secciones E-N)
        // Los datos básicos (A,B,C,D,G) ya los maneja precargarDatosEspecialidad.js
        setTimeout(() => {
            precargarDatosProcesoParcialCompleto(datosParaPrecargar);
        }, 1400); // Delay mayor para que cargue después de los datos básicos
    } else {
        // IMPORTANTE: Aunque no haya datos de secciones, intentar cargar firma y sello
        setTimeout(() => {
            cargarFirmaYSelloGuardados();
        }, 1500);
    }
});

/**
 * Función principal para precargar TODOS los datos del proceso parcial
 */
function precargarDatosProcesoParcialCompleto(datos) {
    if (!datos || typeof datos !== 'object') {
        return;
    }

    // Precargar secciones del especialista (E-N)
    const secciones = [
        { letra: 'E', funcion: precargarSeccionE, delay: 100 },
        { letra: 'F', funcion: precargarSeccionF, delay: 200 },
        { letra: 'H', funcion: precargarSeccionH, delay: 300 },
        { letra: 'I', funcion: precargarSeccionI, delay: 400 },
        { letra: 'J', funcion: precargarSeccionJ, delay: 500 },
        { letra: 'K', funcion: precargarSeccionK, delay: 600 },
        { letra: 'L', funcion: precargarSeccionL, delay: 700 },
        { letra: 'M', funcion: precargarSeccionM, delay: 800 },
        { letra: 'N', funcion: precargarSeccionN, delay: 900 }
    ];

    let seccionesProcesadas = 0;
    let seccionesExitosas = 0;

    secciones.forEach(seccion => {
        setTimeout(() => {
            const datoSeccion = datos[`seccion${seccion.letra}`];
            seccionesProcesadas++;

            if (datoSeccion && (Array.isArray(datoSeccion) ? datoSeccion.length > 0 : Object.keys(datoSeccion).length > 0)) {
                try {
                    seccion.funcion(datoSeccion);
                    seccionesExitosas++;
                } catch (error) {
                    // Error precargando sección
                }
            }

            // Mostrar resumen final
            if (seccionesProcesadas === secciones.length) {
                mostrarResumenPrecarga(seccionesExitosas, secciones.length);
            }
        }, seccion.delay);
    });
}



// ========================================
// FUNCIONES AUXILIARES
// ========================================

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

// ========================================
// FUNCIONES DE PRECARGA DE SECCIONES DEL ESPECIALISTA (E-N)
// ========================================

/**
 * Sección E - Antecedentes
 */
function precargarSeccionE(datos) {
    if (!Array.isArray(datos) || datos.length === 0) {
        return;
    }

    // Limpiar selecciones previas
    document.querySelectorAll('input[name="antecedentes[]"]').forEach(cb => cb.checked = false);
    const checkNoAplica = document.querySelector('input[name="ant_no_aplica"]');
    if (checkNoAplica) checkNoAplica.checked = false;

    let descripcionGuardada = '';

    datos.forEach(antecedente => {
        if (parseInt(antecedente.ap_no_aplica) === 1) {
            if (checkNoAplica) {
                checkNoAplica.checked = true;
            }
        } else if (antecedente.tan_codigo) {
            const checkbox = document.querySelector(`input[name="antecedentes[]"][value="${antecedente.tan_codigo}"]`);
            if (checkbox) {
                checkbox.checked = true;
            }
        }

        if (antecedente.ap_descripcion &&
            antecedente.ap_descripcion !== 'No aplica' &&
            antecedente.ap_descripcion.length > descripcionGuardada.length) {
            descripcionGuardada = antecedente.ap_descripcion;
        }
    });

    if (descripcionGuardada) {
        const descripcionField = document.querySelector('textarea[name="ant_descripcion"]');
        if (descripcionField) {
            descripcionField.value = descripcionGuardada;
        }
    }
}

/**
 * Sección F - Problema actual
 */
function precargarSeccionF(datos) {
    const problema = Array.isArray(datos) ? datos[0] : datos;

    if (!problema || !problema.pro_descripcion) {
        return;
    }

    const descripcionField = document.querySelector('textarea[name="ep_descripcion_actual"]');
    if (descripcionField) {
        descripcionField.value = problema.pro_descripcion;
    }
}

/**
 * Sección H - Examen físico
 */
function precargarSeccionH(datos) {
    if (!Array.isArray(datos) || datos.length === 0) {
        return;
    }

    // NO LIMPIAR checkboxes en contexto de enfermería
    // Enfermería no modifica sección H, solo ve lo que el especialista marcó
    const esEnfermeria = window.contextoEnfermeria === true || window.esEnfermeriaEspecialidad === true;

    if (!esEnfermeria) {
        // Solo limpiar cuando NO es enfermería
        document.querySelectorAll('input[name="zonas_examen_fisico[]"]').forEach(cb => cb.checked = false);
    }

    let descripcionGuardada = '';
    let zonasSeleccionadas = 0;

    datos.forEach(examen => {
        if (examen.ef_presente) {
            const checkbox = document.querySelector(`input[name="zonas_examen_fisico[]"][value="${examen.zef_codigo}"]`);
            if (checkbox) {
                checkbox.checked = true;
                zonasSeleccionadas++;
            }
        }

        if (examen.ef_descripcion && examen.ef_descripcion.length > descripcionGuardada.length) {
            descripcionGuardada = examen.ef_descripcion;
        }
    });

    if (descripcionGuardada) {
        const descripcionField = document.querySelector('textarea[name="ef_descripcion"]');
        if (descripcionField) {
            descripcionField.value = descripcionGuardada;
        }
    }

}

/**
 * Sección I - Trauma
 */
function precargarSeccionI(datos) {
    const trauma = Array.isArray(datos) ? datos[0] : datos;

    if (!trauma || !trauma.tra_descripcion) {
        return;
    }

    const descripcionField = document.querySelector('textarea[name="eft_descripcion"]');
    if (descripcionField) {
        descripcionField.value = trauma.tra_descripcion;
    }
}

/**
 * Sección J - Embarazo
 */
function precargarSeccionJ(datos) {
    const embarazo = Array.isArray(datos) ? datos[0] : datos;

    if (!embarazo) {
        return;
    }

    // No aplica
    if (parseInt(embarazo.emb_no_aplica) === 1) {
        const checkNoAplica = document.querySelector('input[name="emb_no_aplica"]');
        if (checkNoAplica) {
            checkNoAplica.checked = true;
            return;
        }
    }

    // Campos de texto/número/select
    const camposTexto = {
        'emb_gestas': embarazo.emb_numero_gestas,
        'emb_partos': embarazo.emb_numero_partos,
        'emb_abortos': embarazo.emb_numero_abortos,
        'emb_cesareas': embarazo.emb_numero_cesareas,
        'emb_semanas_gestacion': embarazo.emb_semanas_gestacion,
        'emb_fcf': embarazo.emb_frecuencia_cardiaca_fetal,
        'emb_tiempo_ruptura': embarazo.emb_tiempo,
        'emb_afu': embarazo.emb_afu,
        'emb_presentacion': embarazo.emb_presentacion,
        'emb_dilatacion': embarazo.emb_dilatacion,
        'emb_borramiento': embarazo.emb_borramiento,
        'emb_plano': embarazo.emb_plano,
        'emb_score_mama': embarazo.emb_score_mama,
        'emb_observaciones': embarazo.emb_observaciones
    };

    Object.entries(camposTexto).forEach(([campo, valor]) => {
        if (valor !== null && valor !== undefined && valor !== '') {
            const element = document.querySelector(`input[name="${campo}"], textarea[name="${campo}"], select[name="${campo}"]`);
            if (element) {
                element.value = valor;
            }
        }
    });

    // Campos de radio
    const camposRadio = {
        'emb_movimiento_fetal': embarazo.emb_movimiento_fetal,
        'emb_ruptura_membranas': embarazo.emb_ruptura_menbranas,
        'emb_sangrado_vaginal': embarazo.emb_sangrado_vaginal,
        'emb_contracciones': embarazo.emb_contracciones,
        'emb_pelvis_viable': embarazo.emb_pelvis_viable
    };

    Object.entries(camposRadio).forEach(([campo, valor]) => {
        if (valor) {
            const radio = document.querySelector(`input[name="${campo}"][value="${valor.toLowerCase()}"]`);
            if (radio) {
                radio.checked = true;
            }
        }
    });

    // Fecha FUM
    if (embarazo.emb_fum && embarazo.emb_fum !== "0000-00-00") {
        const fumField = document.querySelector('input[name="emb_fum"]');
        if (fumField) {
            fumField.value = embarazo.emb_fum;
        }
    }
}

/**
 * Sección K - Exámenes complementarios
 */
function precargarSeccionK(datos) {
    if (!Array.isArray(datos) || datos.length === 0) {
        return;
    }

    document.querySelectorAll('input[name="tipos_examenes[]"]').forEach(cb => cb.checked = false);

    const hayNoAplica = datos.some(exam => parseInt(exam.exa_no_aplica) === 1);

    if (hayNoAplica) {
        const checkNoAplica = document.querySelector('input[name="exc_no_aplica"]');
        if (checkNoAplica) {
            checkNoAplica.checked = true;
        }
    } else {
        datos.forEach(examen => {
            if (examen.tipo_id) {
                const checkbox = document.querySelector(`input[name="tipos_examenes[]"][value="${examen.tipo_id}"]`);
                if (checkbox) {
                    checkbox.checked = true;
                }
            }
        });
    }

    const observaciones = datos.find(exam => exam.exa_observaciones && exam.exa_observaciones !== 'No aplica');
    if (observaciones) {
        const observacionesField = document.querySelector('textarea[name="exc_observaciones"]');
        if (observacionesField) {
            observacionesField.value = observaciones.exa_observaciones;
        }
    }
}

/**
 * Sección L - Diagnósticos presuntivos
 */
function precargarSeccionL(datos) {
    if (!Array.isArray(datos) || datos.length === 0) {
        return;
    }


    const diagnosticosValidos = datos.filter(diag =>
        diag.diagp_descripcion &&
        diag.diagp_descripcion !== 'Sin diagnóstico presuntivo específico'
    );

    diagnosticosValidos.forEach((diag, index) => {
        if (index < 3) {
            const numDiag = index + 1;

            const descField = document.querySelector(`input[name="diag_pres_desc${numDiag}"], textarea[name="diag_pres_desc${numDiag}"]`);
            if (descField) {
                descField.value = diag.diagp_descripcion;
            }

            if (diag.diagp_cie) {
                const cieField = document.querySelector(`input[name="diag_pres_cie${numDiag}"]`);
                if (cieField) cieField.value = diag.diagp_cie;
            }
        }
    });
}

/**
 * Sección M - Diagnósticos definitivos
 */
function precargarSeccionM(datos) {
    if (!Array.isArray(datos) || datos.length === 0) {
        return;
    }

    const diagnosticosValidos = datos.filter(diag =>
        diag.diagd_descripcion &&
        diag.diagd_descripcion !== 'Sin diagnóstico definitivo específico'
    );

    diagnosticosValidos.forEach((diag, index) => {
        if (index < 3) {
            const numDiag = index + 1;

            const descField = document.querySelector(`input[name="diag_def_desc${numDiag}"], textarea[name="diag_def_desc${numDiag}"]`);
            if (descField) {
                descField.value = diag.diagd_descripcion;
            }

            if (diag.diagd_cie) {
                const cieField = document.querySelector(`input[name="diag_def_cie${numDiag}"]`);
                if (cieField) cieField.value = diag.diagd_cie;
            }
        }
    });
}

/**
 * Sección N - Tratamientos
 */
function precargarSeccionN(datos) {
    if (!Array.isArray(datos) || datos.length === 0) {
        return;
    }

    // Plan de tratamiento general
    if (datos[0] && datos[0].trat_observaciones) {
        const observacionesField = document.querySelector('textarea[name="plan_tratamiento"]');
        if (observacionesField) {
            observacionesField.value = datos[0].trat_observaciones;
        }
    }

    // Tratamientos específicos
    let indexTrat = 0;

    datos.forEach(trat => {
        if (trat.trat_medicamento &&
            trat.trat_medicamento !== 'Plan de tratamiento' &&
            !esRegistroVacioSinTratamiento(trat) &&
            indexTrat < 7) {

            const numTrat = indexTrat + 1;

            const medField = document.querySelector(`input[name="trat_med${numTrat}"]`);
            if (medField) {
                medField.value = trat.trat_medicamento;
            }

            const campos = [
                { name: `trat_via${numTrat}`, value: trat.trat_via },
                { name: `trat_dosis${numTrat}`, value: trat.trat_dosis },
                { name: `trat_posologia${numTrat}`, value: trat.trat_posologia },
                { name: `trat_dias${numTrat}`, value: trat.trat_dias },
                { name: `trat_id${numTrat}`, value: trat.trat_id }
            ];

            campos.forEach(campo => {
                if (campo.value) {
                    const element = document.querySelector(`select[name="${campo.name}"], input[name="${campo.name}"]`);
                    if (element) element.value = campo.value;
                }
            });

            // AGREGAR: Campo ADMINISTRADO
            if (trat.trat_administrado !== undefined && trat.trat_administrado !== null) {
                const administradoField = document.querySelector(`input[name="trat_administrado${numTrat}"]`);
                const btnAdministrado = document.getElementById(`btn_administrado${numTrat}`);

                if (administradoField && btnAdministrado) {
                    administradoField.value = trat.trat_administrado;

                    // Actualizar visual del botón según el valor
                    if (trat.trat_administrado == 1) {
                        btnAdministrado.classList.remove('bg-green-100', 'hover:bg-green-200', 'text-green-600', 'hover:text-green-700');
                        btnAdministrado.classList.add('bg-green-600', 'hover:bg-green-700', 'text-white');
                        btnAdministrado.setAttribute('title', 'Administrado');

                        // Cambiar icono
                        const svg = btnAdministrado.querySelector('svg path');
                        if (svg) svg.setAttribute('d', 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z');
                    }

                }
            }

            indexTrat++;
        }
    });

    // NUEVA FUNCIONALIDAD: Cargar firma y sello guardados del proceso
    setTimeout(() => {
        cargarFirmaYSelloGuardados();
    }, 200);
}


// ========================================
// FUNCIONES DE UTILIDAD
// ========================================

/**
 * Mostrar resumen de precarga
 */
function mostrarResumenPrecarga(exitosas, total) {
    const porcentaje = Math.round((exitosas / total) * 100);
    const mensaje = `Precarga completada: ${exitosas}/${total} secciones (${porcentaje}%)`;

    // No mostrar notificación en contexto de enfermería (ya tiene su propia notificación específica)
    if (window.contextoEnfermeria && window.esEnfermeriaEspecialidad) {
        return;
    }

    mostrarNotificacionPrecarga(mensaje);
}

/**
 * Mostrar notificación visual
 */
function mostrarNotificacionPrecarga(mensaje) {
    const notificacion = document.createElement('div');
    notificacion.className = 'fixed top-4 right-4 z-50 bg-green-500 text-white px-4 py-3 rounded-lg shadow-lg';
    notificacion.innerHTML = `
        <div class="flex items-center">
            <span class="mr-2"></span>
            <span>${mensaje}</span>
        </div>
    `;

    document.body.appendChild(notificacion);

    setTimeout(() => {
        if (notificacion.parentNode) {
            notificacion.parentNode.removeChild(notificacion);
        }
    }, 5000);
}

// Exportar funciones - Solo para secciones E-N del especialista
window.precargarProcesoParcial = {
    precargarDatos: precargarDatosProcesoParcialCompleto
};

/**
 * FUNCIÓN MEJORADA: Cargar firma y sello guardados del proceso en Sección N
 * Usando la misma lógica robusta que precargarDatosModificacionEspecialista.js
 */
function cargarFirmaYSelloGuardados() {
    if (typeof window.medico_que_guardo_proceso === 'undefined' || !window.medico_que_guardo_proceso) {
        return;
    }

    const firmaUrl = window.medico_que_guardo_proceso.firma_url;
    const selloUrl = window.medico_que_guardo_proceso.sello_url;

    if (firmaUrl && firmaUrl.trim() !== '') {
        manejarImagenProfesional('esp_firma_n', firmaUrl, true, 'Firma');
    }

    if (selloUrl && selloUrl.trim() !== '') {
        manejarImagenProfesional('esp_sello_n', selloUrl, true, 'Sello');
    }
}

/**
 * FUNCIÓN: Manejar imagen profesional (firma/sello) - Copiada de precargarDatosModificacionEspecialista.js
 */
function manejarImagenProfesional(fieldName, imagenBase64, existe, titulo) {

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
        messageDiv.className = 'image-message mt-2 p-2 bg-green-50 border border-green-200 rounded text-xs text-green-700';
        messageDiv.innerHTML = `
            <i class="fas fa-info-circle mr-1"></i>
            ${titulo} cargado/a desde registro anterior.
            <strong>No es necesario volver a subirlo/a.</strong>
        `;
        container.appendChild(messageDiv);

    } else {
        // No existe imagen - mostrar input normalmente
        inputField.style.display = 'block';

        // Limpiar preview si existe
        if (existingPreviewDiv) {
            existingPreviewDiv.innerHTML = '';
            existingPreviewDiv.classList.remove('has-image');
        }
    }
}
