// ========================================
// LISTAESPECIALIDAD.JS - FUNCIONALIDAD PARA ESPECIALISTAS CORREGIDA
// ========================================

// Estado global de la aplicación
window.ESTADO_ESPECIALIDADES = {
    especialidadActiva: null,
};

$(document).ready(function () {
    // Verificar que estamos en contexto de especialidades
    if (typeof window.ESPECIALIDADES_URLS === 'undefined') {
        return;
    }

    // Inicializar especialidades
    if (window.ESPECIALIDADES_DATA && window.ESPECIALIDADES_DATA.length > 0) {
        // Establecer primera especialidad como activa
        const primeraEspecialidad = window.ESPECIALIDADES_DATA[0].esp_codigo;
        window.ESTADO_ESPECIALIDADES.especialidadActiva = primeraEspecialidad;

        cargarPacientesEspecialidad(primeraEspecialidad);

        // Cargar contadores para todas las especialidades
        window.ESPECIALIDADES_DATA.forEach(function (especialidad) {
            actualizarContadoresEspecialidad(especialidad.esp_codigo);
        });
    }

    // Auto-refresh cada 45 segundos
    setInterval(function () {
        if (window.ESTADO_ESPECIALIDADES.especialidadActiva) {
            cargarPacientesEspecialidad(window.ESTADO_ESPECIALIDADES.especialidadActiva);
        }
        actualizarTodosLosContadores();
    }, 45000);
});

// FUNCIÓN ELIMINADA: cargarContadoresEspecialidad() - reemplazada por actualizarContadoresEspecialidad()

// FUNCIÓN ÚNICA: Actualizar contadores de una especialidad específica
function actualizarContadoresEspecialidad(esp_codigo) {
    if (!esp_codigo) return;

    $.ajax({
        url: window.ESPECIALIDADES_URLS.obtenerPacientes + '/' + esp_codigo,
        method: "GET",
        dataType: "json",
        success: function (response) {
            if (response.success) {
                const pendientes = response.pendientes?.length || 0;
                const enAtencion = response.en_atencion?.length || 0;
                const enProceso = response.en_proceso?.length || 0;
                const continuandoProceso = response.continuando_proceso?.length || 0;
                const total = pendientes + enAtencion + enProceso + continuandoProceso;

                // Actualizar contador principal de la pestaña
                const tabButton = document.getElementById('tab-esp-' + esp_codigo);
                if (tabButton) {
                    const contadorSpan = tabButton.querySelector('span');
                    if (contadorSpan) {
                        contadorSpan.textContent = total;
                        contadorSpan.className = total > 0 ?
                            'bg-red-500 text-white text-xs rounded-full px-2 py-1 ml-2' :
                            'bg-gray-400 text-white text-xs rounded-full px-2 py-1 ml-2';
                    }
                }

                // Actualizar contadores internos de médicos
                const contadorMedicos = document.getElementById(`count-medicos-${esp_codigo}`);
                if (contadorMedicos) {
                    contadorMedicos.textContent = total;
                }

                // Actualizar contadores de enfermería (obtener por separado)
                actualizarContadoresEnfermeria(esp_codigo);
            }
        },
        error: function (xhr, status, error) {
            console.warn('Error al cargar contadores de especialidad ' + esp_codigo + ':', error);
        }
    });
}

// Actualizar contadores de enfermería
function actualizarContadoresEnfermeria(esp_codigo) {
    $.ajax({
        url: window.ESPECIALIDADES_URLS.obtenerPacientesEnfermeria + '/' + esp_codigo,
        method: "GET",
        dataType: "json",
        success: function (response) {
            if (response.success && response.data) {
                const totalAsignados = response.data.asignados?.length || 0;
                const totalPendientes = response.data.pendientes?.length || 0;
                const totalEnfermeros = totalAsignados + totalPendientes;

                const contadorEnfermeros = document.getElementById(`count-enfermeros-${esp_codigo}`);
                if (contadorEnfermeros) {
                    contadorEnfermeros.textContent = totalEnfermeros;
                }
            }
        },
        error: function () {
            console.warn('Error al cargar contadores de enfermería ' + esp_codigo);
        }
    });
}

// Actualizar todos los contadores
function actualizarTodosLosContadores() {
    if (!window.ESPECIALIDADES_DATA) return;

    window.ESPECIALIDADES_DATA.forEach(function (especialidad) {
        actualizarContadoresEspecialidad(especialidad.esp_codigo);
    });
}

// FUNCIÓN: Cambiar entre pestañas de especialidades
function cambiarTab(button, espCodigo) {
    // Actualizar estado global de especialidad activa
    window.ESTADO_ESPECIALIDADES.especialidadActiva = espCodigo;

    // Remover clases activas de todos los botones (patrón similar a médicos)
    document.querySelectorAll('.tab-button').forEach(btn => {
        btn.classList.remove('border-green-500', 'text-green-600');
        btn.classList.add('border-transparent', 'text-gray-500');
    });

    // Agregar clases activas al botón seleccionado
    button.classList.remove('border-transparent', 'text-gray-500');
    button.classList.add('border-green-500', 'text-green-600');

    // Ocultar todos los contenidos
    document.querySelectorAll('.tab-content').forEach(content => {
        content.classList.add('hidden');
    });

    // Mostrar contenido activo
    const contenidoActivo = document.getElementById('especialidad-' + espCodigo);
    if (contenidoActivo) {
        contenidoActivo.classList.remove('hidden');
    }

    // USAR LA ÚLTIMA VISTA ACTIVA REGISTRADA POR EL USUARIO
    const tipoVistaActiva = window.ULTIMA_VISTA_ACTIVA || 'medicos';


    // Mantener el mismo tipo de vista que estaba activo anteriormente
    if (tipoVistaActiva === 'enfermeros') {
        // Activar automáticamente el botón de enfermeros para esta especialidad
        const btnEnfermeros = document.getElementById(`btn-enfermeros-${espCodigo}`);
        if (btnEnfermeros) {
            // Simular click en el botón de enfermeros sin desencadenar el evento
            setTimeout(() => {
                cambiarTipoPersonal(espCodigo, 'enfermeros', btnEnfermeros);
            }, 100);
        }
    } else {
        // Activar automáticamente el botón de médicos para esta especialidad
        const btnMedicos = document.getElementById(`btn-medicos-${espCodigo}`);
        if (btnMedicos) {
            // Simular click en el botón de médicos sin desencadenar el evento
            setTimeout(() => {
                cambiarTipoPersonal(espCodigo, 'medicos', btnMedicos);
            }, 100);
        }
    }
}

// FUNCIÓN ELIMINADA: actualizarContadoresPestanas() - duplicaba funcionalidad con actualizarTodosLosContadores()
// FUNCIÓN PRINCIPAL: Cargar pacientes de una especialidad específica
function cargarPacientesEspecialidad(esp_codigo) {
    if (!esp_codigo) {
        console.error('Código de especialidad no válido');
        return;
    }

    mostrarCargandoPacientes(esp_codigo);

    $.ajax({
        url: window.ESPECIALIDADES_URLS.obtenerPacientes + '/' + esp_codigo,
        method: "GET",
        dataType: "json",
        success: function (response) {
            if (response.success) {

                // Cargar las CUATRO categorías
                cargarTablaPacientes(response.pendientes || [], 'pendientes', esp_codigo);
                cargarTablaPacientes(response.en_proceso || [], 'proceso', esp_codigo);
                cargarTablaPacientes(response.en_atencion || [], 'atencion', esp_codigo);
                cargarTablaPacientes(response.continuando_proceso || [], 'continuando', esp_codigo);

                // Actualizar contadores
                actualizarContadores(esp_codigo,
                    response.pendientes?.length || 0,
                    response.en_proceso?.length || 0,
                    response.en_atencion?.length || 0,
                    response.continuando_proceso?.length || 0
                );

                // También cargar datos de enfermería si el usuario está en esa vista
                const btnEnfermerosActivo = document.getElementById(`btn-enfermeros-${esp_codigo}`)?.classList.contains('active');
                if (btnEnfermerosActivo) {
                    cargarDatosEnfermeriaCompletos(esp_codigo);
                }
            }
        }
    });
}

function mostrarCargandoPacientes(esp_codigo) {
    const tbodyPendientes = $(`#pacientes-pendientes-${esp_codigo}`);
    const tbodyProceso = $(`#pacientes-proceso-${esp_codigo}`);        // NUEVO
    const tbodyAtencion = $(`#pacientes-atencion-${esp_codigo}`);

    const filaCargandoPendientes = `
        <tr>
            <td colspan="4" class="text-center py-8 text-gray-500">
                <i class="fas fa-spinner fa-spin mr-2"></i>
                Cargando...
            </td>
        </tr>
    `;

    const filaCargandoProceso = `
        <tr>
            <td colspan="5" class="text-center py-8 text-gray-500">
                <i class="fas fa-spinner fa-spin mr-2"></i>
                Cargando...
            </td>
        </tr>
    `;

    tbodyPendientes.html(filaCargandoPendientes);
    tbodyProceso.html(filaCargandoProceso);        // NUEVO - Con 5 columnas
    tbodyAtencion.html(filaCargandoPendientes);
}

// FUNCIÓN: Mostrar indicadores de carga
function mostrarErrorCarga(esp_codigo, mensaje) {
    const tbodyPendientes = $(`#pacientes-pendientes-${esp_codigo}`);
    const tbodyAtencion = $(`#pacientes-atencion-${esp_codigo}`);

    const filaError = `
        <tr>
            <td colspan="4" class="text-center py-8 text-red-500">
                <i class="fas fa-exclamation-triangle mr-2"></i>
                Error: ${mensaje}
                <br>
                <button onclick="cargarPacientesEspecialidad(${esp_codigo})" 
                        class="mt-2 text-blue-600 hover:underline">
                    <i class="fas fa-redo mr-1"></i> Reintentar
                </button>
            </td>
        </tr>
    `;

    tbodyPendientes.html(filaError);
    tbodyAtencion.html(filaError);
}


// FUNCIÓN: Cargar tabla de pacientes (pendientes, proceso, atencion, continuando)
function cargarTablaPacientes(pacientes, tipo, esp_codigo) {
    const tbody = $(`#pacientes-${tipo}-${esp_codigo}`);
    tbody.empty();

    if (!pacientes || pacientes.length === 0) {
        let mensajeVacio;
        let colspan = 4; // Valor por defecto para pendientes y otros

        switch (tipo) {
            case 'pendientes':
                mensajeVacio = 'No hay pacientes pendientes de atención';
                break;
            case 'proceso':
                mensajeVacio = 'No hay procesos parciales guardados';
                colspan = 5; // Paciente, Triaje, Especialista, Guardado, Acciones
                break;
            case 'atencion':
                mensajeVacio = 'No hay pacientes en atención actualmente';
                break;
            case 'continuando':
                mensajeVacio = 'No hay especialistas continuando procesos';
                break;
        }

        tbody.append(`
            <tr>
                <td colspan="${colspan}" class="text-center py-8 text-gray-500">
                    <i class="fas fa-info-circle mr-2"></i>
                    ${mensajeVacio}
                </td>
            </tr>
        `);
        return;
    }

    pacientes.forEach(function (paciente) {
        let fila = '';
        let badgeTriaje = determinarColorTriaje(paciente.triaje_color || paciente.ate_colores, 'badge');

        if (tipo === 'pendientes') {
            fila = generarFilaPacientePendiente(paciente, badgeTriaje);
        } else if (tipo === 'proceso') {
            fila = generarFilaPacienteEnProceso(paciente, badgeTriaje);
        } else if (tipo === 'atencion') {
            fila = generarFilaPacienteEnAtencion(paciente, badgeTriaje);
        } else if (tipo === 'continuando') {
            fila = generarFilaPacienteContinuando(paciente, badgeTriaje);  // NUEVA FUNCIÓN
        }

        tbody.append(fila);
    });
}
function generarFilaPacienteEnAtencion(paciente, badgeTriaje) {
    const codigo_paciente = paciente.are_codigo || paciente.ate_codigo;
    const tiempoTranscurrido = calcularTiempoTranscurrido(paciente.are_hora_inicio_atencion);

    // Verificar si es un paciente tomado desde observación
    const esDesdeObservacion = paciente.tipo_atencion === 'observacion';
    let indicadorEspecial = '';

    if (esDesdeObservacion) {
        const infoObservacion = paciente.observacion_info || {};
        indicadorEspecial = `
            <div class="text-xs text-orange-600 font-medium flex items-center mt-1">
                <i class="fas fa-eye mr-1"></i>
                Tomado desde Observación
            </div>
            <div class="text-xs text-gray-500 mt-1">
                <span class="font-medium">Origen:</span> ${infoObservacion.especialidad_origen || 'No registrado'}
            </div>
        `;
    }

    // Detectar si es modificación habilitada (igual que listaMedicos.js)
    let indicadorModificacion = '';
    let claseFilaModificacion = 'hover:bg-green-50 transition-colors border-l-4 border-green-400';
    let tituloBoton = 'Continuar con la atención';
    let iconoBoton = 'edit';
    let textoBoton = 'Continuar';

    if (paciente.habilitado_por_admin == 1 || paciente.es_modificacion || paciente.tipo_acceso === 'MODIFICACION_HABILITADA') {
        indicadorModificacion = '<span class="bg-orange-100 text-orange-800 px-2 py-1 rounded-full text-xs font-bold ml-2">🔄 MODIFICACIÓN</span>';
        claseFilaModificacion = 'hover:bg-orange-50 transition-colors border-l-4 border-orange-400 bg-orange-50';
        tituloBoton = 'Modificar formulario de especialidad previamente completado';
        iconoBoton = 'edit';
        textoBoton = 'Modificar';
    }

    return `
        <tr class="${claseFilaModificacion}">
            <td class="px-4 py-3">
                <div class="font-medium text-gray-900">
                    ${paciente.pac_nombres || 'N/A'} ${paciente.pac_apellidos || ''}
                    ${indicadorModificacion}
                </div>
                <div class="text-sm text-gray-500">
                    CI: ${paciente.pac_cedula || 'No registrada'}
                </div>
                <div class="text-xs text-green-600 font-medium flex items-center mt-1">
                    <i class="fas fa-user-md mr-1"></i>
                    En Atención
                </div>
                ${indicadorEspecial}
            </td>
            <td class="px-4 py-3">
                <div class="font-medium text-gray-700">
                    ${(paciente.medico_nombre || 'Médico') + ' ' + (paciente.medico_apellido || '')}
                </div>
                <div class="text-xs text-gray-500">
                    Especialista asignado
                </div>
            </td>
            <td class="px-4 py-3">
                <div class="text-sm text-gray-600">
                    ${formatearHora(paciente.are_hora_inicio_atencion)}
                </div>
                <div class="text-xs text-gray-400">
                    ${tiempoTranscurrido ? `Hace ${tiempoTranscurrido}` : 'Tiempo no calculado'}
                </div>
            </td>
            <td class="px-4 py-3">
                <div class="flex space-x-2">
                    <button class="bg-green-600 hover:bg-green-700 text-white px-3 py-1 rounded-md text-sm transition-colors flex items-center"
                            onclick="continuarAtencion(${codigo_paciente})"
                            title="${tituloBoton}">
                        <i class="fas fa-${iconoBoton} mr-1"></i>
                        ${textoBoton}
                    </button>
                </div>
            </td>
        </tr>
    `;
}
function generarFilaPacientePendiente(paciente, badgeTriaje) {
    const codigo_paciente = paciente.are_codigo || paciente.ate_codigo;

    // Detectar si fue enviado a observación
    const esEnviadoObservacion = paciente.tipo_especial === 'enviado_observacion' ||
        paciente.are_estado === 'ENVIADO_A_OBSERVACION' ||
        (paciente.are_observaciones && paciente.are_observaciones.includes('Enviado a observación'));

    // Determinar clase y contenido según el estado
    let claseFilaEstado = '';
    let indicadorEstado = '';
    let textoBoton = 'Tomar';
    let claseBoton = 'bg-green-600 hover:bg-green-700';
    let iconoBoton = 'user-md';
    let funcionBoton = `tomarAtencionEspecialidad(${codigo_paciente})`;

    if (esEnviadoObservacion) {
        // Obtener información de origen
        const especialidadOrigen = paciente.especialidad_origen_nombre || paciente.especialidad_origen || 'Especialidad no encontrada';
        const medicoQueEnvio = paciente.medico_envia_nombre ? `${paciente.medico_envia_nombre} ${paciente.medico_envia_apellido}` : 'Médico no encontrado';

        claseFilaEstado = 'bg-orange-50 border-l-4 border-orange-500';
        indicadorEstado = `
            <div class="text-xs text-orange-600 font-medium flex items-center mt-1">
                <i class="fas fa-arrow-right mr-1"></i>
                Enviado a Observación
            </div>
            <div class="text-xs text-gray-600 mt-1">
                <span class="font-medium">Desde:</span> ${especialidadOrigen}
            </div>
            <div class="text-xs text-gray-600 mt-1">
                <span class="font-medium">Enviado por:</span> ${medicoQueEnvio}
            </div>
        `;

        // El botón sigue siendo "Tomar" y llama a tomarAtencionEspecialidad
        textoBoton = 'Tomar';
        claseBoton = 'bg-green-600 hover:bg-green-700'; // Verde como los demás
        iconoBoton = 'user-md';
        funcionBoton = `tomarAtencionEspecialidad(${codigo_paciente})`;
    }

    return `
        <tr class="hover:bg-gray-50 transition-colors ${claseFilaEstado}" data-are-codigo="${codigo_paciente}">
            <td class="px-4 py-3">
                <div class="font-medium text-gray-900">
                    ${paciente.pac_nombres || 'N/A'} ${paciente.pac_apellidos || ''}
                </div>
                <div class="text-sm text-gray-500">
                    CI: ${paciente.pac_cedula || 'No registrada'}
                </div>
                ${indicadorEstado}
                ${esEnviadoObservacion ? `
                    <div style="display:none;" 
                         data-especialidad-origen="${paciente.especialidad_origen_nombre || paciente.especialidad_origen || ''}"
                         data-motivo="${paciente.motivo_observacion || paciente.motivo_envio || ''}">
                    </div>
                ` : ''}
            </td>
            <td class="px-4 py-3">
                ${badgeTriaje}
            </td>
            <td class="px-4 py-3">
                <div class="text-sm text-gray-600">
                    ${formatearHora(paciente.are_hora_asignacion || paciente.ate_hora)}
                </div>
                <div class="text-xs text-gray-400">
                    ${esEnviadoObservacion ? 'Enviado a Observación' : 'Asignado: ' + formatearFecha(paciente.are_fecha_asignacion || paciente.ate_fecha)}
                </div>
            </td>
            <td class="px-4 py-3">
                <button class="${claseBoton} text-white px-3 py-1 rounded-md text-sm transition-colors flex items-center"
                        onclick="${funcionBoton}"
                        title="${esEnviadoObservacion ? 'Tomar atención en observación' : 'Tomar atención del paciente'}">
                    <i class="fas fa-${iconoBoton} mr-1"></i>
                    ${textoBoton}
                </button>
            </td>
        </tr>
    `;
}
// NUEVA FUNCIÓN: Generar fila para pacientes continuando proceso
function generarFilaPacienteContinuando(paciente, badgeTriaje) {
    const codigo_paciente = paciente.are_codigo || paciente.ate_codigo;
    const infoProceso = paciente.info_proceso || {};

    // Detectar si es modificación habilitada (igual que listaMedicos.js)
    let indicadorModificacion = '';
    let claseFilaModificacion = 'hover:bg-blue-50 transition-colors border-l-4 border-blue-400';
    let tituloBoton = 'Continuar con la atención';
    let iconoBoton = 'edit';
    let textoBoton = 'Continuar';

    if (paciente.habilitado_por_admin == 1 || paciente.es_modificacion || paciente.tipo_acceso === 'MODIFICACION_HABILITADA') {
        indicadorModificacion = '<span class="bg-orange-100 text-orange-800 px-2 py-1 rounded-full text-xs font-bold ml-2">🔄 MODIFICACIÓN</span>';
        claseFilaModificacion = 'hover:bg-orange-50 transition-colors border-l-4 border-orange-400 bg-orange-50';
        tituloBoton = 'Modificar formulario de especialidad previamente completado';
        iconoBoton = 'edit';
        textoBoton = 'Modificar';
    }

    return `
        <tr class="${claseFilaModificacion}">
            <td class="px-4 py-3">
                <div class="font-medium text-gray-900">
                    ${paciente.pac_nombres || 'N/A'} ${paciente.pac_apellidos || ''}
                    ${indicadorModificacion}
                </div>
                <div class="text-sm text-gray-500">
                    CI: ${paciente.pac_cedula || 'No registrada'}
                </div>
                <div class="text-xs text-blue-600 font-medium flex items-center mt-1">
                    <i class="fas fa-play mr-1"></i>
                    Continuando Proceso
                </div>
            </td>
            <td class="px-4 py-3">
                <div class="font-medium text-gray-700">
                    ${paciente.medico_nombre || 'Especialista actual'}
                </div>
                <div class="text-xs text-gray-500">
                    Continuando atención
                </div>
            </td>
            <td class="px-4 py-3">
                <div class="text-sm text-gray-600">
                    ${infoProceso.especialista_nombre || 'Especialista original'}
                </div>
                <div class="text-xs text-gray-400">
                    Proceso original
                </div>
            </td>
            <td class="px-4 py-3">
                <button class="bg-blue-600 hover:bg-blue-700 text-white px-3 py-1 rounded-md text-sm transition-colors flex items-center"
                        onclick="continuarAtencion(${codigo_paciente})"
                        title="${tituloBoton}">
                    <i class="fas fa-${iconoBoton} mr-1"></i>
                    ${textoBoton}
                </button>
            </td>
        </tr>
    `;
}
// NUEVA FUNCIÓN: Generar fila para pacientes en proceso
function generarFilaPacienteEnProceso(paciente, badgeTriaje) {
    const codigo_paciente = paciente.are_codigo || paciente.ate_codigo;
    const infoProceso = paciente.info_proceso || {};

    // Determinar valores con fallbacks
    let nombreEspecialista = 'Especialista no encontrado';
    let especialidadNombre = 'Sin especialidad';
    let fechaGuardado = 'No registrada';
    let horaGuardado = '';

    if (infoProceso && Object.keys(infoProceso).length > 0) {
        nombreEspecialista = infoProceso.especialista_nombre || 'Especialista no encontrado';
        especialidadNombre = infoProceso.especialidad_nombre || 'Sin especialidad';

        if (infoProceso.fecha_guardado) {
            fechaGuardado = formatearFecha(infoProceso.fecha_guardado);
        }
        if (infoProceso.hora_guardado) {
            horaGuardado = formatearHora(infoProceso.hora_guardado);
        }
    } else if (paciente.medico_nombre) {
        // Fallback al médico del área
        nombreEspecialista = paciente.medico_nombre + ' ' + (paciente.medico_apellido || '');
        especialidadNombre = paciente.esp_nombre || 'Sin especialidad';
    }

    return `
        <tr class="hover:bg-purple-50 transition-colors border-l-4 border-purple-400">
            <td class="px-4 py-3">
                <div class="font-medium text-gray-900">
                    ${paciente.pac_nombres || 'N/A'} ${paciente.pac_apellidos || ''}
                </div>
                <div class="text-sm text-gray-500">
                    CI: ${paciente.pac_cedula || 'No registrada'}
                </div>
                <div class="text-xs text-purple-600 font-medium flex items-center mt-1">
                    <i class="fas fa-save mr-1"></i>
                    Proceso Guardado
                </div>
            </td>
            <td class="px-4 py-3">
                ${badgeTriaje}
            </td>
            <td class="px-4 py-3">
                <div class="font-medium text-gray-700">
                    ${nombreEspecialista}
                </div>
                <div class="text-xs text-gray-500">
                    ${especialidadNombre}
                </div>
            </td>
            <td class="px-4 py-3">
                <div class="text-sm text-gray-600">
                    ${horaGuardado}
                </div>
                <div class="text-xs text-gray-400">
                    ${fechaGuardado || 'Hora no registrada'}
                </div>
            </td>
            <td class="px-4 py-3">
                <div class="flex space-x-2">
                    <button class="bg-purple-500 hover:bg-purple-600 text-white px-3 py-1 rounded-md text-sm transition-colors flex items-center"
                            onclick="continuarProcesoParcial(${codigo_paciente})"
                            title="Continuar proceso parcial guardado">
                        <i class="fas fa-play mr-1"></i>
                        Continuar
                    </button>
                </div>
            </td>
        </tr>
    `;
}
// NUEVAS FUNCIONES para manejar procesos parciales
function continuarProcesoParcial(are_codigo) {
    if (!are_codigo) {
        mostrarError('Código de atención no válido');
        return;
    }


    // Llamar al endpoint para determinar qué tipo de validación necesita
    $.ajax({
        url: window.ESPECIALIDADES_URLS.validarContinuarProceso,
        method: "POST",
        data: {
            are_codigo: are_codigo
        },
        dataType: "json",
        success: function (response) {
            if (response.success) {
                const tipoValidacion = response.tipo_validacion;

                if (tipoValidacion === 'MISMO_ESPECIALISTA') {
                    // Solo pedir contraseña
                    mostrarModalValidacionContinuar(are_codigo, 'MISMO_ESPECIALISTA', response.mensaje);
                } else if (tipoValidacion === 'DIFERENTE_ESPECIALISTA') {
                    // Pedir usuario y contraseña
                    mostrarModalValidacionContinuar(are_codigo, 'DIFERENTE_ESPECIALISTA', response.mensaje);
                }
            } else {
                mostrarError('Error: ' + response.error);
            }
        },
        error: function (xhr, status, error) {
            mostrarError('Error de comunicación: ' + error);
        }
    });
}
// Modal específico para continuar proceso
function mostrarModalValidacionContinuar(are_codigo, tipoValidacion, mensaje) {
    // Limpiar campos
    $('#usuario_continuar').val('');
    $('#password_continuar').val('');
    $('#are_codigo_continuar').val(are_codigo);
    $('#tipo_validacion_continuar').val(tipoValidacion);

    // Configurar el modal
    const titulo = document.getElementById('titulo-modal-continuar');
    const campoUsuario = document.getElementById('campo-usuario-continuar');
    const mensajeModal = document.getElementById('mensaje-continuar');

    // SIEMPRE mostrar ambos campos para continuación de proceso
    titulo.innerHTML = '<i class="fas fa-user-plus mr-2"></i>Continuar Proceso Parcial';
    campoUsuario.style.display = 'block';
    mensajeModal.innerHTML = mensaje.replace(/\n/g, '<br>');

    // Mostrar modal
    document.getElementById('modalContinuarProceso').classList.remove('hidden');
    document.getElementById('modalContinuarProceso').classList.add('flex');

    // Focus en usuario
    setTimeout(() => $('#usuario_continuar').focus(), 300);
}

function cerrarModalContinuar() {
    document.getElementById('modalContinuarProceso').classList.add('hidden');
    document.getElementById('modalContinuarProceso').classList.remove('flex');
    $('#usuario_continuar').val('');
    $('#password_continuar').val('');
}

function confirmarContinuarProceso() {
    const are_codigo = $('#are_codigo_continuar').val();
    const tipoValidacion = $('#tipo_validacion_continuar').val();
    const password = $('#password_continuar').val();
    const usuario = $('#usuario_continuar').val();

    if (!password) {
        Swal.fire({
            icon: 'warning',
            title: 'Campo requerido',
            text: 'Ingrese su contraseña',
            confirmButtonText: 'Aceptar'
        }).then(() => {
            $('#password_continuar').focus();
        });
        return;
    }

    if (tipoValidacion === 'DIFERENTE_ESPECIALISTA' && !usuario) {
        Swal.fire({
            icon: 'warning',
            title: 'Campo requerido',
            text: 'Ingrese su usuario',
            confirmButtonText: 'Aceptar'
        }).then(() => {
            $('#usuario_continuar').focus();
        });
        return;
    }

    // Preparar datos
    const datos = {
        are_codigo: are_codigo,
        password: password,
        tipo_validacion: tipoValidacion
    };

    if (tipoValidacion === 'DIFERENTE_ESPECIALISTA') {
        datos.usuario = usuario;
    }

    // Deshabilitar botón
    const submitBtn = document.querySelector('#modalContinuarProceso button[onclick="confirmarContinuarProceso()"]');
    const originalText = submitBtn.innerHTML;
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i> Validando...';

    $.ajax({
        url: window.ESPECIALIDADES_URLS.continuarProcesoConValidacion,
        method: "POST",
        data: datos,
        dataType: "json",
        success: function (response) {
            if (response.success) {
                cerrarModalContinuar();
                mostrarNotificacion(response.message, 'success');

                // Abrir formulario
                window.open(response.redirect_url, '_blank');

                // Refrescar lista
                setTimeout(function () {
                    const tabActivo = document.querySelector('.tab-button.border-green-500');
                    if (tabActivo) {
                        const espCodigo = tabActivo.dataset.especialidad;
                        cargarPacientesEspecialidad(espCodigo);
                        // También refrescar datos de enfermería
                        actualizarContadoresEspecialidad(espCodigo);
                    }
                    actualizarTodosLosContadores();
                }, 2000);
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: response.error,
                    confirmButtonText: 'Aceptar'
                }).then(() => {
                    if (tipoValidacion === 'MISMO_ESPECIALISTA') {
                        $('#password_continuar').val('').focus();
                    } else {
                        $('#usuario_continuar').val('').focus();
                    }
                });
            }
        },
        error: function (xhr, status, error) {
            Swal.fire({
                icon: 'error',
                title: 'Error de comunicación',
                text: 'No se pudo conectar con el servidor',
                confirmButtonText: 'Aceptar'
            });
        },
        complete: function () {
            submitBtn.disabled = false;
            submitBtn.innerHTML = originalText;
        }
    });
}

// Permitir Enter en los campos
$(document).ready(function () {
    $('#password_continuar').on('keypress', function (e) {
        if (e.which === 13) {
            e.preventDefault();
            confirmarContinuarProceso();
        }
    });

    $('#usuario_continuar').on('keypress', function (e) {
        if (e.which === 13) {
            e.preventDefault();
            $('#password_continuar').focus();
        }
    });

    // Modal Tomar Atención - Agregar funcionalidad Enter
    $('#usuario_tomar').on('keypress', function (e) {
        if (e.which === 13) {
            e.preventDefault();
            $('#password_tomar').focus();
        }
    });
    $('#password_tomar').on('keypress', function (e) {
        if (e.which === 13) {
            e.preventDefault();
            confirmarTomarConCredenciales();
        }
    });
});

// Función mejorada para ver estado de observación con información de origen
function verEstadoObservacion(are_codigo, especialidadOrigen = '', motivoEnvio = '') {
    if (!are_codigo) {
        mostrarError('Código de atención no válido');
        return;
    }

    // Construir HTML estructurado para la alerta
    let mensajeHTML = '<div class="text-left">';

    // Información de origen
    if (especialidadOrigen) {
        mensajeHTML += `
            <div class="mb-3 p-3 bg-blue-50 rounded border-l-4 border-blue-400">
                <p class="text-sm"><strong class="text-blue-700">📍 Enviado desde:</strong> ${especialidadOrigen}</p>
            </div>
        `;
    }

    // Estado y ubicación
    mensajeHTML += `
        <div class="mb-3 space-y-2">
            <p class="flex items-center">
                <span class="text-green-600 font-semibold mr-2">📄 Estado:</span>
                <span class="bg-green-100 text-green-800 px-3 py-1 rounded-full text-sm font-semibold">En Observación</span>
            </p>
            <p class="text-gray-700">
                <span class="font-semibold">🏢 Ubicación:</span> Módulo de Observación
            </p>
        </div>
    `;

    // Motivo del envío (si existe)
    if (motivoEnvio && motivoEnvio !== 'Motivo no especificado') {
        mensajeHTML += `
            <div class="mb-3 p-3 bg-yellow-50 rounded border-l-4 border-yellow-400">
                <p class="text-sm font-semibold text-yellow-800 mb-1">💭 Motivo del envío:</p>
                <p class="text-sm text-gray-700 italic">"${motivoEnvio}"</p>
            </div>
        `;
    }

    // Instrucciones de seguimiento
    mensajeHTML += `
        <div class="mt-4 p-3 bg-gray-50 rounded border border-gray-200">
            <p class="font-semibold text-gray-800 mb-2">👩‍⚕️ Para dar seguimiento:</p>
            <ul class="space-y-1 text-sm text-gray-700">
                <li class="flex items-start">
                    <span class="mr-2">•</span>
                    <span>Consulte el módulo de Observación</span>
                </li>
                <li class="flex items-start">
                    <span class="mr-2">•</span>
                    <span>Este paciente ya no está disponible en esta especialidad</span>
                </li>
                <li class="flex items-start">
                    <span class="mr-2">•</span>
                    <span>La atención continuará en el área de Observación</span>
                </li>
            </ul>
        </div>
    `;

    mensajeHTML += '</div>';

    Swal.fire({
        icon: 'info',
        title: '🏥 Paciente en Observación',
        html: mensajeHTML,
        confirmButtonText: 'Entendido',
        confirmButtonColor: '#3b82f6',
        width: '600px'
    });
}

function mostrarInfoYLuegoModal(are_codigo) {
    // Obtener datos del paciente desde la fila
    const filaActual = document.querySelector(`tr[data-are-codigo="${are_codigo}"]`);
    let especialidadOrigen = 'Especialidad no encontrada';
    let motivoEnvio = 'Motivo no especificado';

    if (filaActual) {
        const elementoOculto = filaActual.querySelector('[data-especialidad-origen]');
        if (elementoOculto) {
            especialidadOrigen = elementoOculto.dataset.especialidadOrigen || 'Especialidad no encontrada';
            motivoEnvio = elementoOculto.dataset.motivo || 'Motivo no especificado';
        }
    }

    // Construir HTML estructurado
    let mensajeHTML = '<div class="text-left">';

    // Información de origen
    mensajeHTML += `
        <div class="mb-3 p-3 bg-blue-50 rounded border-l-4 border-blue-400">
            <p class="text-sm"><strong class="text-blue-700">📍 Enviado desde:</strong> ${especialidadOrigen}</p>
        </div>
    `;

    // Estado y ubicación
    mensajeHTML += `
        <div class="mb-3 space-y-2">
            <p class="flex items-center">
                <span class="text-green-600 font-semibold mr-2">📄 Estado:</span>
                <span class="bg-green-100 text-green-800 px-3 py-1 rounded-full text-sm font-semibold">En Observación</span>
            </p>
            <p class="text-gray-700">
                <span class="font-semibold">🏢 Ubicación:</span> Módulo de Observación
            </p>
        </div>
    `;

    // Motivo del envío (si existe)
    if (motivoEnvio && motivoEnvio !== 'Motivo no especificado') {
        mensajeHTML += `
            <div class="mb-3 p-3 bg-yellow-50 rounded border-l-4 border-yellow-400">
                <p class="text-sm font-semibold text-yellow-800 mb-1">💭 Motivo del envío:</p>
                <p class="text-sm text-gray-700 italic">"${motivoEnvio}"</p>
            </div>
        `;
    }

    // Pregunta de confirmación
    mensajeHTML += `
        <div class="mt-4 p-3 bg-blue-50 rounded border border-blue-200">
            <p class="font-semibold text-blue-800 mb-1">👩‍⚕️ ¿Desea tomar esta atención?</p>
            <p class="text-sm text-gray-700">Se abrirá el formulario de credenciales para confirmar.</p>
        </div>
    `;

    mensajeHTML += '</div>';

    // Mostrar confirmación con SweetAlert2
    Swal.fire({
        icon: 'question',
        title: '🏥 Paciente en Observación',
        html: mensajeHTML,
        showCancelButton: true,
        confirmButtonText: 'Sí, tomar atención',
        cancelButtonText: 'Cancelar',
        confirmButtonColor: '#3b82f6',
        cancelButtonColor: '#6b7280',
        width: '600px'
    }).then((result) => {
        if (result.isConfirmed) {
            $('#are_codigo_tomar').val(are_codigo);
            mostrarModalTomar();
        }
    });
}
// Función para actualizar la vista después del envío a observación
function actualizarVistaPostObservacion(are_codigo) {
    // Buscar la fila del paciente y actualizarla
    const tabla = document.querySelector(`tr[data-are-codigo="${are_codigo}"]`);
    if (tabla) {
        tabla.classList.add('bg-orange-50', 'border-l-4', 'border-orange-500');

        // Actualizar el indicador de estado
        const celdaPaciente = tabla.querySelector('td:first-child');
        if (celdaPaciente) {
            const indicadorExistente = celdaPaciente.querySelector('.text-orange-600');
            if (!indicadorExistente) {
                const indicador = document.createElement('div');
                indicador.className = 'text-xs text-orange-600 font-medium flex items-center mt-1';
                indicador.innerHTML = '<i class="fas fa-eye mr-1"></i>Enviado a Observación';
                celdaPaciente.appendChild(indicador);
            }
        }

        // Actualizar el botón de acción
        const botonTomar = tabla.querySelector('button[onclick*="tomarAtencionEspecialidad"]');
        if (botonTomar) {
            botonTomar.className = 'bg-orange-500 hover:bg-orange-600 text-white px-3 py-1 rounded-md text-sm transition-colors flex items-center';
            botonTomar.innerHTML = '<i class="fas fa-eye mr-1"></i>Ver Estado';
            botonTomar.setAttribute('onclick', `verEstadoObservacion(${are_codigo})`);
            botonTomar.setAttribute('title', 'Ver estado en observación');
        }
    }

    // Actualizar contadores
    setTimeout(() => {
        const tabActivo = document.querySelector('.tab-button.border-green-500');
        if (tabActivo) {
            const espCodigo = tabActivo.dataset.especialidad;
            cargarPacientesEspecialidad(espCodigo);
        }
    }, 1000);
}
function validarYModificar(are_codigo) {
    if (!are_codigo) {
        mostrarError('Código de atención no válido');
        return;
    }


    // Mensaje específico para modificación
    const mensajeModificacion = "🔄 MODIFICACIÓN HABILITADA\n\n" +
        "Esta es una modificación autorizada por el administrador.\n\n" +
        "Para continuar, debe ingresar la contraseña del médico que atendió ORIGINALMENTE al paciente.";

    mostrarModalValidacion(are_codigo, mensajeModificacion);
}
// FUNCIÓN: Actualizar contadores
function actualizarContadores(esp_codigo, pendientes, enProceso, enAtencion, continuando = 0) {
    $(`#count-pendientes-${esp_codigo}`).text(pendientes);
    $(`#count-proceso-${esp_codigo}`).text(enProceso);
    $(`#count-atencion-${esp_codigo}`).text(enAtencion);
    $(`#count-continuando-${esp_codigo}`).text(continuando);

    // Actualizar contador total en la pestaña
    const tab = $(`#tab-esp-${esp_codigo} span`);
    if (tab.length) {
        const total = pendientes + enProceso + enAtencion + continuando;
        tab.text(total);

        tab[0].className = total > 0 ?
            'bg-red-500 text-white text-xs rounded-full px-2 py-1 ml-2' :
            'bg-gray-400 text-white text-xs rounded-full px-2 py-1 ml-2';
    }
}

// FUNCIÓN: Tomar atención de un paciente
function tomarAtencionEspecialidad(are_codigo) {
    if (!are_codigo) {
        mostrarError('Código de atención no válido');
        return;
    }


    // Verificar si es un paciente enviado a observación
    const filaActual = document.querySelector(`tr[data-are-codigo="${are_codigo}"]`);
    
    const esEnviadoObservacion = filaActual && (
        filaActual.classList.contains('border-orange-500') ||
        filaActual.querySelector('.text-orange-600')
    );

    if (esEnviadoObservacion) {
        // Si es enviado a observación, primero mostrar información
        mostrarInfoYLuegoModal(are_codigo);
    } else {
        // Si es paciente normal, directamente abrir modal
        $('#are_codigo_tomar').val(are_codigo);
        mostrarModalTomar();
    }
}

/**
 * Detectar si estamos en contexto de enfermería
 * @returns {boolean}
 */
function detectarContextoEnfermeria() {

    // Verificar variables globales de contexto
    if (window.contextoEnfermeria === true) {
        return true;
    }

    // Verificar si la pestaña activa es de enfermería
    const tabActivo = document.querySelector('.tab-button.border-green-500');
    if (tabActivo && tabActivo.textContent.includes('Enfermería')) {
        return true;
    }

    // Verificar si estamos viendo contenido de enfermería
    const contenidoEnfermeria = document.querySelector('[id*="enfermeria-content"]');
    if (contenidoEnfermeria && !contenidoEnfermeria.classList.contains('hidden')) {
        return true;
    }

    // NUEVA VERIFICACIÓN: Buscar botón "Ver Formulario" específico de enfermería
    const botonVerFormulario = document.querySelector('[onclick*="mostrarModalValidacionEnfermeria"]');
    if (botonVerFormulario) {
        return true;
    }

    // NUEVA VERIFICACIÓN: Buscar tabla de enfermería visible
    const tablaEnfermeria = document.querySelector('table[id*="enfermeria-"]');
    if (tablaEnfermeria && tablaEnfermeria.closest('.hidden') === null) {
        return true;
    }

    // Verificar por URL parameters
    const urlParams = new URLSearchParams(window.location.search);
    const paramEnfermeria = urlParams.get('enfermeria');
    if (paramEnfermeria === '1') {
        return true;
    }

    return false;
}

// FUNCIÓN: Continuar atención existente
function continuarAtencion(are_codigo, esForzadoEnfermeria = false) {
    if (!are_codigo) {
        mostrarError('Código de atención no válido');
        return;
    }


    $.ajax({
        url: window.ESPECIALIDADES_URLS.verificarDisponibilidad + '/' + are_codigo,
        method: "GET",
        dataType: "json",
        success: function (response) {
            if (response.disponible) {
                // Puede acceder directamente - agregar parámetro enfermería si es necesario
                let url = window.ESPECIALIDADES_URLS.formulario + '/' + are_codigo;

                // Detectar si estamos en contexto de enfermería o si está forzado
                const esContextoEnfermeria = esForzadoEnfermeria || detectarContextoEnfermeria();

                if (esContextoEnfermeria) {
                    url += '?enfermeria=1';
                }

                window.open(url, '_blank');
            } else {
                // Detectar si estamos en contexto de enfermería para mostrar el modal correcto
                const esContextoEnfermeria = esForzadoEnfermeria || detectarContextoEnfermeria();

                if (esContextoEnfermeria) {
                    // Mostrar modal de validación para enfermería
                    const mensajeEnfermeria = "👩‍⚕️ ATENCIÓN DE ENFERMERÍA EN CURSO\n\n" +
                        response.mensaje + "\n\n" +
                        "Para continuar, debe ingresar la contraseña del enfermero que tiene asignada esta atención.";
                    mostrarModalValidacionEnfermeria(are_codigo, mensajeEnfermeria);
                } else {
                    // Mostrar modal de validación para atención normal (médicos)
                    const mensajeAtencion = "👨‍⚕️ ATENCIÓN EN CURSO\n\n" +
                        response.mensaje + "\n\n" +
                        "Para continuar, debe ingresar la contraseña del médico que tiene asignada esta atención.";
                    mostrarModalValidacion(are_codigo, mensajeAtencion);
                }
            }
        },
        error: function (xhr, status, error) {
            mostrarError('Error al verificar disponibilidad: ' + error);
        }
    });
}

function confirmarTomarConCredenciales() {
    const are_codigo = $('#are_codigo_tomar').val();
    const usuario = $('#usuario_tomar').val().trim();
    const password = $('#password_tomar').val();

    if (!usuario) {
        Swal.fire({
            icon: 'warning',
            title: 'Campo requerido',
            text: 'Ingrese su usuario',
            confirmButtonText: 'Aceptar'
        }).then(() => {
            $('#usuario_tomar').focus();
        });
        return;
    }

    if (!password) {
        Swal.fire({
            icon: 'warning',
            title: 'Campo requerido',
            text: 'Ingrese su contraseña',
            confirmButtonText: 'Aceptar'
        }).then(() => {
            $('#password_tomar').focus();
        });
        return;
    }

    // Determinar URL según el contexto (médico o enfermería)
    const url = window.contextoEnfermeria ?
        window.ESPECIALIDADES_URLS.tomarAtencionEnfermeria :
        window.ESPECIALIDADES_URLS.tomarAtencionConCredenciales;

    $.ajax({
        url: url,
        method: "POST",
        data: {
            are_codigo: are_codigo,
            usuario: usuario,
            password: password
        },
        dataType: "json",
        success: function (response) {
            if (response.success) {
                cerrarModalTomar();

                if (response.redirect_url) {
                    // Agregar parámetro enfermería si es necesario
                    let url = response.redirect_url;
              
                    if (window.contextoEnfermeria) {
                        // Agregar parámetro enfermeria=1 a la URL
                        url += (url.includes('?') ? '&' : '?') + 'enfermeria=1';
                    }
                    window.open(url, '_blank');
                }

                // Resetear contexto después de usar la URL
                window.contextoEnfermeria = false;
                mostrarNotificacion(response.message, 'success');

                // Refrescar lista
                setTimeout(function () {
                    const tabActivo = document.querySelector('.tab-button.border-green-500');
                    if (tabActivo) {
                        const espCodigo = tabActivo.dataset.especialidad;
                        cargarPacientesEspecialidad(espCodigo);
                        // También refrescar datos de enfermería
                        actualizarContadoresEspecialidad(espCodigo);
                    }
                    // También actualizar todos los contadores
                    actualizarTodosLosContadores();
                }, 1000);
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: response.error,
                    confirmButtonText: 'Aceptar'
                });
            }
        },
        error: function (xhr, status, error) {
            Swal.fire({
                icon: 'error',
                title: 'Error de comunicación',
                text: 'No se pudo conectar con el servidor',
                confirmButtonText: 'Aceptar'
            });
        }
    });
}


// FUNCIÓN: Mostrar modal de validación de contraseña

function mostrarModalTomar() {
    document.getElementById('modalTomarAtencion').classList.remove('hidden');
    document.getElementById('modalTomarAtencion').classList.add('flex');
}

function cerrarModalTomar() {
    document.getElementById('modalTomarAtencion').classList.add('hidden');
    document.getElementById('modalTomarAtencion').classList.remove('flex');
    $('#usuario_tomar').val('');
    $('#password_tomar').val('');
}

function mostrarModalValidacion(are_codigo, mensaje) {
    // Convertir saltos de línea en HTML
    const mensajeHTML = mensaje.replace(/\n/g, '<br>');

    $('#mensaje-medico-actual').html(mensajeHTML);
    $('#are_codigo_validar').val(are_codigo);

    // Limpiar campo de contraseña
    $('#password_validar').val('');

    document.getElementById('modalValidarContrasena').classList.remove('hidden');
    document.getElementById('modalValidarContrasena').classList.add('flex');

    // Hacer focus en el campo de contraseña
    setTimeout(() => {
        $('#password_validar').focus();
    }, 300);
}

function cerrarModalValidar() {
    document.getElementById('modalValidarContrasena').classList.add('hidden');
    document.getElementById('modalValidarContrasena').classList.remove('flex');
    $('#password_validar').val('');
}

function validarYContinuar() {
    const are_codigo = $('#are_codigo_validar').val();
    const password = $('#password_validar').val();

    if (!password) {
        Swal.fire({
            icon: 'warning',
            title: 'Campo requerido',
            text: 'Ingrese su contraseña',
            confirmButtonText: 'Aceptar'
        }).then(() => {
            $('#password_validar').focus();
        });
        return;
    }

    // Deshabilitar botón temporalmente
    const submitBtn = document.querySelector('#modalValidarContrasena button[onclick="validarYContinuar()"]');
    const originalText = submitBtn.innerHTML;
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i> Validando...';

    // Determinar URL según contexto (para enfermería usar validarAccesoEnfermeria)
    const url = window.contextoValidacionEnfermeria ?
        window.ESPECIALIDADES_URLS.validarAccesoEnfermeria :
        window.ESPECIALIDADES_URLS.validarContrasena;


    $.ajax({
        url: url,
        method: "POST",
        data: {
            are_codigo: are_codigo,
            password: password
        },
        dataType: "json",
        success: function (response) {
            if (response.success) {
                cerrarModalValidar();

                // Mensaje de éxito diferenciado
                const tipoValidacion = response.tipo_validacion || 'ATENCION_NORMAL';
                let mensajeExito;

                if (tipoValidacion === 'MODIFICACION') {
                    mensajeExito = '🔄 Validación exitosa para modificación';
                } else {
                    mensajeExito = '✅ Validación exitosa';
                }

                mostrarNotificacion(mensajeExito, 'success');

                // Abrir formulario - agregar parámetro enfermería si es necesario
                let url = response.redirect_url;

                if (window.contextoValidacionEnfermeria && !url.includes('enfermeria=1')) {
                    // Solo agregar parámetro enfermeria=1 si no existe ya
                    url += (url.includes('?') ? '&' : '?') + 'enfermeria=1';
                } else if (url.includes('enfermeria=1')) {
                }
                window.open(url, '_blank');

                // Resetear contextos después de usar la URL
                if (window.contextoValidacionEnfermeria) {
                    window.contextoValidacionEnfermeria = false;
                }

                // Refrescar lista después de un momento
                setTimeout(function () {
                    const tabActivo = document.querySelector('.tab-button.border-green-500');
                    if (tabActivo) {
                        const espCodigo = tabActivo.dataset.especialidad;
                        cargarPacientesEspecialidad(espCodigo);
                        // También refrescar datos de enfermería
                        actualizarContadoresEspecialidad(espCodigo);
                    }
                    actualizarTodosLosContadores();
                }, 2000);

            } else {
                // Error de validación
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: response.error,
                    confirmButtonText: 'Aceptar'
                }).then(() => {
                    $('#password_validar').val('').focus();
                });
            }
        },
        error: function (xhr, status, error) {
            console.error('Error en validación:', error);
            Swal.fire({
                icon: 'error',
                title: 'Error de comunicación',
                text: 'No se pudo conectar con el servidor',
                confirmButtonText: 'Aceptar'
            }).then(() => {
                $('#password_validar').val('').focus();
            });
        },
        complete: function () {
            // Restaurar botón siempre
            submitBtn.disabled = false;
            submitBtn.innerHTML = originalText;
        }
    });
}
// FUNCIONES DE UTILIDAD
function formatearHora(hora) {
    if (!hora) return 'No registrada';

    // Si viene con fecha completa, extraer solo la hora
    if (hora.includes(' ')) {
        hora = hora.split(' ')[1];
    }

    return hora.substring(0, 5); // HH:MM
}

function formatearFecha(fecha) {
    if (!fecha) return 'No registrada';

    // Si viene con hora, extraer solo la fecha
    if (fecha.includes(' ')) {
        fecha = fecha.split(' ')[0];
    }

    // Convertir de YYYY-MM-DD a DD/MM/YYYY
    const partes = fecha.split('-');
    if (partes.length === 3) {
        return `${partes[2]}/${partes[1]}/${partes[0]}`;
    }

    return fecha;
}
$(document).ready(function () {
    $('#password_validar').on('keypress', function (e) {
        if (e.which === 13) { // Enter key
            e.preventDefault();
            validarYContinuar();
        }
    });
});
function calcularTiempoTranscurrido(horaInicio) {
    if (!horaInicio) return '';

    try {
        const ahora = new Date();
        const inicio = new Date();

        // Si solo tenemos hora, usar la fecha actual
        if (!horaInicio.includes(' ')) {
            const [horas, minutos] = horaInicio.split(':');
            inicio.setHours(parseInt(horas), parseInt(minutos), 0, 0);
        } else {
            inicio = new Date(horaInicio);
        }

        const diffMs = ahora - inicio;
        const diffMins = Math.floor(diffMs / 60000);

        if (diffMins < 60) {
            return `${diffMins} min`;
        } else {
            const hours = Math.floor(diffMins / 60);
            const mins = diffMins % 60;
            return `${hours}h ${mins}m`;
        }
    } catch (e) {
        return '';
    }
}

// FUNCIÓN: Sistema centralizado de colores de triaje
function determinarColorTriaje(color, tipo = 'badge') {
    if (!color) {
        return '<span class="inline-block bg-gray-100 text-gray-800 px-2 py-1 rounded-full text-xs font-semibold">Sin triaje</span>';
    }

    const configuracionColores = {
        'ROJO': {
            badge: 'bg-red-100 text-red-800 border-red-200',
            bg: 'bg-red-500',
            text: '🔴 ROJO'
        },
        'NARANJA': {
            badge: 'bg-orange-100 text-orange-800 border-orange-200',
            bg: 'bg-orange-500',
            text: '🟠 NARANJA'
        },
        'AMARILLO': {
            badge: 'bg-yellow-100 text-yellow-800 border-yellow-200',
            bg: 'bg-yellow-500',
            text: '🟡 AMARILLO'
        },
        'VERDE': {
            badge: 'bg-green-100 text-green-800 border-green-200',
            bg: 'bg-green-500',
            text: '🟢 VERDE'
        },
        'AZUL': {
            badge: 'bg-blue-100 text-blue-800 border-blue-200',
            bg: 'bg-blue-500',
            text: '🔵 AZUL'
        }
    };

    const colorConfig = configuracionColores[color.toUpperCase()] || {
        badge: 'bg-gray-100 text-gray-800',
        bg: 'bg-gray-500',
        text: color
    };

    if (tipo === 'badge') {
        return `<span class="inline-block ${colorConfig.badge} px-2 py-1 rounded-full text-xs font-semibold border">${colorConfig.text}</span>`;
    } else {
        return colorConfig;
    }
}

// FUNCIÓN: Mostrar errores
function mostrarError(mensaje) {
    console.error('Error en especialidades:', mensaje);

    Swal.fire({
        icon: 'error',
        title: 'Error',
        text: mensaje,
        confirmButtonText: 'Aceptar'
    });
}

// FUNCIONES REUTILIZADAS DEL SISTEMA PRINCIPAL (mantener compatibilidad)
function refrescarEspecialidad(esp_codigo) {
    // Refrescar la vista principal de pacientes
    cargarPacientesEspecialidad(esp_codigo);

    // Actualizar contadores unificadamente
    actualizarContadoresEspecialidad(esp_codigo);
}

function mostrarNotificacion(mensaje, tipo = 'info') {
    // Crear notificación temporal
    const notificacion = document.createElement('div');
    notificacion.className = `fixed top-4 right-4 z-50 px-4 py-3 rounded-lg shadow-lg transition-all duration-300 transform translate-x-full`;

    // Colores según tipo
    const colores = {
        'success': 'bg-green-500 text-white',
        'error': 'bg-red-500 text-white',
        'info': 'bg-blue-500 text-white',
        'warning': 'bg-yellow-500 text-black'
    };

    notificacion.className += ` ${colores[tipo] || colores.info}`;
    notificacion.innerHTML = `
        <div class="flex items-center">
            <i class="fas fa-${tipo === 'success' ? 'check-circle' : tipo === 'error' ? 'exclamation-circle' : 'info-circle'} mr-2"></i>
            <span>${mensaje}</span>
        </div>
    `;

    document.body.appendChild(notificacion);

    // Animar entrada
    setTimeout(() => {
        notificacion.classList.remove('translate-x-full');
    }, 100);

    // Auto-remover después de 4 segundos
    setTimeout(() => {
        notificacion.classList.add('translate-x-full');
        setTimeout(() => {
            if (notificacion.parentNode) {
                notificacion.parentNode.removeChild(notificacion);
            }
        }, 300);
    }, 4000);
}

// ========================================
// NAVEGACIÓN ENTRE MÉDICOS Y ENFERMEROS
// ========================================

// Variable global para rastrear la última vista activa del usuario
window.ULTIMA_VISTA_ACTIVA = 'medicos'; // Por defecto médicos

/**
 * Detectar qué tipo de personal está activo actualmente
 */
function detectarTipoPersonalActivo() {
    // USAR LA ÚLTIMA VISTA ACTIVA REGISTRADA POR EL USUARIO
    return window.ULTIMA_VISTA_ACTIVA || 'medicos';
}


/**
 * Cambiar entre vista de médicos y enfermeros
 */
function cambiarTipoPersonal(espCodigo, tipo, botonClickeado) {
  

    // REGISTRAR LA ÚLTIMA VISTA ACTIVA DEL USUARIO
    window.ULTIMA_VISTA_ACTIVA = tipo;

    // Remover clase active de todos los botones de esta especialidad
    const botones = document.querySelectorAll(`[data-especialidad="${espCodigo}"].tipo-personal-btn`);
    botones.forEach(btn => {
        btn.classList.remove('active', 'text-gray-800', 'bg-gray-200', 'font-semibold', 'shadow-sm');
        btn.classList.add('text-gray-600', 'bg-transparent');
    });

    // Activar el botón clickeado
    botonClickeado.classList.add('active', 'text-gray-800', 'bg-gray-200', 'font-semibold', 'shadow-sm');
    botonClickeado.classList.remove('text-gray-600', 'bg-transparent');

    // Ocultar todos los contenidos
    const contenidos = document.querySelectorAll(`[id^="content-medicos-${espCodigo}"], [id^="content-enfermeros-${espCodigo}"]`);
    contenidos.forEach(contenido => contenido.classList.add('hidden'));

    // Mostrar el contenido correspondiente
    const contenidoTarget = document.getElementById(`content-${tipo}-${espCodigo}`);
    if (contenidoTarget) {
        contenidoTarget.classList.remove('hidden');
    }


    // Cargar datos específicos según el tipo de personal
    if (tipo === 'enfermeros') {
        // Cargar datos específicos de enfermería
        cargarDatosEnfermeriaCompletos(espCodigo);
    } else if (tipo === 'medicos') {
        // Solo actualizar contadores para médicos (los datos ya se cargan con cargarPacientesEspecialidad)
        actualizarContadoresEspecialidad(espCodigo);
    }
}

// FUNCIÓN: Cargar datos completos de enfermería (contadores + tablas)
function cargarDatosEnfermeriaCompletos(espCodigo) {
    // Mostrar loading en las tablas de enfermería
    const tablasEnfermeria = [
        `enfermeria-asignados-${espCodigo}`,
        `enfermeria-pendientes-${espCodigo}`
    ];

    tablasEnfermeria.forEach(tablaId => {
        const tbody = document.getElementById(tablaId);
        if (tbody) {
            tbody.innerHTML = '<tr><td colspan="4" class="text-center py-8 text-blue-500"><i class="fas fa-spinner fa-spin mr-2"></i>Cargando datos de enfermería...</td></tr>';
        }
    });

    // Cargar datos de enfermería desde el servidor
    fetch(`${window.base_url}especialidades/obtenerPacientesEnfermeria/${espCodigo}`)
        .then(response => response.json())
        .then(data => {
            if (data.success && data.data) {
                // Actualizar contadores
                const totalAsignados = data.data.asignados?.length || 0;
                const totalPendientes = data.data.pendientes?.length || 0;
                const totalEnfermeros = totalAsignados + totalPendientes;

                // Actualizar contador de enfermeros
                const contadorEnfermeros = document.getElementById(`count-enfermeros-${espCodigo}`);
                if (contadorEnfermeros) {
                    contadorEnfermeros.textContent = totalEnfermeros;
                }

                // Actualizar contadores internos
                const contadorAsignados = document.getElementById(`count-enfermeria-asignados-${espCodigo}`);
                if (contadorAsignados) {
                    contadorAsignados.textContent = totalAsignados;
                }

                const contadorPendientes = document.getElementById(`count-enfermeria-pendientes-${espCodigo}`);
                if (contadorPendientes) {
                    contadorPendientes.textContent = totalPendientes;
                }

                // Llenar las tablas con los datos
                llenarTablaEnfermeriaAsignados(espCodigo, data.data.asignados || []);
                llenarTablaEnfermeriaPendientes(espCodigo, data.data.pendientes || []);

            } else {
                console.error('❌ Error cargando datos de enfermería:', data.error || 'Respuesta inválida');
                mostrarErrorEnfermeria(espCodigo, data.error || 'Error al cargar datos de enfermería');
            }
        })
        .catch(error => {
            console.error('❌ Error en fetch de enfermería:', error);
            mostrarErrorEnfermeria(espCodigo, 'Error de conexión al cargar datos de enfermería');
        });
}

function mostrarDatosEnfermeria(espCodigo, datos) {

    // Mostrar pacientes asignados (en atención por enfermería)
    const asignadosBody = document.getElementById(`enfermeria-asignados-${espCodigo}`);
    if (asignadosBody && datos.asignados) {
        if (datos.asignados.length === 0) {
            asignadosBody.innerHTML = `
                <tr>
                    <td colspan="4" class="text-center py-8 text-gray-500">
                        <i class="fas fa-inbox mr-2"></i>
                        No hay pacientes asignados
                    </td>
                </tr>
            `;
        } else {
            asignadosBody.innerHTML = datos.asignados.map(paciente => `
                <tr class="hover:bg-blue-50">
                    <td class="px-4 py-3 border-b">
                        <div class="font-medium text-gray-900">${paciente.pac_nombres}</div>
                        <div class="text-sm text-gray-500">${paciente.pac_documento}</div>
                        <div class="text-xs text-gray-400">${paciente.pac_edad} - ${paciente.pac_sexo}</div>
                    </td>
                    <td class="px-4 py-3 border-b">
                        <div class="text-sm text-gray-900">${paciente.enfermero_asignado || 'Sin asignar'}</div>
                    </td>
                    <td class="px-4 py-3 border-b">
                        <div class="text-sm text-gray-900">${paciente.fecha_envio || 'N/A'}</div>
                        <div class="text-xs text-gray-500">${paciente.hora_envio || ''}</div>
                    </td>
                    <td class="px-4 py-3 border-b">
                        <button class="bg-blue-500 hover:bg-blue-600 text-white px-3 py-1 rounded text-xs"
                                onclick="continuarAtencion('${paciente.are_codigo}', true)">
                            Continuar
                        </button>
                    </td>
                </tr>
            `).join('');
        }
    }

    // Mostrar pacientes pendientes (enviados por médicos, esperando ser tomados)
    const pendientesBody = document.getElementById(`enfermeria-pendientes-${espCodigo}`);
    if (pendientesBody && datos.pendientes) {
        if (datos.pendientes.length === 0) {
            pendientesBody.innerHTML = `
                <tr>
                    <td colspan="4" class="text-center py-8 text-gray-500">
                        <i class="fas fa-inbox mr-2"></i>
                        No hay pacientes pendientes
                    </td>
                </tr>
            `;
        } else {
            pendientesBody.innerHTML = datos.pendientes.map(paciente => `
                <tr class="hover:bg-green-50">
                    <td class="px-4 py-3 border-b">
                        <div class="font-medium text-gray-900">${paciente.pac_nombres}</div>
                        <div class="text-sm text-gray-500">${paciente.pac_documento}</div>
                        <div class="text-xs text-gray-400">${paciente.pac_edad} - ${paciente.pac_sexo}</div>
                    </td>
                    <td class="px-4 py-3 border-b">
                        <div class="text-sm text-gray-900">${paciente.medico_que_envio}</div>
                        <div class="text-xs text-gray-500">${paciente.especialidad}</div>
                    </td>
                    <td class="px-4 py-3 border-b">
                        <div class="text-sm text-gray-900">${paciente.fecha_envio || 'Hoy'}</div>
                        <div class="text-xs text-gray-500">${paciente.hora_envio || ''}</div>
                    </td>
                    <td class="px-4 py-3 border-b">
                        <button class="bg-green-500 hover:bg-green-600 text-white px-3 py-1 rounded text-xs"
                                onclick="mostrarModalTomarEnfermeria('${paciente.are_codigo}')">
                            Tomar
                        </button>
                    </td>
                </tr>
            `).join('');
        }
    }
}
// Función para tomar atención de enfermería
function tomarAtencionEnfermeria(are_codigo) {
    if (!are_codigo) {
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: 'Código de área no válido',
            confirmButtonText: 'Aceptar'
        });
        return;
    }

    Swal.fire({
        icon: 'question',
        title: 'Tomar Atención de Enfermería',
        html: '✅ Se asignará este paciente a su cuidado<br>' +
              '📋 Podrá acceder al formulario médico completo<br>' +
              '🔄 El paciente pasará de "Pendiente" a "En Atención"<br><br>' +
              '¿Desea continuar?',
        showCancelButton: true,
        confirmButtonText: 'Sí, tomar atención',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            // Llamar al método para tomar la atención
            fetch(`${window.base_url}especialidades/tomarAtencionEnfermeria`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify({
                    are_codigo: are_codigo
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Éxito',
                        text: 'Atención tomada exitosamente',
                        confirmButtonText: 'Aceptar'
                    }).then(() => {
                        // Abrir el formulario directamente
                        window.location.href = `${window.base_url}especialidades/formulario/${are_codigo}?enfermeria=1`;
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: data.message || 'No se pudo tomar la atención',
                        confirmButtonText: 'Aceptar'
                    });
                }
            })
            .catch(error => {
                console.error('Error:', error);
                Swal.fire({
                    icon: 'error',
                    title: 'Error de conexión',
                    text: 'Error de conexión al tomar la atención',
                    confirmButtonText: 'Aceptar'
                });
            });
        }
    });
}

// Función abrirFormularioEnfermeria() eliminada
// Ahora se usa continuarAtencion() que maneja la validación automáticamente
function mostrarErrorEnfermeria(espCodigo, error) {
    const mensaje = `
        <tr>
            <td colspan="4" class="text-center py-8 text-red-500">
                <i class="fas fa-exclamation-triangle mr-2"></i>
                ${error}
            </td>
        </tr>
    `;

    const tablas = [
        `enfermeria-asignados-${espCodigo}`,
        `enfermeria-pendientes-${espCodigo}`
    ];

    tablas.forEach(tablaId => {
        const tbody = document.getElementById(tablaId);
        if (tbody) {
            tbody.innerHTML = mensaje;
        }
    });
}
/**
 * Mantener la especialidad activa visualmente marcada
 */
function mantenerEspecialidadActivaMarcada() {
    // Remover marca de todas las especialidades
    document.querySelectorAll('.tab-button').forEach(btn => {
        btn.classList.remove('border-green-500', 'text-green-600');
        btn.classList.add('border-transparent', 'text-gray-500');
    });

    // Marcar la especialidad activa
    if (window.ESTADO_ESPECIALIDADES.especialidadActiva) {
        const tabActivo = document.getElementById(`tab-esp-${window.ESTADO_ESPECIALIDADES.especialidadActiva}`);
        if (tabActivo) {
            tabActivo.classList.remove('border-transparent', 'text-gray-500');
            tabActivo.classList.add('border-green-500', 'text-green-600');
        }
    }
}


/**
 * Cargar datos de enfermeros
 */
function cargarDatosEnfermeros(especialidadCodigo) {
    limpiarTablasEnfermeria(especialidadCodigo);

    $.ajax({
        url: window.base_url + 'especialidades/obtenerPacientesEnfermeria/' + especialidadCodigo,
        method: 'GET',
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                actualizarTablasEnfermeria(especialidadCodigo, response.data);
            } else {
                mostrarErrorEnfermeria(especialidadCodigo, response.error || 'Error al cargar datos de enfermería');
            }
        },
        error: function() {
            mostrarErrorEnfermeria(especialidadCodigo, 'Error de conexión');
        }
    });
}

/**
 * Limpiar tablas de enfermería
 */
function limpiarTablasEnfermeria(especialidadCodigo) {
    const tablas = [
        'enfermeria-asignados',
        'enfermeria-pendientes'
    ];

    tablas.forEach(tabla => {
        const tbody = document.getElementById(`${tabla}-${especialidadCodigo}`);
        if (tbody) {
            tbody.innerHTML = '<tr><td colspan="4" class="text-center py-8 text-gray-500"><i class="fas fa-spinner fa-spin mr-2"></i>Cargando...</td></tr>';
        }
    });
}

/**
 * Actualizar tablas de enfermería con datos
 */
function actualizarTablasEnfermeria(especialidadCodigo, data) {
    // Actualizar contadores
    actualizarContadorEnfermeria('asignados', especialidadCodigo, data.asignados?.length || 0);
    actualizarContadorEnfermeria('pendientes', especialidadCodigo, data.pendientes?.length || 0);
    actualizarContadorEnfermeria('especiales', especialidadCodigo, data.especiales?.length || 0);
    actualizarContadorEnfermeria('seguimiento', especialidadCodigo, data.seguimiento?.length || 0);

    // Actualizar contador total de enfermeros
    const totalEnfermeros = (data.asignados?.length || 0) + (data.pendientes?.length || 0);
    actualizarContadorEnfermeria('total', especialidadCodigo, totalEnfermeros);

    // SOLO actualizar el contador general si la vista de enfermeros está activa
    const btnEnfermerosActivo = document.getElementById(`btn-enfermeros-${especialidadCodigo}`)?.classList.contains('active');
    if (btnEnfermerosActivo) {
        const tab = $(`#tab-esp-${especialidadCodigo} span`);
        if (tab.length) {
            tab.text(totalEnfermeros);
            tab[0].className = totalEnfermeros > 0 ?
                'bg-red-500 text-white text-xs rounded-full px-2 py-1 ml-2' :
                'bg-gray-400 text-white text-xs rounded-full px-2 py-1 ml-2';
        }
    }

    // Llenar tablas
    llenarTablaEnfermeriaAsignados(especialidadCodigo, data.asignados || []);
    llenarTablaEnfermeriaPendientes(especialidadCodigo, data.pendientes || []);
}

/**
 * Actualizar contador específico de enfermería
 */
function actualizarContadorEnfermeria(tipo, especialidadCodigo, cantidad) {
    let counterId;
    if (tipo === 'total') {
        counterId = `count-enfermeros-${especialidadCodigo}`;
    } else {
        counterId = `count-enfermeria-${tipo}-${especialidadCodigo}`;
    }

    const contador = document.getElementById(counterId);
    if (contador) {
        contador.textContent = cantidad;
    }
}


/**
 * Procesar datos de médicos recibidos
 */
function procesarDatosMedicos(especialidadCodigo, data) {

    // Extraer datos correctos del endpoint
    const datosMedicos = data.data || {};
    const pendientes = datosMedicos.pendientes || [];
    const enAtencion = datosMedicos.en_atencion || [];
    const enProceso = datosMedicos.en_proceso || [];
    const continuandoProceso = datosMedicos.continuando_proceso || [];

    // Actualizar contador total de médicos
    const totalMedicos = pendientes.length + enAtencion.length + enProceso.length + continuandoProceso.length;
    actualizarContadorMedicos('total', especialidadCodigo, totalMedicos);

    // SOLO actualizar el contador general si la vista de médicos está activa
    const btnMedicosActivo = document.getElementById(`btn-medicos-${especialidadCodigo}`)?.classList.contains('active');
    if (btnMedicosActivo) {
        const tab = $(`#tab-esp-${especialidadCodigo} span`);
        if (tab.length) {
            tab.text(totalMedicos);
            tab[0].className = totalMedicos > 0 ?
                'bg-red-500 text-white text-xs rounded-full px-2 py-1 ml-2' :
                'bg-gray-400 text-white text-xs rounded-full px-2 py-1 ml-2';
        }
    }

    // Llenar tablas
    llenarTablaMedicosPendientes(especialidadCodigo, pendientes);
    llenarTablaMedicosAtencion(especialidadCodigo, enAtencion);
    llenarTablaMedicosProceso(especialidadCodigo, enProceso);
}

/**
 * Actualizar contador específico de médicos
 */
function actualizarContadorMedicos(tipo, especialidadCodigo, cantidad) {
    let counterId;
    if (tipo === 'total') {
        counterId = `count-medicos-${especialidadCodigo}`;
    } else {
        counterId = `count-medicos-${tipo}-${especialidadCodigo}`;
    }

    const contador = document.getElementById(counterId);
    if (contador) {
        contador.textContent = cantidad;
    }
}

/**
 * Mostrar error en tablas de médicos
 */
function mostrarErrorMedicos(especialidadCodigo, mensaje) {
    const tablas = [
        'medicos-pendientes',
        'medicos-atencion',
        'medicos-proceso'
    ];

    tablas.forEach(tabla => {
        const tbody = document.getElementById(`${tabla}-${especialidadCodigo}`);
        if (tbody) {
            tbody.innerHTML = `<tr><td colspan="4" class="text-center py-8 text-red-500"><i class="fas fa-exclamation-triangle mr-2"></i>${mensaje}</td></tr>`;
        }
    });
}

/**
 * Llenar tabla de médicos pendientes
 */
function llenarTablaMedicosPendientes(especialidadCodigo, datos) {
    const tbody = document.getElementById(`medicos-pendientes-${especialidadCodigo}`);
    if (!tbody) {
        return;
    }

    if (datos.length === 0) {
        tbody.innerHTML = '<tr><td colspan="4" class="text-center py-8 text-gray-500"><i class="fas fa-inbox mr-2"></i>No hay médicos pendientes</td></tr>';
        return;
    }

    // Implementar el HTML para médicos pendientes similar a enfermería
    // Por ahora, mostrar datos básicos
    tbody.innerHTML = datos.map(medico => `
        <tr class="hover:bg-gray-50">
            <td class="px-6 py-4">${medico.pac_nombres || ''} ${medico.pac_apellidos || ''}</td>
            <td class="px-6 py-4">${medico.pac_cedula || ''}</td>
            <td class="px-6 py-4">${medico.are_estado || ''}</td>
            <td class="px-6 py-4">
                <button class="bg-blue-500 text-white px-3 py-1 rounded text-sm">
                    Tomar Atención
                </button>
            </td>
        </tr>
    `).join('');
}

/**
 * Llenar tabla de médicos en atención
 */
function llenarTablaMedicosAtencion(especialidadCodigo, datos) {
    const tbody = document.getElementById(`medicos-atencion-${especialidadCodigo}`);
    if (!tbody) return;

    if (datos.length === 0) {
        tbody.innerHTML = '<tr><td colspan="4" class="text-center py-8 text-gray-500"><i class="fas fa-inbox mr-2"></i>No hay médicos en atención</td></tr>';
        return;
    }

    tbody.innerHTML = datos.map(medico => `
        <tr class="hover:bg-gray-50">
            <td class="px-6 py-4">${medico.pac_nombres || ''} ${medico.pac_apellidos || ''}</td>
            <td class="px-6 py-4">${medico.pac_cedula || ''}</td>
            <td class="px-6 py-4">${medico.are_estado || ''}</td>
            <td class="px-6 py-4">
                <button class="bg-green-500 text-white px-3 py-1 rounded text-sm">
                    Continuar
                </button>
            </td>
        </tr>
    `).join('');
}

/**
 * Llenar tabla de médicos proceso
 */
function llenarTablaMedicosProceso(especialidadCodigo, datos) {
    const tbody = document.getElementById(`medicos-proceso-${especialidadCodigo}`);
    if (!tbody) return;

    if (datos.length === 0) {
        tbody.innerHTML = '<tr><td colspan="4" class="text-center py-8 text-gray-500"><i class="fas fa-inbox mr-2"></i>No hay médicos en proceso</td></tr>';
        return;
    }

    tbody.innerHTML = datos.map(medico => `
        <tr class="hover:bg-gray-50">
            <td class="px-6 py-4">${medico.pac_nombres || ''} ${medico.pac_apellidos || ''}</td>
            <td class="px-6 py-4">${medico.pac_cedula || ''}</td>
            <td class="px-6 py-4">${medico.are_estado || ''}</td>
            <td class="px-6 py-4">
                <button class="bg-yellow-500 text-white px-3 py-1 rounded text-sm">
                    Ver Proceso
                </button>
            </td>
        </tr>
    `).join('');
}


/**
 * Llenar tabla de enfermería asignados
 */
function llenarTablaEnfermeriaAsignados(especialidadCodigo, datos) {
    const tbody = document.getElementById(`enfermeria-asignados-${especialidadCodigo}`);
    if (!tbody) return;

    if (datos.length === 0) {
        tbody.innerHTML = '<tr><td colspan="4" class="text-center py-8 text-gray-500">No hay pacientes asignados</td></tr>';
        return;
    }

    let html = '';
    datos.forEach(paciente => {
        html += `
            <tr class="hover:bg-gray-50">
                <td class="px-4 py-3 text-sm">
                    <div class="font-medium text-gray-900">${paciente.pac_nombres}</div>
                    <div class="text-xs text-gray-500">${paciente.pac_documento}</div>
                    <div class="text-xs text-gray-500">Edad: ${paciente.pac_edad}</div>
                </td>
                <td class="px-4 py-3 text-sm text-gray-600">
                    <div class="text-sm">${paciente.enfermero_asignado || 'Sin asignar'}</div>
                    <div class="text-xs text-gray-500">${paciente.especialidad}</div>
                </td>
                <td class="px-4 py-3 text-sm text-gray-600">
                    <div class="text-sm">${paciente.fecha_envio}</div>
                    <div class="text-xs text-gray-500">${paciente.hora_envio}</div>
                </td>
                <td class="px-4 py-3 text-sm">
                    <div class="flex space-x-2">
                        <button onclick="mostrarModalValidacionEnfermeria('${paciente.are_codigo}', 'Validación requerida para acceder al formulario.\\nSolo el enfermero asignado puede continuar.')"
                                class="bg-blue-500 hover:bg-blue-600 text-white px-3 py-1 rounded text-xs font-medium transition-colors">
                            <i class="fas fa-eye mr-1"></i>
                            Ver Formulario
                        </button>
                    </div>
                </td>
            </tr>
        `;
    });
    tbody.innerHTML = html;
}

/**
 * Llenar tabla de enfermería pendientes
 */
function llenarTablaEnfermeriaPendientes(especialidadCodigo, datos) {
    const tbody = document.getElementById(`enfermeria-pendientes-${especialidadCodigo}`);
    if (!tbody) return;

    if (datos.length === 0) {
        tbody.innerHTML = '<tr><td colspan="4" class="text-center py-8 text-gray-500">No hay pacientes pendientes</td></tr>';
        return;
    }

    let html = '';
    datos.forEach(paciente => {
        html += `
            <tr class="hover:bg-gray-50">
                <td class="px-4 py-3 text-sm">
                    <div class="font-medium text-gray-900">${paciente.pac_nombres}</div>
                    <div class="text-xs text-gray-500">${paciente.pac_documento}</div>
                    <div class="text-xs text-gray-500">Edad: ${paciente.pac_edad}</div>
                </td>
                <td class="px-4 py-3 text-sm text-gray-600">
                    <div class="text-sm">${paciente.medico_que_envio}</div>
                    <div class="text-xs text-gray-500">${paciente.especialidad}</div>
                </td>
                <td class="px-4 py-3 text-sm text-gray-600">
                    <div class="text-sm">${paciente.fecha_envio}</div>
                    <div class="text-xs text-gray-500">${paciente.hora_envio}</div>
                </td>
                <td class="px-4 py-3 text-sm">
                    <div class="flex space-x-2">
                        <button onclick="mostrarModalTomarEnfermeria('${paciente.are_codigo}')"
                                class="bg-green-500 hover:bg-green-600 text-white px-3 py-1 rounded text-xs font-medium transition-colors">
                            <i class="fas fa-hand-paper mr-1"></i>
                            Tomar
                        </button>
                    </div>
                </td>
            </tr>
        `;
    });
    tbody.innerHTML = html;
}

/**
 * Llenar tabla de cuidados especiales
 */
function llenarTablaEnfermeriaEspeciales(especialidadCodigo, datos) {
    const tbody = document.getElementById(`enfermeria-especiales-${especialidadCodigo}`);
    if (!tbody) return;

    if (datos.length === 0) {
        tbody.innerHTML = '<tr><td colspan="4" class="text-center py-8 text-gray-500">No hay cuidados especiales</td></tr>';
        return;
    }

    let html = '';
    datos.forEach(paciente => {
        const prioridadClass = paciente.prioridad === 'Alta' ? 'bg-red-100 text-red-800' : 'bg-yellow-100 text-yellow-800';
        html += `
            <tr class="hover:bg-gray-50">
                <td class="px-4 py-3 text-sm">
                    <div class="font-medium text-gray-900">${paciente.pac_nombres} ${paciente.pac_apellidos}</div>
                </td>
                <td class="px-4 py-3 text-sm text-gray-600">${paciente.tipo_cuidado || '-'}</td>
                <td class="px-4 py-3 text-sm">
                    <span class="px-2 py-1 text-xs rounded-full ${prioridadClass}">${paciente.prioridad || '-'}</span>
                </td>
                <td class="px-4 py-3 text-sm">
                    <div class="flex space-x-2">
                        <button class="bg-purple-500 hover:bg-purple-600 text-white px-2 py-1 rounded text-xs">
                            Atender
                        </button>
                    </div>
                </td>
            </tr>
        `;
    });
    tbody.innerHTML = html;
}

/**
 * Llenar tabla de seguimiento
 */
function llenarTablaEnfermeriaSeguimiento(especialidadCodigo, datos) {
    const tbody = document.getElementById(`enfermeria-seguimiento-${especialidadCodigo}`);
    if (!tbody) return;

    if (datos.length === 0) {
        tbody.innerHTML = '<tr><td colspan="4" class="text-center py-8 text-gray-500">No hay seguimientos</td></tr>';
        return;
    }

    let html = '';
    datos.forEach(paciente => {
        html += `
            <tr class="hover:bg-gray-50">
                <td class="px-4 py-3 text-sm">
                    <div class="font-medium text-gray-900">${paciente.pac_nombres} ${paciente.pac_apellidos}</div>
                </td>
                <td class="px-4 py-3 text-sm text-gray-600">${paciente.ultima_nota || '-'}</td>
                <td class="px-4 py-3 text-sm">
                    <span class="px-2 py-1 text-xs rounded-full bg-green-100 text-green-800">${paciente.estado || '-'}</span>
                </td>
                <td class="px-4 py-3 text-sm">
                    <div class="flex space-x-2">
                        <button class="bg-orange-500 hover:bg-orange-600 text-white px-2 py-1 rounded text-xs">
                            Actualizar
                        </button>
                    </div>
                </td>
            </tr>
        `;
    });
    tbody.innerHTML = html;
}

// =====================================================
// FUNCIONES PARA MODALES DE ENFERMERÍA
// =====================================================

/**
 * Mostrar modal para tomar atención en enfermería (reutiliza modal existente)
 */
function mostrarModalTomarEnfermeria(arecodigo) {
    // Usar el modal existente pero marcar el contexto de enfermería
    window.contextoEnfermeria = true;
    $('#are_codigo_tomar').val(arecodigo);
    $('#usuario_tomar').val('');
    $('#password_tomar').val('');

    document.getElementById('modalTomarAtencion').classList.remove('hidden');
    document.getElementById('modalTomarAtencion').classList.add('flex');

    // Hacer focus en el campo de usuario
    setTimeout(() => {
        $('#usuario_tomar').focus();
    }, 300);
}

/**
 * Mostrar modal de validación para enfermería (reutiliza modal existente)
 */
function mostrarModalValidacionEnfermeria(are_codigo, mensaje) {
    // Marcar contexto de validación de enfermería
    window.contextoValidacionEnfermeria = true;

    // Convertir saltos de línea en HTML
    const mensajeHTML = mensaje.replace(/\n/g, '<br>');

    $('#mensaje-medico-actual').html(mensajeHTML);
    $('#are_codigo_validar').val(are_codigo);

    // Limpiar campo de contraseña
    $('#password_validar').val('');

    document.getElementById('modalValidarContrasena').classList.remove('hidden');
    document.getElementById('modalValidarContrasena').classList.add('flex');

    // Hacer focus en el campo de contraseña
    setTimeout(() => {
        $('#password_validar').focus();
    }, 300);
}

/**
 * Mostrar modal para validar contraseña (ver formulario)
 */
function mostrarModalValidarEnfermeria(arecodigo) {
    const modal = `
        <div id="modalValidarEnfermeria" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
            <div class="bg-white rounded-lg p-6 w-full max-w-md mx-4">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-semibold text-gray-900">
                        <i class="fas fa-lock text-blue-600 mr-2"></i>
                        Validar Acceso - Enfermería
                    </h3>
                    <button onclick="cerrarModalValidarEnfermeria()" class="text-gray-400 hover:text-gray-600">
                        <i class="fas fa-times"></i>
                    </button>
                </div>

                <div class="mb-4">
                    <p class="text-sm text-gray-600 mb-4">
                        Ingrese la contraseña del enfermero asignado para continuar:
                    </p>

                    <form id="formValidarEnfermeria" onsubmit="validarAccesoEnfermeria(event, '${arecodigo}')">
                        <div class="mb-6">
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Contraseña:
                            </label>
                            <input type="password"
                                   id="password_validar_enfermeria"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                                   placeholder="Ingrese la contraseña"
                                   required>
                        </div>

                        <div class="flex space-x-3">
                            <button type="button"
                                    onclick="cerrarModalValidarEnfermeria()"
                                    class="flex-1 bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-md transition-colors">
                                Cancelar
                            </button>
                            <button type="submit"
                                    class="flex-1 bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-md transition-colors">
                                <i class="fas fa-eye mr-2"></i>
                                Ver Formulario
                            </button>
                        </div>
                    </form>
                </div>

                <div id="mensajeValidarEnfermeria" class="mt-4 hidden"></div>
            </div>
        </div>
    `;

    document.body.insertAdjacentHTML('beforeend', modal);
    document.getElementById('password_validar_enfermeria').focus();
}

/**
 * Cerrar modal de validar contraseña
 */
function cerrarModalValidarEnfermeria() {
    const modal = document.getElementById('modalValidarEnfermeria');
    if (modal) {
        modal.remove();
    }
}

/**
 * Procesar validación de acceso en enfermería
 */
function validarAccesoEnfermeria(event, arecodigo) {
    event.preventDefault();

    const password = document.getElementById('password_validar_enfermeria').value;
    const mensajeDiv = document.getElementById('mensajeValidarEnfermeria');

    if (!password) {
        mostrarMensajeModal(mensajeDiv, 'La contraseña es obligatoria', 'error');
        return;
    }

    // Deshabilitar botón de envío
    const submitBtn = event.target.querySelector('button[type="submit"]');
    const textoOriginal = submitBtn.innerHTML;
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Validando...';

    // Enviar datos al servidor
    fetch(window.base_url + 'especialidades/validarAccesoEnfermeria', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: JSON.stringify({
            are_codigo: arecodigo,
            password: password
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            mostrarMensajeModal(mensajeDiv, data.message, 'success');

            setTimeout(() => {
                cerrarModalValidarEnfermeria();
                // Redireccionar al formulario
                if (data.redirect_url) {
                    window.location.href = data.redirect_url;
                }
            }, 1000);
        } else {
            mostrarMensajeModal(mensajeDiv, data.message || 'Contraseña incorrecta', 'error');
            submitBtn.disabled = false;
            submitBtn.innerHTML = textoOriginal;
        }
    })
    .catch(error => {
        console.error('Error:', error);
        mostrarMensajeModal(mensajeDiv, 'Error de conexión', 'error');
        submitBtn.disabled = false;
        submitBtn.innerHTML = textoOriginal;
    });
}

/**
 * Mostrar mensaje en modal
 */
function mostrarMensajeModal(elemento, mensaje, tipo) {
    elemento.className = `mt-4 p-3 rounded-md ${tipo === 'success' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'}`;
    elemento.innerHTML = `
        <i class="fas fa-${tipo === 'success' ? 'check-circle' : 'exclamation-triangle'} mr-2"></i>
        ${mensaje}
    `;
    elemento.classList.remove('hidden');
}

/**
 * Obtener la especialidad activa actual
 */
function obtenerEspecialidadActiva() {
    // Buscar el botón activo de especialidad
    const botonActivo = document.querySelector('.boton-especialidad.active');
    return botonActivo ? botonActivo.getAttribute('data-especialidad') : null;
}

// INICIALIZACIÓN AUTOMÁTICA AL CARGAR LA PÁGINA
document.addEventListener('DOMContentLoaded', function() {

    // Esperar a que todas las variables globales estén disponibles
    setTimeout(function() {
        if (window.ESPECIALIDADES_DATA && Array.isArray(window.ESPECIALIDADES_DATA)) {

            // Inicializar contadores para cada especialidad
            window.ESPECIALIDADES_DATA.forEach(function(especialidad) {
                const espCodigo = especialidad.esp_codigo;

                // SIEMPRE cargar datos principales de la especialidad
                cargarPacientesEspecialidad(espCodigo);

                // CARGAR CONTADORES UNIFICADAMENTE
                actualizarContadoresEspecialidad(espCodigo);

            });

        } else {
            console.warn('⚠️ No se encontraron datos de especialidades para inicializar');
        }
    }, 800);
});

// TAMBIÉN inicializar después de que la ventana se cargue completamente
window.addEventListener('load', function() {

    // Verificar si los contadores ya se inicializaron, si no, forzar inicialización
    setTimeout(function() {
        if (window.ESPECIALIDADES_DATA && Array.isArray(window.ESPECIALIDADES_DATA)) {
            window.ESPECIALIDADES_DATA.forEach(function(especialidad) {
                const espCodigo = especialidad.esp_codigo;
                const contadorMedicos = document.getElementById(`count-medicos-${espCodigo}`);
                const contadorEnfermeros = document.getElementById(`count-enfermeros-${espCodigo}`);

                // Si cualquiera de los contadores sigue en 0, forzar actualización de ambos
                if ((contadorMedicos && contadorMedicos.textContent === '0') ||
                    (contadorEnfermeros && contadorEnfermeros.textContent === '0')) {
                    actualizarContadoresEspecialidad(espCodigo);
                }
            });
        }
    }, 1000);
});
