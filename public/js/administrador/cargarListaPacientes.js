/**
 * MÓDULO DE GESTIÓN DE PACIENTES
 * Maneja la visualización de pacientes con formularios completos
 */

// Función para mostrar pacientes con historial completo
function showPatients() {
    
    const dashboardContent = document.getElementById('dashboard-content');
    const userTableContent = document.getElementById('user-table-content');
    
    // Destruir tabla de pacientes existente si existe
    if (window.PanelState && window.PanelState.patientsDataTable) {
        try {
            window.PanelState.patientsDataTable.destroy();
            window.PanelState.patientsDataTable = null;
        } catch (error) {
            window.PanelState.patientsDataTable = null;
        }
    }
    
    // Ocultar la tabla de usuarios
    if (userTableContent) {
        userTableContent.classList.add('hidden');
    }
    
    // Mostrar el dashboard content
    if (dashboardContent) {
        dashboardContent.classList.remove('hidden');
        
        // Reemplazar contenido con la vista de pacientes
        dashboardContent.innerHTML = `
            <!-- Header de Pacientes Registrados -->
            <div class="bg-white rounded-lg shadow-md mb-6">
                <div class="bg-gradient-to-r from-blue-600 to-blue-700 text-white rounded-t-lg px-6 py-4">
                    <div class="flex justify-between items-center">
                        <div>
                            <h1 class="text-2xl font-bold flex items-center">
                                <i class="fas fa-user-injured mr-3"></i>
                                Pacientes Registrados
                            </h1>
                            <p class="text-blue-100 text-sm mt-1">
                                Historial completo de pacientes con formularios médicos - Formulario Digital
                            </p>
                        </div>
                        <div class="text-right">
                            <button onclick="abrirReportesAdmin()"
                                    class="bg-green-500 hover:bg-green-600 text-white px-3 py-1 rounded text-xs mb-2">
                                <i class="fas fa-chart-bar mr-1"></i>
                                Reportes
                            </button>
                            <button onclick="goToDashboard()"
                                    class="bg-indigo-500 hover:bg-indigo-600 text-white px-3 py-1 rounded text-xs block w-full">
                                <i class="fas fa-arrow-left mr-1"></i>
                                Volver al Panel
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tabla de pacientes -->
            <div class="bg-white rounded-lg shadow-md p-6">
                <h3 class="text-lg font-semibold mb-4 flex items-center">
                    <i class="fas fa-table mr-2 text-blue-600"></i>
                    Historial de Pacientes Registrados
                </h3>

                <div class="overflow-x-auto">
                    <table id="tablaHistorialPacientes" class="table table-striped" style="width:100%">
                        <thead>
                            <tr>
                                <th>Historia Clínica</th>
                                <th>Cédula</th>
                                <th>Apellidos</th>
                                <th>Nombres</th>
                                <th>Última Atención</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td colspan="6" class="text-center">
                                    <div class="flex items-center justify-center py-4">
                                        <i class="fas fa-spinner fa-spin mr-2"></i>
                                        <span>Inicializando tabla...</span>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
            
            <!-- Loading overlay -->
            <div id="patients-loading" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center">
                <div class="bg-white p-6 rounded-lg shadow-lg flex items-center space-x-3">
                    <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600"></div>
                    <span class="text-gray-700">Cargando formulario...</span>
                </div>
            </div>
        `;
        
        // Establecer vista actual
        if (window.PanelState) {
            window.PanelState.currentView = 'patients';
        }
        
        // Inicializar DataTable después de insertar el HTML con un pequeño delay
        setTimeout(() => {
            initializePatientsDataTable();
        }, 200);
    } else {
        console.error('No se encontró el elemento dashboard-content');
    }
}


// Función para inicializar DataTable con server-side processing
function initializePatientsDataTable() {
    
    // Verificar que la tabla existe en el DOM
    const tableElement = document.getElementById('tablaHistorialPacientes');
    if (!tableElement) {
        console.error('Elemento tabla no encontrado');
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
        
        // Inicializar DataTable con configuración server-side y estilos TailwindCSS
        const patientsDataTable = $('#tablaHistorialPacientes').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: BASE_URL + '/administrador/historial/ajaxPacientes',
                type: 'GET',
                error: function(xhr, error, thrown) {
                    console.error('Error en AJAX DataTables:', error, thrown);
                    console.error('Response status:', xhr.status);
                    console.error('Response text:', xhr.responseText);
                    showAlert('Error al cargar los datos de pacientes: ' + error, 'error');
                }
            },
            columns: [
                { 
                    data: 'pac_his_cli',
                    name: 'pac_his_cli',
                    render: function(data, type, row) {
                        return data ? `<span class="font-mono text-sm bg-gray-100 px-2 py-1 rounded">${escapeHtml(data)}</span>` : '<span class="text-gray-400">-</span>';
                    }
                },
                { 
                    data: 'pac_cedula',
                    name: 'pac_cedula',
                    render: function(data, type, row) {
                        return data ? `<span class="font-mono text-sm">${escapeHtml(data)}</span>` : '<span class="text-gray-400">-</span>';
                    }
                },
                { 
                    data: 'pac_apellidos',
                    name: 'pac_apellidos',
                    render: function(data, type, row) {
                        return data ? `<span class="font-mono text-sm">${escapeHtml(data)}</span>` : '<span class="text-gray-400">-</span>';
                    }
                },
                { 
                    data: 'pac_nombres',
                    name: 'pac_nombres',
                    render: function(data, type, row) {
                        return data ? `<span class="font-mono text-sm">${escapeHtml(data)}</span>` : '<span class="text-gray-400">-</span>';
                    }
                },
                {
                    data: 'ultima_atencion',
                    name: 'ultima_atencion',
                    render: function(data, type, row) {
                        if (data) {
                            // ✅ CORREGIR INTERPRETACIÓN DE FECHA PARA EVITAR PROBLEMAS DE ZONA HORARIA
                            let fechaFormatada = '';

                            try {
                                // Si la fecha viene en formato YYYY-MM-DD, agregarle la hora para evitar problemas de timezone
                                if (data.match(/^\d{4}-\d{2}-\d{2}$/)) {
                                    // Solo fecha, agregar hora para evitar shift de timezone
                                    const fecha = new Date(data + 'T00:00:00');
                                    fechaFormatada = fecha.toLocaleDateString('es-ES');
                                } else {
                                    // Fecha con hora o formato diferente
                                    const fecha = new Date(data);
                                    fechaFormatada = fecha.toLocaleDateString('es-ES');
                                }
                            } catch (error) {
                                console.warn('Error parseando fecha:', data, error);
                                // Fallback: mostrar fecha como viene de la BD si no se puede parsear
                                fechaFormatada = data;
                            }

                            return `<span class="font-mono text-sm">${fechaFormatada}</span>`;
                        }
                        return '<span class="text-gray-400">-</span>';
                    }
                },
                { 
                    data: null,
                    orderable: false,
                    searchable: false,
                    render: function(data, type, row) {
                        const identificador = row.pac_cedula || row.pac_his_cli;
                        
                        if (!identificador) {
                            return '<span class="text-gray-400">Sin identificador</span>';
                        }
                        
                        return `
                            <div class="flex flex-wrap gap-1">
                                <button onclick="verVistaDual('${identificador}')"
                                        class="bg-blue-600 hover:bg-blue-700 text-white px-3 py-2 rounded text-sm transition-colors duration-200 flex items-center space-x-2"
                                        title="Ver formulario completo del paciente">
                                    <i class="fas fa-eye text-sm"></i>
                                    <span>Ver Formulario</span>
                                </button>
                            </div>
                        `;
                    }
                }
            ],
            pageLength: 10,
            lengthMenu: [[10, 25, 50, 100], [10, 25, 50, 100]],
            order: [[4, 'desc']], // Ordenar por última atención descendente
            language: {
                processing: "Procesando...",
                search: "Buscar:",
                lengthMenu: "Mostrar _MENU_ registros",
                info: "Mostrando del _START_ al _END_ de _TOTAL_ registros",
                infoEmpty: "Mostrando del 0 al 0 de 0 registros",
                infoFiltered: "(filtrado de _MAX_ registros)",
                loadingRecords: "Cargando...",
                zeroRecords: "No se encontraron registros",
                emptyTable: "No hay pacientes con formulario completo",
                paginate: {
                    first: "Primero",
                    previous: "Anterior",
                    next: "Siguiente",
                    last: "Último"
                }
            },
            // Configuración mejorada de scroll y layout
            scrollX: true,
            scrollY: '500px',
            scrollCollapse: true,
            fixedHeader: false,
            autoWidth: false,
            responsive: false,
            dom: '<"flex flex-wrap items-center justify-between mb-4"<"flex items-center space-x-4"l><"flex items-center space-x-2"f>>rtip',

            drawCallback: function() {
                aplicarEstilosTabla()
            },
            initComplete: function () {
                this.api().columns.adjust();
            }
        });
        
        // Guardar referencia en el estado global
        if (window.PanelState) {
            window.PanelState.patientsDataTable = patientsDataTable;
        }
        
    } catch (error) {
        console.error('Error inicializando DataTable:', error);
        showAlert('Error al inicializar la tabla de pacientes: ' + error.message, 'error');
        
        // Mostrar error en la tabla
        const tbody = document.querySelector('#tablaHistorialPacientes tbody');
        if (tbody) {
            tbody.innerHTML = `
                <tr>
                    <td colspan="6" class="px-6 py-4 text-center text-red-600">
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

function aplicarEstilosTabla() {
    // Aplicar estilos específicos de TailwindCSS a elementos de DataTables
    const wrapper = document.querySelector('#tablaHistorialPacientes_wrapper');
    if (wrapper) {
        // Aplicar estilos mejorados a controles de DataTables
        const searchInput = wrapper.querySelector('input[type="search"]');
        if (searchInput) {
            searchInput.className = 'w-full px-4 py-2 border-2 border-gray-200 rounded-lg focus:border-blue-500 focus:ring-2 focus:ring-blue-100 transition-all duration-200 bg-gray-50 focus:bg-white text-gray-800 placeholder-gray-500 text-sm';
            searchInput.placeholder = 'Buscar pacientes...';
        }

        const lengthSelect = wrapper.querySelector('select');
        if (lengthSelect) {
            lengthSelect.className = 'px-3 py-2 border-2 border-gray-200 rounded-lg focus:border-blue-500 focus:ring-2 focus:ring-blue-100 transition-all duration-200 bg-gray-50 focus:bg-white text-gray-800 text-sm';
        }

        // Estilos para los botones de paginación
        const paginateButtons = wrapper.querySelectorAll('.paginate_button');
        paginateButtons.forEach(button => {
            if (!button.classList.contains('disabled') && !button.classList.contains('current')) {
                button.className = 'paginate_button bg-white hover:bg-gray-50 text-gray-700 border border-gray-300 px-3 py-2 rounded-md text-sm transition-colors duration-200 mx-1';
            } else if (button.classList.contains('current')) {
                button.className = 'paginate_button current bg-blue-600 text-white border border-blue-600 px-3 py-2 rounded-md text-sm mx-1';
            } else if (button.classList.contains('disabled')) {
                button.className = 'paginate_button disabled bg-gray-100 text-gray-400 border border-gray-200 px-3 py-2 rounded-md text-sm mx-1 cursor-not-allowed';
            }
        });

        // Estilos para la información de la tabla
        const infoElement = wrapper.querySelector('.dataTables_info');
        if (infoElement) {
            infoElement.className = 'text-sm text-gray-600 font-medium';
        }

        // Aplicar estilos a las celdas de la tabla
        const table = document.querySelector('#tablaHistorialPacientes');
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
// Función para ver formulario completo del paciente
function verVistaDual(identificador) {
    if (!identificador) {
        showAlert('Identificador de paciente no válido', 'error');
        return;
    }

    // Mostrar loading
    const loadingElement = document.getElementById('patients-loading');
    if (loadingElement) {
        loadingElement.classList.remove('hidden');
    }

    // Redirigir al formulario completo
    const url = `${BASE_URL}administrador/formulario/dual/${encodeURIComponent(identificador)}`;
    window.location.href = url;
}

// Función para refrescar la tabla
function refreshPatientsTable() {
    if (window.PanelState && window.PanelState.patientsDataTable) {
        window.PanelState.patientsDataTable.ajax.reload(null, false); // false para mantener la posición actual
    }
}

// Función para limpiar la tabla cuando se cambie de vista
function destroyPatientsTable() {
    if (window.PanelState && window.PanelState.patientsDataTable) {
        try {
            window.PanelState.patientsDataTable.destroy();
            window.PanelState.patientsDataTable = null;
        } catch (error) {
            window.PanelState.patientsDataTable = null;
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
    switch(type) {
        case 'success': return 'bg-green-100 text-green-800 border border-green-300';
        case 'error': return 'bg-red-100 text-red-800 border border-red-300';
        case 'warning': return 'bg-yellow-100 text-yellow-800 border border-yellow-300';
        default: return 'bg-blue-100 text-blue-800 border border-blue-300';
    }
}

function getAlertIcon(type) {
    switch(type) {
        case 'success': return 'fa-check-circle';
        case 'error': return 'fa-exclamation-circle';
        case 'warning': return 'fa-exclamation-triangle';
        default: return 'fa-info-circle';
    }
}