<?php if (isset($es_modificacion) && $es_modificacion): ?>
    <div class="bg-orange-100 border-l-4 border-orange-500 text-orange-700 p-4 mb-4" role="alert">
        <div class="flex items-center">
            <i class="fas fa-edit mr-3 text-orange-500"></i>
            <div>
                <strong>Modo Modificación Habilitada:</strong>
                <p class="text-sm">Este formulario fue habilitado para modificación por el administrador. Puede editar y
                    guardar los cambios.</p>
                <?php if (isset($mensaje_modificacion) && !empty($mensaje_modificacion)): ?>
                    <p class="text-xs mt-1 italic"><?= esc($mensaje_modificacion) ?></p>
                <?php endif; ?>
            </div>
        </div>
    </div>
<?php endif; ?>

<!-- Mostrar mensaje flash de info si existe -->
<?php if (session()->getFlashdata('info')): ?>
    <div class="bg-blue-100 border border-blue-400 text-blue-700 px-4 py-3 rounded mb-4" role="alert">
        <div class="flex items-center">
            <i class="fas fa-info-circle mr-2"></i>
            <?= session()->getFlashdata('info') ?>
        </div>
    </div>
<?php endif; ?>
<!-- Mostrar mensaje de éxito si existe -->
<?php if (isset($mensaje_exito)): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="fas fa-check-circle mr-2"></i>
        <?= $mensaje_exito ?>
        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button>
    </div>
<?php endif; ?>