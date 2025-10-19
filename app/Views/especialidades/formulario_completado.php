
<?= $this->include('templates/header') ?>

<?php if (in_array(session()->get('rol_id'), [5])): ?>

<body class="bg-gray-50 min-h-screen">
    <?= $this->include('templates/alertas') ?>
    <div class="container mx-auto px-4 py-6">
        <div class="max-w-4xl mx-auto">
            
            <!-- Alerta principal de formulario completado -->
            <div class="bg-blue-50 border-l-4 border-blue-500 p-6 rounded-lg shadow-md mb-6">
                <div class="flex items-start">
                    <div class="flex-shrink-0">
                        <i class="fas fa-check-circle text-blue-500 text-3xl"></i>
                    </div>
                    <div class="ml-4 flex-1">
                        <h2 class="text-xl font-bold text-blue-800 mb-3">
                            Formulario de Especialidad Ya Completado
                        </h2>
                        <p class="text-blue-700 text-lg mb-2">
                            <?= htmlspecialchars($mensaje_completado ?? 'Este formulario ya ha sido procesado.') ?>
                        </p>
                        
                        <?php if (!empty($fecha_completado)): ?>
                            <div class="bg-white p-3 rounded border border-blue-200 mt-3">
                                <p class="text-sm text-blue-600">
                                    <strong>Fecha de finalización:</strong> 
                                    <?= htmlspecialchars($fecha_completado) ?>
                                    <?php if (!empty($hora_completado)): ?>
                                        a las <?= htmlspecialchars($hora_completado) ?>
                                    <?php endif; ?>
                                </p>
                                
                                <?php if (!empty($especialista_que_completo)): ?>
                                    <p class="text-sm text-blue-600 mt-1">
                                        <strong>Completado por:</strong> 
                                        <?= htmlspecialchars($especialista_que_completo) ?>
                                    </p>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Información adicional -->
            <div class="bg-white p-6 rounded-lg shadow-md mb-6">
                <h3 class="text-lg font-semibold text-gray-800 mb-4">Información de la Atención</h3>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-600">Código de Área:</label>
                        <p class="text-gray-800 font-mono"><?= htmlspecialchars($are_codigo ?? 'N/A') ?></p>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-600">Código de Atención:</label>
                        <p class="text-gray-800 font-mono"><?= htmlspecialchars($ate_codigo ?? 'N/A') ?></p>
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
                                <li>El formulario de especialidad para este paciente ya fue completado</li>
                                <li>La atención médica en esta especialidad ha finalizado</li>
                                <li>No es posible modificar los datos ya registrados</li>
                                <li>Esta es una medida de seguridad para proteger la integridad de los registros</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Acciones disponibles -->
            <div class="flex justify-center space-x-4 mt-8">
                <a href="<?= base_url('especialidades') ?>" 
                   class="btn bg-blue-600 hover:bg-blue-700 text-white px-6 py-3 rounded-lg transition-colors duration-200">
                    <i class="fas fa-arrow-left mr-2"></i> 
                    Volver a Lista de Especialidades
                </a>
                
                <a href="<?= base_url('especialidades') ?>" 
                   class="btn bg-gray-600 hover:bg-gray-700 text-white px-6 py-3 rounded-lg transition-colors duration-200">
                    <i class="fas fa-home mr-2"></i> 
                    Ir al Inicio
                </a>
            </div>

            <!-- Información adicional para administradores o casos especiales -->
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
                // Redirigir a la lista de especialidades si intenta ir atrás
                window.location.href = "<?= base_url('especialidades') ?>";
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
            if (confirm('Esta página se cerrará automáticamente. ¿Desea ir a la lista de especialidades ahora?')) {
                window.location.href = "<?= base_url('especialidades') ?>";
            }
        }, 30000); // 30 segundos
    </script>

<?php else: ?>
    <div class="bg-red-100 text-red-700 p-4 rounded">
        ⚠️ No tiene permisos para acceder a esta sección.
    </div>
<?php endif; ?>

<?= $this->include('templates/footer') ?>