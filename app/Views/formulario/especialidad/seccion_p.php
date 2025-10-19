<!-- SECCION P -->
<div class="diagnostico-table-container bg-white shadow-xl rounded-lg overflow-hidden mt-6 seccion-p">
    <table class="w-full table-auto">
        <thead>
            <tr>
                <th colspan="5" class="header-main">P. DATOS DEL PROFESIONAL RESPONSABLE</th>
            </tr>
            <tr>
                <th class="subheader">Fecha</th>
                <th class="subheader">Hora</th>
                <th class="subheader">Primer Nombre</th>
                <th class="subheader">Primer Apellido</th>
                <th class="subheader">Segundo Apellido</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td><input type="date" id="prof_fecha" name="prof_fecha" class="form-input border-gray-300 bg-gray-100 text-gray-500 cursor-not-allowed" value="<?= date('Y-m-d') ?>"readonly></td>
                <td><input type="time" id="prof_hora" name="prof_hora" class="form-input" value="<?= date('H:i')?>"></td>
                <td><input type="text" id="prof_primer_nombre" name="prof_primer_nombre" class="form-input"
                        placeholder="Primer nombre"></td>
                <td><input type="text" id="prof_primer_apellido" name="prof_primer_apellido"
                        class="form-input" placeholder="Primer apellido"></td>
                <td><input type="text" id="prof_segundo_apellido" name="prof_segundo_apellido"
                        class="form-input" placeholder="Segundo apellido"></td>
            </tr>
            <tr>
                <th class="subheader">N° Documento de Identidad</th>
                <th class="subheader" colspan="2">Firma (Imagen)</th>
                <th class="subheader" colspan="2">Sello (Imagen)</th>
            </tr>
            <tr>
                <td><input type="text" id="prof_documento" name="prof_documento" class="form-input"
                        placeholder="Documento"></td>
                <td colspan="2">
                    <div class="file-upload-container">
                        <input type="file" id="prof_firma" name="prof_firma" 
                               class="form-input-file" 
                               accept="image/png,image/jpeg,image/jpg" 
                               onchange="previewImage(this, 'firma-preview')">
                        <label for="prof_firma" class="file-upload-label">
                            <i class="fas fa-camera mr-2"></i>Subir Firma
                        </label>
                        <div id="firma-preview" class="image-preview"></div>
                        <small class="text-gray-500">PNG, JPG, JPEG. Máx 2MB</small>
                    </div>
                </td>
                <td colspan="2">
                    <div class="file-upload-container">
                        <input type="file" id="prof_sello" name="prof_sello" 
                               class="form-input-file" 
                               accept="image/png,image/jpeg,image/jpg" 
                               onchange="previewImage(this, 'sello-preview')">
                        <label for="prof_sello" class="file-upload-label">
                            <i class="fas fa-stamp mr-2"></i>Subir Sello
                        </label>
                        <div id="sello-preview" class="image-preview"></div>
                        <small class="text-gray-500">PNG, JPG, JPEG. Máx 2MB</small>
                    </div>
                </td>
            </tr>
        </tbody>
    </table>
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