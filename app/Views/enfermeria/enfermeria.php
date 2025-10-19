<?= $this->include('templates/header') ?>

<?php if (session()->get('rol_id') == 3): ?>

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

    <body class="bg-gray-50 min-h-screen">
    
        <div class="container mx-auto px-4 py-6">
        <?= $this->include('templates/alertas') ?>
            <div class="max-w-7xl mx-auto">

                <?= $this->include('templates/informacionUsuarioFormulario') ?>
                <?= $this->include('formulario/seccion_a') ?>
                <?= $this->include('formulario/seccion_b') ?>


                <form method="post" action="<?= base_url('enfermeria/guardarEnfermeria') ?>" id="form">
                    <!-- Campo oculto para el ID del paciente -->
                    <?php if (isset($paciente_id)): ?>
                        <input type="hidden" name="paciente_id" value="<?= $paciente_id ?>">
                    <?php endif; ?>
                    <!-- Campo oculto para el código de atención -->
                    <?php if (isset($ate_codigo) && !empty($ate_codigo)): ?>
                        <input type="hidden" name="ate_codigo" value="<?= $ate_codigo ?>">
                    <?php endif; ?>
                    <!-- Sección G - Mostrar solo si es rol de enfermería y se ha cargado un paciente -->
                    <?php if (session()->get('rol_id') == 3 && isset($mostrar_seccion_g) && $mostrar_seccion_g): ?>
                        <!-- no agregar div porq se desordena la tabla -->
                        <?= $this->include('formulario/seccion_g') ?>

                    <?php endif; ?>

                    <!-- Botón guardar visible solo si hay formulario cargado -->
                    <?php if (in_array(session()->get('rol_id'), [3]) && isset($mostrar_seccion_g) && $mostrar_seccion_g): ?>
                        <div class="flex justify-end space-x-4 mt-6">
                            <a href="<?= base_url('enfermeria/lista') ?>" class="btn btn-secondary">
                                <i class="fas fa-arrow-left mr-2"></i> Volver a Lista
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save mr-2"></i> Guardar Sección G
                            </button>
                        </div>
                    <?php endif; ?>
                </form>
            </div>
        </div>
        <script>
            // Variables específicas para ENFERMERÍA
            window.contextoEnfermeria = true;
            window.contextoMedico = false;

            // Variables para precarga de datos - ESPECÍFICAS ENFERMERÍA
            window.precargarDatosEnfermeria = <?= json_encode($precargar_datos ?? false) ?>;
            window.datosPacienteEnfermeria = <?= json_encode($datos_paciente_mapeados ?? []) ?>;
            window.datosAtencionEnfermeria = <?= json_encode($datos_atencion_mapeados ?? []) ?>;

            // Variable para el nombre del admisionista original - ENFERMERÍA
            window.admisionistaEnfermeria = <?= json_encode($admisionista_original ?? '') ?>;
        </script>


    <?php else: ?>
        <div class="bg-red-100 text-red-700 p-4 rounded">
            ❌ No tienes permisos para acceder a esta sección.
        </div>
    <?php endif; ?>
    <?= $this->include('templates/footer') ?>