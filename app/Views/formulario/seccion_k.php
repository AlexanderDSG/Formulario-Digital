<!-- SECCION K -->
<div class="bg-white shadow-xl rounded-lg overflow-hidden">
    <table class="w-full table-auto">
        <thead>
            <tr>
                <th colspan="8" class="header-main"> K. EXÁMENES COMPLEMENTARIOS
                    <label class="header-checkbox-label float-right"
                        style="font-weight: normal; font-size: 0.65rem;">
                        <input type="checkbox" id="exc_no_aplica" name="exc_no_aplica" value="1"
                            class="form-checkbox" onchange="toggleExamenesComplementarios()">
                        <span>No aplica</span>
                    </label>
                </th>
            </tr>
        </thead>
        <tbody>
            <tr class="checkbox-group">
                <td><label class="checkbox-label">
                    <input type="checkbox" id="exc_biometria" name="tipos_examenes[]" value="1" class="form-checkbox examen-item">
                    <span>1. Biometría</span>
                </label></td>
                
                <td><label class="checkbox-label">
                    <input type="checkbox" id="exc_quimica_sanguinea" name="tipos_examenes[]" value="3" class="form-checkbox examen-item">
                    <span>3. Química Sanguínea</span>
                </label></td>
                
                <td><label class="checkbox-label">
                    <input type="checkbox" id="exc_gasometria" name="tipos_examenes[]" value="5" class="form-checkbox examen-item">
                    <span>5. Gasometría</span>
                </label></td>
                
                <td><label class="checkbox-label">
                    <input type="checkbox" id="exc_endoscopia" name="tipos_examenes[]" value="7" class="form-checkbox examen-item">
                    <span>7. Endoscopía</span>
                </label></td>
                
                <td><label class="checkbox-label">
                    <input type="checkbox" id="exc_rx_abdomen" name="tipos_examenes[]" value="9" class="form-checkbox examen-item">
                    <span>9. RX Abdomen</span>
                </label></td>
                
                <td><label class="checkbox-label">
                    <input type="checkbox" id="exc_eco_abdomen" name="tipos_examenes[]" value="11" class="form-checkbox examen-item">
                    <span>11. Ecografía Abdomen</span>
                </label></td>
                
                <td><label class="checkbox-label">
                    <input type="checkbox" id="exc_tomografia" name="tipos_examenes[]" value="13" class="form-checkbox examen-item">
                    <span>13. Tomografía</span>
                </label></td>
                
                <td><label class="checkbox-label">
                    <input type="checkbox" id="exc_interconsulta" name="tipos_examenes[]" value="15" class="form-checkbox examen-item">
                    <span>15. Interconsulta</span>
                </label></td>
            </tr>
            <tr class="checkbox-group">
                <td><label class="checkbox-label">
                    <input type="checkbox" id="exc_uroanalisis" name="tipos_examenes[]" value="2" class="form-checkbox examen-item">
                    <span>2. Uroanálisis</span>
                </label></td>
                
                <td><label class="checkbox-label">
                    <input type="checkbox" id="exc_electrolitos" name="tipos_examenes[]" value="4" class="form-checkbox examen-item">
                    <span>4. Electrolitos</span>
                </label></td>
                
                <td><label class="checkbox-label">
                    <input type="checkbox" id="exc_electrocardiograma" name="tipos_examenes[]" value="6" class="form-checkbox examen-item">
                    <span>6. Electrocardiograma</span>
                </label></td>
                
                <td><label class="checkbox-label">
                    <input type="checkbox" id="exc_rx_torax" name="tipos_examenes[]" value="8" class="form-checkbox examen-item">
                    <span>8. RX Tórax</span>
                </label></td>
                
                <td><label class="checkbox-label">
                    <input type="checkbox" id="exc_rx_osea" name="tipos_examenes[]" value="10" class="form-checkbox examen-item">
                    <span>10. RX Ósea</span>
                </label></td>
                
                <td><label class="checkbox-label">
                    <input type="checkbox" id="exc_eco_pelvica" name="tipos_examenes[]" value="12" class="form-checkbox examen-item">
                    <span>12. Ecografía Pélvica</span>
                </label></td>
                
                <td><label class="checkbox-label">
                    <input type="checkbox" id="exc_resonancia" name="tipos_examenes[]" value="14" class="form-checkbox examen-item">
                    <span>14. Resonancia</span>
                </label></td>
                
                <td><label class="checkbox-label">
                    <input type="checkbox" id="exc_otros" name="tipos_examenes[]" value="16" class="form-checkbox examen-item">
                    <span>16. Otros</span>
                </label></td>
            </tr>
            <tr>
                <th class="subheader" colspan="8">Observaciones</th>
            </tr>
            <tr>
                <td colspan="7">
                    <textarea id="exc_observaciones" name="exc_observaciones" class="form-textarea" rows="1"
                        placeholder="Describa cualquier observación relevante sobre los exámenes complementarios..."></textarea>
                </td>
            </tr>
        </tbody>
    </table>
</div>

<script>
function toggleExamenesComplementarios() {
    const noAplicaCheckbox = document.getElementById('exc_no_aplica');
    const examenesCheckboxes = document.querySelectorAll('.examen-item');
    const observacionesTextarea = document.getElementById('exc_observaciones');
    
    if (noAplicaCheckbox.checked) {
        // Si marca "No aplica", deshabilitar y desmarcar todos los exámenes
        examenesCheckboxes.forEach(checkbox => {
            checkbox.checked = false;
            checkbox.disabled = true;
        });
        observacionesTextarea.disabled = true;
        observacionesTextarea.value = '';
        observacionesTextarea.placeholder = 'No aplica - Campo deshabilitado';
    } else {
        // Si desmarca "No aplica", habilitar todos los exámenes
        examenesCheckboxes.forEach(checkbox => {
            checkbox.disabled = false;
        });
        observacionesTextarea.disabled = false;
        observacionesTextarea.placeholder = 'Describa cualquier observación relevante sobre los exámenes complementarios...';
    }
}

// Event listeners para evitar que se marque "No aplica" si hay exámenes seleccionados
document.addEventListener('DOMContentLoaded', function() {
    const examenesCheckboxes = document.querySelectorAll('.examen-item');
    const noAplicaCheckbox = document.getElementById('exc_no_aplica');
    
    examenesCheckboxes.forEach(checkbox => {
        checkbox.addEventListener('change', function() {
            if (this.checked) {
                noAplicaCheckbox.checked = false;
            }
        });
    });
});
</script>