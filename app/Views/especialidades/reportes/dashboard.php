<?php
// Configurar variables para el header
$title = 'Reportes de Especialidades Médicas';
?>

<?= $this->include('templates/header') ?>

<body class="bg-gray-100">
    <!-- Header -->
    <nav class="bg-green-600 text-white p-4 shadow-lg">
        <div class="container mx-auto flex justify-between items-center">
            <div class="flex items-center">
                <i class="fas fa-chart-line text-2xl mr-3"></i>
                <div>
                    <h1 class="text-xl font-bold">Reportes de Especialidades Médicas</h1>
                    <p class="text-green-200 text-sm">Sistema de Análisis y Reportes - Hospital San Vicente de Paúl</p>
                </div>
            </div>
            <div class="flex items-center space-x-4">
                <div class="text-right">
                    <p class="text-sm">Bienvenido</p>
                    <p class="font-semibold">Dr. Especialista</p>
                    <p class="text-xs text-green-200">MEDICO ESPECIALISTA</p>
                </div>
                <a href="#" onclick="cerrarSesionReportes()"
                    class="bg-red-500 hover:bg-red-600 px-3 py-2 rounded-lg text-sm transition-colors">
                    <i class="fas fa-sign-out-alt mr-1"></i> Salir
                </a>
            </div>
        </div>
    </nav>

    <div class="container mx-auto p-6">
        <!-- Estadísticas -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-6">
            <div class="stats-card bg-white rounded-lg shadow-md p-6">
                <div class="flex items-center">
                    <div class="bg-blue-500 text-white p-3 rounded-full">
                        <i class="fas fa-users text-xl"></i>
                    </div>
                    <div class="ml-4">
                        <h3 class="text-gray-500 text-sm">Total Atenciones</h3>
                        <p id="totalAtenciones" class="text-2xl font-bold text-gray-800">-</p>
                    </div>
                </div>
            </div>

            <div class="stats-card bg-white rounded-lg shadow-md p-6">
                <div class="flex items-center">
                    <div class="bg-green-500 text-white p-3 rounded-full">
                        <i class="fas fa-check-circle text-xl"></i>
                    </div>
                    <div class="ml-4">
                        <h3 class="text-gray-500 text-sm">Completadas</h3>
                        <p id="totalCompletadas" class="text-2xl font-bold text-gray-800">-</p>
                    </div>
                </div>
            </div>

            <div class="stats-card bg-white rounded-lg shadow-md p-6">
                <div class="flex items-center">
                    <div class="bg-yellow-500 text-white p-3 rounded-full">
                        <i class="fas fa-clock text-xl"></i>
                    </div>
                    <div class="ml-4">
                        <h3 class="text-gray-500 text-sm">En Atención</h3>
                        <p id="totalEnAtencion" class="text-2xl font-bold text-gray-800">-</p>
                    </div>
                </div>
            </div>

            <div class="stats-card bg-white rounded-lg shadow-md p-6">
                <div class="flex items-center">
                    <div class="bg-purple-500 text-white p-3 rounded-full">
                        <i class="fas fa-percentage text-xl"></i>
                    </div>
                    <div class="ml-4">
                        <h3 class="text-gray-500 text-sm">% Afiliados</h3>
                        <p id="porcentajeAfiliados" class="text-2xl font-bold text-gray-800">-%</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filtros -->
        <div class="filtros-card text-white rounded-lg shadow-lg p-6 mb-6">
            <h2 class="text-lg font-bold mb-4 flex items-center">
                <i class="fas fa-filter mr-2"></i>
                Filtros de Búsqueda
            </h2>

            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div>
                    <label class="block text-sm font-medium mb-2">Fecha Inicio</label>
                    <input type="date" id="fechaInicio"
                        class="w-full px-3 py-2 border border-gray-300 rounded-md text-gray-800 focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>

                <div>
                    <label class="block text-sm font-medium mb-2">Fecha Fin</label>
                    <input type="date" id="fechaFin"
                        class="w-full px-3 py-2 border border-gray-300 rounded-md text-gray-800 focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>

                <div>
                    <label class="block text-sm font-medium mb-2">Especialidad</label>
                    <select id="filtroEspecialidad"
                        class="w-full px-3 py-2 border border-gray-300 rounded-md text-gray-800 focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="todas">Todas las especialidades</option>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium mb-2">Estado</label>
                    <select id="filtroEstado"
                        class="w-full px-3 py-2 border border-gray-300 rounded-md text-gray-800 focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="todos">Todos los estados</option>
                        <option value="PENDIENTE">Pendiente</option>
                        <option value="EN_ATENCION">En Atención</option>
                        <option value="COMPLETADA">Completada</option>
                        <option value="ENVIADO_A_OBSERVACION">Enviado a Observación</option>
                        <option value="EN_PROCESO">En Proceso</option>
                    </select>
                </div>
            </div>

            <div class="flex justify-end mt-4 space-x-3">
                <button id="btnLimpiarFiltros"
                    class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-lg transition-colors">
                    <i class="fas fa-eraser mr-2"></i>Limpiar
                </button>
                <button id="btnAplicarFiltros"
                    class="bg-white hover:bg-gray-100 text-purple-700 px-4 py-2 rounded-lg font-medium transition-colors">
                    <i class="fas fa-search mr-2"></i>Aplicar Filtros
                </button>
            </div>
        </div>

        <!-- Tabla de Reportes -->
        <div class="bg-white rounded-lg shadow-lg overflow-hidden">
            <div class="p-6 border-b border-gray-200">
                <div class="flex justify-between items-center">
                    <div>
                        <h2 class="text-lg font-bold text-gray-800 flex items-center">
                            <i class="fas fa-table mr-2 text-blue-600"></i>
                            Reporte de Atenciones en Especialidades
                        </h2>
                        <p class="text-gray-600 text-sm mt-1">Matriz completa de datos con filtros dinámicos y
                            exportación</p>
                    </div>
                    <div class="text-sm text-gray-500" id="infoRegistros">
                        <i class="fas fa-info-circle mr-1"></i>
                        <span id="contadorRegistros">Cargando datos...</span>
                    </div>
                </div>
            </div>

            <div class="p-6">
                <div class="overflow-x-auto">
                    <table id="tablaReportes" class="table table-striped" style="width:100%">
                        <thead class="bg-gray-50">
                            <tr>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Fecha Ingreso</th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Hora Ingreso</th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Hora Atención</th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Hora Alta</th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Paciente</th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Cédula</th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Edad</th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Nacionalidad</th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Etnia</th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Pueblos Indígenas</th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Nacionalidad Indígenas</th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Triaje</th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Especialidad</th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Estado</th>

                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Afiliado</th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Grupo Prioritario</th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Seguro</th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Condición Alta</th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Modalidad Egreso</th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Tipo Egreso</th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Médico Asignado</th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Días Reposo</th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Observaciones</th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Diagnóstico Presuntivo</th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Diagnóstico Definitivo</th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Accion</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <tr>
                                <td colspan="26" class="px-6 py-4 text-center">
                                    <div class="flex items-center justify-center">
                                        <i class="fas fa-spinner fa-spin mr-2"></i>
                                        <span>Inicializando tabla...</span>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Loading overlay -->
    <div id="loadingOverlay" class="loading-overlay">
        <div class="bg-white p-6 rounded-lg shadow-lg flex items-center space-x-3">
            <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600"></div>
            <span class="text-gray-700">Cargando datos...</span>
        </div>
    </div>

<?= $this->include('templates/footer') ?>