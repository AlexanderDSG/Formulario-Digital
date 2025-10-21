document.getElementById('btn-buscar').addEventListener('click', async () => {
    const fuente = document.querySelector('input[name="fuente_datos"]:checked')?.value;
    if (fuente !== 'hospital') return;

    const cedula = document.getElementById('input-cedula').value.trim();
    const apellido = document.getElementById('buscar_apellido').value.trim();
    const historia = document.getElementById('input-historia-clinica').value.trim();

    let endpoint = '';
    let bodyData = '';

    if (cedula) {
        endpoint = 'admisiones/busqueda-hospital/cedula';
        bodyData = `cedula=${encodeURIComponent(cedula)}`;
    } else if (apellido) {
        endpoint = 'admisiones/busqueda-hospital/apellido';
        bodyData = `apellido=${encodeURIComponent(apellido)}`;
    } else if (historia) {
        endpoint = 'admisiones/busqueda-hospital/historia';
        bodyData = `historia=${encodeURIComponent(historia)}`;
    } else {
        mostrarAlerta('info', 'Por favor ingrese una cédula, apellido o número de historia clínica.');
        return;
    }

    const btnBuscar = document.getElementById('btn-buscar');
    const originalText = btnBuscar.innerHTML;
    btnBuscar.disabled = true;
    btnBuscar.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Buscando...';

    try {
        const response = await fetch(endpoint, {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: bodyData
        });

        if (!response.ok) {
            throw new Error(`HTTP Error: ${response.status} ${response.statusText}`);
        }

        const json = await response.json();

        if (json.success) {
            const d = json.datos;

            try {
                // Borrar campos de búsqueda
                document.getElementById('input-cedula').value = '';
                document.getElementById('buscar_apellido').value = '';
                document.getElementById('input-historia-clinica').value = '';

                const codHistoriaEl = document.getElementById('cod-historia');
                if (codHistoriaEl) {
                    codHistoriaEl.value = d.nro_historia || '';
                }

                const establHistoriaEl = document.getElementById('estab_historia_clinica');
                if (establHistoriaEl) {
                    establHistoriaEl.value = d.cedula || '';
                }

                const radioSiEl = document.getElementById('adm_historia_clinica_estab_si');
                const radioNoEl = document.getElementById('adm_historia_clinica_estab_no');

                if (radioSiEl && radioNoEl) {
                    if ((d.nro_historia || d.pac_his_cli)?.toString().trim()) {
                        radioSiEl.checked = true;
                        radioNoEl.checked = false;
                    } else {
                        radioSiEl.checked = false;
                        radioNoEl.checked = true;
                    }
                }

                // FILA 2 - Separar nombres y apellidos
                function dividirDatos(apellidos, nombres) {
                    const apellidosTokens = (apellidos || '').trim().split(/\s+/);
                    const nombresTokens = (nombres || '').trim().split(/\s+/);

                    let apellido1 = '', apellido2 = '', nombre1 = '', nombre2 = '';

                    if (apellidosTokens.length >= 2) {
                        apellido2 = apellidosTokens.pop();
                        apellido1 = apellidosTokens.join(' ');
                    } else {
                        apellido1 = apellidosTokens[0] || '';
                    }

                    if (nombresTokens.length >= 2) {
                        nombre1 = nombresTokens.shift();
                        nombre2 = nombresTokens.join(' ');
                    } else {
                        nombre1 = nombresTokens[0] || '';
                    }

                    return { apellido1, apellido2, nombre1, nombre2 };
                }

                const datos = dividirDatos(d.apellidos || d.pac_apellidos, d.nombres || d.pac_nombres);

                const elementos = [
                    { id: 'pac_apellido1', valor: datos.apellido1 },
                    { id: 'pac_apellido2', valor: datos.apellido2 },
                    { id: 'pac_nombre1', valor: datos.nombre1 },
                    { id: 'pac_nombre2', valor: datos.nombre2 }
                ];

                elementos.forEach(elem => {
                    const el = document.getElementById(elem.id);
                    if (el) el.value = elem.valor;
                });

                // FILA 3 - Estado civil y género
                const estadoCivilHospitalMap = {
                    'S': 'SOLTERO(A)',
                    'C': 'CASADO(A)',
                    'V': 'VIUDO(A)',
                    'D': 'DIVORCIADO(A)',
                    'U': 'UNIÓN LIBRE',
                    'H': 'UNIÓN DE HECHO',
                    'A': 'A ESPECIFICAR',
                    'N': 'NO APLICA',
                };

                const generoHospitalMap = {
                    'M': 'MASCULINO',
                    'F': 'FEMENINO',
                    'O': 'OTRO',
                    'A': 'A ESPECIFICAR',
                };

                const estadoCivilMap = {
                    'SOLTERO(A)': '1',
                    'CASADO(A)': '2',
                    'VIUDO(A)': '3',
                    'DIVORCIADO(A)': '4',
                    'UNIÓN LIBRE': '5',
                    'A ESPECIFICAR': '6',
                    'UNIÓN DE HECHO': '7',
                    'NO APLICA': '8',
                };

                const generoMap = {
                    'MASCULINO': '1',
                    'FEMENINO': '2',
                    'OTRO': '3',
                    'A ESPECIFICAR': '4',
                };

                const estadoCivilTexto = estadoCivilHospitalMap[d.estado_civil?.trim().toUpperCase()] || '';
                const estadoCivilValue = estadoCivilMap[estadoCivilTexto] || '';

                const generoTexto = generoHospitalMap[d.sexo?.trim().toUpperCase()] || '';
                const generoValue = generoMap[generoTexto] || '';

                const camposSegundaFila = [
                    { id: 'pac_estado_civil', valor: estadoCivilValue },
                    { id: 'pac_sexo', valor: generoValue },
                    { id: 'pac_telefono_fijo', valor: d.telefonos || '' }
                ];

                camposSegundaFila.forEach(campo => {
                    const el = document.getElementById(campo.id);
                    if (el) el.value = campo.valor;
                });

                // Fecha de nacimiento
                let fechaFormateada = '';
                if (d.fecha_nac) {
                    fechaFormateada = d.fecha_nac.split(' ')[0];
                    const fechaNacEl = document.getElementById('pac_fecha_nacimiento');
                    if (fechaNacEl) fechaNacEl.value = fechaFormateada;
                }

                // ✅ FILA 4 - NACIONALIDAD Y LUGAR DE NACIMIENTO
                const nacionalidadHospitalMap = {
                    'ECU': 'ECUATORIANA',
                    'PER': 'PERUANA',
                    'CUB': 'CUBANA',
                    'COL': 'COLOMBIANA',
                    'OTR': 'OTRA',
                    'ESP': 'A ESPECIFICAR',
                };
                
                const nacionalidadMap = {
                    'ECUATORIANA': '1',
                    'PERUANA': '2',
                    'CUBANA': '3',
                    'COLOMBIANA': '4',
                    'OTRA': '5',
                    'A ESPECIFICAR': '6',
                };
                
                const nacionalidadTexto = nacionalidadHospitalMap[d.id_nacionalidad?.trim().toUpperCase()] || '';
                const nacionalidadValue = nacionalidadMap[nacionalidadTexto] || '';
                const nacionalidadEl = document.getElementById('pac_nacionalidad');
                
                if (nacionalidadEl) {
                    nacionalidadEl.value = nacionalidadValue;
                    
                    // Disparar evento change para mostrar campos correctos
                    const event = new Event('change', { bubbles: true });
                    nacionalidadEl.dispatchEvent(event);
                    
                    // Si es ecuatoriano, dejar los selects vacíos para que el usuario los llene
                    // Si es extranjero, poner el campo de texto con información del hospital si existe
                    setTimeout(() => {
                        if (nacionalidadValue === '1') {
                            // Ecuatoriano - limpiar selects para que usuario los llene manualmente
                        } else {
                            // Extranjero - poner cualquier dato de ubicación que venga
                            const lugarNacEl = document.getElementById('pac_lugar_nacimiento');
                            if (lugarNacEl && d.lugar_nacimiento) {
                                lugarNacEl.value = d.lugar_nacimiento;
                            }
                        }
                    }, 300);
                }

                // Residencia
                const camposTerceraFila = [
                    { id: 'res_provincia', valor: d.id_provincia || '' },
                    { id: 'res_canton', valor: d.id_canton || '' },
                    { id: 'res_parroquia', valor: d.id_parroquia || '' }
                ];

                camposTerceraFila.forEach(campo => {
                    const el = document.getElementById(campo.id);
                    if (el) el.value = campo.valor;
                });

                // Calcular edad
                const edadInput = document.getElementById('pac_edad_valor');
                if (edadInput && fechaFormateada) {
                    const edad = calcularEdad(fechaFormateada);
                    edadInput.value = edad;
                }

                mostrarAlerta('success', 'Datos del hospital cargados correctamente.');

            } catch (fillError) {
                console.error('❌ Error llenando formulario:', fillError);
                mostrarAlerta('warning', 'Datos del paciente obtenidos, Algunos campos del formulario deben ser actualizados.');
            }

        } else {
            mostrarAlerta('error', json.mensaje || json.message || 'No se encontraron datos del hospital.');
        }

    } catch (error) {
        console.error('❌ Error completo en la consulta:', error);
        console.error('Stack trace:', error.stack);
        mostrarAlerta('error', `Error al consultar la base de datos del hospital: ${error.message}`);
    } finally {
        btnBuscar.disabled = false;
        btnBuscar.innerHTML = originalText;
    }
});

// El autocompletado se maneja desde buscar_paciente.js