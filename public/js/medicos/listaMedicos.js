// ========================================
// LISTAMÉDICOS.JS - FUNCIONALIDAD CONSOLIDADA
// ========================================

// URLs CORRECTAS para el sistema consolidado de médicos
window.TRIAJE_URLS = {
    // Según Routes.php, la ruta es 'medicos/obtenerPacientes'
    obtenerPacientes: window.base_url + 'medicos/obtenerPacientes',
    tomarAtencionRapida: window.base_url + 'medicos/tomarAtencionRapida',
    asignarEspecialidad: window.base_url + 'medicos/asignarAEspecialidad',
    obtenerEstadisticas: window.base_url + 'medicos/obtenerEstadisticasTriaje'
};

$(document).ready(function () {
    // Verificar que estamos en el contexto correcto
    if (typeof window.contextoMedicoTriaje === 'undefined') {
        return;
    }

    cargarPacientesTriaje();
    cargarEstadisticas();

    // Actualizar cada 30 segundos automáticamente
    setInterval(function () {
        refrescarTodo();
    }, 30000);
});

// FUNCIÓN PRINCIPAL DE REFRESCAR TODO
function refrescarTodo() {

    // Mostrar indicador de carga en el botón
    const botonRefrescar = document.querySelector('button[onclick="refrescarTodo()"]');
    if (botonRefrescar) {
        const textoOriginal = botonRefrescar.innerHTML;
        botonRefrescar.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i> Refrescando...';
        botonRefrescar.disabled = true;

        // Restaurar botón después de 2 segundos
        setTimeout(() => {
            botonRefrescar.innerHTML = textoOriginal;
            botonRefrescar.disabled = false;
        }, 2000);
    }

    // Cargar pacientes y estadísticas
    cargarPacientesTriaje();
    cargarEstadisticas();
}



// FUNCIÓN PRINCIPAL: Cargar pacientes de triaje
function cargarPacientesTriaje() {
    
    $.ajax({
        url: window.TRIAJE_URLS.obtenerPacientes,
        method: "GET",
        dataType: "json",
        success: function (data) {
            
            $('#tabla-pacientes-triaje tbody').empty();

            if (data.length > 0) {
                data.forEach(function (paciente) {
        
                    let triajeColor = determinarColorTriaje(paciente.triaje_color, 'badge');
                    
                    // Detectar si es modificación habilitada
                    let indicadorModificacion = '';
                    let claseFilaModificacion = '';
                    let tituloBoton = 'Ver y evaluar atención médica';
                    let iconoBoton = 'eye';
                    let textoBoton = 'Ver Atención';
                    
                    if (paciente.habilitado_por_admin == 1 || paciente.es_modificacion || paciente.tipo_acceso === 'MODIFICACION_HABILITADA') {
                        indicadorModificacion = '<span class="bg-orange-100 text-orange-800 px-2 py-1 rounded-full text-xs font-bold ml-2">🔄 MODIFICACIÓN</span>';
                        claseFilaModificacion = 'bg-orange-50 border-l-4 border-orange-400';
                        tituloBoton = 'Modificar formulario médico previamente completado';
                        iconoBoton = 'edit';
                        textoBoton = 'Modificar';
                    }

                    var fila = `<tr class="hover:bg-gray-50 ${claseFilaModificacion}">
                    <td class="px-4 py-3 border-b">
                        ${paciente.pac_nombres} ${paciente.pac_apellidos}
                        ${indicadorModificacion}
                    </td>
                    <td class="px-4 py-3 border-b">${paciente.pac_cedula}</td>
                    <td class="px-4 py-3 border-b">${paciente.ate_fecha}</td>
                    <td class="px-4 py-3 border-b">${paciente.ate_hora || 'No registrada'}</td>
                    <td class="px-4 py-3 border-b">${triajeColor}</td>
                    <td class="px-4 py-3 border-b">
                        <div class="flex flex-wrap gap-2">
                            <button class="bg-green-500 hover:bg-green-600 text-white px-3 py-1 rounded text-sm transition-colors duration-200 flex items-center"
                                data-ate-codigo="${paciente.ate_codigo}"
                                data-paciente="${paciente.pac_nombres} ${paciente.pac_apellidos}"
                                onclick="verAtencionDebug(this)"
                                title="${tituloBoton}">
                                <i class="fas fa-${iconoBoton} mr-1"></i> ${textoBoton}
                            </button>
                        </div>
                    </td>
                </tr>`;
                    $('#tabla-pacientes-triaje tbody').append(fila);
                });
                            
            } else {
                $('#tabla-pacientes-triaje tbody').append(
                    '<tr><td colspan="6" class="px-4 py-8 text-center text-gray-500">' +
                    '<i class="fas fa-info-circle mr-2"></i>' +
                    'No hay pacientes pendientes de decisión en triaje.' +
                    '</td></tr>'
                );
            }
        },
        error: function (xhr, status, error) {
            console.error('❌ Error al cargar pacientes:', {
                status: xhr.status,
                statusText: xhr.statusText,
                responseText: xhr.responseText,
                error: error
            });
            
            $('#tabla-pacientes-triaje tbody').html(
                '<tr><td colspan="6" class="px-4 py-8 text-center text-red-500">' +
                '<i class="fas fa-exclamation-triangle mr-2"></i>' +
                'Error al cargar los pacientes: ' + error +
                '<br><small>URL: ' + window.TRIAJE_URLS.obtenerPacientes + '</small>' +
                '</td></tr>'
            );
        }
    });
}

// FUNCIÓN: Cargar estadísticas de triaje
function cargarEstadisticas() {
    $.ajax({
        url: window.TRIAJE_URLS.obtenerEstadisticas,
        method: "GET",
        dataType: "json",
        success: function (response) {
            if (response.success) {
                const stats = response.estadisticas;
                $('#total-pendientes').text(stats.pacientes_triaje || 0);

                let rojos = 0, naranjas = 0, amarillos = 0, verdes = 0, azules = 0;
                if (stats.por_color) {
                    stats.por_color.forEach(function (color) {
                        switch (color.color?.toUpperCase()) {
                            case 'ROJO': rojos = color.cantidad; break;
                            case 'NARANJA': naranjas = color.cantidad; break;
                            case 'AMARILLO': amarillos = color.cantidad; break;
                            case 'VERDE': verdes = color.cantidad; break;
                            case 'AZUL': azules = color.cantidad; break;
                        }
                    });
                }

                $('#total-rojos').text(rojos);
                $('#total-naranjas').text(naranjas);
                $('#total-amarillos').text(amarillos);
                $('#total-verdes').text(verdes);
                $('#total-azules').text(azules);
            }
        },
        error: function (xhr, status, error) {
            console.error('Error al cargar estadísticas:', xhr.responseText);
        }
    });
}

// FUNCIÓN: Tomar atención rápida con debug mejorado
function verAtencionDebug(button) {
    const atecodigo = button.getAttribute('data-ate-codigo');
    const pacienteNombre = button.getAttribute('data-paciente');

    Swal.fire({
        icon: 'question',
        title: 'Evaluar atención',
        html: `¿Desea evaluar la atención de <strong>${pacienteNombre}</strong>?<br><br>Se abrirá el formulario médico.`,
        showCancelButton: true,
        confirmButtonText: 'Sí, evaluar',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            window.location.href = window.TRIAJE_URLS.tomarAtencionRapida + '/' + atecodigo;
        }
    });
}

// FUNCIÓN: Determinar color de triaje
function determinarColorTriaje(color, tipo) {
    if (!color) return '<span class="inline-block bg-gray-100 text-gray-800 px-2 py-1 rounded-full text-xs font-semibold">Sin triaje</span>';

    const colores = {
        'ROJO': { class: 'bg-red-100 text-red-800', text: 'ROJO' },
        'NARANJA': { class: 'bg-orange-100 text-orange-800', text: 'NARANJA' },
        'AMARILLO': { class: 'bg-yellow-100 text-yellow-800', text: 'AMARILLO' },
        'VERDE': { class: 'bg-green-100 text-green-800', text: 'VERDE' },
        'AZUL': { class: 'bg-blue-100 text-blue-800', text: 'AZUL' },
    };

    const colorConfig = colores[color.toUpperCase()] || { class: 'bg-gray-100 text-gray-800', text: color };
    return `<span class="inline-block ${colorConfig.class} px-2 py-1 rounded-full text-xs font-semibold">${colorConfig.text}</span>`;
}

// FUNCIONES DE COMPATIBILIDAD (mantener para no romper código existente)
function refrescarDatos() {
    refrescarTodo();
}

function refrescarTablaPacientes() {
    refrescarTodo();
}