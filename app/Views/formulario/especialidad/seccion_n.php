<!-- SECCION N ESPECIALIDADES -->
<div class="diagnostico-table-container bg-white shadow-xl rounded-lg overflow-hidden mt-6">
    <table class="w-full table-auto">
        <thead>
            <tr>
                <th colspan="7" class="header-main">N. PLAN DE TRATAMIENTO</th>
            </tr>
            <tr>
                <th class="subheader text-center" style="width:2em;"></th>
                <th class="subheader">Medicamentos</th>
                <th class="subheader">Vía</th>
                <th class="subheader">Dosis</th>
                <th class="subheader">Posología</th>
                <th class="subheader">Días</th>
                <th class="subheader text-center" style="width:120px;">ADMINISTRADO</th>
            </tr>
        </thead>
        <tbody id="tratamientos-tbody">
        <script>
            (function() {
                const tbody = document.getElementById('tratamientos-tbody');
                if (!tbody) return;

                // Generar 7 filas de tratamientos
                for (let i = 1; i <= 7; i++) {
                    const fila = crearFilaTratamiento(i);
                    tbody.appendChild(fila);
                }

                // Agregar fila de observaciones al final
                const filaObservaciones = document.createElement('tr');
                filaObservaciones.innerHTML = `
                    <td colspan="7">
                        <textarea id="plan_tratamiento" name="plan_tratamiento" class="form-textarea" rows="1"
                            placeholder="Observaciones..."></textarea>
                    </td>
                `;
                tbody.appendChild(filaObservaciones);

                function crearFilaTratamiento(numero) {
                    const fila = document.createElement('tr');
                    fila.innerHTML = `
                        <td class="examen-fisico-numeral text-center">${numero}</td>
                        <td><input type="text" id="trat_med${numero}" name="trat_med${numero}" class="form-input"></td>
                        <td><input type="text" id="trat_via${numero}" name="trat_via${numero}" class="form-input"></td>
                        <td><input type="text" id="trat_dosis${numero}" name="trat_dosis${numero}" class="form-input"></td>
                        <td><input type="text" id="trat_posologia${numero}" name="trat_posologia${numero}" class="form-input"></td>
                        <td><input type="number" id="trat_dias${numero}" name="trat_dias${numero}" class="form-input" min="1"></td>
                        <td class="text-center">
                            <button type="button"
                                    class="inline-flex items-center justify-center w-8 h-8 bg-green-100 hover:bg-green-200 text-green-600 hover:text-green-700 rounded-full transition-colors btn-administrado-tratamiento"
                                    id="btn_administrado${numero}"
                                    data-row="${numero}"
                                    title="Marcar como administrado"
                                    onclick="toggleAdministradoTratamiento(this)">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                </svg>
                            </button>
                            <input type="hidden" name="trat_administrado${numero}" id="trat_administrado${numero}" value="0">
                            <input type="hidden" name="trat_id${numero}" id="trat_id${numero}" value="">
                        </td>
                    `;
                    return fila;
                }
            })();
        </script>
        </tbody>

        
    </table>

    <!-- NUEVA SECCIÓN: Datos del especialista que completa esta sección -->
<div class="border-t-2 border-blue-500 bg-blue-50 p-4">
    <h4 class="text-lg font-bold text-blue-800 mb-4 flex items-center">
        <i class="fas fa-user-md mr-2"></i>
        Especialista Responsable de esta Sección
        <?php if (!empty($es_continuacion_proceso)): ?>
            <span class="ml-2 text-sm text-orange-600">(Proceso Guardado Previamente)</span>
        <?php endif; ?>
    </h4>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
        <div>
            <label for="esp_primer_nombre_n" class="block text-sm font-medium text-gray-700 mb-1">Primer Nombre *</label>
            <input type="text" id="esp_primer_nombre_n" name="esp_primer_nombre_n"
                class="form-input <?= (!empty($es_continuacion_proceso)) ? 'bg-gray-100' : '' ?>" 
                <?= (!empty($es_continuacion_proceso)) ? 'readonly' : '' ?>
                value="<?php 
                    if (!empty($es_continuacion_proceso) && !empty($medico_que_guardo_proceso['primer_nombre'])) {
                        echo htmlspecialchars($medico_que_guardo_proceso['primer_nombre']);
                    } else {
                        echo htmlspecialchars($medico_actual['primer_nombre'] ?? '');
                    }
                ?>"
                required readonly>
        </div>

        <div>
            <label for="esp_primer_apellido_n" class="block text-sm font-medium text-gray-700 mb-1">Primer Apellido *</label>
            <input type="text" id="esp_primer_apellido_n" name="esp_primer_apellido_n"
                class="form-input <?= (!empty($es_continuacion_proceso)) ? 'bg-gray-100' : '' ?>" 
                <?= (!empty($es_continuacion_proceso)) ? 'readonly' : '' ?>
                value="<?php 
                    if (!empty($es_continuacion_proceso) && !empty($medico_que_guardo_proceso['primer_apellido'])) {
                        echo htmlspecialchars($medico_que_guardo_proceso['primer_apellido']);
                    } else {
                        echo htmlspecialchars($medico_actual['primer_apellido'] ?? '');
                    }
                ?>"
                required readonly>
        </div>

        <div>
            <label for="esp_segundo_apellido_n" class="block text-sm font-medium text-gray-700 mb-1">Segundo Apellido</label>
            <input type="text" id="esp_segundo_apellido_n" name="esp_segundo_apellido_n"
                class="form-input <?= (!empty($es_continuacion_proceso)) ? 'bg-gray-100' : '' ?>" 
                <?= (!empty($es_continuacion_proceso)) ? 'readonly' : '' ?>
                value="<?= htmlspecialchars($medico_que_guardo_proceso['segundo_apellido'] ?? $medico_actual['segundo_apellido'] ?? '') ?>" readonly>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
        <div>
            <label for="esp_documento_n" class="block text-sm font-medium text-gray-700 mb-1">Documento *</label>
            <input type="text" id="esp_documento_n" name="esp_documento_n"
                class="form-input <?= (!empty($es_continuacion_proceso)) ? 'bg-gray-100' : '' ?>" 
                <?= (!empty($es_continuacion_proceso)) ? 'readonly' : '' ?>
                value="<?= htmlspecialchars($medico_que_guardo_proceso['documento'] ?? $medico_actual['documento'] ?? '') ?>"
                required readonly>
        </div>

        <div>
            <label for="esp_especialidad_n" class="block text-sm font-medium text-gray-700 mb-1">Especialidad</label>
            <input type="text" id="esp_especialidad_n" name="esp_especialidad_n" class="form-input bg-gray-100"
                value="<?= htmlspecialchars($medico_que_guardo_proceso['especialidad'] ?? $medico_responsable['especialidad'] ?? 'No especificada') ?>"
                readonly>
        </div>

        <div>
            <label for="esp_fecha_n" class="block text-sm font-medium text-gray-700 mb-1">Fecha *</label>
            <input type="date" id="esp_fecha_n" name="esp_fecha_n" class="form-input"
                value="<?= $medico_que_guardo_proceso['fecha_guardado'] ?? date('Y-m-d') ?>" required readonly>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div>
            <label for="esp_hora_n" class="block text-sm font-medium text-gray-700 mb-1">Hora *</label>
            <input type="time" id="esp_hora_n" name="esp_hora_n" class="form-input"
                value="<?= $medico_que_guardo_proceso['hora_guardado'] ?? date('H:i') ?>" required readonly>
        </div>

        <div>
            <label for="esp_firma_n" class="block text-sm font-medium text-gray-700 mb-1">Firma (Imagen)</label>
            <div class="file-upload-container">
                <input type="file" id="esp_firma_n" name="esp_firma_n"
                       class="form-input-file"
                       accept="image/png,image/jpeg,image/jpg"
                       onchange="previewImage(this, 'firma-n-preview')">
                <label for="esp_firma_n" class="file-upload-label">
                    <i class="fas fa-camera mr-2"></i>Subir Firma
                </label>
                <div id="firma-n-preview" class="image-preview"></div>
                <small class="text-gray-500">PNG, JPG, JPEG. Máx 2MB</small>
            </div>
        </div>

        <div>
            <label for="esp_sello_n" class="block text-sm font-medium text-gray-700 mb-1">Sello (Imagen)</label>
            <div class="file-upload-container">
                <input type="file" id="esp_sello_n" name="esp_sello_n"
                       class="form-input-file"
                       accept="image/png,image/jpeg,image/jpg"
                       onchange="previewImage(this, 'sello-n-preview')">
                <label for="esp_sello_n" class="file-upload-label">
                    <i class="fas fa-stamp mr-2"></i>Subir Sello
                </label>
                <div id="sello-n-preview" class="image-preview"></div>
                <small class="text-gray-500">PNG, JPG, JPEG. Máx 2MB</small>
            </div>
        </div>
    </div>
</div>

</div>

<!-- Estilos CSS para la subida de archivos -->
<style>
    .file-upload-container {
        position: relative;
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 10px;
    }

    .form-input-file {
        display: none;
    }

    .file-upload-label {
        display: inline-flex;
        align-items: center;
        padding: 8px 16px;
        background-color: #3b82f6;
        color: white;
        border-radius: 6px;
        cursor: pointer;
        font-size: 14px;
        transition: background-color 0.2s;
    }

    .file-upload-label:hover {
        background-color: #2563eb;
    }

    .image-preview {
        max-width: 150px;
        max-height: 100px;
        border: 2px dashed #d1d5db;
        border-radius: 6px;
        display: flex;
        align-items: center;
        justify-content: center;
        min-height: 60px;
        background-color: #f9fafb;
    }

    .image-preview img {
        max-width: 100%;
        max-height: 100%;
        border-radius: 4px;
    }

    .image-preview.has-image {
        border-color: #10b981;
        background-color: #ecfdf5;
    }
</style>

<!-- JavaScript para preview de imágenes -->
<script>
    function previewImage(input, previewId) {
        const preview = document.getElementById(previewId);
        const file = input.files[0];

        if (file) {
            // Validar tamaño del archivo (2MB máximo)
            if (file.size > 2 * 1024 * 1024) {
                alert('El archivo es demasiado grande. Máximo 2MB permitido.');
                input.value = '';
                return;
            }

            // Validar tipo de archivo
            if (!file.type.match(/^image\/(png|jpeg|jpg)$/)) {
                alert('Solo se permiten archivos PNG, JPG o JPEG.');
                input.value = '';
                return;
            }

            const reader = new FileReader();
            reader.onload = function (e) {
                preview.innerHTML = `<img src="${e.target.result}" alt="Preview">`;
                preview.classList.add('has-image');
            };
            reader.readAsDataURL(file);
        } else {
            preview.innerHTML = '<i class="fas fa-image text-gray-400"></i>';
            preview.classList.remove('has-image');
        }
    }

    // Función para toggle del estado "administrado" en tratamientos (igual que en evolución y prescripciones)
    function toggleAdministradoTratamiento(button) {
        const row = button.getAttribute('data-row');
        const hiddenInput = document.getElementById(`trat_administrado${row}`);

        if (button.classList.contains('bg-green-100')) {
            // Cambiar a administrado (verde activo)
            button.classList.remove('bg-green-100', 'hover:bg-green-200', 'text-green-600', 'hover:text-green-700');
            button.classList.add('bg-green-600', 'hover:bg-green-700', 'text-white');
            button.setAttribute('title', 'Administrado');

            // Cambiar icono a check relleno
            const svg = button.querySelector('svg path');
            if (svg) svg.setAttribute('d', 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z');

            // Actualizar valor hidden
            hiddenInput.value = '1';
        } else {
            // Cambiar a no administrado (verde claro)
            button.classList.remove('bg-green-600', 'hover:bg-green-700', 'text-white');
            button.classList.add('bg-green-100', 'hover:bg-green-200', 'text-green-600', 'hover:text-green-700');
            button.setAttribute('title', 'Marcar como administrado');

            // Cambiar icono a check simple
            const svg = button.querySelector('svg path');
            if (svg) svg.setAttribute('d', 'M5 13l4 4L19 7');

            // Actualizar valor hidden
            hiddenInput.value = '0';
        }
    }
</script>