<!-- SECCION C -->
<div class="bg-white shadow-xl rounded-lg overflow-hidden">
    <table class="w-full table-auto">
        <thead>
            <tr>
                <th colspan="3" class="header-main">C. INICIO DE ATENCIÓN</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <th class="subheader">Fecha</th>
                <th class="subheader">Hora</th>
                <th class="subheader">Condición de llegada</th>
            </tr>
            <tr>
                <td><input type="date" data-field="iat_fecha" id="inicio_atencion_fecha" name="inicio_atencion_fecha"
                        class="form-input border-gray-300 bg-gray-100 text-gray-500 cursor-not-allowed" value="<?= date('Y-m-d') ?>" readonly>
                </td>
                <td><input type="time" data-field="iat_hora" id="inicio_atencion_hora" name="inicio_atencion_hora"
                        class="form-input" value="<?= date('H:i'); ?>">
                </td>
                <td>
                    <select data-field="condicion_llegada" id="inicio_atencion_condicion" name="inicio_atencion_condicion"
                        class="form-select" required>
                        <option value="" disabled selected>Seleccione...</option>
                        <?php if (!empty($CondicionLlegada)): ?>
                            <?php foreach ($CondicionLlegada as $condicion): ?>
                                <option value="<?= $condicion['col_codigo'] ?>">
                                    <?= htmlspecialchars($condicion['col_descripcion']) ?>
                                </option>
                            <?php endforeach; ?>
                            <?php endif; ?>
                    </select>
                </td>
            </tr>

            <tr>
                <th class="subheader" colspan="3">Motivo de atención</th>
            </tr>
            <tr>
                <td colspan="3">
                    <textarea data-field="iat_motivo" id="inicio_atencion_motivo" name="inicio_atencion_motivo"
                        class="form-textarea" rows="1"
                        placeholder="Describa el motivo principal de la atención..."></textarea>
                </td>
            </tr>
        </tbody>
    </table>
</div>