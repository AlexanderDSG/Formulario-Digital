<?= $this->include('templates/header') ?>

<?php if (in_array(session()->get('rol_id'), [5])): ?>

    <body class="bg-gray-100 min-h-screen">
        <?= $this->include('templates/alertas') ?>
        <div class="container mx-auto px-4 mt-6">
            <!-- Header de login con informacion de usuario -->
            <?= $this->include('templates/InfromacionUsuario') ?>
            <div class="w-full">
                <!-- Header Principal -->

                <div class="bg-white rounded-lg shadow-md mb-6 relative">

                    <!-- Botón de Reportes posicionado sobre la línea verde -->
                    <?= $this->include('especialidades/reportesEspecialidades') ?>

                    <div class="bg-green-600 text-white rounded-t-lg px-6 py-4">
                        <h3 class="text-xl font-bold flex items-center justify-center">
                            <i class="fas fa-hospital mr-3"></i>
                            Especialidades Médicas - Centro de Atención
                        </h3>
                        <p class="text-green-100 text-sm mt-2 text-center">
                            Gestione pacientes asignados por triaje a las diferentes especialidades médicas
                        </p>
                    </div>

                    <!-- Pestañas de especialidades -->
                    <div class="bg-white rounded-lg shadow-md">
                        <div class="border-b border-gray-200 overflow-x-auto">
                            <nav class="flex space-x-8 px-6" id="especialidades-tabs" style="min-width: max-content;">
                                <?php if (!empty($especialidades)): ?>
                                    <?php $first = true; ?>
                                    <?php foreach ($especialidades as $esp): ?>
                                        <button
                                            class="<?= $first ? 'border-green-500 text-green-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' ?>
                                                 whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm flex items-center tab-button"
                                            id="tab-esp-<?= $esp['esp_codigo'] ?>" data-especialidad="<?= $esp['esp_codigo'] ?>"
                                            onclick="cambiarTab(this, <?= $esp['esp_codigo'] ?>)">
                                            <i class="fas fa-stethoscope mr-2"></i>
                                            <?= htmlspecialchars($esp['esp_nombre']) ?>
                                            <span class="bg-blue-500 text-white text-xs rounded-full px-2 py-1 ml-2">
                                                <?= $esp['total_pacientes'] ?? 0 ?>
                                            </span>
                                        </button>
                                        <?php $first = false; ?>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <button
                                        class="border-green-500 text-green-600 whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm">
                                        No hay especialidades configuradas
                                    </button>
                                <?php endif; ?>
                            </nav>
                        </div>
                        <div class="p-6">
                            <div id="especialidades-content">
                                <?php if (!empty($especialidades)): ?>
                                    <?php $first = true; ?>
                                    <?php foreach ($especialidades as $esp): ?>
                                        <div class="tab-content <?= $first ? '' : 'hidden' ?>"
                                            id="especialidad-<?= $esp['esp_codigo'] ?>">

                                            <!-- Controles de la especialidad -->
                                            <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-6">
                                                <div class="mb-4 md:mb-0">
                                                    <h4 class="text-2xl font-bold text-blue-600 flex items-center">
                                                        <i class="fas fa-hospital mr-3"></i>
                                                        <?= htmlspecialchars($esp['esp_nombre']) ?>
                                                    </h4>
                                                    <?php if (!empty($esp['esp_descripcion'])): ?>
                                                        <p class="text-gray-600 mt-2"><?= htmlspecialchars($esp['esp_descripcion']) ?>
                                                        </p>
                                                    <?php endif; ?>
                                                </div>
                                                <div class="flex space-x-2">
                                                    <button
                                                        class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-lg text-sm flex items-center transition-colors"
                                                        onclick="refrescarEspecialidad(<?= $esp['esp_codigo'] ?>)">
                                                        <i class="fas fa-sync-alt mr-2"></i> Actualizar
                                                    </button>
                                                </div>
                                            </div>

                                            <!-- Navegación entre Médicos y Enfermeros -->
                                            <div class="bg-gray-50 rounded-lg p-4 mb-6">
                                                <div class="flex justify-center">
                                                    <div class="bg-white rounded-lg p-1 shadow-sm">
                                                        <button
                                                            class="px-6 py-2 rounded-md text-sm font-medium transition-all duration-200 text-gray-800 bg-gray-200 hover:bg-gray-300 font-semibold shadow-sm tipo-personal-btn active"
                                                            id="btn-medicos-<?= $esp['esp_codigo'] ?>" data-tipo="medicos"
                                                            data-especialidad="<?= $esp['esp_codigo'] ?>"
                                                            onclick="cambiarTipoPersonal(<?= $esp['esp_codigo'] ?>, 'medicos', this)">
                                                            <i class="fas fa-user-md mr-2"></i>
                                                            Médicos Especialistas
                                                            <span class="bg-blue-500 text-white text-xs rounded-full px-2 py-1 ml-2"
                                                                id="count-medicos-<?= $esp['esp_codigo'] ?>">0</span>
                                                        </button>
                                                        <button
                                                            class="px-6 py-2 rounded-md text-sm font-medium transition-all duration-200 text-gray-600 bg-transparent hover:text-gray-800 hover:bg-gray-100 tipo-personal-btn"
                                                            id="btn-enfermeros-<?= $esp['esp_codigo'] ?>" data-tipo="enfermeros"
                                                            data-especialidad="<?= $esp['esp_codigo'] ?>"
                                                            onclick="cambiarTipoPersonal(<?= $esp['esp_codigo'] ?>, 'enfermeros', this)">
                                                            <i class="fas fa-user-nurse mr-2"></i>
                                                            Enfermeros Especialidad
                                                            <span
                                                                class="bg-green-500 text-white text-xs rounded-full px-2 py-1 ml-2"
                                                                id="count-enfermeros-<?= $esp['esp_codigo'] ?>">0</span>
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>

                                            <!-- Vista de Médicos Especialistas -->
                                            <div class="tipo-personal-content" id="content-medicos-<?= $esp['esp_codigo'] ?>">
                                                <!-- Secciones: Pendientes y En Atención -->
                                                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                                                    <!-- Pacientes Pendientes -->
                                                    <div class="bg-white border border-yellow-300 rounded-lg shadow-sm">
                                                        <div class="bg-yellow-400 text-yellow-900 px-4 py-3 rounded-t-lg">
                                                            <h5 class="font-bold flex items-center justify-between">
                                                                <span class="flex items-center">
                                                                    <i class="fas fa-clock mr-2"></i>
                                                                    Pacientes Pendientes
                                                                </span>
                                                                <span
                                                                    class="bg-yellow-900 text-yellow-100 px-2 py-1 rounded-full text-sm"
                                                                    id="count-pendientes-<?= $esp['esp_codigo'] ?>">0</span>
                                                            </h5>
                                                        </div>
                                                        <div class="p-0">
                                                            <div class="overflow-x-auto overflow-y-auto" style="max-height: 400px;">
                                                                <table class="w-full text-xs sm:text-sm min-w-full">
                                                                    <thead class="bg-gray-50 sticky top-0 z-10">
                                                                        <tr class="border-b border-gray-200">
                                                                            <th
                                                                                class="px-2 sm:px-4 py-2 sm:py-3 text-left font-medium text-gray-700 text-xs sm:text-sm">
                                                                                Paciente</th>
                                                                            <th
                                                                                class="px-2 sm:px-4 py-2 sm:py-3 text-left font-medium text-gray-700 text-xs sm:text-sm">
                                                                                Triaje</th>
                                                                            <th
                                                                                class="px-2 sm:px-4 py-2 sm:py-3 text-left font-medium text-gray-700 text-xs sm:text-sm">
                                                                                Hora</th>
                                                                            <th
                                                                                class="px-2 sm:px-4 py-2 sm:py-3 text-left font-medium text-gray-700 text-xs sm:text-sm">
                                                                                Acciones</th>
                                                                        </tr>
                                                                    </thead>
                                                                    <tbody id="pacientes-pendientes-<?= $esp['esp_codigo'] ?>"
                                                                        class="divide-y divide-gray-200">
                                                                        <tr>
                                                                            <td colspan="4" class="text-center py-8 text-gray-500">
                                                                                <i class="fas fa-spinner fa-spin mr-2"></i>
                                                                                Cargando...
                                                                            </td>
                                                                        </tr>
                                                                    </tbody>
                                                                </table>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <!-- Pacientes En Atención -->
                                                    <div class="bg-white border border-green-300 rounded-lg shadow-sm">
                                                        <div class="bg-green-600 text-white px-4 py-3 rounded-t-lg">
                                                            <h5 class="font-bold flex items-center justify-between">
                                                                <span class="flex items-center">
                                                                    <i class="fas fa-user-md mr-2"></i>
                                                                    En Atención
                                                                </span>
                                                                <span class="bg-white text-green-600 px-2 py-1 rounded-full text-sm"
                                                                    id="count-atencion-<?= $esp['esp_codigo'] ?>">0</span>
                                                            </h5>
                                                        </div>
                                                        <div class="p-0">
                                                            <div class="overflow-x-auto overflow-y-auto" style="max-height: 400px;">
                                                                <table class="w-full text-xs sm:text-sm min-w-full">
                                                                    <thead class="bg-gray-50 sticky top-0 z-10">
                                                                        <tr class="border-b border-gray-200">
                                                                            <th
                                                                                class="px-2 sm:px-4 py-2 sm:py-3 text-left font-medium text-gray-700 text-xs sm:text-sm">
                                                                                Paciente</th>
                                                                            <th
                                                                                class="px-2 sm:px-4 py-2 sm:py-3 text-left font-medium text-gray-700 text-xs sm:text-sm">
                                                                                Médico</th>
                                                                            <th
                                                                                class="px-2 sm:px-4 py-2 sm:py-3 text-left font-medium text-gray-700 text-xs sm:text-sm">
                                                                                Inicio</th>
                                                                            <th
                                                                                class="px-2 sm:px-4 py-2 sm:py-3 text-left font-medium text-gray-700 text-xs sm:text-sm">
                                                                                Acciones</th>
                                                                        </tr>
                                                                    </thead>
                                                                    <tbody id="pacientes-atencion-<?= $esp['esp_codigo'] ?>"
                                                                        class="divide-y divide-gray-200">
                                                                        <tr>
                                                                            <td colspan="4" class="text-center py-8 text-gray-500">
                                                                                <i class="fas fa-spinner fa-spin mr-2"></i>
                                                                                Cargando...
                                                                            </td>
                                                                        </tr>
                                                                    </tbody>
                                                                </table>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <!-- 2. EN PROCESO (NUEVA SECCIÓN) -->
                                                    <div class="bg-white border border-purple-300 rounded-lg shadow-sm">
                                                        <div class="bg-purple-500 text-white px-4 py-3 rounded-t-lg">
                                                            <h5 class="font-bold flex items-center justify-between">
                                                                <span class="flex items-center">
                                                                    <i class="fas fa-hourglass-half mr-2"></i>
                                                                    En Proceso Parcial
                                                                </span>
                                                                <span
                                                                    class="bg-purple-700 text-purple-100 px-2 py-1 rounded-full text-sm"
                                                                    id="count-proceso-<?= $esp['esp_codigo'] ?>">0</span>
                                                            </h5>
                                                        </div>
                                                        <div class="p-0">
                                                            <div class="overflow-x-auto overflow-y-auto" style="max-height: 400px;">
                                                                <table class="w-full text-xs sm:text-sm min-w-full">
                                                                    <thead class="bg-gray-50 sticky top-0 z-10">
                                                                        <tr class="border-b border-gray-200">
                                                                            <th
                                                                                class="px-2 sm:px-4 py-2 sm:py-3 text-left font-medium text-gray-700 text-xs sm:text-sm">
                                                                                Paciente</th>
                                                                            <th
                                                                                class="px-2 sm:px-4 py-2 sm:py-3 text-left font-medium text-gray-700 text-xs sm:text-sm">
                                                                                Triaje</th>
                                                                            <th
                                                                                class="px-2 sm:px-4 py-2 sm:py-3 text-left font-medium text-gray-700 text-xs sm:text-sm">
                                                                                Especialista</th>
                                                                            <th
                                                                                class="px-2 sm:px-4 py-2 sm:py-3 text-left font-medium text-gray-700 text-xs sm:text-sm">
                                                                                Guardado</th>
                                                                            <th
                                                                                class="px-2 sm:px-4 py-2 sm:py-3 text-left font-medium text-gray-700 text-xs sm:text-sm">
                                                                                Acciones</th>
                                                                        </tr>
                                                                    </thead>
                                                                    <tbody id="pacientes-proceso-<?= $esp['esp_codigo'] ?>"
                                                                        class="divide-y divide-gray-200">
                                                                        <!-- Contenido dinámico -->
                                                                    </tbody>
                                                                </table>
                                                            </div>
                                                        </div>

                                                    </div>
                                                    <!-- 4. Continuando Proceso -->
                                                    <div class="bg-white border border-blue-300 rounded-lg shadow-sm">
                                                        <div class="bg-blue-600 text-white px-4 py-3 rounded-t-lg">
                                                            <h5 class="font-bold flex items-center justify-between">
                                                                <span class="flex items-center">
                                                                    <i class="fas fa-play-circle mr-2"></i>
                                                                    Continuando Proceso
                                                                </span>
                                                                <span
                                                                    class="bg-blue-700 text-blue-100 px-2 py-1 rounded-full text-sm"
                                                                    id="count-continuando-<?= $esp['esp_codigo'] ?>">0</span>
                                                            </h5>
                                                        </div>
                                                        <div class="p-0">
                                                            <div class="overflow-x-auto overflow-y-auto" style="max-height: 300px;">
                                                                <table class="w-full text-xs sm:text-sm min-w-full">
                                                                    <thead class="bg-gray-50 sticky top-0 z-10">
                                                                        <tr class="border-b border-gray-200">
                                                                            <th
                                                                                class="px-2 sm:px-4 py-2 sm:py-3 text-left font-medium text-gray-700 text-xs sm:text-sm">
                                                                                Paciente</th>
                                                                            <th
                                                                                class="px-2 sm:px-4 py-2 sm:py-3 text-left font-medium text-gray-700 text-xs sm:text-sm">
                                                                                Especialista</th>
                                                                            <th
                                                                                class="px-2 sm:px-4 py-2 sm:py-3 text-left font-medium text-gray-700 text-xs sm:text-sm">
                                                                                Original</th>
                                                                            <th
                                                                                class="px-2 sm:px-4 py-2 sm:py-3 text-left font-medium text-gray-700 text-xs sm:text-sm">
                                                                                Acciones</th>
                                                                        </tr>
                                                                    </thead>
                                                                    <tbody id="pacientes-continuando-<?= $esp['esp_codigo'] ?>"
                                                                        class="divide-y divide-gray-200">
                                                                        <!-- Contenido dinámico -->
                                                                    </tbody>
                                                                </table>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <!-- Vista de Enfermeros de Especialidad -->
                                            <div class="tipo-personal-content hidden"
                                                id="content-enfermeros-<?= $esp['esp_codigo'] ?>">
                                                <!-- Secciones para Enfermeros -->
                                                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                                                    <!-- Pacientes Pendientes para Enfermería -->
                                                    <div class="bg-white border border-green-300 rounded-lg shadow-sm">
                                                        <div class="bg-green-500 text-white px-4 py-3 rounded-t-lg">
                                                            <h5 class="font-bold flex items-center justify-between">
                                                                <span class="flex items-center">
                                                                    <i class="fas fa-clipboard-list mr-2"></i>
                                                                    Pendientes Enfermería
                                                                </span>
                                                                <span
                                                                    class="bg-green-700 text-green-100 px-2 py-1 rounded-full text-sm"
                                                                    id="count-enfermeria-pendientes-<?= $esp['esp_codigo'] ?>">0</span>
                                                            </h5>
                                                        </div>
                                                        <div class="p-0">
                                                            <div class="overflow-x-auto overflow-y-auto" style="max-height: 400px;">
                                                                <table class="w-full text-xs sm:text-sm min-w-full">
                                                                    <thead class="bg-gray-50 sticky top-0 z-10">
                                                                        <tr class="border-b border-gray-200">
                                                                            <th
                                                                                class="px-2 sm:px-4 py-2 sm:py-3 text-left font-medium text-gray-700 text-xs sm:text-sm">
                                                                                Paciente</th>
                                                                            <th
                                                                                class="px-2 sm:px-4 py-2 sm:py-3 text-left font-medium text-gray-700 text-xs sm:text-sm">
                                                                                Origen</th>
                                                                            <th
                                                                                class="px-2 sm:px-4 py-2 sm:py-3 text-left font-medium text-gray-700 text-xs sm:text-sm">
                                                                                Hora</th>
                                                                            <th
                                                                                class="px-2 sm:px-4 py-2 sm:py-3 text-left font-medium text-gray-700 text-xs sm:text-sm">
                                                                                Acciones</th>
                                                                        </tr>
                                                                    </thead>
                                                                    <tbody id="enfermeria-pendientes-<?= $esp['esp_codigo'] ?>"
                                                                        class="divide-y divide-gray-200">
                                                                        <tr>
                                                                            <td colspan="4" class="text-center py-8 text-gray-500">
                                                                                <i class="fas fa-spinner fa-spin mr-2"></i>
                                                                                Cargando pacientes...
                                                                            </td>
                                                                        </tr>
                                                                    </tbody>
                                                                </table>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <!-- Pacientes tomados por enfermeria -->
                                                    <div class="bg-white border border-blue-300 rounded-lg shadow-sm">
                                                        <div class="bg-blue-500 text-white px-4 py-3 rounded-t-lg">
                                                            <h5 class="font-bold flex items-center justify-between">
                                                                <span class="flex items-center">
                                                                    <i class="fas fa-user-nurse mr-2"></i>
                                                                    Pacientes en Enfermería
                                                                </span>
                                                                <span
                                                                    class="bg-blue-700 text-blue-100 px-2 py-1 rounded-full text-sm"
                                                                    id="count-enfermeria-asignados-<?= $esp['esp_codigo'] ?>">0</span>
                                                            </h5>
                                                        </div>
                                                        <div class="p-0">
                                                            <div class="overflow-x-auto overflow-y-auto" style="max-height: 400px;">
                                                                <table class="w-full text-xs sm:text-sm min-w-full">
                                                                    <thead class="bg-gray-50 sticky top-0 z-10">
                                                                        <tr class="border-b border-gray-200">
                                                                            <th
                                                                                class="px-2 sm:px-4 py-2 sm:py-3 text-left font-medium text-gray-700 text-xs sm:text-sm">
                                                                                Paciente</th>
                                                                            <th
                                                                                class="px-2 sm:px-4 py-2 sm:py-3 text-left font-medium text-gray-700 text-xs sm:text-sm">
                                                                                Enfermero</th>
                                                                            <th
                                                                                class="px-2 sm:px-4 py-2 sm:py-3 text-left font-medium text-gray-700 text-xs sm:text-sm">
                                                                                Asignado</th>
                                                                            <th
                                                                                class="px-2 sm:px-4 py-2 sm:py-3 text-left font-medium text-gray-700 text-xs sm:text-sm">
                                                                                Acciones</th>
                                                                        </tr>
                                                                    </thead>
                                                                    <tbody id="enfermeria-asignados-<?= $esp['esp_codigo'] ?>"
                                                                        class="divide-y divide-gray-200">
                                                                        <tr>
                                                                            <td colspan="4" class="text-center py-8 text-gray-500">
                                                                                <i class="fas fa-spinner fa-spin mr-2"></i>
                                                                                Cargando pacientes...
                                                                            </td>
                                                                        </tr>
                                                                    </tbody>
                                                                </table>
                                                            </div>
                                                        </div>
                                                    </div>

                                                </div>
                                            </div>
                                        </div>
                                        <?php $first = false; ?>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <div class="tab-content">
                                        <div class="bg-blue-50 border border-blue-200 text-blue-800 px-4 py-3 rounded-lg">
                                            <i class="fas fa-info-circle mr-2"></i>
                                            No hay especialidades configuradas en el sistema.
                                        </div>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- Modal para TOMAR atención (usuario + contraseña) -->
            <div class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50"
                id="modalTomarAtencion">
                <div class="bg-white rounded-lg max-w-md w-full mx-4">
                    <div class="bg-green-600 text-white px-6 py-4 rounded-t-lg flex justify-between items-center">
                        <h4 class="text-lg font-bold flex items-center">
                            <i class="fas fa-user-md mr-2"></i>
                            Tomar Atención - Identificación
                        </h4>
                        <button type="button" class="text-white hover:text-gray-200" onclick="cerrarModalTomar()">
                            <i class="fas fa-times text-xl"></i>
                        </button>
                    </div>
                    <div class="p-6">
                        <div class="bg-green-50 border border-green-200 text-green-800 px-4 py-3 rounded-lg mb-4">
                            <i class="fas fa-info-circle mr-2"></i>
                            <strong>Ingrese sus credenciales para tomar la atención del paciente.</strong>
                        </div>
                        <form id="form-tomar-atencion">
                            <input type="hidden" id="are_codigo_tomar">

                            <div class="mb-4">
                                <label for="usuario_tomar" class="block text-sm font-medium text-gray-700 mb-2">
                                    <i class="fas fa-user mr-1"></i>
                                    Usuario:
                                </label>
                                <input type="text"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500"
                                    id="usuario_tomar" placeholder="Nombre de usuario" required>
                            </div>

                            <div class="mb-4">
                                <label for="password_tomar" class="block text-sm font-medium text-gray-700 mb-2">
                                    <i class="fas fa-key mr-1"></i>
                                    Contraseña:
                                </label>
                                <input type="password"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500"
                                    id="password_tomar" placeholder="Contraseña" required>
                            </div>
                        </form>
                    </div>
                    <div class="px-6 py-4 bg-gray-50 rounded-b-lg flex justify-end space-x-3">
                        <button type="button"
                            class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-lg transition-colors flex items-center"
                            onclick="cerrarModalTomar()">
                            <i class="fas fa-times mr-2"></i> Cancelar
                        </button>
                        <button type="button"
                            class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg transition-colors flex items-center"
                            onclick="confirmarTomarConCredenciales()">
                            <i class="fas fa-user-md mr-2"></i> Tomar Atención
                        </button>
                    </div>
                </div>
            </div>

            <!-- Modal para VALIDAR contraseña (solo contraseña) -->
            <div class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50"
                id="modalValidarContrasena">
                <div class="bg-white rounded-lg max-w-md w-full mx-4">
                    <div class="bg-yellow-500 text-yellow-900 px-6 py-4 rounded-t-lg flex justify-between items-center">
                        <h4 class="text-lg font-bold flex items-center">
                            <i class="fas fa-lock mr-2"></i>
                            Validación de Acceso
                        </h4>
                        <button type="button" class="text-yellow-900 hover:text-yellow-700" onclick="cerrarModalValidar()">
                            <i class="fas fa-times text-xl"></i>
                        </button>
                    </div>
                    <div class="p-6">
                        <div class="bg-yellow-50 border border-yellow-200 text-yellow-800 px-4 py-3 rounded-lg mb-4">
                            <i class="fas fa-exclamation-triangle mr-2"></i>
                            <strong id="mensaje-medico-actual">Esta atención está siendo tomada por otro médico.</strong>
                        </div>
                        <form id="form-validar-contrasena">
                            <input type="hidden" id="are_codigo_validar">

                            <div class="mb-4">
                                <label for="password_validar" class="block text-sm font-medium text-gray-700 mb-2">
                                    <i class="fas fa-key mr-1"></i>
                                    Ingrese su contraseña para continuar:
                                </label>
                                <input type="password"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-yellow-500 focus:border-yellow-500"
                                    id="password_validar" placeholder="Contraseña" required>
                            </div>

                            <p class="text-sm text-gray-600">
                                Solo el médico asignado puede continuar con esta atención.
                            </p>
                        </form>
                    </div>
                    <div class="px-6 py-4 bg-gray-50 rounded-b-lg flex justify-end space-x-3">
                        <button type="button"
                            class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-lg transition-colors flex items-center"
                            onclick="cerrarModalValidar()">
                            <i class="fas fa-times mr-2"></i> Cancelar
                        </button>
                        <button type="button"
                            class="bg-yellow-500 hover:bg-yellow-600 text-white px-4 py-2 rounded-lg transition-colors flex items-center"
                            onclick="validarYContinuar()">
                            <i class="fas fa-unlock mr-2"></i> Validar y Continuar
                        </button>
                    </div>
                </div>
            </div>
            <!-- Modal para CONTINUAR proceso con validación -->
            <div class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50"
                id="modalContinuarProceso">
                <div class="bg-white rounded-lg max-w-md w-full mx-4">
                    <div class="bg-blue-600 text-white px-6 py-4 rounded-t-lg flex justify-between items-center">
                        <h4 class="text-lg font-bold" id="titulo-modal-continuar">
                            <i class="fas fa-user-md mr-2"></i>
                            Continuar Proceso
                        </h4>
                        <button type="button" class="text-white hover:text-gray-200" onclick="cerrarModalContinuar()">
                            <i class="fas fa-times text-xl"></i>
                        </button>
                    </div>
                    <div class="p-6">
                        <div class="bg-blue-50 border border-blue-200 text-blue-800 px-4 py-3 rounded-lg mb-4">
                            <i class="fas fa-info-circle mr-2"></i>
                            <span id="mensaje-continuar">Validación requerida para continuar el proceso.</span>
                        </div>
                        <form id="form-continuar-proceso">
                            <input type="hidden" id="are_codigo_continuar">
                            <input type="hidden" id="tipo_validacion_continuar">

                            <div class="mb-4" id="campo-usuario-continuar">
                                <label for="usuario_continuar" class="block text-sm font-medium text-gray-700 mb-2">
                                    <i class="fas fa-user mr-1"></i>
                                    Usuario:
                                </label>
                                <input type="text"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                    id="usuario_continuar" placeholder="Nombre de usuario">
                            </div>

                            <div class="mb-4">
                                <label for="password_continuar" class="block text-sm font-medium text-gray-700 mb-2">
                                    <i class="fas fa-key mr-1"></i>
                                    Contraseña:
                                </label>
                                <input type="password"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                    id="password_continuar" placeholder="Contraseña" required>
                            </div>
                        </form>
                    </div>
                    <div class="px-6 py-4 bg-gray-50 rounded-b-lg flex justify-end space-x-3">
                        <button type="button"
                            class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-lg transition-colors flex items-center"
                            onclick="cerrarModalContinuar()">
                            <i class="fas fa-times mr-2"></i> Cancelar
                        </button>
                        <button type="button"
                            class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg transition-colors flex items-center"
                            onclick="confirmarContinuarProceso()">
                            <i class="fas fa-arrow-right mr-2"></i> Continuar
                        </button>
                    </div>
                </div>
            </div>

            <script src="<?= base_url('public/js/especialidades/listaEspecialidad.js') ?>"></script>
            <!-- JavaScript específico para especialidades - SOLO VARIABLES ESENCIALES -->
            <script>
                // Variables globales necesarias para el contexto
                window.contextoEspecialidades = true;
                window.base_url = "<?= base_url() ?>";

                // URLs para el sistema de especialidades
                window.ESPECIALIDADES_URLS = {
                    obtenerPacientes: "<?= base_url('especialidades/obtenerPacientesEspecialidad') ?>",
                    obtenerPacientesEnfermeria: "<?= base_url('especialidades/obtenerPacientesEnfermeria') ?>",
                    verificarDisponibilidad: "<?= base_url('especialidades/verificarDisponibilidad') ?>",
                    tomarAtencionConCredenciales: "<?= base_url('especialidades/tomarAtencionConCredenciales') ?>",
                    validarContrasena: "<?= base_url('especialidades/validarContrasena') ?>",
                    formulario: "<?= base_url('especialidades/formulario') ?>",
                    validarContinuarProceso: "<?= base_url('especialidades/validarContinuarProceso') ?>",
                    continuarProcesoConValidacion: "<?= base_url('especialidades/continuarProcesoConValidacion') ?>",
                    tomarAtencionEnfermeria: "<?= base_url('especialidades/tomarAtencionEnfermeria') ?>",
                    validarAccesoEnfermeria: "<?= base_url('especialidades/validarAccesoEnfermeria') ?>"
                };

                // Datos de especialidades
                window.ESPECIALIDADES_DATA = <?= json_encode($especialidades ?? []) ?>;

                // Variable para el médico actual
                window.medicoActualEspecialidades = {
                    usu_codigo: <?= session()->get('usu_codigo') ?? 0 ?>,
                    usu_nombre: "<?= session()->get('usu_nombre') ?? '' ?>",
                    usu_apellido: "<?= session()->get('usu_apellido') ?? '' ?>"
                };
            </script>



        <?php else: ?>
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-lg">
                ⚠️ No tiene permisos para acceder a las especialidades médicas.
            </div>
        <?php endif; ?>

        <?= $this->include('templates/footer') ?>