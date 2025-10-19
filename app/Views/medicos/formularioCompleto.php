<?= $this->include('templates/alertas') ?>
<?= $this->include('templates/header') ?>

<!-- Verificar que el usuario sea médico -->
<?php if (session()->get('rol_id') == 4): ?>

<div id="contenedor-alertas" class="space-y-2"></div>

<!-- Cerrar sesión -->
<?= $this->include('templates/cerrar_sesion') ?>

<!-- este formulario es para el medico para cuando un pacinete este inconciente y pueda llenar completo el formulario
 del paciente desde el inicio hasta el final -->
<body class="bg-gray-100">
    
    <!-- Título especial para formulario completo -->
    <div class="container-fluid mb-3">
        <div class="alert alert-warning border-l-4 border-yellow-500 bg-yellow-50">
            <div class="flex">
                <div class="flex-shrink-0">
                    <i class="fas fa-exclamation-triangle text-yellow-500 text-xl"></i>
                </div>
                <div class="ml-3">
                    <h3 class="text-lg font-medium text-yellow-800">
                        📋 Formulario Completo - Paciente Inconsciente
                    </h3>
                    <div class="mt-2 text-sm text-yellow-700">
                        <p>Este formulario está diseñado para registrar pacientes que ingresan inconscientes.</p>
                        <p><strong>Complete todos los datos disponibles:</strong> Información del paciente, constantes vitales y evaluación médica completa.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Header con número de historia -->
    <div class="flex justify-end w-full px-20 py-2">
        <input type="hidden" id="cod-historia" name="cod-historia"
            class="form-input w-20 h-9 text-sm shadow rounded-md border border-gray-300 bg-gray-100 text-gray-500 cursor-not-allowed"
            readonly />
    </div>

    <!-- Selector de fuente de datos -->
    <div class="flex gap-2 px-20 mb-4">
        <label class="flex items-center">
            <input type="radio" name="fuente_datos" value="local" checked class="mr-2"> 
            <span class="text-sm">Base Local</span>
        </label>
        <label class="flex items-center">
            <input type="radio" name="fuente_datos" value="hospital" class="mr-2"> 
            <span class="text-sm">Base del Hospital</span>
        </label>
    </div>

    <!-- Buscador -->
    <?= $this->include('templates/buscador.php') ?>

    <!-- FORMULARIO PRINCIPAL -->
    <form method="post" action="<?= base_url('medicos/guardarFormularioCompleto') ?>" id="formMedicoCompleto" enctype="multipart/form-data">
        <div id="form-secciones">
            
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

        <!-- BOTONES DE ACCIÓN -->
        <div class="flex justify-center space-x-4 mt-8 mb-6">
            <button type="button" onclick="window.location.href='<?= base_url('medicos/lista') ?>'" 
                    class="btn bg-gray-500 hover:bg-gray-600 text-white px-6 py-3 rounded-lg">
                <i class="fas fa-arrow-left mr-2"></i> Cancelar
            </button>
            
            <button type="submit" class="btn bg-blue-600 hover:bg-blue-700 text-white px-6 py-3 rounded-lg">
                <i class="fas fa-save mr-2"></i> Guardar Formulario Completo
            </button>
        </div>
        
    </form>

    <!-- SOLO INCLUIR EL JAVASCRIPT ESPECÍFICO PARA FORMULARIO COMPLETO -->
    <script src="<?= base_url('public/js/buscar_formulario_completo.js') ?>"></script>
    
    <!-- JavaScript específico para formulario completo -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            
            // Configurar rutas base correctas para el contexto de médicos
            window.BASE_URL_MEDICOS = '<?= base_url() ?>';
            
            // Mostrar alerta de confirmación antes de enviar
            const form = document.getElementById('formMedicoCompleto');
            
            if (form) {
                form.addEventListener('submit', function(e) {
                    const confirmacion = confirm(
                        '¿Está seguro de guardar este formulario completo?\n\n' +
                        'Se registrará un nuevo paciente con todos los datos médicos proporcionados.\n\n' +
                        'Esta acción no se puede deshacer.'
                    );
                    
                    if (!confirmacion) {
                        e.preventDefault();
                        return false;
                    }
                });
            }

            // 🔥 CORRECCIÓN: Obtener datos del médico correctamente separados
            const medicoCompleto = "<?= session()->get('usu_nombre') ?? '' ?>";
            const apellidoCompleto = "<?= session()->get('usu_apellido') ?? '' ?>";
            
            // Separar nombres correctamente
            const nombres = medicoCompleto.trim().split(' ');
            const primerNombre = nombres[0] || '';
            
            // Separar apellidos correctamente  
            const apellidos = apellidoCompleto.trim().split(' ');
            const primerApellido = apellidos[0] || '';
            const segundoApellido = apellidos[1] || '';

            // Llenar automáticamente los datos del profesional responsable
            const profPrimerNombre = document.querySelector('input[name="prof_primer_nombre"]');
            const profPrimerApellido = document.querySelector('input[name="prof_primer_apellido"]');
            const profSegundoApellido = document.querySelector('input[name="prof_segundo_apellido"]');
            const profDocumento = document.querySelector('input[name="prof_documento"]');
            
            if (profPrimerNombre && primerNombre) {
                profPrimerNombre.value = primerNombre;
            }
            if (profPrimerApellido && primerApellido) {
                profPrimerApellido.value = primerApellido;
            }
            if (profSegundoApellido && segundoApellido) {
                profSegundoApellido.value = segundoApellido;
            }
            
            // 🔥 NUEVO: Intentar obtener el documento desde el servidor
            // Nota: necesitarás pasar este dato desde el controlador
            const documentoMedico = '<?= $medico_actual["documento"] ?? "" ?>';
            if (profDocumento && documentoMedico) {
                profDocumento.value = documentoMedico;
            } else {
                console.warn('Documento del médico no disponible');
            }

            
        });
    </script>

<?php else: ?>
    <div class="bg-red-100 text-red-700 p-4 rounded">
        ❌ No tienes permisos para acceder a esta sección. Solo los médicos pueden usar el formulario completo.
    </div>
<?php endif; ?>

<?= $this->include('templates/footer') ?>