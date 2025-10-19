<?= $this->include('templates/header') ?>

<?php if (session()->get('rol_id') == 1): ?>

    <!-- VISTA DUAL: FORMULARIO 008 + FORMULARIO 005 -->
    <div class="mb-4 bg-white p-4 rounded shadow">
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-xl font-semibold">📋 Vista Dual: Formularios 008 + 005</h2>
            <div class="flex items-center space-x-4">
                <div class="text-gray-600">
                    <?php if (!empty($cedula_paciente)): ?>
                        <span>Cedula: <span class="font-semibold text-blue-600"><?= $cedula_paciente ?></span></span>
                    <?php endif; ?>
                    <?php if (!empty($historia_clinica)): ?>
                        <span class="ml-4">Historia Clinica: <span
                                class="font-semibold text-green-600"><?= $historia_clinica ?></span></span>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- BARRA DE BÚSQUEDA -->
        <div class="flex items-center space-x-4 bg-gray-50 p-3 rounded mb-6">
            <label for="fecha" class="block text-sm font-medium text-gray-700">
                📅 Seleccionar fecha de atención:
            </label>

            <input type="date" id="filtro-fecha" name="filtro-fecha"
                class="border border-gray-300 rounded px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                value="<?= date('Y-m-d') ?>">

            <button type="button" id="btn-consultar-fecha"
                class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700 transition-colors duration-200 flex items-center space-x-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                </svg>
                <span>Consultar ambos formularios</span>
            </button>

            <!-- Botón PDF para Formulario 008 -->
            <button type="button" id="btn-generar-pdf-008"
                class="bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700 transition-colors duration-200 flex items-center space-x-2 disabled:opacity-50 disabled:cursor-not-allowed"
                disabled>
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z">
                    </path>
                </svg>
                <span>PDF 008</span>
            </button>
            
            <!-- Botón PDF para Formulario 005 -->
            <button type="button" id="btn-generar-pdf-005"
                class="bg-teal-600 text-white px-4 py-2 rounded hover:bg-teal-700 transition-colors duration-200 flex items-center space-x-2 disabled:opacity-50 disabled:cursor-not-allowed"
                disabled>
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z">
                    </path>
                </svg>
                <span>PDF 005</span>
            </button>
            <button type="button" id="btn-recargar"
                class="bg-purple-600 text-white px-3 py-2 rounded hover:bg-purple-700 transition-colors duration-200"
                onclick="window.location.reload();">
                Limpiar
            </button>
        </div>
        <!-- CONTENEDOR PRINCIPAL CON DOS COLUMNAS -->
        <div class="max-w-7xl mx-auto">
            <div class="flex flex-col lg:flex-row gap-6 w-full">

                <!-- COLUMNA IZQUIERDA: FORMULARIO 008 -->
                <div class="bg-blue-50 p-4 rounded-lg w-full lg:w-1/2 flex-1">
                    <div class="bg-blue-600 text-white px-4 py-2 rounded-t-lg mb-4">
                        <h3 class="text-lg font-semibold">📋 FORMULARIO 008 - Formulario Completo</h3>
                    </div>

                    <!-- Contenedor del formulario 008 -->
                    <div id="contenedor-formulario-008" class="bg-white rounded-lg p-2 overflow-auto max-h-screen">
                        <?= $this->include('administrador/formulario_completo_contenido') ?>
                    </div>
                </div>

            </div>

        </div>
        <div class="max-w-7xl mx-auto">
            <div class="flex flex-col lg:flex-row gap-6 w-full">

                <!-- COLUMNA DERECHA: FORMULARIO 005 -->
                <div class="bg-green-50 p-4 rounded-lg w-full lg:w-1/2 flex-1">
                    <div class="bg-green-600 text-white px-4 py-2 rounded-t-lg mb-4">
                        <h3 class="text-lg font-semibold">🩺 FORMULARIO 005 - Evolución y Prescripciones</h3>
                    </div>

                    <!-- Contenedor del formulario 005 -->
                    <div id="contenedor-formulario-005" class="bg-white rounded-lg p-2 overflow-auto max-h-screen">
                        <?= $this->include('administrador/formulario_completo005') ?>
                    </div>
                </div>

            </div>

        </div>

    </div>
<!-- Configuración global -->
    <script>
        window.APP_URLS = window.APP_URLS || {};
        window.APP_URLS.baseUrl = '<?= base_url() ?>';
        window.APP_URLS.buscarPorFecha = '<?= base_url('administrador/datos-pacientes/buscar-por-fecha') ?>';
        window.APP_URLS.buscarEvolucionPorFecha = '<?= base_url('administrador/datos-pacientes/buscar-evolucion-por-fecha') ?>';

        window.formularioInfo = {
            identificador: '<?= $identificador_paciente ?? $cedula_paciente ?? $historia_clinica ?? '' ?>',
            cedula: '<?= $cedula_paciente ?? '' ?>',
            historia_clinica: '<?= $historia_clinica ?? '' ?>'
        };

    </script>

    <!-- Carga de librerías y scripts -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    
    <script>
        if (window.jspdf) {
            window.jsPDF = window.jspdf.jsPDF;
        }
        
        function cargarScriptsFormulario() {
            const scriptApp = document.createElement('script');
            scriptApp.src = '<?= base_url('public/js/administrador/formulario008PDF.js') ?>?v=<?= time() ?>';
            scriptApp.onload = () => {
                const scriptBusqueda = document.createElement('script');
                scriptBusqueda.src = '<?= base_url('public/js/administrador/buscarPorFecha.js') ?>?v=<?= time() ?>';
                scriptBusqueda.onload = () => {
                    // Cargar directamente el script del formulario 005
                    const scriptFormulario005 = document.createElement('script');
                    scriptFormulario005.src = '<?= base_url('public/js/administrador/formulario005.js') ?>?v=<?= time() ?>';
                    scriptFormulario005.onload = () => {
                            // Cargar script PDF del formulario 005
                            const scriptPDF005 = document.createElement('script');
                            scriptPDF005.src = '<?= base_url('public/js/administrador/formulario005PDF.js') ?>?v=<?= time() ?>';
                            document.head.appendChild(scriptPDF005);
                        };
                        document.head.appendChild(scriptFormulario005);
                    };
                document.head.appendChild(scriptBusqueda);
            };
            document.head.appendChild(scriptApp);
        }
        
        if (typeof window.jsPDF !== 'undefined') {
            cargarScriptsFormulario();
        } else {
            setTimeout(cargarScriptsFormulario, 1000);
        }
    </script>

    <!-- Script para convertir campos específicos -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const camposParaConvertir = ['prof_firma', 'prof_sello'];

            camposParaConvertir.forEach(idCampo => {
                const campo = document.getElementById(idCampo);
                if (campo && campo.type === 'file') {
                    const campoTexto = document.createElement('input');
                    campoTexto.type = 'text';
                    campoTexto.id = campo.id;
                    campoTexto.name = campo.name;
                    campoTexto.className = 'form-input bg-gray-100 text-gray-600';
                    campoTexto.placeholder = `${campo.id} - Solo lectura`;
                    campoTexto.readOnly = true;

                    campo.parentNode.replaceChild(campoTexto, campo);

                    const contenedor = campoTexto.closest('.file-upload-container');
                    if (contenedor) {
                        const label = contenedor.querySelector('label');
                        const preview = contenedor.querySelector('.image-preview');
                        const small = contenedor.querySelector('small');

                        if (label) label.style.display = 'none';
                        if (preview) preview.style.display = 'none';
                        if (small) small.style.display = 'none';
                    }
                }
            });
        });
    </script>
    
<?php else: ?>
        <div class="bg-red-100 text-red-700 p-4 rounded border-l-4 border-red-500">
            <div class="flex items-center">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                No tienes permisos para acceder a esta sección.
            </div>
        </div>
    <?php endif; ?>

    <?= $this->include('templates/footer') ?>