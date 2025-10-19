<!--SECCION E  -->
<div class="bg-white shadow-xl rounded-lg overflow-hidden">
    <table class="w-full table-auto">
        <thead>
            <tr>
                <th colspan="10" class="header-main"> E. ANTECEDENTES PATOLÓGICOS PERSONALES Y FAMILIARES
                    <label class="header-checkbox-label float-right"
                        style="font-weight: normal; font-size: 0.65rem;">
                        <span class="note-text float-right">Seleccione el número correspondiente y describa
                            (señalando el número) en la columna de descripción de antecedentes.</span>
                    </label>
                </th>
                <th colspan="5" class="header-main">
                    <label class="header-checkbox-label float-right"
                        style="font-weight: normal; font-size: 0.65rem;">
                        <input type="checkbox" id="ant_no_aplica" name="ant_no_aplica" value="1"
                            class="form-checkbox" onchange="toggleAntecedentes()">
                        <span>No aplica</span>
                    </label>
                </th>
                
            </tr>
        </thead>
        <tbody>
            <tr class="antecedentes-checkbox-group">
                <td>
                    <div>
                        <label class="checkbox-label">
                            <input type="checkbox" id="ant_alergicos" name="antecedentes[]" value="1" class="form-checkbox antecedente-item">
                            <span>1. Alérgicos</span>
                        </label>
                        <label class="checkbox-label">
                            <input type="checkbox" id="ant_clinicos" name="antecedentes[]" value="2" class="form-checkbox antecedente-item">
                            <span>2. Clínicos</span>
                        </label>
                    </div>
                </td>
                <td>
                    <div>
                        <label class="checkbox-label">
                            <input type="checkbox" id="ant_ginecologicos" name="antecedentes[]" value="3" class="form-checkbox antecedente-item">
                            <span>3. Ginecológicos</span>
                        </label>
                        <label class="checkbox-label">
                            <input type="checkbox" id="ant_traumatologicos" name="antecedentes[]" value="4" class="form-checkbox antecedente-item">
                            <span>4. Traumatológicos</span>
                        </label>
                    </div>
                </td>
                <td>
                    <div>
                        <label class="checkbox-label">
                            <input type="checkbox" id="ant_pediatricos" name="antecedentes[]" value="5" class="form-checkbox antecedente-item">
                            <span>5. Pediátricos</span>
                        </label>
                        <label class="checkbox-label">
                            <input type="checkbox" id="ant_quirurgicos" name="antecedentes[]" value="6" class="form-checkbox antecedente-item">
                            <span>6. Quirúrgicos</span>
                        </label>
                    </div>
                </td>
                <td>
                    <div>
                        <label class="checkbox-label">
                            <input type="checkbox" id="ant_farmacologicos" name="antecedentes[]" value="7" class="form-checkbox antecedente-item">
                            <span>7. Farmacológicos</span>
                        </label>
                        <label class="checkbox-label">
                            <input type="checkbox" id="ant_habitos" name="antecedentes[]" value="8" class="form-checkbox antecedente-item">
                            <span>8. Hábitos</span>
                        </label>
                    </div>
                </td>
                <td>
                    <div>
                        <label class="checkbox-label">
                            <input type="checkbox" id="ant_familiares" name="antecedentes[]" value="9" class="form-checkbox antecedente-item">
                            <span>9. Familiares</span>
                        </label>
                        <label class="checkbox-label">
                            <input type="checkbox" id="ant_otros" name="antecedentes[]" value="10" class="form-checkbox antecedente-item">
                            <span>10. Otros</span>
                        </label>
                    </div>
                </td>
            </tr>
            <tr>
                <td colspan="5">
                    <textarea id="ant_descripcion" name="ant_descripcion" class="form-textarea" rows="1"
                        placeholder="Ej: 1. Alergia al polen. 2. Hipertensión diagnosticada hace 5 años..."></textarea>
                </td>
            </tr>
        </tbody>
    </table>
</div>

<script>
function toggleAntecedentes() {
    const noAplicaCheckbox = document.getElementById('ant_no_aplica');
    const antecedentesCheckboxes = document.querySelectorAll('.antecedente-item');
    const descripcionTextarea = document.getElementById('ant_descripcion');
    
    if (noAplicaCheckbox.checked) {
        // Si marca "No aplica", deshabilitar y desmarcar todos los antecedentes
        antecedentesCheckboxes.forEach(checkbox => {
            checkbox.checked = false;
            checkbox.disabled = true;
        });
        descripcionTextarea.disabled = true;
        descripcionTextarea.value = '';
        descripcionTextarea.placeholder = 'No aplica - Campo deshabilitado';
    } else {
        // Si desmarca "No aplica", habilitar todos los antecedentes
        antecedentesCheckboxes.forEach(checkbox => {
            checkbox.disabled = false;
        });
        descripcionTextarea.disabled = false;
        descripcionTextarea.placeholder = 'Ej: 1. Alergia al polen. 2. Hipertensión diagnosticada hace 5 años...';
    }
}

// También agregar event listeners para evitar que se marque "No aplica" si hay antecedentes seleccionados
document.addEventListener('DOMContentLoaded', function() {
    const antecedentesCheckboxes = document.querySelectorAll('.antecedente-item');
    const noAplicaCheckbox = document.getElementById('ant_no_aplica');
    
    antecedentesCheckboxes.forEach(checkbox => {
        checkbox.addEventListener('change', function() {
            if (this.checked) {
                noAplicaCheckbox.checked = false;
            }
        });
    });
});
</script>