// ============ SCRIPT ESPECÍFICO PARA FORMULARIO COMPLETO DE MÉDICOS PARA PACIENTE INCONCIENTE (NO UTILIZAR) ============
document.addEventListener('DOMContentLoaded', function() {

    // ============ EVENTO PRINCIPAL DE BÚSQUEDA ============
    const btnBuscar = document.getElementById('btn-buscar');
    if (btnBuscar) {
        btnBuscar.addEventListener('click', async () => {
            const fuente = document.querySelector('input[name="fuente_datos"]:checked')?.value;
            
            if (!fuente) {
                mostrarAlerta('info', 'Seleccione una fuente de datos (Local o Hospital).');
                return;
            }

            if (fuente === 'hospital') {
                await buscarEnHospitalMedico();
            } else if (fuente === 'local') {
                await buscarEnLocalMedico();
            }
        });
    }

    // ============ INICIALIZAR AUTOCOMPLETADO ============
    const apellidoInput = document.getElementById('buscar_apellido');
    if (apellidoInput) {
        new AutoComplete(apellidoInput, {
            minLength: 2,
            maxResults: 15,
            source: async (term) => {
                const fuente = document.querySelector('input[name="fuente_datos"]:checked')?.value;
                
                if (!fuente) return [];
                
                // ✅ SOLUCIÓN 1: Usar rutas absolutas desde base_url
                let url = '';
                if (fuente === 'hospital') {
                    // Ruta global del hospital
                    url = `${window.BASE_URL_MEDICOS}busqueda-hospital/autocompletar-apellidos`;
                } else if (fuente === 'local') {
                    // Ruta global de formulario local  
                    url = `${window.BASE_URL_MEDICOS}formulario/autocompletar-apellidos`;
                } else {
                    return [];
                }
                
                try {
                    const response = await fetch(`${url}?term=${encodeURIComponent(term)}`);
                    
                    if (!response.ok) {
                        console.error('Error HTTP en autocompletado:', response.status, response.statusText);
                        return [];
                    }
                    
                    const data = await response.json();
                    
                    // Verificar que data sea un array
                    if (!Array.isArray(data)) {
                        console.error('Respuesta no es un array:', data);
                        return [];
                    }
                    
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

    // ============ AUTO-LLENAR DATOS DEL MÉDICO ============
    autoLlenarDatosMedico();
});

// ============ BÚSQUEDA EN BASE DEL HOSPITAL PARA MÉDICOS ============
async function buscarEnHospitalMedico() {
    const cedula = document.getElementById('input-cedula')?.value.trim();
    const apellido = document.getElementById('buscar_apellido')?.value.trim();
    const historia = document.getElementById('input-historia-clinica')?.value.trim();

    let endpoint = '';
    let bodyData = '';

    // ✅ SOLUCIÓN 2: Usar rutas absolutas para búsqueda también
    if (cedula) {
        endpoint = `${window.BASE_URL_MEDICOS}busqueda-hospital/cedula`;
        bodyData = `cedula=${encodeURIComponent(cedula)}`;
    } else if (apellido) {
        endpoint = `${window.BASE_URL_MEDICOS}busqueda-hospital/apellido`;
        bodyData = `apellido=${encodeURIComponent(apellido)}`;
    } else if (historia) {
        endpoint = `${window.BASE_URL_MEDICOS}busqueda-hospital/historia`;
        bodyData = `historia=${encodeURIComponent(historia)}`;
    } else {
        mostrarAlerta('info', 'Por favor ingrese una cédula, apellido o número de historia clínica.');
        return;
    }

    try {
        console.log('🚀 Búsqueda en hospital (médico):', endpoint);
        
        const response = await fetch(endpoint, {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: bodyData
        });

        if (!response.ok) {
            throw new Error(`HTTP Error: ${response.status} ${response.statusText}`);
        }

        const json = await response.json();
        console.log('📋 Respuesta del hospital (médico):', json);

        if (json.success && json.datos) {
            llenarFormularioCompletoDesdeHospital(json.datos);
            limpiarCamposBusqueda();
            mostrarAlerta('success', '✅ Datos del hospital cargados correctamente para formulario completo.');
        } else {
            mostrarAlerta('error', json.mensaje || json.message || 'No se encontraron datos en el hospital.');
        }

    } catch (error) {
        console.error('❌ Error consultando hospital (médico):', error);
        mostrarAlerta('error', `Error al consultar hospital: ${error.message}`);
    }
}

// ============ BÚSQUEDA EN BASE LOCAL PARA MÉDICOS ============
async function buscarEnLocalMedico() {
    const cedula = document.getElementById('input-cedula')?.value.trim();
    const apellido = document.getElementById('buscar_apellido')?.value.trim();
    const historia = document.getElementById('input-historia-clinica')?.value.trim();

    let endpoint = '';
    let bodyData = '';

    // ✅ SOLUCIÓN 3: Usar rutas absolutas para búsqueda local
    if (cedula) {
        endpoint = `${window.BASE_URL_MEDICOS}formulario/buscarPorCedula`;
        bodyData = `cedula=${encodeURIComponent(cedula)}`;
    } else if (apellido) {
        endpoint = `${window.BASE_URL_MEDICOS}formulario/buscarPorApellido`;
        bodyData = `apellido=${encodeURIComponent(apellido)}`;
    } else if (historia) {
        endpoint = `${window.BASE_URL_MEDICOS}formulario/buscarPorHistoria`;
        bodyData = `historia=${encodeURIComponent(historia)}`;
    } else {
        mostrarAlerta('info', 'Ingrese cédula, apellido o historia clínica para buscar.');
        return;
    }

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

        if (json.success && json.data) {
            llenarFormularioCompletoDesdeLocal(json.data);
            limpiarCamposBusqueda();
            mostrarAlerta('success', 'Datos locales cargados correctamente para formulario completo.');
        } else {
            mostrarAlerta('error', json.message || 'No se encontraron datos en la base local.');
        }

    } catch (error) {
        console.error('❌ Error consultando base local (médico):', error);
        mostrarAlerta('error', `Error al consultar base local: ${error.message}`);
    }
}

// ============ LLENAR FORMULARIO COMPLETO DESDE HOSPITAL ============
function llenarFormularioCompletoDesdeHospital(datos) {
    console.log('🔄 Llenando formulario completo desde hospital...');
    
    // SECCIÓN A - Historia clínica
    setearCampo('cod-historia', datos.nro_historia || '');
    setearCampo('estab_historia_clinica', datos.cedula || '');
    
    // SECCIÓN B - Radio historia clínica
    marcarHistoriaClinica(datos.nro_historia || datos.pac_his_cli);
    
    // Fecha de admisión
    // Usar fecha actual con zona horaria local
    const fechaHoy = new Date();
    const fechaFormateada = `${fechaHoy.getFullYear()}-${String(fechaHoy.getMonth() + 1).padStart(2, '0')}-${String(fechaHoy.getDate()).padStart(2, '0')}`;
    setearFecha('adm_fecha', fechaFormateada);
    
    // Dividir nombres y apellidos
    const datosNombres = dividirNombresApellidos(
        datos.apellidos || datos.pac_apellidos || '', 
        datos.nombres || datos.pac_nombres || ''
    );
    
    // Nombres y apellidos
    setearCampo('pac_apellido1', datosNombres.apellido1);
    setearCampo('pac_apellido2', datosNombres.apellido2);
    setearCampo('pac_nombre1', datosNombres.nombre1);
    setearCampo('pac_nombre2', datosNombres.nombre2);
    
    // Mapear y setear estado civil
    const estadoCivil = mapearEstadoCivilHospital(datos.estado_civil);
    setearSelect('pac_estado_civil', estadoCivil);
    
    // Mapear y setear género
    const genero = mapearGeneroHospital(datos.sexo);
    setearSelect('pac_sexo', genero);
    
    // Otros campos básicos
    setearCampo('pac_telefono_fijo', datos.telefonos || '');
    setearFecha('pac_fecha_nacimiento', datos.fecha_nac);
    
    // Ubicación
    setearCampo('res_provincia', datos.id_provincia || '');
    setearCampo('res_canton', datos.id_canton || '');
    setearCampo('res_parroquia', datos.id_parroquia || '');
    
    // Nacionalidad
    const nacionalidad = mapearNacionalidadHospital(datos.id_nacionalidad);
    setearSelect('pac_nacionalidad', nacionalidad);
    
    // Calcular edad
    calcularYSetearEdad(datos.fecha_nac);
    
    // Auto-llenar algunos campos médicos específicos
    autoLlenarCamposMedicos();
    
}

// ============ LLENAR FORMULARIO COMPLETO DESDE LOCAL ============
function llenarFormularioCompletoDesdeLocal(datos) {
    
    if (!datos.pac_his_cli) {
        mostrarAlerta('info', 'El paciente no tiene historia clínica. Complete manualmente.');
        // No retornar, continuar llenando lo que se pueda
    }
    
    // SECCIÓN A - Historia clínica
    setearCampo('cod-historia', datos.pac_his_cli || '');
    setearCampo('estab_historia_clinica', datos.cedula || '');
    
    // SECCIÓN B - Radio historia clínica
    marcarHistoriaClinica(datos.pac_his_cli);
    
    // Fecha de admisión
    // Usar fecha actual con zona horaria local
    const fechaHoy2 = new Date();
    const fechaFormateada2 = `${fechaHoy2.getFullYear()}-${String(fechaHoy2.getMonth() + 1).padStart(2, '0')}-${String(fechaHoy2.getDate()).padStart(2, '0')}`;
    setearFecha('adm_fecha', fechaFormateada2);
    
    // Dividir nombres y apellidos
    const datosNombres = dividirNombresApellidos(
        datos.pac_apellidos || '', 
        datos.pac_nombres || ''
    );
    
    // Nombres y apellidos
    setearCampo('pac_apellido1', datosNombres.apellido1);
    setearCampo('pac_apellido2', datosNombres.apellido2);
    setearCampo('pac_nombre1', datosNombres.nombre1);
    setearCampo('pac_nombre2', datosNombres.nombre2);
    
    // Tipo documento
    const tipoDoc = mapearTipoDocumento(datos.tipo_documento);
    setearSelect('pac_tipo_documento', tipoDoc);
    
    // Estado civil
    const estadoCivil = mapearEstadoCivil(datos.estado_civil);
    setearSelect('pac_estado_civil', estadoCivil);
    
    // Género
    const genero = mapearGenero(datos.genero);
    setearSelect('pac_sexo', genero);
    
    // Otros campos
    setearCampo('pac_telefono_fijo', datos.pac_telefono || '');
    setearFecha('pac_fecha_nacimiento', datos.pac_fecha_nac);
    setearCampo('pac_lugar_nacimiento', datos.pac_lugar_nac || '');
    
    // Nacionalidad
    const nacionalidad = mapearNacionalidad(datos.nacionalidad);
    setearSelect('pac_nacionalidad', nacionalidad);
    
    // Etnia, educación, empresa, seguro
    const etnia = mapearEtnia(datos.etnia);
    setearSelect('pac_etnia', etnia);
    
    const nivelEducativo = mapearNivelEducativo(datos.nivel_educativo);
    setearSelect('pac_nivel_educacion', nivelEducativo);
    
    const estadoEducacion = mapearEstadoEducacion(datos.estado_nivel_educativo);
    setearSelect('pac_estado_educacion', estadoEducacion);
    
    const empresa = mapearEmpresa(datos.tipo_empresa);
    setearSelect('pac_tipo_empresa', empresa);
    
    setearCampo('pac_ocupacion', datos.pac_ocupacion || '');
    
    const seguro = mapearSeguro(datos.seguro);
    setearSelect('pac_seguro', seguro);
    
    // Dirección
    setearCampo('res_calle_principal', datos.direccion || '');
    setearCampo('res_provincia', datos.provincia || '');
    setearCampo('res_canton', datos.canton || '');
    setearCampo('res_parroquia', datos.parroquia || '');
    setearCampo('res_barrio_sector', datos.barrio || '');
    
    // Contacto emergencia
    setearCampo('contacto_emerg_nombre', datos.pac_avisar_a || '');
    setearCampo('contacto_emerg_parentesco', datos.pac_parentezco_avisar_a || '');
    setearCampo('contacto_emerg_direccion', datos.pac_direccion_avisar || '');
    setearCampo('contacto_emerg_telefono', datos.pac_telefono_avisar_a || '');
    
    // Forma llegada y otros
    const formaLlegada = mapearFormaLlegada(datos.forma_llegada);
    setearSelect('forma_llegada', formaLlegada);
    
    setearCampo('fuente_informacion', datos.fuente_informacion || '');
    setearCampo('entrega_paciente_nombre_inst', datos.institucion_entrega || '');
    setearCampo('entrega_paciente_telefono', datos.telefono_atencion || '');
    
    // Calcular edad
    calcularYSetearEdad(datos.pac_fecha_nac);
    
    // Auto-llenar algunos campos médicos específicos
    autoLlenarCamposMedicos();
    
}

// ============ AUTO-LLENAR DATOS DEL MÉDICO ============
function autoLlenarDatosMedico() {
    const fechaActual = new Date();
    const fechaFormateada = `${fechaActual.getFullYear()}-${String(fechaActual.getMonth() + 1).padStart(2, '0')}-${String(fechaActual.getDate()).padStart(2, '0')}`;
    const horaActual = new Date().toTimeString().split(' ')[0].substring(0, 5);
    
    // Sección C - Inicio de atención
    setearFecha('inicio_atencion_fecha', fechaFormateada);
    setearCampo('inicio_atencion_hora', horaActual);
    setearCampo('inicio_atencion_motivo', 'PACIENTE INCONSCIENTE');
    
    // Sección P - Profesional responsable (ya está en el PHP)
}

// ============ AUTO-LLENAR CAMPOS MÉDICOS ESPECÍFICOS ============
function autoLlenarCamposMedicos() {
    const fechaActual = new Date();
    const fechaFormateada = `${fechaActual.getFullYear()}-${String(fechaActual.getMonth() + 1).padStart(2, '0')}-${String(fechaActual.getDate()).padStart(2, '0')}`;

    // Sección D - Accidentes/Eventos
    setearFecha('acc_fecha_evento', fechaFormateada);
    setearCampo('acc_observaciones', 'Paciente ingresado inconsciente - Evaluar posibles causas');
    
    // Sección F - Enfermedad o problema actual
    setearCampo('ep_descripcion_actual', 'PACIENTE INCONSCIENTE - EVALUAR CAUSA SUBYACENTE');
    
}

// ============ FUNCIONES AUXILIARES ============

function setearCampo(id, valor) {
    const elemento = document.getElementById(id);
    if (elemento) {
        elemento.value = valor || '';
    }
}

function setearSelect(id, valor) {
    const elemento = document.getElementById(id);
    if (elemento) {
        elemento.value = valor || '';
    }
}

function setearFecha(id, fecha) {
    const elemento = document.getElementById(id);
    if (elemento && fecha) {
        const fechaFormateada = fecha.split(' ')[0];
        elemento.value = fechaFormateada;
    }
}

function marcarHistoriaClinica(historia) {
    const radioSi = document.getElementById('adm_historia_clinica_estab_si');
    const radioNo = document.getElementById('adm_historia_clinica_estab_no');
    
    if (radioSi && radioNo) {
        if (historia && historia.toString().trim()) {
            radioSi.checked = true;
            radioNo.checked = false;
        } else {
            radioSi.checked = false;
            radioNo.checked = true;
        }
    }
}

function dividirNombresApellidos(apellidos, nombres) {
    const apellidosTokens = (apellidos || '').trim().split(/\s+/).filter(t => t);
    const nombresTokens = (nombres || '').trim().split(/\s+/).filter(t => t);
    
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

function calcularYSetearEdad(fechaNacimiento) {
    const edadInput = document.getElementById('pac_edad_valor');
    if (edadInput && fechaNacimiento) {
        const edad = calcularEdad(fechaNacimiento);
        edadInput.value = edad;
    }
}

function calcularEdad(fechaNacimiento) {
    const hoy = new Date();
    const nacimiento = new Date(fechaNacimiento);
    let edad = hoy.getFullYear() - nacimiento.getFullYear();
    const m = hoy.getMonth() - nacimiento.getMonth();
    
    if (m < 0 || (m === 0 && hoy.getDate() < nacimiento.getDate())) {
        edad--;
    }
    
    return edad;
}

function limpiarCamposBusqueda() {
    setearCampo('input-cedula', '');
    setearCampo('buscar_apellido', '');
    setearCampo('input-historia-clinica', '');
}

// ============ MAPEOS DE DATOS ============

// Mapeos para hospital
function mapearEstadoCivilHospital(valor) {
    const map = {
        'S': '1', 'C': '2', 'V': '3', 'D': '4', 
        'U': '5', 'H': '7', 'A': '6', 'N': '8'
    };
    return map[valor?.trim().toUpperCase()] || '';
}

function mapearGeneroHospital(valor) {
    const map = {
        'M': '1', 'F': '2', 'O': '3', 'A': '4'
    };
    return map[valor?.trim().toUpperCase()] || '';
}

function mapearNacionalidadHospital(valor) {
    const map = {
        'ECU': '1', 'PER': '2', 'CUB': '3', 
        'COL': '4', 'OTR': '5', 'ESP': '6'
    };
    return map[valor?.trim().toUpperCase()] || '';
}

// Mapeos para base local
function mapearTipoDocumento(valor) {
    const map = {
        'CC/CI': '1', 'PAS': '2', 'CARNÉT': '3', 'S/D': '4'
    };
    return map[valor?.trim().toUpperCase()] || '';
}

function mapearEstadoCivil(valor) {
    const map = {
        'SOLTERO(A)': '1', 'CASADO(A)': '2', 'VIUDO(A)': '3', 
        'DIVORCIADO(A)': '4', 'UNIÓN LIBRE': '5', 'A ESPECIFICAR': '6',
        'UNIÓN DE HECHO': '7', 'NO APLICA': '8'
    };
    return map[valor?.trim().toUpperCase()] || '';
}

function mapearGenero(valor) {
    const map = {
        'MASCULINO': '1', 'FEMENINO': '2', 'OTRO': '3', 'A ESPECIFICAR': '4'
    };
    return map[valor?.trim().toUpperCase()] || '';
}

function mapearNacionalidad(valor) {
    const map = {
        'ECUATORIANA': '1', 'PERUANA': '2', 'CUBANA': '3', 
        'COLOMBIANA': '4', 'OTRA': '5', 'A ESPECIFICAR': '6'
    };
    return map[valor?.trim().toUpperCase()] || '';
}

function mapearEtnia(valor) {
    const map = {
        'INDIGENA': '1', 'AFREOECUATORIANA': '2', 'MESTIZO': '3', 
        'MONTUBIO': '4', 'BLANCO': '5', 'OTROS': '6', 'A ESPECIFICAR': '7'
    };
    return map[valor?.trim().toUpperCase()] || '';
}

function mapearNivelEducativo(valor) {
    const map = {
        'EDUCACIÓN INICIAL': '1', 'EGB': '2', 
        'BACHILLERATO': '3', 'EDUCACIÓN SUPERIOR': '4'
    };
    return map[valor?.trim().toUpperCase()] || '';
}

function mapearEstadoEducacion(valor) {
    const map = {
        'INCOMPLETA': '1', 'CURSANDO': '2', 'COMPLETA': '3'
    };
    return map[valor?.trim().toUpperCase()] || '';
}

function mapearEmpresa(valor) {
    const map = {
        'PÚBLICA': '1', 'PRIVADA': '2', 'NO TRABAJA': '3', 'A ESPECIFICAR': '4'
    };
    return map[valor?.trim().toUpperCase()] || '';
}

function mapearSeguro(valor) {
    const map = {
        'IESS': '1', 'ISSPOL': '2', 'ISSFA': '3', 
        'PRIVADO': '4', 'A ESPECIFICAR': '5'
    };
    return map[valor?.trim().toUpperCase()] || '';
}

function mapearFormaLlegada(valor) {
    const map = {
        'AMBULATORIO': '1', 'AMBULANCIA': '2', 'OTRO TRANSPORTE': '3'
    };
    return map[valor?.trim().toUpperCase()] || '';
}

// ============ CLASE AUTOCOMPLETE ============
class AutoComplete {
    constructor(inputElement, options = {}) {
        this.input = inputElement;
        this.options = {
            minLength: options.minLength || 2,
            source: options.source || (() => []),
            onSelect: options.onSelect || (() => {}),
            maxResults: options.maxResults || 10
        };
        
        this.suggestions = [];
        this.currentFocus = -1;
        
        this.init();
    }
    
    init() {
        this.suggestionsContainer = document.createElement('div');
        this.suggestionsContainer.className = 'autocomplete-suggestions';
        this.suggestionsContainer.style.cssText = `
            position: absolute; background: white; border: 1px solid #ccc;
            border-radius: 4px; max-height: 200px; overflow-y: auto;
            z-index: 1000; display: none; box-shadow: 0 2px 5px rgba(0,0,0,0.2);
        `;
        
        this.input.parentNode.insertBefore(this.suggestionsContainer, this.input.nextSibling);
        
        this.input.addEventListener('input', this.onInput.bind(this));
        this.input.addEventListener('keydown', this.onKeyDown.bind(this));
        this.input.addEventListener('blur', () => {
            setTimeout(() => this.hideSuggestions(), 150);
        });
        
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
            
            // Verificar que suggestions sea un array válido
            if (Array.isArray(suggestions)) {
                this.showSuggestions(suggestions);
            } else {
                console.error('Sugerencias no es un array:', suggestions);
                this.hideSuggestions();
            }
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
                padding: 8px 12px; cursor: pointer; border-bottom: 1px solid #eee;
            `;
            div.textContent = suggestion.label || suggestion.value || suggestion;
            
            div.addEventListener('mouseenter', () => this.setFocus(index));
            div.addEventListener('click', () => this.selectSuggestion(index));
            
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