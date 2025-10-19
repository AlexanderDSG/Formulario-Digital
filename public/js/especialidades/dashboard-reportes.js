/**
 * Dashboard de Reportes de Especialidades Médicas
 * Formulario Digital - Hospital San Vicente de Paúl
 */

// Variables globales
let tabla;
let baseUrl;

$(document).ready(function () {
    // Configurar baseUrl usando BASE_URL global
    baseUrl = (BASE_URL || window.location.origin + '/') + 'especialidades/reportes/';

    // Inicializar fecha por defecto (último mes)
    const hoy = new Date();
    const unMesAtras = new Date();
    unMesAtras.setMonth(hoy.getMonth() - 1);

    $('#fechaFin').val(hoy.toISOString().split('T')[0]);
    $('#fechaInicio').val(unMesAtras.toISOString().split('T')[0]);

    // Cargar especialidades
    cargarEspecialidades();

    // Inicializar DataTable con delay similar al otro archivo
    setTimeout(() => {
        inicializarTabla();
    }, 200);

    // Eventos
    $('#btnAplicarFiltros').click(function () {
        aplicarFiltros();
    });

    $('#btnLimpiarFiltros').click(function () {
        limpiarFiltros();
    });

    // Aplicar filtros automáticamente al cambiar
    $('#fechaInicio, #fechaFin, #filtroEspecialidad, #filtroEstado').change(function () {
        aplicarFiltros();
    });
});

function cargarEspecialidades() {
    $.get(baseUrl + 'obtenerEspecialidades')
        .done(function (data) {
            const select = $('#filtroEspecialidad');
            if (Array.isArray(data)) {
                data.forEach(function (esp) {
                    select.append(`<option value="${esp.esp_codigo}">${esp.esp_nombre}</option>`);
                });
            }
        })
        .fail(function (xhr, status, error) {
            console.error('Error cargando especialidades:', error);
            showAlert('Error al cargar especialidades', 'error');
        });
}

function inicializarTabla() {
    // Verificar que la tabla existe en el DOM
    const tableElement = document.getElementById('tablaReportes');
    if (!tableElement) {
        return;
    }

    try {
        // Verificar si jQuery y DataTables están disponibles
        if (typeof $ === 'undefined') {
            console.error('jQuery no está disponible');
            return;
        }

        if (typeof $.fn.DataTable === 'undefined') {
            console.error('DataTables no está disponible');
            return;
        }

        // Configuración mejorada con Bootstrap 5
        tabla = $('#tablaReportes').DataTable({
            processing: true,
            serverSide: false,
            dom: 'Bfrtip',
           
            ajax: {
                url: baseUrl + 'obtenerDatosAlternativo',
                type: 'GET',
                data: function (d) {
                    d.fecha_inicio = $('#fechaInicio').val();
                    d.fecha_fin = $('#fechaFin').val();
                    d.especialidad = $('#filtroEspecialidad').val();
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
                    const tbody = document.querySelector('#tablaReportes tbody');
                    if (tbody) {
                        tbody.innerHTML = `
                            <tr>
                                <td colspan="26" class="px-6 py-4 text-center text-red-600">
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
                    data: 'fecha_ingreso',
                    name: 'fecha_ingreso',
                    render: function (data) {
                        return data ? `<span class="font-mono text-sm">${escapeHtml(data)}</span>` : '<span class="text-gray-400">-</span>';
                    }
                },
                {
                    data: 'hora_ingreso',
                    name: 'hora_ingreso',
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
                    data: 'hora_alta',
                    name: 'hora_alta',
                    render: function (data) {
                        return data ? `<span class="font-mono text-sm">${escapeHtml(data)}</span>` : '<span class="text-gray-400">-</span>';
                    }
                },
                {
                    data: 'paciente',
                    name: 'paciente',
                    render: function (data) {
                        return data ? `<span class="font-mono text-sm">${escapeHtml(data)}</span>` : '<span class="text-gray-400">-</span>';
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
                    data: 'nacionalidad',
                    name: 'nacionalidad',
                    render: function (data) {
                        return data ? `<span class="font-mono text-sm">${escapeHtml(data)}</span>` : '<span class="text-gray-400">-</span>';
                    }
                },
                {
                    data: 'etnia',
                    name: 'etnia',
                    render: function (data) {
                        return data ? `<span class="font-mono text-sm">${escapeHtml(data)}</span>` : '<span class="text-gray-400">-</span>';
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
                    data: 'triaje_color',
                    name: 'triaje_color',
                    render: function (data) {
                        if (!data || data === '') return '<span class="text-gray-400">-</span>';
                        const color = data.toLowerCase();
                        return `<span class="badge badge-triaje badge-${color}">${data}</span>`;
                    }
                },
                {
                    data: 'especialidad',
                    name: 'especialidad',
                    render: function (data) {
                        return data ? `<span class="font-mono text-sm">${escapeHtml(data)}</span>` : '<span class="text-gray-400">-</span>';
                    }
                },
                {
                    data: 'estado',
                    name: 'estado',
                    orderable: true,
                    searchable: true,
                    render: function (data) {
                        const badges = {
                            'PENDIENTE': 'warning',
                            'EN_ATENCION': 'primary',
                            'COMPLETADA': 'success',
                            'ENVIADO_A_OBSERVACION': 'info',
                            'EN_PROCESO': 'secondary'
                        };
                        const badge = badges[data] || 'secondary';
                        return `<span class="badge bg-${badge}">${data || '-'}</span>`;
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
                        return data ? `<span class="font-mono text-sm">${escapeHtml(data)}</span>` : '<span class="text-gray-400">-</span>';
                    }
                },
                {
                    data: 'estado_egreso',
                    name: 'estado_egreso',
                    render: function (data, type, row) {
                        // Para exportación a Excel, devolver el texto con comas
                        if (type === 'export' || type === 'type') {
                            return data || '';
                        }

                        if (!data || data === '-' || data === null || data === undefined || data === 'null') {
                            return '<span class="text-gray-400">-</span>';
                        }

                        // Convertir a string por si acaso
                        const dataStr = String(data).trim();

                        if (dataStr === '' || dataStr === 'null') {
                            return '<span class="text-gray-400">-</span>';
                        }

                        // Si contiene comas, es múltiple
                        if (dataStr.includes(',')) {
                            const valores = dataStr.split(',').map(v => v.trim()).filter(v => v !== '' && v !== 'null');
                            return valores.map(valor =>
                                `<span class="badge bg-primary me-1" style="font-size: 11px; margin-right: 3px;">${escapeHtml(valor)}</span>`
                            ).join('');
                        }

                        return `<span class="badge bg-primary" style="font-size: 11px;">${escapeHtml(dataStr)}</span>`;
                    }
                },
                {
                    data: 'modalidad_egreso',
                    name: 'modalidad_egreso',
                    render: function (data, type, row) {
                        // Para exportación a Excel, devolver el texto con comas
                        if (type === 'export' || type === 'type') {
                            return data || '';
                        }

                        if (!data || data === '-' || data === null || data === undefined || data === 'null') {
                            return '<span class="text-gray-400">-</span>';
                        }

                        // Convertir a string por si acaso
                        const dataStr = String(data).trim();

                        if (dataStr === '' || dataStr === 'null') {
                            return '<span class="text-gray-400">-</span>';
                        }

                        // Si contiene comas, es múltiple
                        if (dataStr.includes(',')) {
                            const valores = dataStr.split(',').map(v => v.trim()).filter(v => v !== '' && v !== 'null');
                            return valores.map(valor =>
                                `<span class="badge bg-success me-1" style="font-size: 11px; margin-right: 3px;">${escapeHtml(valor)}</span>`
                            ).join('');
                        }

                        return `<span class="badge bg-success" style="font-size: 11px;">${escapeHtml(dataStr)}</span>`;
                    }
                },
                {
                    data: 'tipo_egreso',
                    name: 'tipo_egreso',
                    render: function (data, type, row) {
                        // Para exportación a Excel, devolver el texto con comas
                        if (type === 'export' || type === 'type') {
                            return data || '';
                        }

                        if (!data || data === '-' || data === null || data === undefined || data === 'null') {
                            return '<span class="text-gray-400">-</span>';
                        }

                        // Convertir a string por si acaso
                        const dataStr = String(data).trim();

                        if (dataStr === '' || dataStr === 'null') {
                            return '<span class="text-gray-400">-</span>';
                        }

                        // Si contiene comas, es múltiple
                        if (dataStr.includes(',')) {
                            const valores = dataStr.split(',').map(v => v.trim()).filter(v => v !== '' && v !== 'null');
                            return valores.map(valor =>
                                `<span class="badge bg-warning me-1" style="font-size: 11px; margin-right: 3px;">${escapeHtml(valor)}</span>`
                            ).join('');
                        }

                        return `<span class="badge bg-warning" style="font-size: 11px;">${escapeHtml(dataStr)}</span>`;
                    }
                },
                {
                    data: 'medico_asignado',
                    name: 'medico_asignado',
                    render: function (data) {
                        return data ? `<span class="font-mono text-sm">${escapeHtml(data)}</span>` : '<span class="text-gray-400">-</span>';
                    }
                },
                {
                    data: 'dias_reposo',
                    name: 'dias_reposo',
                    className: 'text-center',
                    render: function (data) {
                        return data ? `<span class="font-mono text-sm">${escapeHtml(data)}</span>` : '<span class="text-gray-400">0</span>';
                    }
                },
                {
                    data: 'observaciones_egreso',
                    name: 'observaciones_egreso',
                    render: function (data) {
                        if (!data || data === '-') return '<span class="text-gray-400">-</span>';

                        // Formatear texto con saltos de línea cada 40 caracteres
                        const formatearTexto = (texto) => {
                            if (texto.length <= 40) return escapeHtml(texto);

                            const palabras = texto.split(' ');
                            let lineas = [];
                            let lineaActual = '';

                            palabras.forEach(palabra => {
                                if ((lineaActual + palabra).length <= 40) {
                                    lineaActual += (lineaActual ? ' ' : '') + palabra;
                                } else {
                                    if (lineaActual) lineas.push(lineaActual);
                                    lineaActual = palabra;
                                }
                            });

                            if (lineaActual) lineas.push(lineaActual);

                            return lineas.map(linea => escapeHtml(linea)).join('<br>');
                        };

                        return `<div style="max-width: 200px; font-size: 12px; line-height: 1.3;">${formatearTexto(data)}</div>`;
                    }
                },
                // Columna 23 - Diagnósticos Presuntivos
                {
                    data: 'diagnosticos_presuntivos',
                    name: 'diagnosticos_presuntivos',
                    orderable: false,
                    render: function (data, type, row) {
                        // Para exportación, devolver texto plano
                        if (type === 'export' || type === 'type') {
                            if (!data || !Array.isArray(data)) return '';
                            return data.map(d => `${d.diagp_descripcion || ''} ${d.diagp_cie ? '(' + d.diagp_cie + ')' : ''}`).join('; ');
                        }

                        // Para display normal, mostrar los inputs
                        if (!data) data = [];
                        let html = '<div class="diagnosticos-container" style="min-width: 250px;">';

                        for (let i = 0; i < 3; i++) {
                            const diag = data[i] || {};
                            html += `
                                <div style="display: flex; gap: 5px; margin-bottom: 3px;">
                                    <input type="text" class="diag-desc"
                                           placeholder="Diagnóstico ${i + 1}"
                                           value="${escapeHtml(diag.diagp_descripcion || '')}"
                                           style="flex: 1; font-size: 11px; padding: 2px;"
                                           disabled>
                                    <input type="text" class="diag-cie"
                                           placeholder="CIE"
                                           value="${escapeHtml(diag.diagp_cie || '')}"
                                           style="width: 50px; font-size: 11px; padding: 2px;"
                                           disabled>
                                </div>
                            `;
                        }

                        html += '</div>';
                        return html;
                    }
                },
                // Columna 24 - Diagnósticos Definitivos
                {
                    data: 'diagnosticos_definitivos',
                    name: 'diagnosticos_definitivos',
                    orderable: false,
                    render: function (data, type, row) {
                        // Para exportación, devolver texto plano
                        if (type === 'export' || type === 'type') {
                            if (!data || !Array.isArray(data)) return '';
                            return data.map(d => `${d.diagd_descripcion || ''} ${d.diagd_cie ? '(' + d.diagd_cie + ')' : ''}`).join('; ');
                        }

                        // Para display normal, mostrar los inputs
                        if (!data) data = [];
                        let html = '<div class="diagnosticos-def-container" style="min-width: 250px;">';

                        for (let i = 0; i < 3; i++) {
                            const diag = data[i] || {};
                            html += `
                                <div style="display: flex; gap: 5px; margin-bottom: 3px;">
                                    <input type="text" class="diag-desc-def"
                                           placeholder="Diagnóstico ${i + 1}"
                                           value="${escapeHtml(diag.diagd_descripcion || '')}"
                                           style="flex: 1; font-size: 11px; padding: 2px;"
                                           disabled>
                                    <input type="text" class="diag-cie-def"
                                           placeholder="CIE"
                                           value="${escapeHtml(diag.diagd_cie || '')}"
                                           style="width: 50px; font-size: 11px; padding: 2px;"
                                           disabled>
                                </div>
                            `;
                        }

                        html += '</div>';
                        return html;
                    }
                },
                // Columna 25 - Acciones
                {
                    data: null,
                    orderable: false,
                    searchable: false,
                    className: 'no-export', // Agregar esta clase
                    render: function (data, type, row) {
                        // Si es para exportación, retornar vacío
                        if (type === 'export') {
                            return '';
                        }

                        const modificacionesRestantes = (row.modificaciones_permitidas || 3) - (row.modificaciones_usadas || 0);
                        const puedeModificar = modificacionesRestantes > 0;

                        return `
                            <div style="min-width: 100px; text-align: center;">
                                <button class="btn-habilitar"
                                        data-ate="${row.ate_codigo || ''}"
                                        style="padding: 4px 8px; font-size: 12px; background: ${puedeModificar ? '#0d6efd' : '#6c757d'}; color: white; border: none; border-radius: 3px; cursor: ${puedeModificar ? 'pointer' : 'not-allowed'}"
                                        ${!puedeModificar ? 'disabled' : ''}>
                                    ✏️ Habilitar
                                </button>
                                <button class="btn-guardar"
                                        data-ate="${row.ate_codigo || ''}"
                                        style="display: none; padding: 4px 8px; font-size: 12px; background: #198754; color: white; border: none; border-radius: 3px; cursor: pointer">
                                    💾 Guardar
                                </button>
                                <div style="margin-top: 3px; font-size: 11px; color: #666;">
                                    🔄 ${row.modificaciones_usadas || 0}/3
                                </div>
                            </div>
                        `;
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
            // Configuración mejorada de scroll
            scrollX: true,
            scrollY: '500px',
            scrollCollapse: true,
            fixedHeader: false,
            autoWidth: false,
            responsive: false,
            dom: '<"flex flex-wrap items-center justify-between mb-4"<"flex items-center space-x-4"lB><"flex items-center space-x-2"f>>rtip',
            buttons: [
                {
                    extend: 'excel',
                    text: '<i class="fas fa-file-excel mr-1"></i> Excel',
                    className: 'bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg text-sm',
                    title: 'Reporte_Especialidades_' + new Date().toISOString().split('T')[0],
                    exportOptions: {
                        columns: ':visible:not(.no-export)',
                        modifier: {
                            page: 'current'
                        },
                        format: {
                            body: function (data, row, column, node) {
                                let cleanData = data;

                                if (typeof data === 'string' && data.includes('<')) {
                                    let tempDiv = document.createElement('div');
                                    tempDiv.innerHTML = data;
                                    cleanData = tempDiv.textContent || tempDiv.innerText || '';
                                }

                                if (column === 23 || column === 24) {
                                    let diagnosticos = [];
                                    $(node).find('input').each(function () {
                                        let valor = $(this).val();
                                        if (valor && valor.trim() !== '') {
                                            diagnosticos.push(valor.trim());
                                        }
                                    });
                                    return diagnosticos.join('; ');
                                }

                                return cleanData;
                            }
                        }
                    }
                },
            ],
            initComplete: function () {
                // Ajustar el ancho de las columnas después de cargar
                this.api().columns.adjust();
            }
        });

        // Guardar referencia en el estado global similar al otro archivo
        if (!window.PanelState) {
            window.PanelState = {};
        }
        window.PanelState.reportesDataTable = tabla;

    } catch (error) {
        console.error('Error inicializando DataTable:', error);
        showAlert('Error al inicializar la tabla: ' + error.message, 'error');

        // Mostrar error en la tabla
        const tbody = document.querySelector('#tablaReportes tbody');
        if (tbody) {
            tbody.innerHTML = `
                <tr>
                    <td colspan="26" class="px-6 py-4 text-center text-red-600">
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
        }, false); // false = no resetear paginación
    }
}

function limpiarFiltros() {
    $('#fechaInicio').val('');
    $('#fechaFin').val('');
    $('#filtroEspecialidad').val('todas');
    $('#filtroEstado').val('todos');
    aplicarFiltros();
}

function actualizarEstadisticas(datos) {
    if (!Array.isArray(datos)) {
        console.warn('Datos no válidos para estadísticas:', datos);
        datos = [];
    }

    const total = datos.length;
    const completadas = datos.filter(d => d && d.estado === 'COMPLETADA').length;
    const enAtencion = datos.filter(d => d && (d.estado === 'EN_ATENCION' || d.estado === 'EN_PROCESO')).length;
    const afiliados = datos.filter(d => d && d.paciente_afiliado === 'Sí').length;
    const porcentajeAfiliados = total > 0 ? Math.round((afiliados / total) * 100) : 0;

    $('#totalAtenciones').text(total.toLocaleString());
    $('#totalCompletadas').text(completadas.toLocaleString());
    $('#totalEnAtencion').text(enAtencion.toLocaleString());
    $('#porcentajeAfiliados').text(porcentajeAfiliados + '%');
}

function actualizarContadorRegistros(total) {
    $('#contadorRegistros').text(`${total.toLocaleString()} registros encontrados`);
}

function mostrarCarga(mostrar) {
    const overlay = $('#loadingOverlay');
    if (mostrar) {
        overlay.addClass('active');
    } else {
        overlay.removeClass('active');
    }
}

// Función para refrescar la tabla
function refreshReportesTable() {
    if (window.PanelState && window.PanelState.reportesDataTable) {
        window.PanelState.reportesDataTable.ajax.reload(null, false);
    }
}

// Función para destruir la tabla cuando se cambie de vista
function destroyReportesTable() {
    if (window.PanelState && window.PanelState.reportesDataTable) {
        try {
            window.PanelState.reportesDataTable.destroy();
            window.PanelState.reportesDataTable = null;
        } catch (error) {
            window.PanelState.reportesDataTable = null;
        }
    }
}

// Event Handlers
$(document).on('click', '.btn-habilitar', function () {
    const row = $(this).closest('tr');

    // Habilitar todos los inputs de diagnóstico en esta fila
    row.find('.diag-desc, .diag-cie, .diag-desc-def, .diag-cie-def')
        .prop('disabled', false)
        .css('background', 'white');

    // Cambiar botones
    $(this).hide();
    row.find('.btn-guardar').show();
});

// Evento para guardar - Versión con debugging mejorado
$(document).on('click', '.btn-guardar', function () {
    const ate_codigo = $(this).data('ate');
    const row = $(this).closest('tr');
    const btn = $(this);

    // Validar que tenemos el código de atención
    if (!ate_codigo || ate_codigo === 'undefined' || ate_codigo === '') {
        showAlert('Error: No se encontró el código de atención válido', 'error');
        console.error('ate_codigo inválido:', ate_codigo);
        return;
    }

    btn.prop('disabled', true).text('Guardando...');

    const diagnosticos_presuntivos = [];
    row.find('.diagnosticos-container').find('div').each(function () {
        const desc = $(this).find('.diag-desc').val();
        const cie = $(this).find('.diag-cie').val();

        // Solo agregar si hay descripción
        if (desc && desc.trim() !== '') {
            diagnosticos_presuntivos.push({
                descripcion: desc.trim(),
                cie: cie ? cie.trim() : ''
            });
        }
    });

    const diagnosticos_definitivos = [];
    row.find('.diagnosticos-def-container').find('div').each(function () {
        const desc = $(this).find('.diag-desc-def').val();
        const cie = $(this).find('.diag-cie-def').val();

        // Solo agregar si hay descripción
        if (desc && desc.trim() !== '') {
            diagnosticos_definitivos.push({
                descripcion: desc.trim(),
                cie: cie ? cie.trim() : ''
            });
        }
    });

    // Validar que hay al menos un diagnóstico
    if (diagnosticos_presuntivos.length === 0 && diagnosticos_definitivos.length === 0) {
        showAlert('Error: Debe ingresar al menos un diagnóstico', 'warning');
        btn.prop('disabled', false).text('💾 Guardar');
        return;
    }

    // Enviar al servidor
    $.ajax({
        url: baseUrl + 'guardarDiagnosticos',
        method: 'POST',
        dataType: 'json',
        data: {
            ate_codigo: ate_codigo,
            diagnosticos_presuntivos: diagnosticos_presuntivos,
            diagnosticos_definitivos: diagnosticos_definitivos
        },
        success: function (response) {
            if (response.success) {
                showAlert('Diagnósticos guardados correctamente', 'success');

                // Recargar tabla después de 1 segundo
                setTimeout(() => {
                    if (typeof tabla !== 'undefined' && tabla.ajax) {
                        tabla.ajax.reload(null, false);
                    }
                }, 1000);
            } else {
                console.error('Error en respuesta:', response);
                showAlert(response.error || 'Error al guardar', 'error');
            }
        },
        error: function (xhr, status, error) {
            console.error('Error AJAX completo:', {
                status: status,
                error: error,
                xhr: xhr,
                responseText: xhr.responseText,
                responseJSON: xhr.responseJSON
            });

            let errorMsg = 'Error de conexión';

            // Intentar obtener mensaje de error más específico
            try {
                const response = JSON.parse(xhr.responseText);
                if (response.error) {
                    errorMsg = response.error;
                }
            } catch (e) {
                if (xhr.status === 500) {
                    errorMsg = 'Error interno del servidor';
                } else if (xhr.status === 404) {
                    errorMsg = 'Endpoint no encontrado';
                } else if (xhr.status === 403) {
                    errorMsg = 'No autorizado';
                } else {
                    errorMsg += ': ' + error;
                }
            }

            showAlert(errorMsg, 'error');
        },
        complete: function () {
            // Re-habilitar el botón siempre
            btn.prop('disabled', false).text('💾 Guardar');
        }
    });
});

// Funciones auxiliares
function escapeHtml(text) {
    if (!text) return '';
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

function showAlert(message, type = 'info') {
    // Crear elemento de alerta
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

    // Auto-remover después de 5 segundos
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

// Función para agrupar datos por ate_codigo y consolidar múltiples checkboxes
function agruparDatosPorAteCodigo(datos) {
    const grupos = {};

    datos.forEach(fila => {
        const ateKey = fila.ate_codigo;

        if (!grupos[ateKey]) {
            // Primera vez que vemos este ate_codigo, crear base
            grupos[ateKey] = { ...fila };
            grupos[ateKey].estado_egreso_valores = [];
            grupos[ateKey].modalidad_egreso_valores = [];
            grupos[ateKey].tipo_egreso_valores = [];
        }

        // Recopilar valores de estado_egreso
        if (fila.estado_egreso && fila.estado_egreso !== null && fila.estado_egreso !== 'null') {
            if (!grupos[ateKey].estado_egreso_valores.includes(fila.estado_egreso)) {
                grupos[ateKey].estado_egreso_valores.push(fila.estado_egreso);
            }
        }

        // Recopilar valores de modalidad_egreso
        if (fila.modalidad_egreso && fila.modalidad_egreso !== null && fila.modalidad_egreso !== 'null') {
            if (!grupos[ateKey].modalidad_egreso_valores.includes(fila.modalidad_egreso)) {
                grupos[ateKey].modalidad_egreso_valores.push(fila.modalidad_egreso);
            }
        }

        // Recopilar valores de tipo_egreso
        if (fila.tipo_egreso && fila.tipo_egreso !== null && fila.tipo_egreso !== 'null') {
            if (!grupos[ateKey].tipo_egreso_valores.includes(fila.tipo_egreso)) {
                grupos[ateKey].tipo_egreso_valores.push(fila.tipo_egreso);
            }
        }
    });

    // Convertir arrays a strings con comas
    return Object.values(grupos).map(grupo => {
        grupo.estado_egreso = grupo.estado_egreso_valores.length > 0
            ? grupo.estado_egreso_valores.join(',')
            : grupo.estado_egreso;

        grupo.modalidad_egreso = grupo.modalidad_egreso_valores.length > 0
            ? grupo.modalidad_egreso_valores.join(',')
            : grupo.modalidad_egreso;

        grupo.tipo_egreso = grupo.tipo_egreso_valores.length > 0
            ? grupo.tipo_egreso_valores.join(',')
            : grupo.tipo_egreso;

        // Limpiar arrays temporales
        delete grupo.estado_egreso_valores;
        delete grupo.modalidad_egreso_valores;
        delete grupo.tipo_egreso_valores;

        return grupo;
    });
}

// Función global para cerrar sesión
function cerrarSesionReportes() {
    if (confirm('¿Está seguro que desea cerrar la sesión de reportes?')) {
        window.location.href = (BASE_URL || window.location.origin + '/') + 'especialidades/reportes/cerrarSesion';
    }
}