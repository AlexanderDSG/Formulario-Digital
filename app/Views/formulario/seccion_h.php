<!-- SECCION H -->
<div id="seccionHContenedor" class="bg-white shadow-xl rounded-lg overflow-hidden">
    <table class="w-full table-auto">
        <thead>
            <tr>
                <th colspan="10" class="header-main"> H. EXAMEN FÍSICO
                    <label class="header-checkbox-label float-right"
                        style="font-weight: normal; font-size: 0.75rem;">
                        <span class="note-text float-right">(seleccione cuando presente patología y
                            describa)</span>
                    </label>
                </th>
            </tr>
        </thead>
        <tbody class="examen-fisico-item">
            <tr>
                <td class="examen-fisico-numeral">1</td>
                <td><label class="checkbox-label">
                    <input type="checkbox" id="ef_piel_faneras" name="zonas_examen_fisico[]" value="1" class="form-checkbox">
                    <span>Piel-faneras</span>
                </label></td>
                
                <td class="examen-fisico-numeral">4</td>
                <td><label class="checkbox-label">
                    <input type="checkbox" id="ef_oidos" name="zonas_examen_fisico[]" value="4" class="form-checkbox">
                    <span>Oídos</span>
                </label></td>
                
                <td class="examen-fisico-numeral">7</td>
                <td><label class="checkbox-label">
                    <input type="checkbox" id="ef_oro_faringe" name="zonas_examen_fisico[]" value="7" class="form-checkbox">
                    <span>Oro faringe</span>
                </label></td>
                
                <td class="examen-fisico-numeral">10</td>
                <td><label class="checkbox-label">
                    <input type="checkbox" id="ef_torax" name="zonas_examen_fisico[]" value="10" class="form-checkbox">
                    <span>Tórax</span>
                </label></td>
                
                <td class="examen-fisico-numeral">13</td>
                <td><label class="checkbox-label">
                    <input type="checkbox" id="ef_ingle_perine" name="zonas_examen_fisico[]" value="13" class="form-checkbox">
                    <span>Ingle-periné</span>
                </label></td>
            </tr>
            <tr>
                <td class="examen-fisico-numeral">2</td>
                <td><label class="checkbox-label">
                    <input type="checkbox" id="ef_cabeza" name="zonas_examen_fisico[]" value="2" class="form-checkbox">
                    <span>Cabeza</span>
                </label></td>
                
                <td class="examen-fisico-numeral">5</td>
                <td><label class="checkbox-label">
                    <input type="checkbox" id="ef_nariz" name="zonas_examen_fisico[]" value="5" class="form-checkbox">
                    <span>Nariz</span>
                </label></td>
                
                <td class="examen-fisico-numeral">8</td>
                <td><label class="checkbox-label">
                    <input type="checkbox" id="ef_cuello" name="zonas_examen_fisico[]" value="8" class="form-checkbox">
                    <span>Cuello</span>
                </label></td>
                
                <td class="examen-fisico-numeral">11</td>
                <td><label class="checkbox-label">
                    <input type="checkbox" id="ef_abdomen" name="zonas_examen_fisico[]" value="11" class="form-checkbox">
                    <span>Abdomen</span>
                </label></td>
                
                <td class="examen-fisico-numeral">14</td>
                <td><label class="checkbox-label">
                    <input type="checkbox" id="ef_miembros_superiores" name="zonas_examen_fisico[]" value="14" class="form-checkbox">
                    <span>Miembros sup.</span>
                </label></td>
            </tr>
            <tr>
                <td class="examen-fisico-numeral">3</td>
                <td><label class="checkbox-label">
                    <input type="checkbox" id="ef_ojos" name="zonas_examen_fisico[]" value="3" class="form-checkbox">
                    <span>Ojos</span>
                </label></td>
                
                <td class="examen-fisico-numeral">6</td>
                <td><label class="checkbox-label">
                    <input type="checkbox" id="ef_boca" name="zonas_examen_fisico[]" value="6" class="form-checkbox">
                    <span>Boca</span>
                </label></td>
                
                <td class="examen-fisico-numeral">9</td>
                <td><label class="checkbox-label">
                    <input type="checkbox" id="ef_axilas_mamas" name="zonas_examen_fisico[]" value="9" class="form-checkbox">
                    <span>Axilas-mamas</span>
                </label></td>
                
                <td class="examen-fisico-numeral">12</td>
                <td><label class="checkbox-label">
                    <input type="checkbox" id="ef_columna_vertebral" name="zonas_examen_fisico[]" value="12" class="form-checkbox">
                    <span>Columna vertebral</span>
                </label></td>
                
                <td class="examen-fisico-numeral">15</td>
                <td><label class="checkbox-label">
                    <input type="checkbox" id="ef_miembros_inferiores" name="zonas_examen_fisico[]" value="15" class="form-checkbox">
                    <span>Miembros inf.</span>
                </label></td>
            </tr>

            <tr>
                <td colspan="10">
                    <textarea id="ef_descripcion" name="ef_descripcion" class="form-textarea" rows="1"
                        placeholder="Ej: 1. Palidez cutánea. 5. Secreción nasal purulenta..."></textarea>
                </td>
            </tr>
        </tbody>
    </table>
</div>