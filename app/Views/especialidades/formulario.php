
<?= $this->include('templates/header') ?>
<?php if (isset($es_modificacion) && $es_modificacion): ?>
    <script>window.esModificacion = true;</script>
    <!-- También agregar un input hidden para mayor seguridad -->
    <input type="hidden" name="es_modificacion" value="1">
    <input type="hidden" name="habilitado_por_admin" value="1">
<?php endif; ?>
<?php if (in_array(session()->get('rol_id'), [3, 5])): ?>
    <?php if (isset($es_modificacion) && $es_modificacion): ?>
        <!-- Alerta de Modificación Habilitada -->
        <div class="bg-orange-100 border border-orange-400 text-orange-700 px-4 py-3 rounded mb-4">
            <div class="flex">
                <div class="flex-shrink-0">
                    <i class="fas fa-edit text-orange-500 text-xl"></i>
                </div>
                <div class="ml-3">
                    <h3 class="text-lg font-medium text-orange-800">
                        📄 Formulario Habilitado para Modificación
                    </h3>
                    <div class="mt-2 text-sm text-orange-700">
                        <p><strong>Motivo:</strong>
                            <?= htmlspecialchars($motivo_modificacion ?? 'Modificación autorizada por administrador') ?></p>
                        <?php if (!empty($fecha_habilitacion)): ?>
                            <p><strong>Fecha de habilitación:</strong> <?= htmlspecialchars($fecha_habilitacion) ?></p>
                        <?php endif; ?>
                        <p class="mt-2 text-orange-600">
                            ⚠️ Puede realizar cambios y guardar <strong>UNA VEZ MÁS</strong>.
                            Los datos del formulario original se mantendrán hasta que guarde los cambios.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <body class="bg-gray-50 min-h-screen">
        <?= $this->include('templates/alertas') ?>
        <div class="container mx-auto px-4 py-6">
            <div class="max-w-7xl mx-auto">

                <!-- Header específico para especialidades -->
                <div class="container-fluid mb-3">
                    <div class="alert alert-info border-l-4 border-blue-500 bg-blue-50">
                        <div class="flex">
                            <div class="flex-shrink-0">
                                <i class="fas fa-hospital text-blue-500 text-xl"></i>
                            </div>
                            <div class="ml-3">
                                <h3 class="text-lg font-medium text-blue-800">
                                    🏥 Formulario Médico - Especialidad
                                </h3>
                                <div class="mt-2 text-sm text-blue-700">
                                    <p><strong>Especialidad:</strong>
                                        <?= htmlspecialchars($medico_responsable['especialidad'] ?? 'No especificada') ?>
                                    </p>
                                    <p><strong>Estado:</strong>
                                        <span
                                            class="badge-success"><?= $medico_responsable['are_estado'] ?? 'EN_ATENCION' ?></span>
                                    </p>
                                    <p><strong>Médico Responsable:</strong>
                                        <?= htmlspecialchars($medico_responsable['nombre_completo'] ?? 'No asignado') . ' ' .
                                            htmlspecialchars($medico_responsable['apellido_completo'] ?? '') ?>
                                    </p>
                                    <p class="text-xs text-gray-500 mt-1">
                                        <strong>Sesión iniciada por:</strong>
                                        <?= session()->get('usu_nombre') . ' ' . session()->get('usu_apellido') ?>
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

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

                <!-- Secciones A, B y G - Solo lectura (datos del paciente) -->
                <?= $this->include('formulario/seccion_a') ?>
                <?= $this->include('formulario/seccion_b') ?>
                <?= $this->include('formulario/seccion_g') ?>
                <?= $this->include('formulario/seccion_c') ?>
                <?= $this->include('formulario/seccion_d') ?>

                <!-- FORMULARIO PRINCIPAL PARA ESPECIALIDAD -->
                <form method="post" action="<?= base_url('especialidades/guardarFormulario') ?>" id="formEspecialidad"
                    enctype="multipart/form-data">

                    <!-- Campos ocultos específicos para especialidades -->
                    <input type="hidden" name="ate_codigo" value="<?= $ate_codigo ?? '' ?>">
                    <input type="hidden" name="are_codigo" id="are_codigo" value="<?= $are_codigo ?? '' ?>">
                    <input type="hidden" name="esp_codigo" value="<?= $area_atencion['esp_codigo'] ?? '' ?>">

                    <!-- SECCIONES MÉDICAS (E a P) -->
                    <?= $this->include('formulario/seccion_e') ?>
                    <?= $this->include('formulario/seccion_f') ?>
                    <?= $this->include('formulario/seccion_h') ?>
                    <?= $this->include('formulario/seccion_i') ?>
                    <?= $this->include('formulario/seccion_j') ?>
                    <?= $this->include('formulario/seccion_k') ?>
                    <?= $this->include('formulario/seccion_l') ?>
                    <?= $this->include('formulario/seccion_m') ?>
                    <?= $this->include('formulario/especialidad/seccion_n') ?>

                    <!-- SECCIONES FINALES (OCULTAS INICIALMENTE) -->
                    <?= $this->include('formulario/especialidad/seccion_o') ?>
                    <?= $this->include('formulario/especialidad/seccion_p') ?>

                    <!-- CONTENEDOR PARA ELEMENTOS DINÁMICOS JS -->
                    <div id="contenedor-dinamico-js">
                        <!-- Los botones, modal y otros elementos se crearán aquí por JavaScript -->
                    </div>

                </form>
            </div>
        </div>

        <script>
            // Variables específicas para especialidades
            window.contextoEspecialidad = true;
            window.contextoMedico = <?= json_encode(!($contextoEnfermeria ?? false)) ?>;
            window.contextoEnfermeria = <?= json_encode($contextoEnfermeria ?? false) ?>;
            window.esEnfermeriaEspecialidad = <?= json_encode($esEnfermeriaEspecialidad ?? false) ?>;
            window.ocultarSeccionesOyP = <?= json_encode($ocultarSeccionesOyP ?? false) ?>;

            // Variables de códigos necesarios
            window.ate_codigo = <?= json_encode($ate_codigo ?? '') ?>;
            window.are_codigo = <?= json_encode($are_codigo ?? '') ?>;

            // Variables para modo modificación (igual que médicos)
            window.modoModificacion = true;
            window.precargarDatosModificadosEspecialistas = true;

            // Variables para modificación de ESPECIALISTA
            window.esModificacionEspecialista = <?= json_encode($es_modificacion ?? false) ?>;
            window.motivoModificacionEspecialista = <?= json_encode($motivo_modificacion ?? '') ?>;
            window.fechaHabilitacionEspecialista = <?= json_encode($fecha_habilitacion ?? '') ?>;

            // Nueva variable: Para continuación de proceso
            window.esContinuacionProceso = <?= json_encode($es_continuacion_proceso ?? false) ?>;
            window.mensajeContinuacion = <?= json_encode($mensaje_continuacion ?? '') ?>;
            window.medico_que_guardo_proceso = <?= json_encode($medico_que_guardo_proceso ?? []) ?>;

            // Variables para precarga de datos
            window.precargarDatosEspecialidades = <?= json_encode($precargar_datos ?? false) ?>;
            window.datosPacienteEspecialidades = <?= json_encode($datos_paciente_mapeados ?? []) ?>;
            window.datosAtencionEspecialidades = <?= json_encode($datos_atencion_mapeados ?? []) ?>;
            window.datosConstantesVitalesEspecialidades = <?= json_encode($datos_constantes_vitales_mapeados ?? []) ?>;

            // Variables de observación
            window.contextoObservacion = <?= json_encode($contextoObservacion ?? false) ?>;
            window.esObservacionEmergencia = <?= json_encode($esObservacionEmergencia ?? false) ?>;
            window.especialidad_codigo = <?= json_encode($area_atencion['esp_codigo'] ?? '') ?>;
            window.datosObservacionGuardados = <?= json_encode($datosObservacionGuardados ?? []) ?>;
            window.medico_que_guardo_especialidad = <?= json_encode($medico_que_guardo_especialidad ?? []) ?>;

            // Datos de secciones C, D y G
            window.datosSeccionCEspecialidades = <?= json_encode($datos_seccionC_mapeados ?? []) ?>;
            window.datosSeccionDEspecialidades = <?= json_encode($datos_seccionD_mapeados ?? []) ?>;
            window.datosSeccionGEspecialidades = <?= json_encode($datos_seccionG_mapeados ?? []) ?>;

            // Datos del formulario guardado para proceso parcial
            window.datosFormularioGuardadoEspecialista = <?= json_encode($datosFormularioGuardadoEspecialista ?? []) ?>;

            // Datos del médico actual para sección P
            window.medicoActual = <?= json_encode($medico_actual ?? []) ?>;
            window.medico_responsable = <?= json_encode($medico_responsable ?? []) ?>;

            window.base_url = "<?= base_url() ?>";
        </script>

        <!-- Scripts específicos para especialidades -->
        <script src="<?= base_url('public/js/especialidades/interfazDinamica.js') ?>"></script>
        <script src="<?= base_url('public/js/especialidades/envioObservacion.js') ?>"></script>
        <script src="<?= base_url('public/js/especialidades/bloquearSeccionesEspecialidad.js') ?>"></script>
        <script src="<?= base_url('public/js/especialidades/evolucionPrescripciones.js') ?>"></script>


        <!-- SCRIPT BASE UNIVERSAL - Siempre carga datos A,B,C,D,G -->
        <script src="<?= base_url('public/js/especialidades/precargarDatosEspecialidad.js') ?>"></script>

        <!-- Scripts específicos según contexto -->
        <?php if ((isset($es_continuacion_proceso) && $es_continuacion_proceso) || (isset($es_modificacion) && $es_modificacion)): ?>
            <!-- Script de precarga para continuación de proceso O modificación -->
            <script src="<?= base_url('public/js/especialidades/precargarDatosProcesoParcial.js') ?>"></script>

            <?php if (isset($es_modificacion) && $es_modificacion): ?>
                <script src="<?= base_url('public/js/especialidades/precargarDatosModificacionEspecialista.js') ?>"></script>
            <?php endif; ?>
        <?php endif; ?>

        <?php if (isset($contextoEnfermeria) && $contextoEnfermeria): ?>
            <!-- Script para datos básicos (A,B,C,D,G) necesarios para enfermería -->
            <script src="<?= base_url('public/js/especialidades/precargarDatosProcesoParcial.js') ?>"></script>

            <!-- Script específico para enfermería de especialidad (secciones E-N del especialista) -->
            <script src="<?= base_url('public/js/especialidades/precargarDatosEnfermeriaEspecialidad.js') ?>"></script>
        <?php else: ?>
            <!-- Script para EN ATENCIÓN con datos del especialista -->
            <?php if (isset($datosFormularioGuardadoEspecialista) && !empty($datosFormularioGuardadoEspecialista)): ?>
                <script src="<?= base_url('public/js/especialidades/precargarDatosAtencionEspecialidad.js') ?>"></script>
            <?php else: ?>
                <script>console.log('🔍 DEBUG PHP - NO cargando script de atención especialidad');</script>
            <?php endif; ?>
        <?php endif; ?>

        <!-- Nuevo script para observación -->
        <?php if (isset($contextoObservacion) && $contextoObservacion): ?>
            <script src="<?= base_url('public/js/especialidades/precargarDatosObservacionReutilizado.js') ?>"></script>
        <?php endif; ?>


    <?php else: ?>
        <div class="bg-red-100 text-red-700 p-4 rounded">
            ❌ No tiene permisos para acceder a esta sección.
        </div>
    <?php endif; ?>

    <?= $this->include('templates/footer') ?>