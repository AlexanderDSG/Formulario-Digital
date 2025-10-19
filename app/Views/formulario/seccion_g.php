<div class="bg-white shadow-xl rounded-lg overflow-hidden">
    <table class="w-full table-auto">
        <thead>
            <tr>
                <th colspan="6" class="header-main">
                    G. CONSTANTES VITALES Y ANTROPOMETRÍA
                    <label class="header-checkbox-label float-right" style="font-weight: normal; font-size: 0.75rem;">
                        <input type="checkbox" id="cv_sin_vitales" name="cv_sin_vitales" value="1" class="form-checkbox" onchange="toggleConstantesVitales()">
                        <span>Sin Constantes Vitales</span>
                    </label>
                </th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <th class="subheader"></th>
                <th class="subheader">Presión Arterial (mmHg)</th>
                <th class="subheader">Pulso/min</th>
                <th class="subheader">Frec. Resp./min</th>
                <th class="subheader">
                    <span style="color: #dc2626; font-weight: bold;">* Clasificación de Triaje</span>
                    <small style="display: block; color: #dc2626; font-weight: normal; font-size: 0.7rem;">(Obligatorio)</small>
                </th>
                <th class="subheader"></th>
            </tr>
            <tr>
                <td>
                    <span class="text-sm text-gray-600">Signos Vitales Básicos</span>
                </td>
                <td>
                    <input type="text" id="cv_presion_arterial" name="cv_presion_arterial"
                        class="form-input constante-vital-item" placeholder="Ej: 120/80">
                </td>
                <td>
                    <input type="text" id="cv_pulso" name="cv_pulso" class="form-input constante-vital-item"
                        placeholder="Ej: 70">
                </td>
                <td>
                    <input type="text" id="cv_frec_resp" name="cv_frec_resp" class="form-input constante-vital-item"
                        placeholder="Ej: 16">
                </td>
                <td>
                    <select id="cv_triaje_color" name="cv_triaje_color" class="form-select constante-vital-item" 
                            onchange="cambiarColorTriaje(this)" required 
                            style="border: 2px solid #dc2626;">
                        <option value="">⚠️ Seleccionar color de triaje...</option>
                        <option value="ROJO" style="background-color: #ff4444; color: white;">🔴 ROJO - Emergencia</option>
                        <option value="NARANJA" style="background-color: #ff8800; color: white;">🟠 NARANJA - Urgencia</option>
                        <option value="AMARILLO" style="background-color: #ffdd00; color: black;">🟡 AMARILLO - Urgencia Menor</option>
                        <option value="VERDE" style="background-color: #44aa44; color: white;">🟢 VERDE - No Urgente</option>
                        <option value="AZUL" style="background-color: #4444ff; color: white;">🔵 AZUL - Consulta</option>
                    </select>
                    <div id="triaje_error" style="color: #dc2626; font-size: 0.75rem; margin-top: 2px; display: none;">
                        El color de triaje es obligatorio
                    </div>
                </td>
                <td></td>
            </tr>
            <tr>
                <th class="subheader">Pulsioximetría (%)</th>
                <th class="subheader">Perímetro Cefálico (cm)</th>
                <th class="subheader">Peso (kg)</th>
                <th class="subheader">Talla (cm)</th>
                <th class="subheader">Glicemia Capilar (mg/dl)</th>
                <th class="subheader">Tiempo (min)</th>
            </tr>
            <tr>
                <td>
                    <input type="text" id="cv_pulsioximetria" name="cv_pulsioximetria" class="form-input constante-vital-item"
                        placeholder="Ej: 98">
                </td>
                <td>
                    <input type="text" id="cv_perimetro_cefalico" name="cv_perimetro_cefalico"
                        class="form-input constante-vital-item" placeholder="Ej: 35">
                </td>
                <td>
                    <input type="text" id="cv_peso" name="cv_peso" class="form-input constante-vital-item"
                        placeholder="Ej: 70.5">
                </td>
                <td>
                    <input type="text" id="cv_talla" name="cv_talla" class="form-input constante-vital-item"
                        placeholder="Ej: 1.70">
                </td>
                <td>
                    <input type="text" id="cv_glicemia" name="cv_glicemia" class="form-input constante-vital-item"
                        placeholder="Ej: 90">
                </td>
                <td>
                    <input type="text" id="cv_tiempo_atencion" name="cv_tiempo_atencion" class="form-input"
                        placeholder="Tiempo estimado" readonly style="background-color: #f0f0f0;">
                </td>
            </tr>
            <tr>
                <th class="subheader">Glasgow Inicial: Ocular (4)</th>
                <th class="subheader">Verbal (5)</th>
                <th class="subheader">Motora (6)</th>
                <th class="subheader">Reacción Pupilar Der.</th>
                <th class="subheader">Reacción Pupilar Izq.</th>
                <th class="subheader">T. Llenado Capilar (seg)</th>
            </tr>
            <tr>
                <td>
                    <input type="text" id="cv_glasgow_ocular" name="cv_glasgow_ocular" class="form-input constante-vital-item"
                        min="1" max="4" placeholder="Ej: 1-4">
                </td>
                <td>
                    <input type="text" id="cv_glasgow_verbal" name="cv_glasgow_verbal" class="form-input constante-vital-item"
                        min="1" max="5" placeholder="Ej: 1-5">
                </td>
                <td>
                    <input type="text" id="cv_glasgow_motora" name="cv_glasgow_motora" class="form-input constante-vital-item"
                        min="1" max="6" placeholder="Ej: 1-6">
                </td>
                <td>
                    <input type="text" id="cv_reaccion_pupilar_der" name="cv_reaccion_pupilar_der"
                        class="form-input constante-vital-item" placeholder="Normal/Lenta/Fija">
                </td>
                <td>
                    <input type="text" id="cv_reaccion_pupilar_izq" name="cv_reaccion_pupilar_izq"
                        class="form-input constante-vital-item" placeholder="Normal/Lenta/Fija">
                </td>
                <td>
                    <input type="text" id="cv_llenado_capilar" name="cv_llenado_capilar" class="form-input constante-vital-item"
                        placeholder="Ej: <2 seg">
                </td>
            </tr>
        </tbody>
    </table>
</div>

<script>
// ✅ FUNCIÓN TOGGLE CORREGIDA - IGUAL QUE SECCIÓN K
function toggleConstantesVitales() {
    const sinVitalesCheckbox = document.getElementById('cv_sin_vitales');
    const constantesInputs = document.querySelectorAll('.constante-vital-item');
    const tiempoAtencion = document.getElementById('cv_tiempo_atencion');
    const triajeSelect = document.getElementById('cv_triaje_color');

    if (sinVitalesCheckbox.checked) {
        // Si marca "Sin Constantes Vitales", deshabilitar todos los campos EXCEPTO el triaje
        constantesInputs.forEach(input => {
            if (input.id !== 'cv_triaje_color') { // NO deshabilitar el select de triaje
                if (input.type === 'text' || input.type === 'number') {
                    input.value = '';
                    input.disabled = true;
                    input.placeholder = 'Sin constantes vitales - Campo deshabilitado';
                } else if (input.type === 'select-one') {
                    input.selectedIndex = 0;
                    input.disabled = true;
                }
            }
        });

        // Limpiar tiempo
        tiempoAtencion.value = 'No aplica';
        tiempoAtencion.disabled = true;

        // Mantener el triaje habilitado y requerido
        triajeSelect.disabled = false;
        triajeSelect.style.border = '2px solid #dc2626';

        console.log('✅ Campos de constantes vitales deshabilitados (excepto triaje)');
    } else {
        // Si desmarca "Sin Constantes Vitales", habilitar todos los campos
        constantesInputs.forEach(input => {
            input.disabled = false;

            if (input.type === 'text' || input.type === 'number') {
                // Restaurar placeholders originales
                const placeholders = {
                    'cv_presion_arterial': 'Ej: 120/80',
                    'cv_pulso': 'Ej: 70',
                    'cv_frec_resp': 'Ej: 16',
                    'cv_pulsioximetria': 'Ej: 98',
                    'cv_perimetro_cefalico': 'Ej: 35',
                    'cv_peso': 'Ej: 70.5',
                    'cv_talla': 'Ej: 1.70',
                    'cv_glicemia': 'Ej: 90',
                    'cv_glasgow_ocular': '1-4',
                    'cv_glasgow_verbal': '1-5',
                    'cv_glasgow_motora': '1-6',
                    'cv_reaccion_pupilar_der': 'Normal/Lenta/Fija',
                    'cv_reaccion_pupilar_izq': 'Normal/Lenta/Fija',
                    'cv_llenado_capilar': 'Ej: <2 seg'
                };
                input.placeholder = placeholders[input.id] || '';
            }
        });

        tiempoAtencion.value = '';
        tiempoAtencion.disabled = false;
        console.log('✅ Campos de constantes vitales habilitados');
    }
}

function cambiarColorTriaje(select) {
    const tiempoAtencion = document.getElementById('cv_tiempo_atencion');
    const triajeError = document.getElementById('triaje_error');

    const colores = {
        'ROJO': {
            tiempo: 'Inmediato (0 min)',
            borderColor: '#ff4444'
        },
        'NARANJA': {
            tiempo: '15 minutos',
            borderColor: '#ff8800'
        },
        'AMARILLO': {
            tiempo: '60 minutos',
            borderColor: '#ffdd00'
        },
        'VERDE': {
            tiempo: '120 minutos',
            borderColor: '#44aa44'
        },
        'AZUL': {
            tiempo: '240 minutos',
            borderColor: '#4444ff'
        }
    };

    if (select.value && colores[select.value]) {
        const color = colores[select.value];

        // Solo actualizar tiempo si no está deshabilitado
        if (!tiempoAtencion.disabled) {
            tiempoAtencion.value = color.tiempo;
        }

        // Cambiar el color de fondo del select y quitar borde rojo
        select.style.backgroundColor = color.borderColor;
        select.style.color = select.value === 'AMARILLO' ? 'black' : 'white';
        select.style.border = '2px solid ' + color.borderColor;

        // Ocultar mensaje de error
        triajeError.style.display = 'none';
    } else {
        if (!tiempoAtencion.disabled) {
            tiempoAtencion.value = '';
        }
        select.style.backgroundColor = '';
        select.style.color = '';
        select.style.border = '2px solid #dc2626'; // Mantener borde rojo si no hay selección

        // Mostrar mensaje de error
        triajeError.style.display = 'block';
    }
}

// ✅ VALIDACIÓN ANTES DEL SUBMIT
function validarTriajeObligatorio() {
    const triajeSelect = document.getElementById('cv_triaje_color');
    const triajeError = document.getElementById('triaje_error');
    
    if (!triajeSelect.value) {
        triajeError.style.display = 'block';
        triajeSelect.style.border = '2px solid #dc2626';
        triajeSelect.focus();
        
        // Mostrar alerta
        alert('⚠️ El color de triaje es obligatorio. Por favor seleccione un color.');
        return false;
    }
    
    triajeError.style.display = 'none';
    return true;
}

// ✅ EVENT LISTENERS MEJORADOS
document.addEventListener('DOMContentLoaded', function() {
    const constantesInputs = document.querySelectorAll('.constante-vital-item');
    const sinVitalesCheckbox = document.getElementById('cv_sin_vitales');
    const triajeSelect = document.getElementById('cv_triaje_color');
    
    // Event listeners para inputs normales
    constantesInputs.forEach(input => {
        if (input.id !== 'cv_triaje_color') { // Excluir el select de triaje
            input.addEventListener('input', function() {
                if (this.value.trim() !== '') {
                    sinVitalesCheckbox.checked = false;
                }
            });
        }
    });
    
    // Event listener especial para el select de triaje
    triajeSelect.addEventListener('change', function() {
        if (this.selectedIndex > 0) {
            // No desmarcar "sin vitales" cuando se selecciona triaje
            // porque el triaje es independiente de las constantes vitales
            cambiarColorTriaje(this);
        }
    });
    
    // Interceptar el submit del formulario para validar triaje
    const form = document.querySelector('form');
    if (form) {
        form.addEventListener('submit', function(e) {
            if (!validarTriajeObligatorio()) {
                e.preventDefault();
                return false;
            }
        });
    }
});
</script>