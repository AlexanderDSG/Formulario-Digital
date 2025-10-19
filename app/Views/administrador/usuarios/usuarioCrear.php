<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Crear Usuario</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="<?= base_url('public/js/vendor/fontawesome-free/css/all.min.css') ?>">
</head>

<body class="bg-gray-100">
    <?php if (session()->get('rol_id') == 1): ?>

        <div class="min-h-screen py-6 flex flex-col justify-center sm:py-12">
            <div class="relative py-3 sm:max-w-xl sm:mx-auto">
                <div class="relative px-4 py-10 bg-white mx-8 md:mx-0 shadow rounded-3xl sm:p-10">
                    <!-- Header -->
                    <div class="max-w-md mx-auto">
                        <div class="flex items-center justify-between mb-6">
                            <h1 class="text-2xl font-bold text-gray-800">Crear Usuario</h1>
                            <a href="<?= base_url('administrador') ?>"
                                class="text-indigo-600 hover:text-indigo-800">
                                <i class="fas fa-arrow-left mr-2"></i>Volver
                            </a>
                        </div>

                        <!-- Alertas -->
                        <?php if (session()->getFlashdata('error')): ?>
                            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                                <i class="fas fa-exclamation-triangle mr-2"></i>
                                <?= session()->getFlashdata('error') ?>
                            </div>
                        <?php endif; ?>

                        <!-- Formulario -->
                        <form action="<?= base_url('administrador/usuarios/guardar') ?>" method="POST" class="space-y-4">
                            <!-- Información Personal -->
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Nombre</label>
                                    <input type="text" name="usu_nombre"
                                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500"
                                        value="<?= old('usu_nombre') ?>" required>
                                    <?php if (session()->getFlashdata('errors')['usu_nombre'] ?? false): ?>
                                        <p class="text-red-500 text-sm mt-1"><?= session()->getFlashdata('errors')['usu_nombre'] ?></p>
                                    <?php endif; ?>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Apellido</label>
                                    <input type="text" name="usu_apellido"
                                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500"
                                        value="<?= old('usu_apellido') ?>" required>
                                    <?php if (session()->getFlashdata('errors')['usu_apellido'] ?? false): ?>
                                        <p class="text-red-500 text-sm mt-1"><?= session()->getFlashdata('errors')['usu_apellido'] ?></p>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <!-- Número de Documento -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Número de Documento</label>
                                <input type="text" name="usu_nro_documento"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500"
                                    value="<?= old('usu_nro_documento') ?>" required>
                                <?php if (session()->getFlashdata('errors')['usu_nro_documento'] ?? false): ?>
                                    <p class="text-red-500 text-sm mt-1"><?= session()->getFlashdata('errors')['usu_nro_documento'] ?></p>
                                <?php endif; ?>
                            </div>

                            <!-- Credenciales -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Usuario</label>
                                <input type="text" name="usu_usuario"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500"
                                    value="<?= old('usu_usuario') ?>" required>
                                <?php if (session()->getFlashdata('errors')['usu_usuario'] ?? false): ?>
                                    <p class="text-red-500 text-sm mt-1"><?= session()->getFlashdata('errors')['usu_usuario'] ?></p>
                                <?php endif; ?>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Contraseña</label>
                                <input type="password" name="usu_password"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500"
                                    required>
                                <?php if (session()->getFlashdata('errors')['usu_password'] ?? false): ?>
                                    <p class="text-red-500 text-sm mt-1"><?= session()->getFlashdata('errors')['usu_password'] ?></p>
                                <?php endif; ?>
                            </div>

                            <!-- Rol -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Rol</label>
                                <select name="rol_id" id="rol_id"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500"
                                    required onchange="toggleEspecialidades()">
                                    <option value="">Seleccionar rol...</option>
                                    <option value="1" <?= old('rol_id') == '1' ? 'selected' : '' ?>>Administrador</option>
                                    <option value="2" <?= old('rol_id') == '2' ? 'selected' : '' ?>>Admisionista</option>
                                    <option value="3" <?= old('rol_id') == '3' ? 'selected' : '' ?>>Enfermera</option>
                                    <option value="4" <?= old('rol_id') == '4' ? 'selected' : '' ?>>Médico</option>
                                    <option value="5" <?= old('rol_id') == '5' ? 'selected' : '' ?>>Médico Especialista</option>
                                </select>
                                <?php if (session()->getFlashdata('errors')['rol_id'] ?? false): ?>
                                    <p class="text-red-500 text-sm mt-1"><?= session()->getFlashdata('errors')['rol_id'] ?></p>
                                <?php endif; ?>
                            </div>

                            <!-- Especialidades (Solo para Médico Especialista) -->
                            <div id="especialidades-container" class="hidden">
                                <label class="block text-sm font-medium text-gray-700 mb-1">Especialidad</label>
                                <select name="especialidades[]" id="especialidades"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500">
                                    <option value="">Seleccionar Area...</option>
                                    <?php foreach ($especialidades as $especialidad): ?>
                                        <option value="<?= $especialidad['esp_codigo'] ?>"
                                            <?= (is_array(old('especialidades')) && in_array($especialidad['esp_codigo'], old('especialidades'))) ? 'selected' : '' ?>>
                                            <?= esc($especialidad['esp_nombre']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <?php if (session()->getFlashdata('errors')['especialidades'] ?? false): ?>
                                    <p class="text-red-500 text-sm mt-1"><?= session()->getFlashdata('errors')['especialidades'] ?></p>
                                <?php endif; ?>
                                <p class="text-sm text-gray-500 mt-1">
                                    <i class="fas fa-info-circle mr-1"></i>
                                    Solo puede seleccionar una Area
                                </p>
                            </div>

                            <!-- Botones -->
                            <div class="flex justify-between pt-6">
                                <a href="<?= base_url('administrador') ?>"
                                    class="px-6 py-2 border border-gray-300 text-gray-700 rounded-md hover:bg-gray-50 transition-colors">
                                    Cancelar
                                </a>
                                <button type="submit"
                                    class="px-6 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 transition-colors">
                                    <i class="fas fa-save mr-2"></i>
                                    Crear Usuario
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <script>
            function toggleEspecialidades() {
                const rolId = document.getElementById('rol_id').value;
                const especialidadesContainer = document.getElementById('especialidades-container');
                const especialidadesSelect = document.getElementById('especialidades');

                if (rolId === '5') { // Médico Especialista
                    especialidadesContainer.classList.remove('hidden');
                    especialidadesSelect.required = true;
                } else {
                    especialidadesContainer.classList.add('hidden');
                    especialidadesSelect.required = false;
                    especialidadesSelect.value = ''; // Limpiar selección
                }
            }

            // Ejecutar al cargar la página para mantener estado si hay errores
            document.addEventListener('DOMContentLoaded', function() {
                toggleEspecialidades();
            });
        </script>
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
                            <a href="<?= base_url() ?>"
                                class="text-indigo-600 hover:text-indigo-800">
                                <i class="fas fa-arrow-left mr-2"></i>Volver al inicio
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>
</body>

</html>