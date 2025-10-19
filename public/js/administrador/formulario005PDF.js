// ===== SISTEMA COMPLETO DE GENERACIÓN DE PDF FORMULARIO 005 - VERSIÓN DINÁMICA =====

// === CONFIGURACIÓN GLOBAL ===
const PDF_CONFIG_005 = {
    DEBUG: true,
    PAGE_WIDTH: 765,
    PAGE_HEIGHT: 1050,

    // Configuraciones de tamaño de fuente por sección
    FONT_SIZES: {
        // PÁGINA 1
        ESTABLECIMIENTO_PAG1: 12,    // Sección A - Página 1
        PACIENTE_PAG1: 12,           // Sección A - Página 1 (datos paciente)
        TABLA_PAG1: 9,               // Sección B - Página 1 (tabla evoluciones)

        // PÁGINA 2
        PACIENTE_PAG2: 12,            // Sección A - Página 2 (datos paciente)
        TABLA_PAG2: 9,               // Sección B - Página 2 (tabla evoluciones)

        // PÁGINA 3
        PACIENTE_PAG3: 12,            // Sección A - Página 3 (datos paciente)
        TABLA_PAG3: 9,               // Sección B - Página 3 (tabla evoluciones)
    },

    MARGIN_LEFT: 10,
    MARGIN_TOP: 20,

    // CONFIGURACIÓN MANUAL DE FILAS - Ajustable por el usuario
    // Nota: 1cm = 28.35 píxeles aproximadamente (72 DPI)
    MANUAL_ROW_CONFIG: {
        pagina1: {
            // Configuración por defecto para cada fila de página 1
            filas: [
                { y_inicial: 195, altura: 10, max_lineas: 6, espaciado_linea: 18 }, // Fila 1
                { y_inicial: 290, altura: 10, max_lineas: 6, espaciado_linea: 18 }, // Fila 2
                { y_inicial: 385, altura: 10, max_lineas: 6, espaciado_linea: 18 }, // Fila 3
                { y_inicial: 482, altura: 10, max_lineas: 6, espaciado_linea: 18 }, // Fila 4
                { y_inicial: 578, altura: 10, max_lineas: 6, espaciado_linea: 18 }, // Fila 5
                { y_inicial: 675, altura: 10, max_lineas: 6, espaciado_linea: 18 }, // Fila 6
                { y_inicial: 770, altura: 10, max_lineas: 6, espaciado_linea: 18 }, // Fila 7
                { y_inicial: 865, altura: 10, max_lineas: 6, espaciado_linea: 18 }  // Fila 8
            ]
        },
        pagina2: {
            // Configuración por defecto para cada fila de página 2
            filas: [
                { y_inicial: 155, altura: 10, max_lineas: 6, espaciado_linea: 18 }, // Fila 1
                { y_inicial: 255, altura: 10, max_lineas: 6, espaciado_linea: 18 }, // Fila 2
                { y_inicial: 353, altura: 10, max_lineas: 6, espaciado_linea: 18 }, // Fila 3
                { y_inicial: 455, altura: 10, max_lineas: 6, espaciado_linea: 18 }, // Fila 4
                { y_inicial: 552, altura: 10, max_lineas: 6, espaciado_linea: 18 }, // Fila 5
                { y_inicial: 653, altura: 10, max_lineas: 6, espaciado_linea: 18 }, // Fila 6
                { y_inicial: 750, altura: 10, max_lineas: 6, espaciado_linea: 18 }, // Fila 7
                { y_inicial: 855, altura: 10, max_lineas: 6, espaciado_linea: 18 }, // Fila 8
                { y_inicial: 950, altura: 10, max_lineas: 6, espaciado_linea: 18 }  // Fila 9
            ]
        },
        pagina3: {
            // Configuración por defecto para cada fila de página 3
            filas: [
                { y_inicial: 155, altura: 10, max_lineas: 6, espaciado_linea: 18 }, // Fila 1
                { y_inicial: 255, altura: 10, max_lineas: 6, espaciado_linea: 18 }, // Fila 2
                { y_inicial: 353, altura: 10, max_lineas: 6, espaciado_linea: 18 }, // Fila 3
                { y_inicial: 455, altura: 10, max_lineas: 6, espaciado_linea: 18 }, // Fila 4
                { y_inicial: 552, altura: 10, max_lineas: 6, espaciado_linea: 18 }, // Fila 5
                { y_inicial: 653, altura: 10, max_lineas: 6, espaciado_linea: 18 }, // Fila 6
                { y_inicial: 750, altura: 10, max_lineas: 6, espaciado_linea: 18 }, // Fila 7
                { y_inicial: 855, altura: 10, max_lineas: 6, espaciado_linea: 18 }, // Fila 8
                { y_inicial: 950, altura: 10, max_lineas: 6, espaciado_linea: 18 }  // Fila 9
            ]
        }
    }
};

// === ESTADO GLOBAL ===
window.pdfSystem005Loaded = false;
window.pdfSystem005Initialized = false;

// === FUNCIONES PARA CONFIGURACIÓN MANUAL DE FILAS ===

/**
 * Configurar manualmente las filas de una página específica
 * @param {string} pagina - 'pagina1' o 'pagina2'
 * @param {number} filaIndex - Índice de la fila (0-7 para página 1, 0-8 para página 2)
 * @param {object} config - {y_inicial: number, altura: number, max_lineas: number, espaciado_linea: number}
 */
function configurarFilaPDF005(pagina, filaIndex, config) {
    if (!PDF_CONFIG_005.MANUAL_ROW_CONFIG[pagina]) {
        console.error(`❌ [PDF-005] Página inválida: ${pagina}`);
        return false;
    }

    if (!PDF_CONFIG_005.MANUAL_ROW_CONFIG[pagina].filas[filaIndex]) {
        console.error(`❌ [PDF-005] Índice de fila inválido: ${filaIndex} para ${pagina}`);
        return false;
    }

    // Actualizar configuración
    const filaActual = PDF_CONFIG_005.MANUAL_ROW_CONFIG[pagina].filas[filaIndex];

    if (config.y_inicial !== undefined) filaActual.y_inicial = config.y_inicial;
    if (config.altura !== undefined) filaActual.altura = config.altura;
    if (config.max_lineas !== undefined) filaActual.max_lineas = config.max_lineas;
    if (config.espaciado_linea !== undefined) filaActual.espaciado_linea = config.espaciado_linea;

    return true;
}

/**
 * Configurar múltiples filas a la vez
 * @param {string} pagina - 'pagina1' o 'pagina2'
 * @param {array} configuraciones - Array de objetos {fila: number, y_inicial?: number, altura?: number, max_lineas?: number}
 */
function configurarMultiplesFilasPDF005(pagina, configuraciones) {
    const resultados = [];

    configuraciones.forEach(config => {
        const exito = configurarFilaPDF005(pagina, config.fila, {
            y_inicial: config.y_inicial,
            altura: config.altura,
            max_lineas: config.max_lineas
        });
        resultados.push({ fila: config.fila, exito });
    });

    return resultados;
}

/**
 * Obtener configuración actual de las filas
 * @param {string} pagina - 'pagina1' o 'pagina2'
 */
function obtenerConfiguracionFilasPDF005(pagina) {
    if (!PDF_CONFIG_005.MANUAL_ROW_CONFIG[pagina]) {
        console.error(`❌ [PDF-005] Página inválida: ${pagina}`);
        return null;
    }

    return JSON.parse(JSON.stringify(PDF_CONFIG_005.MANUAL_ROW_CONFIG[pagina].filas));
}

/**
 * Resetear configuración de filas a valores por defecto
 * @param {string} pagina - 'pagina1' o 'pagina2'
 */
function resetearConfiguracionFilasPDF005(pagina) {
    const configDefault = {
        pagina1: [
            { y_inicial: 195, altura: 10, max_lineas: 6, espaciado_linea: 18 }, // Fila 1
            { y_inicial: 290, altura: 10, max_lineas: 6, espaciado_linea: 18 }, // Fila 2
            { y_inicial: 385, altura: 10, max_lineas: 6, espaciado_linea: 18 }, // Fila 3
            { y_inicial: 482, altura: 10, max_lineas: 6, espaciado_linea: 18 }, // Fila 4
            { y_inicial: 578, altura: 10, max_lineas: 6, espaciado_linea: 18 }, // Fila 5
            { y_inicial: 675, altura: 10, max_lineas: 6, espaciado_linea: 18 }, // Fila 6
            { y_inicial: 770, altura: 10, max_lineas: 6, espaciado_linea: 18 }, // Fila 7
            { y_inicial: 865, altura: 10, max_lineas: 6, espaciado_linea: 18 }  // Fila 8
        ],
        pagina2: [
            { y_inicial: 155, altura: 10, max_lineas: 6, espaciado_linea: 18 }, // Fila 1
            { y_inicial: 255, altura: 10, max_lineas: 6, espaciado_linea: 18 }, // Fila 2
            { y_inicial: 353, altura: 10, max_lineas: 6, espaciado_linea: 18 }, // Fila 3
            { y_inicial: 455, altura: 10, max_lineas: 6, espaciado_linea: 18 }, // Fila 4
            { y_inicial: 552, altura: 10, max_lineas: 6, espaciado_linea: 18 }, // Fila 5
            { y_inicial: 653, altura: 10, max_lineas: 6, espaciado_linea: 18 }, // Fila 6
            { y_inicial: 750, altura: 10, max_lineas: 6, espaciado_linea: 18 }, // Fila 7
            { y_inicial: 855, altura: 10, max_lineas: 6, espaciado_linea: 18 }, // Fila 8
            { y_inicial: 950, altura: 10, max_lineas: 6, espaciado_linea: 18 }  // Fila 9
        ],
        pagina3: [
            { y_inicial: 155, altura: 10, max_lineas: 6, espaciado_linea: 18 }, // Fila 1
            { y_inicial: 255, altura: 10, max_lineas: 6, espaciado_linea: 18 }, // Fila 2
            { y_inicial: 353, altura: 10, max_lineas: 6, espaciado_linea: 18 }, // Fila 3
            { y_inicial: 455, altura: 10, max_lineas: 6, espaciado_linea: 18 }, // Fila 4
            { y_inicial: 552, altura: 10, max_lineas: 6, espaciado_linea: 18 }, // Fila 5
            { y_inicial: 653, altura: 10, max_lineas: 6, espaciado_linea: 18 }, // Fila 6
            { y_inicial: 750, altura: 10, max_lineas: 6, espaciado_linea: 18 }, // Fila 7
            { y_inicial: 855, altura: 10, max_lineas: 6, espaciado_linea: 18 }, // Fila 8
            { y_inicial: 950, altura: 10, max_lineas: 6, espaciado_linea: 18 }  // Fila 9
        ]
    };

    if (configDefault[pagina]) {
        PDF_CONFIG_005.MANUAL_ROW_CONFIG[pagina].filas = JSON.parse(JSON.stringify(configDefault[pagina]));


        return true;
    }

    return false;
}


// === FUNCIÓN PARA VERIFICAR SI JSPDF ESTÁ DISPONIBLE ===
function verificarJsPDF005() {
    if (typeof window.jsPDF === 'undefined' && typeof jsPDF === 'undefined') {
        return cargarJsPDF005();
    }
    return Promise.resolve(true);
}

// === FUNCIÓN PARA CARGAR JSPDF DINÁMICAMENTE ===
function cargarJsPDF005() {
    return new Promise((resolve, reject) => {
        if (typeof window.jsPDF !== 'undefined' || typeof jsPDF !== 'undefined') {
            resolve(true);
            return;
        }

        const script = document.createElement('script');
        script.src = 'https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js';
        script.onload = () => {
            // jsPDF se carga como window.jspdf.jsPDF
            if (window.jspdf) {
                window.jsPDF = window.jspdf.jsPDF;
            }
            resolve(true);
        };
        script.onerror = () => {
            console.error('❌ [PDF-005] Error cargando jsPDF');
            reject(new Error('No se pudo cargar jsPDF'));
        };
        document.head.appendChild(script);
    });
}

// === FUNCIÓN PARA CARGAR IMÁGENES ===
function loadImage005(url) {
    return new Promise((resolve, reject) => {
        const xhr = new XMLHttpRequest();
        xhr.open('GET', url, true);
        xhr.responseType = "blob";
        xhr.onload = function () {
            if (xhr.status === 200) {
                const reader = new FileReader();
                reader.onload = function (event) {

                    resolve(event.target.result);
                };
                reader.readAsDataURL(this.response);
            } else {
                reject(new Error(`Error cargando imagen ${url} - Status: ${xhr.status}`));
            }
        };
        xhr.onerror = () => reject(new Error(`No se pudo conectar al servidor para ${url}`));
        xhr.send();
    });
}

// === FUNCIÓN PARA OBTENER VALOR DE ELEMENTO ===
function getValue005(elementId, defaultValue = '') {
    const element = document.getElementById(elementId);
    if (!element) {
        if (PDF_CONFIG_005.DEBUG) {
            console.warn(`⚠️ [PDF-005] Elemento no encontrado: ${elementId}`);
        }
        return defaultValue;
    }

    if (element.type === 'radio' || element.type === 'checkbox') {
        const checked = document.querySelector(`input[name="${element.name}"]:checked`);
        return checked ? checked.value : defaultValue;
    }

    return element.value || defaultValue;
}

// === FUNCIÓN PARA OBTENER VALOR DE RADIO BUTTON ===
function getRadioValue005(name, defaultValue = '') {
    const checked = document.querySelector(`input[name="${name}"]:checked`);
    return checked ? checked.value : defaultValue;
}

// === FUNCIÓN PRINCIPAL PARA GENERAR PDF 005 ===
async function generatePDF005(datosPersonalizados = null) {
    try {
        await verificarJsPDF005();

        // Mostrar indicador de carga si existe
        const loadingIndicator = document.getElementById('loadingIndicator');
        if (loadingIndicator) {
            loadingIndicator.classList.remove('hidden');
        }

        // Recopilar datos del formulario
        const datos = datosPersonalizados || recopilarDatosFormulario005();


        // Cargar imágenes de fondo
        const baseUrl = window.APP_URLS?.baseUrl || '/Formulario-Digital/';
        const rutaImagen1 = baseUrl + 'public/img/Formulario005Pag1.jpg';
        const rutaImagen2 = baseUrl + 'public/img/Formulario005Pag2.jpg';

        const image1 = await loadImage005(rutaImagen1);
        const image2 = await loadImage005(rutaImagen2);

        // Crear PDF
        const pdf = new window.jsPDF('p', 'px', [PDF_CONFIG_005.PAGE_WIDTH, PDF_CONFIG_005.PAGE_HEIGHT]);

        // PÁGINA 1 - Datos del establecimiento y tabla de evolución
        pdf.addImage(image1, 'JPEG', 0, 0, PDF_CONFIG_005.PAGE_WIDTH, PDF_CONFIG_005.PAGE_HEIGHT);
        pdf.setFontSize(PDF_CONFIG_005.FONT_SIZE);

        // PÁGINA 1
        llenarSeccionEstablecimiento005PDF(pdf, datos.seccionEstablecimiento);
        llenarSeccionPaciente005PDF(pdf, datos.seccionPaciente);
        llenarTablaEvolucion005PDF(pdf, datos.evoluciones.slice(0, 8)); // Solo primeras 8 filas

        // PÁGINA 2 si es necesario
        if (datos.evoluciones.length > 8) {
            pdf.addPage();
            pdf.addImage(image2, 'JPEG', 0, 0, PDF_CONFIG_005.PAGE_WIDTH, PDF_CONFIG_005.PAGE_HEIGHT);
            pdf.setFontSize(PDF_CONFIG_005.FONT_SIZE);

            // Llenar sección A de página 2 (datos del paciente con coordenadas diferentes)
            llenarSeccionPacientePag2_005PDF(pdf, datos);

            // Llenar tabla de evoluciones con coordenadas específicas de página 2
            llenarTablaEvolucionPag2_005PDF(pdf, datos.evoluciones.slice(8, 17)); // Filas 9-17 (9 filas)
        }

        // PÁGINA 3 si es necesario
        if (datos.evoluciones.length > 17) {
            pdf.addPage();
            pdf.addImage(image2, 'JPEG', 0, 0, PDF_CONFIG_005.PAGE_WIDTH, PDF_CONFIG_005.PAGE_HEIGHT);
            pdf.setFontSize(PDF_CONFIG_005.FONT_SIZE);

            // Llenar sección A de página 3 (datos del paciente - reutiliza la función de pag2)
            llenarSeccionPacientePag3_005PDF(pdf, datos);

            // Llenar tabla de evoluciones con coordenadas específicas de página 3
            llenarTablaEvolucionPag3_005PDF(pdf, datos.evoluciones.slice(17)); // Desde la fila 18 en adelante
        }

        // Generar nombre del archivo
        const nombreArchivo = generarNombreArchivo005(datos);
        // // Descargar PDF en el navegador
        // pdf.save(nombreArchivo);
        
        // ===== GUARDAR PDF EN EL SERVIDOR ORGANIZADO POR MES =====
        try {
            // Convertir PDF a base64
            const pdfBase64 = pdf.output('datauristring');

            // Enviar al servidor
            const baseUrl = window.APP_URLS?.baseUrl || '/Formulario-Digital/';
            const response = await fetch(baseUrl + 'administrador/pdf/guardar-005', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    pdfBase64: pdfBase64,
                    nombreArchivo: nombreArchivo
                })
            });

            const resultado = await response.json();

            if (resultado.success) {
                console.log('✅ PDF guardado en servidor:', resultado.carpeta_mes);
                Swal.fire({
                    icon: 'success',
                    title: 'Éxito',
                    text: 'PDF guardado exitosamente en: ' + resultado.carpeta_mes,
                    confirmButtonText: 'Aceptar'
                });
            } else {
                console.warn('⚠️ Error al guardar PDF en servidor:', resultado.message);
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Error al guardar PDF: ' + resultado.message,
                    confirmButtonText: 'Aceptar'
                });
            }
        } catch (errorServidor) {
            console.error('❌ Error enviando PDF al servidor:', errorServidor);
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'Error al guardar PDF en el servidor',
                confirmButtonText: 'Aceptar'
            });
        }

        // Ocultar indicador de carga
        if (loadingIndicator) {
            loadingIndicator.classList.add('hidden');
        }


    } catch (error) {
        console.error('🩺 [PDF-005] 💥 Error generando PDF:', error);

        // Ocultar indicador de carga
        const loadingIndicator = document.getElementById('loadingIndicator');
        if (loadingIndicator) {
            loadingIndicator.classList.add('hidden');
        }

    }
}

/**
 * Recopilar datos del formulario 005
 */
function recopilarDatosFormulario005() {

    const datos = {
        // Sección del establecimiento
        seccionEstablecimiento: {
            institucion: getValue005('estab_institucion'),
            unicodigo: getValue005('estab_unicode'),
            establecimiento: getValue005('estab_nombre'),
            historia_clinica: document.getElementById('ep_historia_clinica')?.value || '',
            numero_archivo: document.getElementById('ep_numero_archivo')?.value || '',
            numero_hoja: document.getElementById('ep_numero_hoja')?.value || ''
        },

        // Sección del paciente
        seccionPaciente: {
            primer_apellido: getValue005('ep_primer_apellido'),
            segundo_apellido: getValue005('ep_segundo_apellido'),
            primer_nombre: getValue005('ep_primer_nombre'),
            segundo_nombre: getValue005('ep_segundo_nombre'),
            sexo: getValue005('ep_sexo'),
            edad: getValue005('ep_edad'),
            condicion_edad: document.querySelector('input[name="ep_condicion_edad"]:checked')?.value || ''
        },

        // Evoluciones y prescripciones (desde la tabla)
        evoluciones: recopilarEvoluciones005()
    };

    return datos;
}

/**
 * Recopilar datos de evoluciones desde la tabla
 */
function recopilarEvoluciones005() {
    const evoluciones = [];
    const tbody = document.querySelector('#contenedor-formulario-005 tbody');

    if (tbody) {
        const filas = tbody.querySelectorAll('tr');

        filas.forEach((fila, index) => {
            const celdas = fila.querySelectorAll('td');

            // Solo procesar filas que tienen datos (no mensajes de "no hay datos")
            if (celdas.length === 5 && !fila.textContent.includes('No hay registros')) {
                const evolucion = {
                    fecha: celdas[0]?.textContent?.trim() || '',
                    hora: celdas[1]?.textContent?.trim() || '',
                    notas_evolucion: celdas[2]?.textContent?.trim() || '',
                    farmacoterapia: celdas[3]?.textContent?.trim() || '',
                    administrado: celdas[4]?.querySelector('svg')?.classList?.contains('text-green-600') || false
                };

                evoluciones.push(evolucion);
            }
        });
    }

    return evoluciones;
}

// === SECCIÓN ESTABLECIMIENTO: DATOS DEL ESTABLECIMIENTO ===
function llenarSeccionEstablecimiento005PDF(pdf, seccionEstablecimiento) {

    // Configurar tamaño de fuente específico para esta sección
    pdf.setFontSize(PDF_CONFIG_005.FONT_SIZES.ESTABLECIMIENTO_PAG1);

    // Primera fila - Datos del establecimiento (MAYÚSCULAS)
    pdf.text((seccionEstablecimiento.institucion || '').toUpperCase(), 90, 52);
    pdf.text((seccionEstablecimiento.unicodigo || '').toUpperCase(), 185, 52);
    pdf.text((seccionEstablecimiento.establecimiento || '').toUpperCase(), 280, 52);
    pdf.text((seccionEstablecimiento.historia_clinica || '').toUpperCase(), 450, 52);
    pdf.text((seccionEstablecimiento.numero_archivo || '').toUpperCase(), 610, 52);
    pdf.text((seccionEstablecimiento.numero_hoja || '').toUpperCase(), 700, 52);

}

// === SECCIÓN PACIENTE: DATOS DEL USUARIO/PACIENTE (PÁGINA 1) ===
function llenarSeccionPaciente005PDF(pdf, seccionPaciente) {

    // Configurar tamaño de fuente específico para esta sección
    pdf.setFontSize(PDF_CONFIG_005.FONT_SIZES.PACIENTE_PAG1);

    // Segunda fila - Datos del paciente (MAYÚSCULAS)
    pdf.text((seccionPaciente.primer_apellido || '').toUpperCase(), 90, 100);
    pdf.text((seccionPaciente.segundo_apellido || '').toUpperCase(), 250, 100);
    pdf.text((seccionPaciente.primer_nombre || '').toUpperCase(), 380, 100);
    pdf.text((seccionPaciente.segundo_nombre || '').toUpperCase(), 490, 100);
    
    // Convertir sexo a iniciales: FEMENINO -> F, MASCULINO -> M
    const sexoTexto = (seccionPaciente.sexo || '').toUpperCase();
    let sexoInicial = '';
    if (sexoTexto === 'FEMENINO' || sexoTexto === 'F') {
        sexoInicial = 'F';
    } else if (sexoTexto === 'MASCULINO' || sexoTexto === 'M') {
        sexoInicial = 'M';
    } else {
        sexoInicial = sexoTexto.charAt(0); // Primera letra si no coincide
    }
    pdf.text(sexoInicial, 590, 100);
    pdf.text((seccionPaciente.edad || '').toUpperCase(), 630, 100);

    // Condición de edad (marcar checkbox correspondiente)
    const condicionEdadCoords = {
        'H': { x: 668, y: 100 }, // H (Horas)
        'D': { x: 688, y: 100 }, // D (Días)
        'M': { x: 709, y: 100 }, // M (Meses)
        'A': { x: 728, y: 100 }  // A (Años)
    };

    const condicionEdad = (seccionPaciente.condicion_edad || '').trim().toUpperCase();
    if (condicionEdadCoords[condicionEdad]) {
        const coord = condicionEdadCoords[condicionEdad];
        pdf.text('X', coord.x, coord.y);
    }
}

// === SECCIÓN TABLA PÁGINA 1: EVOLUCIÓN Y PRESCRIPCIONES ===
function llenarTablaEvolucion005PDF(pdf, evoluciones) {

    if (!evoluciones || evoluciones.length === 0) {
        return;
    }

    // Configurar tamaño de fuente
    pdf.setFontSize(PDF_CONFIG_005.FONT_SIZES.TABLA_PAG1);

    // USAR CONFIGURACIÓN MANUAL AJUSTABLE
    const configManual = PDF_CONFIG_005.MANUAL_ROW_CONFIG.pagina1;

    const config = {
        // Espaciado entre líneas dentro de cada celda
        espaciado_linea: 23,

        // Posiciones X de las columnas
        columnas: {
            fecha: 28,
            hora: 90,
            evolucion: 122,
            farmacoterapia: 450,
            administrado: 690,
        },

        // Anchos máximos para calcular caracteres
        anchos: {
            evolucion: 500,      // Ancho en píxeles para evolución
            farmacoterapia: 300   // Ancho en píxeles para farmacoterapia
        },

        // Caracteres máximos por línea (aproximado)
        max_chars: {
            evolucion: 72,
            farmacoterapia: 58
        }
    };

    // Procesar solo las primeras 7 evoluciones (página 1)
    const max_filas = configManual.filas.length;
    const evolucionesPag1 = evoluciones.slice(0, max_filas);

    evolucionesPag1.forEach((evolucion, index) => {
        // Usar configuración manual para cada fila
        const filaConfig = configManual.filas[index];
        const y_fila = filaConfig.y_inicial;
        const max_lineas = filaConfig.max_lineas;
        const espaciado_linea = filaConfig.espaciado_linea || config.espaciado_linea; // Usar espaciado de fila o por defecto

        // FECHA (MAYÚSCULAS)
        pdf.text((evolucion.fecha || '').toUpperCase(), config.columnas.fecha, y_fila);

        // HORA (MAYÚSCULAS - sin segundos)
        pdf.text(formatearHora005(evolucion.hora || '').toUpperCase(), config.columnas.hora, y_fila);

        // EVOLUCIÓN - División automática de texto con límite manual
        const textoEvolucion = (evolucion.notas_evolucion || '').toUpperCase();
        if (textoEvolucion) {
            const lineasEvolucion = dividirTextoAutomaticoManual(
                textoEvolucion,
                config.max_chars.evolucion,
                max_lineas,
                `Página 1, Fila ${index + 1}, Evolución`
            );

            lineasEvolucion.forEach((linea, lineaIndex) => {
                const y_linea = y_fila + (lineaIndex * espaciado_linea);
                pdf.text(linea, config.columnas.evolucion, y_linea);
            });
        }

        // FARMACOTERAPIA - División automática de texto con límite manual
        const textoFarmacoterapia = (evolucion.farmacoterapia || '').toUpperCase();
        if (textoFarmacoterapia) {
            const lineasFarmacoterapia = dividirTextoAutomaticoManual(
                textoFarmacoterapia,
                config.max_chars.farmacoterapia,
                max_lineas,
                `Página 1, Fila ${index + 1}, Farmacoterapia`
            );

            lineasFarmacoterapia.forEach((linea, lineaIndex) => {
                const y_linea = y_fila + (lineaIndex * espaciado_linea);
                pdf.text(linea, config.columnas.farmacoterapia, y_linea);
            });
        }

        // ADMINISTRADO (marcar si administraron)
        if (evolucion.administrado) {
            pdf.text('X', config.columnas.administrado + 20, y_fila);
        }


    });
}

// === FUNCIÓN MANUAL MEJORADA PARA DIVIDIR TEXTO ===
function dividirTextoAutomaticoManual(texto, maxCharsPorLinea, maxLineas, contexto = '') {
    if (!texto) return [];

    const palabras = texto.split(' ');
    const lineas = [];
    let lineaActual = '';

    for (const palabra of palabras) {
        // Si agregamos esta palabra, ¿excedemos el límite de caracteres?
        const lineaConPalabra = lineaActual ? `${lineaActual} ${palabra}` : palabra;

        if (lineaConPalabra.length <= maxCharsPorLinea) {
            // Cabe en la línea actual
            lineaActual = lineaConPalabra;
        } else {
            // No cabe, guardar línea actual y empezar nueva
            if (lineaActual) {
                lineas.push(lineaActual);
            }

            // Si ya alcanzamos el máximo de líneas, terminar
            if (lineas.length >= maxLineas) {
                break;
            }

            // Empezar nueva línea
            lineaActual = palabra;
        }
    }

    // Agregar la última línea si hay espacio
    if (lineaActual && lineas.length < maxLineas) {
        lineas.push(lineaActual);
    }

    // Mostrar información detallada si se truncó texto
    if (lineas.length >= maxLineas && PDF_CONFIG_005.DEBUG) {
        const textoCompleto = palabras.join(' ');
        const textoMostrado = lineas.join(' ');
        const textoRestante = textoCompleto.replace(textoMostrado, '').trim();

        if (textoRestante.length > 0) {
            console.warn(`⚠️ [PDF-005] Texto truncado en ${contexto}:`);
            console.warn(`   📝 Texto completo: "${textoCompleto.substring(0, 100)}..."`);
            console.warn(`   ✂️ Texto cortado: "${textoRestante.substring(0, 50)}..."`);
            console.warn(`   📊 Líneas usadas: ${lineas.length}/${maxLineas}`);
        }
    }

    return lineas;
}

// === FUNCIÓN ORIGINAL PARA COMPATIBILIDAD ===
function dividirTextoAutomatico(texto, maxCharsPorLinea, maxLineas) {
    return dividirTextoAutomaticoManual(texto, maxCharsPorLinea, maxLineas);
}


// === FUNCIÓN PARA LLENAR SECCIÓN A DE PÁGINA 2 (PACIENTE) ===
function llenarSeccionPacientePag2_005PDF(pdf, datos) {

    // Configurar tamaño de fuente específico para la sección paciente de página 2
    pdf.setFontSize(PDF_CONFIG_005.FONT_SIZES.PACIENTE_PAG2);

    // Coordenadas para página 2 - Sección A (usando datos correctos)
    pdf.text((datos.seccionPaciente.primer_apellido || '').toUpperCase(), 90, 60);
    pdf.text((datos.seccionPaciente.primer_nombre || '').toUpperCase(), 280, 60);
    pdf.text((datos.seccionPaciente.edad || '').toUpperCase(), 400, 60);
    // Estos dos campos vienen de seccionEstablecimiento, no de seccionPaciente
    pdf.text((datos.seccionEstablecimiento.historia_clinica || '').toUpperCase(), 500, 60);
    pdf.text((datos.seccionEstablecimiento.numero_archivo || '').toUpperCase(), 680, 60);

}

// === FUNCIÓN PARA PÁGINA 2 DE EVOLUCIÓN ===
function llenarTablaEvolucionPag2_005PDF(pdf, evoluciones) {

    if (!evoluciones || evoluciones.length === 0) {
        return;
    }

    // Configurar tamaño de fuente
    pdf.setFontSize(PDF_CONFIG_005.FONT_SIZES.TABLA_PAG2);

    // USAR CONFIGURACIÓN MANUAL AJUSTABLE PARA PÁGINA 2
    const configManual = PDF_CONFIG_005.MANUAL_ROW_CONFIG.pagina2;

    const config = {
        // Espaciado entre líneas dentro de cada celda
        espaciado_linea: 23,

        // Posiciones X de las columnas
        columnas: {
            fecha: 23,
            hora: 80,
            evolucion: 110,
            farmacoterapia: 450,
            administrado: 700
        },

        // Anchos máximos para calcular caracteres
        anchos: {
            evolucion: 320,      // Ancho en píxeles para evolución
            farmacoterapia: 280   // Ancho en píxeles para farmacoterapia
        },

        // Caracteres máximos por línea (aproximado)
        max_chars: {
            evolucion: 77,
            farmacoterapia: 61
        }
    };

    // Procesar todas las evoluciones de página 2
    const max_filas = configManual.filas.length;
    const evolucionesPag2 = evoluciones.slice(0, max_filas);

    evolucionesPag2.forEach((evolucion, index) => {
        // Usar configuración manual para cada fila
        const filaConfig = configManual.filas[index];
        const y_fila = filaConfig.y_inicial;
        const max_lineas = filaConfig.max_lineas;
        const espaciado_linea = filaConfig.espaciado_linea || config.espaciado_linea; // Usar espaciado de fila o por defecto

        // FECHA (MAYÚSCULAS)
        pdf.text((evolucion.fecha || '').toUpperCase(), config.columnas.fecha, y_fila);

        // HORA (MAYÚSCULAS - sin segundos)
        pdf.text(formatearHora005(evolucion.hora || '').toUpperCase(), config.columnas.hora, y_fila);

        // EVOLUCIÓN - División automática de texto con límite manual
        const textoEvolucion = (evolucion.notas_evolucion || '').toUpperCase();
        if (textoEvolucion) {
            const lineasEvolucion = dividirTextoAutomaticoManual(
                textoEvolucion,
                config.max_chars.evolucion,
                max_lineas,
                `Página 2, Fila ${index + 1}, Evolución`
            );

            lineasEvolucion.forEach((linea, lineaIndex) => {
                const y_linea = y_fila + (lineaIndex * espaciado_linea);
                pdf.text(linea, config.columnas.evolucion, y_linea);
            });
        }

        // FARMACOTERAPIA - División automática de texto con límite manual
        const textoFarmacoterapia = (evolucion.farmacoterapia || '').toUpperCase();
        if (textoFarmacoterapia) {
            const lineasFarmacoterapia = dividirTextoAutomaticoManual(
                textoFarmacoterapia,
                config.max_chars.farmacoterapia,
                max_lineas,
                `Página 2, Fila ${index + 1}, Farmacoterapia`
            );

            lineasFarmacoterapia.forEach((linea, lineaIndex) => {
                const y_linea = y_fila + (lineaIndex * espaciado_linea);
                pdf.text(linea, config.columnas.farmacoterapia, y_linea);
            });
        }

        // ADMINISTRADO (marcar si es verdadero)
        if (evolucion.administrado) {
            pdf.text('X', config.columnas.administrado + 20, y_fila);
        }

    });
}

// === FUNCIÓN PARA LLENAR SECCIÓN A DE PÁGINA 3 (PACIENTE) ===
function llenarSeccionPacientePag3_005PDF(pdf, datos) {

    // Configurar tamaño de fuente específico para la sección paciente de página 3
    pdf.setFontSize(PDF_CONFIG_005.FONT_SIZES.PACIENTE_PAG3);

    // Coordenadas para página 3 - Sección A (igual que página 2)
    pdf.text((datos.seccionPaciente.primer_apellido || '').toUpperCase(), 90, 60);
    pdf.text((datos.seccionPaciente.primer_nombre || '').toUpperCase(), 280, 60);
    pdf.text((datos.seccionPaciente.edad || '').toUpperCase(), 400, 60);
    pdf.text((datos.seccionEstablecimiento.historia_clinica || '').toUpperCase(), 500, 60);
    pdf.text((datos.seccionEstablecimiento.numero_archivo || '').toUpperCase(), 680, 60);

}

// === FUNCIÓN PARA PÁGINA 3 DE EVOLUCIÓN ===
function llenarTablaEvolucionPag3_005PDF(pdf, evoluciones) {

    if (!evoluciones || evoluciones.length === 0) {
        return;
    }

    // Configurar tamaño de fuente
    pdf.setFontSize(PDF_CONFIG_005.FONT_SIZES.TABLA_PAG3);

    // USAR CONFIGURACIÓN MANUAL AJUSTABLE PARA PÁGINA 3
    const configManual = PDF_CONFIG_005.MANUAL_ROW_CONFIG.pagina3;

    const config = {
        // Espaciado entre líneas dentro de cada celda
        espaciado_linea: 23,

        // Posiciones X de las columnas (igual que página 2)
        columnas: {
            fecha: 23,
            hora: 80,
            evolucion: 110,
            farmacoterapia: 450,
            administrado: 700
        },

        // Anchos máximos para calcular caracteres
        anchos: {
            evolucion: 320,      // Ancho en píxeles para evolución
            farmacoterapia: 280   // Ancho en píxeles para farmacoterapia
        },

        // Caracteres máximos por línea (aproximado)
        max_chars: {
            evolucion: 79,
            farmacoterapia: 61
        }
    };

    // Procesar todas las evoluciones de página 3
    const max_filas = configManual.filas.length;
    const evolucionesPag3 = evoluciones.slice(0, max_filas);

    evolucionesPag3.forEach((evolucion, index) => {
        // Usar configuración manual para cada fila
        const filaConfig = configManual.filas[index];
        const y_fila = filaConfig.y_inicial;
        const max_lineas = filaConfig.max_lineas;
        const espaciado_linea = filaConfig.espaciado_linea || config.espaciado_linea;

        // FECHA (MAYÚSCULAS)
        pdf.text((evolucion.fecha || '').toUpperCase(), config.columnas.fecha, y_fila);

        // HORA (MAYÚSCULAS - sin segundos)
        pdf.text(formatearHora005(evolucion.hora || '').toUpperCase(), config.columnas.hora, y_fila);

        // EVOLUCIÓN - División automática de texto con límite manual
        const textoEvolucion = (evolucion.notas_evolucion || '').toUpperCase();
        if (textoEvolucion) {
            const lineasEvolucion = dividirTextoAutomaticoManual(
                textoEvolucion,
                config.max_chars.evolucion,
                max_lineas,
                `Página 3, Fila ${index + 1}, Evolución`
            );

            lineasEvolucion.forEach((linea, lineaIndex) => {
                const y_linea = y_fila + (lineaIndex * espaciado_linea);
                pdf.text(linea, config.columnas.evolucion, y_linea);
            });
        }

        // FARMACOTERAPIA - División automática de texto con límite manual
        const textoFarmacoterapia = (evolucion.farmacoterapia || '').toUpperCase();
        if (textoFarmacoterapia) {
            const lineasFarmacoterapia = dividirTextoAutomaticoManual(
                textoFarmacoterapia,
                config.max_chars.farmacoterapia,
                max_lineas,
                `Página 3, Fila ${index + 1}, Farmacoterapia`
            );

            lineasFarmacoterapia.forEach((linea, lineaIndex) => {
                const y_linea = y_fila + (lineaIndex * espaciado_linea);
                pdf.text(linea, config.columnas.farmacoterapia, y_linea);
            });
        }

        // ADMINISTRADO (marcar si es verdadero)
        if (evolucion.administrado) {
            pdf.text('X', config.columnas.administrado + 20, y_fila);
        }

    });
}

/**
 * Función auxiliar para formatear hora (quitar segundos)
 */
function formatearHora005(hora) {
    if (!hora) return '';

    // Si la hora viene con formato HH:MM:SS, extraer solo HH:MM
    if (hora.includes(':')) {
        const partes = hora.split(':');
        if (partes.length >= 2) {
            return `${partes[0]}:${partes[1]}`;
        }
    }

    return hora;
}

/**
 * Función auxiliar para convertir texto a mayúsculas de forma segura
 */
function toUpperSafe005(text) {
    return (text || '').toString().toUpperCase();
}

/**
 * Función auxiliar para wrap de texto
 */
function wrapText005(text, maxWidth) {
    if (!text || text.length < 50) return text;

    // Simple wrap - dividir en palabras y crear líneas
    const words = text.split(' ');
    const lines = [];
    let currentLine = '';

    words.forEach(word => {
        if (currentLine.length + word.length < maxWidth / 6) { // Aproximación de caracteres por ancho
            currentLine += word + ' ';
        } else {
            lines.push(currentLine.trim());
            currentLine = word + ' ';
        }
    });

    if (currentLine.trim()) {
        lines.push(currentLine.trim());
    }

    return lines.slice(0, 3).join('\n'); // Máximo 3 líneas
}

/**
 * Generar nombre de archivo para el PDF
 */
function generarNombreArchivo005(datos) {
    // Usar la fecha de la primera evolución (fecha de atención) en lugar de la fecha actual
    const fechaEvolucion = datos.evoluciones && datos.evoluciones.length > 0
        ? datos.evoluciones[0].fecha
        : new Date().toISOString().split('T')[0];

    // Obtener primer apellido y primer nombre
    const primerApellido = (datos.seccionPaciente?.primer_apellido || '').trim().replace(/\s+/g, '_');
    const primerNombre = (datos.seccionPaciente?.primer_nombre || '').trim().replace(/\s+/g, '_');
    const cedula = datos.seccionEstablecimiento?.historia_clinica || 'SIN_HC';

    // Generar nombre del archivo: Formulario_005_APELLIDO_NOMBRE_CEDULA_FECHA.pdf
    if (primerApellido && primerNombre) {
        return `Formulario_005_${primerApellido}_${primerNombre}_${cedula}_${fechaEvolucion}.pdf`;
    } else if (primerApellido) {
        // Fallback si solo hay apellido
        return `Formulario_005_${primerApellido}_${cedula}_${fechaEvolucion}.pdf`;
    } else {
        // Fallback si no hay datos del paciente
        return `Formulario_005_Paciente_${cedula}_${fechaEvolucion}.pdf`;
    }
}

// === FUNCIÓN MEJORADA PARA INICIALIZAR PDF 005 DINÁMICAMENTE ===
function inicializarGeneradorPDF005() {
    const btnGenerarPDF = document.getElementById('btn-generar-pdf-005');

    if (btnGenerarPDF) {
        // Remover event listeners existentes
        const newBtn = btnGenerarPDF.cloneNode(true);
        btnGenerarPDF.parentNode.replaceChild(newBtn, btnGenerarPDF);

        // Agregar nuevo event listener
        newBtn.addEventListener('click', async (event) => {
            event.preventDefault();
            await generatePDF005();
        });

        // Asegurar que el botón inicie deshabilitado hasta que haya datos
        newBtn.disabled = true;
        newBtn.classList.add('opacity-50', 'cursor-not-allowed');

        window.pdfSystem005Initialized = true;

        return true;
    } else {
        if (PDF_CONFIG_005.DEBUG) {
            console.warn('🩺 [PDF-005] ⚠️ Botón PDF 005 no encontrado');
        }
        return false;
    }
}

// === FUNCIÓN PARA REINICIALIZAR PDF CUANDO SE CARGA CONTENIDO DINÁMICO ===
function reinicializarGeneradorPDF005() {
    if (window.pdfSystem005Initialized) {
        return inicializarGeneradorPDF005();
    } else {
        return inicializarGeneradorPDF005();
    }
}

// === OBSERVADOR DE CAMBIOS EN EL DOM PARA FORMULARIO 005 ===
function iniciarObservadorPDF005() {
    const observer = new MutationObserver((mutations) => {
        mutations.forEach((mutation) => {
            mutation.addedNodes.forEach((node) => {
                if (node.nodeType === 1 && node.id === 'contenedor-formulario-005') {
                    reinicializarGeneradorPDF005();
                }

                if (node.nodeType === 1 && node.querySelector && node.querySelector('#btn-generar-pdf-005')) {
                    reinicializarGeneradorPDF005();
                }
            });
        });
    });

    observer.observe(document.body, {
        childList: true,
        subtree: true
    });
}

// === FUNCIÓN GLOBAL PARA INICIALIZAR PDF 005 DESDE OTROS SCRIPTS ===
window.inicializarPDF005 = function () {
    reinicializarGeneradorPDF005();
};

// === INICIALIZACIÓN AUTOMÁTICA ===
document.addEventListener('DOMContentLoaded', function () {

    // Intentar inicializar inmediatamente
    if (!inicializarGeneradorPDF005()) {
        // Si no encontró el botón, iniciar observador
        iniciarObservadorPDF005();
    }

    window.pdfSystem005Loaded = true;
});

// === INICIALIZACIÓN ALTERNATIVA PARA DOM YA CARGADO ===
if (document.readyState === 'complete' || document.readyState === 'interactive') {
    setTimeout(() => {
        if (!window.pdfSystem005Loaded) {
            inicializarGeneradorPDF005();
            iniciarObservadorPDF005();
            window.pdfSystem005Loaded = true;
        }
    }, 100);
}

// === HACER FUNCIONES GLOBALES PARA USO EXTERNO ===
window.generatePDF005 = generatePDF005;
window.recopilarDatosFormulario005PDF = recopilarDatosFormulario005;

// === FUNCIONES GLOBALES PARA CONFIGURACIÓN MANUAL DE FILAS ===
window.configurarFilaPDF005 = configurarFilaPDF005;
window.configurarMultiplesFilasPDF005 = configurarMultiplesFilasPDF005;
window.obtenerConfiguracionFilasPDF005 = obtenerConfiguracionFilasPDF005;
window.resetearConfiguracionFilasPDF005 = resetearConfiguracionFilasPDF005;


// === FUNCIÓN DE AYUDA PARA MOSTRAR COMANDOS DISPONIBLES ===
window.ayudaPDF005 = function () {
    console.log(`
📋 === AYUDA DEL SISTEMA PDF FORMULARIO 005 ===

🔧 CONFIGURACIÓN MANUAL DE FILAS:

1️⃣ Configurar una fila específica:
   configurarFilaPDF005('pagina1', 0, {
     y_inicial: 195,       // Posición Y inicial
     max_lineas: 7,        // Máximo número de líneas
     espaciado_linea: 18   // Espaciado entre líneas (píxeles)
   });

2️⃣ Configurar múltiples filas:
   configurarMultiplesFilasPDF005('pagina1', [
     {fila: 0, max_lineas: 7, espaciado_linea: 18},
     {fila: 1, max_lineas: 5, espaciado_linea: 20},
     {fila: 2, max_lineas: 4, espaciado_linea: 16}
   ]);

3️⃣ Ver configuración actual:
   obtenerConfiguracionFilasPDF005('pagina1');
   obtenerConfiguracionFilasPDF005('pagina2');

4️⃣ Resetear a valores optimizados:
   resetearConfiguracionFilasPDF005('pagina1');
   resetearConfiguracionFilasPDF005('pagina2');

📄 PÁGINAS DISPONIBLES:
   - 'pagina1': 8 filas (índices 0-7) - espaciado: 18px
   - 'pagina2': 9 filas (índices 0-8) - espaciado: 18px
   - 'pagina3': 9 filas (índices 0-8) - espaciado: 18px

💡 CONFIGURACIÓN ACTUAL OPTIMIZADA:
   - Página 1: 8 filas máximo, espaciado 18px (filas 1-8)
   - Página 2: 9 filas máximo, espaciado 18px (filas 9-17)
   - Página 3: 9 filas máximo, espaciado 18px (filas 18-26)

🚀 GENERAR PDF:
   generatePDF005();
`);
};