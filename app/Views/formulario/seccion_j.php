<div class="bg-white shadow-xl rounded-lg overflow-hidden">
    <table class="w-full table-auto">
        <thead>
            <tr>
                <th colspan="8" class="header-main">
                    J. EMBARAZO-PARTO
                    <label class="header-checkbox-label float-right" style="font-weight: normal; font-size: 0.65rem;">
                        <input type="checkbox" id="emb_no_aplica" name="emb_no_aplica" value="1" class="form-checkbox" onchange="toggleEmbarazo()">
                        <span>No aplica</span>
                    </label>
                </th>
            </tr>
        </thead>
        <tbody>
            <!-- Primera fila de datos básicos -->
            <tr>
                <th class="subheader">Gestas</th>
                <th class="subheader">Partos</th>
                <th class="subheader">Abortos</th>
                <th class="subheader">Cesáreas</th>
                <th class="subheader">F.U.M.</th>
                <th class="subheader">Semanas de gestación</th>
                <th class="subheader">Movimiento fetal</th>
                <th class="subheader">F.C.F.</th>
            </tr>
            <tr>
                <td><input type="number" id="emb_gestas" name="emb_gestas" class="form-input embarazo-item" min="0" placeholder="0"></td>
                <td><input type="number" id="emb_partos" name="emb_partos" class="form-input embarazo-item" min="0" placeholder="0"></td>
                <td><input type="number" id="emb_abortos" name="emb_abortos" class="form-input embarazo-item" min="0" placeholder="0"></td>
                <td><input type="number" id="emb_cesareas" name="emb_cesareas" class="form-input embarazo-item" min="0" placeholder="0"></td>
                <td><input type="date" id="emb_fum" name="emb_fum" class="form-input embarazo-item"></td>
                <td><input type="number" id="emb_semanas_gestacion" name="emb_semanas_gestacion" class="form-input embarazo-item" min="0" max="42" placeholder="0"></td>
                <td>
                    <div class="flex flex-col">
                        <label class="radio-label">
                            <input type="radio" id="emb_movimiento_fetal_si" name="emb_movimiento_fetal" value="si" class="form-radio embarazo-item">
                            <span>Sí</span>
                        </label>
                        <label class="radio-label">
                            <input type="radio" id="emb_movimiento_fetal_no" name="emb_movimiento_fetal" value="no" class="form-radio embarazo-item">
                            <span>No</span>
                        </label>
                    </div>
                </td>
                <td><input type="number" id="emb_fcf" name="emb_fcf" class="form-input embarazo-item" min="0" placeholder="FCF"></td>
            </tr>

            <!-- Segunda fila -->
            <tr>
                <th class="subheader">Ruptura membranas</th>
                <th class="subheader">Tiempo (horas)</th>
                <th class="subheader">A.F.U. (cm)</th>
                <th class="subheader">Presentación</th>
                <th class="subheader">Sangrado vaginal</th>
                <th class="subheader">Contracciones</th>
                <th class="subheader">Dilatación (cm)</th>
                <th class="subheader">Borramiento (%)</th>
            </tr>
            <tr>
                <td>
                    <div class="flex flex-col">
                        <label class="radio-label">
                            <input type="radio" id="emb_ruptura_membranas_si" name="emb_ruptura_membranas" value="si" class="form-radio embarazo-item">
                            <span>Sí</span>
                        </label>
                        <label class="radio-label">
                            <input type="radio" id="emb_ruptura_membranas_no" name="emb_ruptura_membranas" value="no" class="form-radio embarazo-item">
                            <span>No</span>
                        </label>
                    </div>
                </td>
                <td><input type="number" id="emb_tiempo_ruptura" name="emb_tiempo_ruptura" class="form-input embarazo-item" min="0" placeholder="0"></td>
                <td><input type="number" id="emb_afu" name="emb_afu" class="form-input embarazo-item" min="0" step="0.1" placeholder="0.0"></td>
                <td>
                    <select id="emb_presentacion" name="emb_presentacion" class="form-select embarazo-item">
                        <option value="">Seleccionar...</option>
                        <option value="cefalica">Cefálica</option>
                        <option value="podalica">Podálica</option>
                        <option value="transversa">Transversa</option>
                    </select>
                </td>
                <td>
                    <div class="flex flex-col">
                        <label class="radio-label">
                            <input type="radio" id="emb_sangrado_vaginal_si" name="emb_sangrado_vaginal" value="si" class="form-radio embarazo-item">
                            <span>Sí</span>
                        </label>
                        <label class="radio-label">
                            <input type="radio" id="emb_sangrado_vaginal_no" name="emb_sangrado_vaginal" value="no" class="form-radio embarazo-item">
                            <span>No</span>
                        </label>
                    </div>
                </td>
                <td>
                    <div class="flex flex-col">
                        <label class="radio-label">
                            <input type="radio" id="emb_contracciones_si" name="emb_contracciones" value="si" class="form-radio embarazo-item">
                            <span>Sí</span>
                        </label>
                        <label class="radio-label">
                            <input type="radio" id="emb_contracciones_no" name="emb_contracciones" value="no" class="form-radio embarazo-item">
                            <span>No</span>
                        </label>
                    </div>
                </td>
                <td><input type="number" id="emb_dilatacion" name="emb_dilatacion" class="form-input embarazo-item" min="0" max="10" placeholder="0"></td>
                <td><input type="number" id="emb_borramiento" name="emb_borramiento" class="form-input embarazo-item" min="0" max="100" placeholder="0"></td>
            </tr>

            <!-- Tercera fila -->
            <tr>
                <th class="subheader">Plano</th>
                <th class="subheader">Pelvis viable</th>
                <th class="subheader">Score Mama</th>
                <th class="subheader" colspan="5">Observaciones adicionales</th>
            </tr>
            <tr>
                <td>
                    <select id="emb_plano" name="emb_plano" class="form-select embarazo-item">
                        <option value="">Seleccionar...</option>
                        <option value="-3">-3</option>
                        <option value="-2">-2</option>
                        <option value="-1">-1</option>
                        <option value="0">0</option>
                        <option value="+1">+1</option>
                        <option value="+2">+2</option>
                        <option value="+3">+3</option>
                    </select>
                </td>
                <td>
                    <div class="flex flex-col">
                        <label class="radio-label">
                            <input type="radio" id="emb_pelvis_viable_si" name="emb_pelvis_viable" value="si" class="form-radio embarazo-item">
                            <span>Sí</span>
                        </label>
                        <label class="radio-label">
                            <input type="radio" id="emb_pelvis_viable_no" name="emb_pelvis_viable" value="no" class="form-radio embarazo-item">
                            <span>No</span>
                        </label>
                    </div>
                </td>
                <td><input type="number" id="emb_score_mama" name="emb_score_mama" class="form-input embarazo-item" min="0" max="13" placeholder="0"></td>
                <td colspan="5">
                    <textarea id="emb_observaciones" name="emb_observaciones" class="form-textarea embarazo-item" rows="2" 
                              placeholder="Observaciones adicionales sobre embarazo y parto..."></textarea>
                </td>
            </tr>
        </tbody>
    </table>
</div>

<script>
// ✅ FUNCIÓN TOGGLE CORREGIDA - IGUAL QUE SECCIÓN K
function toggleEmbarazo() {
    const noAplicaCheckbox = document.getElementById('emb_no_aplica');
    const embarazoInputs = document.querySelectorAll('.embarazo-item');
    
    if (noAplicaCheckbox.checked) {
        // Si marca "No aplica", deshabilitar y limpiar todos los campos
        embarazoInputs.forEach(input => {
            if (input.type === 'text' || input.type === 'number' || input.type === 'date') {
                input.value = '';
                input.disabled = true;
                input.placeholder = 'No aplica - Campo deshabilitado';
            } else if (input.type === 'select-one') {
                input.selectedIndex = 0;
                input.disabled = true;
            } else if (input.type === 'radio') {
                input.checked = false;
                input.disabled = true;
            } else if (input.tagName.toLowerCase() === 'textarea') {
                input.value = '';
                input.disabled = true;
                input.placeholder = 'No aplica - Campo deshabilitado';
            }
        });
        
        // console.log('✅ Campos de embarazo deshabilitados');
    } else {
        // Si desmarca "No aplica", habilitar todos los campos
        embarazoInputs.forEach(input => {
            input.disabled = false;
            
            if (input.type === 'text' || input.type === 'number' || input.type === 'date') {
                // Restaurar placeholders originales
                const placeholders = {
                    'emb_gestas': '0',
                    'emb_partos': '0',
                    'emb_abortos': '0',
                    'emb_cesareas': '0',
                    'emb_semanas_gestacion': '0',
                    'emb_fcf': 'FCF',
                    'emb_tiempo_ruptura': '0',
                    'emb_afu': '0.0',
                    'emb_dilatacion': '0',
                    'emb_borramiento': '0',
                    'emb_score_mama': '0'
                };
                input.placeholder = placeholders[input.id] || '';
            } else if (input.tagName.toLowerCase() === 'textarea') {
                input.placeholder = 'Observaciones adicionales sobre embarazo y parto...';
            }
        });
        
        // console.log('✅ Campos de embarazo habilitados');
    }
}

// ✅ EVENT LISTENERS IGUAL QUE SECCIÓN K
document.addEventListener('DOMContentLoaded', function() {
    const embarazoInputs = document.querySelectorAll('.embarazo-item');
    const noAplicaCheckbox = document.getElementById('emb_no_aplica');
    
    embarazoInputs.forEach(input => {
        input.addEventListener('input', function() {
            if (this.value.trim() !== '' || (this.type === 'select-one' && this.selectedIndex > 0)) {
                noAplicaCheckbox.checked = false;
            }
        });
        
        input.addEventListener('change', function() {
            if (this.type === 'radio' && this.checked) {
                noAplicaCheckbox.checked = false;
            } else if (this.type === 'select-one' && this.selectedIndex > 0) {
                noAplicaCheckbox.checked = false;
            }
        });
    });
});
</script>