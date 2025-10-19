<!-- Header de Control de Modificaciones -->
<div class="bg-white rounded-lg shadow-md mb-6">
    <div class="bg-gradient-to-r from-orange-600 to-orange-700 text-white rounded-t-lg px-6 py-4">
        <div class="flex justify-between items-center">
            <div>
                <h1 class="text-2xl font-bold flex items-center">
                    <i class="fas fa-edit mr-3"></i>
                    Control de Modificaciones de Formularios
                </h1>
                <p class="text-orange-100 text-sm mt-1">
                    Gestión y control de modificaciones en formularios médicos - Formulario Digital
                </p>
            </div>
            <div class="text-right">
                <p class="text-sm">
                    <i class="fas fa-user mr-1"></i>
                    <?= esc(session()->get('usu_nombre') . ' ' . session()->get('usu_apellido')) ?>
                </p>
                <p class="text-xs text-orange-100">
                    <?= esc(session()->get('rol_nombre')) ?>
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
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
    <div class="bg-white p-4 rounded-lg shadow-md">
        <div class="flex items-center justify-between">
            <div>
                <h3 class="text-sm font-medium text-gray-500">Total Formularios</h3>
                <p id="totalFormularios" class="text-2xl font-bold text-gray-800">0</p>
            </div>
            <div class="p-2 bg-blue-100 rounded-full">
                <i class="fas fa-file-medical text-blue-600"></i>
            </div>
        </div>
    </div>

    <div class="bg-white p-4 rounded-lg shadow-md">
        <div class="flex items-center justify-between">
            <div>
                <h3 class="text-sm font-medium text-gray-500">Pueden Modificar</h3>
                <p id="puedenModificar" class="text-2xl font-bold text-green-600">0</p>
            </div>
            <div class="p-2 bg-green-100 rounded-full">
                <i class="fas fa-edit text-green-600"></i>
            </div>
        </div>
    </div>

    <div class="bg-white p-4 rounded-lg shadow-md">
        <div class="flex items-center justify-between">
            <div>
                <h3 class="text-sm font-medium text-gray-500">Bloqueados</h3>
                <p id="bloqueados" class="text-2xl font-bold text-red-600">0</p>
            </div>
            <div class="p-2 bg-red-100 rounded-full">
                <i class="fas fa-lock text-red-600"></i>
            </div>
        </div>
    </div>

    <div class="bg-white p-4 rounded-lg shadow-md">
        <div class="flex items-center justify-between">
            <div>
                <h3 class="text-sm font-medium text-gray-500">Modificaciones Hoy</h3>
                <p id="modificacionesHoy" class="text-2xl font-bold text-purple-600">0</p>
            </div>
            <div class="p-2 bg-purple-100 rounded-full">
                <i class="fas fa-calendar-day text-purple-600"></i>
            </div>
        </div>
    </div>
</div>

<!-- Filtros -->
<div class="bg-white rounded-lg shadow-md mb-6 p-6">
    <h3 class="text-lg font-semibold mb-4 flex items-center">
        <i class="fas fa-filter mr-2 text-orange-600"></i>
        Filtros de Control
    </h3>

    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <div>
            <label for="fechaInicioMod" class="block text-sm font-medium text-gray-700 mb-1">
                Fecha Inicio
            </label>
            <input type="date"
                   id="fechaInicioMod"
                   class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-orange-500">
        </div>

        <div>
            <label for="fechaFinMod" class="block text-sm font-medium text-gray-700 mb-1">
                Fecha Fin
            </label>
            <input type="date"
                   id="fechaFinMod"
                   class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-orange-500">
        </div>

        <div>
            <label for="filtroEstadoMod" class="block text-sm font-medium text-gray-700 mb-1">
                Estado del Formulario
            </label>
            <select id="filtroEstadoMod"
                    class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-orange-500">
                <option value="todos">Todos los estados</option>
                <option value="ES">Especialista</option>
                <option value="ME">Médico</option>
            </select>
        </div>

        <div class="flex items-end space-x-2">
            <button id="btnAplicarFiltrosMod"
                    class="bg-orange-500 hover:bg-orange-600 text-white px-4 py-2 rounded-md">
                <i class="fas fa-search mr-1"></i>
                Aplicar
            </button>
            <button id="btnLimpiarFiltrosMod"
                    class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-md">
                <i class="fas fa-times mr-1"></i>
                Limpiar
            </button>
            <button id="btnRefrescarMod"
                    class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-md"
                    title="Refrescar tabla para ver cambios recientes">
                <i class="fas fa-sync-alt mr-1"></i>
                Refrescar
            </button>
        </div>
    </div>
</div>

<!-- Estilos específicos para DataTables -->
<style>
    #tablaModificaciones_wrapper {
        width: 100%;
    }

    #tablaModificaciones {
        border-collapse: collapse;
        font-size: 0.875rem;
    }

    #tablaModificaciones th,
    #tablaModificaciones td {
        border: 1px solid #e5e7eb;
        vertical-align: middle;
    }

    #tablaModificaciones thead th {
        background-color: #f9fafb;
        font-weight: 600;
        color: #374151;
    }

    .dataTables_scroll {
        overflow: auto;
    }

    .dataTables_scrollBody {
        border: 1px solid #e5e7eb;
    }
</style>

<!-- Tabla de modificaciones -->
<div class="bg-white rounded-lg shadow-md p-6">
    <h3 class="text-lg font-semibold mb-4 flex items-center">
        <i class="fas fa-table mr-2 text-orange-600"></i>
        Control de Modificaciones
    </h3>

    <div class="overflow-x-auto">
        <table id="tablaModificaciones" class="table table-striped" style="width:100%">
            <thead>
                <tr>
                    <th>Fecha</th>
                    <th>Hora</th>
                    <th>Paciente</th>
                    <th>Cédula</th>
                    <th>Estado</th>
                    <th>Modificaciones</th>
                    <th>Puede Modificar</th>
                    <th>Último Usuario</th>
                    <th>Última Modificación</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <!-- Los datos se cargan via AJAX -->
            </tbody>
        </table>
    </div>
</div>

<!-- Loading overlay para modificaciones -->
<div id="loadingOverlayModificaciones" class="fixed inset-0 bg-black bg-opacity-50 z-50 hidden items-center justify-center">
    <div class="text-center text-white">
        <div class="animate-spin rounded-full h-32 w-32 border-b-2 border-white mx-auto mb-4"></div>
        <p class="text-lg">Cargando datos...</p>
    </div>
</div>

<!-- Modal para habilitar modificación -->
<div class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50" id="modalHabilitar">
    <div class="bg-white rounded-lg max-w-md w-full mx-4">
        <div class="bg-orange-600 text-white px-6 py-4 rounded-t-lg flex justify-between items-center">
            <h4 class="text-lg font-bold flex items-center">
                <i class="fas fa-unlock mr-2"></i>
                Habilitar Modificación
            </h4>
            <button type="button" class="text-white hover:text-gray-200" onclick="cerrarModalHabilitar()">
                <i class="fas fa-times text-xl"></i>
            </button>
        </div>

        <form id="formHabilitar">
            <div class="p-6">
                <div class="mb-4">
                    <p class="text-gray-700 mb-2">
                        <strong>Paciente:</strong> <span id="nombrePaciente"></span>
                    </p>
                </div>

                <div class="bg-yellow-50 border border-yellow-200 text-yellow-800 px-4 py-3 rounded-lg mb-4">
                    <i class="fas fa-exclamation-triangle mr-2"></i>
                    <strong>Importante:</strong> Esta acción permitirá al médico modificar el formulario UNA vez más.
                </div>

                <div class="mb-4">
                    <label for="motivoHabilitacion" class="block text-sm font-medium text-gray-700 mb-2">
                        Motivo de la habilitación *
                    </label>
                    <textarea id="motivoHabilitacion" name="motivo" rows="4" required
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-orange-500"
                        placeholder="Describa el motivo por el cual se habilita la modificación del formulario..."></textarea>
                </div>

                <input type="hidden" id="ateCodigoHabilitar" name="ate_codigo">
                <input type="hidden" id="seccionHabilitar" name="seccion">
            </div>

            <div class="px-6 py-4 bg-gray-50 rounded-b-lg flex justify-end space-x-3">
                <button type="button" onclick="cerrarModalHabilitar()"
                    class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-lg transition-colors flex items-center">
                    <i class="fas fa-times mr-2"></i> Cancelar
                </button>
                <button type="button" onclick="enviarHabilitacion()"
                    class="bg-orange-600 hover:bg-orange-700 text-white px-4 py-2 rounded-lg transition-colors flex items-center">
                    <i class="fas fa-unlock mr-2"></i> Habilitar Modificación
                </button>
            </div>
        </form>
    </div>
</div>

