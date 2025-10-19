<div class="container mx-auto max-w-6xl space-y-4">
    <!-- SECCION A -->


    <div class="bg-white shadow-xl rounded-lg overflow-hidden">

        <table class="w-full table-auto border-collapse">
            <thead>
                <tr>
                    <th colspan="5" class="header-main">A. DATOS DEL ESTABLECIMIENTO</th>
                </tr>
                <tr>
                    <th class="subheader">Institución del Sistema</th>
                    <th class="subheader">Unicode</th>
                    <th class="subheader">Establecimiento de Salud</th>
                    <th class="subheader">Número de Historia Clínica</th>
                    <th class="subheader">Número de Archivo</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    
                    <td><input type="text"  id="estab_institucion" name="estab_institucion" class="form-input border-gray-300 bg-gray-100 text-gray-500 cursor-not-allowed"
                            value="<?= esc($estab_institucion) ?>" readonly></td>
                    <td><input type="text" id="estab_unicode" name="estab_unicode" class="form-input border-gray-300 bg-gray-100 text-gray-500 cursor-not-allowed"
                            value="<?= esc($estab_unicode) ?>" readonly></td>
                    <td><input type="text" id="estab_nombre" name="estab_nombre" class="form-input border-gray-300 bg-gray-100 text-gray-500 cursor-not-allowed"
                            value="<?= esc($estab_nombre) ?>" readonly></td>
                            
                    <td>
                        <!-- este es donde se pone la cedula cuando se busca si no hay no se llena y debera actualizar y poner la cedula -->
                        <input type="text" data-field="pac_cedula" id="estab_historia_clinica" name="estab_historia_clinica" class="form-input"
                            placeholder="Cédula">
                    </td>

                    <td>
                        <input type="text" data-field="est_num_archivo" id="estab_archivo" name="estab_archivo" class="form-input border-gray-300 bg-gray-100 text-gray-500 cursor-not-allowed"
                            value="<?= isset($estabArchivo) ? $estabArchivo : '00001'; ?>"
                            placeholder="00001" readonly>
                    </td>


                </tr>
            </tbody>
        </table>
    </div>