// ========================================
// ENVIO OBSERVACION.JS - FUNCIONALIDAD COMPLETA PARA ENVÍO A OBSERVACIÓN
// ========================================

$(document).ready(function () {
    inicializarEventosObservacion();
});

/**
 * Inicializar todos los eventos relacionados con observación
 */
function inicializarEventosObservacion() {
    // 🔥 EVENTO: Checkbox observación de emergencia
    $(document).on('change', '#egreso_observacion_emergencia', function () {
        if (this.checked) {
            mostrarModalObservacion();
        }
    });

    // 🔥 EVENTOS DEL MODAL
    $(document).on('click', '#btn-cerrar-modal, #btn-cancelar-observacion', function () {
        cerrarModalObservacion();
    });

    $(document).on('click', '#btn-confirmar-observacion', function () {
        enviarAObservacionConDatos();
    });

    // 🔥 EVENTO: Validación en tiempo real del motivo
    $(document).on('input', '#motivo_observacion', function () {
        validarMotivoObservacion();
    });
}

/**
 * Mostrar modal de observación
 */
function mostrarModalObservacion() {
    // Verificar que tenemos el are_codigo
    const areCodigoInput = document.querySelector('input[name="are_codigo"]');
    if (!areCodigoInput || !areCodigoInput.value) {
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: 'No se encontró el código del área. No se puede enviar a observación.',
            confirmButtonText: 'Aceptar'
        });
        const checkbox = document.getElementById('egreso_observacion_emergencia');
        if (checkbox) checkbox.checked = false;
        return;
    }

    // Buscar secciones O y P por diferentes métodos - agregando más selectores
    let seccionO = document.querySelector('.seccion-o') ||
        document.querySelector('div:has(table thead tr th[colspan="7"]:contains("O. CONDICIÓN AL EGRESO"))') ||
        document.querySelector('table:has(th:contains("O. CONDICIÓN AL EGRESO"))') ||
        document.getElementById('seccion-o') ||
        document.getElementById('seccion_o');

    // Si no encontramos el contenedor, buscar directamente la tabla de egreso
    if (!seccionO) {
        // Buscar por el contenido específico del header
        const tables = document.querySelectorAll('table');
        for (let table of tables) {
            const header = table.querySelector('th[colspan="7"]');
            if (header && header.textContent.includes('CONDICIÓN AL EGRESO')) {
                seccionO = table.closest('div') || table;
                break;
            }
        }
    }

    if (seccionO) {

        // Verificar múltiples formas de estar oculta
        const computedStyle = window.getComputedStyle(seccionO);
        const estaOculta = seccionO.style.display === 'none' ||
            seccionO.classList.contains('hidden') ||
            computedStyle.display === 'none' ||
            computedStyle.visibility === 'hidden';
        if (estaOculta) {
            seccionO.style.display = 'block';
            seccionO.style.visibility = 'visible';
            seccionO.classList.remove('hidden');
        }

        // Verificar si ahora los elementos de la sección O son visibles
        const elementosEstados = seccionO.querySelectorAll('input[name="estados_egreso[]"]');
    }

    // Limpiar modal y mostrar
    $('#motivo_observacion').val('').removeClass('border-red-300');
    $('#error-motivo').addClass('hidden');
    $('#modalObservacion').removeClass('hidden').addClass('flex');

    // Focus en el campo de motivo
    setTimeout(() => {
        $('#motivo_observacion').focus();
    }, 100);
}

/**
 * Cerrar modal de observación
 */
function cerrarModalObservacion() {
    $('#modalObservacion').addClass('hidden').removeClass('flex');
    $('#motivo_observacion').val('').removeClass('border-red-300');
    $('#error-motivo').addClass('hidden');

    // Desmarcar checkbox si está marcado
    const checkbox = document.getElementById('egreso_observacion_emergencia');
    if (checkbox) {
        checkbox.checked = false;
    }
}

/**
 * Validar motivo de observación
 */
function validarMotivoObservacion() {
    const motivo = $('#motivo_observacion').val().trim();
    const errorDiv = $('#error-motivo');
    const input = $('#motivo_observacion');

    if (!motivo) {
        input.addClass('border-red-300');
        errorDiv.text('El motivo es obligatorio').removeClass('hidden');
        return false;
    }

    if (motivo.length < 5) {
        input.addClass('border-red-300');
        errorDiv.text('El motivo debe tener al menos 5 caracteres').removeClass('hidden');
        return false;
    }

    if (motivo.length > 500) {
        input.addClass('border-red-300');
        errorDiv.text('El motivo no puede exceder 500 caracteres').removeClass('hidden');
        return false;
    }

    input.removeClass('border-red-300');
    errorDiv.addClass('hidden');
    return true;
}

/**
 * Recopilar datos del formulario antes del envío
 */
function recopilarDatosFormulario() {
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
                // Incluir campo trat_administrado para En Atención y Continuando Proceso
                const administrado = document.getElementById(`trat_administrado${i}`)?.value || '0';

                datos.seccionN.tratamientos.push({
                    medicamento: medicamento,
                    via: document.getElementById(`trat_via${i}`)?.value || '',
                    dosis: document.getElementById(`trat_dosis${i}`)?.value || '',
                    posologia: document.getElementById(`trat_posologia${i}`)?.value || '',
                    dias: document.getElementById(`trat_dias${i}`)?.value || '',
                    administrado: parseInt(administrado) || 0
                });
            }
        }


        // Buscar todos los elementos
        const todosEstados = document.querySelectorAll('input[name="estados_egreso[]"]');
        const todasModalidades = document.querySelectorAll('input[name="modalidades_egreso[]"]');
        const todosTipos = document.querySelectorAll('input[name="tipos_egreso[]"]');

        const estadosEgreso = Array.from(document.querySelectorAll('input[name="estados_egreso[]"]:checked')).map(cb => cb.value);
        const modalidadesEgreso = Array.from(document.querySelectorAll('input[name="modalidades_egreso[]"]:checked')).map(cb => cb.value);
        const tiposEgreso = Array.from(document.querySelectorAll('input[name="tipos_egreso[]"]:checked')).map(cb => cb.value);

        // Solo incluir seccionO si hay elementos o si hay datos de texto
        const establecimiento = document.getElementById('egreso_establecimiento')?.value || '';
        const observaciones = document.getElementById('egreso_observacion')?.value || '';
        const diasReposo = document.getElementById('egreso_dias_reposo')?.value || '';

        const tieneElementos = todosEstados.length > 0 || todasModalidades.length > 0 || todosTipos.length > 0;
        const tieneDatos = estadosEgreso.length > 0 || modalidadesEgreso.length > 0 || tiposEgreso.length > 0 || establecimiento || observaciones || diasReposo;

        if (tieneElementos || tieneDatos) {
            datos.seccionO = {
                // Recopilar múltiples estados de egreso
                estados_egreso: estadosEgreso,
                // Recopilar múltiples modalidades de egreso
                modalidades_egreso: modalidadesEgreso,
                // Recopilar múltiples tipos de egreso
                tipos_egreso: tiposEgreso,
                // Campos de texto
                establecimiento: establecimiento,
                observaciones: observaciones,
                dias_reposo: diasReposo,
                // Checkbox de observación de emergencia
                observacion_emergencia: document.getElementById('egreso_observacion_emergencia')?.checked || false
            };

        }

        // Datos del especialista (si están completos)
        const especialistaNombre = document.getElementById('esp_primer_nombre_n')?.value || '';
        const especialistaApellido = document.getElementById('esp_primer_apellido_n')?.value || '';

        if (especialistaNombre && especialistaApellido) {
            datos.especialista = {
                primer_nombre: especialistaNombre,
                primer_apellido: especialistaApellido,
                segundo_apellido: document.getElementById('esp_segundo_apellido_n')?.value || '',
                documento: document.getElementById('esp_documento_n')?.value || '',
                especialidad: document.getElementById('esp_especialidad_n')?.value || '',
                fecha: document.getElementById('esp_fecha_n')?.value || '',
                hora: document.getElementById('esp_hora_n')?.value || ''
            };
        }

        return datos;

    } catch (error) {
        console.error('❌ Error recopilando datos del formulario:', error);
        return {};
    }
}

/**
 * Enviar a observación con datos guardados
 */
function enviarAObservacionConDatos() {
    // Validar motivo
    if (!validarMotivoObservacion()) {
        return;
    }

    const motivo = $('#motivo_observacion').val().trim();
    const areCodigoInput = document.querySelector('input[name="are_codigo"]');

    if (!areCodigoInput || !areCodigoInput.value) {
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: 'No se encontró el código del área',
            confirmButtonText: 'Aceptar'
        });
        return;
    }

    const are_codigo = areCodigoInput.value;

    // Mostrar confirmación detallada
    Swal.fire({
        icon: 'question',
        title: 'Confirmar envío a observación',
        html:
            '<div class="text-left whitespace-pre-line">' +
            '<strong>CONFIRMACIÓN DE ENVÍO A OBSERVACIÓN</strong>\n\n' +
            'Se realizarán las siguientes acciones:\n\n' +
            '<strong class="text-green-600">✅ GUARDAR DATOS:</strong>\n' +
            '   • Todas las secciones completadas del formulario\n' +
            '   • Datos del paciente y evaluación actual\n' +
            '   • Información del especialista (si está completa)\n\n' +
            '<strong class="text-blue-600">📤 ENVIAR A OBSERVACIÓN:</strong>\n' +
            '   • El paciente será transferido al módulo de observación\n' +
            '   • Se registrará el motivo especificado\n' +
            '   • La atención en esta especialidad finalizará\n\n' +
            '<strong class="text-orange-600">⚠️ IMPORTANTE:</strong>\n' +
            '   • Esta acción no se puede deshacer\n' +
            '   • Los datos se guardarán automáticamente\n\n' +
            '¿Está seguro de continuar?' +
            '</div>',
        showCancelButton: true,
        confirmButtonText: 'Sí, enviar a observación',
        cancelButtonText: 'Cancelar',
        confirmButtonColor: '#10b981',
        cancelButtonColor: '#6b7280',
        width: '600px'
    }).then((result) => {
        if (!result.isConfirmed) {
            return;
        }

        // Continuar con el envío si confirma
        ejecutarEnvioObservacion(are_codigo, motivo);
    });
}

// Función auxiliar para ejecutar el envío a observación
function ejecutarEnvioObservacion(are_codigo, motivo) {

    // Deshabilitar botón y mostrar carga
    const btn = $('#btn-confirmar-observacion');
    const originalText = btn.html();
    btn.prop('disabled', true);
    btn.html('<i class="fas fa-spinner fa-spin mr-2"></i>Guardando y enviando...');

    // Recopilar datos del formulario
    const datosFormulario = recopilarDatosFormulario();

    // Preparar datos para envío
    const datosEnvio = {
        are_codigo: are_codigo,
        motivo_observacion: motivo,
        datos_formulario: JSON.stringify(datosFormulario)
    };

    // EXTRAER DATOS DE SECCIÓN O PARA ENVÍO DIRECTO AL CONTROLADOR
    if (datosFormulario.seccionO) {

        // Enviar arrays de checkboxes como campos individuales POST
        if (datosFormulario.seccionO.estados_egreso && datosFormulario.seccionO.estados_egreso.length > 0) {
            datosEnvio['estados_egreso'] = datosFormulario.seccionO.estados_egreso;
        }

        if (datosFormulario.seccionO.modalidades_egreso && datosFormulario.seccionO.modalidades_egreso.length > 0) {
            datosEnvio['modalidades_egreso'] = datosFormulario.seccionO.modalidades_egreso;
        }

        if (datosFormulario.seccionO.tipos_egreso && datosFormulario.seccionO.tipos_egreso.length > 0) {
            datosEnvio['tipos_egreso'] = datosFormulario.seccionO.tipos_egreso;
        }

        // Enviar campos de texto adicionales si existen
        if (datosFormulario.seccionO.observaciones) {
            datosEnvio['egreso_observacion'] = datosFormulario.seccionO.observaciones;
        }

        if (datosFormulario.seccionO.establecimiento) {
            datosEnvio['egreso_establecimiento'] = datosFormulario.seccionO.establecimiento;
        }

        if (datosFormulario.seccionO.dias_reposo) {
            datosEnvio['egreso_dias_reposo'] = datosFormulario.seccionO.dias_reposo;
        }

    }

    // Realizar petición AJAX
    $.ajax({
        url: window.base_url + 'especialidades/observacion/enviarAObservacionConDatos',
        method: 'POST',
        data: datosEnvio,
        dataType: 'json',
        success: function (response) {

            if (response.success) {
                // Cerrar modal
                cerrarModalObservacion();

                // Mostrar mensaje de éxito
                Swal.fire({
                    icon: 'success',
                    title: 'Envío Exitoso',
                    html: '✅ Datos guardados correctamente<br>' +
                        '📤 Paciente enviado a observación<br>' +
                        '🏥 Disponible en módulo de observación<br><br>' +
                        'Redirigiendo a la lista de especialidades...',
                    timer: 2000,
                    showConfirmButton: false
                }).then(() => {
                    window.location.href = window.base_url + 'especialidades/lista';
                });

            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: response.error || 'Error desconocido',
                    confirmButtonText: 'Aceptar'
                });
                console.error('❌ Error del servidor:', response);
            }
        },
        error: function (xhr, status, error) {
            console.error('❌ Error AJAX:', error);
            console.error('Respuesta completa:', xhr.responseText);

            let mensajeError = 'Error de comunicación con el servidor';

            try {
                const errorResponse = JSON.parse(xhr.responseText);
                if (errorResponse.error) {
                    mensajeError = errorResponse.error;
                }
            } catch (e) {
                // Si no se puede parsear la respuesta, usar mensaje genérico
            }

            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: mensajeError,
                confirmButtonText: 'Aceptar'
            });
        },
        complete: function () {
            // Restaurar botón siempre
            btn.prop('disabled', false);
            btn.html(originalText);
        }
    });
}

// Funciones globales para compatibilidad
window.mostrarModalObservacion = mostrarModalObservacion;
window.cerrarModalObservacion = cerrarModalObservacion;
window.enviarAObservacionConDatos = enviarAObservacionConDatos;
window.confirmarEnvioObservacion = enviarAObservacionConDatos; // Alias para compatibilidad

/**
 * Función para agregar el manejador del checkbox observación dinámicamente
 */
function manejarObservacionEmergencia(checkbox) {
    if (checkbox.checked) {
        mostrarModalObservacion();
    }
}

// Exponer globalmente para uso en HTML inline
window.manejarObservacionEmergencia = manejarObservacionEmergencia;