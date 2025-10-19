/**
 * Dashboard de Reportes Administrativos
 * Formulario Digital - Hospital San Vicente de Paúl
 */

// Variables globales
let tabla;
let baseUrl;

/**
 * Función principal para inicializar reportes (llamada desde panelCoordinator)
 */
function inicializarTablaReportesAdmin() {

    // Configurar baseUrl
    baseUrl = (BASE_URL || window.location.origin + '/') + 'administrador/reportes/';

    // Inicializar fecha por defecto (último mes)
    const hoy = new Date();
    const unMesAtras = new Date();
    unMesAtras.setMonth(hoy.getMonth() - 1);

    const fechaInicioEl = document.getElementById('fechaInicio');
    const fechaFinEl = document.getElementById('fechaFin');

    if (fechaInicioEl && fechaFinEl) {
        fechaFinEl.value = hoy.toISOString().split('T')[0];
        fechaInicioEl.value = unMesAtras.toISOString().split('T')[0];
    }

    // Inicializar DataTable con delay
    setTimeout(() => {
        inicializarTabla();
    }, 200);

    // Configurar eventos
    configurarEventosReportes();
}

/**
 * Configurar eventos de filtros
 */
function configurarEventosReportes() {
    const btnAplicar = document.getElementById('btnAplicarFiltros');
    const btnLimpiar = document.getElementById('btnLimpiarFiltros');
    const fechaInicio = document.getElementById('fechaInicio');
    const fechaFin = document.getElementById('fechaFin');
    const filtroEstado = document.getElementById('filtroEstado');

    if (btnAplicar) {
        btnAplicar.addEventListener('click', aplicarFiltros);
    }

    if (btnLimpiar) {
        btnLimpiar.addEventListener('click', limpiarFiltros);
    }

    // Aplicar filtros automáticamente al cambiar
    if (fechaInicio) fechaInicio.addEventListener('change', aplicarFiltros);
    if (fechaFin) fechaFin.addEventListener('change', aplicarFiltros);
    if (filtroEstado) filtroEstado.addEventListener('change', aplicarFiltros);
}

// Mantener compatibilidad con jQuery si se carga normalmente
$(document).ready(function () {
    // Solo ejecutar si no estamos en modo dinámico
    if (!window.reportesAdminCargado) {
        inicializarTablaReportesAdmin();
    }
});

function inicializarTabla() {
    // Verificar que la tabla existe en el DOM
    const tableElement = document.getElementById('tablaReportesAdmin');
    if (!tableElement) {
        console.error('Elemento tabla no encontrado');
        return;
    }

    try {
        // Verificar que jQuery y DataTables están disponibles
        if (typeof $ === 'undefined') {
            console.error('jQuery no está disponible');
            return;
        }

        if (typeof $.fn.DataTable === 'undefined') {
            console.error('DataTables no está disponible');
            return;
        }

        // Configuración de DataTable con Bootstrap 5
        tabla = $('#tablaReportesAdmin').DataTable({
            processing: true,
            serverSide: false,
            responsive: false,
            dom: '<"flex flex-wrap items-center justify-between mb-4"<"flex items-center space-x-4"lB><"flex items-center space-x-2"f>>rtip',
            
            ajax: {
                url: baseUrl + 'obtenerDatos',
                type: 'GET',
                data: function (d) {
                    d.fecha_inicio = $('#fechaInicio').val();
                    d.fecha_fin = $('#fechaFin').val();
                    d.estado = $('#filtroEstado').val();
                },
                beforeSend: function () {
                    mostrarCarga(true);
                },
                complete: function () {
                    mostrarCarga(false);
                },
                dataSrc: function (json) {
                    if (json && json.data && Array.isArray(json.data)) {
                        actualizarEstadisticas(json.data);
                        actualizarContadorRegistros(json.data.length);
                        cargarEstadisticasEmbarazos();
                        return json.data;
                    } else {
                        console.error('Datos no válidos recibidos:', json);
                        actualizarEstadisticas([]);
                        actualizarContadorRegistros(0);
                        if (json.error) {
                            showAlert('Error: ' + json.error, 'error');
                        }
                        return [];
                    }
                },
                error: function (xhr, error, thrown) {
                    console.error('Error en petición AJAX:', error, thrown);
                    console.error('Response status:', xhr.status);
                    console.error('Response text:', xhr.responseText);
                    showAlert('Error al cargar los datos: ' + error, 'error');
                    mostrarCarga(false);

                    // Mostrar error en la tabla
                    const tbody = document.querySelector('#tablaReportesAdmin tbody');
                    if (tbody) {
                        tbody.innerHTML = `
                            <tr>
                                <td colspan="18" class="px-6 py-4 text-center text-red-600">
                                    <div class="flex flex-col items-center">
                                        <i class="fas fa-exclamation-triangle text-2xl mb-2"></i>
                                        <span>Error al cargar los datos</span>
                                        <span class="text-sm">${error}</span>
                                    </div>
                                </td>
                            </tr>
                        `;
                    }
                }
            },
            columns: [
                {
                    data: 'fecha_atencion',
                    name: 'fecha_atencion',
                    render: function (data) {
                        return data ? `<span class="font-mono text-sm">${escapeHtml(data)}</span>` : '<span class="text-gray-400">-</span>';
                    }
                },
                {
                    data: 'hora_atencion',
                    name: 'hora_atencion',
                    render: function (data) {
                        return data ? `<span class="font-mono text-sm">${escapeHtml(data)}</span>` : '<span class="text-gray-400">-</span>';
                    }
                },
                {
                    data: 'paciente',
                    name: 'paciente',
                    render: function (data) {
                        return data ? `<span class="font-medium">${escapeHtml(data)}</span>` : '<span class="text-gray-400">-</span>';
                    }
                },
                {
                    data: 'cedula',
                    name: 'cedula',
                    render: function (data) {
                        return data ? `<span class="font-mono text-sm bg-gray-100 px-2 py-1 rounded">${escapeHtml(data)}</span>` : '<span class="text-gray-400">-</span>';
                    }
                },
                {
                    data: 'sexo',
                    name: 'sexo',
                    render: function (data) {
                        const color = data === 'M' ? 'bg-blue-100 text-blue-800' : data === 'F' ? 'bg-pink-100 text-pink-800' : 'bg-gray-100 text-gray-800';
                        return data ? `<span class="badge ${color}">${escapeHtml(data)}</span>` : '<span class="text-gray-400">-</span>';
                    }
                },
                {
                    data: 'edad',
                    name: 'edad',
                    render: function (data) {
                        if (!data) return '<span class="text-gray-400">-</span>';
                        const badge = data.includes('M') || data.includes('D') || data.includes('H')
                            ? 'bg-yellow-100 text-yellow-800'
                            : 'bg-blue-100 text-blue-800';
                        return `<span class="badge ${badge}">${escapeHtml(data)}</span>`;
                    }
                },
                {
                    data: 'triaje_color',
                    name: 'triaje_color',
                    render: function (data) {
                        if (!data || data === '') return '<span class="text-gray-400">-</span>';

                        // Mapeo correcto de colores según escala de Manchester
                        const colorMappings = {
                            'rojo': 'rojo',
                            'red': 'rojo',
                            'naranja': 'naranja',
                            'orange': 'naranja',
                            'amarillo': 'amarillo',
                            'yellow': 'amarillo',
                            'verde': 'verde',
                            'green': 'verde',
                            'azul': 'azul',
                            'blue': 'azul'
                        };

                        const colorKey = data.toLowerCase().trim();
                        const mappedColor = colorMappings[colorKey] || colorKey;

                        return `<span class="badge badge-triaje badge-${mappedColor}">${data}</span>`;
                    }
                },
                {
                    data: 'paciente_afiliado',
                    name: 'paciente_afiliado',
                    render: function (data) {
                        return data === 'Sí' ?
                            '<span class="badge bg-success">Sí</span>' :
                            '<span class="badge bg-danger">No</span>';
                    }
                },
                {
                    data: 'grupo_prioritario',
                    name: 'grupo_prioritario',
                    render: function (data) {
                        return data === 'Sí' ?
                            '<span class="badge bg-warning">Sí</span>' :
                            '<span class="badge bg-secondary">No</span>';
                    }
                },
                {
                    data: 'seguro',
                    name: 'seguro',
                    render: function (data) {
                        return data ? `<span class="text-sm">${escapeHtml(data)}</span>` : '<span class="text-gray-400">-</span>';
                    }
                },
                {
                    data: 'nacionalidad',
                    name: 'nacionalidad',
                    render: function (data) {
                        return data ? `<span class="text-sm">${escapeHtml(data)}</span>` : '<span class="text-gray-400">-</span>';
                    }
                },
                {
                    data: 'etnia',
                    name: 'etnia',
                    render: function (data) {
                        return data ? `<span class="text-sm">${escapeHtml(data)}</span>` : '<span class="text-gray-400">-</span>';
                    }
                },
                {
                    data: 'nacionalidad_indigena',
                    name: 'nacionalidad_indigena',
                    render: function (data) {
                        return data && data !== '-' ?
                            `<span class="badge bg-info">${escapeHtml(data)}</span>` :
                            '<span class="text-gray-400">-</span>';
                    }
                },
                {
                    data: 'pueblo_indigena',
                    name: 'pueblo_indigena',
                    render: function (data) {
                        return data && data !== '-' ?
                            `<span class="badge bg-info">${escapeHtml(data)}</span>` :
                            '<span class="text-gray-400">-</span>';
                    }
                },
                
                {
                    data: 'embarazada',
                    name: 'embarazada',
                    render: function (data) {
                        return data === 'Sí' ?
                            '<span class="badge" style="background-color: #ec4899; color: white;">Sí</span>' :
                            '<span class="badge bg-secondary">No</span>';
                    }
                },
                {
                    data: 'establecimiento_ingreso',
                    name: 'establecimiento_ingreso',
                    render: function (data) {
                        return data && data !== 'No especificado' ? `<span class="text-sm">${escapeHtml(data)}</span>` : '<span class="text-gray-400">-</span>';
                    }
                },
                {
                    data: 'establecimiento_egreso',
                    name: 'establecimiento_egreso',
                    render: function (data) {
                        return data && data !== 'No especificado' ? `<span class="text-sm">${escapeHtml(data)}</span>` : '<span class="text-gray-400">-</span>';
                    }
                },
                
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
            buttons: [
                {
                    extend: 'excel',
                    text: '<i class="fas fa-file-excel mr-1"></i> Excel',
                    className: 'bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg text-sm',
                    title: 'Reporte_Administrativo_' + new Date().toISOString().split('T')[0],
                    exportOptions: {
                        columns: ':visible',
                        format: {
                            body: function (data, row, column, node) {
                                if (typeof data === 'string' && data.includes('<')) {
                                    let tempDiv = document.createElement('div');
                                    tempDiv.innerHTML = data;
                                    return tempDiv.textContent || tempDiv.innerText || '';
                                }
                                return data;
                            }
                        }
                    }
                }
            ],
            initComplete: function () {
                this.api().columns.adjust();
            }
        });

        // Guardar referencia en el estado global
        if (!window.PanelState) {
            window.PanelState = {};
        }
        window.PanelState.adminReportesDataTable = tabla;

    } catch (error) {
        console.error('Error inicializando DataTable:', error);
        showAlert('Error al inicializar la tabla: ' + error.message, 'error');

        // Mostrar error en la tabla
        const tbody = document.querySelector('#tablaReportesAdmin tbody');
        if (tbody) {
            tbody.innerHTML = `
                <tr>
                    <td colspan="18" class="px-6 py-4 text-center text-red-600">
                        <div class="flex flex-col items-center">
                            <i class="fas fa-exclamation-triangle text-2xl mb-2"></i>
                            <span>Error al inicializar la tabla</span>
                            <span class="text-sm">${error.message}</span>
                        </div>
                    </td>
                </tr>
            `;
        }
    }
}

function aplicarFiltros() {
    if (tabla) {
        mostrarCarga(true);
        tabla.ajax.reload(function (json) {
            mostrarCarga(false);
        }, false);
    }
}

function limpiarFiltros() {
    $('#fechaInicio').val('');
    $('#fechaFin').val('');
    $('#filtroEstado').val('todos');
    aplicarFiltros();
}

function actualizarEstadisticas(datos) {
    // Asegurar que tengamos un array para procesar
    if (!Array.isArray(datos)) {
        datos = [];
    }

    const total = datos.length;
    const afiliados = datos.filter(d => d && d.paciente_afiliado === 'Sí').length;
    const grupoPrioritario = datos.filter(d => d && d.grupo_prioritario === 'Sí').length;
    const porcentajeAfiliados = total > 0 ? Math.round((afiliados / total) * 100) : 0;

    $('#totalPacientes').text(total.toLocaleString());
    $('#pacientesAfiliados').text(porcentajeAfiliados + '%');
    $('#grupoPrioritario').text(grupoPrioritario.toLocaleString());
}

function cargarEstadisticasEmbarazos() {
    const fechaInicio = $('#fechaInicio').val();
    const fechaFin = $('#fechaFin').val();

    $.get(baseUrl + 'obtenerEstadisticasEmbarazos', {
        fecha_inicio: fechaInicio,
        fecha_fin: fechaFin
    })
    .done(function (data) {

        if (data.success && data.data) {
            const totalEmbarazadas = data.data.total_embarazadas || 0;
            $('#totalEmbarazadas').text(totalEmbarazadas);
        } else {
            console.warn('⚠️ Respuesta sin datos válidos:', data);
            $('#totalEmbarazadas').text('0');
        }
    })
    .fail(function (xhr, status, error) {
        console.error('❌ Error cargando estadísticas de embarazos:', {
            status: status,
            error: error,
            response: xhr.responseText
        });
        $('#totalEmbarazadas').text('0');
    });
}

function actualizarContadorRegistros(total) {
    $('#contadorRegistros').text(total.toLocaleString());
}

function mostrarCarga(mostrar) {
    const overlay = document.getElementById('loadingOverlayReportes');
    if (overlay) {
        if (mostrar) {
            overlay.classList.remove('hidden');
            overlay.classList.add('flex');
        } else {
            overlay.classList.add('hidden');
            overlay.classList.remove('flex');
        }
    }
}

// Función para refrescar la tabla
function refreshAdminReportesTable() {
    if (window.PanelState && window.PanelState.adminReportesDataTable) {
        window.PanelState.adminReportesDataTable.ajax.reload(null, false);
    }
}

// Función para destruir la tabla cuando se cambie de vista
function destroyAdminReportesTable() {
    if (window.PanelState && window.PanelState.adminReportesDataTable) {
        try {
            window.PanelState.adminReportesDataTable.destroy();
            window.PanelState.adminReportesDataTable = null;
        } catch (error) {
            window.PanelState.adminReportesDataTable = null;
        }
    }
}

// Funciones auxiliares
function escapeHtml(text) {
    if (!text) return '';
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

function showAlert(message, type = 'info') {
    const alertDiv = document.createElement('div');
    alertDiv.className = `fixed top-4 right-4 z-50 p-4 rounded-lg shadow-lg max-w-sm ${getAlertClass(type)}`;
    alertDiv.innerHTML = `
        <div class="flex items-center">
            <i class="fas ${getAlertIcon(type)} mr-2"></i>
            <span>${escapeHtml(message)}</span>
            <button onclick="this.parentElement.parentElement.remove()" class="ml-4 text-lg">&times;</button>
        </div>
    `;

    document.body.appendChild(alertDiv);

    setTimeout(() => {
        if (alertDiv.parentElement) {
            alertDiv.remove();
        }
    }, 5000);
}

function getAlertClass(type) {
    switch (type) {
        case 'success': return 'bg-green-100 text-green-800 border border-green-300';
        case 'error': return 'bg-red-100 text-red-800 border border-red-300';
        case 'warning': return 'bg-yellow-100 text-yellow-800 border border-yellow-300';
        default: return 'bg-blue-100 text-blue-800 border border-blue-300';
    }
}

function getAlertIcon(type) {
    switch (type) {
        case 'success': return 'fa-check-circle';
        case 'error': return 'fa-exclamation-circle';
        case 'warning': return 'fa-exclamation-triangle';
        default: return 'fa-info-circle';
    }
}