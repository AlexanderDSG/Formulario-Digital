<!-- Header de Reportes -->
<div class="bg-white rounded-lg shadow-md mb-6">
    <div class="bg-gradient-to-r from-green-600 to-green-700 text-white rounded-t-lg px-6 py-4">
        <div class="flex justify-between items-center">
            <div>
                <h1 class="text-2xl font-bold flex items-center">
                    <i class="fas fa-chart-bar mr-3"></i>
                    Reportes Administrativos
                </h1>
                <p class="text-green-100 text-sm mt-1">
                    Sistema de reportes y estadísticas administrativas - Formulario Digital
                </p>
            </div>
            <div class="text-right">
                <p class="text-sm">
                    <i class="fas fa-user mr-1"></i>
                    <?= esc($usuario_reportes) ?>
                </p>
                <p class="text-xs text-green-100">
                    <?= esc($rol_reportes) ?>
                </p>
                <button onclick="goToDashboard()"
                        class="mt-2 bg-blue-500 hover:bg-blue-600 text-white px-3 py-1 rounded text-xs">
                    <i class="fas fa-arrow-left mr-1"></i>
                    Volver al Panel
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Estadísticas rápidas -->
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4 mb-6">
    <div class="bg-white p-4 rounded-lg shadow-md">
        <div class="flex items-center justify-between">
            <div>
                <h3 class="text-sm font-medium text-gray-500">Total Pacientes</h3>
                <p id="totalPacientes" class="text-2xl font-bold text-gray-800">0</p>
            </div>
            <div class="p-2 bg-blue-100 rounded-full">
                <i class="fas fa-users text-blue-600"></i>
            </div>
        </div>
    </div>

    <div class="bg-white p-4 rounded-lg shadow-md">
        <div class="flex items-center justify-between">
            <div>
                <h3 class="text-sm font-medium text-gray-500">Pacientes Afiliados</h3>
                <p id="pacientesAfiliados" class="text-2xl font-bold text-green-600">0%</p>
            </div>
            <div class="p-2 bg-green-100 rounded-full">
                <i class="fas fa-shield-alt text-green-600"></i>
            </div>
        </div>
    </div>

    <div class="bg-white p-4 rounded-lg shadow-md">
        <div class="flex items-center justify-between">
            <div>
                <h3 class="text-sm font-medium text-gray-500">Embarazadas</h3>
                <p id="totalEmbarazadas" class="text-2xl font-bold text-pink-600">0</p>
            </div>
            <div class="p-2 bg-pink-100 rounded-full">
                <i class="fas fa-baby text-pink-600"></i>
            </div>
        </div>
    </div>

    <div class="bg-white p-4 rounded-lg shadow-md">
        <div class="flex items-center justify-between">
            <div>
                <h3 class="text-sm font-medium text-gray-500">Grupo Prioritario</h3>
                <p id="grupoPrioritario" class="text-2xl font-bold text-orange-600">0</p>
            </div>
            <div class="p-2 bg-orange-100 rounded-full">
                <i class="fas fa-exclamation-triangle text-orange-600"></i>
            </div>
        </div>
    </div>

    <div class="bg-white p-4 rounded-lg shadow-md">
        <div class="flex items-center justify-between">
            <div>
                <h3 class="text-sm font-medium text-gray-500">Registros Encontrados</h3>
                <p id="contadorRegistros" class="text-2xl font-bold text-gray-800">0</p>
            </div>
            <div class="p-2 bg-purple-100 rounded-full">
                <i class="fas fa-list text-purple-600"></i>
            </div>
        </div>
    </div>
</div>

<!-- Filtros -->
<div class="bg-white rounded-lg shadow-md mb-6 p-6">
    <h3 class="text-lg font-semibold mb-4 flex items-center">
        <i class="fas fa-filter mr-2 text-blue-600"></i>
        Filtros de Reporte
    </h3>

    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <div>
            <label for="fechaInicio" class="block text-sm font-medium text-gray-700 mb-1">
                Fecha Inicio
            </label>
            <input type="date"
                   id="fechaInicio"
                   class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
        </div>

        <div>
            <label for="fechaFin" class="block text-sm font-medium text-gray-700 mb-1">
                Fecha Fin
            </label>
            <input type="date"
                   id="fechaFin"
                   class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
        </div>

        <div>
            <label for="filtroEstado" class="block text-sm font-medium text-gray-700 mb-1">
                Estado de Triaje
            </label>
            <select id="filtroEstado"
                    class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                <option value="todos">Todos los estados</option>
                <option value="ROJO">Rojo</option>
                <option value="NARANJA">Naranja</option>
                <option value="AMARILLO">Amarillo</option>
                <option value="VERDE">Verde</option>
                <option value="AZUL">Azul</option>
            </select>
        </div>

        <div class="flex items-end space-x-2">
            <button id="btnAplicarFiltros"
                    class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-md">
                <i class="fas fa-search mr-1"></i>
                Aplicar
            </button>
            <button id="btnLimpiarFiltros"
                    class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-md">
                <i class="fas fa-times mr-1"></i>
                Limpiar
            </button>
        </div>
    </div>
</div>

<!-- Tabla de datos -->
<div class="bg-white rounded-lg shadow-md p-6">
    <h3 class="text-lg font-semibold mb-4 flex items-center">
        <i class="fas fa-table mr-2 text-blue-600"></i>
        Pacientes Registrados
    </h3>

    <div class="overflow-x-auto">
        <table id="tablaReportesAdmin" class="table table-striped" style="width:100%">
            <thead>
                <tr>
                    <th>Fecha</th>
                    <th>Hora</th>
                    <th>Paciente</th>
                    <th>Cédula</th>
                    <th>Sexo</th>
                    <th>Edad</th>
                    <th>Triaje</th>
                    <th>Afiliado</th>
                    <th>Grupo Prioritario</th>
                    <th>Seguro</th>
                    <th>Nacionalidad</th>
                    <th>Etnia</th>
                    <th>Nacionalidad Indígena</th>
                    <th>Pueblo Indígena</th>
                    <th>Embarazada</th>
                    <th>Establecimiento Ingreso</th>
                    <th>Establecimiento Egreso</th>
                                
                </tr>
            </thead>
            <tbody>
                <!-- Los datos se cargan via AJAX -->
            </tbody>
        </table>
    </div>
</div>

<!-- Loading overlay para reportes -->
<div id="loadingOverlayReportes" class="fixed inset-0 bg-black bg-opacity-50 z-50 hidden items-center justify-center">
    <div class="text-center text-white">
        <div class="animate-spin rounded-full h-32 w-32 border-b-2 border-white mx-auto mb-4"></div>
        <p class="text-lg">Cargando datos...</p>
    </div>
</div>