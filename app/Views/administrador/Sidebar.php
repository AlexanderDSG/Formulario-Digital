<div id="admin-sidebar" class="w-64 bg-gradient-to-b from-indigo-800 to-indigo-900 shadow-xl transition-all duration-300 flex flex-col h-screen">
    <!-- Sidebar Brand -->
    <div class="flex items-center justify-center h-16 px-4 bg-indigo-900 flex-shrink-0">
        <div class="flex items-center">

            <div class="transform -rotate-12 mr-3">
                <i class="fas fa-laugh-wink text-white text-xl"></i>
            </div>

            <div id="sidebar-title" class="text-white font-bold text-lg">Panel Administrador</div>

        </div>
    </div>

    <!-- Navigation (scrollable area) -->
    <nav class="mt-6 overflow-y-auto flex-1 pb-6">
        <!-- Dashboard -->
        <a href="#" onclick="goToDashboard()"
            class="flex items-center px-6 py-3 text-gray-300 hover:bg-indigo-700 hover:text-white transition-colors duration-200">
            <i class="fas fa-tachometer-alt mr-3"></i>
            <span>Panel</span>
        </a>

        <!-- Divider -->
        <div class="border-t border-indigo-700 mx-6 my-4"></div>

        <!-- Section Header -->
        <div class="px-6 py-2 text-xs font-semibold text-indigo-300 uppercase tracking-wider">
            Gestión de Usuarios
        </div>

        <!-- Users Dropdown -->
        <div class="mt-2">
            <button onclick="toggleUsersMenu()"
                class="w-full flex items-center justify-between px-6 py-3 text-gray-300 hover:bg-indigo-700 hover:text-white transition-colors duration-200 focus:outline-none">
                <div class="flex items-center">
                    <i class="fas fa-users mr-3"></i>
                    <span>Usuarios</span>
                </div>
                <i id="users-arrow" class="fas fa-chevron-right transform transition-transform duration-200 rotate-90"></i>
            </button>

            <!-- Dropdown Menu -->
            <div id="users-dropdown" class="bg-indigo-800">
                <a href="#" onclick="goToUsers('administradores')"
                    class="flex items-center px-10 py-2 text-sm text-gray-300 hover:bg-indigo-700 hover:text-white transition-colors duration-200">
                    <i class="fas fa-user-shield mr-2 text-xs"></i>
                    Administradores
                </a>
                <a href="#" onclick="goToUsers('admisionistas')"
                    class="flex items-center px-10 py-2 text-sm text-gray-300 hover:bg-indigo-700 hover:text-white transition-colors duration-200">
                    <i class="fas fa-user-nurse mr-2 text-xs"></i>
                    Admisionistas
                </a>
                <a href="#" onclick="goToUsers('enfermeras')"
                    class="flex items-center px-10 py-2 text-sm text-gray-300 hover:bg-indigo-700 hover:text-white transition-colors duration-200">
                    <i class="fas fa-user-nurse mr-2 text-xs"></i>
                    Enfermeras
                </a>
                <a href="#" onclick="goToUsers('medicos')"
                    class="flex items-center px-10 py-2 text-sm text-gray-300 hover:bg-indigo-700 hover:text-white transition-colors duration-200">
                    <i class="fas fa-user-md mr-2 text-xs"></i>
                    Médicos
                </a>
                <a href="#" onclick="goToUsers('especialistas')"
                    class="flex items-center px-10 py-2 text-sm text-gray-300 hover:bg-indigo-700 hover:text-white transition-colors duration-200">
                    <i class="fas fa-stethoscope mr-2 text-xs"></i>
                    Médicos Especialistas
                </a>
            </div>
        </div>

        <!-- Divider -->
        <div class="border-t border-indigo-700 mx-6 my-4"></div>

        <!-- Section Header - Pacientes -->
        <div class="px-6 py-2 text-xs font-semibold text-indigo-300 uppercase tracking-wider">
            Gestión de Pacientes
        </div>

        <!-- Pacientes Menu -->
        <div class="mt-2">
            <a href="#" onclick="goToPatients()"
                class="flex items-center px-6 py-3 text-gray-300 hover:bg-indigo-700 hover:text-white transition-colors duration-200">
                <i class="fas fa-user-injured mr-3"></i>
                <span>Pacientes Registrados</span>
            </a>
        </div>

        <!-- Control de Modificaciones - VISTA INTERNA -->
        <div class="mt-2">
            <a href="#" onclick="goToModificaciones()"
                class="flex items-center px-6 py-3 text-gray-300 hover:bg-indigo-700 hover:text-white transition-colors duration-200">
                <i class="fas fa-edit mr-3"></i>
                <span>Control de Modificaciones</span>
            </a>
        </div>

        <!-- Reportes Administrativos -->
        <div class="mt-2">
            <a href="#" onclick="abrirReportesAdmin()"
                class="flex items-center px-6 py-3 text-gray-300 hover:bg-indigo-700 hover:text-white transition-colors duration-200">
                <i class="fas fa-chart-bar mr-3"></i>
                <span>Reportes</span>
            </a>
        </div>

        <!-- Divider -->
        <div class="border-t border-indigo-700 mx-6 my-4"></div>
    </nav>
</div>