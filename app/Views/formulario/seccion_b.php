<!-- SECCION B -->
<div class="bg-white shadow-xl rounded-lg overflow-hidden">
        <table class="w-full table-auto">
                <thead>
                        <tr>
                                <th colspan="5" class="header-main">B. REGISTRO DE ADMISIÓN DEL PACIENTE</th>
                        </tr>
                </thead>
                <tbody>
                        <!-- Fila 1 -->
                        <tr>
                                <th class="subheader" colspan="1">Fecha de admisión</th>
                                <th class="subheader" colspan="2">Nombres y apellidos del admisionista</th>
                                <th class="subheader" colspan="2">Historia clínica en este establecimiento</th>
                        </tr>
                        <tr>
                                <td colspan="1"><input type="date" data-field="ate_fecha" id="adm_fecha" name="adm_fecha" value="<?= (new DateTime('now', new DateTimeZone(config('App')->appTimezone ?? 'America/Guayaquil')))->format('Y-m-d') ?>" class="form-input border-gray-300 bg-gray-100 text-gray-500 cursor-not-allowed" readonly></td>
                                <td colspan="2">
                                        <input type="text" data-field="usuario_nombre_completo" id="adm_admisionista_nombre"
                                                name="adm_admisionista_nombre"
                                                class="form-input border-gray-300 bg-gray-100 text-gray-500 cursor-not-allowed"
                                                value="<?= esc($nombre_admisionista ?? '') ?>"
                                                readonly>
                                </td>

                                <td colspan="2">
                                        <div class="flex items-center space-x-2 sm:space-x-4">
                                                <label class="radio-label"><input type="radio" id="adm_historia_clinica_estab_si"
                                                                name="adm_historia_clinica_estab" value="si"
                                                                class="form-radio"><span>Si</span></label>
                                                <label class="radio-label"><input type="radio" id="adm_historia_clinica_estab_no"
                                                                name="adm_historia_clinica_estab" value="no"
                                                                class="form-radio"><span>No</span></label>
                                        </div>
                                </td>
                        </tr>
                        <!-- Fila 2 -->
                        <tr>
                                <th class="subheader">Primer apellido</th>
                                <th class="subheader">Segundo apellido</th>
                                <th class="subheader">Primer nombre</th>
                                <th class="subheader">Segundo nombre</th>
                                <th class="subheader">Tipo de documento de identificación</th>
                        </tr>
                        <tr>
                                <td><input type="text" data-field="pac_apellidos" id="pac_apellido1" name="pac_apellido1" class="form-input"
                                                placeholder="Primer apellido del paciente"></td>
                                <td><input type="text" data-field="pac_apellidos" id="pac_apellido2" name="pac_apellido2" class="form-input"
                                                placeholder="Segundo apellido (Opcional)"></td>
                                <td><input type="text" data-field="pac_nombres" id="pac_nombre1" name="pac_nombre1" class="form-input"
                                                placeholder="Primer nombre del paciente"></td>
                                <td><input type="text" data-field="pac_nombres" id="pac_nombre2" name="pac_nombre2" class="form-input"
                                                placeholder="Segundo nombre (Opcional)"></td>

                                <td>
                                        <select data-field="tipo_documento" id="pac_tipo_documento" name="pac_tipo_documento" class="form-select w-full h-10">
                                                <option value="" disabled selected>Seleccione</option>
                                                <?php foreach ($tiposDocumento as $tipo): ?>
                                                        <option value="<?= esc($tipo['tdoc_codigo']) ?>">
                                                                <?= esc($tipo['tdoc_descripcion']) ?>
                                                        </option>
                                                <?php endforeach; ?>
                                        </select>

                                </td>

                        </tr>
                        <!-- Fila 3 -->
                        <tr>
                                <th class="subheader">Estado civil</th>
                                <th class="subheader">Sexo</th>
                                <th class="subheader">N° teléfono fijo</th>
                                <th class="subheader">N° teléfono celular</th>
                                <th class="subheader">Fecha de nacimiento</th>
                        </tr>
                        <tr>
                                <td>
                                        <select data-field="estado_civil" id="pac_estado_civil" name="pac_estado_civil" class="form-select w-full h-10">
                                                <option value="" disabled selected>Seleccione</option>
                                                <?php foreach ($estadoCiviles as $estadoCivil): ?>
                                                        <option value="<?= esc($estadoCivil['esc_codigo']) ?>">
                                                                <?= esc($estadoCivil['esc_descripcion']) ?>
                                                        </option>
                                                <?php endforeach; ?>
                                        </select>
                                </td>

                                <td>
                                        <select data-field="genero" id="pac_sexo" name="pac_sexo" class="form-select w-40 h-10">
                                                <option value="" disabled selected>Seleccione</option>
                                                <?php foreach ($generos as $genero): ?>
                                                        <option value="<?= esc($genero['gen_codigo']) ?>">
                                                                <?= esc($genero['gen_descripcion']) ?>
                                                        </option>
                                                <?php endforeach; ?>
                                        </select>
                                </td>

                                <td><input type="tel" data-field="pac_telefono" id="pac_telefono_fijo" name="pac_telefono_fijo" class="form-input"
                                                placeholder="Ej: 022123456"></td>
                                <td><input type="tel" id="pac_telefono_celular" name="pac_telefono_celular"
                                                class="form-input" placeholder="Ej: 0991234567"></td>
                                <td><input type="date" data-field="pac_fecha_nac" id="pac_fecha_nacimiento" name="pac_fecha_nacimiento"
                                                class="form-input"></td>
                        </tr>
                        <!-- Fila 4 -->
                        <tr>
                                <th class="subheader">Lugar de nacimiento <br><span class="font-normal text-xs">(País/Prov/Cantón/Parroquia)</span></th>
                                <th class="subheader">Nacionalidad</th>
                                <th class="subheader">Edad</th>
                                <th class="subheader">Grupo prioritario</th>
                                <th class="subheader">Especifique (si es prioritario)</th>
                        </tr>
                        <tr>
                                <td>
                                        <?php if (session()->get('rol_id') == 2): ?>
                                                <!-- SELECTS EN CASCADA PARA ECUATORIANOS -->
                                                <div id="lugar_nac_ecuador" class="space-y-2" style="display: none;">

                                                        <!-- Provincia -->
                                                        <select id="nac_provincia"
                                                                name="nac_provincia"
                                                                class="form-select w-full h-10 text-sm">
                                                                <option value="">Seleccione provincia</option>
                                                                <?php foreach ($provincias as $prov): ?>
                                                                        <option value="<?= esc($prov['prov_codigo']) ?>">
                                                                                <?= esc($prov['prov_nombre']) ?>
                                                                        </option>
                                                                <?php endforeach; ?>
                                                        </select>

                                                        <!-- Cantón (se llena dinámicamente) -->
                                                        <select id="nac_canton"
                                                                name="nac_canton"
                                                                class="form-select w-full h-10 text-sm"
                                                                disabled>
                                                                <option value="">Primero seleccione provincia</option>
                                                        </select>

                                                        <!-- Parroquia (se llena dinámicamente) -->
                                                        <select id="nac_parroquia"
                                                                name="nac_parroquia"
                                                                class="form-select w-full h-10 text-sm"
                                                                disabled>
                                                                <option value="">Primero seleccione cantón</option>
                                                        </select>
                                                </div>

                                                <!-- INPUT SIMPLE PARA EXTRANJEROS -->
                                                <div id="lugar_nac_extranjero" style="display: none;">
                                                        <input type="text" data-field="pac_lugar_nac" id="pac_lugar_nacimiento" name="pac_lugar_nacimiento"
                                                                class="form-input" placeholder="País, Provincia, Canton, Parroquia">
                                                </div>

                                                <!-- Mensaje inicial -->
                                                <div id="lugar_nac_placeholder" class="text-gray-400 text-sm italic text-center py-4">
                                                        Primero seleccione la nacionalidad
                                                </div>
                                        <?php else: ?>

                                                <input type="text" data-field="pac_lugar_nac" id="pac_lugar_nacimiento" name="pac_lugar_nacimiento"
                                                        class="form-input" placeholder="País, Provincia, Ciudad">

                                        <?php endif; ?>


                                </td>

                                <td>
                                        <select data-field="nacionalidad" id="pac_nacionalidad" name="pac_nacionalidad" class="form-select w-full h-10">
                                                <option value="" disabled selected>Seleccione</option>
                                                <?php foreach ($nacionalidades as $nacionalidad): ?>
                                                        <option value="<?= esc($nacionalidad['nac_codigo']) ?>">
                                                                <?= esc($nacionalidad['nac_descripcion']) ?>
                                                        </option>
                                                <?php endforeach; ?>
                                        </select>
                                </td>
                                <td>
                                        <div class="input-group">
                                                <input type="text" data-field="ate_edad_anios" id="pac_edad_valor" name="pac_edad_valor"
                                                        class="form-input w-20" placeholder="Edad" min="0">
                                                <div class="radio-group-horizontal">
                                                        <label class="radio-label"><input type="radio" id="pac_edad_unidad_h"
                                                                        name="pac_edad_unidad" value="H"
                                                                        class="form-radio"><span>H</span></label>
                                                        <label class="radio-label"><input type="radio" id="pac_edad_unidad_d"
                                                                        name="pac_edad_unidad" value="D"
                                                                        class="form-radio"><span>D</span></label>
                                                        <label class="radio-label"><input type="radio" id="pac_edad_unidad_m"
                                                                        name="pac_edad_unidad" value="M"
                                                                        class="form-radio"><span>M</span></label>
                                                        <label class="radio-label"><input type="radio" id="pac_edad_unidad_a"
                                                                        name="pac_edad_unidad" value="A"
                                                                        class="form-radio"><span>A</span></label>
                                                </div>
                                        </div>
                                </td>
                                <td>
                                        <div class="flex items-center space-x-2 sm:space-x-4">
                                                <label class="radio-label"><input type="radio" id="pac_grupo_prioritario_si"
                                                                name="pac_grupo_prioritario" value="si"
                                                                class="form-radio"><span>Sí</span></label>
                                                <label class="radio-label"><input type="radio" id="pac_grupo_prioritario_no"
                                                                name="pac_grupo_prioritario" value="no"
                                                                class="form-radio"><span>No</span></label>
                                        </div>
                                </td>
                                <td><input type="text" id="pac_grupo_prioritario_especifique"
                                                name="pac_grupo_prioritario_especifique" class="form-input"
                                                placeholder="Detalle del grupo"></td>
                        </tr>
                        <!-- Fila 5 -->
                        <tr>
                                <th class="subheader">Autoidentificación étnica</th>
                                <th class="subheader">Nacionalidad indígena (si aplica)</th>
                                <th class="subheader">Pueblo indígena (si aplica)</th>
                                <th class="subheader" colspan="2">Nivel de Educación</th>
                        </tr>
                        <tr>
                                <td>
                                        <select data-field="grupo_cultural" id="pac_etnia" name="pac_etnia" class="form-select w-full h-10">
                                                <option value="" disabled selected>Seleccione etnia</option>
                                                <?php foreach ($etnias as $etnia): ?>
                                                        <option value="<?= esc($etnia['gcu_codigo']) ?>">
                                                                <?= esc($etnia['gcu_descripcion']) ?>
                                                        </option>
                                                <?php endforeach; ?>
                                        </select>
                                </td>

                                <td>
                                        <select data-field="nacionalidad_indigena" id="pac_nacionalidad_indigena" name="pac_nacionalidad_indigena" class="form-select w-full h-10">
                                                <option value="" disabled selected>Seleccione nacionalidad</option>
                                                <?php foreach ($nacionalidadIndigena as $nacIndigena): ?>
                                                        <option value="<?= esc($nacIndigena['nac_ind_codigo']) ?>">
                                                                <?= esc($nacIndigena['nac_ind_nombre']) ?>
                                                        </option>
                                                <?php endforeach; ?>
                                        </select>
                                </td>

                                <td>
                                        <select data-field="pueblo_indigena" id="pac_pueblo_indigena" name="pac_pueblo_indigena" class="form-select w-full h-10">
                                                <option value="" disabled selected>Seleccione pueblo</option>
                                                <?php foreach ($puebloIndigena as $puebloInd): ?>
                                                        <option value="<?= esc($puebloInd['pue_ind_codigo']) ?>">
                                                                <?= esc($puebloInd['pue_ind_nombre']) ?>
                                                        </option>
                                                <?php endforeach; ?>
                                        </select>
                                </td>
                                <td>
                                        <select data-field="nivel_educacion" id="pac_nivel_educacion" name="pac_nivel_educacion" class="form-select w-full h-10">
                                                <option value="" disabled selected>Seleccione</option>
                                                <?php foreach ($nivelesEducacion as $nivel): ?>
                                                        <option value="<?= esc($nivel['nedu_codigo']) ?>">
                                                                <?= esc($nivel['nedu_nivel']) ?>
                                                        </option>
                                                <?php endforeach; ?>
                                </td>

                        </tr>
                        <!-- Fila 6 -->
                        <tr>
                                <th class="subheader">Estado de nivel de Educación</th>
                                <th class="subheader">Tipo de Empresa de trabajo</th>
                                <th class="subheader">Ocupación / Profesión</th>
                                <th class="subheader" colspan="2">Seguro de salud principal</th>
                        </tr>
                        <tr>
                                <td>
                                        <select data-field="estado_nivel_educ" id="pac_estado_educacion" name="pac_estado_educacion" class="form-select w-full h-10">
                                                <option value="" disabled selected>Seleccione</option>
                                                <?php foreach ($estadosEducacion as $estado): ?>
                                                        <option value="<?= esc($estado['eneduc_codigo']) ?>">
                                                                <?= esc($estado['eneduc_estado']) ?>
                                                        </option>
                                                <?php endforeach; ?>
                                        </select>

                                <td>
                                        <select data-field="empresa" id="pac_tipo_empresa" name="pac_tipo_empresa" class="form-select w-full h-10">
                                                <option value="" disabled selected>Seleccione</option>
                                                <?php foreach ($empresas as $empresa): ?>
                                                        <option value="<?= esc($empresa['emp_codigo']) ?>">
                                                                <?= esc($empresa['emp_descripcion']) ?>
                                                        </option>
                                                <?php endforeach; ?>
                                        </select>
                                </td>


                                <td><input type="text" data-field="pac_ocupacion" id="pac_ocupacion" name="pac_ocupacion" class="form-input"
                                                placeholder="Ej: Agricultor, Profesor, etc.">
                                </td>

                                <td>

                                        <select data-field="seguro" id="pac_seguro" name="pac_seguro" class="form-select">
                                                <option value="" disabled selected>Seleccione</option>
                                                <?php foreach ($seguros as $seguro): ?>
                                                        <option value="<?= esc($seguro['seg_codigo']) ?>">
                                                                <?= esc($seguro['seg_descripcion']) ?>
                                                        </option>
                                                <?php endforeach; ?>
                                        </select>

                                <td>
                        </tr>
                        <!-- Fila 7 -->
                        <tr>
                                <th class="section-label" rowspan="2">Residencia</th>
                                <td>
                                        <label for="res_provincia" class="form-field-label">Provincia</label>
                                        <input type="text" data-field="pac_provincias" id="res_provincia" name="res_provincia" class="form-input"
                                                placeholder="Ej: Pichincha">
                                </td>
                                <td>
                                        <label for="res_canton" class="form-field-label">Cantón</label>
                                        <input type="text" data-field="pac_cantones" id="res_canton" name="res_canton" class="form-input"
                                                placeholder="Ej: Quito">
                                </td>
                                <td>
                                        <label for="res_parroquia" class="form-field-label">Parroquia</label>
                                        <input type="text" data-field="pac_parroquias" id="res_parroquia" name="res_parroquia" class="form-input"
                                                placeholder="Ej: Calderón">
                                </td>
                                <td>
                                        <label for="res_barrio_sector" class="form-field-label">Barrio o Sector</label>
                                        <input type="text" data-field="pac_barrio" id="res_barrio_sector" name="res_barrio_sector" class="form-input"
                                                placeholder="Ej: Carapungo">
                                </td>
                        </tr>
                        <tr>
                                <td>
                                        <label for="res_calle_principal" class="form-field-label">Calle Principal</label>
                                        <input type="text" data-field="pac_direccion" id="res_calle_principal" name="res_calle_principal"
                                                class="form-input" placeholder="Ej: Av. Amazonas N30-123">
                                </td>
                                <td>
                                        <label for="res_calle_secundaria" class="form-field-label">Calle Secundaria</label>
                                        <input type="text" id="res_calle_secundaria" name="res_calle_secundaria"
                                                class="form-input" placeholder="Ej: Río Coca E10-45">
                                </td>
                                <td>
                                        <label for="res_referencia" class="form-field-label">Referencia</label>
                                        <input type="text" id="res_referencia" name="res_referencia" class="form-input"
                                                placeholder="Ej: Casa de 2 pisos color crema">
                                </td>
                        </tr>
                        <!-- Fila 8 -->
                        <tr>
                                <th class="subheader">En caso necesario llamar a:</th>
                                <th class="subheader">Parentesco</th>
                                <th class="subheader">Dirección (Contacto)</th>
                                <th class="subheader" colspan="2">N° teléfono (Contacto)</th>
                        </tr>
                        <tr>
                                <td><input type="text" data-field="pac_avisar_a" id="contacto_emerg_nombre" name="contacto_emerg_nombre"
                                                class="form-input" placeholder="Nombre completo"></td>
                                <td><input type="text" data-field="pac_parentezco_avisar_a" id="contacto_emerg_parentesco" name="contacto_emerg_parentesco"
                                                class="form-input" placeholder="Ej: Madre, Esposo/a"></td>
                                <td><input type="text" data-field="pac_direccion_avisar" id="contacto_emerg_direccion" name="contacto_emerg_direccion"
                                                class="form-input" placeholder="Dirección del contacto"></td>
                                <td><input type="tel" data-field="pac_telefono_avisar_a" id="contacto_emerg_telefono" name="contacto_emerg_telefono"
                                                class="form-input" placeholder="Ej: 0987654321"></td>
                        </tr>
                        <!-- Fila 9 -->
                        <tr>
                                <th class="subheader">
                                        <span style="color: #dc2626; font-weight: bold;">* Forma de llegada</span>
                                        <small style="display: block; color: #dc2626; font-weight: normal; font-size: 0.7rem;">(Obligatorio)</small>
                                </th>
                                <th class="subheader">Fuente de información</th>
                                <th class="subheader">Institución o persona que entrega al paciente</th>
                                <th class="subheader" colspan="2">N° teléfono (Fuente)</th>
                        </tr>
                        <tr>

                                <td>

                                        <select data-field="forma_llegada" id="forma_llegada" name="forma_llegada"
                                                class="form-select w-full h-10" required
                                                style="border: 2px solid #dc2626;">
                                                <option value="">⚠️ Seleccionar forma de llegada...</option>
                                                <?php foreach ($formasLlegada as $llegada): ?>
                                                        <option value="<?= esc($llegada['lleg_codigo']) ?>">
                                                                <?= esc($llegada['lleg_descripcion']) ?>
                                                        </option>
                                                <?php endforeach; ?>
                                        </select>
                                        <div id="forma_llegada_error" style="color: #dc2626; font-size: 0.75rem; margin-top: 2px; display: none;">
                                                La forma de llegada es obligatoria
                                        </div>

                                </td>
                                <td><input type="text" data-field="ate_fuente_informacion" id="fuente_informacion" name="fuente_informacion" class="form-input"
                                                placeholder="Ej: Familiar, Policía">
                                </td>

                                <td><input type="text" data-field="ate_ins_entrega_paciente" id="entrega_paciente_nombre_inst" name="entrega_paciente_nombre_inst"
                                                class="form-input" placeholder="Institución o persona que entrega">
                                </td>

                                <td><input type="tel" data-field="ate_telefono" id="entrega_paciente_telefono" name="entrega_paciente_telefono"
                                                class="form-input" placeholder="Teléfono de quien entrega">
                                </td>


                        </tr>
                </tbody>
        </table>
</div>
<script>
        <?php if (session()->get('rol_id') == 2): ?>
                // JAVASCRIPT PARA CASCADA DE UBICACIONES
                document.addEventListener('DOMContentLoaded', function() {
                        const provinciaSelect = document.getElementById('nac_provincia');
                        const cantonSelect = document.getElementById('nac_canton');
                        const parroquiaSelect = document.getElementById('nac_parroquia');

                        const nacionalidadSelect = document.getElementById('pac_nacionalidad');
                        const lugarEcuador = document.getElementById('lugar_nac_ecuador');
                        const lugarExtranjero = document.getElementById('lugar_nac_extranjero');
                        const lugarPlaceholder = document.getElementById('lugar_nac_placeholder');

                        // Cuando cambia la nacionalidad
                        if (nacionalidadSelect) {
                                nacionalidadSelect.addEventListener('change', function() {
                                        const nacionalidadTexto = this.options[this.selectedIndex].text.toUpperCase();

                                        // Ocultar todo primero
                                        lugarEcuador.style.display = 'none';
                                        lugarExtranjero.style.display = 'none';
                                        lugarPlaceholder.style.display = 'none';

                                        // Limpiar valores previos
                                        document.getElementById('nac_provincia').value = '';
                                        document.getElementById('nac_canton').innerHTML = '<option value="">Primero seleccione provincia</option>';
                                        document.getElementById('nac_canton').disabled = true;
                                        document.getElementById('nac_parroquia').innerHTML = '<option value="">Primero seleccione cantón</option>';
                                        document.getElementById('nac_parroquia').disabled = true;
                                        document.getElementById('pac_lugar_nacimiento').value = '';

                                        // Mostrar según nacionalidad
                                        if (nacionalidadTexto.includes('ECUATORIAN')) {
                                                lugarEcuador.style.display = 'block';
                                        } else if (this.value) {
                                                lugarExtranjero.style.display = 'block';
                                        } else {
                                                lugarPlaceholder.style.display = 'block';
                                        }
                                });
                        }

                        // Cuando cambia la provincia
                        provinciaSelect.addEventListener('change', async function() {
                                const provCodigo = this.value;

                                // Resetear cantón y parroquia
                                cantonSelect.innerHTML = '<option value="">Cargando...</option>';
                                cantonSelect.disabled = true;
                                parroquiaSelect.innerHTML = '<option value="">Primero seleccione cantón</option>';
                                parroquiaSelect.disabled = true;

                                if (!provCodigo) {
                                        cantonSelect.innerHTML = '<option value="">Primero seleccione provincia</option>';
                                        return;
                                }

                                try {
                                        const response = await fetch(`<?= base_url('api/ubicacion/cantones/') ?>${provCodigo}`);
                                        const data = await response.json();

                                        if (data.status === 'success' && data.data.length > 0) {
                                                cantonSelect.innerHTML = '<option value="">Seleccione cantón</option>';
                                                data.data.forEach(canton => {
                                                        const option = document.createElement('option');
                                                        option.value = canton.cant_codigo;
                                                        option.textContent = canton.cant_nombre;
                                                        cantonSelect.appendChild(option);
                                                });
                                                cantonSelect.disabled = false;
                                        } else {
                                                cantonSelect.innerHTML = '<option value="">No hay cantones disponibles</option>';
                                        }
                                } catch (error) {
                                        console.error('Error al cargar cantones:', error);
                                        cantonSelect.innerHTML = '<option value="">Error al cargar cantones</option>';
                                }
                        });

                        // Cuando cambia el cantón
                        cantonSelect.addEventListener('change', async function() {
                                const cantCodigo = this.value;

                                // Resetear parroquia
                                parroquiaSelect.innerHTML = '<option value="">Cargando...</option>';
                                parroquiaSelect.disabled = true;

                                if (!cantCodigo) {
                                        parroquiaSelect.innerHTML = '<option value="">Primero seleccione cantón</option>';
                                        return;
                                }

                                try {
                                        const response = await fetch(`<?= base_url('api/ubicacion/parroquias/') ?>${cantCodigo}`);
                                        const data = await response.json();

                                        if (data.status === 'success' && data.data.length > 0) {
                                                parroquiaSelect.innerHTML = '<option value="">Seleccione parroquia</option>';
                                                data.data.forEach(parroquia => {
                                                        const option = document.createElement('option');
                                                        option.value = parroquia.codigo;
                                                        option.textContent = parroquia.nombre;
                                                        parroquiaSelect.appendChild(option);
                                                });
                                                parroquiaSelect.disabled = false;
                                        } else {
                                                parroquiaSelect.innerHTML = '<option value="">No hay parroquias disponibles</option>';
                                        }

                                } catch (error) {
                                        console.error('Error al cargar parroquias:', error);
                                        parroquiaSelect.innerHTML = '<option value="">Error al cargar parroquias</option>';
                                }
                        });

                        // Habilitar campos disabled antes de enviar
                        const form = document.querySelector('form');
                        if (form) {
                                form.addEventListener('submit', function(e) {
                                        // Habilitar los selects si tienen valor
                                        if (cantonSelect && cantonSelect.disabled && cantonSelect.value) {
                                                cantonSelect.disabled = false;
                                        }

                                        if (parroquiaSelect && parroquiaSelect.disabled && parroquiaSelect.value) {
                                                parroquiaSelect.disabled = false;
                                        }

                                });
                        }
                });
        <?php endif; ?>
        // VALIDACIÓN PARA FORMA DE LLEGADA OBLIGATORIA
        function validarFormaLlegada() {
                const formaLlegadaSelect = document.getElementById('forma_llegada');
                const formaLlegadaError = document.getElementById('forma_llegada_error');

                if (!formaLlegadaSelect.value) {
                        formaLlegadaError.style.display = 'block';
                        formaLlegadaSelect.style.border = '2px solid #dc2626';
                        formaLlegadaSelect.focus();
                        alert('⚠️ La forma de llegada es obligatoria. Por favor seleccione una opción.');
                        return false;
                }

                formaLlegadaError.style.display = 'none';
                formaLlegadaSelect.style.border = '2px solid #10b981';
                return true;
        }

        // Event listener para cambiar color cuando se selecciona
        document.addEventListener('DOMContentLoaded', function() {
                const formaLlegadaSelect = document.getElementById('forma_llegada');
                const formaLlegadaError = document.getElementById('forma_llegada_error');

                if (formaLlegadaSelect) {
                        formaLlegadaSelect.addEventListener('change', function() {
                                if (this.value) {
                                        formaLlegadaError.style.display = 'none';
                                        this.style.border = '2px solid #10b981';
                                } else {
                                        formaLlegadaError.style.display = 'block';
                                        this.style.border = '2px solid #dc2626';
                                }
                        });
                }

                // Interceptar el submit del formulario para validar forma de llegada
                const form = document.querySelector('form');
                if (form) {
                        form.addEventListener('submit', function(e) {
                                if (!validarFormaLlegada()) {
                                        e.preventDefault();
                                        return false;
                                }
                        });
                }
        });
</script>