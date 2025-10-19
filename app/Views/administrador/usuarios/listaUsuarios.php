<?php if (session()->get('rol_id') == 1): ?>

<!-- Dashboard de Estadísticas de Atenciones -->
<?= $this->include('administrador/dashboard/estadisticasAtenciones') ?>

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