$(document).ready(function () {

    // VERIFICAR QUE ESTAMOS EN CONTEXTO DE MÉDICOS
    if (typeof window.contextoMedico === 'undefined' || !window.contextoMedico) {
        return;
    }

    // VERIFICAR QUE NO ESTAMOS EN CONTEXTO DE ENFERMERÍA
    if (typeof window.contextoEnfermeria !== 'undefined' && window.contextoEnfermeria) {
        return;
    }


    // Verificar si se deben precargar datos automáticamente - VARIABLES ESPECÍFICAS MÉDICOS
    if (typeof window.precargarDatosMedicos !== 'undefined' && window.precargarDatosMedicos === true) {


        // Precargar nombre del admisionista original si está disponible
        if (typeof window.admisionistaMedicos !== 'undefined' && window.admisionistaMedicos) {
            setTimeout(() => {
                $('#adm_admisionista_nombre').val(window.admisionistaMedicos);
            }, 200);
        }

        // Precargar datos del paciente si están disponibles
        if (typeof window.datosPacienteMedicos !== 'undefined' && window.datosPacienteMedicos) {
            setTimeout(() => {
                precargarDatosPacienteMedicos(window.datosPacienteMedicos);
            }, 300);
        }

        // Precargar datos de atención si están disponibles
        if (typeof window.datosAtencionMedicos !== 'undefined' && window.datosAtencionMedicos) {
            setTimeout(() => {
                precargarDatosAtencionMedicos(window.datosAtencionMedicos);
            }, 500);
        }

        // PRECARGA Y BLOQUEO DE CONSTANTES VITALES
        if (typeof window.datosConstantesVitalesMedicos !== 'undefined' && window.datosConstantesVitalesMedicos) {
            setTimeout(() => {
                precargarYBloquearConstantesVitales(window.datosConstantesVitalesMedicos);
            }, 700);
        }

        // Bloquear TODAS las secciones A, B y G para solo lectura
        setTimeout(() => {
            bloquearTodasLasSeccionesMedicos();
        }, 900);

        // Auto-completar datos del médico en sección P
        setTimeout(() => {
            autoCompletarDatosMedico();
        }, 1000);
    }
});

// FUNCIÓN PRINCIPAL PARA CONSTANTES VITALES
function precargarYBloquearConstantesVitales(constantesVitales) {

    if (!constantesVitales || Object.keys(constantesVitales).length === 0) {
        return;
    }

    // Mapeo de campos de constantes vitales
    const camposConstantesVitales = {
        'cv_sin_vitales': constantesVitales.cv_sin_vitales,
        'cv_presion_arterial': constantesVitales.cv_presion_arterial,
        'cv_pulso': constantesVitales.cv_pulso,
        'cv_frec_resp': constantesVitales.cv_frec_resp,
        'cv_pulsioximetria': constantesVitales.cv_pulsioximetria,
        'cv_perimetro_cefalico': constantesVitales.cv_perimetro_cefalico,
        'cv_peso': constantesVitales.cv_peso,
        'cv_talla': constantesVitales.cv_talla,
        'cv_glicemia': constantesVitales.cv_glicemia,
        'cv_reaccion_pupilar_der': constantesVitales.cv_reaccion_pupilar_der,
        'cv_reaccion_pupilar_izq': constantesVitales.cv_reaccion_pupilar_izq,
        'cv_llenado_capilar': constantesVitales.cv_llenado_capilar,
        'cv_glasgow_ocular': constantesVitales.cv_glasgow_ocular,
        'cv_glasgow_verbal': constantesVitales.cv_glasgow_verbal,
        'cv_glasgow_motora': constantesVitales.cv_glasgow_motora,
        'cv_triaje_color': constantesVitales.cv_triaje_color || constantesVitales.ate_colores

    };


    // Llenar cada campo y hacer readonly/disabled
    Object.keys(camposConstantesVitales).forEach(fieldName => {
        const element = document.querySelector(`[name="${fieldName}"]`);
        const valor = camposConstantesVitales[fieldName];

        if (element && valor !== undefined && valor !== null && valor !== '') {
            if (element.type === 'checkbox') {
                // Manejar checkboxes
                element.checked = valor === '1' || valor === true || valor === 1;
                element.disabled = true;
                element.style.cursor = 'not-allowed';

                const label = element.closest('label');
                if (label) {
                    label.style.opacity = '0.6';
                    label.style.cursor = 'not-allowed';
                    label.title = 'Campo bloqueado - Dato registrado por enfermería';
                }

            } else if (element.tagName === 'SELECT') {
                // Manejar selects
                element.value = valor;
                element.disabled = true;
                element.style.backgroundColor = '#f3f4f6';
                element.style.cursor = 'not-allowed';

                // ESPECIAL PARA cv_triaje_color: aplicar el color visual
                if (fieldName === 'cv_triaje_color' && typeof cambiarColorTriaje === 'function') {
                    // Llamar a la función para aplicar el estilo visual del color
                    cambiarColorTriaje(element);
                }

            } else {
                // Manejar inputs de texto/número
                element.value = valor;
                element.setAttribute('readonly', true);
                element.style.backgroundColor = '#f3f4f6';
                element.style.cursor = 'not-allowed';
                element.style.borderColor = '#d1d5db';
            }
        }
    });

    // Bloquear específicamente la sección G completa
    bloquearSeccionGCompleta();

}

// Función para bloquear completamente la sección G
function bloquearSeccionGCompleta() {
    // Buscar la sección G por diferentes posibles selectores
    const seccionG = document.querySelector('#seccion-g, .seccion-g, [data-seccion="g"]') ||
        document.querySelector('table').closest('div'); // Fallback

    if (seccionG) {
        // Bloquear todos los inputs dentro de la sección G
        const todosLosCampos = seccionG.querySelectorAll('input, select, textarea');
        todosLosCampos.forEach(campo => {
            if (campo.type === 'checkbox' || campo.type === 'radio') {
                campo.disabled = true;
            } else {
                campo.setAttribute('readonly', true);
                campo.style.backgroundColor = '#f3f4f6';
                campo.style.borderColor = '#d1d5db';
            }
            campo.style.cursor = 'not-allowed';
        });

        // Agregar una etiqueta visual indicando que la sección está bloqueada
        const headerSeccionG = seccionG.querySelector('th, .header-main, h3, h4');
        if (headerSeccionG && !headerSeccionG.querySelector('.readonly-indicator')) {
            const indicator = document.createElement('span');
            indicator.className = 'readonly-indicator';
            indicator.style.cssText = 'color: #dc2626; font-size: 0.8em; margin-left: 10px;';
            indicator.textContent = '(Solo lectura - Registrado por enfermería)';
            headerSeccionG.appendChild(indicator);
        }

    } else {
    }
}

// Función para auto-completar datos del médico en sección P
function autoCompletarDatosMedico() {
    if (typeof window.medicoActual === 'undefined') {
        return;
    }

    // Usar los nombres ya separados correctamente
    const primerNombre = window.medicoActual.primer_nombre || window.medicoActual.nombre_completo?.trim().split(' ')[0] || '';
    const primerApellido = window.medicoActual.primer_apellido || '';
    const segundoApellido = window.medicoActual.segundo_apellido || '';
    const documento = window.medicoActual.documento || window.medicoActual.usu_nro_documento || '';

    // Llenar automáticamente los datos del profesional responsable
    const profPrimerNombre = document.querySelector('input[name="prof_primer_nombre"]');
    const profPrimerApellido = document.querySelector('input[name="prof_primer_apellido"]');
    const profSegundoApellido = document.querySelector('input[name="prof_segundo_apellido"]');
    const profDocumento = document.querySelector('input[name="prof_documento"]');

    if (profPrimerNombre && primerNombre) {
        profPrimerNombre.value = primerNombre;
    }
    if (profPrimerApellido && primerApellido) {
        profPrimerApellido.value = primerApellido;
    }
    if (profSegundoApellido && segundoApellido) {
        profSegundoApellido.value = segundoApellido;
    }
    if (profDocumento && documento) {
        profDocumento.value = documento;
    } else {
        console.warn('Documento no encontrado o campo no existe');
    }


}

// Función para precargar datos del paciente - ESPECÍFICA MÉDICOS
function precargarDatosPacienteMedicos(datos) {

    // Historia clínica
    if (datos.pac_cedula) {
        $('#estab_historia_clinica').val(datos.pac_cedula);
        $('#adm_historia_clinica_estab_si').prop('checked', true);
    }
    
    // Nombres y apellidos
    if (datos.apellido1) $('#pac_apellido1').val(datos.apellido1);
    if (datos.apellido2) $('#pac_apellido2').val(datos.apellido2);
    if (datos.nombre1) $('#pac_nombre1').val(datos.nombre1);
    if (datos.nombre2) $('#pac_nombre2').val(datos.nombre2);
    
    // Tipo documento
    if (datos.tipo_documento) $('#pac_tipo_documento').val(datos.tipo_documento);
    
    // Datos personales
    if (datos.estado_civil) $('#pac_estado_civil').val(datos.estado_civil);
    if (datos.sexo) $('#pac_sexo').val(datos.sexo);
    if (datos.telefono_fijo) $('#pac_telefono_fijo').val(datos.telefono_fijo);
    if (datos.telefono_celular) $('#pac_telefono_celular').val(datos.telefono_celular);
    if (datos.grupo_prioritario == 1 || datos.grupo_prioritario === true) {
        $('#pac_grupo_prioritario_si').prop('checked', true);
    } else {
        $('#pac_grupo_prioritario_no').prop('checked', true);
    }
    if (datos.grupo_sanguineo) $('#pac_grupo_prioritario_especifique').val(datos.grupo_sanguineo);
    
    // Limpiar y formatear fecha de nacimiento
    if (datos.fecha_nacimiento) {
        let fechaLimpia = datos.fecha_nacimiento.trim();
        // Si viene con formato datetime, extraer solo la fecha
        if (fechaLimpia.includes(' ')) {
            fechaLimpia = fechaLimpia.split(' ')[0];
        }
        // Asegurar formato YYYY-MM-DD
        const fecha = new Date(fechaLimpia);
        if (!isNaN(fecha.getTime())) {
            const fechaFormateada = fecha.getFullYear() + '-' + 
                                  String(fecha.getMonth() + 1).padStart(2, '0') + '-' + 
                                  String(fecha.getDate()).padStart(2, '0');
            $('#pac_fecha_nacimiento').val(fechaFormateada);
        }
    }
    
    if (datos.lugar_nacimiento) $('#pac_lugar_nacimiento').val(datos.lugar_nacimiento);
    
    // Nacionalidad y otros
    if (datos.nacionalidad) $('#pac_nacionalidad').val(datos.nacionalidad);
    if (datos.etnia) $('#pac_etnia').val(datos.etnia);
    if (datos.nivel_educacion) $('#pac_nivel_educacion').val(datos.nivel_educacion);
    if (datos.estado_educacion) $('#pac_estado_educacion').val(datos.estado_educacion);
    if (datos.tipo_empresa) $('#pac_tipo_empresa').val(datos.tipo_empresa);
    if (datos.ocupacion) $('#pac_ocupacion').val(datos.ocupacion);
    if (datos.seguro) $('#pac_seguro').val(datos.seguro);
    if (datos.nacionalidadIndigena) $('#pac_nacionalidad_indigena').val(datos.nacionalidadIndigena);
    if (datos.puebloIndigena) $('#pac_pueblo_indigena').val(datos.puebloIndigena);
    
    // Dirección de residencia (mapear a campos existentes)
    if (datos.res_provincia) $('#res_provincia').val(datos.res_provincia);
    if (datos.res_canton) $('#res_canton').val(datos.res_canton);
    if (datos.res_parroquia) $('#res_parroquia').val(datos.res_parroquia);
    if (datos.res_barrio_sector) $('#res_barrio_sector').val(datos.res_barrio_sector);
    if (datos.res_direccion) $('#res_calle_principal').val(datos.res_direccion);
    if (datos.res_calle_secundaria) $('#res_calle_secundaria').val(datos.res_calle_secundaria);
    if (datos.res_referencia) $('#res_referencia').val(datos.res_referencia);

    // Contacto de emergencia
    if (datos.contacto_emerg_nombre) $('#contacto_emerg_nombre').val(datos.contacto_emerg_nombre);
    if (datos.contacto_emerg_parentesco) $('#contacto_emerg_parentesco').val(datos.contacto_emerg_parentesco);
    if (datos.contacto_emerg_direccion) $('#contacto_emerg_direccion').val(datos.contacto_emerg_direccion);
    if (datos.contacto_emerg_telefono) $('#contacto_emerg_telefono').val(datos.contacto_emerg_telefono);
    
    // **CORRECCIÓN AQUÍ: Manejar edad y unidad desde la base de datos**
    if (datos.pac_edad_valor) {
        $('#pac_edad_valor').val(datos.pac_edad_valor);
        
        // Marcar la unidad correcta según lo que viene de la BD
        if (datos.pac_edad_unidad) {
            switch (datos.pac_edad_unidad.toUpperCase()) {
                case 'A':
                case 'AÑOS':
                case 'AÑO':
                    $('#pac_edad_unidad_a').prop('checked', true);
                    break;
                case 'M':
                case 'MESES':
                case 'MES':
                    $('#pac_edad_unidad_m').prop('checked', true);
                    break;
                case 'D':
                case 'DÍAS':
                case 'DIA':
                case 'DIAS':
                    $('#pac_edad_unidad_d').prop('checked', true);
                    break;
                case 'H':
                case 'HORAS':
                case 'HORA':
                    $('#pac_edad_unidad_h').prop('checked', true);
                    break;
                default:
                    // Si no reconoce la unidad, calcular desde fecha de nacimiento
                    calcularEdadDesdeFechaNacimiento(datos.fecha_nacimiento);
            }
        } else {
            // Si no hay unidad definida, calcular desde fecha de nacimiento
            calcularEdadDesdeFechaNacimiento(datos.fecha_nacimiento);
        }
    } else {
        // Si no hay edad en BD, calcular desde fecha de nacimiento
        calcularEdadDesdeFechaNacimiento(datos.fecha_nacimiento);
    }
    
    // Establecer fecha de admisión como hoy usando zona horaria local
    const fechaHoy = new Date();
    const año = fechaHoy.getFullYear();
    const mes = String(fechaHoy.getMonth() + 1).padStart(2, '0');
    const día = String(fechaHoy.getDate()).padStart(2, '0');
    $('#adm_fecha').val(`${año}-${mes}-${día}`);

}

// Función para precargar datos de atención - ESPECÍFICA MÉDICOS
function precargarDatosAtencionMedicos(datos) {

    if (datos.lleg_codigo) $('#forma_llegada').val(datos.lleg_codigo);
    if (datos.ate_fuente_informacion) $('#fuente_informacion').val(datos.ate_fuente_informacion);
    if (datos.ate_ins_entrega_paciente) $('#entrega_paciente_nombre_inst').val(datos.ate_ins_entrega_paciente);
    if (datos.ate_telefono) $('#entrega_paciente_telefono').val(datos.ate_telefono);
    if (datos.ate_colores) {
        $('select[name="cv_triaje_color"]').val(datos.ate_colores);
    }
}

// Función para precargar constantes vitales (versión array) - ESPECÍFICA MÉDICOS
function precargarConstantesVitalesMedicos(datos) {

    if (Array.isArray(datos) && datos.length > 0) {
        // Tomar las constantes vitales más recientes (primera del array)
        const ultimasConstantes = datos[0];
        if (ultimasConstantes.con_presion_arterial) $('#cv_presion_arterial').val(ultimasConstantes.con_presion_arterial);
        if (ultimasConstantes.con_pulso) $('#cv_pulso').val(ultimasConstantes.con_pulso);
        if (ultimasConstantes.con_frec_respiratoria) $('#cv_frec_resp').val(ultimasConstantes.con_frec_respiratoria);
        if (ultimasConstantes.con_pulsioximetria) $('#cv_pulsioximetria').val(ultimasConstantes.con_pulsioximetria);
        if (ultimasConstantes.con_perimetro_cefalico) $('#cv_perimetro_cefalico').val(ultimasConstantes.con_perimetro_cefalico);
        if (ultimasConstantes.con_peso) $('#cv_peso').val(ultimasConstantes.con_peso);
        if (ultimasConstantes.con_talla) $('#cv_talla').val(ultimasConstantes.con_talla);
        if (ultimasConstantes.con_glucemia_capilar) $('#cv_glicemia').val(ultimasConstantes.con_glucemia_capilar);
        if (ultimasConstantes.con_reaccion_pupila_der) $('#cv_reaccion_pupilar_der').val(ultimasConstantes.con_reaccion_pupila_der);
        if (ultimasConstantes.con_reaccion_pupila_izq) $('#cv_reaccion_pupilar_izq').val(ultimasConstantes.con_reaccion_pupila_izq);
        if (ultimasConstantes.con_t_lleno_capilar) $('#cv_llenado_capilar').val(ultimasConstantes.con_t_lleno_capilar);
        if (ultimasConstantes.con_glasgow_ocular) $('#cv_glasgow_ocular').val(ultimasConstantes.con_glasgow_ocular);
        if (ultimasConstantes.con_glasgow_verbal) $('#cv_glasgow_verbal').val(ultimasConstantes.con_glasgow_verbal);
        if (ultimasConstantes.con_glasgow_motora) $('#cv_glasgow_motora').val(ultimasConstantes.con_glasgow_motora);
        if (ultimasConstantes.ate_colores) $('#cv_triaje_color').val(ultimasConstantes.ate_colores);

    }
}

// Función para bloquear TODAS las secciones (A, B y G) para solo lectura - ESPECÍFICA MÉDICOS
function bloquearTodasLasSeccionesMedicos() {

    // Bloquear campos de la Sección A (basado en el contenido de seccion_a.php)
    // Campos del establecimiento - están en la primera tabla
    $('input[name="estab_institucion"], input[name="estab_unicode"], input[name="estab_nombre"], input[name="estab_historia_clinica"], input[name="estab_archivo"]').each(function () {
        const $this = $(this);
        if (!$this.prop('readonly')) { // Solo si no está ya readonly
            $this.prop('readonly', true);
            $this.css({
                'background-color': 'white', // Fondo blanco
                'border-color': '#d1d5db', // Borde gris neutral
                'color': '#374151', // Texto gris oscuro normal
                'cursor': 'not-allowed'
            });
        }
    });

    // Bloquear campos de la Sección B (registro de admisión del paciente)
    // Usar selectores que NO incluyan campos de la sección G
    const camposSeccionB = [
        'input[name="adm_fecha"]',
        'input[name="adm_admisionista_nombre"]',
        'input[name="adm_historia_clinica_estab"]',
        'input[name="pac_apellido1"]',
        'input[name="pac_apellido2"]',
        'input[name="pac_nombre1"]',
        'input[name="pac_nombre2"]',
        'select[name="pac_tipo_documento"]',
        'select[name="pac_estado_civil"]',
        'select[name="pac_sexo"]',
        'input[name="pac_telefono_fijo"]',
        'input[name="pac_telefono_celular"]',
        'input[name="pac_fecha_nacimiento"]',
        'input[name="pac_lugar_nacimiento"]',
        'select[name="pac_nacionalidad"]',
        'input[name="pac_edad_valor"]',
        'input[name="pac_edad_unidad"]',
        'input[name="pac_grupo_prioritario"]',
        'input[name="pac_grupo_prioritario_especifique"]',
        'select[name="pac_etnia"]',
        'select[name="pac_nacionalidad_indigena"]',
        'select[name="pac_pueblo_indigena"]',
        'select[name="pac_nivel_educacion"]',
        'select[name="pac_estado_educacion"]',
        'select[name="pac_tipo_empresa"]',
        'input[name="pac_ocupacion"]',
        'select[name="pac_seguro"]',
        'input[name="res_provincia"]',
        'input[name="res_canton"]',
        'input[name="res_parroquia"]',
        'input[name="res_barrio_sector"]',
        'input[name="res_calle_principal"]',
        'input[name="res_calle_secundaria"]',
        'input[name="res_referencia"]',
        'input[name="contacto_emerg_nombre"]',
        'input[name="contacto_emerg_parentesco"]',
        'input[name="contacto_emerg_direccion"]',
        'input[name="contacto_emerg_telefono"]',
        'select[name="forma_llegada"]',
        'input[name="fuente_informacion"]',
        'input[name="entrega_paciente_nombre_inst"]',
        'input[name="entrega_paciente_telefono"]',

        'input[name="prof_hora"]',
        'input[name="prof_primer_nombre"]',
        'input[name="prof_primer_apellido"]',
        'input[name="prof_segundo_apellido"]',
        'input[name="prof_documento"]'

    ];

    // Aplicar bloqueo a los campos de la Sección B
    camposSeccionB.forEach(selector => {
        $(selector).each(function () {
            const $this = $(this);
            $this.prop('readonly', true);
            if ($this.is('select')) {
                $this.prop('disabled', true);
            }
            $this.css({
                'background-color': 'white', // Fondo blanco
                'border-color': '#d1d5db', // Borde gris neutral
                'color': '#374151', // Texto gris oscuro normal
                'cursor': 'not-allowed'
            });
        });
    });

    // Aplicar bloqueo a los campos de la Sección G
    const camposSeccionG = [
        'input[name="cv_sin_vitales"]',
        'input[name="cv_presion_arterial"]',
        'input[name="cv_pulso"]',
        'input[name="cv_frec_resp"]',
        'input[name="cv_pulsioximetria"]',
        'input[name="cv_perimetro_cefalico"]',
        'input[name="cv_peso"]',
        'input[name="cv_talla"]',
        'input[name="cv_glicemia"]',
        'input[name="cv_reaccion_pupilar_der"]',
        'input[name="cv_reaccion_pupilar_izq"]',
        'input[name="cv_glasgow_ocular"]',
        'input[name="cv_glasgow_verbal"]',
        'input[name="cv_glasgow_motora"]',
        'input[name="cv_llenado_capilar"]',
        'select[name="cv_triaje_color"]'
    ];

    camposSeccionG.forEach(selector => {
        $(selector).each(function () {
            const $this = $(this);
            if ($this.is('input[type="checkbox"]')) {
                $this.prop('disabled', true);
            } else {
                $this.prop('readonly', true);
                if ($this.is('select')) {
                    $this.prop('disabled', true);
                }
            }
            $this.css({
                'background-color': 'white', // Fondo blanco
                'border-color': '#d1d5db', // Borde gris neutral
                'color': '#374151', // Texto gris oscuro normal
                'cursor': 'not-allowed'
            });
        });
    });
    // Bloquear radios buttons específicos con estilos mejorados
    $('input[type="radio"][name="adm_historia_clinica_estab"], \
      input[type="radio"][name="pac_edad_unidad"], \
      input[type="radio"][name="pac_grupo_prioritario"]').each(function() {
        $(this).prop('disabled', true);
        $(this).get(0).style.setProperty('accent-color', '#2563eb', 'important'); // Azul vibrante con !important

        // Estilizar el label del radio
        const label = $(this).closest('label');
        if (label.length) {
            label.css({
                'background-color': 'transparent', // Sin fondo
                'color': '#374151', // Texto gris oscuro normal
                'cursor': 'not-allowed'
            });
        }
    });

}