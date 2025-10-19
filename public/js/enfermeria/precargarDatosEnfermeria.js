$(document).ready(function() {
    
    // VERIFICAR QUE ESTAMOS EN CONTEXTO DE ENFERMERÍA
    if (typeof window.contextoEnfermeria === 'undefined' || !window.contextoEnfermeria) {
        return;
    }
    
    // VERIFICAR QUE NO ESTAMOS EN CONTEXTO MÉDICO
    if (typeof window.contextoMedico !== 'undefined' && window.contextoMedico) {
        return;
    }
    
    
    // Verificar si se deben precargar datos automáticamente
    if (typeof window.precargarDatosEnfermeria !== 'undefined' && window.precargarDatosEnfermeria === true) {
        
        
        // Precargar nombre del admisionista original si está disponible
        if (typeof window.admisionistaEnfermeria !== 'undefined' && window.admisionistaEnfermeria) {
            setTimeout(() => {
                $('#adm_admisionista_nombre').val(window.admisionistaEnfermeria);
            }, 200);
        }
        
        // Precargar datos del paciente si están disponibles
        if (typeof window.datosPacienteEnfermeria !== 'undefined' && window.datosPacienteEnfermeria) {
            setTimeout(() => {
                precargarDatosPacienteEnfermeria(window.datosPacienteEnfermeria);
            }, 300);
        }

        // Precargar datos de atención si están disponibles
        if (typeof window.datosAtencionEnfermeria !== 'undefined' && window.datosAtencionEnfermeria) {
            setTimeout(() => {
                precargarDatosAtencionEnfermeria(window.datosAtencionEnfermeria);
            }, 500);
        }

        // Bloquear campos de las secciones A y B
        setTimeout(() => {
            bloquearCamposSeccionesAB();
        }, 700);
    }
});

// Función para precargar datos del paciente en Sección A y B - ESPECÍFICA PARA ENFERMERÍA
function precargarDatosPacienteEnfermeria(datos) {
    
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

// Función para precargar datos de atención - ESPECÍFICA PARA ENFERMERÍA
function precargarDatosAtencionEnfermeria(datos) {
    
    if (datos.lleg_codigo) $('#forma_llegada').val(datos.lleg_codigo);
    if (datos.ate_fuente_informacion) $('#fuente_informacion').val(datos.ate_fuente_informacion);
    if (datos.ate_ins_entrega_paciente) $('#entrega_paciente_nombre_inst').val(datos.ate_ins_entrega_paciente);
    if (datos.ate_telefono) $('#entrega_paciente_telefono').val(datos.ate_telefono);
}

// Función para bloquear campos SOLO de las secciones A y B usando selectores más específicos
function bloquearCamposSeccionesAB() {
    
    // Bloquear campos de la Sección A (basado en el contenido de seccion_a.php)
    // Campos del establecimiento - están en la primera tabla
    $('input[name="estab_institucion"], input[name="estab_unicode"], input[name="estab_nombre"], input[name="estab_historia_clinica"], input[name="estab_archivo"]').each(function() {
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
        'input[name="entrega_paciente_telefono"]'
    ];

    // Aplicar bloqueo solo a estos campos específicos
    camposSeccionB.forEach(selector => {
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
            $this.attr('title', 'Campo bloqueado - Registrado por admision');
        });
    });
    
    $('input[type="radio"]').not('[data-seccion="enfermeria"]').each(function() {
        $(this).prop('disabled', true);
    });
}

