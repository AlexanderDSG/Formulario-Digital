<!-- SECCION D -->
<div class="bg-white shadow-xl rounded-lg overflow-hidden">
    <table class="w-full table-auto">
        <thead>
            <tr>
                <th colspan="5" class="header-main">D. ACCIDENTES, VIOLENCIAS, INTOXICACIÓN</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <th class="subheader">Fecha</th>
                <th class="subheader">Hora</th>
                <th class="subheader">Lugar del evento</th>
                <th class="subheader">Dirección del evento</th>
                <th class="subheader">Custodia policial</th>
            </tr>
            <tr>
                <td><input type="date" data-field="eve_fecha" id="acc_fecha_evento" name="acc_fecha_evento" class="form-input" value="<?= date('Y-m-d') ?>" >
                </td>
                <td><input type="time" data-field="eve_hora" id="acc_hora_evento" name="acc_hora_evento" class="form-input" value="<?= date('H:i') ?>"></td>
                <td><input type="text" data-field="eve_lugar" id="acc_lugar_evento" name="acc_lugar_evento" class="form-input"
                        placeholder="Ej: Vía pública, Domicilio"></td>
                <td><input type="text" data-field="eve_direccion" id="acc_direccion_evento" name="acc_direccion_evento"
                        class="form-input" placeholder="Dirección específica"></td>
                <td>
                    <div class="flex flex-col">
                        <label class="radio-label"><input type="radio" id="acc_custodia_policial_si"
                                name="acc_custodia_policial" value="si"
                                class="form-radio"><span>Sí</span></label>
                        <label class="radio-label"><input type="radio" id="acc_custodia_policial_no"
                                name="acc_custodia_policial" value="no"
                                class="form-radio"><span>No</span></label>
                    </div>
                </td>
            </tr>

            <tr class="checkbox-group">
                <td>
                    <div>
                        <label class="checkbox-label"><input type="checkbox" id="acc_tipo_transito"
                                name="tipos_evento[]" value="1" class="form-checkbox"><span>Accidente de tránsito</span></label>
                        <label class="checkbox-label"><input type="checkbox" id="acc_tipo_arma_fuego"
                                name="tipos_evento[]" value="8" class="form-checkbox"><span>Violencia por arma de fuego</span></label>
                        <label class="checkbox-label"><input type="checkbox" id="acc_tipo_intox_alcohol"
                                name="tipos_evento[]" value="15" class="form-checkbox"><span>Intoxicación alcohólica</span></label>
                    </div>
                </td>
                <td>
                    <div>
                        <label class="checkbox-label"><input type="checkbox" id="acc_tipo_caida"
                                name="tipos_evento[]" value="2" class="form-checkbox"><span>Caída</span></label>
                        <label class="checkbox-label"><input type="checkbox" id="acc_tipo_arma_cp"
                                name="tipos_evento[]" value="9" class="form-checkbox"><span>Violencia por arma C. punzante</span></label>
                        <label class="checkbox-label"><input type="checkbox" id="acc_tipo_intox_alimentaria"
                                name="tipos_evento[]" value="16" class="form-checkbox"><span>Intoxicación alimentaria</span></label>
                    </div>
                </td>
                <td>
                    <div>
                        <label class="checkbox-label"><input type="checkbox" id="acc_tipo_quemadura"
                                name="tipos_evento[]" value="3" class="form-checkbox"><span>Quemadura</span></label>
                        <label class="checkbox-label"><input type="checkbox" id="acc_tipo_rina"
                                name="tipos_evento[]" value="10" class="form-checkbox"><span>Violencia por riña</span></label>
                        <label class="checkbox-label"><input type="checkbox" id="acc_tipo_intox_drogas"
                                name="tipos_evento[]" value="17" class="form-checkbox"><span>Intoxicación por drogas</span></label>
                    </div>
                </td>
                <td>
                    <div>
                        <label class="checkbox-label"><input type="checkbox" id="acc_tipo_mordedura"
                                name="tipos_evento[]" value="4" class="form-checkbox"><span>Mordedura</span></label>
                        <label class="checkbox-label"><input type="checkbox" id="acc_tipo_violencia_familiar"
                                name="tipos_evento[]" value="11" class="form-checkbox"><span>Violencia familiar</span></label>
                        <label class="checkbox-label"><input type="checkbox" id="acc_tipo_inhalacion_gases"
                                name="tipos_evento[]" value="18" class="form-checkbox"><span>Inhalación de gases</span></label>
                    </div>
                </td>
                <td>
                    <div>
                        <label class="checkbox-label"><input type="checkbox" id="acc_otro_accidente_custodia"
                                name="tipos_evento[]" value="23" class="form-checkbox"><span>Otro accidente</span></label>
                    </div>
                </td>
            </tr>

            <tr class="checkbox-group">
                <td>
                    <div>
                        <label class="checkbox-label"><input type="checkbox" id="acc_tipo_cuerpo_extrano"
                                name="tipos_evento[]" value="6" class="form-checkbox"><span>Cuerpo extraño</span></label>
                        <label class="checkbox-label"><input type="checkbox" id="acc_tipo_violencia_psicologica"
                                name="tipos_evento[]" value="13" class="form-checkbox"><span>Presunta violencia psicológica</span></label>
                        <label class="checkbox-label"><input type="checkbox" id="acc_tipo_picadura"
                                name="tipos_evento[]" value="20" class="form-checkbox"><span>Picadura</span></label>
                    </div>
                </td>
                <td>
                    <div>
                        <label class="checkbox-label">
                            <input type="checkbox" id="acc_tipo_aplastamiento"
                                name="tipos_evento[]" value="7" class="form-checkbox"><span>Aplastamiento</span></label>
                        <label class="checkbox-label"><input type="checkbox" id="acc_tipo_violencia_sexual"
                                name="tipos_evento[]" value="14" class="form-checkbox"><span>Presunta violencia sexual</span></label>
                        <label class="checkbox-label"><input type="checkbox" id="acc_tipo_envenenamiento"
                                name="tipos_evento[]" value="21" class="form-checkbox"><span>Envenenamiento</span></label>
                    </div>
                </td>
                <td>
                    <div>
                        <label class="checkbox-label"><input type="checkbox" id="acc_tipo_ahogamiento"
                                name="tipos_evento[]" value="5" class="form-checkbox"><span>Ahogamiento</span></label>
                        <label class="checkbox-label"><input type="checkbox" id="acc_tipo_violencia_fisica"
                                name="tipos_evento[]" value="12" class="form-checkbox"><span>Presunta violencia física</span></label>
                        <label class="checkbox-label"><input type="checkbox" id="acc_tipo_otra_intox"
                                name="tipos_evento[]" value="19" class="form-checkbox"><span>Otra intoxicación</span></label>
                    </div>
                </td>
                <td>
                    <label class="checkbox-label mt-1"><input type="checkbox" id="acc_anafilaxia_custodia"
                            name="tipos_evento[]" value="22" class="form-checkbox"><span>Anafilaxia</span></label>
                </td>
                <td>
                    <div class="mt-1">
                        <span class="text-sm font-medium text-gray-700 mr-2">Notificación:</span>
                        <label class="radio-label inline-flex items-center"><input type="radio"
                                id="acc_notificacion_custodia_si" name="acc_notificacion_custodia"
                                value="si" class="form-radio"><span>Sí</span></label>
                        <label class="radio-label inline-flex items-center"><input type="radio"
                                id="acc_notificacion_custodia_no" name="acc_notificacion_custodia"
                                value="no" class="form-radio"><span>No</span></label>
                    </div>
                </td>
            </tr>

            <tr>
                <th class="subheader" colspan="3">Observaciones</th>
                <th class="subheader" colspan="2">Indicadores Adicionales</th>
            </tr>
            <tr>
                <td colspan="3">
                    <textarea id="acc_observaciones" name="acc_observaciones" class="form-textarea" rows="1"
                        placeholder="Detalle las observaciones del accidente, violencia o intoxicación..."></textarea>
                </td>
                <td colspan="2" style="vertical-align: middle;">
                    <label class="checkbox-label">
                        <input type="checkbox" id="acc_sugestivo_alcohol" name="acc_sugestivo_alcohol"
                            class="form-checkbox">
                        <span>Sugestivo de ingesta alcohólica</span>
                    </label>
                </td>
            </tr>
        </tbody>
    </table>
</div>