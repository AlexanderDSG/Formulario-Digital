// ===== SISTEMA COMPLETO DE GENERACIÓN DE PDF - VERSIÓN DINÁMICA =====

// === CONFIGURACIÓN GLOBAL ===
const PDF_CONFIG = {
    DEBUG: true,
    PAGE_WIDTH: 765,
    PAGE_HEIGHT: 1050,
    FONT_SIZE: 7,
};

// === ESTADO GLOBAL ===
window.pdfSystemLoaded = false;
window.pdfSystemInitialized = false;

// === FUNCIÓN PARA VERIFICAR SI JSPDF ESTÁ DISPONIBLE ===
function verificarJsPDF() {
    if (typeof jsPDF === 'undefined') {
        console.warn('⚠️ jsPDF no está cargado, intentando cargar...');
        return cargarJsPDF();
    }
    return Promise.resolve(true);
}

// === FUNCIÓN PARA CARGAR JSPDF DINÁMICAMENTE ===
function cargarJsPDF() {
    return new Promise((resolve, reject) => {
        if (typeof jsPDF !== 'undefined') {
            resolve(true);
            return;
        }

        const script = document.createElement('script');
        script.src = 'https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js';
        script.onload = () => {
            // jsPDF se carga como window.jspdf.jsPDF
            if (window.jspdf) {
                window.jsPDF = window.jspdf.jsPDF;
            }
            resolve(true);
        };
        script.onerror = () => {
            console.error('❌ Error cargando jsPDF');
            reject(new Error('No se pudo cargar jsPDF'));
        };
        document.head.appendChild(script);
    });
}

// === FUNCIÓN PARA CARGAR IMÁGENES ===
function loadImage(url) {
    return new Promise((resolve, reject) => {
        const xhr = new XMLHttpRequest();
        xhr.open('GET', url, true);
        xhr.responseType = "blob";
        xhr.onload = function () {
            if (xhr.status === 200) {
                const reader = new FileReader();
                reader.onload = function (event) {
                    resolve(event.target.result);
                };
                reader.readAsDataURL(this.response);
            } else {
                reject(new Error(`Error cargando imagen ${url}`));
            }
        };
        xhr.onerror = () => reject(new Error(`No se pudo conectar al servidor para ${url}`));
        xhr.send();
    });
}

// === FUNCIÓN PARA OBTENER VALOR DE ELEMENTO ===
function getValue(elementId, defaultValue = '') {
    const element = document.getElementById(elementId);
    if (!element) {
        console.warn(`⚠️ Elemento no encontrado: ${elementId}`);
        return defaultValue;
    }

    if (element.type === 'radio' || element.type === 'checkbox') {
        const checked = document.querySelector(`input[name="${element.name}"]:checked`);
        return checked ? checked.value : defaultValue;
    }

    return element.value || defaultValue;
}

// === FUNCIÓN PARA OBTENER VALOR DE RADIO BUTTON ===
function getRadioValue(name, defaultValue = '') {
    const checked = document.querySelector(`input[name="${name}"]:checked`);
    return checked ? checked.value : defaultValue;
}

// === FUNCIÓN PARA OBTENER CHECKBOXES MARCADOS ===
function getCheckedBoxes(name) {
    const checked = document.querySelectorAll(`input[name="${name}"]:checked`);
    return Array.from(checked).map(cb => cb.value);
}

// === RECOPILAR TODAS LAS SECCIONES DEL FORMULARIO ===
function recopilarDatosFormulario() {

    const datos = {
        // === SECCIÓN A: DATOS DEL ESTABLECIMIENTO ===
        seccionA: {
            institucion: getValue('estab_institucion'),
            unicodigo: getValue('estab_unicode'),
            establecimiento: getValue('estab_nombre'),
            historia: getValue('estab_historia_clinica'),
            archivo: getValue('estab_archivo'),
            cod_historia: getValue('cod-historia'),
        },


        // === SECCIÓN B: DATOS DEL PACIENTE ===
        seccionB: {
            fecha_admision: getValue('adm_fecha'),
            nombre_completo_admisionista: getValue('adm_admisionista_nombre'),
            historia_clinica_establecimiento: getRadioValue('adm_historia_clinica_estab'),

            // Nombres y apellidos
            primer_apellido: getValue('pac_apellido1'),
            segundo_apellido: getValue('pac_apellido2'),
            primer_nombre: getValue('pac_nombre1'),
            segundo_nombre: getValue('pac_nombre2'),

            // Documentos e información personal
            tipo_documento: getValue('pac_tipo_documento'),
            estado_civil: getValue('pac_estado_civil'),
            sexo: getValue('pac_sexo'),
            telefono_fijo: getValue('pac_telefono_fijo'),
            telefono_celular: getValue('pac_telefono_celular'),
            fecha_nacimiento: getValue('pac_fecha_nacimiento'),
            lugar_nacimiento: getValue('pac_lugar_nacimiento'),
            nacionalidad: getValue('pac_nacionalidad'),
            edad: getValue('pac_edad_valor'),
            condicion_edad: getRadioValue('pac_edad_unidad'),

            // Información étnica y educativa
            autoidentificacion_etnica: getValue('pac_etnia'),
            nacionalidad_indigena: getValue('pac_nacionalidad_indigena'),
            pueblo_indigena: getValue('pac_pueblo_indigena'),
            nivel_educacion: getValue('pac_nivel_educacion'),
            estado_educacion: getValue('pac_estado_educacion'),
            tipo_empresa: getValue('pac_tipo_empresa'),
            ocupacion: getValue('pac_ocupacion'),
            seguro_salud: getValue('pac_seguro'),

            // Grupo prioritario
            grupo_prioritario: getRadioValue('pac_grupo_prioritario'),
            grupo_prioritario_especifique: getValue('pac_grupo_prioritario_especifique'),

            // Dirección
            provincia: getValue('res_provincia'),
            canton: getValue('res_canton'),
            parroquia: getValue('res_parroquia'),
            barrio: getValue('res_barrio_sector'),
            calle_principal: getValue('res_calle_principal'),
            calle_secundaria: getValue('res_calle_secundaria'),
            referencia: getValue('res_referencia'),

            // Contacto de emergencia
            contacto_emergencia: getValue('contacto_emerg_nombre'),
            parentesco: getValue('contacto_emerg_parentesco'),
            direccion_contacto: getValue('contacto_emerg_direccion'),
            telefono_contacto: getValue('contacto_emerg_telefono'),

            // Información de llegada
            forma_llegada: getValue('forma_llegada'),
            fuente_informacion: getValue('fuente_informacion'),
            institucion_entrega: getValue('entrega_paciente_nombre_inst'),
            telefono_entrega: getValue('entrega_paciente_telefono')
        },

        // === SECCIÓN C: INICIO DE ATENCIÓN ===
        seccionC: {
            fecha: getValue('inicio_atencion_fecha'),
            hora: getValue('inicio_atencion_hora'),
            condicion: getValue('inicio_atencion_condicion'),
            motivo: getValue('inicio_atencion_motivo')
        },

        // === SECCIÓN D: ACCIDENTES, VIOLENCIAS, INTOXICACIÓN ===
        seccionD: {
            tipos_evento: getCheckedBoxes('tipos_evento[]'),
            fecha_evento: getValue('acc_fecha_evento'),
            hora_evento: getValue('acc_hora_evento'),
            lugar_evento: getValue('acc_lugar_evento'),
            direccion_evento: getValue('acc_direccion_evento'),
            custodia_policial: getRadioValue('acc_custodia_policial'),
            notificacion: getRadioValue('acc_notificacion_custodia'),
            sugestivo_alcohol: document.getElementById('acc_sugestivo_alcohol')?.checked || false,
            observaciones: getValue('acc_observaciones')
        },

        // === SECCIÓN E: ANTECEDENTES PATOLÓGICOS ===
        seccionE: {
            no_aplica: document.getElementById('ant_no_aplica')?.checked || false,
            antecedentes: getCheckedBoxes('antecedentes[]'),
            descripcion: getValue('ant_descripcion')
        },

        // === SECCIÓN F: ENFERMEDAD O PROBLEMA ACTUAL ===
        seccionF: {
            descripcion: getValue('ep_descripcion_actual')
        },

        // === SECCIÓN G: CONSTANTES VITALES ===
        seccionG: {
            sin_vitales: getValue('cv_sin_vitales') === 'on' || document.getElementById('cv_sin_vitales')?.checked,
            presion_arterial: getValue('cv_presion_arterial'),
            pulso: getValue('cv_pulso'),
            frecuencia_respiratoria: getValue('cv_frec_resp'),
            pulsioximetria: getValue('cv_pulsioximetria'),
            temperatura: getValue('cv_temperatura'),
            peso: getValue('cv_peso'),
            talla: getValue('cv_talla'),
            perimetro_cefalico: getValue('cv_perimetro_cefalico'),
            glicemia: getValue('cv_glicemia'),
            reaccion_pupilar_derecha: getValue('cv_reaccion_pupilar_der'),
            reaccion_pupilar_izquierda: getValue('cv_reaccion_pupilar_izq'),
            llenado_capilar: getValue('cv_llenado_capilar'),
            glasgow_ocular: getValue('cv_glasgow_ocular'),
            glasgow_verbal: getValue('cv_glasgow_verbal'),
            glasgow_motora: getValue('cv_glasgow_motora'),
            triaje_color: getValue('cv_triaje_color')
        },

        // === SECCIÓN H: EXAMEN FÍSICO ===
        seccionH: {
            zonas_examen: getCheckedBoxes('zonas_examen_fisico[]'),
            descripcion: getValue('ef_descripcion')
        },

        // === SECCIÓN I: EXAMEN FÍSICO DE TRAUMA/CRÍTICO ===
        seccionI: {
            descripcion: getValue('eft_descripcion')
        },

        // === SECCIÓN J: EMBARAZO-PARTO ===
        seccionJ: {
            no_aplica: document.getElementById('emb_no_aplica')?.checked || false,
            gestas: getValue('emb_gestas'),
            partos: getValue('emb_partos'),
            abortos: getValue('emb_abortos'),
            cesareas: getValue('emb_cesareas'),
            fum: getValue('emb_fum'),
            semanas_gestacion: getValue('emb_semanas_gestacion'),
            movimiento_fetal: getRadioValue('emb_movimiento_fetal'),
            fcf: getValue('emb_fcf'),
            ruptura_membranas: getRadioValue('emb_ruptura_membranas'),
            tiempo_ruptura: getValue('emb_tiempo_ruptura'),
            afu: getValue('emb_afu'),
            presentacion: getValue('emb_presentacion'),
            sangrado_vaginal: getRadioValue('emb_sangrado_vaginal'),
            contracciones: getRadioValue('emb_contracciones'),
            dilatacion: getValue('emb_dilatacion'),
            borramiento: getValue('emb_borramiento'),
            plano: getValue('emb_plano'),
            pelvis_viable: getRadioValue('emb_pelvis_viable'),
            score_mama: getValue('emb_score_mama'),
            observaciones: getValue('emb_observaciones')
        },

        // === SECCIÓN K: EXÁMENES COMPLEMENTARIOS ===
        seccionK: {
            no_aplica: document.getElementById('exc_no_aplica')?.checked || false,
            tipos_examenes: getCheckedBoxes('tipos_examenes[]'),
            observaciones: getValue('exc_observaciones')
        },

        // === SECCIÓN L: DIAGNÓSTICOS PRESUNTIVOS ===
        seccionL: {
            diagnostico1: {
                descripcion: getValue('diag_pres_desc1'),
                cie: getValue('diag_pres_cie1')
            },
            diagnostico2: {
                descripcion: getValue('diag_pres_desc2'),
                cie: getValue('diag_pres_cie2')
            },
            diagnostico3: {
                descripcion: getValue('diag_pres_desc3'),
                cie: getValue('diag_pres_cie3')
            }
        },

        // === SECCIÓN M: DIAGNÓSTICOS DEFINITIVOS ===
        seccionM: {
            diagnostico1: {
                descripcion: getValue('diag_def_desc1'),
                cie: getValue('diag_def_cie1')
            },
            diagnostico2: {
                descripcion: getValue('diag_def_desc2'),
                cie: getValue('diag_def_cie2')
            },
            diagnostico3: {
                descripcion: getValue('diag_def_desc3'),
                cie: getValue('diag_def_cie3')
            }
        },

        // === SECCIÓN N: PLAN DE TRATAMIENTO ===
        seccionN: {
            tratamiento1: {
                medicamento: getValue('trat_med1'),
                via: getValue('trat_via1'),
                dosis: getValue('trat_dosis1'),
                posologia: getValue('trat_posologia1'),
                dias: getValue('trat_dias1')
            },
            tratamiento2: {
                medicamento: getValue('trat_med2'),
                via: getValue('trat_via2'),
                dosis: getValue('trat_dosis2'),
                posologia: getValue('trat_posologia2'),
                dias: getValue('trat_dias2')
            },
            tratamiento3: {
                medicamento: getValue('trat_med3'),
                via: getValue('trat_via3'),
                dosis: getValue('trat_dosis3'),
                posologia: getValue('trat_posologia3'),
                dias: getValue('trat_dias3')
            },
            tratamiento4: {
                medicamento: getValue('trat_med4'),
                via: getValue('trat_via4'),
                dosis: getValue('trat_dosis4'),
                posologia: getValue('trat_posologia4'),
                dias: getValue('trat_dias4')
            },
            tratamiento5: {
                medicamento: getValue('trat_med5'),
                via: getValue('trat_via5'),
                dosis: getValue('trat_dosis5'),
                posologia: getValue('trat_posologia5'),
                dias: getValue('trat_dias5')
            },
            tratamiento6: {
                medicamento: getValue('trat_med6'),
                via: getValue('trat_via6'),
                dosis: getValue('trat_dosis6'),
                posologia: getValue('trat_posologia6'),
                dias: getValue('trat_dias6')
            },
            tratamiento7: {
                medicamento: getValue('trat_med7'),
                via: getValue('trat_via7'),
                dosis: getValue('trat_dosis7'),
                posologia: getValue('trat_posologia7'),
                dias: getValue('trat_dias7')
            },
            observaciones: getValue('plan_tratamiento')
        },

        // === SECCIÓN O: CONDICIÓN AL EGRESO ===
        seccionO: {
            estados_egreso: getCheckedBoxes('estados_egreso[]'),
            modalidades_egreso: getCheckedBoxes('modalidades_egreso[]'),
            tipos_egreso: getCheckedBoxes('tipos_egreso[]'),
            establecimiento_egreso: getValue('egreso_establecimiento'),
            observaciones: getValue('egreso_observacion'),
            dias_reposo: getValue('egreso_dias_reposo')
        },

        // === SECCIÓN P: DATOS DEL PROFESIONAL RESPONSABLE ===
        seccionP: {
            fecha: getValue('prof_fecha'),
            hora: getValue('prof_hora'),
            primer_nombre: getValue('prof_primer_nombre'),
            primer_apellido: getValue('prof_primer_apellido'),
            segundo_apellido: getValue('prof_segundo_apellido'),
            documento: getValue('prof_documento'),
            firma: getValue('prof_firma'),
            sello: getValue('prof_sello')
        }

    };

    return datos;
}

// === FUNCIÓN PRINCIPAL DE GENERACIÓN DE PDF ===
async function generatePDF(datosPersonalizados = null) {
    try {
        await verificarJsPDF();

        const loadingIndicator = document.getElementById('loadingIndicator');
        if (loadingIndicator) {
            loadingIndicator.classList.remove('hidden');
        }

        // Recopilar datos del formulario
        const datos = datosPersonalizados || recopilarDatosFormulario();

        // Si hay datos de imágenes guardados, usarlos
        if (window.datosImagenesParaPDF) {
            datos.seccionP.firma_base64 = window.datosImagenesParaPDF.firma_base64 || '';
            datos.seccionP.sello_base64 = window.datosImagenesParaPDF.sello_base64 || '';
            datos.seccionP.firma_existe = window.datosImagenesParaPDF.firma_existe || false;
            datos.seccionP.sello_existe = window.datosImagenesParaPDF.sello_existe || false;
        }

        // Si los datos vienen directamente del modelo (datosPersonalizados)
        if (datosPersonalizados && datosPersonalizados.pro_firma_base64) {
            datos.seccionP.firma_base64 = datosPersonalizados.pro_firma_base64;
            datos.seccionP.firma_existe = datosPersonalizados.pro_firma_existe || false;
        }

        if (datosPersonalizados && datosPersonalizados.pro_sello_base64) {
            datos.seccionP.sello_base64 = datosPersonalizados.pro_sello_base64;
            datos.seccionP.sello_existe = datosPersonalizados.pro_sello_existe || false;
        }


        // Obtener imágenes del formulario
        const baseUrl = window.APP_URLS?.baseUrl || window.location.origin + '/Formulario-Digital/';
        const rutaImagen1 = baseUrl + 'public/img/FormularioA.jpg';
        const rutaImagen2 = baseUrl + 'public/img/FormularioP.jpg';

        const image1 = await loadImage(rutaImagen1);
        const image2 = await loadImage(rutaImagen2);

        const pdf = new jsPDF('p', 'px', [PDF_CONFIG.PAGE_WIDTH, PDF_CONFIG.PAGE_HEIGHT]);

        // PÁGINA 1
        pdf.addImage(image1, 'JPEG', 0, 10, PDF_CONFIG.PAGE_WIDTH, PDF_CONFIG.PAGE_HEIGHT);
        pdf.setFontSize(PDF_CONFIG.FONT_SIZE);

        llenarSeccionAPDF(pdf, datos.seccionA);
        llenarSeccionBPDF(pdf, datos.seccionB);
        llenarSeccionCPDF(pdf, datos.seccionC);
        llenarSeccionDPDF(pdf, datos.seccionD);
        llenarSeccionEPDF(pdf, datos.seccionE);
        llenarSeccionFPDF(pdf, datos.seccionF);

        // PÁGINA 2
        pdf.addPage();
        pdf.addImage(image2, 'JPEG', 0, 0, PDF_CONFIG.PAGE_WIDTH, PDF_CONFIG.PAGE_HEIGHT);
        pdf.setFontSize(PDF_CONFIG.FONT_SIZE);

        llenarSeccionGPDF(pdf, datos.seccionG);
        llenarSeccionHPDF(pdf, datos.seccionH);
        llenarSeccionIPDF(pdf, datos.seccionI);
        llenarSeccionJPDF(pdf, datos.seccionJ);
        llenarSeccionKPDF(pdf, datos.seccionK);
        llenarSeccionLPDF(pdf, datos.seccionL);
        llenarSeccionMPDF(pdf, datos.seccionM);
        llenarSeccionNPDF(pdf, datos.seccionN);
        llenarSeccionOPDF(pdf, datos.seccionO);
        llenarSeccionPPDF(pdf, datos.seccionP);

        // Usar la fecha de admisión del formulario (no la fecha actual)
        const fechaAtencion = datos.seccionB.fecha_admision || new Date().toISOString().split('T')[0];
        const cedula = datos.seccionA.historia || 'sin_cedula';

        // Obtener primer apellido y primer nombre
        const primerApellido = (datos.seccionB.primer_apellido || '').trim().replace(/\s+/g, '_');
        const primerNombre = (datos.seccionB.primer_nombre || '').trim().replace(/\s+/g, '_');

        // Generar nombre del archivo: formulario_008_APELLIDO_NOMBRE_CEDULA_FECHA.pdf
        let nombreArchivo;
        if (primerApellido && primerNombre) {
            nombreArchivo = `Formulario_008_${primerApellido}_${primerNombre}_${cedula}_${fechaAtencion}.pdf`;
        } else {
            // Fallback si no hay nombre completo
            nombreArchivo = `Formulario_008_${cedula}_${fechaAtencion}.pdf`;
        }

        // // Descargar PDF en el navegador
        // pdf.save(nombreArchivo);

        // ===== GUARDAR PDF EN EL SERVIDOR ORGANIZADO POR MES =====
        try {
            // Convertir PDF a base64
            const pdfBase64 = pdf.output('datauristring');

            // Enviar al servidor
            const baseUrl = window.APP_URLS?.baseUrl || window.location.origin + '/Formulario-Digital/';
            const response = await fetch(baseUrl + 'administrador/pdf/guardar-008', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    pdfBase64: pdfBase64,
                    nombreArchivo: nombreArchivo
                })
            });

            const resultado = await response.json();

            if (resultado.success) {
                console.log('✅ PDF guardado en servidor:', resultado.carpeta_mes);
                Swal.fire({
                    icon: 'success',
                    title: 'Éxito',
                    text: 'PDF guardado exitosamente en: ' + resultado.carpeta_mes,
                    confirmButtonText: 'Aceptar'
                });
            } else {
                console.warn('⚠️ Error al guardar PDF en servidor:', resultado.message);
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Error al guardar PDF: ' + resultado.message,
                    confirmButtonText: 'Aceptar'
                });
            }
        } catch (errorServidor) {
            console.error('❌ Error enviando PDF al servidor:', errorServidor);
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'Error al guardar PDF en el servidor',
                confirmButtonText: 'Aceptar'
            });
        }


    } catch (error) {
        console.error('Error generando PDF:', error);
    } finally {
        const loadingIndicator = document.getElementById('loadingIndicator');
        if (loadingIndicator) {
            loadingIndicator.classList.add('hidden');
        }
    }
}

// === FUNCIONES AUXILIARES PARA MENSAJES ===
function mostrarMensajeExito(mensaje) {
    const successMessage = document.getElementById('successMessage');
    if (successMessage) {
        const successText = document.getElementById('successText');
        if (successText) {
            successText.textContent = mensaje;
        }
        successMessage.classList.remove('hidden');
        setTimeout(() => {
            successMessage.classList.add('hidden');
        }, 5000);
    }
}

function mostrarMensajeError(mensaje) {
    const errorMessage = document.getElementById('errorMessage');
    if (errorMessage) {
        const errorText = document.getElementById('errorText');
        if (errorText) {
            errorText.textContent = mensaje;
        }
        errorMessage.classList.remove('hidden');
        setTimeout(() => {
            errorMessage.classList.add('hidden');
        }, 8000);
    }
}
function formatearHora008(hora) {
    if (!hora) return '';

    // Si la hora viene con formato HH:MM:SS, extraer solo HH:MM
    if (hora.includes(':')) {
        const partes = hora.split(':');
        if (partes.length >= 2) {
            return `${partes[0]}:${partes[1]}`;
        }
    }

    return hora;
}
// ===LLENAR LA PAGINA 1 EN PDF ===
function llenarSeccionAPDF(pdf, seccionA) {

    // Historia clínica (esquina superior derecha)
    pdf.text(seccionA.cod_historia || '', 670, 7);

    // Fila 1: Datos del establecimiento
    pdf.text((seccionA.institucion || '').toUpperCase(), 110, 60);
    pdf.text((seccionA.unicodigo || '').toUpperCase(), 225, 60);
    pdf.text((seccionA.establecimiento || '').toUpperCase(), 320, 60);
    pdf.text((seccionA.historia || '').toUpperCase(), 480, 60);
    pdf.text((seccionA.archivo || '').toUpperCase(), 620, 60);
}

// === FUNCIÓN PARA LLENAR SECCIÓN B EN PDF ===
function llenarSeccionBPDF(pdf, seccionB) {

    // Fila 1
    pdf.text((seccionB.fecha_admision || '').toUpperCase(), 194, 122);
    pdf.text((seccionB.nombre_completo_admisionista || '').toUpperCase(), 420, 122);
    seccionB.historia_clinica_establecimiento == "si" ? pdf.text('X', 630, 122) :
    seccionB.historia_clinica_establecimiento == "no" ? pdf.text('X', 677, 122) : null;

    // Fila 2
    pdf.text((seccionB.primer_apellido || '').toUpperCase(), 110, 167);
    pdf.text((seccionB.segundo_apellido || '').toUpperCase(), 270, 167);
    pdf.text((seccionB.primer_nombre || '').toUpperCase(), 390, 167);
    pdf.text((seccionB.segundo_nombre || '').toUpperCase(), 505, 167);

    // Tipo de documento
    const tipoDocumentoTexto = {
        '1': 'CC/CI',
        '2': 'PASAPORTE',
        '3': 'CARNET',
        '4': 'SIN DOCUMENTO'
    };
    const tipoDocumentoCoords = {
        'CC/CI': { x: 592, y: 167 },
        'PASAPORTE': { x: 620, y: 167 },
        'CARNET': { x: 650, y: 167 },
        'SIN DOCUMENTO': { x: 678, y: 167 }
    };

    const tipoDocCodigo = (seccionB.tipo_documento || '').trim();
    const tipoDocTexto = tipoDocumentoTexto[tipoDocCodigo];
    if (tipoDocTexto && tipoDocumentoCoords[tipoDocTexto]) {
        const coords = tipoDocumentoCoords[tipoDocTexto];
        pdf.text('X', coords.x, coords.y);
    }

    // Fila 3 
    const estadoCivilTexto = {
        '1': 'SOLTERO(A)',
        '2': 'CASADO(A)',
        '3': 'VIUDO(A)',
        '4': 'DIVORCIADO(A)',
        '5': 'UNIÓN LIBRE',
        '6': 'A ESPECIFICAR'
    };
    const estadoCivilCoords = {
        'SOLTERO(A)': { x: 75, y: 209 },
        'CASADO(A)': { x: 93, y: 209 },
        'VIUDO(A)': { x: 133, y: 209 },
        'DIVORCIADO(A)': { x: 113, y: 209 },
        'UNIÓN LIBRE': { x: 152, y: 209 },
        'A ESPECIFICAR': { x: 189, y: 209 }
    };

    const estadoCivilCodigo = (seccionB.estado_civil || '').trim();
    const estadoCivilTextoValor = estadoCivilTexto[estadoCivilCodigo];
    if (estadoCivilTextoValor && estadoCivilCoords[estadoCivilTextoValor]) {
        const coord = estadoCivilCoords[estadoCivilTextoValor];
        pdf.text('X', coord.x, coord.y);
    }



    const generoTexto = {
        '1': 'MASCULINO',
        '2': 'FEMENINO',
        '3': 'OTRO',
        '4': 'A ESPECIFICAR'
    };

    const generoCodigo = (seccionB.sexo || '').toString().trim();
    const genero = generoTexto[generoCodigo] || '';
    pdf.text(genero, 205, 209);


    pdf.text(seccionB.telefono_fijo || '', 288, 209);
    pdf.text(seccionB.telefono_celular || '', 420, 209);
    pdf.text((seccionB.fecha_nacimiento || '').toUpperCase(), 580, 209);

    // Fila 4
    pdf.text((seccionB.lugar_nacimiento || '').toUpperCase(), 90, 253);

    const nacionalidadTexto = {
        '1': 'ECUATORIANA',
        '2': 'PERUANA',
        '3': 'CUBANA',
        '4': 'COLOMBIANA',
        '5': 'OTRA',
        '6': 'A ESPECIFICAR'
    };
    const nacionalidadCodigo = (seccionB.nacionalidad || '').toString().trim();
    const nacionalidad = nacionalidadTexto[nacionalidadCodigo] || seccionB.nacionalidad || '';
    pdf.text(nacionalidad, 288, 253);

    pdf.text(seccionB.edad || '', 405, 253);

    const edadUnidadCoords = {
        'H': { x: 442, y: 253 },
        'D': { x: 461, y: 253 },
        'M': { x: 480, y: 253 },
        'A': { x: 500, y: 253 }
    };

    const unidadEdad = (seccionB.condicion_edad || '').trim().toUpperCase();
    if (edadUnidadCoords[unidadEdad]) {
        const coord = edadUnidadCoords[unidadEdad];
        pdf.text('X', coord.x, coord.y);
    } else {
        console.warn('Unidad de edad no reconocida:', unidadEdad);
    }

    // Grupo prioritario - manejar múltiples formatos de valor
    const grupoPrioritario = seccionB.grupo_prioritario;
    if (grupoPrioritario === "si" || grupoPrioritario === "1" || grupoPrioritario === 1 || grupoPrioritario === true) {
        pdf.text('X', 645, 225);
    } else if (grupoPrioritario === "no" || grupoPrioritario === "0" || grupoPrioritario === 0 || grupoPrioritario === false) {
        pdf.text('X', 685, 225);
    }

    // pdf.text((seccionB.grupo_prioritario_especifique || '').toUpperCase(), 586, 242);
    
    const textoLargo = (seccionB.grupo_prioritario_especifique || '').toUpperCase();
    const lineas = pdf.splitTextToSize(textoLargo, 100);

    let y = 242; // punto inicial en vertical
    const salto = 14; // distancia entre líneas

    lineas.forEach((linea, index) => {
        const x = index === 0 ? 586 : 520; // primera línea en 100, resto en 70
        pdf.text(linea, x, y);
        y += salto; // baja para la siguiente línea
    });

    // Fila 5
    const etniaTexto = {
        '1': 'INDÍGENA',
        '2': 'AFROECUATORIANA',
        '3': 'MESTIZO',
        '4': 'MONTUBIO',
        '5': 'BLANCO',
        '6': 'OTROS',
        '7': 'A ESPECIFICAR'

    }; const etniaCodigo = (seccionB.autoidentificacion_etnica || '').toString().trim();
    const etnia = etniaTexto[etniaCodigo] || seccionB.autoidentificacion_etnica || '';
    pdf.text(etnia, 110, 282);

    // Mapeo de nacionalidad indígena
    const nacionalidadIndigenaTexto = {
        '1': 'ÉPERA', '2': 'CHACHI', '3': 'AWÁ', '4': 'TSÁCHILA', '5': 'KICHWA',
        '6': 'SHUAR', '7': 'COFÁN', '8': 'SIONA', '9': 'SECOYA', '10': 'WAORANI',
        '11': 'ZÁPARA', '12': 'ANDOA', '13': 'SHIWIAR', '14': 'ACHUAR', '15': 'OTRAS'
    };
    const nacionalidadIndigena = nacionalidadIndigenaTexto[seccionB.nacionalidad_indigena] || seccionB.nacionalidad_indigena || '';
    pdf.text(nacionalidadIndigena, 270, 282);

    // Mapeo de pueblo indígena
    const puebloIndigenaTexto = {
        '1': 'HUANCAVILCA', '2': 'MANTA', '3': 'KARANKI', '4': 'OTAVALO',
        '5': 'NATABUELA', '6': 'KAYAMBI', '7': 'KITU KARA', '8': 'PANZALEO',
        '9': 'CHIBULEO', '10': 'KISAPINCHA', '11': 'SALASAKA', '12': 'WARANKA',
        '13': 'PURUWÁ', '14': 'KAÑARI', '15': 'PALTA', '16': 'SARAGURO',
        '17': 'COFÁN', '18': 'SIONA - SECOYA', '19': 'OTROS'
    };
    const puebloIndigena = puebloIndigenaTexto[seccionB.pueblo_indigena] || seccionB.pueblo_indigena || '';
    pdf.text(puebloIndigena, 450, 282);
    
    

    const nivelEducacionTexto = {
        '1': 'EDUCACIÓN INICIAL',
        '2': 'EGB',
        '3': 'BACHILLERATO',
        '4': 'EDUCACIÓN SUPERIOR'
    };
    const nivelEducCodigo = (seccionB.nivel_educacion || '').toString().trim();
    const nivelEducacion = nivelEducacionTexto[nivelEducCodigo] || seccionB.nivel_educacion || '';
    pdf.text(nivelEducacion, 580, 282);


    // Fila 6
    const estadoEducacionTexto = {
        '1': 'INCOMPLETA',
        '2': 'CURSANDO',
        '3': 'COMPLETA'
    };
    const estadoEducCodigo = (seccionB.estado_educacion || '').toString().trim();
    const estadoEducacion = estadoEducacionTexto[estadoEducCodigo] || seccionB.estado_educacion || '';
    pdf.text(estadoEducacion, 110, 322);

    const tipoEmpresaTexto = {
        '1': 'PÚBLICA',
        '2': 'PRIVADA',
        '3': 'NO TRABAJA',
        '4': 'A ESPECIFICAR'
    };
    const tipoEmpCodigo = (seccionB.tipo_empresa || '').toString().trim();
    const tipoEmpresa = tipoEmpresaTexto[tipoEmpCodigo] || seccionB.tipo_empresa || '';
    pdf.text(tipoEmpresa, 260, 322);

    pdf.text(seccionB.ocupacion || '', 415, 322);

    //I-590, 
    const seguroTexto = {
        '1': 'IESS',
        '2': 'ISSPOL',
        '3': 'ISSFA',
        '4': 'PRIVADO',
        '5': 'A ESPECIFICAR'
    };
    const seguroCoords = {
        'IESS': { x: 536, y: 322 },
        'ISSPOL': { x: 590, y: 322 },
        'ISSFA': { x: 620, y: 322 },
        'PRIVADO': { x: 652, y: 322 },
        'A ESPECIFICAR': { x: 678, y: 322 }
    };

    const seguroCodigo = (seccionB.seguro_salud || '').trim();
    const seguroTextoValor = seguroTexto[seguroCodigo];
    if (seguroTextoValor && seguroCoords[seguroTextoValor]) {
        const coord = seguroCoords[seguroTextoValor];
        pdf.text('X', coord.x, coord.y);
    }

    // Fila 7
    pdf.text((seccionB.provincia || '').toUpperCase(), 180, 352);//(entre mas alto el numero va a bajar y en menos alto el numero sube)
    pdf.text((seccionB.canton || '').toUpperCase(), 310, 352);
    pdf.text((seccionB.parroquia || '').toUpperCase(), 420, 352);
    pdf.text((seccionB.barrio || '').toUpperCase(), 545, 352);

    // Fila 8
    pdf.text((seccionB.calle_principal || '').toUpperCase(), 140, 382);
    pdf.text((seccionB.calle_secundaria || '').toUpperCase(), 365, 382);
    pdf.text((seccionB.referencia || '').toUpperCase(), 545, 382);

    // Fila 9
    pdf.text((seccionB.contacto_emergencia || '').toUpperCase(), 130, 412);
    pdf.text((seccionB.parentesco || '').toUpperCase(), 300, 412);
    pdf.text((seccionB.direccion_contacto || '').toUpperCase(), 410, 412);
    pdf.text((seccionB.telefono_contacto || '').toUpperCase(), 620, 412);

    // Fila 10
    const formaLlegadaTexto = {
        '1': 'AMBULATORIO',
        '2': 'AMBULANCIA',
        '3': 'OTRO TRANSPORTE'
    };
    const formaLlegadaCoords = {
        'AMBULATORIO': { x: 130, y: 445 },
        'AMBULANCIA': { x: 210, y: 445 },
        'OTRO TRANSPORTE': { x: 278, y: 445 }
    };

    const llegadaCodigo = ((seccionB.forma_llegada || '').toUpperCase()).trim();
    const llegadaTextoValor = formaLlegadaTexto[llegadaCodigo];
    if (llegadaTextoValor && formaLlegadaCoords[llegadaTextoValor]) {
        const coord = formaLlegadaCoords[llegadaTextoValor];
        pdf.text('X', coord.x, coord.y);
    }

    pdf.text((seccionB.fuente_informacion || '').toUpperCase(), 335, 445);
    pdf.text((seccionB.institucion_entrega || '').toUpperCase(), 480, 445);
    pdf.text((seccionB.telefono_entrega || '').toUpperCase(), 620, 445);
}


// === FUNCIÓN PARA LLENAR SECCIÓN C EN PDF ===
function llenarSeccionCPDF(pdf, seccionC) {
    pdf.text((seccionC.fecha || '').toUpperCase(), 150, 490);
    pdf.text(formatearHora008(seccionC.hora || '').toUpperCase(), 300, 490);

    // Condición de llegada (según códigos del catálogo)
    const condicionCoords = {
        '1': { x: 490, y: 490 },
        '2': { x: 578, y: 490 },
        '3': { x: 680, y: 490 }
    };

    const condicion = seccionC.condicion;
    if (condicionCoords[condicion]) {
        const coord = condicionCoords[condicion];
        pdf.text('X', coord.x, coord.y);
    }

    const textoLargo = (seccionC.motivo || '').toUpperCase();
    const lineas = pdf.splitTextToSize(textoLargo, 530);

    let y = 505; // punto inicial en vertical
    const salto = 9; // distancia entre líneas

    lineas.forEach((linea, index) => {
        const x = index === 0 ? 140 : 140;
        pdf.text(linea, x, y);
        y += salto;
    });
}

// === FUNCIÓN PARA LLENAR SECCIÓN D EN PDF ===
function llenarSeccionDPDF(pdf, seccionD) {
    pdf.text((seccionD.fecha_evento || '').toUpperCase(), 85, 570);
    pdf.text(formatearHora008(seccionD.hora_evento || '').toUpperCase(), 150, 570);
    pdf.text((seccionD.lugar_evento || '').toUpperCase(), 250, 570);
    pdf.text((seccionD.direccion_evento || '').toUpperCase(), 440, 570);

    // Tipos de evento (checkboxes múltiples)
    const tiposEventoCoords = {
        //Fila 1
        '1': { x: 131, y: 588 }, //ACCIDENTE DE TRÁNSITO
        '2': { x: 210, y: 588 }, //CAÍDA  
        '3': { x: 288, y: 588 }, //QUEMADURA
        '4': { x: 365, y: 588 }, //MORDEDURA
        '5': { x: 443, y: 588 }, //AHOGAMIENTO
        '6': { x: 519, y: 588 }, //CUERPO EXTRAÑO
        '7': { x: 608, y: 588 }, //APLASTAMIENTO
        //Fila 2
        '8': { x: 131, y: 611 }, //VIOLENCIA POR ARMA DE FUEGO
        '9': { x: 210, y: 611 }, //VIOLENCIA POR ARMA C. PUNZANTE
        '10': { x: 288, y: 611 }, //VIOLENCIA POR RIÑA
        '11': { x: 365, y: 611 }, //VIOLENCIA FAMILIAR
        '12': { x: 443, y: 611 }, //VIOLENCIA FÍSICA
        '13': { x: 519, y: 611 }, //VIOLENCIA PSICOLÓGICA
        '14': { x: 608, y: 611 }, //VIOLENCIA SEXUAL
        //Fila 3
        '15': { x: 131, y: 630 }, //INTOXICACIÓN ALCOHÓLICA
        '16': { x: 210, y: 630 }, //INTOXICACIÓN ALIMENTARIA
        '17': { x: 288, y: 630 }, //INTOXICACIÓN POR DROGAS
        '18': { x: 365, y: 630 }, //INHALACIÓN DE GASES
        '19': { x: 443, y: 630 }, //OTRA INTOXICACIÓN
        '20': { x: 519, y: 630 }, //PICADURA
        '21': { x: 608, y: 630 }, //ENVENENAMIENTO
        //Otras opciones
        '23': { x: 683, y: 588 }, //OTRO ACCIDENTE
        '22': { x: 684, y: 630 }, //ANAFILAXIA
    };

    seccionD.tipos_evento.forEach(tipo => {
        if (tiposEventoCoords[tipo]) {
            const coord = tiposEventoCoords[tipo];
            pdf.text('X', coord.x, coord.y);
        }
    });

    // Custodia policial
    if (seccionD.custodia_policial === 'si') {
        pdf.text('X', 645, 570);
    } else if (seccionD.custodia_policial === 'no') {
        pdf.text('X', 684, 570);
    }

    // Notificación
    if (seccionD.notificacion === 'si') {
        pdf.text('X', 645, 615);
    } else if (seccionD.notificacion === 'no') {
        pdf.text('X', 684, 615);
    }

    // Sugestivo de alcohol
    if (seccionD.sugestivo_alcohol) {
        pdf.text('X', 684, 720);
    }

    const textoLargo = (seccionD.observaciones || '').toUpperCase();
    const lineas = pdf.splitTextToSize(textoLargo, 530);

    let y = 648; // punto inicial en vertical
    const salto = 14; // distancia entre líneas

    lineas.forEach((linea, index) => {
        const x = index === 0 ? 160 : 75; // primera línea en 100, resto en 70
        pdf.text(linea, x, y);
        y += salto; // baja para la siguiente línea
    });


}

// === FUNCIÓN PARA LLENAR SECCIÓN E EN PDF ===
function llenarSeccionEPDF(pdf, seccionE) {
    if (seccionE.no_aplica) {
        pdf.text('X', 684, 749);
    } else {
        // Antecedentes (checkboxes múltiples)
        const antecedentesCoords = {
            '1': { x: 159, y: 763 }, // ALERGICOS
            '3': { x: 279, y: 763 }, // GINECOLOGICOS
            '5': { x: 413, y: 763 }, // PEDIATRICOS
            '7': { x: 558, y: 763 }, // FAMACOLOGICOS
            '9': { x: 683, y: 763 }, // FAMILIARES

            '2': { x: 159, y: 781 }, // CLINICOS
            '4': { x: 279, y: 781 }, // TRAUMATOLOGICOS
            '6': { x: 413, y: 781 }, // QUIRURGICOS
            '8': { x: 558, y: 781 }, // HABITOS
            '10': { x: 683, y: 781 }, // OTROS
        };

        seccionE.antecedentes.forEach(antecedente => {
            if (antecedentesCoords[antecedente]) {
                const coord = antecedentesCoords[antecedente];
                pdf.text('X', coord.x, coord.y);
            }
        });

        const textoLargo = (seccionE.descripcion || '').toUpperCase();
        const lineas = pdf.splitTextToSize(textoLargo, 600); // ajusta el ancho del texto

        let y = 800; // punto inicial en vertical
        const salto = 18; // distancia entre líneas (puedes ajustar: 15, 18, 20, etc.)

        lineas.forEach(linea => {
            pdf.text(linea, 75, y);
            y += salto; // baja para la siguiente línea
        });

    }
}

// === FUNCIÓN PARA LLENAR SECCIÓN F EN PDF ===
function llenarSeccionFPDF(pdf, seccionF) {
    const textoLargo = (seccionF.descripcion || '').toUpperCase();
    const lineas = pdf.splitTextToSize(textoLargo, 590); // ajusta el ancho del texto

    let y = 900; // punto inicial en vertical
    const salto = 18; // distancia entre líneas (puedes ajustar: 15, 18, 20, etc.)

    lineas.forEach(linea => {
        pdf.text(linea, 75, y);
        y += salto; // baja para la siguiente línea
    });
}

// === FUNCIÓN PARA LLENAR SECCIÓN G EN PDF ===
function llenarSeccionGPDF(pdf, seccionG) {
    // Checkbox "Sin constantes vitales"
    if (seccionG.sin_vitales) {
        pdf.text('X', 155, 24);
    }

    // Constantes vitales básicas
    pdf.text((seccionG.presion_arterial || '').toUpperCase(), 310, 26);
    pdf.text((seccionG.pulso || '').toUpperCase(), 485, 26);
    pdf.text((seccionG.frecuencia_respiratoria || '').toUpperCase(), 689, 26);
    pdf.text((seccionG.pulsioximetria || '').toUpperCase(), 145, 41);

    // Antropometría
    pdf.text((seccionG.perimetro_cefalico || '').toUpperCase(), 310, 41);
    pdf.text((seccionG.peso || '').toUpperCase(), 430, 41);
    pdf.text((seccionG.talla || '').toUpperCase(), 520, 41);
    pdf.text((seccionG.glicemia || '').toUpperCase(), 689, 41);

    // Reacciones pupilares
    pdf.text((seccionG.reaccion_pupilar_derecha || '').toUpperCase(), 487, 56);
    pdf.text((seccionG.reaccion_pupilar_izquierda || '').toUpperCase(), 577, 56);
    pdf.text((seccionG.llenado_capilar || '').toUpperCase(), 689, 56);

    // Glasgow
    pdf.text((seccionG.glasgow_ocular || '').toUpperCase(), 177, 56);
    pdf.text((seccionG.glasgow_verbal || '').toUpperCase(), 290, 56);
    pdf.text((seccionG.glasgow_motora || '').toUpperCase(), 394, 56);

}

// === FUNCIÓN PARA LLENAR SECCIÓN H EN PDF ===
function llenarSeccionHPDF(pdf, seccionH) {
    // Zonas de examen físico (checkboxes múltiples)
    const zonasCoords = {
        //Fila 1
        '1': { x: 145, y: 92 }, //PIEL - FANERAS
        '4': { x: 275, y: 92 }, //OÍDOS
        '7': { x: 417, y: 92 }, //ORO FARINGE
        '10': { x: 557, y: 92 },  //TÓRAX
        '13': { x: 722, y: 92 },  //INGLE - PERINÉ
        //Fila 2
        '2': { x: 145, y: 107 }, //CABEZA
        '5': { x: 275, y: 107 }, //NARIZ
        '8': { x: 417, y: 107 }, //CUELLO
        '11': { x: 557, y: 107 },  //ABDOMEN
        '14': { x: 722, y: 107 },  //MIEMBROS SUPERIORES
        //Fila 3
        '3': { x: 145, y: 120 }, //OJOS
        '6': { x: 275, y: 120 }, //BOCA
        '9': { x: 417, y: 120 }, //AXILAS - MAMAS
        '12': { x: 557, y: 120 },  //COLUMNA VERTEBRAL
        '15': { x: 722, y: 120 },  //MIEMBROS INFERIORES
    };

    seccionH.zonas_examen.forEach(zona => {
        if (zonasCoords[zona]) {
            const coord = zonasCoords[zona];
            pdf.text('X', coord.x, coord.y);
        }
    });

    const textoLargo = (seccionH.descripcion || '').toUpperCase();
    const lineas = pdf.splitTextToSize(textoLargo, 685);

    let y = 138; // punto inicial en vertical
    const salto = 14; // distancia entre líneas (puedes ajustar: 15, 18, 20, etc.)

    lineas.forEach(linea => {
        pdf.text(linea, 35, y);
        y += salto; // baja para la siguiente línea
    });

}

// === FUNCIÓN PARA LLENAR SECCIÓN I EN PDF ===
function llenarSeccionIPDF(pdf, seccionI) {
    const textoLargo = (seccionI.descripcion || '').toUpperCase();
    const lineas = pdf.splitTextToSize(textoLargo, 665);

    let y = 236; // punto inicial en vertical
    const salto = 15; // distancia entre líneas (puedes ajustar: 15, 18, 20, etc.)

    lineas.forEach(linea => {
        pdf.text(linea, 35, y);
        y += salto; // baja para la siguiente línea
    });
}


// === FUNCIÓN PARA LLENAR SECCIÓN J EN PDF ===
function llenarSeccionJPDF(pdf, seccionJ) {
    // Verificar si "No aplica" está marcado
    if (seccionJ.no_aplica) {
        pdf.text('X', 722, 365);
        return;
    }

    //Fila 1

    // === DATOS OBSTÉTRICOS ===
    pdf.text((seccionJ.gestas || '').toUpperCase(), 101, 375);
    pdf.text((seccionJ.partos || '').toUpperCase(), 195, 375);
    pdf.text((seccionJ.abortos || '').toUpperCase(), 285, 375);
    pdf.text((seccionJ.cesareas || '').toUpperCase(), 380, 375);

    // === INFORMACIÓN DEL EMBARAZO ACTUAL ===
    pdf.text((seccionJ.fum || '').toUpperCase(), 468, 375);
    pdf.text((seccionJ.semanas_gestacion || '').toUpperCase(), 590, 375);

    // === EVALUACIÓN FETAL ===
    // Movimiento fetal (radio buttons)
    if (seccionJ.movimiento_fetal === 'si') {
        pdf.text('SI', 710, 375);
    } else if (seccionJ.movimiento_fetal === 'no') {
        pdf.text('NO', 710, 375);
    }

    //Fila 2

    // Frecuencia cardíaca fetal
    pdf.text((seccionJ.fcf || '').toUpperCase(), 140, 392);

    // === TRABAJO DE PARTO ===
    // Ruptura de membranas (radio buttons)
    if (seccionJ.ruptura_membranas === 'si') {
        pdf.text('SI', 285, 392);
    } else if (seccionJ.ruptura_membranas === 'no') {
        pdf.text('NO', 285, 392);
    }

    // Tiempo de ruptura
    pdf.text((seccionJ.tiempo_ruptura || '').toUpperCase(), 380, 392);

    // AFU (Altura del Fondo Uterino)
    pdf.text((seccionJ.afu || '').toUpperCase(), 475, 392);

    // Presentación fetal
    pdf.text((seccionJ.presentacion || '').toUpperCase(), 590, 392);;

    //Fila 3

    // === EVALUACIÓN CERVICAL ===
    pdf.text((seccionJ.dilatacion || '').toUpperCase(), 140, 408);
    pdf.text((seccionJ.borramiento || '').toUpperCase(), 285, 408);
    pdf.text((seccionJ.plano || '').toUpperCase(), 379, 408);

    // Pelvis viable (radio buttons)
    if (seccionJ.pelvis_viable === 'si') {
        pdf.text('SI', 475, 408);
    } else if (seccionJ.pelvis_viable === 'no') {
        pdf.text('NO', 475, 408);
    }

    // Sangrado vaginal (radio buttons)
    if (seccionJ.sangrado_vaginal === 'si') {
        pdf.text('SI', 584, 408);
    } else if (seccionJ.sangrado_vaginal === 'no') {
        pdf.text('NO', 584, 408);
    }

    // Contracciones (radio buttons)
    if (seccionJ.contracciones === 'si') {
        pdf.text('SI', 710, 408);
    } else if (seccionJ.contracciones === 'no') {
        pdf.text('NO', 710, 408);
    }

    //Fila 4
    // Score Mamá
    pdf.text(seccionJ.score_mama || '', 140, 420);

    // === OBSERVACIONES ===
    const textoLargo = (seccionJ.observaciones || '').toUpperCase();
    const lineas = pdf.splitTextToSize(textoLargo, 580);

    let y = 421; // punto inicial en vertical
    const salto = 15; // distancia entre líneas

    lineas.forEach((linea, index) => {
        const x = index === 0 ? 160 : 35; // primera línea en 100, resto en 70
        pdf.text(linea, x, y);
        y += salto; // baja para la siguiente línea
    });
}

// === FUNCIÓN PARA LLENAR SECCIÓN K EN PDF ===
function llenarSeccionKPDF(pdf, seccionK) {
    if (seccionK.no_aplica) {
        pdf.text('X', 722, 490);
    } else {
        const examenesCoords = {
            '1': { x: 101, y: 504 }, // BIOMETRIA
            '3': { x: 189, y: 504 }, // QUIMICA SANGUINEA
            '5': { x: 277, y: 504 }, // GASOMETRIA
            '7': { x: 365, y: 504 }, // ENDOSCOPIA
            '9': { x: 436, y: 504 }, // RX ABDOMEN
            '11': { x: 527, y: 504 }, // ECOGRAFIA ABDOMEN
            '13': { x: 625, y: 504 }, // TOMOGRAFIA
            '15': { x: 722, y: 504 }, // INTERCONSULTA

            '2': { x: 101, y: 517 }, // UROANALISIS
            '4': { x: 189, y: 517 }, // ELECRTOLITOS
            '6': { x: 277, y: 517 }, // ELECTRO CARDIOGRAMA
            '8': { x: 365, y: 517 }, // RX TORAX
            '10': { x: 436, y: 517 }, // RX OSEA
            '12': { x: 527, y: 517 }, // ECOGRAFIA PELVICA
            '14': { x: 625, y: 517 }, // RESONANCIA
            '16': { x: 722, y: 517 }, // OTROS
        };

        seccionK.tipos_examenes.forEach(examen => {
            if (examenesCoords[examen]) {
                const coord = examenesCoords[examen];
                pdf.text('X', coord.x, coord.y);
            }
        });

        const textoLargo = (seccionK.observaciones || '').toUpperCase();
        const lineas = pdf.splitTextToSize(textoLargo, 640);

        let y = 532; // punto inicial en vertical
        const salto = 15; // distancia entre líneas

        lineas.forEach((linea, index) => {
            const x = index === 0 ? 97 : 35; // primera línea en 100, resto en 70
            pdf.text(linea, x, y);
            y += salto; // baja para la siguiente línea
        });

    }
}

// === FUNCIÓN PARA LLENAR SECCIÓN L EN PDF ===
function llenarSeccionLPDF(pdf, seccionL) {
    const posiciones = { descripcion: 46, cie: 335 };
    llenarDiagnosticos(pdf, seccionL, posiciones);
}

function llenarSeccionMPDF(pdf, seccionM) {
    const posiciones = { descripcion: 406, cie: 700 };
    llenarDiagnosticos(pdf, seccionM, posiciones);
}

function llenarDiagnosticos(pdf, seccion, pos) {
    const config = { yInicial: 590, espaciado: 15, maxDiagnosticos: 3 };

    for (let i = 1; i <= config.maxDiagnosticos; i++) {
        const diag = seccion[`diagnostico${i}`];
        if (!diag) continue;

        const y = config.yInicial + (i - 1) * config.espaciado;

        // Descripción con salto de línea
        const textoDescripcion = (diag.descripcion || '').toUpperCase();
        const lineasDesc = pdf.splitTextToSize(textoDescripcion, 280);

        let yDesc = y;
        const saltoDesc = 5;

        lineasDesc.forEach((linea) => {
            pdf.text(linea, pos.descripcion, yDesc);
            yDesc += saltoDesc;
        });

        // CIE en su posición
        pdf.text((diag.cie || '').toUpperCase(), pos.cie, y);
    }
}

// === FUNCIÓN PARA LLENAR SECCIÓN N EN PDF ===
function llenarSeccionNPDF(pdf, seccionN) {
    // Configuración de columnas y posiciones
    const columnas = [
        { campo: 'medicamento', x: 50 },
        { campo: 'via', x: 390 },
        { campo: 'dosis', x: 470 },
        { campo: 'posologia', x: 545 },
        { campo: 'dias', x: 695 }
    ];
    
    // Configuración de tratamientos
    const yInicial = 678;
    const espaciadoTratamientos = 14;
    const maxTratamientos = 7;
    
    // Llenar tratamientos de forma dinámica
    for (let i = 1; i <= maxTratamientos; i++) {
        const tratamiento = seccionN[`tratamiento${i}`];
        if (!tratamiento) continue;
        
        const y = yInicial + (i - 1) * espaciadoTratamientos;
        
        columnas.forEach(col => {
            const valor = (tratamiento[col.campo] || '').toUpperCase();
            pdf.text(valor, col.x, y);
        });
    }
    
    // Observaciones del plan de tratamiento
    
    if (seccionN.observaciones) {
        const lineas = pdf.splitTextToSize((seccionN.observaciones || '').toUpperCase(), 660);
        let y = 778;
        const saltoLinea = 14;
        
        lineas.forEach(linea => {
            pdf.text(linea, 35, y);
            y += saltoLinea;
        });
    }
}

function llenarSeccionOPDF(pdf, seccionO) {
    // Mapeos y coordenadas en un solo objeto
    const configuracion = {
        estados_egreso: {
            mapeo: {
                '1': 'VIVO',
                '2': 'ESTABLE',
                '3': 'INESTABLE',
                '4': 'FALLECIDO'
            },
            coords: {
                'VIVO': [126, 869],
                'ESTABLE': [225, 869],
                'INESTABLE': [325, 869],
                'FALLECIDO': [424, 869]
            }
        },
        modalidades_egreso: {
            mapeo: {
                '1': 'ALTA DEFINITIVA',
                '2': 'CONSULTA EXTERNA',
                '3': 'OBSERVACIÓN DE EMERGENCIA'
            },
            coords: {
                'ALTA DEFINITIVA': [510, 869],
                'CONSULTA EXTERNA': [595, 869],
                'OBSERVACIÓN DE EMERGENCIA': [714, 869]
            }
        },
        tipos_egreso: {
            mapeo: {
                '1': 'HOSPITALIZACIÓN',
                '2': 'REFERENCIA',
                '3': 'REFERENCIA INVERSA',
                '4': 'DERIVACIÓN'
            },
            coords: {
                'HOSPITALIZACIÓN': [126, 883],
                'REFERENCIA': [225, 883],
                'REFERENCIA INVERSA': [325, 883],
                'DERIVACIÓN': [424, 883]
            }
        }
    };

    // Procesar cada tipo de egreso
    Object.keys(configuracion).forEach(tipoEgreso => {
        const config = configuracion[tipoEgreso];
        const valores = seccionO[tipoEgreso] || [];

        valores.forEach(codigo => {
            const texto = config.mapeo[codigo];
            const coord = config.coords[texto];
            if (coord) {
                pdf.text('X', coord[0], coord[1]);
            }
        });
    });

    // Campos de texto
    pdf.text((seccionO.establecimiento_egreso || '').toUpperCase(), 530, 883);

    const textoLargo = (seccionO.observaciones|| '').toUpperCase();
    const lineas = pdf.splitTextToSize(textoLargo, 540);

    let y = 898; // punto inicial en vertical
    const salto = 14; // distancia entre líneas

    lineas.forEach(linea => {
        pdf.text(linea, 133, y);
        y += salto; // baja para la siguiente línea
    });

    pdf.text(seccionO.dias_reposo || '', 704, 912);
}

// === FUNCIÓN PARA LLENAR SECCIÓN P EN PDF ===
function llenarSeccionPPDF(pdf, seccionP) {
    // Datos básicos del profesional
    pdf.text(seccionP.fecha || '', 55, 965);
    pdf.text(formatearHora008(seccionP.hora || ''), 127, 965);
    pdf.text((seccionP.primer_nombre || '').toUpperCase(), 260, 965);
    pdf.text((seccionP.primer_apellido || '').toUpperCase(), 460, 965);
    pdf.text((seccionP.segundo_apellido || '').toUpperCase(), 640, 965);
    pdf.text((seccionP.documento || '').toUpperCase(), 70, 1000);

    try {
        // Procesar firma
        if (seccionP.firma_base64 && seccionP.firma_base64.trim() !== '') {
            pdf.addImage(seccionP.firma_base64, 'JPEG', 280, 980, 80, 40);
        } else if (seccionP.firma_existe) {
            pdf.text('FIRMA REGISTRADA', 220, 1000);
        } else {
            pdf.text('_____________________', 220, 1000);
            pdf.text('FIRMA', 220, 1015);
        }

        // Procesar sello
        if (seccionP.sello_base64 && seccionP.sello_base64.trim() !== '') {
            pdf.addImage(seccionP.sello_base64, 'JPEG', 550, 980, 80, 40);
        } else if (seccionP.sello_existe) {
            pdf.text('SELLO REGISTRADO', 520, 1000);
        } else {
            pdf.text('_____________________', 520, 1000);
            pdf.text('SELLO', 520, 1015);
        }

    } catch (error) {
        console.error('❌ Error procesando imágenes:', error);
        pdf.text('_____________________', 220, 1000);
        pdf.text('FIRMA', 220, 1015);
        pdf.text('_____________________', 520, 1000);
        pdf.text('SELLO', 520, 1015);
    }
}

// === FUNCIÓN MEJORADA PARA INICIALIZAR PDF DINÁMICAMENTE ===
function inicializarGeneradorPDF() {

    const btnGenerarPDF = document.getElementById('btn-generar-pdf-008');

    if (btnGenerarPDF) {
        // Remover event listeners existentes
        const newBtn = btnGenerarPDF.cloneNode(true);
        btnGenerarPDF.parentNode.replaceChild(newBtn, btnGenerarPDF);

        // Agregar nuevo event listener
        newBtn.addEventListener('click', async (event) => {
            event.preventDefault();
            await generatePDF();
        });

        window.pdfSystemInitialized = true;
        return true;
    } else {
        return false;
    }
}

// === FUNCIÓN PARA REINICIALIZAR PDF CUANDO SE CARGA CONTENIDO DINÁMICO ===
function reinicializarGeneradorPDF() {

    // Esperar un poco para que el DOM se establezca
    setTimeout(() => {
        const exito = inicializarGeneradorPDF();
        if (exito) {
        } else {
            console.warn('⚠️ No se pudo reinicializar PDF, intentando de nuevo...');
            setTimeout(() => {
                inicializarGeneradorPDF();
            }, 1000);
        }
    }, 500);
}

// === FUNCIÓN PARA DETECTAR CUANDO SE CARGA EL FORMULARIO ===
function observarFormulario() {
    const observer = new MutationObserver((mutations) => {
        mutations.forEach((mutation) => {
            mutation.addedNodes.forEach((node) => {
                if (node.nodeType === 1 && node.id === 'formulario-completo') {
                    reinicializarGeneradorPDF();
                }

                if (node.nodeType === 1 && node.querySelector && node.querySelector('#btn-generar-pdf-008')) {
                    reinicializarGeneradorPDF();
                }
            });
        });
    });

    observer.observe(document.body, {
        childList: true,
        subtree: true
    });

}

// === FUNCIÓN GLOBAL PARA INICIALIZAR PDF DESDE OTROS SCRIPTS ===
window.inicializarPDF = function () {
    reinicializarGeneradorPDF();
};

// === HACER FUNCIÓN GLOBAL PARA USO EXTERNO ===
window.generatePDF = generatePDF;
window.reinicializarGeneradorPDF = reinicializarGeneradorPDF;

// === AUTO-INICIALIZACIÓN MEJORADA ===
document.addEventListener('DOMContentLoaded', function () {

    // Configurar observer
    observarFormulario();

    // Intentar inicializar inmediatamente
    setTimeout(() => {
        inicializarGeneradorPDF();
    }, 1000);

    // Intentar de nuevo después de un tiempo
    setTimeout(() => {
        if (!window.pdfSystemInitialized) {
            inicializarGeneradorPDF();
        }
    }, 3000);
});

if (document.readyState === 'complete' || document.readyState === 'interactive') {
    observarFormulario();
    setTimeout(() => {
        inicializarGeneradorPDF();
    }, 500);
}

window.pdfSystemLoaded = true;
