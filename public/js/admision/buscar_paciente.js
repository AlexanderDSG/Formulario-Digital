document.getElementById('btn-buscar').addEventListener('click', async () => {
    const fuente = document.querySelector('input[name="fuente_datos"]:checked')?.value;

    if (fuente !== 'local') return;
    const cedula = document.getElementById('input-cedula').value.trim();
    const apellido = document.getElementById('buscar_apellido').value.trim();
    const historia = document.getElementById('input-historia-clinica').value.trim();

    let endpoint = '';
    let bodyData = '';

    if (cedula) {
        endpoint = 'admisiones/formulario/buscarPorCedula';
        bodyData = `cedula=${encodeURIComponent(cedula)}`;
    } else if (apellido) {
        endpoint = 'admisiones/formulario/buscarPorApellido';
        bodyData = `apellido=${encodeURIComponent(apellido)}`;
    } else if (historia) {
        endpoint = 'admisiones/formulario/buscarPorHistoria';
        bodyData = `historia=${encodeURIComponent(historia)}`;
    } else {
        return mostrarAlerta('info', 'Ingrese cédula, apellido o historia clínica para buscar.');
    }

    // Mostrar indicador de carga
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

        const json = await response.json();

        if (json.success) {
            const d = json.data;

            // Borrar campos de búsqueda
            document.getElementById('input-cedula').value = '';
            document.getElementById('buscar_apellido').value = '';
            document.getElementById('input-historia-clinica').value = '';

            // Verificar si tiene historia clínica
            if (!d.pac_his_cli) {
                mostrarAlerta('info', 'El paciente no tiene historia clínica registrada. Ingrese los datos manualmente.');
                return;
            }

            document.getElementById('cod-historia').value = d.pac_his_cli;

            //SECCION A

            // FILA 1
            document.getElementById('estab_historia_clinica').value = d.cedula || '';


            //SECCION B

            //FILA 1
            // Siempre usar la fecha actual para nueva admisión, no la fecha de atención previa
            const fechaHoy = new Date();
            const año = fechaHoy.getFullYear();
            const mes = String(fechaHoy.getMonth() + 1).padStart(2, '0');
            const día = String(fechaHoy.getDate()).padStart(2, '0');
            document.getElementById('adm_fecha').value = `${año}-${mes}-${día}`;
            if (d.pac_his_cli && d.pac_his_cli.trim() !== '') {
                document.getElementById('adm_historia_clinica_estab_si').checked = true;
            } else {
                // Asegura que ningún radio quede seleccionado
                document.getElementById('adm_historia_clinica_estab_si').checked = false;
                document.getElementById('adm_historia_clinica_estab_no').checked = false;
            }

            //FILA 2
            function dividirDatos(apellidos, nombres) {
                const apellidosTokens = apellidos.trim().split(/\s+/);
                const nombresTokens = nombres.trim().split(/\s+/);

                let apellido1 = '', apellido2 = '', nombre1 = '', nombre2 = '';

                // Apellidos: último token es segundo apellido, el resto es el primero
                if (apellidosTokens.length >= 2) {
                    apellido2 = apellidosTokens[apellidosTokens.length - 1];
                    apellido1 = apellidosTokens.slice(0, -1).join(' ');
                } else if (apellidosTokens.length === 1) {
                    apellido1 = apellidosTokens[0];
                }

                // Nombres: primer token es primer nombre, el resto es segundo nombre
                if (nombresTokens.length >= 2) {
                    nombre1 = nombresTokens[0];
                    nombre2 = nombresTokens.slice(1).join(' ');
                } else if (nombresTokens.length === 1) {
                    nombre1 = nombresTokens[0];
                }

                return { apellido1, apellido2, nombre1, nombre2 };
            }

            // Usar con tu JSON de datos
            const datos = dividirDatos(d.pac_apellidos, d.pac_nombres);

            // Asignar al formulario
            document.getElementById('pac_apellido1').value = datos.apellido1;
            document.getElementById('pac_apellido2').value = datos.apellido2;
            document.getElementById('pac_nombre1').value = datos.nombre1;
            document.getElementById('pac_nombre2').value = datos.nombre2;


            const tipoDocumentoMap = {
                'CC/CI': '1',
                'PAS': '2',
                'CARNÉT': '3',
                'S/D': '4',
            }
            const tipoDocumentoValue = tipoDocumentoMap[d.tipo_documento?.trim().toUpperCase()] || '';
            const tipoDocumentoSelect = document.getElementById('pac_tipo_documento');
            if (tipoDocumentoSelect) {
                tipoDocumentoSelect.value = tipoDocumentoValue;
            }


            // FILA 3
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

            const estadoCivilValue = estadoCivilMap[d.estado_civil?.trim().toUpperCase()] || '';
            const estadoCivilSelect = document.getElementById('pac_estado_civil');
            if (estadoCivilSelect) {
                estadoCivilSelect.value = estadoCivilValue;
            }

            const generoMap = {
                'MASCULINO': '1',
                'FEMENINO': '2',
                'OTRO': '3',
                'A ESPECIFICAR': '4',
            };

            const generoValue = generoMap[d.genero?.trim().toUpperCase()] || '';

            const sexoSelect = document.getElementById('pac_sexo');
            if (sexoSelect) {
                sexoSelect.value = generoValue;
            }

            // CAMPOS DE TELÉFONO ACTUALIZADOS
            document.getElementById('pac_telefono_fijo').value = d.telefono_fijo || '';
            document.getElementById('pac_telefono_celular').value = d.telefono_celular || '';

            // Formatear correctamente la fecha para el input date
            let fechaFormateada = '';
            if (d.pac_fecha_nac) {
                if (d.pac_fecha_nac.includes(' ')) {
                    // Si viene con hora "1975-09-15 00:00:00", extraer solo fecha
                    fechaFormateada = d.pac_fecha_nac.split(' ')[0];
                } else {
                    const fecha = new Date(d.pac_fecha_nac);
                    if (!isNaN(fecha.getTime())) {
                        fechaFormateada = fecha.toISOString().split('T')[0];
                    }
                }
            }
            document.getElementById('pac_fecha_nacimiento').value = fechaFormateada;

            //FILA 4 - LUGAR DE NACIMIENTO Y NACIONALIDAD

            const nacionalidadMap = {
                'ECUATORIANA': '1',
                'PERUANA': '2',
                'CUBANA': '3',
                'COLOMBIANA': '4',
                'OTRA': '5',
                'A ESPECIFICAR': '6',
            };

            const nacionalidadValue = nacionalidadMap[d.nacionalidad?.trim().toUpperCase()] || '';
            const nacionalidadSelect = document.getElementById('pac_nacionalidad');

            if (nacionalidadSelect) {
                nacionalidadSelect.value = nacionalidadValue;

                // Disparar el evento change para mostrar los campos correctos
                const event = new Event('change', { bubbles: true });
                nacionalidadSelect.dispatchEvent(event);

                // Esperar un momento para que se muestren los campos correctos
                setTimeout(async () => {
                   
                    if (nacionalidadValue === '1' && d.lugar_nac_provincia) {
                        await cargarLugarNacimientoEcuador(d);
                    } else if (nacionalidadValue === '1' && !d.lugar_nac_provincia && d.pac_lugar_nac) {
                        console.warn('⚠️ Paciente ecuatoriano sin códigos - mostrando texto');
                        document.getElementById('pac_lugar_nacimiento').value = d.pac_lugar_nac || '';

                        // Opcional: Cambiar a modo extranjero temporalmente para mostrar el input de texto
                        document.getElementById('lugar_nac_ecuador').style.display = 'none';
                        document.getElementById('lugar_nac_extranjero').style.display = 'block';
                    } else if (nacionalidadValue !== '1' && d.pac_lugar_nac) {
                        document.getElementById('pac_lugar_nacimiento').value = d.pac_lugar_nac || '';
                    }
                }, 400);
            }


            // Manejar la edad desde t_paciente
            if (d.edad_numero && d.edad_unidad) {
                // Usar el ID correcto del campo de edad
                const campoEdad = document.getElementById('pac_edad_valor');

                // Los IDs correctos según tu HTML
                const radioUnidad = document.getElementById(`pac_edad_unidad_${d.edad_unidad.toLowerCase()}`);

                if (campoEdad) {
                    campoEdad.value = d.edad_numero;
                }

                if (radioUnidad) {
                    radioUnidad.checked = true;
                } else {
                    console.warn(`No se encontró el radio button para la unidad: pac_edad_unidad_${d.edad_unidad.toLowerCase()}`);
                    console.warn('Unidad recibida:', d.edad_unidad);

                    // Debug: mostrar todos los radio buttons disponibles
                    const todosRadios = document.querySelectorAll('input[name="pac_edad_unidad"]');
                }
            } else {
                // Si no hay edad guardada, calcular desde fecha de nacimiento
                establecerEdadDesdeBusqueda(d.pac_fecha_nac);
            }


            // GRUPO PRIORITARIO - NUEVO CAMPO
            if (d.grupo_prioritario == 1 || d.grupo_prioritario === true) {
                document.getElementById('pac_grupo_prioritario_si').checked = true;
            } else {
                document.getElementById('pac_grupo_prioritario_no').checked = true;
            }

            document.getElementById('pac_grupo_prioritario_especifique').value = d.grupo_sanguineo || '';


            //FILA 5
            const etniaMap = {
                'INDIGENA': '1',
                'AFREOECUATORIANA': '2',
                'MESTIZO': '3',
                'MONTUBIO': '4',
                'BLANCO': '5',
                'OTROS': '6',
                'A ESPECIFICAR': '7',
            };

            const etniaValue = etniaMap[d.etnia?.trim().toUpperCase()] || '';
            const etniaSelect = document.getElementById('pac_etnia');
            if (etniaSelect) {
                etniaSelect.value = etniaValue;
            }

            // NACIONALIDAD INDÍGENA - NUEVO CAMPO
            const nacionalidadIndigenaSelect = document.getElementById('pac_nacionalidad_indigena');
            if (nacionalidadIndigenaSelect && d.nacionalidad_indigena) {
                // Buscar el valor correcto en el select por texto
                const options = nacionalidadIndigenaSelect.options;
                for (let i = 0; i < options.length; i++) {
                    if (options[i].text.toUpperCase() === d.nacionalidad_indigena.toUpperCase()) {
                        nacionalidadIndigenaSelect.value = options[i].value;
                        break;
                    }
                }
            }

            // PUEBLO INDÍGENA - NUEVO CAMPO
            const puebloIndigenaSelect = document.getElementById('pac_pueblo_indigena');
            if (puebloIndigenaSelect && d.pueblo_indigena) {
                // Buscar el valor correcto en el select por texto
                const options = puebloIndigenaSelect.options;
                for (let i = 0; i < options.length; i++) {
                    if (options[i].text.toUpperCase() === d.pueblo_indigena.toUpperCase()) {
                        puebloIndigenaSelect.value = options[i].value;
                        break;
                    }
                }
            }

            const nivelEducativoMap = {
                'EDUCACIÓN INICIAL': '1',
                'EGB': '2',
                'BACHILLERATO': '3',
                'EDUCACIÓN SUPERIOR': '4',
            }
            const nivelEducativoValue = nivelEducativoMap[d.nivel_educativo?.trim().toUpperCase()] || '';
            const nivelEducativoSelect = document.getElementById('pac_nivel_educacion');
            if (nivelEducativoSelect) {
                nivelEducativoSelect.value = nivelEducativoValue;
            }
            //FILA 6
            const estadoEducacionMap = {
                'INCOMPLETA': '1',
                'CURSANDO': '2',
                'COMPLETA': '3',
            }
            const estadoEducacionValue = estadoEducacionMap[d.estado_nivel_educativo?.trim().toUpperCase()] || '';
            const estadoEducacionSelect = document.getElementById('pac_estado_educacion');
            if (estadoEducacionSelect) {
                estadoEducacionSelect.value = estadoEducacionValue;
            }

            const empresaMap = {
                'PÚBLICA': '1',
                'PRIVADA': '2',
                'NO TRABAJA': '3',
                'A ESPECIFICAR': '4'
            };

            const rawEmpresa = d.tipo_empresa?.trim().toUpperCase();
            const empresaValue = empresaMap[rawEmpresa] || '';

            const empresaSelect = document.getElementById('pac_tipo_empresa');
            if (empresaSelect) {
                empresaSelect.value = empresaValue;
            }

            document.getElementById('pac_ocupacion').value = d.pac_ocupacion || '';

            const seguroMap = {
                'IESS': '1',
                'ISSPOL': '2',
                'ISSFA': '3',
                'PRIVADO': '4',
                'A ESPECIFICAR': '5',
            };

            const rawSeguro = d.seguro?.trim().toUpperCase();
            const seguroValue = seguroMap[rawSeguro] || '';

            const seguroSelect = document.getElementById('pac_seguro');
            if (seguroSelect) {
                seguroSelect.value = seguroValue;
            }

            //FILA 7 residencia
            document.getElementById('res_calle_principal').value = d.direccion || '';
            document.getElementById('res_provincia').value = d.provincia || '';
            document.getElementById('res_canton').value = d.canton || '';
            document.getElementById('res_parroquia').value = d.parroquia || '';
            document.getElementById('res_barrio_sector').value = d.barrio || '';

            // CAMPOS DE DIRECCIÓN NUEVOS
            document.getElementById('res_calle_secundaria').value = d.calle_secundaria || '';
            document.getElementById('res_referencia').value = d.referencia || '';

            //FILA 8
            document.getElementById('contacto_emerg_nombre').value = d.pac_avisar_a || '';
            document.getElementById('contacto_emerg_parentesco').value = d.pac_parentezco_avisar_a || '';
            document.getElementById('contacto_emerg_direccion').value = d.pac_direccion_avisar || '';
            document.getElementById('contacto_emerg_telefono').value = d.pac_telefono_avisar_a || '';

            //FILA 9
            const formaLlegadaMap = {
                'AMBULATORIO': '1',
                'AMBULANCIA': '2',
                'OTRO TRANSPORTE': '3'
            };

            const formaLlegadaValue = formaLlegadaMap[d.forma_llegada?.trim().toUpperCase()] || '';
            const formaLlegadaSelect = document.getElementById('forma_llegada');
            if (formaLlegadaSelect) {
                formaLlegadaSelect.value = formaLlegadaValue;
            }

            document.getElementById('fuente_informacion').value = d.fuente_informacion || '';

            document.getElementById('entrega_paciente_nombre_inst').value = d.institucion_entrega || '';

            document.getElementById('entrega_paciente_telefono').value = d.telefono_atencion || '';

        } else {
            alert(json.message);
            // CAMPOS ACTUALIZADOS PARA LIMPIAR
            const campos = [
                'pac_apellido1', 'pac_apellido2', 'pac_nombre1', 'pac_nombre2', 'estab_historia_clinica',
                'pac_fecha_nacimiento', 'pac_lugar_nacimiento', 'pac_nacionalidad', 'pac_ocupacion',
                'pac_telefono_fijo', 'pac_telefono_celular',
                'res_calle_principal', 'res_calle_secundaria', 'res_referencia',
                'res_provincia', 'res_canton', 'res_parroquia', 'res_barrio_sector',
                'pac_etnia', 'pac_nacionalidad_indigena', 'pac_pueblo_indigena',
                'pac_tipo_empresa_trabaja', 'pac_edad_valor',
                'contacto_emerg_nombre', 'contacto_emerg_parentesco', 'contacto_emerg_direccion',
                'contacto_emerg_telefono', 'fuente_informacion', 'entrega_paciente_nombre_inst',
                'entrega_paciente_telefono', 'pac_tipo_empresa', 'pac_seguro'
            ]
            campos.forEach(id => {
                const input = document.getElementById(id);
                if (input) input.value = '';
            });
            // Desmarcar radios
            document.querySelectorAll('input[type="radio"]').forEach(radio => {
                radio.checked = false;
            });
        }
    } catch (error) {
        console.error('Error al buscar paciente:', error);
        mostrarAlerta('error', 'Ocurrió un error al realizar la búsqueda.');
    } finally {
        // Restaurar el botón a su estado original
        btnBuscar.disabled = false;
        btnBuscar.innerHTML = originalText;
    }
});

/**
 * Obtener la ruta base de la aplicación
 * 
 */
function obtenerRutaBase() {
    const pathname = window.location.pathname;
    
    const match = pathname.match(/^(\/[^\/]+)/);
    
    if (match && match[1] !== '/index.php') {
        return match[1]; // Retorna /Formulario-Digital
    }
    
    return '';
}

/**
 * Construir URL completa para las APIs
 */
function construirUrlApi(endpoint) {
    const baseUrl = window.location.origin;
    const rutaBase = obtenerRutaBase();
    
    return `${baseUrl}${rutaBase}/api/ubicacion/${endpoint}`;
}

/**
 * Cargar lugar de nacimiento para pacientes ecuatorianos
 * Rellena los selects de provincia, cantón y parroquia en cascada
 */
async function cargarLugarNacimientoEcuador(data) {
  
    try {
        const provinciaSelect = document.getElementById('nac_provincia');
        const cantonSelect = document.getElementById('nac_canton');
        const parroquiaSelect = document.getElementById('nac_parroquia');

        // Verificar si tenemos códigos de ubicación
        if (!data.lugar_nac_provincia) {
            console.warn('⚠️ No se encontró código de provincia');
            return;
        }

        // 1. Seleccionar provincia
        provinciaSelect.value = data.lugar_nac_provincia;

        // Verificar si se seleccionó correctamente
        if (provinciaSelect.value !== data.lugar_nac_provincia) {
            console.error('❌ No se pudo seleccionar la provincia. Código no existe en el select.');
            return;
        }

        // 2. Cargar cantones de esa provincia
        if (data.lugar_nac_canton) {
            cantonSelect.innerHTML = '<option value="">Cargando...</option>';
            cantonSelect.disabled = true;

            const urlCantones = construirUrlApi(`cantones/${data.lugar_nac_provincia}`);

            const response = await fetch(urlCantones);
            
            if (!response.ok) {
                throw new Error(`Error HTTP ${response.status}: ${response.statusText}`);
            }
            
            const cantones = await response.json();

            if (cantones.status === 'success' && cantones.data.length > 0) {
                cantonSelect.innerHTML = '<option value="">Seleccione cantón</option>';
                cantones.data.forEach(canton => {
                    const option = document.createElement('option');
                    option.value = canton.cant_codigo;
                    option.textContent = canton.cant_nombre;
                    cantonSelect.appendChild(option);
                });
                cantonSelect.disabled = false;

                // 3. Seleccionar cantón
                cantonSelect.value = data.lugar_nac_canton;

                if (cantonSelect.value !== data.lugar_nac_canton) {
                    console.error('❌ No se pudo seleccionar el cantón. Código no existe en el select.');
                    return;
                }

                // 4. Cargar parroquias de ese cantón
                if (data.lugar_nac_parroquia) {
                    parroquiaSelect.innerHTML = '<option value="">Cargando...</option>';
                    parroquiaSelect.disabled = true;

                    const urlParroquias = construirUrlApi(`parroquias/${data.lugar_nac_canton}`);

                    const responseParr = await fetch(urlParroquias);
                    
                    if (!responseParr.ok) {
                        throw new Error(`Error HTTP ${responseParr.status}: ${responseParr.statusText}`);
                    }
                    
                    const parroquias = await responseParr.json();

                    if (parroquias.status === 'success' && parroquias.data.length > 0) {
                        parroquiaSelect.innerHTML = '<option value="">Seleccione parroquia</option>';
                        parroquias.data.forEach(parroquia => {
                            const option = document.createElement('option');
                            option.value = parroquia.codigo;
                            option.textContent = parroquia.nombre;
                            parroquiaSelect.appendChild(option);
                        });
                        parroquiaSelect.disabled = false;

                        // 5. Seleccionar parroquia
                        parroquiaSelect.value = data.lugar_nac_parroquia;

                        if (parroquiaSelect.value !== data.lugar_nac_parroquia) {
                            console.warn('⚠️ No se pudo seleccionar la parroquia. Código:', data.lugar_nac_parroquia);
                        } else {
                        }
                    } else {
                        console.warn('⚠️ No hay parroquias disponibles para este cantón');
                        parroquiaSelect.innerHTML = '<option value="">No hay parroquias disponibles</option>';
                        parroquiaSelect.disabled = true;
                    }
                }
            } else {
                console.warn('⚠️ No hay cantones disponibles para esta provincia');
                cantonSelect.innerHTML = '<option value="">No hay cantones disponibles</option>';
                cantonSelect.disabled = true;
            }
        }
    } catch (error) {
        console.error('❌ Error al cargar lugar de nacimiento:', error);
        mostrarAlerta('warning', 'No se pudo cargar el lugar de nacimiento: ' + error.message);
    }
}

// AUTOCOMPLETADO PERSONALIZADO - Sin jQuery UI
class AutoComplete {
    constructor(inputElement, options = {}) {
        this.input = inputElement;
        this.options = {
            minLength: options.minLength || 2,
            source: options.source || (() => []),
            onSelect: options.onSelect || (() => { }),
            maxResults: options.maxResults || 10
        };

        this.suggestions = [];
        this.currentFocus = -1;

        this.init();
    }

    init() {
        // Crear contenedor de sugerencias
        this.suggestionsContainer = document.createElement('div');
        this.suggestionsContainer.className = 'autocomplete-suggestions';
        this.suggestionsContainer.style.cssText = `
            position: absolute;
            background: white;
            border: 1px solid #ccc;
            border-radius: 4px;
            max-height: 200px;
            overflow-y: auto;
            z-index: 1000;
            display: none;
            box-shadow: 0 2px 5px rgba(0,0,0,0.2);
        `;

        // Insertar después del input
        this.input.parentNode.insertBefore(this.suggestionsContainer, this.input.nextSibling);

        // Event listeners
        this.input.addEventListener('input', this.onInput.bind(this));
        this.input.addEventListener('keydown', this.onKeyDown.bind(this));
        this.input.addEventListener('blur', () => {
            // Delay para permitir click en sugerencias
            setTimeout(() => this.hideSuggestions(), 150);
        });

        // Posicionar el contenedor
        this.positionSuggestions();
        window.addEventListener('resize', () => this.positionSuggestions());
    }

    positionSuggestions() {
        const rect = this.input.getBoundingClientRect();
        this.suggestionsContainer.style.left = rect.left + 'px';
        this.suggestionsContainer.style.top = (rect.bottom + window.scrollY) + 'px';
        this.suggestionsContainer.style.width = rect.width + 'px';
    }

    async onInput(e) {
        const value = e.target.value;

        if (value.length < this.options.minLength) {
            this.hideSuggestions();
            return;
        }

        try {
            const suggestions = await this.options.source(value);
            this.showSuggestions(suggestions);
        } catch (error) {
            console.error('Error obteniendo sugerencias:', error);
            this.hideSuggestions();
        }
    }

    showSuggestions(suggestions) {
        this.suggestions = suggestions.slice(0, this.options.maxResults);
        this.currentFocus = -1;

        if (this.suggestions.length === 0) {
            this.hideSuggestions();
            return;
        }

        this.suggestionsContainer.innerHTML = '';

        this.suggestions.forEach((suggestion, index) => {
            const div = document.createElement('div');
            div.className = 'autocomplete-suggestion';
            div.style.cssText = `
                padding: 8px 12px;
                cursor: pointer;
                border-bottom: 1px solid #eee;
            `;
            div.textContent = suggestion.label || suggestion.value || suggestion;

            div.addEventListener('mouseenter', () => {
                this.setFocus(index);
            });

            div.addEventListener('click', () => {
                this.selectSuggestion(index);
            });

            this.suggestionsContainer.appendChild(div);
        });

        this.suggestionsContainer.style.display = 'block';
        this.positionSuggestions();
    }

    hideSuggestions() {
        this.suggestionsContainer.style.display = 'none';
        this.currentFocus = -1;
    }

    onKeyDown(e) {
        const suggestions = this.suggestionsContainer.querySelectorAll('.autocomplete-suggestion');

        if (e.key === 'ArrowDown') {
            e.preventDefault();
            this.currentFocus = Math.min(this.currentFocus + 1, suggestions.length - 1);
            this.updateFocus(suggestions);
        } else if (e.key === 'ArrowUp') {
            e.preventDefault();
            this.currentFocus = Math.max(this.currentFocus - 1, -1);
            this.updateFocus(suggestions);
        } else if (e.key === 'Enter') {
            e.preventDefault();
            if (this.currentFocus >= 0) {
                this.selectSuggestion(this.currentFocus);
            }
        } else if (e.key === 'Escape') {
            this.hideSuggestions();
        }
    }

    setFocus(index) {
        this.currentFocus = index;
        const suggestions = this.suggestionsContainer.querySelectorAll('.autocomplete-suggestion');
        this.updateFocus(suggestions);
    }

    updateFocus(suggestions) {
        suggestions.forEach((suggestion, index) => {
            if (index === this.currentFocus) {
                suggestion.style.backgroundColor = '#e6f3ff';
            } else {
                suggestion.style.backgroundColor = '';
            }
        });
    }

    selectSuggestion(index) {
        const suggestion = this.suggestions[index];
        const value = suggestion.value || suggestion.label || suggestion;

        this.input.value = value;
        this.options.onSelect(suggestion);
        this.hideSuggestions();
    }
}

// INICIALIZAR AUTOCOMPLETADO
document.addEventListener('DOMContentLoaded', function () {
    const apellidoInput = document.getElementById('buscar_apellido');

    // Verificar que no se haya inicializado ya
    if (apellidoInput && !apellidoInput.hasAttribute('data-autocomplete-initialized')) {
        // Marcar como inicializado
        apellidoInput.setAttribute('data-autocomplete-initialized', 'true');

        new AutoComplete(apellidoInput, {
            minLength: 3, // Aumentado a 3 caracteres para búsquedas más rápidas
            maxResults: 10, // Reducido a 10 resultados
            source: async (term) => {
                const fuente = document.querySelector('input[name="fuente_datos"]:checked')?.value;

                if (!fuente) return [];

                let url = '';
                if (fuente === 'local') {
                    url = 'admisiones/formulario/autocompletar-apellidos';
                } else if (fuente === 'hospital') {
                    url = 'admisiones/busqueda-hospital/autocompletar-apellidos';
                } else {
                    return [];
                }

                try {
                    const response = await fetch(`${url}?term=${encodeURIComponent(term)}`);
                    const data = await response.json();
                    return data;
                } catch (error) {
                    console.error('Error en autocompletado:', error);
                    return [];
                }
            },
            onSelect: (suggestion) => {
            }
        });
    }
});
