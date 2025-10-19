<?= $this->include('templates/header') ?>

<body class="bg-gray-100 font-nunito">
    <?php if (session()->get('rol_id') == 1): ?>
        <!-- Page Wrapper -->
        <div class="flex h-screen bg-gray-100">
            <!-- Sidebar -->
            <div class="w-64 bg-gradient-to-b from-indigo-800 to-indigo-900 shadow-xl">
                <!-- Sidebar Brand -->
                <div class="flex items-center justify-center h-16 px-4 bg-indigo-900">
                    <div class="flex items-center">
                        <div class="transform -rotate-12 mr-3">
                            <i class="fas fa-laugh-wink text-white text-xl"></i>
                        </div>
                        <div class="text-white font-bold text-lg">Panel Administrador</div>
                    </div>
                </div>

                <!-- Navigation -->
                <nav class="mt-6">
                    <!-- Dashboard -->
                    <a href="<?= base_url('administrador') ?>"
                        class="flex items-center px-6 py-3 text-gray-300 hover:bg-indigo-700 hover:text-white transition-colors duration-200">
                        <i class="fas fa-tachometer-alt mr-3"></i>
                        <span>Volver al Panel</span>
                    </a>
                </nav>
            </div>

            <!-- Main Content Area -->
            <div class="flex-1 flex flex-col overflow-hidden">
                <!-- Topbar -->
                <header class="bg-white shadow-sm border-b border-gray-200">
                    <div class="flex items-center justify-between px-6 py-4">
                        <div class="flex items-center">
                            <button class="text-gray-500 focus:outline-none lg:hidden">
                                <i class="fas fa-bars"></i>
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
                    <?= $this->include('templates/alertasUsuarios') ?>

                    <!-- Page Header -->
                    <div class="flex items-center justify-between mb-6">
                        <h1 class="text-3xl font-bold text-gray-800">Usuarios Desactivados</h1>
                        <div class="flex space-x-3">
                            <a href="<?= base_url('administrador') ?>"
                                class="bg-indigo-600 text-white px-4 py-2 rounded-lg hover:bg-indigo-700 transition-colors duration-200 flex items-center">
                                <i class="fas fa-arrow-left mr-2"></i>
                                Volver al Panel
                            </a>
                        </div>
                    </div>

                    <!-- Deleted Users Table -->
                    <div class="bg-white rounded-lg shadow-md overflow-hidden">
                        <div class="px-6 py-4 border-b border-gray-200">
                            <h3 class="text-lg font-semibold text-gray-800">
                                Lista de Usuarios Desactivados
                                <?php if (isset($usuarios) && is_array($usuarios)): ?>
                                    (<?= count($usuarios) ?>)
                                <?php endif; ?>
                            </h3>
                            <p class="text-sm text-gray-600 mt-1">
                                Estos usuarios han sido desactivados pero pueden ser reactivados en cualquier momento
                            </p>
                        </div>

                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Nombre
                                        </th>
                                        <th
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Cédula
                                        </th>
                                        <th
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Usuario
                                        </th>
                                        <th
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Rol
                                        </th>
                                        <th
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Estado
                                        </th>
                                        <th
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Acciones
                                        </th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    <?php if (isset($usuarios) && is_array($usuarios) && !empty($usuarios)): ?>
                                        <?php foreach ($usuarios as $usuario): ?>
                                            <tr class="hover:bg-gray-50" id="usuario-<?= $usuario['usu_id'] ?>">
                                                <td class="px-6 py-4 whitespace-nowrap">
                                                    <div class="flex items-center">
                                                        <div
                                                            class="w-10 h-10 bg-gray-300 rounded-full flex items-center justify-center">
                                                            <i class="fas fa-user text-gray-600"></i>
                                                        </div>
                                                        <div class="ml-4">
                                                            <div class="text-sm font-medium text-gray-900">
                                                                <?= esc($usuario['usu_nombre'] . ' ' . $usuario['usu_apellido']) ?>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                    <?= esc($usuario['usu_nro_documento'] ?? 'No registrado') ?>
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                    <?= esc($usuario['usu_usuario']) ?>
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap">
                                                    <span
                                                        class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-gray-100 text-gray-800">
                                                        <?= esc($usuario['rol_nombre'] ?? 'Sin rol') ?>
                                                    </span>
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap">
                                                    <span
                                                        class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-800">
                                                        Inactivo
                                                    </span>
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                                    <button
                                                        onclick="reactivarUsuario(<?= $usuario['usu_id'] ?>, '<?= esc($usuario['usu_nombre'] . ' ' . $usuario['usu_apellido']) ?>')"
                                                        class="text-green-600 hover:text-green-900 bg-green-100 hover:bg-green-200 px-3 py-1 rounded-md text-xs border border-green-600">
                                                        <i class="fas fa-undo mr-1"></i>
                                                        Reactivar
                                                    </button>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="6" class="px-6 py-8 text-center">
                                                <div class="flex flex-col items-center">
                                                    <i class="fas fa-user-check text-6xl text-green-300 mb-4"></i>
                                                    <h3 class="text-lg font-medium text-gray-900 mb-2">¡Excelente!</h3>
                                                    <p class="text-gray-500">No hay usuarios desactivados en el sistema</p>
                                                    <p class="text-sm text-gray-400 mt-2">Todos los usuarios están activos y
                                                        funcionando correctamente</p>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </main>
            </div>
        </div>

        <!-- JavaScript para las funciones -->
        <script src="<?= base_url('public/js/administrador/reactivarUsarios.js') ?>"></script>

    </body>

    </html>

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