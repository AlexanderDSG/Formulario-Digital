// ========================================
// PRECARGARDATOSESPECIALIDAD.JS - ESPECIALISTAS (CORREGIDO)
// ========================================

/**
 * Formatear hora de SQL Server al formato HTML5 (HH:mm:ss)
 * Elimina los microsegundos que SQL Server agrega
 */
function formatearHoraHTML(hora) {
    if (!hora) return '';

    // Si viene en formato TIME de SQL Server (09:35:00.0000000)
    if (typeof hora === 'string' && hora.includes('.')) {
        hora = hora.split('.')[0];
    }

    // Asegurar formato HH:mm:ss
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

$(document).ready(function () {

    // VERIFICAR QUE ESTAMOS EN CONTEXTO DE ESPECIALIDADES
    if (typeof window.contextoEspecialidad === 'undefined' || !window.contextoEspecialidad) {
        return;
    }


    // Verificar si se deben precargar datos automáticamente - VARIABLES ESPECÍFICAS ESPECIALIDADES
    if (typeof window.precargarDatosEspecialidades !== 'undefined' && window.precargarDatosEspecialidades === true) {

        // Precargar nombre del admisionista original si está disponible
        if (typeof window.admisionistaEspecialidades !== 'undefined' && window.admisionistaEspecialidades) {
            setTimeout(() => {
                $('#adm_admisionista_nombre').val(window.admisionistaEspecialidades);
            }, 500);
        }

        // Precargar datos del paciente si están disponibles
        if (typeof window.datosPacienteEspecialidades !== 'undefined' && window.datosPacienteEspecialidades) {
            setTimeout(() => {
                precargarDatosPaciente(window.datosPacienteEspecialidades);
            }, 550);
        }

        // Precargar datos de atención si están disponibles
        if (typeof window.datosAtencionEspecialidades !== 'undefined' && window.datosAtencionEspecialidades) {
            setTimeout(() => {
                precargarDatosAtencion(window.datosAtencionEspecialidades);
            }, 600);
        }

        // 🔥 PRECARGA Y BLOQUEO DE CONSTANTES VITALES PARA ESPECIALIDADES
        if (typeof window.datosConstantesVitalesEspecialidades !== 'undefined' && window.datosConstantesVitalesEspecialidades) {
            setTimeout(() => {
                precargarYBloquearConstantesVitales(window.datosConstantesVitalesEspecialidades);
            }, 650);
        }

        // Precargar Sección C (Inicio de Atención)
        if (typeof window.datosSeccionCEspecialidades !== 'undefined' &&
            Object.keys(window.datosSeccionCEspecialidades).length > 0) {
            setTimeout(() => {
                precargarSeccionCCompleto(window.datosSeccionCEspecialidades);
            }, 700);
        }

        // Precargar Sección D (Eventos/Accidentes)
        if (typeof window.datosSeccionDEspecialidades !== 'undefined' &&
            Object.keys(window.datosSeccionDEspecialidades).length > 0) {
            setTimeout(() => {
                precargarSeccionDCompleto(window.datosSeccionDEspecialidades);
            }, 1000);
        }

        // Bloquear TODAS las secciones A, B y G para solo lectura - DESPUÉS de precargar
        setTimeout(() => {
            bloquearTodasLasSecciones();
        }, 1200);

        // Auto-completar datos del médico especialista en sección P
        setTimeout(() => {
            autoCompletarDatosMedico();
        }, 500);

    }
});
// FUNCIÓN: Precargar datos del paciente - UNIFICADA CON MÉDICOS
function precargarDatosPaciente(datos) {

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

    // Teléfonos - con logging para debug - MAPEO CORREGIDO
    const telefonoFijo = datos.pac_telefono_fijo || datos.pac_telefono || datos.telefono_fijo;
    const telefonoCelular = datos.pac_telefono_celular || datos.pac_celular || datos.telefono_celular;

    if (telefonoFijo) {
        $('#pac_telefono_fijo').val(telefonoFijo);
    }
    if (telefonoCelular) {
        $('#pac_telefono_celular').val(telefonoCelular);
    }

    if (datos.pac_grupo_prioritario !== undefined) {
        if (datos.pac_grupo_prioritario == 1 || datos.pac_grupo_prioritario == 'si' || datos.pac_grupo_prioritario === true) {
            $('#pac_grupo_prioritario_si').prop('checked', true);
        } else {
            $('#pac_grupo_prioritario_no').prop('checked', true);
        }
    } else if (datos.grupo_prioritario !== undefined) {
        if (datos.grupo_prioritario == 1 || datos.grupo_prioritario == 'si' || datos.grupo_prioritario === true) {
            $('#pac_grupo_prioritario_si').prop('checked', true);
        } else {
            $('#pac_grupo_prioritario_no').prop('checked', true);
        }
    }

    // Especificar grupo prioritario - MAPEO CORREGIDO
    const especificar = datos.pac_grupo_prioritario_especifique || datos.pac_grupo_sanguineo || datos.grupo_sanguineo;
    if (especificar) {
        $('#pac_grupo_prioritario_especifique').val(especificar);
    }

    // Limpiar y formatear fecha de nacimiento - Mapeo mejorado con logging
    if (datos.pac_fecha_nac || datos.fecha_nacimiento) {
        let fechaOriginal = datos.pac_fecha_nac || datos.fecha_nacimiento;

        let fechaLimpia = fechaOriginal.toString().trim();
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

    // Lugar de nacimiento - mapeo múltiple
    const lugarNacimiento = datos.pac_lugar_nac || datos.lugar_nacimiento || datos.pac_lugar_nacimiento;
    if (lugarNacimiento) {
        $('#pac_lugar_nacimiento').val(lugarNacimiento);
    }

    // Nacionalidad y otros
    if (datos.nacionalidad) $('#pac_nacionalidad').val(datos.nacionalidad);
    if (datos.etnia) $('#pac_etnia').val(datos.etnia);
    if (datos.nivel_educacion || datos.nedu_codigo) $('#pac_nivel_educacion').val(datos.nivel_educacion || datos.nedu_codigo);
    if (datos.estado_educacion || datos.estado_nivel_educ) $('#pac_estado_educacion').val(datos.estado_educacion || datos.estado_nivel_educ);
    if (datos.tipo_empresa || datos.empresa) $('#pac_tipo_empresa').val(datos.tipo_empresa || datos.empresa);
    // Ocupación/Profesión - Mapeo corregido con logging
    const ocupacion = datos.pac_ocupacion || datos.ocupacion;
    if (ocupacion) {
        $('#pac_ocupacion').val(ocupacion);
    }
    if (datos.seguro || datos.seg_codigo) $('#pac_seguro').val(datos.seguro || datos.seg_codigo);
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

// FUNCIÓN: Precargar datos de atención - UNIFICADA
function precargarDatosAtencion(datos) {

    if (datos.lleg_codigo) $('#forma_llegada').val(datos.lleg_codigo);
    if (datos.ate_fuente_informacion) $('#fuente_informacion').val(datos.ate_fuente_informacion);
    if (datos.ate_ins_entrega_paciente) $('#entrega_paciente_nombre_inst').val(datos.ate_ins_entrega_paciente);
    if (datos.ate_telefono) $('#entrega_paciente_telefono').val(datos.ate_telefono);
    if (datos.ate_colores) {
        $('select[name="cv_triaje_color"]').val(datos.ate_colores);
    }

    // NUEVOS CAMPOS AGREGADOS:
    // Custodia policial
    if (datos.ate_custodia_policial) {
        if (datos.ate_custodia_policial.toLowerCase() === 'si' || datos.ate_custodia_policial === '1') {
            $('#acc_custodia_policial_si').prop('checked', true);
        } else if (datos.ate_custodia_policial.toLowerCase() === 'no' || datos.ate_custodia_policial === '0') {
            $('#acc_custodia_policial_no').prop('checked', true);
        }
    }

    // Aliento etílico (sugestivo de ingesta alcohólica)
    if (datos.ate_aliento_etilico) {
        if (datos.ate_aliento_etilico.toLowerCase() === 'si' ||
            datos.ate_aliento_etilico === '1' ||
            datos.ate_aliento_etilico === true) {
            $('#acc_sugestivo_alcohol').prop('checked', true);
        }
    }
}


// 🔥 FUNCIÓN PRINCIPAL PARA CONSTANTES VITALES - ESPECIALIDADES
function precargarYBloquearConstantesVitales(constantesVitales) {
    if (!constantesVitales || Object.keys(constantesVitales).length === 0) {
        return;
    }

    // Mapeo de campos de constantes vitales (mismo que médicos generales)
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
                element.style.setProperty('accent-color', '#2563eb', 'important'); // Azul vibrante con !important

                const label = element.closest('label');
                if (label) {
                    label.style.backgroundColor = 'transparent'; // Sin fondo
                    label.style.color = '#374151'; // Texto gris oscuro normal
                    label.style.cursor = 'not-allowed';
                    label.title = 'Campo bloqueado - Dato registrado por enfermería';
                }

            } else if (element.tagName === 'SELECT') {
                // Manejar selects
                element.value = valor;
                element.disabled = true;
                element.style.backgroundColor = 'white'; // Fondo blanco
                element.style.borderColor = '#d1d5db'; // Borde gris neutral
                element.style.color = '#374151'; // Texto gris oscuro normal
                element.style.cursor = 'not-allowed';

                // 🔥 ESPECIAL PARA cv_triaje_color: aplicar el color visual
                if (fieldName === 'cv_triaje_color' && typeof cambiarColorTriaje === 'function') {
                    cambiarColorTriaje(element);
                }

            } else {
                // Manejar inputs de texto/número
                element.value = valor;
                element.setAttribute('readonly', true);
                element.style.backgroundColor = 'white'; // Fondo blanco
                element.style.borderColor = '#d1d5db'; // Borde gris neutral
                element.style.color = '#374151'; // Texto gris oscuro normal
                element.style.cursor = 'not-allowed';
            }

            // Agregar tooltip explicativo
            element.title = 'Campo bloqueado - Registrado por enfermería';
        }
    });



    // Bloquear específicamente la sección G completa
    bloquearSeccionGCompleta();
}

function precargarSeccionCCompleto(datos) {

    if (!datos || Object.keys(datos).length === 0) {
        console.warn('precargarSeccionCCompleto: Datos vacíos o inválidos');
        return;
    }

    // Mapeo CORREGIDO de campos según seccion_c.php
    const camposSeccionC = [
        { campo: 'inicio_atencion_fecha', valor: datos.iat_fecha, tipo: 'date' },
        { campo: 'inicio_atencion_hora', valor: formatearHoraHTML(datos.iat_hora), tipo: 'time' },
        { campo: 'inicio_atencion_motivo', valor: datos.iat_motivo, tipo: 'textarea' }
    ];

    // Llenar cada campo
    camposSeccionC.forEach(({ campo, valor, tipo }) => {
        if (valor) {
            const element = document.getElementById(campo);
            if (element) {
                element.value = valor;
                element.setAttribute('title', 'Precargado desde triaje médico');
            } else {
                console.warn(`⚠️ Campo ${campo} no encontrado en el DOM`);
            }
        }
    });

    // CORRECCIÓN: Select de condición con el ID correcto
    if (datos.col_codigo) {
        const selectCondicion = document.getElementById('inicio_atencion_condicion'); // ID correcto

        if (selectCondicion) {
            selectCondicion.value = datos.col_codigo;
            selectCondicion.setAttribute('title', 'Precargado desde triaje médico');
        } else {
            console.warn('⚠️ Select inicio_atencion_condicion no encontrado');
            // Buscar alternativas
            const alternativeSelect = document.querySelector('select[name="inicio_atencion_condicion"]');
            if (alternativeSelect) {
                alternativeSelect.value = datos.col_codigo;
            }
        }
    }

}

// NUEVA FUNCIÓN: precargarSeccionDCompleto de medico especialista
function precargarSeccionDCompleto(datos) {

    if (!datos || Object.keys(datos).length === 0) {
        console.warn('precargarSeccionDCompleto: Datos vacíos o inválidos');
        return;
    }

    let eventoBase, tiposEventos;

    // Verificar estructura de datos
    if (datos.evento_principal && datos.tipos_eventos) {
        // Nueva estructura con múltiples eventos
        eventoBase = datos.evento_principal;
        tiposEventos = datos.tipos_eventos;

    } else if (Array.isArray(datos)) {
        // Array de eventos directo
        eventoBase = datos[0];
        tiposEventos = datos.map(evento => evento.tev_codigo).filter(codigo => codigo);
    } else {
        // Evento único
        eventoBase = datos;
        tiposEventos = datos.tev_codigo ? [datos.tev_codigo] : [];
    }

    // Llenar campos básicos usando el evento base
    if (eventoBase) {
        const camposEvento = [
            { campo: 'acc_fecha_evento', valor: eventoBase.eve_fecha, tipo: 'date' },
            { campo: 'acc_hora_evento', valor: formatearHoraHTML(eventoBase.eve_hora), tipo: 'time' },
            { campo: 'acc_lugar_evento', valor: eventoBase.eve_lugar, tipo: 'text' },
            { campo: 'acc_direccion_evento', valor: eventoBase.eve_direccion, tipo: 'text' },
            { campo: 'acc_observaciones', valor: eventoBase.eve_observacion, tipo: 'textarea' }
        ];

        camposEvento.forEach(({ campo, valor, tipo }) => {
            if (valor) {
                const element = document.getElementById(campo);
                if (element) {
                    element.value = valor;
                    element.setAttribute('title', 'Precargado desde triaje médico');
                }
            }
        });

        // Notificación
        if (eventoBase.eve_notificacion) {
            const valor = eventoBase.eve_notificacion.toLowerCase();
            if (valor === 'si') {
                $('#acc_notificacion_custodia_si').prop('checked', true);
            } else if (valor === 'no') {
                $('#acc_notificacion_custodia_no').prop('checked', true);
            }
        }
    }

    // Marcar TODOS los tipos de evento
    if (tiposEventos && tiposEventos.length > 0) {
        tiposEventos.forEach(tevCodigo => {
            marcarTipoEvento(tevCodigo);
        });
    }

}

// FUNCIÓN MEJORADA: marcarTipoEvento
function marcarTipoEvento(tevCodigo) {
    // Mapeo de códigos a IDs de checkboxes según seccion_d.php
    const mapaEventos = {
        1: 'acc_tipo_transito',
        2: 'acc_tipo_caida',
        3: 'acc_tipo_quemadura',
        4: 'acc_tipo_mordedura',
        5: 'acc_tipo_ahogamiento',
        6: 'acc_tipo_cuerpo_extrano',
        7: 'acc_tipo_aplastamiento',
        8: 'acc_tipo_arma_fuego',
        9: 'acc_tipo_arma_cp',
        10: 'acc_tipo_rina',
        11: 'acc_tipo_violencia_familiar',
        12: 'acc_tipo_violencia_fisica',
        13: 'acc_tipo_violencia_psicologica',
        14: 'acc_tipo_violencia_sexual',
        15: 'acc_tipo_intox_alcohol',
        16: 'acc_tipo_intox_alimentaria',
        17: 'acc_tipo_intox_drogas',
        18: 'acc_tipo_inhalacion_gases',
        19: 'acc_tipo_otra_intox',
        20: 'acc_tipo_picadura',
        21: 'acc_tipo_envenenamiento',
        22: 'acc_anafilaxia_custodia',
        23: 'acc_otro_accidente_custodia'
    };

    const checkboxId = mapaEventos[parseInt(tevCodigo)];
    if (checkboxId) {
        const checkbox = document.getElementById(checkboxId);
        if (checkbox) {
            checkbox.checked = true;
            checkbox.style.accentColor = '#3b82f6';
            checkbox.setAttribute('title', 'Precargado desde triaje médico');



        } else {
            console.warn(`Checkbox ${checkboxId} no encontrado para código ${tevCodigo}`);
        }
    } else {
        console.warn(`No hay mapeo para el código de evento: ${tevCodigo}`);
    }
}


// FUNCIÓN: Auto-completar datos del médico especialista en sección P
function autoCompletarDatosMedico() {

    if (typeof window.medicoActual === 'undefined' || !window.medicoActual) {
        console.warn('⚠ No hay datos del médico actual disponibles');
        return;
    }

    // 🔥 USAR LOS DATOS YA SEPARADOS CORRECTAMENTE EN EL CONTROLADOR
    const primerNombre = window.medicoActual.primer_nombre || '';
    const primerApellido = window.medicoActual.primer_apellido || '';
    const segundoApellido = window.medicoActual.segundo_apellido || '';
    const documento = window.medicoActual.documento || '';

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
    }

    // También llenar fecha y hora actuales
    const profFecha = document.querySelector('input[name="prof_fecha"]');
    const profHora = document.querySelector('input[name="prof_hora"]');

    if (profFecha) {
        const fechaActual = new Date();
        const fechaFormateada = `${fechaActual.getFullYear()}-${String(fechaActual.getMonth() + 1).padStart(2, '0')}-${String(fechaActual.getDate()).padStart(2, '0')}`;
        profFecha.value = fechaFormateada;
    }

    if (profHora) {
        const horaActual = new Date().toTimeString().split(' ')[0].substring(0, 5);
        profHora.value = horaActual;
    }

}




// FUNCIÓN: Bloquear completamente la sección G para especialidades
function bloquearSeccionGCompleta() {
    // Buscar la sección G por diferentes posibles selectores
    const seccionG = document.querySelector('#seccion_g, .seccion_g, [data-seccion="g"]') ||
        document.querySelector('table').closest('div'); // Fallback

    if (seccionG) {
        // Bloquear todos los campos dentro de la sección G
        const todosLosCampos = seccionG.querySelectorAll('input, select, textarea');
        todosLosCampos.forEach(campo => {
            if (campo.type === 'checkbox' || campo.type === 'radio') {
                campo.disabled = true;
                campo.style.setProperty('accent-color', '#2563eb', 'important'); // Azul vibrante con !important

                // Estilizar el label
                const label = campo.closest('label');
                if (label) {
                    label.style.backgroundColor = 'transparent'; // Sin fondo
                    label.style.color = '#374151'; // Texto gris oscuro normal
                }
            } else {
                campo.setAttribute('readonly', true);
                campo.style.backgroundColor = 'white'; // Fondo blanco
                campo.style.borderColor = '#d1d5db'; // Borde gris neutral
                campo.style.color = '#374151'; // Texto gris oscuro normal
            }
            campo.style.cursor = 'not-allowed';
            campo.title = 'Campo bloqueado - Registrado por medico';
        });

        // Agregar etiqueta visual indicando que la sección está bloqueada
        const headerSeccionG = seccionG.querySelector('th, .header-main, h3, h4');
        if (headerSeccionG && !headerSeccionG.querySelector('.readonly-indicator')) {
            const indicator = document.createElement('span');
            indicator.className = 'readonly-indicator';
            indicator.style.cssText = 'color: #dc2626; font-size: 0.8em; margin-left: 10px;';
            indicator.textContent = '(Solo lectura - Registrado por medico)';
            headerSeccionG.appendChild(indicator);
        }
    }
}

// FUNCIÓN: Bloquear todas las secciones (A, B y G) para solo lectura - UNIFICADA
function bloquearTodasLasSecciones() {

    // Campos de la Sección A (establecimiento)
    $('input[name="estab_institucion"], input[name="estab_unicode"], input[name="estab_nombre"], input[name="estab_historia_clinica"], input[name="estab_archivo"]').each(function () {
        const $this = $(this);
        if (!$this.prop('readonly')) {
            $this.prop('readonly', true);
            $this.css({
                'background-color': 'white', // Fondo blanco
                'border-color': '#d1d5db', // Borde gris neutral
                'color': '#374151', // Texto gris oscuro normal
                'cursor': 'not-allowed'
            });
            $this.attr('title', 'Campo bloqueado - Datos del establecimiento');
        }
    });

    const bloquearSecciones = [
        // Campos de la Sección B (registro de admisión del paciente)
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

        //seccion G
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
        'select[name="cv_triaje_color"]',

        //Seccion C
        'input[name="inicio_atencion_hora"]',
        'select[name="inicio_atencion_condicion"]',
        'textarea[name="inicio_atencion_motivo"]',

        //Seccion D
        'input[name="acc_fecha_evento"]',
        'input[name="acc_hora_evento"]',
        'input[name="acc_lugar_evento"]',
        'input[name="acc_hora_evento"]',
        'input[name="acc_hora_evento"]',
        'input[name="acc_direccion_evento"]',
        'textarea[name="acc_observaciones"]',

        //Seccion P
        'input[name="prof_hora"]',
        'input[name="prof_primer_nombre"]',
        'input[name="prof_primer_apellido"]',
        'input[name="prof_segundo_apellido"]',
        'input[name="prof_documento"]',
    ];

    // Aplicar bloqueo a los campos de la Sección B - Solo si no están vacíos
    bloquearSecciones.forEach(selector => {
        $(selector).each(function () {
            const $this = $(this);
            // Solo bloquear si ya tiene valor o si no es un campo crítico que debe precargarse
            const camposCriticos = ['pac_ocupacion', 'pac_grupo_prioritario', 'pac_fecha_nacimiento', 'pac_telefono_fijo', 'pac_telefono_celular'];
            const esCampoGrupo = $this.attr('name') === 'pac_grupo_prioritario';
            const esCampoCritico = camposCriticos.some(campo => $this.attr('name') === campo);

            // Para campos críticos, esperar a que se precarguen antes de bloquear
            if (esCampoGrupo) {
                // Para radios de grupo prioritario, verificar si alguno está marcado
                if ($this.is(':checked')) {
                    $this.prop('disabled', true);
                }
            } else if (!esCampoCritico || $this.val()) {
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
                $this.attr('title', 'Campo bloqueado - Datos registrados en admisión');
            }
        });
    });



    bloquearSecciones.forEach(selector => {
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
            $this.attr('title', 'Campo bloqueado - Registrado por enfermería');
        });
    });

    // Bloquear radios buttons específicos con estilos
    $('input[type="radio"][name="adm_historia_clinica_estab"], \
      input[type="radio"][name="pac_edad_unidad"], \
      input[type="radio"][name="acc_custodia_policial"], \
      input[type="radio"][name="acc_notificacion_custodia"], \
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

    // Bloquear checkboxes específicos con estilos
    $('input[type="checkbox"][name="acc_sugestivo_alcohol"], \
        input[type="checkbox"][name="tipos_evento[]"]').each(function() {
        $(this).prop('disabled', true);
        $(this).get(0).style.setProperty('accent-color', '#2563eb', 'important'); // Azul vibrante con !important

        // Estilizar el label del checkbox
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