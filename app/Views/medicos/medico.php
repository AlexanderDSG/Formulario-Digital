
<?= $this->include('templates/header') ?>
<?php if (isset($es_modificacion) && $es_modificacion): ?>
    <script>window.esModificacion = true;</script>
    <!-- También agregar un input hidden para mayor seguridad -->
    <input type="hidden" name="es_modificacion" value="1">
    <input type="hidden" name="habilitado_por_admin" value="1">
<?php endif; ?>
<?php if (session()->get('rol_id') == 4): ?>
    
    <body class="bg-gray-50 min-h-screen">
        
        <div class="container mx-auto px-4 py-6">
            <?= $this->include('templates/alertas') ?>
            <div class="max-w-7xl mx-auto">
                <?= $this->include('medicos/AlertasModificacion') ?>
                <?= $this->include('templates/informacionUsuarioFormulario') ?>

                <!-- Secciones A y B G - Solo lectura para médicos -->
                <?= $this->include('formulario/seccion_a') ?>
                <?= $this->include('formulario/seccion_b') ?>
                <?= $this->include('formulario/seccion_g') ?>

                <!-- 🔥 INICIO DEL FORMULARIO - TODO DEBE ESTAR DENTRO -->
                <form method="post" action="<?= base_url('medicos/guardarMedico') ?>" id="form"
                    enctype="multipart/form-data">

                    <!-- Campos ocultos -->
                    <?php if (isset($paciente_id)): ?>
                        <input type="hidden" name="paciente_id" value="<?= $paciente_id ?>">
                    <?php endif; ?>
                    <?php if (isset($ate_codigo) && !empty($ate_codigo)): ?>
                        <input type="hidden" name="ate_codigo" value="<?= $ate_codigo ?>">
                        <script>console.log('ate_codigo encontrado:', '<?= $ate_codigo ?>');</script>
                    <?php else: ?>
                        <script>console.error('ate_codigo NO encontrado en PHP');</script>
                    <?php endif; ?>

                    <!-- Secciones C y D -->
                    <?= $this->include('formulario/seccion_c') ?>
                    <?= $this->include('formulario/seccion_d') ?>

                    <!-- DECISIÓN MÉDICA -->
                    <?= $this->include('medicos/botonesDecisionMedica') ?>

                    <!-- RESTO DE SECCIONES (Inicialmente ocultas por JS) -->
                    <?= $this->include('formulario/seccion_e') ?>
                    <?= $this->include('formulario/seccion_f') ?>
                    <?= $this->include('formulario/seccion_h') ?>
                    <?= $this->include('formulario/seccion_i') ?>
                    <?= $this->include('formulario/seccion_j') ?>
                    <?= $this->include('formulario/seccion_k') ?>
                    <?= $this->include('formulario/seccion_l') ?>
                    <?= $this->include('formulario/seccion_m') ?>
                    <?= $this->include('formulario/medico/seccion_n') ?>
                    <?= $this->include('formulario/seccion_o') ?>
                    <?= $this->include('formulario/seccion_p') ?>

                    <!-- BOTONES DE ACCIÓN - DENTRO DEL FORM -->
                    <div class="flex justify-end space-x-4 mt-6">
                        <a href="<?= base_url('medicos/lista') ?>"
                            class="bg-gray-500 hover:bg-gray-600 text-white px-6 py-3 rounded-lg font-semibold flex items-center transition-colors duration-200">
                            <i class="fas fa-arrow-left mr-2"></i> Volver a Lista
                        </a>
                        <button type="submit" id="btn-guardar-formulario"
                            class="bg-blue-500 hover:bg-blue-600 text-white px-6 py-3 rounded-lg font-semibold flex items-center transition-colors duration-200">
                            <i class="fas fa-save mr-2"></i> Guardar Formulario
                        </button>
                    </div>

                    <!-- 🔥 FIN DEL FORMULARIO -->
                </form>

                <!-- MODAL PARA ASIGNAR ESPECIALIDAD - FUERA DEL FORM -->
                <?= $this->include('medicos/modalEspecialidad') ?>

            </div>
        </div>

        <!-- SCRIPTS JAVASCRIPT -->
        <script>
            window.base_url = '<?= base_url() ?>';
            // Variables globales para PHP -> JS
            window.contextoMedicoTriaje = true;
            window.contextoMedico = true;
            window.contextoEnfermeria = false;
            window.modoModificacion = true;
            window.precargarDatosModificadosMedicos = true;
            // Variables para precarga de datos
            window.precargarDatosMedicos = <?= json_encode($precargar_datos ?? false) ?>;
            window.datosPacienteMedicos = <?= json_encode($datos_paciente_mapeados ?? []) ?>;
            window.datosAtencionMedicos = <?= json_encode($datos_atencion_mapeados ?? []) ?>;
            window.datosConstantesVitalesMedicos = <?= json_encode($datos_constantes_vitales_mapeados ?? []) ?>;
            window.admisionistaMedicos = <?= json_encode($admisionista_original ?? '') ?>;
            window.modoMedicoActivo = <?= json_encode($mostrar_modo_medico ?? false) ?>;
            window.medicoActual = <?= json_encode($medico_actual ?? []) ?>;

            // 🔥 NOTA: La limpieza del sessionStorage ahora la maneja bloquearSecciones.js
            // para evitar conflictos con la lógica de ocultación de secciones
        </script>
        <script src="<?= base_url('public/js/medicos/evolucionPrescripciones.js') ?>"></script>

        <!-- Archivos JavaScript externos -->
        <script src="<?= base_url('public/js/medicos/bloquearSecciones.js') ?>"></script>
        <script src="<?= base_url('public/js/medicos/modalEspecialidades.js') ?>"></script>
        <?php if (isset($es_modificacion) && $es_modificacion && isset($datosFormularioGuardadoMedico)): ?>
            <script>
                // Datos guardados para modificación
                window.datosFormularioGuardadoMedico = <?= json_encode($datosFormularioGuardadoMedico) ?>;
            </script>
            <!-- Incluir el script de precarga para modificaciones -->
            <script src="<?= base_url('public/js/medicos/precargarDatosModificacionMedico.js') ?>"></script>
        <?php endif; ?>
    </body>

<?php else: ?>
    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded" role="alert">
        <div class="flex items-center">
            <i class="fas fa-exclamation-triangle mr-2"></i>
            <strong>Acceso Denegado:</strong> No tienes permisos para acceder a esta sección.
        </div>
    </div>
<?php endif; ?>

<?= $this->include('templates/footer') ?>