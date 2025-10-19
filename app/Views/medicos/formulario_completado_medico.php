<?= $this->include('templates/alertas') ?>
<?= $this->include('templates/header') ?>

<?php if (in_array(session()->get('rol_id'), [4])): ?>

<body class="bg-gray-50 min-h-screen">
    <div class="container mx-auto px-4 py-6">
        <div class="max-w-4xl mx-auto">
            
            <!-- Alerta principal de formulario completado -->
            <div class="bg-green-50 border-l-4 border-green-500 p-6 rounded-lg shadow-md mb-6">
                <div class="flex items-start">
                    <div class="flex-shrink-0">
                        <i class="fas fa-check-circle text-green-500 text-3xl"></i>
                    </div>
                    <div class="ml-4 flex-1">
                        <h2 class="text-xl font-bold text-green-800 mb-3">
                            Formulario Médico Ya Completado
                        </h2>
                        <p class="text-green-700 text-lg mb-2">
                            <?= htmlspecialchars($mensaje_completado ?? 'Este formulario médico ya ha sido procesado.') ?>
                        </p>
                        
                        <?php if (!empty($fecha_completado)): ?>
                            <div class="bg-white p-3 rounded border border-green-200 mt-3">
                                <p class="text-sm text-green-600">
                                    <strong>Fecha de finalización:</strong> 
                                    <?= date('d/m/Y H:i', strtotime($fecha_completado)) ?>
                                </p>
                                
                                <?php if (!empty($medico_que_completo)): ?>
                                    <p class="text-sm text-green-600 mt-1">
                                        <strong>Completado por:</strong> 
                                        Dr(a). <?= htmlspecialchars($medico_que_completo) ?>
                                    </p>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Información del paciente -->
            <?php if (!empty($paciente_info)): ?>
            <div class="bg-white p-6 rounded-lg shadow-md mb-6">
                <h3 class="text-lg font-semibold text-gray-800 mb-4">
                    <i class="fas fa-user-injured mr-2 text-blue-500"></i>
                    Información del Paciente
                </h3>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-600">Paciente:</label>
                        <p class="text-gray-800 font-medium">
                            <?= htmlspecialchars(($paciente_info['pac_nombres'] ?? '') . ' ' . ($paciente_info['pac_apellidos'] ?? '')) ?>
                        </p>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-600">Cédula:</label>
                        <p class="text-gray-800 font-mono"><?= htmlspecialchars($paciente_info['pac_cedula'] ?? 'N/A') ?></p>
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <!-- Información adicional de la atención -->
            <div class="bg-white p-6 rounded-lg shadow-md mb-6">
                <h3 class="text-lg font-semibold text-gray-800 mb-4">
                    <i class="fas fa-clipboard-list mr-2 text-blue-500"></i>
                    Información de la Atención Médica
                </h3>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-600">Código de Atención:</label>
                        <p class="text-gray-800 font-mono"><?= htmlspecialchars($ate_codigo ?? 'N/A') ?></p>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-600">Estado del Formulario:</label>
                        <p class="text-green-600 font-semibold">
                            <i class="fas fa-check-circle mr-1"></i>
                            Completado
                        </p>
                    </div>
                </div>
            </div>

            <!-- Mensaje informativo sobre qué hacer -->
            <div class="bg-yellow-50 border-l-4 border-yellow-400 p-4 rounded-lg shadow-sm mb-6">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <i class="fas fa-info-circle text-yellow-400"></i>
                    </div>
                    <div class="ml-3">
                        <h4 class="text-sm font-medium text-yellow-800">¿Qué significa esto?</h4>
                        <div class="mt-2 text-sm text-yellow-700">
                            <ul class="list-disc pl-5">
                                <li>El formulario médico para este paciente ya fue completado y guardado</li>
                                <li>La evaluación médica ha finalizado correctamente</li>
                                <li>Los datos han sido registrados en el sistema de manera segura</li>
                                <li>No es posible modificar los datos ya registrados sin autorización del administrador</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Acciones disponibles -->
            <div class="flex justify-center space-x-4 mt-8">
                <a href="<?= base_url('medicos/lista') ?>" 
                   class="btn bg-blue-600 hover:bg-blue-700 text-white px-6 py-3 rounded-lg transition-colors duration-200">
                    <i class="fas fa-arrow-left mr-2"></i> 
                    Volver a Lista de Pacientes
                </a>
                
                <a href="<?= base_url('medicos/lista') ?>" 
                   class="btn bg-gray-600 hover:bg-gray-700 text-white px-6 py-3 rounded-lg transition-colors duration-200">
                    <i class="fas fa-home mr-2"></i> 
                    Ir al Inicio
                </a>
            </div>

            <!-- Información adicional para administradores -->
            <?php if (session()->get('rol_id') == 1): // Solo para administradores ?>
                <div class="bg-gray-50 p-4 rounded-lg shadow-sm mt-6">
                    <p class="text-xs text-gray-500 text-center">
                        <i class="fas fa-lock mr-1"></i>
                        Solo los administradores del sistema pueden habilitar modificaciones a formularios completados
                    </p>
                </div>
            <?php endif; ?>

        </div>
    </div>

    <!-- JavaScript para prevenir navegación hacia atrás -->
    <script>
        // Prevenir que el usuario use el botón atrás para volver al formulario
        if (window.history && window.history.pushState) {
            window.history.replaceState(null, null, window.location.href);
            
            window.addEventListener('popstate', function(event) {
                // Redirigir a la lista de medicos si intenta ir atrás
                window.location.href = "<?= base_url('medicos') ?>";
            });
        }
        
        // Mensaje adicional si el usuario intenta recargar
        window.addEventListener('beforeunload', function(e) {
            // Este mensaje aparecerá si el usuario intenta recargar la página
            const message = 'Este formulario ya está completado. ¿Está seguro de que desea salir?';
            e.returnValue = message;
            return message;
        });
        
        // Auto-redirigir después de cierto tiempo (opcional)
        setTimeout(function() {
            // Mostrar notificación antes de redirigir
            if (confirm('Esta página se cerrará automáticamente. ¿Desea ir a la lista de medicos ahora?')) {
                window.location.href = "<?= base_url('medicos') ?>";
            }
        }, 30000); // 30 segundos
    </script>
<?php else: ?>
    <div class="bg-red-100 text-red-700 p-4 rounded">
        ⚠️ No tiene permisos para acceder a esta sección.
    </div>
<?php endif; ?>

<?= $this->include('templates/footer') ?>