<?= $this->include('templates/header') ?>
<?php if (session()->get('rol_id') == 2): ?>
    <div id="contenedor-alertas" class="space-y-2"></div>

    <body class="bg-gray-100 p-8">
        <?= $this->include('templates/alertas') ?>
        <div class="container mx-auto px-1 py-6">

            <div class="max-w-7xl mx-auto">
                <div class="flex justify-end w-full px-20 py-2">
                    <input type="hidden" id="cod-historia" name="cod-historia"
                        class="form-input w-20 h-9 text-sm shadow rounded-md border border-gray-300 bg-gray-100 text-gray-500 cursor-not-allowed"
                        readonly />
                </div>

                <div class="px-20">
                    <div class="mb-8">
                        <?= $this->include('templates/InfromacionUsuario') ?>
                    </div>
                </div>
                <?= $this->include('templates/buscador') ?>
                <form method="post" action="<?= base_url('admisiones/formulario/guardarAdmisiones') ?>" id="form">
                    <div id="form-secciones">
                        <?php if (session()->get('rol_id') == 2): ?>
                            <?= $this->include('formulario/seccion_a') ?>
                            <?= $this->include('formulario/seccion_b') ?>
                        <?php endif; ?>
                    </div>

                    <!-- Botón guardar visible solo si hay formulario cargado -->
                    <?php if (in_array(session()->get('rol_id'), [2])): ?>
                        <div class="px-20">
                            <div class="flex justify-end space-x-4 mt-3">
                                <button type="submit" class="btn btn-secondary">
                                    <i class="fas fa-save"></i> Guardar
                                </button>
                            </div>
                        </div>
                    <?php endif; ?>
                </form>
            </div>
        </div>
        <script>
            // Asegurar que los estilos se mantengan después del submit
            document.addEventListener('DOMContentLoaded', function () {
                // Re-aplicar estilos si es necesario
                const form = document.getElementById('form');
                if (form) {
                    form.addEventListener('submit', function () {
                        // Mostrar loading spinner o mensaje
                        const submitBtn = form.querySelector('button[type="submit"]');
                        if (submitBtn) {
                            submitBtn.disabled = true;
                            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Guardando...';
                        }
                    });
                }
            });
        </script>
    <?php else: ?>
        <div class="bg-red-100 text-red-700 p-4 rounded">
            ❌ No tienes permisos para acceder a esta sección.
        </div>
    <?php endif; ?>
    <?= $this->include('templates/footer') ?>