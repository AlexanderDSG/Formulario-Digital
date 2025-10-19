/**
 * DASHBOARD DE MODIFICACIONES ADMINISTRATIVAS
 * Funcionalidad para gestión de modificaciones de formularios médicos
 */

// Variables globales para modificaciones
let tablaModificaciones = null;
let modificacionesDataTable = null;
let modificacionesRefreshInterval = null;

/**
 * Inicializar tabla de modificaciones con DataTables
 */
function inicializarTablaModificaciones() {

    // Destruir tabla existente si la hay
    if (tablaModificaciones) {
        try {
            tablaModificaciones.destroy();
            tablaModificaciones = null;
        } catch (error) {
        }
    }

    // Verificar que el elemento existe
    const tablaElement = document.getElementById('tablaModificaciones');
    if (!tablaElement) {
        console.error('Elemento #tablaModificaciones no encontrado');
        return;
    }

    // Debug: Verificar que jQuery y DataTables estén disponibles
    if (typeof $ === 'undefined') {
        console.error('jQuery no está disponible');
        return;
    }
    if (typeof DataTable === 'undefined') {
        console.error('DataTable (nueva sintaxis) no está disponible');
        return;
    }
    if (typeof $.fn.DataTable === 'undefined') {
        console.error('DataTables no está disponible');
        return;
    }

    // Configurar DataTables con Bootstrap 5
    tablaModificaciones = $('#tablaModificaciones').DataTable({
        processing: true,
        serverSide: false,
        responsive: false,
        dom: '<"flex flex-wrap items-center justify-between mb-4"<"flex items-center space-x-4"l><"flex items-center space-x-2"f>>rtip',
        
        ajax: {
            url: BASE_URL + 'administrador/modificaciones/obtenerDatosModificaciones',
            type: 'GET',
            timeout: 15000, // 15 segundos timeout
            data: function (d) {
                // Agregar filtros personalizados
                d.fecha_inicio = $('#fechaInicioMod').val();
                d.fecha_fin = $('#fechaFinMod').val();
                d.estado = $('#filtroEstadoMod').val();
            },
            dataSrc: function (json) {
                return json.data || [];
            },
            error: function (xhr, error, thrown) {
                console.error('Error cargando datos:', error, thrown);
                console.error('XHR Status:', xhr.status);
                console.error('Response Text:', xhr.responseText);

                // Mejorar manejo de errores específicos
                let mensajeError = 'Error al cargar los datos de modificaciones';
                if (xhr.status === 0) {
                    mensajeError = 'Error de conexión. Verifique su conexión a internet.';
                } else if (xhr.status === 401) {
                    mensajeError = 'Sesión expirada. Por favor, inicie sesión nuevamente.';
                    // Redireccionar al login después de un momento
                    setTimeout(() => window.location.href = BASE_URL + 'login', 2000);
                } else if (xhr.status === 403) {
                    mensajeError = 'No tiene permisos para acceder a esta función.';
                } else if (xhr.status === 404) {
                    mensajeError = 'Endpoint no encontrado. Contacte al administrador.';
                } else if (xhr.status === 500) {
                    mensajeError = 'Error interno del servidor. Revise los logs.';
                } else if (error === 'abort') {
                    return;
                } else if (error === 'timeout') {
                    mensajeError = 'Tiempo de espera agotado. La consulta tardó demasiado.';
                }

                mostrarMensajeError(mensajeError);
            }
        },
        columns: [
            {
                data: 'fecha_atencion',
                title: 'Fecha',
                render: function (data) {
                    if (!data) return '<span class="text-gray-400">Sin fecha</span>';
                    return formatearFecha(data);
                }
            },
            {
                data: 'hora_atencion',
                title: 'Hora',
                render: function (data) {
                    if (!data) return '<span class="text-gray-400">Sin hora</span>';
                    return data;
                }
            },
            {
                data: 'paciente',
                title: 'Paciente',
                render: function (data, type, row) {
                    if (!data || data.trim() === '') {
                        return '<span class="text-gray-400">Sin nombre</span>';
                    }
                    return `<span class="font-medium text-gray-900">${data}</span>`;
                }
            },
            {
                data: 'cedula',
                title: 'Cédula',
                render: function (data) {
                    if (!data || data === 'Sin cédula') {
                        return '<span class="text-gray-400 text-sm">Sin cédula</span>';
                    }
                    return `<span class="text-gray-700 font-mono text-sm">${data}</span>`;
                }
            },
            {
                data: 'estado_formulario',
                title: 'Estado',
                render: function (data) {
                    if (data === 'ME') {
                        return '<span class="px-2 py-1 text-xs font-semibold bg-blue-100 text-blue-800 rounded-full">Médico</span>';
                    } else if (data === 'ES') {
                        return '<span class="px-2 py-1 text-xs font-semibold bg-purple-100 text-purple-800 rounded-full">Especialista</span>';
                    }
                    return data;
                }
            },
            {
                data: 'modificaciones_realizadas',
                title: 'Modificaciones',
                className: 'text-center',
                render: function (data, type, row) {
                    const usadas = data || 0;
                    const permitidas = row.modificaciones_permitidas || 3;
                    const restantes = Math.max(0, permitidas - usadas);

                    // Color según el estado
                    let colorClass = 'bg-green-100 text-green-800';
                    if (usadas >= permitidas) {
                        colorClass = 'bg-red-100 text-red-800';
                    } else if (usadas >= (permitidas - 1)) {
                        colorClass = 'bg-yellow-100 text-yellow-800';
                    }

                    return `
                        <div class="text-center">
                            <span class="${colorClass} px-2 py-1 rounded text-sm font-medium">
                                ${usadas}/${permitidas}
                            </span>
                            <div class="text-xs text-gray-500 mt-1">
                                ${restantes} disponibles
                            </div>
                        </div>
                    `;
                }
            },
            {
                data: 'puede_modificar',
                title: 'Puede Modificar',
                className: 'text-center',
                render: function (data, type, row) {
                    const modificaciones_usadas = row.modificaciones_realizadas || 0;
                    const modificaciones_permitidas = row.modificaciones_permitidas || 3;

                    if (data && modificaciones_usadas < modificaciones_permitidas) {
                        return '<span class="px-2 py-1 text-xs font-semibold bg-green-100 text-green-800 rounded-full"><i class="fas fa-check mr-1"></i>Sí</span>';
                    } else {
                        const motivo = modificaciones_usadas >= modificaciones_permitidas ?
                            `Límite alcanzado (${modificaciones_usadas}/${modificaciones_permitidas})` :
                            'No puede modificar';
                        return `<span class="px-2 py-1 text-xs font-semibold bg-red-100 text-red-800 rounded-full" title="${motivo}"><i class="fas fa-times mr-1"></i>No</span>`;
                    }
                }
            },
            {
                data: 'ultimo_usuario',
                title: 'Último Usuario',
                render: function (data) {
                    if (!data) return '<span class="text-gray-400">Sin información</span>';
                    return `<span class="text-gray-700 text-sm">${data}</span>`;
                }
            },
            {
                data: 'fecha_ultima_modificacion',
                title: 'Última Modificación',
                render: function (data) {
                    if (!data) return '<span class="text-gray-400">Sin modificaciones</span>';
                    return formatearFechaHora(data);
                }
            },
            {
                data: null,
                title: 'Acciones',
                orderable: false,
                className: 'text-center',
                width: '120px',
                render: function (data, type, row) {
                    const ate_codigo = row.ate_codigo || '';
                    const estado_formulario = row.estado_formulario || '';
                    const paciente = row.paciente || 'Sin nombre';
                    const estado_boton = row.estado_boton_admin || 'PUEDE_HABILITAR';
                    const modificaciones_usadas = row.modificaciones_realizadas || 0;
                    const modificaciones_permitidas = row.modificaciones_permitidas || 3;

                    // Escapar comillas para evitar errores JavaScript
                    const pacienteEscapado = paciente.replace(/'/g, "\\'");

                    // Mostrar botón según el estado exacto
                    if (estado_boton === 'BLOQUEADO') {
                        // Límite alcanzado - botón rojo bloqueado
                        return `
                            <button class="bg-red-500 text-white px-2 py-1 rounded text-xs whitespace-nowrap cursor-not-allowed opacity-75"
                                    title="Límite de modificaciones alcanzado (${modificaciones_usadas}/${modificaciones_permitidas})" disabled>
                                <i class="fas fa-ban mr-1"></i>Bloqueado
                            </button>
                        `;
                    } else if (estado_boton === 'HABILITADO') {
                        // Ya habilitado por admin - botón verde
                        return `
                            <button class="bg-green-500 text-white px-2 py-1 rounded text-xs whitespace-nowrap cursor-default"
                                    title="Modificación habilitada - esperando que el médico complete" disabled>
                                <i class="fas fa-check mr-1"></i>Habilitado
                            </button>
                        `;
                    } else {
                        // Puede habilitar - botón naranja normal
                        return `
                            <button onclick="abrirModalHabilitar('${ate_codigo}', '${estado_formulario}', '${pacienteEscapado}')"
                                    class="bg-orange-500 hover:bg-orange-600 text-white px-2 py-1 rounded text-xs whitespace-nowrap"
                                    title="Habilitar modificación (${modificaciones_usadas}/${modificaciones_permitidas} usadas)">
                                <i class="fas fa-unlock mr-1"></i>Habilitar
                            </button>
                        `;
                    }
                }
            }
        ],
        pageLength: 10,
            lengthMenu: [[10, 25, 50, 100], [10, 25, 50, 100]],
            order: [[0, 'desc'], [1, 'desc']],
            language: {
                processing: "Procesando...",
                search: "Buscar:",
                lengthMenu: "Mostrar _MENU_ registros",
                info: "Mostrando del _START_ al _END_ de _TOTAL_ registros",
                infoEmpty: "Mostrando del 0 al 0 de 0 registros",
                infoFiltered: "(filtrado de _MAX_ registros)",
                loadingRecords: "Cargando...",
                zeroRecords: "No se encontraron registros",
                emptyTable: "No hay datos disponibles",
                paginate: {
                    first: "Primero",
                    previous: "Anterior",
                    next: "Siguiente",
                    last: "Último"
                }
            },
            scrollX: true,
            scrollY: '500px',
            scrollCollapse: true,
            fixedHeader: false,
            autoWidth: false,
            responsive: false,

        drawCallback: function () {
            // Aplicar estilos de Tailwind después de cada dibujo
            aplicarEstilosTabla();
        },
        initComplete: function () {
            this.api().columns.adjust();
        }
    });

    // Verificar que la tabla se inicializó correctamente
    if (tablaModificaciones) {
        // Configurar eventos de filtros
        configurarEventosModificaciones();

        // Cargar estadísticas
        cargarEstadisticasModificaciones();
    } else {
        console.error('Error: DataTable no se inicializó');
    }


    // Auto-refresh cada 30 segundos para ver cambios en tiempo real
    // Limpiar interval anterior si existe
    if (modificacionesRefreshInterval) {
        clearInterval(modificacionesRefreshInterval);
    }

    modificacionesRefreshInterval = setInterval(function () {
        if (tablaModificaciones && typeof tablaModificaciones.ajax !== 'undefined') {
            try {
                tablaModificaciones.ajax.reload(null, false); // false = no resetear paginación
            } catch (error) {
                console.error('Error en auto-refresh:', error);
            }
        }
    }, 30000); // 30 segundos
}

/**
 * Configurar eventos para filtros y botones
 */
function configurarEventosModificaciones() {
    // Evento para aplicar filtros
    document.getElementById('btnAplicarFiltrosMod')?.addEventListener('click', function () {
        if (tablaModificaciones) {
            mostrarIndicadorCarga();
            tablaModificaciones.ajax.reload(function () {
                ocultarIndicadorCarga();
                mostrarNotificacion('Filtros aplicados correctamente', 'success');
            });
        }
    });

    // Evento para limpiar filtros
    document.getElementById('btnLimpiarFiltrosMod')?.addEventListener('click', function () {
        document.getElementById('fechaInicioMod').value = '';
        document.getElementById('fechaFinMod').value = '';
        document.getElementById('filtroEstadoMod').value = 'todos';

        if (tablaModificaciones) {
            mostrarIndicadorCarga();
            tablaModificaciones.ajax.reload(function () {
                ocultarIndicadorCarga();
                mostrarNotificacion('Filtros limpiados', 'success');
            });
        }
    });

    // Evento para refrescar tabla manualmente
    document.getElementById('btnRefrescarMod')?.addEventListener('click', function () {
        if (tablaModificaciones) {
            mostrarIndicadorCarga();
            tablaModificaciones.ajax.reload(function () {
                ocultarIndicadorCarga();
                mostrarNotificacion('Tabla actualizada', 'success');
                // También recargar estadísticas
                cargarEstadisticasModificaciones();
            });
        }
    });
}

/**
 * Cargar estadísticas del dashboard
 */
function cargarEstadisticasModificaciones() {
    const baseUrl = BASE_URL || window.location.origin + '/';

    fetch(baseUrl + 'administrador/modificaciones/obtenerEstadisticas', {
        method: 'GET',
        headers: {
            'Content-Type': 'application/json'
        }
    })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                actualizarEstadisticasModificaciones(data.estadisticas);
            } else {
                console.error('Error obteniendo estadísticas:', data.error);
            }
        })
        .catch(error => {
            console.error('Error en petición de estadísticas:', error);
        });
}

/**
 * Actualizar estadísticas en el dashboard
 */
function actualizarEstadisticasModificaciones(estadisticas) {

    // Actualizar contadores con validación de existencia de elementos
    const totalFormularios = document.getElementById('totalFormularios');
    if (totalFormularios) {
        totalFormularios.textContent = estadisticas.total_formularios || 0;
    }

    const puedenModificar = document.getElementById('puedenModificar');
    if (puedenModificar) {
        puedenModificar.textContent = estadisticas.pueden_modificar || 0;
    }

    const bloqueados = document.getElementById('bloqueados');
    if (bloqueados) {
        bloqueados.textContent = estadisticas.bloqueados || 0;
    }

    const modificacionesHoy = document.getElementById('modificacionesHoy');
    if (modificacionesHoy) {
        modificacionesHoy.textContent = estadisticas.modificaciones_hoy || 0;
    }
}

/**
 * Abrir modal para habilitar modificación
 */
function abrirModalHabilitar(atecodigo, seccion, nombrePaciente) {
    document.getElementById('ateCodigoHabilitar').value = atecodigo;
    document.getElementById('seccionHabilitar').value = seccion;
    document.getElementById('nombrePaciente').textContent = nombrePaciente;
    document.getElementById('motivoHabilitacion').value = '';

    // Mostrar modal
    const modal = document.getElementById('modalHabilitar');
    modal.classList.remove('hidden');
    modal.classList.add('flex');
}

/**
 * Cerrar modal de habilitar modificación
 */
function cerrarModalHabilitar() {
    const modal = document.getElementById('modalHabilitar');
    modal.classList.add('hidden');
    modal.classList.remove('flex');
}

/**
 * Enviar habilitación de modificación
 */
function enviarHabilitacion() {
    const atecodigo = document.getElementById('ateCodigoHabilitar').value;
    const seccion = document.getElementById('seccionHabilitar').value;
    const motivo = document.getElementById('motivoHabilitacion').value.trim();

    if (!motivo) {
        mostrarNotificacion('Por favor ingrese el motivo de la habilitación', 'error');
        return;
    }

    const baseUrl = BASE_URL || window.location.origin + '/';

    // Mostrar indicador de carga
    const btn = event.target;
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Procesando...';

    fetch(baseUrl + 'administrador/modificaciones/habilitarModificacion', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded'
        },
        body: new URLSearchParams({
            ate_codigo: atecodigo,
            seccion: seccion,
            motivo: motivo
        })
    })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                mostrarNotificacion(data.message || 'Modificación habilitada correctamente', 'success');
                cerrarModalHabilitar();

                // Recargar tabla
                if (tablaModificaciones) {
                    tablaModificaciones.ajax.reload();
                }

                // Actualizar estadísticas
                cargarEstadisticasModificaciones();

            } else {
                mostrarNotificacion(data.error || 'Error al habilitar modificación', 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            mostrarNotificacion('Error de conexión', 'error');
        })
        .finally(() => {
            // Restaurar botón
            btn.disabled = false;
            btn.innerHTML = '<i class="fas fa-unlock mr-2"></i>Habilitar Modificación';
        });
}


/**
 * Funciones de utilidad
 */
function formatearFecha(fecha) {
    if (!fecha) return '';

    // Si la fecha viene en formato YYYY-MM-DD, parsear manualmente para evitar problemas de zona horaria
    if (typeof fecha === 'string' && fecha.match(/^\d{4}-\d{2}-\d{2}$/)) {
        const partes = fecha.split('-');
        const year = parseInt(partes[0]);
        const month = parseInt(partes[1]) - 1; // JavaScript months are 0-indexed
        const day = parseInt(partes[2]);
        const date = new Date(year, month, day);

        return date.toLocaleDateString('es-ES', {
            year: 'numeric',
            month: '2-digit',
            day: '2-digit'
        });
    }

    // Para otros formatos, usar el método original
    const date = new Date(fecha);
    return date.toLocaleDateString('es-ES', {
        year: 'numeric',
        month: '2-digit',
        day: '2-digit'
    });
}

function formatearFechaHora(fechaHora) {
    if (!fechaHora) return '';
    const date = new Date(fechaHora);
    return date.toLocaleString('es-ES', {
        year: 'numeric',
        month: '2-digit',
        day: '2-digit',
        hour: '2-digit',
        minute: '2-digit'
    });
}

function mostrarIndicadorCarga() {
    const overlay = document.getElementById('loadingOverlayModificaciones');
    if (overlay) {
        overlay.classList.remove('hidden');
        overlay.classList.add('flex');
    }
}

function ocultarIndicadorCarga() {
    const overlay = document.getElementById('loadingOverlayModificaciones');
    if (overlay) {
        overlay.classList.add('hidden');
        overlay.classList.remove('flex');
    }
}

function mostrarNotificacion(mensaje, tipo = 'info') {
    // Usar la función global si existe
    if (typeof mostrarNotificacionAdmin === 'function') {
        mostrarNotificacionAdmin(mensaje, tipo);
    } else {
        // Fallback con SweetAlert2
        const iconMap = {
            'info': 'info',
            'success': 'success',
            'error': 'error',
            'warning': 'warning'
        };

        Swal.fire({
            icon: iconMap[tipo] || 'info',
            title: tipo === 'error' ? 'Error' : tipo === 'success' ? 'Éxito' : 'Información',
            text: mensaje,
            confirmButtonText: 'Aceptar'
        });
    }
}

function mostrarMensajeError(mensaje) {
    mostrarNotificacion(mensaje, 'error');
}

function aplicarEstilosTabla() {
    // Aplicar estilos específicos de Tailwind a elementos de DataTables
    const wrapper = document.querySelector('#tablaModificaciones_wrapper');
    if (wrapper) {
        // Aplicar estilos a controles de DataTables
        const searchInput = wrapper.querySelector('input[type="search"]');
        if (searchInput) {
            searchInput.className = 'border border-gray-300 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-orange-500';
        }

        const lengthSelect = wrapper.querySelector('select');
        if (lengthSelect) {
            lengthSelect.className = 'border border-gray-300 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-orange-500';
        }

        // Aplicar estilos a las celdas de la tabla
        const table = document.querySelector('#tablaModificaciones');
        if (table) {
            // Estilos para el cuerpo de la tabla
            const cells = table.querySelectorAll('tbody td');
            cells.forEach(cell => {
                if (!cell.className.includes('px-')) {
                    cell.className += ' px-3 py-2 text-sm';
                }
            });
        }
    }
}

/**
 * Función para limpiar estadísticas (llamada por el coordinador)
 */
function limpiarEstadisticasModificaciones() {
    document.getElementById('totalFormularios').textContent = '0';
    document.getElementById('puedenModificar').textContent = '0';
    document.getElementById('bloqueados').textContent = '0';
    document.getElementById('modificacionesHoy').textContent = '0';
}

/**
 * Función para destruir tabla (llamada por el coordinador)
 */
function destroyModificacionesTable() {
    // Limpiar interval de auto-refresh
    if (modificacionesRefreshInterval) {
        clearInterval(modificacionesRefreshInterval);
        modificacionesRefreshInterval = null;
    }

    if (tablaModificaciones) {
        try {
            tablaModificaciones.destroy();
            tablaModificaciones = null;
        } catch (error) {
        }
    }
}

// Exportar funciones para uso global
window.inicializarTablaModificaciones = inicializarTablaModificaciones;
window.limpiarEstadisticasModificaciones = limpiarEstadisticasModificaciones;
window.destroyModificacionesTable = destroyModificacionesTable;
window.abrirModalHabilitar = abrirModalHabilitar;
window.cerrarModalHabilitar = cerrarModalHabilitar;
window.enviarHabilitacion = enviarHabilitacion;