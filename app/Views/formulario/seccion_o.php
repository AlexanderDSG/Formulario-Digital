<!-- SECCION O -->
<div class="diagnostico-table-container bg-white shadow-xl rounded-lg overflow-hidden mt-6">
    <table class="w-full table-auto">
        <thead>
            <tr>
                <th colspan="7" class="header-main">O. CONDICIÓN AL EGRESO DE EMERGENCIA</th>
            </tr>
            <tr>
                <th class="subheader">Vivo</th>
                <th class="subheader">Estable</th>
                <th class="subheader">Inestable</th>
                <th class="subheader">Fallecido</th>
                <th class="subheader">Alta definitiva</th>
                <th class="subheader">Consulta externa</th>
                <th class="subheader">Observación de emergencia</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td><input type="checkbox" id="egreso_vivo" name="estados_egreso[]" value="1" class="form-checkbox"></td>
                <td><input type="checkbox" id="egreso_estable" name="estados_egreso[]" value="2" class="form-checkbox"></td>
                <td><input type="checkbox" id="egreso_inestable" name="estados_egreso[]" value="3" class="form-checkbox"></td>
                <td><input type="checkbox" id="egreso_fallecido" name="estados_egreso[]" value="4" class="form-checkbox"></td>
                <td><input type="checkbox" id="egreso_alta_definitiva" name="modalidades_egreso[]" value="1" class="form-checkbox"></td>
                <td><input type="checkbox" id="egreso_consulta_externa" name="modalidades_egreso[]" value="2" class="form-checkbox"></td>
                <td><input type="checkbox" id="egreso_observacion_emergencia" name="modalidades_egreso[]" value="3" class="form-checkbox"></td>
            </tr>
            <tr>
                <th class="subheader">Hospitalización</th>
                <th class="subheader">Referencia</th>
                <th class="subheader">Referencia inversa</th>
                <th class="subheader">Derivación</th>
                <th class="subheader">Establecimiento</th>
                <th class="subheader" colspan="2"></th>
            </tr>
            <tr>
                <td><input type="checkbox" id="egreso_hospitalizacion" name="tipos_egreso[]" value="1" class="form-checkbox"></td>
                <td><input type="checkbox" id="egreso_referencia" name="tipos_egreso[]" value="2" class="form-checkbox"></td>
                <td><input type="checkbox" id="egreso_referencia_inversa" name="tipos_egreso[]" value="3" class="form-checkbox"></td>
                <td><input type="checkbox" id="egreso_derivacion" name="tipos_egreso[]" value="4" class="form-checkbox"></td>
                <td><input type="text" id="egreso_establecimiento" name="egreso_establecimiento"
                        class="form-input" placeholder="Nombre establecimiento"></td>
                <td colspan="2"></td>
            </tr>
            <tr>
                <th class="subheader" colspan="5">Observación</th>
                <th class="subheader" colspan="2" style="text-align:left;">Días de reposo</th>
            </tr>
            <tr>
                <td colspan="5">
                    <textarea id="egreso_observacion" name="egreso_observacion" class="form-textarea"
                        rows="1" placeholder="Observaciones..."></textarea>
                </td>
                <td colspan="2">
                    <input type="number" id="egreso_dias_reposo" name="egreso_dias_reposo"
                        class="form-input" min="0" placeholder="Días de reposo">
                </td>
            </tr>
        </tbody>
    </table>
</div>