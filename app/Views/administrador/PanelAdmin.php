<?php
// Configurar variables para el header
$title = 'Panel de Control - Administrador';
?>

<?= $this->include('templates/header') ?>
<?php if (session()->get('rol_id') == 1): ?>

    <body class="bg-gray-100 font-nunito">

        <style>
            /* Estilos para sidebar colapsado */
            #admin-sidebar {
                transition: all 0.3s ease-in-out;
                overflow: hidden;
            }

            #admin-sidebar.collapsed {
                width: 0;
                min-width: 0;
                padding: 0;
            }

            #admin-sidebar.collapsed * {
                opacity: 0;
                pointer-events: none;
            }

            /* Transición suave para el contenido principal */
            .flex-1.flex.flex-col {
                transition: margin-left 0.3s ease-in-out;
            }
        </style>

        <!-- Page Wrapper -->
        <div class="flex h-screen bg-gray-100">

            <!-- Sidebar -->
            <?= $this->include('administrador/Sidebar') ?>
            <!-- End Sidebar-->

            <!-- Main Content Area -->
            <div class="flex-1 flex flex-col overflow-hidden">
                <!-- Topbar -->
                <header class="bg-white shadow-sm border-b border-gray-200">
                    <div class="flex items-center justify-between px-6 py-4">
                        <div class="flex items-center">
                            <button onclick="toggleSidebar()" class="text-gray-500 hover:text-gray-700 focus:outline-none transition-colors" title="Mostrar/Ocultar menú lateral">
                                <i class="fas fa-bars text-xl"></i>
                            </button>
                        </div>

                        <!-- User Dropdown -->
                        <div class="relative">
                            <button onclick="toggleUserDropdown()"
                                class="flex items-center text-sm text-gray-500 hover:text-gray-700 focus:outline-none">
                                <div class="flex items-center">
                                    <div class="w-8 h-8 bg-indigo-600 rounded-full flex items-center justify-center mr-3">
                                        <i class="fas fa-user text-white text-sm"></i>
                                    </div>
                                    <div class="text-left">
                                        <div class="font-medium text-gray-900">
                                            <?= esc(session()->get('usu_nombre') . ' ' . session()->get('usu_apellido')) ?>
                                        </div>
                                        <div class="text-xs text-gray-500">
                                            <?= esc(session()->get('rol_nombre')) ?>
                                        </div>
                                    </div>
                                </div>
                                <i class="fas fa-chevron-down ml-2"></i>
                            </button>

                            <div id="user-dropdown"
                                class="hidden absolute right-0 mt-2 w-56 bg-white rounded-md shadow-lg py-1 z-50 border">
                                <div class="px-4 py-3 border-b border-gray-200">
                                    <p class="text-sm text-gray-900 font-medium">
                                        <?= esc(session()->get('usu_nombre') . ' ' . session()->get('usu_apellido')) ?>
                                    </p>
                                    <p class="text-xs text-gray-500">
                                        <?= esc(session()->get('usu_usuario')) ?>
                                    </p>
                                    <p class="text-xs text-gray-500">
                                        ID: <?= esc(session()->get('usu_id')) ?>
                                    </p>
                                </div>
                                <a href="<?= base_url('logout') ?>"
                                    class="flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                    <i class="fas fa-sign-out-alt mr-2"></i>
                                    Cerrar Sesión
                                </a>
                            </div>
                        </div>
                    </div>
                </header>

                <!-- Page Content -->
                <main class="flex-1 overflow-x-hidden overflow-y-auto bg-gray-100 p-6">
                    <!-- Dashboard Content -->
                    <div id="dashboard-content">
                        <?= $this->include('templates/alertasUsuarios') ?>
                        <div class="flex items-center justify-between mb-6">
                            <h1 class="text-3xl font-bold text-gray-800">Panel</h1>
                        </div>
                        <!-- mostrar los usuarios -->
                        <?= $this->include('administrador/usuarios/listaUsuarios') ?>
                    </div>

                    <!-- User Table Content (hidden by default) -->
                    <div id="user-table-content" class="hidden">
                        <!-- Page Header -->
                        <div class="flex items-center justify-between mb-6">
                            <h1 id="table-title" class="text-3xl font-bold text-gray-800">Usuarios</h1>
                            <div class="flex space-x-3">
                                <a href="<?= base_url('administrador/usuarios/eliminados') ?>"
                                    class="bg-orange-600 text-white px-4 py-2 rounded-lg hover:bg-orange-700 transition-colors duration-200 flex items-center">
                                    <i class="fas fa-user-slash mr-2"></i>
                                    Usuarios Desactivados
                                </a>
                                <a href="<?= base_url('administrador/usuarios/crear') ?>"
                                    class="bg-indigo-600 text-white px-4 py-2 rounded-lg hover:bg-indigo-700 transition-colors duration-200 flex items-center">
                                    <i class="fas fa-plus mr-2"></i>
                                    Nuevo Usuario
                                </a>
                            </div>
                        </div>

                        <!-- Users Table -->
                        <div class="bg-white rounded-lg shadow-md overflow-hidden">
                            <div class="px-6 py-4 border-b border-gray-200">
                                <h3 id="card-title" class="text-lg font-semibold text-gray-800">Lista de Usuarios</h3>
                            </div>
                            <div class="overflow-x-auto">
                                <table class="min-w-full divide-y divide-gray-200">
                                    <thead class="bg-gray-50">
                                        <tr>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                Nombre</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                Cedula</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                Usuario</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                Rol</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                Acciones</th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white divide-y divide-gray-200">
                                        <!-- El contenido se cargará dinámicamente con JavaScript -->
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </main>
            </div>
        </div>

    <?php else: ?>
        <div class="min-h-screen py-6 flex flex-col justify-center sm:py-12">
            <div class="relative py-3 sm:max-w-md sm:mx-auto">
                <div class="relative px-4 py-10 bg-white mx-8 md:mx-0 shadow rounded-3xl sm:p-10">
                    <div class="max-w-md mx-auto text-center">
                        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded">
                            <i class="fas fa-exclamation-triangle mr-2"></i>
                            No tienes permisos para acceder a esta sección.
                        </div>
                        <div class="mt-4">
                            <a href="<?= base_url() ?>" class="text-indigo-600 hover:text-indigo-800">
                                <i class="fas fa-arrow-left mr-2"></i>Volver al inicio
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <?= $this->include('templates/footer') ?>