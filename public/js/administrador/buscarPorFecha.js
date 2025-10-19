// Configuracion global
const CONFIG = {
    DEBUG: true,
    TIMEOUT: 30000,
    VERSION: 'OPTIMIZADO_v5.0'
};

// === UTILIDADES ===
function log(nivel, mensaje, datos = null) {
    // Logs deshabilitados para producción
    return;
}

// === SISTEMA GLOBAL DE MENSAJES SIMPLIFICADO ===
function mostrarMensaje(tipo, mensaje) {

    // Buscar contenedores específicos del formulario completo
    const contenedor = document.getElementById(tipo + 'Message');
    if (contenedor) {
        const textElement = contenedor.querySelector('span') || contenedor;
        textElement.textContent = mensaje;
        contenedor.classList.remove('hidden');
        setTimeout(() => {
            contenedor.classList.add('hidden');
        }, 8000);
        return;
    }

    // Si no existe contenedor específico, crear alerta global
    mostrarMensajeGlobal(tipo, mensaje);
}

function mostrarMensajeGlobal(tipo, mensaje) {
    // Remover alertas anteriores
    const alertaAnterior = document.getElementById('global-alert');
    if (alertaAnterior) {
        alertaAnterior.remove();
    }

    // Crear nueva alerta global
    const alerta = document.createElement('div');
    alerta.id = 'global-alert';
    alerta.className = `fixed top-4 right-4 z-50 p-4 rounded-lg shadow-lg max-w-sm ${getAlertClassGlobal(tipo)}`;
    alerta.innerHTML = `
        <div class="flex items-center">
            <i class="fas ${getAlertIconGlobal(tipo)} mr-2"></i>
            <span>${mensaje}</span>
            <button onclick="this.parentElement.parentElement.remove()" class="ml-4 text-lg font-bold">&times;</button>
        </div>
    `;

    document.body.appendChild(alerta);

    // Auto-remover después de 8 segundos
    setTimeout(() => {
        if (alerta.parentElement) {
            alerta.remove();
        }
    }, 8000);
}

function getAlertClassGlobal(tipo) {
    switch(tipo) {
        case 'success': return 'bg-green-100 text-green-800 border border-green-300';
        case 'error': return 'bg-red-100 text-red-800 border border-red-300';
        case 'warning': return 'bg-yellow-100 text-yellow-800 border border-yellow-300';
        case 'info': return 'bg-blue-100 text-blue-800 border border-blue-300';
        default: return 'bg-blue-100 text-blue-800 border border-blue-300';
    }
}

function getAlertIconGlobal(tipo) {
    switch(tipo) {
        case 'success': return 'fa-check-circle';
        case 'error': return 'fa-exclamation-circle';
        case 'warning': return 'fa-exclamation-triangle';
        case 'info': return 'fa-info-circle';
        default: return 'fa-info-circle';
    }
}

function limpiarMensajes() {
    // Limpiar contenedores específicos del formulario completo
    ['error', 'info', 'success'].forEach(tipo => {
        const contenedor = document.getElementById(tipo + 'Message');
        if (contenedor) {
            contenedor.classList.add('hidden');
        }
    });

    // Limpiar alerta global
    const alertaGlobal = document.getElementById('global-alert');
    if (alertaGlobal) {
        alertaGlobal.remove();
    }

}


// === FUNCIÓN OPTIMIZADA PARA LIMPIAR FORMULARIO ===
function limpiarFormularioCompleto() {
    log('info', 'Iniciando limpieza completa del formulario');

    // 🔥 CONFIGURACIÓN DE CAMPOS OPTIMIZADA
    const configuracionCampos = {
        // Campos de texto simples
        camposTexto: [
            // Paciente - Sección B
            'pac_apellido1', 'pac_apellido2', 'pac_nombre1', 'pac_nombre2', 'estab_historia_clinica',
            'pac_fecha_nacimiento', 'pac_lugar_nacimiento', 'pac_ocupacion', 'pac_telefono_fijo',
            'pac_telefono_celular', 'res_calle_principal', 'res_provincia', 'res_canton', 'res_parroquia',
            'res_barrio_sector', 'pac_edad_valor', 'contacto_emerg_nombre', 'contacto_emerg_parentesco',
            'contacto_emerg_direccion', 'contacto_emerg_telefono', 'fuente_informacion',
            'entrega_paciente_nombre_inst', 'entrega_paciente_telefono', 'cod-historia', 'adm_fecha',
            'adm_admisionista_nombre', 'estab_archivo','pac_nacionalidad_indigena','pac_pueblo_indigena',
            'res_calle_secundaria','res_referencia',

            // Constantes vitales - Sección G
            'cv_presion_arterial', 'cv_pulso', 'cv_frec_resp', 'cv_pulsioximetria', 'cv_temperatura',
            'cv_peso', 'cv_talla', 'cv_perimetro_cefalico', 'cv_glicemia', 'cv_reaccion_pupilar_der',
            'cv_reaccion_pupilar_izq', 'cv_llenado_capilar', 'cv_glasgow_ocular', 'cv_glasgow_verbal',
            'cv_glasgow_motora','cv_triaje_color',

            // Inicio atención - Sección C
            'inicio_atencion_motivo', 'inicio_atencion_fecha', 'inicio_atencion_hora', 'inicio_atencion_condicion',

            // Accidentes - Sección D
            'acc_fecha_evento', 'acc_hora_evento', 'acc_lugar_evento', 'acc_direccion_evento', 'acc_observaciones',

            // Antecedentes - Sección E
            'ant_descripcion',

            // Problema actual - Sección F
            'ep_descripcion_actual',

            // Examen físico - Sección H
            'ef_descripcion',

            // Examen trauma - Sección I
            'eft_descripcion',

            // Diagnósticos - Secciones L y M
            'exc_observaciones',
            'diag_pres_desc1', 'diag_pres_cie1', 'diag_pres_desc2', 'diag_pres_cie2', 'diag_pres_desc3', 'diag_pres_cie3',
            'diag_def_desc1', 'diag_def_cie1', 'diag_def_desc2', 'diag_def_cie2', 'diag_def_desc3', 'diag_def_cie3',

            // Egreso - Sección O
            'egreso_observacion', 'egreso_dias_reposo',

            // Profesional - Sección P
            'prof_fecha', 'prof_hora', 'prof_primer_nombre', 'prof_primer_apellido', 'prof_segundo_apellido',
            'prof_documento', 'prof_firma', 'prof_sello',

            // Plan tratamiento
            'plan_tratamiento'
        ],

        // Embarazo - Sección J
        camposEmbarazo: [
            'emb_gestas', 'emb_partos', 'emb_cesareas', 'emb_abortos', 'emb_fum', 'emb_tiempo_ruptura',
            'emb_semanas_gestacion', 'emb_afu', 'emb_dilatacion', 'emb_borramiento', 'emb_plano',
            'emb_score_mama', 'emb_observaciones'
        ],

        // Selects principales
        selects: [
            'pac_tipo_documento', 'pac_estado_civil', 'pac_sexo', 'pac_nacionalidad',
            'pac_etnia', 'pac_nivel_educacion', 'pac_estado_educacion', 'pac_tipo_empresa',
            'pac_seguro', 'forma_llegada', 'col_codigo', 'tev_codigo', 'tan_codigo',
            'zef_codigo', 'tipo_id', 'ese_codigo', 'moe_codigo', 'tie_codigo'
        ]
    };

    // 🔥 OPTIMIZACIÓN 1: Limpiar campos de tratamiento con FOR LOOP
    log('info', 'Limpiando campos de tratamiento (1-7)');
    for (let i = 1; i <= 7; i++) {
        const camposTratamiento = ['med', 'via', 'dosis', 'posologia', 'dias'];
        camposTratamiento.forEach(tipo => {
            const campo = document.getElementById(`trat_${tipo}${i}`);
            if (campo) {
                campo.value = '';
            }
        });
    }

    // 🔥 OPTIMIZACIÓN 2: Limpiar campos de texto con forEach
    log('info', 'Limpiando campos de texto principales');
    [...configuracionCampos.camposTexto, ...configuracionCampos.camposEmbarazo].forEach(id => {
        const input = document.getElementById(id);
        if (input) {
            input.value = '';
        }
    });

    // 🔥 OPTIMIZACIÓN 3: Limpiar selects con forEach
    log('info', 'Limpiando selects');
    configuracionCampos.selects.forEach(id => {
        const select = document.getElementById(id);
        if (select) {
            select.value = '';
        }
    });

    // 🔥 OPTIMIZACIÓN 4: Limpiar radios y checkboxes de una vez
    log('info', 'Limpiando radios y checkboxes');
    document.querySelectorAll('input[type="radio"], input[type="checkbox"]').forEach(input => {
        input.checked = false;
    });

    // 🔥 RESETEAR CAMPOS ESPECIALES QUE PUEDEN HABER SIDO DESHABILITADOS
    resetearCamposEspeciales();

    log('info', 'Formulario limpio completado exitosamente');
}

// === FUNCIÓN PARA RESETEAR CAMPOS ESPECIALES ===
function resetearCamposEspeciales() {
    // Resetear constantes vitales
    const camposConstantes = document.querySelectorAll('#seccion-constantes-vitales input, #seccion-constantes-vitales select');
    camposConstantes.forEach(campo => {
        campo.disabled = false;
        campo.classList.remove('bg-gray-100', 'cursor-not-allowed');
    });

    // Resetear embarazo
    const camposEmbarazo = document.querySelectorAll('#seccion-embarazo input, #seccion-embarazo select, #seccion-embarazo textarea');
    camposEmbarazo.forEach(campo => {
        campo.disabled = false;
        campo.classList.remove('bg-gray-100', 'cursor-not-allowed');
    });

    // Resetear exámenes complementarios
    const camposExamenes = document.querySelectorAll('#seccion-examenes input, #seccion-examenes textarea');
    camposExamenes.forEach(campo => {
        campo.disabled = false;
        campo.classList.remove('bg-gray-100', 'cursor-not-allowed');
    });

    // 🔥 NUEVO: Resetear campos de firma y sello del profesional
    resetearCamposFirmaSello();
}

// === FUNCIÓN ESPECÍFICA PARA LIMPIAR FIRMA Y SELLO ===
function resetearCamposFirmaSello() {
    const camposImagenes = ['prof_firma', 'prof_sello'];

    camposImagenes.forEach(campoId => {
        // Buscar contenedor existente
        const contenedor = document.querySelector(`input[name="${campoId}"]`)?.closest('.file-upload-container') ||
            document.querySelector(`#${campoId}`)?.parentElement;

        if (!contenedor) return;

        // Remover contenedores de imagen si existen
        const imagenContainer = contenedor.querySelector('.imagen-profesional-container');
        if (imagenContainer) {
            imagenContainer.remove();
        }

        // Buscar o crear el input original
        let inputOriginal = contenedor.querySelector(`input[name="${campoId}"]`);

        if (!inputOriginal || inputOriginal.type !== 'file') {
            // Si no existe o no es tipo file, recrearlo
            if (inputOriginal) {
                inputOriginal.remove();
            }

            inputOriginal = document.createElement('input');
            inputOriginal.type = 'file';
            inputOriginal.id = campoId;
            inputOriginal.name = campoId;
            inputOriginal.className = 'form-input';
            inputOriginal.accept = 'image/*';

            contenedor.insertBefore(inputOriginal, contenedor.firstChild);
        }

        // Limpiar el valor
        inputOriginal.value = '';
        inputOriginal.style.display = '';
        inputOriginal.readOnly = false;
        inputOriginal.classList.remove('bg-gray-100', 'text-gray-600');

        // Mostrar elementos relacionados
        const label = contenedor.querySelector('label');
        if (label) {
            label.style.display = '';
            // Actualizar texto del label si es necesario
            const titulo = campoId === 'prof_firma' ? 'Firma del Profesional' : 'Sello del Profesional';
            if (!label.textContent.includes(titulo)) {
                label.textContent = titulo;
            }
        }

        const preview = contenedor.querySelector('.image-preview');
        if (preview) {
            preview.style.display = '';
            preview.innerHTML = ''; // Limpiar preview
        }

        const small = contenedor.querySelector('small');
        if (small) {
            small.style.display = '';
        }

        const botonSubir = contenedor.querySelector('button[type="button"]');
        if (botonSubir) {
            botonSubir.style.display = '';
        }

        log('debug', `Campo ${campoId} reseteado a estado original`);
    });
}

// === FUNCIÓN MEJORADA PARA GESTIONAR MODO LECTURA ===
function gestionarModoLectura(activar, tipoResultado = null) {
    log('info', `${activar ? '🔒 Activando' : '🔓 Desactivando'} modo lectura. Tipo: ${tipoResultado}`);

    if (activar) {
        // Activar modo lectura
        bloquearCamposFormulario(true);
        mostrarIndicadorSoloLectura(true);

        // Habilitar botón PDF solo si se encontraron datos
        if (tipoResultado === 'datos_encontrados') {
            habilitarBotonPDF008();
            log('info', '📄 Botón PDF habilitado - datos encontrados');
        }

    } else {
        // Desactivar modo lectura
        bloquearCamposFormulario(false);
        mostrarIndicadorSoloLectura(false);

        // Deshabilitar botón PDF
        deshabilitarBotonPDF008();

        log('info', '🔓 Modo edición activado - formulario desbloqueado');
    }
}

// === FUNCIÓN PRINCIPAL DE BÚSQUEDA MEJORADA ===
async function buscarPorFecha(fecha, identificador) {
    log('info', `🔍 Iniciando búsqueda: Fecha=${fecha}, Identificador=${identificador}`);

    if (!fecha) {
        return;
    }

    if (!identificador) {
        return;
    }

    // 🔥 PASO 1: PREPARAR INTERFAZ PARA BÚSQUEDA
    limpiarMensajes();

    // Iniciar sistema de alertas para búsqueda por fecha
    if (typeof iniciarBusquedaFecha === 'function') {
        iniciarBusquedaFecha();
    }

    // Desactivar modo lectura antes de buscar
    gestionarModoLectura(false);

    try {
        const formData = new URLSearchParams();
        formData.append('fecha', fecha);
        formData.append('identificador', identificador);

        log('debug', `URL de búsqueda: ${window.APP_URLS?.buscarPorFecha}`);

        const response = await fetch(window.APP_URLS.buscarPorFecha, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: formData.toString()
        });

        log('info', `🔥 Respuesta recibida: ${response.status} ${response.statusText}`);

        if (!response.ok) {
            throw new Error(`HTTP ${response.status}: ${response.statusText}`);
        }

        const json = await response.json();
        log('info', '📊 Datos JSON recibidos:', json);


        // 🔥 PASO 2: PROCESAR RESULTADOS SEGÚN EL CASO
        if (json.success && json.data) {

            llenarFormularioCompleto(json.data);
            gestionarModoLectura(true, 'datos_encontrados');

            // Notificar que el formulario 008 se cargó exitosamente
            if (typeof completarCargaFormulario008 === 'function') {
                completarCargaFormulario008(true, true);
            }

        } else {
            // ❌ CASO 2: NO SE ENCONTRARON DATOS
            log('info', 'ℹ️ No se encontraron datos - limpiando formulario y volviendo a modo normal');

            limpiarFormularioCompleto();
            gestionarModoLectura(false);

            // Notificar que el formulario 008 se procesó pero sin datos
            if (typeof completarCargaFormulario008 === 'function') {
                completarCargaFormulario008(true, false);
            }
        }

    } catch (error) {
        // 💥 CASO 3: ERROR EN LA BÚSQUEDA
        log('error', '💥 Error en búsqueda por fecha:', error);

        // En caso de error, limpiar y volver a modo normal
        limpiarFormularioCompleto();
        gestionarModoLectura(false);

        // Notificar error en el formulario 008
        if (typeof completarCargaFormulario008 === 'function') {
            completarCargaFormulario008(false, false);
        }
    }
}

// === FUNCIÓN OPTIMIZADA PARA BLOQUEAR/DESBLOQUEAR FORMULARIO ===
function bloquearCamposFormulario(bloquear = true) {
    log('info', `${bloquear ? '🔒 Bloqueando' : '🔓 Desbloqueando'} todos los campos del formulario`);

    // Selectores para todos los elementos de entrada
    const selectores = [
        'input[type="text"]',
        'input[type="tel"]',
        'input[type="number"]',
        'input[type="date"]',
        'input[type="time"]',
        'input[type="datetime-local"]',
        'input[type="url"]',
        'input[type="radio"]',
        'input[type="checkbox"]',
        'select',
        'textarea'
    ];

    // Campos que NUNCA deben bloquearse (controles de búsqueda y navegación)
    const excepciones = [
        'filtro-fecha',
        'identificador_paciente',
        'btn-consultar-fecha',
        'btn-recargar',
        'btn-generar-pdf-008',
        'btn-generar-pdf-005',
        'btn-guardar',
        'btn-nuevo',
        'btn-editar',
        'btn-logout'
    ];

    // Campos que SIEMPRE deben permanecer bloqueados (datos del establecimiento)
    const camposSiempreBloqueados = [
        'estab_institucion',
        'estab_unicode',
        'estab_nombre',
        'estab_archivo'
    ];

    let contador = 0;

    // 🔥 OPTIMIZACIÓN: Procesar todos los selectores de una vez
    const todosLosElementos = document.querySelectorAll(selectores.join(', '));

    todosLosElementos.forEach(elemento => {
        // Verificar si el elemento debe ser excluido
        const esExcepcion = excepciones.some(excepcion => {
            return elemento.id === excepcion ||
                elemento.name === excepcion ||
                elemento.classList.contains(excepcion) ||
                elemento.closest(`#${excepcion}`) !== null;
        });

        if (!esExcepcion) {
            // Verificar si es un campo que siempre debe estar bloqueado
            const esCampoSiempreBloqueado = camposSiempreBloqueados.includes(elemento.id) ||
                camposSiempreBloqueados.includes(elemento.name);

            if (bloquear) {
                // Bloquear elemento
                elemento.disabled = true;
                elemento.setAttribute('readonly', 'readonly');
                elemento.classList.add('bg-gray-100', 'cursor-not-allowed', 'text-gray-600');
                elemento.setAttribute('data-was-blocked', 'true');
            } else {
                // Desbloquear solo si fue bloqueado por esta función Y no es campo siempre bloqueado
                if (elemento.hasAttribute('data-was-blocked') && !esCampoSiempreBloqueado) {
                    elemento.disabled = false;
                    elemento.removeAttribute('readonly');
                    elemento.removeAttribute('data-was-blocked');
                    elemento.classList.remove('bg-gray-100', 'cursor-not-allowed', 'text-gray-600');
                }
                // Los campos siempre bloqueados mantienen su estado
            }
            contador++;
        }
    });

    // Manejar botones del formulario
    const botonesFormulario = document.querySelectorAll('button[type="submit"], button[type="button"]:not(.excluir-bloqueo)');
    botonesFormulario.forEach(boton => {
        const esExcepcion = excepciones.some(excepcion =>
            boton.id === excepcion ||
            boton.classList.contains(excepcion) ||
            boton.classList.contains('excluir-bloqueo')
        );

        if (!esExcepcion) {
            if (bloquear) {
                boton.disabled = true;
                boton.classList.add('opacity-50', 'cursor-not-allowed');
                boton.setAttribute('data-was-blocked', 'true');
            } else if (boton.hasAttribute('data-was-blocked')) {
                boton.disabled = false;
                boton.classList.remove('opacity-50', 'cursor-not-allowed');
                boton.removeAttribute('data-was-blocked');
            }
        }
    });

    log('info', `${bloquear ? '🔒' : '🔓'} ${contador} campos ${bloquear ? 'bloqueados' : 'desbloqueados'}`);
    return contador;
}

// === FUNCIÓN MEJORADA PARA MOSTRAR INDICADOR VISUAL ===
function mostrarIndicadorSoloLectura(mostrar = true) {
    let indicador = document.getElementById('readonly-indicator');

    if (mostrar && !indicador) {
        // Crear indicador si no existe
        indicador = document.createElement('div');
        indicador.id = 'readonly-indicator';
        indicador.className = 'fixed top-4 right-4 bg-yellow-500 text-white px-4 py-2 rounded-lg shadow-lg z-50 transition-all duration-300';
        indicador.innerHTML = `
            <div class="flex items-center space-x-2">
                <svg class="w-5 h-5 animate-pulse" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                          d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z">
                    </path>
                </svg>
                <span class="font-semibold">📖 Modo Solo Lectura</span>
            </div>
        `;
        document.body.appendChild(indicador);

        // Animación de entrada
        setTimeout(() => {
            indicador.style.transform = 'translateX(0)';
        }, 100);

    } else if (!mostrar && indicador) {
        // Remover indicador
        indicador.style.transition = 'all 0.3s ease-out';
        indicador.style.transform = 'translateX(100%)';
        indicador.style.opacity = '0';

        setTimeout(() => {
            if (indicador.parentNode) {
                indicador.parentNode.removeChild(indicador);
            }
        }, 300);
    }
}

// === FUNCIONES DE PROCESAMIENTO DE DATOS (sin cambios) ===
function dividirDatos(apellidos, nombres) {
    const apellidosTokens = (apellidos || '').trim().split(/\s+/);
    const nombresTokens = (nombres || '').trim().split(/\s+/);

    let apellido1 = '', apellido2 = '', nombre1 = '', nombre2 = '';

    if (apellidosTokens.length >= 2) {
        apellido2 = apellidosTokens[apellidosTokens.length - 1];
        apellido1 = apellidosTokens.slice(0, -1).join(' ');
    } else if (apellidosTokens.length === 1) {
        apellido1 = apellidosTokens[0];
    }

    if (nombresTokens.length >= 2) {
        nombre1 = nombresTokens[0];
        nombre2 = nombresTokens.slice(1).join(' ');
    } else if (nombresTokens.length === 1) {
        nombre1 = nombresTokens[0];
    }

    return { apellido1, apellido2, nombre1, nombre2 };
}

function convertirFechaParaInput(fecha) {
    if (!fecha) return '';

    try {
        // Convertir a string por seguridad
        fecha = String(fecha).trim();

        // Si ya está en formato yyyy-MM-dd, retornar directamente
        if (/^\d{4}-\d{2}-\d{2}$/.test(fecha)) {
            return fecha;
        }

        // Si es formato yyyy-MM-dd HH:mm:ss (datetime de MySQL), extraer solo la fecha
        if (/^\d{4}-\d{2}-\d{2}\s\d{2}:\d{2}:\d{2}$/.test(fecha)) {
            return fecha.split(' ')[0];
        }

        // Si es formato dd/MM/yyyy
        if (/^\d{1,2}\/\d{1,2}\/\d{4}$/.test(fecha)) {
            const [dia, mes, anio] = fecha.split('/');
            return `${anio}-${mes.padStart(2, '0')}-${dia.padStart(2, '0')}`;
        }

        // Si contiene texto en español (formato: "jueves, 2 de octubre de 2025")
        if (/[a-zA-ZáéíóúñÑ]/.test(fecha)) {
            const numeros = fecha.match(/\d+/g);
            if (numeros && numeros.length >= 2) {
                const dia = numeros[0].padStart(2, '0');

                // Mapear mes español a número
                const mesesES = {
                    'enero': '01', 'febrero': '02', 'marzo': '03', 'abril': '04',
                    'mayo': '05', 'junio': '06', 'julio': '07', 'agosto': '08',
                    'septiembre': '09', 'octubre': '10', 'noviembre': '11', 'diciembre': '12'
                };

                let mes = '01'; // Por defecto enero
                const fechaLower = fecha.toLowerCase();
                for (const [mesNombre, mesNum] of Object.entries(mesesES)) {
                    if (fechaLower.includes(mesNombre)) {
                        mes = mesNum;
                        break;
                    }
                }

                const anio = numeros[numeros.length - 1]; // Último número es el año
                return `${anio}-${mes}-${dia}`;
            }
        }

        // Último intento: usar Date si viene en otro formato válido
        const date = new Date(fecha);
        if (!isNaN(date.getTime()) && date.getFullYear() > 1900) {
            const year = date.getFullYear();
            const month = String(date.getMonth() + 1).padStart(2, '0');
            const day = String(date.getDate()).padStart(2, '0');
            return `${year}-${month}-${day}`;
        }

        return '';
    } catch (error) {
        log('error', 'Error formateando fecha:', error);
        return '';
    }
}

// === MAPEOS DE DATOS (sin cambios) ===
const MAPEOS = {
    tipo_documento: {
        'CC/CI': '1', 'PAS': '2', 'CARNÉ': '3', 'CARNET': '3', 'S/D': '4'
    },
    estado_civil: {
        'SOLTERO(A)': '1', 'SOLTERO': '1', 'SOLTERA': '1',
        'CASADO(A)': '2', 'CASADO': '2', 'CASADA': '2',
        'VIUDO(A)': '3', 'VIUDO': '3', 'VIUDA': '3',
        'DIVORCIADO(A)': '4', 'DIVORCIADO': '4', 'DIVORCIADA': '4',
        'UNIÓN LIBRE': '5', 'UNION LIBRE': '5',
        'A ESPECIFICAR': '6', 'UNIÓN DE HECHO': '7', 'UNION DE HECHO': '7', 'NO APLICA': '8'
    },
    genero: {
        'MASCULINO': '1', 'FEMENINO': '2', 'OTRO': '3', 'A ESPECIFICAR': '4'
    },
    nacionalidad: {
        'ECUATORIANA': '1', 'PERUANA': '2', 'CUBANA': '3', 'COLOMBIANA': '4', 'OTRA': '5', 'A ESPECIFICAR': '6'
    },
    etnia: {
        'INDIGENA': '1', 'AFREOECUATORIANA': '2', 'AFROECUATORIANA': '2',
        'MESTIZO': '3', 'MONTUBIO': '4', 'BLANCO': '5', 'OTROS': '6', 'A ESPECIFICAR': '7'
    },
    pueblo_indigena: {
        'HUANCAVILCA': '1', 'MANTA': '2', 'KARANKI': '3', 'OTAVALO': '4',
        'NATABUELA': '5', 'KAYAMBI': '6', 'KITU KARA': '7', 'PANZALEO': '8',
        'CHIBULEO': '9', 'KISAPINCHA': '10', 'SALASAKA': '11', 'WARANKA': '12',
        'PURUWÁ': '13', 'KAÑARI': '14', 'PALTA': '15', 'SARAGURO': '16',
        'COFÁN': '17', 'SIONA - SECOYA': '18', 'OTROS': '19'
    },
    nacionalidad_indigena: {
        'ÉPERA': '1', 'CHACHI': '2', 'AWÁ': '3', 'TSÁCHILA': '4', 'KICHWA': '5',
        'SHUAR': '6', 'COFÁN': '7', 'SIONA': '8', 'SECOYA': '9', 'WAORANI': '10',
        'ZÁPARA': '11', 'ANDOA': '12', 'SHIWIAR': '13', 'ACHUAR': '14', 'OTRAS': '15'
    },
    nivel_educacion: {
        'EDUCACIÓN INICIAL': '1', 'EDUCACION INICIAL': '1',
        'EGB': '2', 'BACHILLERATO': '3',
        'EDUCACIÓN SUPERIOR': '4', 'EDUCACION SUPERIOR': '4'
    },
    estado_educacion: {
        'INCOMPLETA': '1', 'CURSANDO': '2', 'COMPLETA': '3'
    },
    empresa: {
        'PÚBLICA': '1', 'PUBLICA': '1', 'PRIVADA': '2', 'NO TRABAJA': '3', 'A ESPECIFICAR': '4'
    },
    seguro: {
        'IESS': '1', 'ISSPOL': '2', 'ISSFA': '3', 'PRIVADO': '4', 'A ESPECIFICAR': '5'
    },
    forma_llegada: {
        'AMBULATORIO': '1', 'AMBULANCIA': '2', 'OTRO TRANSPORTE': '3'
    }
};

// === HELPERS PARA CHECKBOX Y RADIOS ===
function normalizarSiNo(v) {
    if (v == null || v === '') return '';
    const s = String(v).trim().toLowerCase();
    if (['si', 'sí', 's', '1', 'true', 't', 'yes'].includes(s)) return 'si';
    if (['no', 'n', '0', 'false', 'f'].includes(s)) return 'no';
    return '';
}

function setRadioSiNo(baseName, valor) {
    const v = normalizarSiNo(valor);
    if (!v) return;

    const radioSi = document.getElementById(`${baseName}_si`);
    const radioNo = document.getElementById(`${baseName}_no`);

    if (radioSi && radioNo) {
        radioSi.checked = (v === 'si');
        radioNo.checked = (v === 'no');
        log('debug', `Radio ${baseName}: ${v}`);
    }
}

function marcarCheckboxPorCodigo(codigo, categoriaName, categoriaLabel = '') {
    if (!codigo) {
        log('debug', `🔍 No hay código para marcar en ${categoriaLabel || categoriaName}`);
        return false;
    }

    let marcado = false;
    const codigoStr = String(codigo);

    // Estrategia 1: Buscar por name y value exacto
    const checkboxByNameValue = document.querySelector(`input[name="${categoriaName}"][value="${codigoStr}"]`);
    if (checkboxByNameValue) {
        checkboxByNameValue.checked = true;
        marcado = true;
        log('debug', `✅ Marcado por name/value: ${categoriaName}[${codigoStr}]`);
    }

    // Estrategia 2: Buscar por name con array notation y value exacto
    if (!marcado) {
        const checkboxByArrayName = document.querySelector(`input[name="${categoriaName}[]"][value="${codigoStr}"]`);
        if (checkboxByArrayName) {
            checkboxByArrayName.checked = true;
            marcado = true;
            log('debug', `✅ Marcado por name[] y value: ${categoriaName}[][${codigoStr}]`);
        }
    }

    if (!marcado) {
        log('warn', `⚠️ NO SE PUDO MARCAR: ${categoriaLabel || categoriaName} código "${codigoStr}"`);
    }

    return marcado;
}

function marcarMultiplesCheckboxes(codigos, categoriaName, categoriaLabel = '') {
    if (!codigos) {
        log('debug', `🔍 No hay códigos para ${categoriaLabel || categoriaName}`);
        return;
    }

    // Convertir a array si no lo es
    let codigosArray = [];
    if (Array.isArray(codigos)) {
        codigosArray = codigos;
    } else if (typeof codigos === 'string' && codigos.includes(',')) {
        codigosArray = codigos.split(',').map(c => c.trim());
    } else {
        codigosArray = [String(codigos)];
    }

    log('debug', `🔍 Marcando ${codigosArray.length} códigos para ${categoriaLabel || categoriaName}:`, codigosArray);

    let marcados = 0;
    codigosArray.forEach(codigo => {
        if (marcarCheckboxPorCodigo(codigo, categoriaName, categoriaLabel)) {
            marcados++;
        }
    });

    log('info', `📊 ${categoriaLabel || categoriaName}: ${marcados}/${codigosArray.length} checkboxes marcados`);
}

// === FUNCIÓN PRINCIPAL MEJORADA ===
function llenarFormularioCompleto(d) {
    log('info', 'Llenando formulario completo con datos:', d);

    try {
        // Llenar todas las secciones
        llenarSeccionA(d);
        llenarSeccionB(d);
        llenarSeccionC(d);
        llenarSeccionD(d);
        llenarSeccionE(d);
        llenarSeccionF(d);
        llenarSeccionG(d);
        llenarSeccionH(d);
        llenarSeccionI(d);
        llenarSeccionJ(d);
        llenarSeccionK(d);
        llenarSeccionL(d);
        llenarSeccionM(d);
        llenarSeccionN(d);
        llenarSeccionO(d);
        llenarSeccionP(d);

        log('info', '✅ Formulario completo llenado exitosamente');

    } catch (error) {
        log('error', 'Error llenando formulario:', error);
    }
}

// === SECCIÓN N OPTIMIZADA CON FOR LOOPS ===
function llenarSeccionN(d = {}) {
    log('info', '💊 Cargando Sección N - Plan de Tratamiento');

    // 🔥 FUNCIÓN OPTIMIZADA PARA LLENAR UN CAMPO
    const llenarCampo = (id, valor) => {
        const elemento = document.getElementById(id);
        if (elemento) {
            elemento.value = valor || '';
            return true;
        } else {
            log('warn', `⚠️ Campo no encontrado: ${id}`);
            return false;
        }
    };

    // 🔥 OPTIMIZACIÓN: Limpiar todos los campos de tratamiento con FOR LOOP
    log('info', 'Limpiando campos de tratamiento (1-7)');
    for (let i = 1; i <= 7; i++) {
        const tiposCampo = ['med', 'via', 'dosis', 'posologia', 'dias'];
        tiposCampo.forEach(tipo => {
            llenarCampo(`trat_${tipo}${i}`, '');
        });
    }

    let tratamientosCargados = 0;

    // 🔥 ESTRATEGIA 1: Usar campos enumerados directamente (MÁS CONFIABLE)
    for (let i = 1; i <= 7; i++) {
        const datosFilaTratamiento = {
            med: d[`trat_med${i}`],
            via: d[`trat_via${i}`],
            dosis: d[`trat_dosis${i}`],
            posologia: d[`trat_posologia${i}`],
            dias: d[`trat_dias${i}`]
        };

        // Si hay AL MENOS un campo con datos, llenar la fila
        const tieneDatos = Object.values(datosFilaTratamiento).some(valor => valor && valor.toString().trim() !== '');

        if (tieneDatos) {
            Object.entries(datosFilaTratamiento).forEach(([tipo, valor]) => {
                llenarCampo(`trat_${tipo}${i}`, valor);
            });
            tratamientosCargados++;
            log('debug', `💊 Tratamiento ${i} cargado:`, datosFilaTratamiento);
        }
    }

    // 🔥 ESTRATEGIA 2: Si no hay campos enumerados, usar array
    if (tratamientosCargados === 0 && Array.isArray(d.tratamientos)) {
        log('info', 'Usando array de tratamientos como fallback');

        d.tratamientos.slice(0, 7).forEach((trat, index) => {
            const fila = index + 1;
            const datosArray = {
                med: trat.trat_medicamento || trat.medicamento || '',
                via: trat.trat_via || trat.via || '',
                dosis: trat.trat_dosis || trat.dosis || '',
                posologia: trat.trat_posologia || trat.posologia || '',
                dias: trat.trat_dias || trat.dias || ''
            };

            const tieneDatos = Object.values(datosArray).some(valor => valor && valor.toString().trim() !== '');

            if (tieneDatos) {
                Object.entries(datosArray).forEach(([tipo, valor]) => {
                    llenarCampo(`trat_${tipo}${fila}`, valor);
                });
                tratamientosCargados++;
            }
        });
    }

    // 🔥 ESTRATEGIA 3: Si no hay nada, usar campos legacy
    if (tratamientosCargados === 0) {
        const datosLegacy = {
            med: d.trat_medicamento || '',
            via: d.trat_via || '',
            dosis: d.trat_dosis || '',
            posologia: d.trat_posologia || '',
            dias: d.trat_dias || ''
        };

        const tieneDatos = Object.values(datosLegacy).some(valor => valor && valor.toString().trim() !== '');

        if (tieneDatos) {
            Object.entries(datosLegacy).forEach(([tipo, valor]) => {
                llenarCampo(`trat_${tipo}1`, valor);
            });
            tratamientosCargados = 1;
            log('debug', '💊 Usando datos legacy de tratamiento');
        }
    }

    // Plan de tratamiento / observaciones
    const observaciones = d.plan_tratamiento || d.trat_observaciones || '';
    const campoObs = document.getElementById('plan_tratamiento') ||
        document.getElementById('trat_observaciones');

    if (campoObs && observaciones) {
        campoObs.value = observaciones;
        log('debug', `💊 Observaciones: ${observaciones}`);
    }

    log('info', `💊 Sección N completada - ${tratamientosCargados} tratamientos cargados`);

    // 🔥 VERIFICACIÓN INMEDIATA OPTIMIZADA
    setTimeout(() => {
        let tratamientosVerificados = 0;
        for (let i = 1; i <= 7; i++) {
            const med = document.getElementById(`trat_med${i}`)?.value || '';
            if (med.trim() !== '') {
                tratamientosVerificados++;
            }
        }
        log('debug', `💊 Verificación: ${tratamientosVerificados} tratamientos verificados en DOM`);
    }, 100);
}

// === FUNCIONES DE SECCIONES (mantener las existentes pero añadir las que faltan) ===
function llenarSeccionA(d) {
    log('info', '📋 Cargando Sección A - Datos del Establecimiento');

    const fechaFormateada = convertirFechaParaInput(d.ate_fecha);

    const camposSeccionA = [
        ['cod-historia', d.pac_his_cli],
        ['estab_archivo', d.est_num_archivo],
        ['estab_historia_clinica', d.pac_cedula],
        ['adm_fecha', fechaFormateada || new Date().toISOString().split('T')[0]],
        ['adm_admisionista_nombre', d.usuario_nombre_completo]
    ];

    camposSeccionA.forEach(([id, valor]) => {
        const elemento = document.getElementById(id);
        if (elemento && valor) {
            elemento.value = valor;
            log('debug', `📋 ${id}: ${valor}`);
        }
    });

    // Radio buttons de historia clínica
    if (d.pac_his_cli && d.pac_his_cli.trim() !== '') {
        const histSi = document.getElementById('adm_historia_clinica_estab_si');
        if (histSi) histSi.checked = true;
    }
}

function llenarSeccionB(d) {
    log('info', '👤 Cargando Sección B - Datos del Paciente');

    const datos = dividirDatos(d.pac_apellidos, d.pac_nombres);

    // Nombres y apellidos
    const camposNombre = [
        ['pac_apellido1', datos.apellido1],
        ['pac_apellido2', datos.apellido2],
        ['pac_nombre1', datos.nombre1],
        ['pac_nombre2', datos.nombre2]
    ];

    camposNombre.forEach(([id, valor]) => {
        const input = document.getElementById(id);
        if (input) {
            input.value = valor || '';
            log('debug', `👤 ${id}: ${valor || ''}`);
        }
    });

    // Mapear selects
    const selectMappings = [
        ['pac_tipo_documento', 'tipo_documento', d.tipo_documento],
        ['pac_estado_civil', 'estado_civil', d.estado_civil],
        ['pac_sexo', 'genero', d.genero],
        ['pac_nacionalidad', 'nacionalidad', d.nacionalidad],
        ['pac_etnia', 'etnia', d.grupo_cultural],
        ['pac_nacionalidad_indigena', 'nacionalidad_indigena', d.nacionalidad_indigena],
        ['pac_pueblo_indigena', 'pueblo_indigena', d.pueblo_indigena],
        ['pac_nivel_educacion', 'nivel_educacion', d.nivel_educacion],
        ['pac_estado_educacion', 'estado_educacion', d.estado_nivel_educ],
        ['pac_tipo_empresa', 'empresa', d.empresa],
        ['pac_seguro', 'seguro', d.seguro],
        ['forma_llegada', 'forma_llegada', d.forma_llegada]
    ];
    
    // Radio button para grupo prioritario
    if (d.pac_grupo_prioritario == 1 || d.pac_grupo_prioritario === true || d.pac_grupo_prioritario === 'si') {
        const radioSi = document.getElementById('pac_grupo_prioritario_si');
        if (radioSi) radioSi.checked = true;
        log('debug', '👤 Grupo prioritario: SÍ');
    } else {
        const radioNo = document.getElementById('pac_grupo_prioritario_no');
        if (radioNo) radioNo.checked = true;
        log('debug', '👤 Grupo prioritario: NO');
    }

    // Radio button para unidad de edad (H, D, M, A)
    if (d.pac_edad_unidad) {
        const unidad = d.pac_edad_unidad.toString().toUpperCase();
        const radioH = document.getElementById('pac_edad_unidad_h');
        const radioD = document.getElementById('pac_edad_unidad_d');
        const radioM = document.getElementById('pac_edad_unidad_m');
        const radioA = document.getElementById('pac_edad_unidad_a');

        if (unidad === 'H' && radioH) {
            radioH.checked = true;
            log('debug', '👤 Edad unidad: H (Horas)');
        } else if (unidad === 'D' && radioD) {
            radioD.checked = true;
            log('debug', '👤 Edad unidad: D (Días)');
        } else if (unidad === 'M' && radioM) {
            radioM.checked = true;
            log('debug', '👤 Edad unidad: M (Meses)');
        } else if (unidad === 'A' && radioA) {
            radioA.checked = true;
            log('debug', '👤 Edad unidad: A (Años)');
        }
    }
    
    selectMappings.forEach(([elementId, mapKey, value]) => {
        const select = document.getElementById(elementId);
        if (select && value) {
            const valorLimpio = value.trim().toUpperCase();
            const mappedValue = MAPEOS[mapKey] && MAPEOS[mapKey][valorLimpio] || '';
            select.value = mappedValue;
            log('debug', `👤 Select ${elementId}: ${value} -> ${mappedValue}`);
        }
    });

    // Campos de texto del paciente
    const camposTextoPaciente = [
        ['pac_telefono_fijo', d.pac_telefono_fijo],
        ['pac_telefono_celular', d.pac_telefono_celular],
        ['pac_fecha_nacimiento', convertirFechaParaInput(d.pac_fecha_nac)],
        ['pac_lugar_nacimiento', d.pac_lugar_nac],
        ['pac_grupo_prioritario', d.pac_grupo_prioritario],
        ['pac_grupo_prioritario_especifique', d.pac_grupo_sanguineo],
        ['pac_edad_valor', d.pac_edad_valor],
        ['pac_ocupacion', d.pac_ocupacion],
        ['res_calle_principal', d.pac_direccion],
        ['res_calle_secundaria', d.pac_calle_secundaria],
        ['res_referencia', d.pac_referencia],
        ['res_provincia', d.pac_provincias],
        ['res_canton', d.pac_cantones],
        ['res_parroquia', d.pac_parroquias],
        ['res_barrio_sector', d.pac_barrio],
        ['contacto_emerg_nombre', d.pac_avisar_a],
        ['contacto_emerg_parentesco', d.pac_parentezco_avisar_a],
        ['contacto_emerg_direccion', d.pac_direccion_avisar],
        ['contacto_emerg_telefono', d.pac_telefono_avisar_a],
        ['fuente_informacion', d.ate_fuente_informacion],
        ['entrega_paciente_nombre_inst', d.ate_ins_entrega_paciente],
        ['entrega_paciente_telefono', d.ate_telefono]
    ];

    camposTextoPaciente.forEach(([elementId, value]) => {
        const input = document.getElementById(elementId);
        if (input && value) {
            input.value = value;
            log('debug', `👤 ${elementId}: ${value}`);
        }
    });

    
}


function llenarSeccionC(d) {
    log('info', '📋 Cargando Sección C - Inicio de Atención');

    // Inputs simples
    const camposInicioAtencion = [
        ['inicio_atencion_fecha', convertirFechaParaInput(d.iat_fecha)],
        ['inicio_atencion_hora', d.iat_hora],
        ['inicio_atencion_motivo', d.iat_motivo]
    ];

    camposInicioAtencion.forEach(([id, val]) => {
        const el = document.getElementById(id);
        if (el && val != null && val !== '') {
            el.value = val;
            log('debug', `📋 ${id}: ${val}`);
        }
    });

    // Select: condición de llegada
    const select = document.getElementById('inicio_atencion_condicion');
    const incoming = d?.condicion_llegada;

    if (select && incoming != null && incoming !== '') {
        const incomingStr = String(incoming).trim();

        // Intento por value
        const hasValue = Array.from(select.options).some(o => o.value === incomingStr);
        if (hasValue) {
            select.value = incomingStr;
        } else {
            // Fallback por texto (case-insensitive)
            const match = Array.from(select.options).find(o =>
                o.text.trim().toLowerCase() === incomingStr.toLowerCase()
            );
            if (match) select.value = match.value;
        }

        if (select.value) {
            select.dispatchEvent(new Event('change', { bubbles: true }));
            log('debug', `📋 Condición llegada seleccionada: ${select.value}`);
        }
    }
}

function llenarSeccionD(d) {
    log('info', '🚨 Cargando Sección D - Accidentes, Violencias, Intoxicación');

    // Campos de evento
    const camposEvento = [
        ['acc_fecha_evento', convertirFechaParaInput(d.eve_fecha)],
        ['acc_hora_evento', d.eve_hora],
        ['acc_lugar_evento', d.eve_lugar],
        ['acc_direccion_evento', d.eve_direccion],
        ['acc_observaciones', d.eve_observacion]
    ];

    camposEvento.forEach(([id, val]) => {
        const el = document.getElementById(id);
        if (el && val != null && val !== '') {
            el.value = val;
            log('debug', `🚨 ${id}: ${val}`);
        }
    });

    // Radios de policía
    setRadioSiNo('acc_custodia_policial', d.ate_custodia_policial);

    // Notificación
    setRadioSiNo('acc_notificacion_custodia', d.eve_notificacion);

    // Tipos de evento (checkboxes)
    marcarMultiplesCheckboxes(d.tev_codigo, 'tipos_evento[]', 'Tipos de Evento');

    // Sugestivo de alcohol
    const sugAlcohol = document.getElementById('acc_sugestivo_alcohol');
    if (sugAlcohol) {
        const aliento = d.ate_aliento_etilico || d.acc_sugestivo_alcohol;
        sugAlcohol.checked = normalizarSiNo(aliento) === 'si';
        log('debug', `🚨 Sugestivo alcohol: ${aliento} -> ${sugAlcohol.checked}`);
    }
}

function llenarSeccionE(d) {
    log('info', '📝 Cargando Sección E - Antecedentes Patológicos');

    // Verificar "No aplica"
    const noAplicaValue = d.ap_no_aplica || d.ant_no_aplica || d.antecedentes_no_aplica;
    if (noAplicaValue && normalizarSiNo(noAplicaValue) === 'si') {
        const noAplica = document.getElementById('ant_no_aplica');
        if (noAplica) {
            noAplica.checked = true;
            if (typeof toggleAntecedentes === 'function') {
                toggleAntecedentes();
            }
            log('debug', '📝 Antecedentes no aplica marcado');
            return;
        }
    }

    // Antecedentes usando checkboxes
    marcarMultiplesCheckboxes(d.tan_codigo, 'antecedentes[]', 'Antecedentes');

    // Descripción
    const descripcionAnt = document.getElementById('ant_descripcion');
    if (descripcionAnt) {
        const descripcion = d.ap_descripcion || d.ant_descripcion || d.antecedentes_descripcion || '';
        if (descripcion) {
            descripcionAnt.value = descripcion;
            log('debug', `📝 Descripción antecedentes: ${descripcion}`);
        }
    }
}

function llenarSeccionF(d) {
    log('info', '📝 Cargando Sección F - Enfermedad o Problema Actual');

    const descripcionActual = document.getElementById('ep_descripcion_actual');
    if (descripcionActual) {
        const descripcion = d.pro_descripcion || d.ep_descripcion || d.problema_actual || '';
        if (descripcion) {
            descripcionActual.value = descripcion;
            log('debug', `📝 Problema actual: ${descripcion}`);
        }
    }
}

function llenarSeccionG(d) {
    const sinConstantesValue = d.con_sin_constantes;
    const valorNumerico = parseInt(sinConstantesValue);
    const esSinConstantes = valorNumerico === 1;

    if (esSinConstantes) {
        const checkboxSinVitales = document.getElementById('cv_sin_vitales');
        if (checkboxSinVitales) {
            checkboxSinVitales.checked = true;
            
            if (typeof toggleConstantesVitales === 'function') {
                toggleConstantesVitales();
            }
            return;
        }
    }

    // Llenar constantes vitales
    const camposConstantesVitales = [
        ['cv_presion_arterial', d.con_presion_arterial],
        ['cv_pulso', d.con_pulso],
        ['cv_frec_resp', d.con_frec_respiratoria],
        ['cv_pulsioximetria', d.con_pulsioximetria],
        ['cv_perimetro_cefalico', d.con_perimetro_cefalico],
        ['cv_peso', d.con_peso],
        ['cv_talla', d.con_talla],
        ['cv_glicemia', d.con_glucemia_capilar],
        ['cv_glasgow_ocular', d.con_glasgow_ocular],
        ['cv_glasgow_verbal', d.con_glasgow_verbal],
        ['cv_glasgow_motora', d.con_glasgow_motora],
        ['cv_reaccion_pupilar_der', d.con_reaccion_pupila_der],
        ['cv_reaccion_pupilar_izq', d.con_reaccion_pupila_izq],
        ['cv_llenado_capilar', d.con_t_lleno_capilar]
    ];

    camposConstantesVitales.forEach(([elementId, value]) => {
        const input = document.getElementById(elementId);
        if (input && value !== null && value !== undefined && value !== '') {
            input.value = value;
        }
    });

    // Temperatura (si existe en los datos)
    const temperatura = document.getElementById('cv_temperatura');
    if (temperatura && d.con_temperatura) {
        temperatura.value = d.con_temperatura;
    }

    // Clasificación de triaje
    const triajeColor = document.getElementById('cv_triaje_color');
    if (triajeColor && d.ate_colores) {
        triajeColor.value = d.ate_colores;
        if (typeof cambiarColorTriaje === 'function') {
            cambiarColorTriaje(triajeColor);
        }
    }
}

function llenarSeccionH(d) {
    log('info', '👁️ Cargando Sección H - Examen Físico');

    marcarMultiplesCheckboxes(d.zef_codigo, 'zonas_examen_fisico[]', 'Zonas Examen Físico');

    const descripcionEF = document.getElementById('ef_descripcion');
    if (descripcionEF) {
        const descripcion = d.ef_descripcion || d.examen_fisico_descripcion || '';
        if (descripcion) {
            descripcionEF.value = descripcion;
            log('debug', `👁️ Descripción examen físico: ${descripcion}`);
        }
    }
}

function llenarSeccionI(d) {
    log('info', '🚑 Cargando Sección I - Examen Físico de Trauma/Crítico');

    const descripcionEFT = document.getElementById('eft_descripcion');
    if (descripcionEFT) {
        const descripcion = d.tra_descripcion || d.examen_trauma_descripcion || d.eft_descripcion || '';
        if (descripcion) {
            descripcionEFT.value = descripcion;
            log('debug', `🚑 Examen trauma/crítico: ${descripcion}`);
        }
    }
}
function llenarSeccionJ(d) {
    const noAplicaValue = d.emb_no_aplica;
    const valorNumerico = parseInt(noAplicaValue);
    const esNoAplica = valorNumerico === 1;

    if (esNoAplica) {
        const checkboxNoAplica = document.getElementById('emb_no_aplica');
        if (checkboxNoAplica) {
            checkboxNoAplica.checked = true;
            
            if (typeof toggleEmbarazo === 'function') {
                toggleEmbarazo();
            }
            return;
        }
    }

    // Campos de embarazo
    const camposEmbarazo = [
        ['emb_gestas', d.emb_numero_gestas || d.emb_gestas],
        ['emb_partos', d.emb_numero_partos || d.emb_partos],
        ['emb_abortos', d.emb_numero_abortos || d.emb_abortos],
        ['emb_cesareas', d.emb_numero_cesareas || d.emb_cesareas],
        ['emb_semanas_gestacion', d.emb_semanas_gestacion],
        ['emb_afu', d.emb_afu || d.afu],
        ['emb_tiempo_ruptura', d.emb_tiempo || d.emb_tiempo_ruptura],
        ['emb_fcf', d.emb_frecuencia_cardiaca_fetal || d.emb_fcf]
    ];

    camposEmbarazo.forEach(([elementId, value]) => {
        const input = document.getElementById(elementId);
        if (input && value !== null && value !== undefined && value !== '') {
            input.value = value;
        }
    });

    // Fecha de última menstruación
    const fum = document.getElementById('emb_fum');
    if (fum && d.emb_fum) {
        const fechaFum = convertirFechaParaInput(d.emb_fum);
        if (fechaFum) {
            fum.value = fechaFum;
        }
    }

    // Selects de embarazo
    const selectsEmbarazo = [
        ['emb_presentacion', d.emb_presentacion],
        ['emb_plano', d.emb_plano]
    ];

    selectsEmbarazo.forEach(([elementId, value]) => {
        const select = document.getElementById(elementId);
        if (select && value !== null && value !== undefined && value !== '') {
            select.value = value;
        }
    });

    // Radio buttons de embarazo
    const radiosEmbarazo = [
        ['emb_movimiento_fetal', d.emb_movimiento_fetal],
        ['emb_ruptura_membranas', d.emb_ruptura_membranas || d.emb_ruptura_menbranas],
        ['emb_sangrado_vaginal', d.emb_sangrado_vaginal],
        ['emb_contracciones', d.emb_contracciones],
        ['emb_pelvis_viable', d.emb_pelvis_viable]
    ];

    radiosEmbarazo.forEach(([baseName, value]) => {
        if (value !== null && value !== undefined && value !== '') {
            setRadioSiNo(baseName, value);
        }
    });

    // Campos adicionales de texto
    const camposTextoEmbarazo = [
        ['emb_dilatacion', d.emb_dilatacion || d.dilatacion],
        ['emb_borramiento', d.emb_borramiento || d.borramiento],
        ['emb_score_mama', d.emb_score_mama || d.score_mama],
        ['emb_observaciones', d.emb_observaciones || d.observaciones_embarazo]
    ];

    camposTextoEmbarazo.forEach(([elementId, value]) => {
        const input = document.getElementById(elementId);
        if (input && value !== null && value !== undefined && value !== '') {
            input.value = value;
        }
    });
}

function llenarSeccionK(data) {
    log('info', '🧪 Cargando Sección K - Exámenes Complementarios');

    // Manejar checkbox "No aplica"
    const noAplicaCheckbox = document.querySelector('input[name="exc_no_aplica"]');
    if (noAplicaCheckbox) {
        const noAplica = data.exa_no_aplica == 1 || data.exa_no_aplica === true;
        noAplicaCheckbox.checked = noAplica;

        if (noAplica) {
            const tiposExamenes = document.querySelectorAll('input[name="tipos_examenes[]"]');
            tiposExamenes.forEach(checkbox => {
                checkbox.checked = false;
                checkbox.disabled = true;
            });

            const observaciones = document.querySelector('textarea[name="exc_observaciones"]');
            if (observaciones) {
                observaciones.value = 'No aplica';
                observaciones.disabled = true;
            }
            return;
        }
    }

    // Tipos de exámenes seleccionados
    if (data.tipos_examenes_seleccionados) {
        const tiposSeleccionados = data.tipos_examenes_seleccionados.split(',');
        tiposSeleccionados.forEach(tipoId => {
            if (tipoId) {
                const checkbox = document.querySelector(`input[name="tipos_examenes[]"][value="${tipoId}"]`);
                if (checkbox) {
                    checkbox.checked = true;
                }
            }
        });
    }

    // Observaciones
    const observaciones = document.querySelector('textarea[name="exc_observaciones"]');
    if (observaciones && data.exa_observaciones) {
        observaciones.value = data.exa_observaciones;
    }
}

function llenarSeccionL(data) {
    log('info', '🔍 Cargando Sección L - Diagnósticos Presuntivos');

    // 🔥 OPTIMIZACIÓN: Usar FOR LOOP para diagnósticos presuntivos
    for (let i = 1; i <= 3; i++) {
        const descripcionField = document.querySelector(`input[name="diag_pres_desc${i}"], textarea[name="diag_pres_desc${i}"]`);
        const cieField = document.querySelector(`input[name="diag_pres_cie${i}"]`);

        const descripcionValue = data[`diag_pres_desc${i}`] || '';
        const cieValue = data[`diag_pres_cie${i}`] || '';

        if (descripcionField && descripcionValue) {
            descripcionField.value = descripcionValue;
            log('debug', `🔍 Diagnóstico presuntivo ${i} desc: ${descripcionValue}`);
        }

        if (cieField && cieValue) {
            cieField.value = cieValue;
            log('debug', `🔍 Diagnóstico presuntivo ${i} CIE: ${cieValue}`);
        }
    }
}

function llenarSeccionM(data) {
    log('info', '✅ Cargando Sección M - Diagnósticos Definitivos');

    // 🔥 OPTIMIZACIÓN: Usar FOR LOOP para diagnósticos definitivos
    for (let i = 1; i <= 3; i++) {
        const descripcionField = document.querySelector(`input[name="diag_def_desc${i}"], textarea[name="diag_def_desc${i}"]`);
        const cieField = document.querySelector(`input[name="diag_def_cie${i}"]`);

        const descripcionValue = data[`diag_def_desc${i}`] || '';
        const cieValue = data[`diag_def_cie${i}`] || '';

        if (descripcionField && descripcionValue) {
            descripcionField.value = descripcionValue;
            log('debug', `✅ Diagnóstico definitivo ${i} desc: ${descripcionValue}`);
        }

        if (cieField && cieValue) {
            cieField.value = cieValue;
            log('debug', `✅ Diagnóstico definitivo ${i} CIE: ${cieValue}`);
        }
    }
}

function llenarSeccionO(d) {
    log('info', '🚪 Cargando Sección O - Condición al Egreso');

    // 🔥 ACTUALIZACIÓN: Usar arrays de códigos para múltiples checkboxes
    marcarMultiplesCheckboxes(d.estados_egreso || d.ese_codigo, 'estados_egreso[]', 'Estados de Egreso');
    marcarMultiplesCheckboxes(d.modalidades_egreso || d.moe_codigo, 'modalidades_egreso[]', 'Modalidades de Egreso');
    marcarMultiplesCheckboxes(d.tipos_egreso || d.tie_codigo, 'tipos_egreso[]', 'Tipos de Egreso');

    // Campos de texto de egreso
    const camposEgreso = [
        ['egreso_observacion', d.egr_observaciones],
        ['egreso_dias_reposo', d.egr_dias_reposo],
        ['egreso_establecimiento', d.egr_establecimiento]
    ];

    camposEgreso.forEach(([elementId, value]) => {
        const input = document.getElementById(elementId);
        if (input && value) {
            input.value = value;
            log('debug', `🚪 ${elementId}: ${value}`);
        }
    });
}

function llenarSeccionP(data) {
    // Llenar campos de texto básicos
    const campos = {
        'prof_fecha': data.pro_fecha,
        'prof_hora': data.pro_hora,
        'prof_primer_nombre': data.pro_primer_nombre,
        'prof_primer_apellido': data.pro_primer_apellido,
        'prof_segundo_apellido': data.pro_segundo_apellido,
        'prof_documento': data.pro_nro_documento
    };

    Object.keys(campos).forEach(fieldName => {
        const element = document.querySelector(`input[name="${fieldName}"]`);
        if (element && campos[fieldName]) {
            element.value = campos[fieldName];
            element.readOnly = true;
            element.style.backgroundColor = '#f3f4f6';
        }
    });

    // Manejar firma y sello
    manejarImagenProfesional('prof_firma', data.pro_firma_base64, data.pro_firma_existe, 'Firma del Profesional');
    manejarImagenProfesional('prof_sello', data.pro_sello_base64, data.pro_sello_existe, 'Sello del Profesional');
    
    // 🔥 NUEVO: Guardar datos para PDF en una variable global
    window.datosImagenesProfesional = {
        pro_firma_base64: data.pro_firma_base64,
        pro_sello_base64: data.pro_sello_base64,
        pro_firma_existe: data.pro_firma_existe,
        pro_sello_existe: data.pro_sello_existe
    };
}

function manejarImagenProfesional(fieldName, imagenBase64, existe, titulo) {
    const inputField = document.querySelector(`input[name="${fieldName}"]`);
    if (!inputField) return;

    const contenedor = inputField.closest('.file-upload-container') || inputField.parentElement;

    if (existe && imagenBase64) {
        // Hay imagen - mostrarla y ocultar controles
        inputField.style.display = 'none';

        const label = contenedor.querySelector('label');
        if (label) label.style.display = 'none';

        const small = contenedor.querySelector('small');
        if (small) small.style.display = 'none';

        // Buscar el preview existente
        const previewId = fieldName === 'prof_firma' ? 'firma-preview' : 'sello-preview';
        const preview = document.getElementById(previewId);
        
        if (preview) {
            preview.innerHTML = `<img src="${imagenBase64}" alt="${titulo}" style="max-width: 100%; max-height: 100px;">`;
            preview.classList.add('has-image');
        }

        // Guardar la imagen base64 para el PDF
        if (fieldName === 'prof_firma') {
            window.datosImagenesParaPDF = window.datosImagenesParaPDF || {};
            window.datosImagenesParaPDF.firma_base64 = imagenBase64;
            window.datosImagenesParaPDF.firma_existe = true;
        } else if (fieldName === 'prof_sello') {
            window.datosImagenesParaPDF = window.datosImagenesParaPDF || {};
            window.datosImagenesParaPDF.sello_base64 = imagenBase64;
            window.datosImagenesParaPDF.sello_existe = true;
        }

        let imagenContainer = contenedor.querySelector('.imagen-profesional-container');
        if (!imagenContainer) {
            imagenContainer = document.createElement('div');
            imagenContainer.className = 'imagen-profesional-container';
            imagenContainer.innerHTML = `
                <div class="bg-gray-50 border-2 border-gray-200 rounded-lg p-2 text-center">
                    <p class="text-xs text-green-600 font-medium">${titulo} cargada del sistema</p>
                </div>
            `;
            contenedor.appendChild(imagenContainer);
        }
    } else {
        // No hay imagen - limpiar el preview
        const previewId = fieldName === 'prof_firma' ? 'firma-preview' : 'sello-preview';
        const preview = document.getElementById(previewId);
        
        if (preview) {
            preview.innerHTML = '<i class="fas fa-image text-gray-400"></i>';
            preview.classList.remove('has-image');
        }
    }
}

// === FUNCIONES AUXILIARES PARA BOTONES ===
function habilitarBotonPDF(habilitar = true) {
    const btnPDF = document.getElementById('btn-generar-pdf-008');
    if (!btnPDF) return;

    if (habilitar) {
        btnPDF.disabled = false;
        btnPDF.classList.remove('opacity-50', 'cursor-not-allowed');
        btnPDF.classList.add('bg-green-600', 'hover:bg-green-700');
        log('info', '📄 Botón PDF habilitado');
    } else {
        btnPDF.disabled = true;
        btnPDF.classList.add('opacity-50', 'cursor-not-allowed');
        btnPDF.classList.remove('bg-green-600', 'hover:bg-green-700');
        log('info', '📄 Botón PDF deshabilitado');
    }
}

function habilitarBotonConsultar(habilitar = true) {
    const btnConsultar = document.getElementById('btn-consultar-fecha');
    if (!btnConsultar) return;

    btnConsultar.disabled = !habilitar;
    if (habilitar) {
        btnConsultar.classList.remove('opacity-50', 'cursor-not-allowed');
    } else {
        btnConsultar.classList.add('opacity-50', 'cursor-not-allowed');
    }
}

// === FUNCIÓN PARA REINICIAR FORMULARIO A ESTADO INICIAL ===
function reiniciarFormularioEstadoInicial() {
    // Limpiar formulario
    limpiarFormularioCompleto();

    // Desactivar modo lectura
    gestionarModoLectura(false);

    // Limpiar mensajes
    limpiarMensajes();

    // Establecer fecha actual por defecto
    const inputFecha = document.getElementById('filtro-fecha');
    if (inputFecha && !inputFecha.value) {
        inputFecha.value = new Date().toISOString().split('T')[0];
    }

    // 🔥 NUEVO: Bloquear campos del establecimiento siempre
    bloquearCamposEstablecimiento();

    // 🔥 NUEVO: Deshabilitar botón PDF cuando se limpia el formulario
    deshabilitarBotonPDF008();
}

// === FUNCIÓN PARA BLOQUEAR CAMPOS DEL ESTABLECIMIENTO PERMANENTEMENTE ===
function bloquearCamposEstablecimiento() {
    const camposEstablecimiento = [
        'estab_institucion',
        'estab_unicode',
        'estab_nombre',
        'estab_archivo'
    ];

    camposEstablecimiento.forEach(campoId => {
        const campo = document.getElementById(campoId);
        if (campo) {
            campo.disabled = true;
            campo.setAttribute('readonly', 'readonly');
            campo.classList.add('bg-gray-100', 'cursor-not-allowed', 'text-gray-600');
            // NO agregar data-was-blocked para que no se desbloquee nunca
        }
    });
}


// === INICIALIZACIÓN ===
function inicializarBusquedaPorFecha() {

    const btnConsultar = document.getElementById('btn-consultar-fecha');
    const inputFecha = document.getElementById('filtro-fecha');
    const inputIdentificador = document.getElementById('identificador_paciente');

    if (!btnConsultar || !inputFecha || !inputIdentificador) {
        return;
    }


    // Evento principal de búsqueda
    btnConsultar.addEventListener('click', async (event) => {
        event.preventDefault();
        const fecha = inputFecha.value.trim();
        const identificador = inputIdentificador.value.trim();
        await buscarPorFecha(fecha, identificador);
    });

    // Establecer estado inicial
    reiniciarFormularioEstadoInicial();

}



window.alternarModoLectura = function () {
    const enModoLectura = document.getElementById('readonly-indicator') !== null;
    gestionarModoLectura(!enModoLectura);
    log('info', `🔄 Modo lectura ${!enModoLectura ? 'activado' : 'desactivado'} manualmente`);
};


// === FUNCIÓN PARA DESHABILITAR BOTONES PDF ===
function deshabilitarBotonesPDF008() {
    // Deshabilitar botón PDF 008 usando función específica si está disponible
    if (typeof window.deshabilitarBotonPDF008 === 'function') {
        window.deshabilitarBotonPDF008();
        log('info', '🔒 Botón PDF 008 deshabilitado via función específica');
    } else {
        // Fallback manual
        const btnPDF008 = document.getElementById('btn-generar-pdf-008');
        if (btnPDF008) {
            btnPDF008.disabled = true;
            btnPDF008.classList.remove('bg-teal-600', 'hover:bg-teal-700');
            btnPDF008.classList.add('opacity-50', 'cursor-not-allowed');
            btnPDF008.style.opacity = '';
            btnPDF008.style.cursor = '';
            log('info', '🔒 Botón PDF 008 deshabilitado via fallback');
        }
    }

    // Deshabilitar botón PDF 005 usando función específica si está disponible
    if (typeof window.deshabilitarBotonPDF005 === 'function') {
        window.deshabilitarBotonPDF005();
        log('info', '🔒 Botón PDF 005 deshabilitado via función específica');
    } else {
        // Fallback manual
        const btnPDF005 = document.getElementById('btn-generar-pdf-005');
        if (btnPDF005) {
            btnPDF005.disabled = true;
            btnPDF005.classList.remove('bg-teal-600', 'hover:bg-teal-700');
            btnPDF005.classList.add('opacity-50', 'cursor-not-allowed');
            btnPDF005.style.opacity = '';
            btnPDF005.style.cursor = '';
            log('info', '🔒 Botón PDF 005 deshabilitado via fallback');
        }
    }
}

// === FUNCIÓN PARA HABILITAR BOTÓN PDF 008 ===
function habilitarBotonPDF008() {
    const btnPDF008 = document.getElementById('btn-generar-pdf-008');
    if (btnPDF008) {
        btnPDF008.disabled = false;
        btnPDF008.classList.remove('opacity-50', 'cursor-not-allowed');
    }
}

// === FUNCIÓN PARA DESHABILITAR BOTÓN PDF 008 ===
function deshabilitarBotonPDF008() {
    const btnPDF008 = document.getElementById('btn-generar-pdf-008');
    if (btnPDF008) {
        btnPDF008.disabled = true;
        btnPDF008.classList.add('opacity-50', 'cursor-not-allowed');
    }
}

// === INICIALIZACIÓN ===
document.addEventListener('DOMContentLoaded', function() {
    // Solo ejecutar si estamos en una vista que tiene el formulario 008
    const contenedorForm008 = document.getElementById('contenedor-formulario-008');

    if (contenedorForm008) {

        // Asegurar que el botón PDF esté deshabilitado inicialmente
        deshabilitarBotonPDF008();

        // Inicializar búsqueda por fecha
        setTimeout(() => {
            inicializarBusquedaPorFecha();
        }, 100);

    }
});

// === BACKUP: INICIALIZACIÓN ALTERNATIVA ===
// En caso de que DOMContentLoaded ya haya pasado
if (document.readyState === 'complete' || document.readyState === 'interactive') {
    setTimeout(() => {
        const contenedorForm008 = document.getElementById('contenedor-formulario-008');
        if (contenedorForm008) {
            deshabilitarBotonPDF008();
            inicializarBusquedaPorFecha();
        }
    }, 100);
}

// === EXPORTAR FUNCIONES GLOBALES ===
window.buscarPorFecha = buscarPorFecha;
window.llenarFormularioCompleto = llenarFormularioCompleto;
window.limpiarFormularioCompleto = limpiarFormularioCompleto;
window.reiniciarFormularioEstadoInicial = reiniciarFormularioEstadoInicial;
window.habilitarBotonPDF008 = habilitarBotonPDF008;
window.deshabilitarBotonPDF008 = deshabilitarBotonPDF008;

// === EXPORTAR SISTEMA GLOBAL DE LOADING Y MENSAJES ===
window.mostrarMensaje = mostrarMensaje;
window.mostrarMensajeGlobal = mostrarMensajeGlobal;
window.limpiarMensajes = limpiarMensajes;

