<?php if (session()->get('rol_id') == 1): ?>


    <!-- Campo oculto para almacenar el identificador -->
    <input type="hidden" id="identificador_paciente" name="identificador_paciente"
        value="<?= $identificador_paciente ?? $cedula_paciente ?? $historia_clinica ?? '' ?>">
    <!-- Campo código historia -->
    <div class="flex justify-end w-full px-2 py-1 mb-4">
        <input type="hidden" id="cod-historia" name="cod-historia"
            class="form-input w-20 h-6 text-xs shadow rounded border border-gray-300 bg-gray-100 text-gray-500"
            readonly />
    </div>
    
    <!-- Contenedor del formulario COMPACTO -->
    <div id="formulario-completo">
        <?= $this->include('formulario/seccion_a') ?>
        <?= $this->include('formulario/seccion_b') ?>
        <?= $this->include('formulario/seccion_g') ?>
        <?= $this->include('formulario/seccion_c') ?>
        <?= $this->include('formulario/seccion_d') ?>
        <?= $this->include('formulario/seccion_e') ?>
        <?= $this->include('formulario/seccion_f') ?>
        <?= $this->include('formulario/seccion_h') ?>
        <?= $this->include('formulario/seccion_i') ?>
        <?= $this->include('formulario/seccion_j') ?>
        <?= $this->include('formulario/seccion_k') ?>
        <?= $this->include('formulario/seccion_l') ?>
        <?= $this->include('formulario/seccion_m') ?>
        <?= $this->include('formulario/seccion_n') ?>
        <?= $this->include('formulario/seccion_o') ?>
        <?= $this->include('formulario/seccion_p') ?>
    </div>

<?php else: ?>
    <div class="bg-red-100 text-red-700 p-3 rounded border-l-4 border-red-500">
        <div class="flex items-center">
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
            No tienes permisos para acceder a esta sección.
        </div>
    </div>
<?php endif; ?>