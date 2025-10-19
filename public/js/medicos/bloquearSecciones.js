// ========================================
// BLOQUEAR SECCIONES - SCRIPT OPTIMIZADO PARA MEDICO.PHP
// ========================================

// Variables específicas para MÉDICOS
window.contextoMedico = true;
window.contextoEnfermeria = false;

// Inicializar cuando el DOM esté listo
document.addEventListener('DOMContentLoaded', function () {
    // Configurar formulario médico
    inicializarFormularioMedico();

    // NUEVA LÓGICA: Detectar si es modificación
    const esModificacion = verificarSiEsModificacion();

    if (esModificacion) {
        // Si es modificación, mostrar todo el formulario directamente
        sessionStorage.setItem('estadoFormularioMedico', 'completo');
        mostrarSeccionesAvanzadas();
        // Ocultar botones de decisión médica
        ocultarBotonesDecision();
    } else {
        // IMPORTANTE: Limpiar sessionStorage al iniciar nueva atención
        sessionStorage.removeItem('estadoFormularioMedico');

        // Ocultar secciones inmediatamente (sin setTimeout)
        ocultarSeccionesAvanzadas();
    }
});

// NUEVA FUNCIÓN: Verificar si es modificación
function verificarSiEsModificacion() {
    // Verificar múltiples indicadores de modificación

    // 1. Verificar si hay un input hidden o data attribute que indique modificación
    const inputModificacion = document.querySelector('input[name="es_modificacion"]');
    if (inputModificacion && inputModificacion.value === '1') {
        return true;
    }

    // 2. Verificar si hay indicador en el DOM
    const indicadorModificacion = document.querySelector('.indicador-modificacion, .modificacion-habilitada');
    if (indicadorModificacion) {
        return true;
    }

    // 3. Verificar si el formulario tiene datos de formulario_usuario con habilitado_por_admin
    const formularioUsuario = document.querySelector('input[name="habilitado_por_admin"]');
    if (formularioUsuario && formularioUsuario.value === '1') {
        return true;
    }

    // 4. Verificar por mensaje en la página
    const mensajes = document.querySelectorAll('.alert-info, .mensaje-modificacion');
    for (let mensaje of mensajes) {
        if (mensaje.textContent.toLowerCase().includes('modificación')) {
            return true;
        }
    }

    // 5. Verificar si window tiene variable de modificación (desde PHP)
    if (typeof window.esModificacion !== 'undefined' && window.esModificacion === true) {
        return true;
    }

    // 6. Verificar URL - si contiene parámetros de modificación
    const urlParams = new URLSearchParams(window.location.search);
    if (urlParams.get('modificacion') === '1' || urlParams.get('mod') === '1') {
        return true;
    }

    return false;
}

// NUEVA FUNCIÓN: Ocultar botones de decisión
function ocultarBotonesDecision() {
    // Ocultar sección de decisión médica
    const decisionMedica = document.getElementById('decision-medica');
    if (decisionMedica) {
        decisionMedica.style.display = 'none';
    }

    // Ocultar cualquier contenedor de botones de decisión
    const contenedoresDecision = document.querySelectorAll('.decision-medica-container, .botones-decision');
    contenedoresDecision.forEach(contenedor => {
        contenedor.style.display = 'none';
    });

    // Ocultar botones individuales si existen
    const btnContinuar = document.getElementById('btn-continuar-atencion');
    const btnEspecialista = document.getElementById('btn-enviar-especialista');
    const btnGuardarEspecialista = document.getElementById('btn-guardar-enviar-especialista');

    if (btnContinuar) btnContinuar.style.display = 'none';
    if (btnEspecialista) btnEspecialista.style.display = 'none';
    if (btnGuardarEspecialista) btnGuardarEspecialista.style.display = 'none';
}

// FUNCIÓN: Inicializar formulario médico
function inicializarFormularioMedico() {

    // Precargar datos si están disponibles
    if (window.precargarDatosMedicos) {
        precargarDatosFormulario();
    }

    // Mostrar información del médico actual si está disponible
    if (window.medicoActual && window.medicoActual.nombre) {
        mostrarInfoMedicoActual();
    }

    // Solo configurar eventos de decisión si NO es modificación
    if (!verificarSiEsModificacion()) {
        configurarEventosDecision();
    }
}

// FUNCIÓN: Configurar eventos de decisión médica
function configurarEventosDecision() {
    const btnContinuar = document.getElementById('btn-continuar-atencion');

    // Solo configurar el botón de continuar - el de especialista se maneja en modalEspecialidades.js
    if (btnContinuar) {
        btnContinuar.addEventListener('click', function () {
            confirmarContinuarAtencion();
        });
    }
}

// FUNCIÓN: Precargar datos en el formulario
function precargarDatosFormulario() {

    try {
        // Precargar datos del paciente
        if (window.datosPacienteMedicos) {
            precargarDatos(window.datosPacienteMedicos);
        }

        // Precargar datos de atención
        if (window.datosAtencionMedicos) {
            precargarDatos(window.datosAtencionMedicos);
        }

        // Precargar constantes vitales
        if (window.datosConstantesVitalesMedicos) {
            precargarDatos(window.datosConstantesVitalesMedicos);
        }

    } catch (error) {
        console.error('Error precargando datos:', error);
    }
}

// FUNCIÓN: Precargar datos genérica
function precargarDatos(datos) {
    if (!datos) return;

    Object.keys(datos).forEach(campo => {
        const elemento = document.querySelector(`input[name="${campo}"], select[name="${campo}"], textarea[name="${campo}"]`);
        if (elemento && datos[campo] !== null && datos[campo] !== undefined) {
            elemento.value = datos[campo];
        }
    });
}

// FUNCIÓN: Mostrar información del médico actual
function mostrarInfoMedicoActual() {
    const medico = window.medicoActual;

    const infoMedico = document.getElementById('info-medico-actual');
    if (infoMedico && medico.nombre) {
        infoMedico.innerHTML = `
            <div class="bg-blue-50 border-l-4 border-blue-400 p-3 mb-4">
                <div class="flex items-center">
                    <i class="fas fa-user-md text-blue-400 mr-2"></i>
                    <span class="text-blue-800 font-semibold">Dr. ${medico.nombre}</span>
                </div>
            </div>
        `;
    }
}

// FUNCIÓN PRINCIPAL: Ocultar secciones avanzadas
function ocultarSeccionesAvanzadas() {
    let seccionesOcultadas = 0;

    // Las secciones usan shadow-xl, no shadow-md
    const todasLasSecciones = document.querySelectorAll('.bg-white.shadow-xl.rounded-lg, .bg-white.shadow-xl, .diagnostico-table-container');

    const seccionesAOcultar = [
        // Sección E
        { texto: 'e. antecedentes patológicos personales', letra: 'E' },
        { texto: 'antecedentes patológicos', letra: 'E' },
        { texto: 'antecedentes personales', letra: 'E' },
        // Sección F
        { texto: 'f. enfermedad o problema actual', letra: 'F' },
        { texto: 'enfermedad o problema actual', letra: 'F' },
        // Sección H
        { texto: 'h. examen físico', letra: 'H' },
        { texto: 'examen físico', letra: 'H' },
        { texto: 'examen fisico', letra: 'H' },
        // Sección I
        { texto: 'i. examen físico en trauma', letra: 'I' },
        { texto: 'examen físico en trauma', letra: 'I' },
        // Sección J
        { texto: 'j. parto o aborto', letra: 'J' },
        { texto: 'embarazo', letra: 'J' },
        // Sección K
        { texto: 'k. exámenes complementarios', letra: 'K' },
        { texto: 'exámenes complementarios', letra: 'K' },
        { texto: 'examenes complementarios', letra: 'K' },
        // Sección L
        { texto: 'l. diagnóstico presuntivo', letra: 'L' },
        { texto: 'diagnóstico presuntivo', letra: 'L' },
        { texto: 'diagnostico presuntivo', letra: 'L' },
        // Sección M
        { texto: 'm. diagnósticos definitivos', letra: 'M' },
        { texto: 'diagnósticos definitivos', letra: 'M' },
        { texto: 'diagnosticos definitivos', letra: 'M' },
        // Sección N
        { texto: 'n. plan de tratamiento', letra: 'N' },
        { texto: 'plan de tratamiento', letra: 'N' },
        // Sección O
        { texto: 'o. condición al egreso', letra: 'O' },
        { texto: 'condición al egreso', letra: 'O' },
        { texto: 'condicion al egreso', letra: 'O' },
        // Sección P
        { texto: 'p. datos del profesional responsable', letra: 'P' },
        { texto: 'datos del profesional responsable', letra: 'P' },
        { texto: 'responsable de la atención', letra: 'P' }
    ];

    todasLasSecciones.forEach((seccion, index) => {
        const textoSeccion = seccion.textContent.toLowerCase().trim();
        const primerTexto = textoSeccion.substring(0, 100).toLowerCase();

        // Verificar si es alguna de las secciones a ocultar
        const esSeccionAOcultar = seccionesAOcultar.some(s =>
            primerTexto.includes(s.texto.toLowerCase())
        );

        // Excluir secciones que NO deben ocultarse
        const esSeccionExcluida =
            primerTexto.includes('decisión médica') ||
            primerTexto.includes('decision medica') ||
            primerTexto.includes('información del usuario') ||
            primerTexto.includes('informacion del usuario') ||
            primerTexto.includes('constantes vitales') ||
            primerTexto.includes('a. datos del establecimiento') ||
            primerTexto.includes('b. datos del paciente') ||
            primerTexto.includes('c. motivo de consulta') ||
            primerTexto.includes('d. signos vitales') ||
            primerTexto.includes('g. constantes vitales');

        if (esSeccionAOcultar && !esSeccionExcluida) {
            seccion.style.display = 'none';
            seccion.classList.add('seccion-oculta');
            seccionesOcultadas++;
        }
    });

    // Ocultar botón de guardar
    const botonesGuardar = document.querySelectorAll('button[type="submit"], input[type="submit"]');
    botonesGuardar.forEach(btn => {
        if (btn.textContent.toLowerCase().includes('guardar formulario') ||
            (btn.value && btn.value.toLowerCase().includes('guardar'))) {
            btn.style.display = 'none';
        }
    });


    if (seccionesOcultadas === 0) {
        console.warn('⚠️ No se ocultó ninguna sección. Revisa el HTML de las secciones.');
    }

    return seccionesOcultadas;
}

// FUNCIÓN CORREGIDA: Confirmar continuar atención
function confirmarContinuarAtencion() {
    Swal.fire({
        icon: 'question',
        title: '¿Continuar con la atención completa?',
        html:
            '✅ Se mostrarán todas las secciones del formulario médico<br>' +
            '📝 Deberá completar la evaluación completa del paciente<br>' +
            '⏱️ Esto puede tomar más tiempo<br><br>' +
            '¿Está seguro de continuar?',
        showCancelButton: true,
        confirmButtonText: 'Sí, continuar',
        cancelButtonText: 'Cancelar',
        confirmButtonColor: '#10b981',
        cancelButtonColor: '#6b7280'
    }).then((result) => {
        if (result.isConfirmed) {
            // GUARDAR ESTADO EN SESSIONSTORAGE
            sessionStorage.setItem('estadoFormularioMedico', 'completo');

            mostrarSeccionesAvanzadas();
        }
    });
}

// FUNCIÓN: Mostrar secciones avanzadas
function mostrarSeccionesAvanzadas() {

    let seccionesMostradas = 0;

    // Remover clase de secciones ocultas y mostrar
    const elementosOcultos = document.querySelectorAll('.seccion-oculta, [style*="display: none"], [style*="display:none"]');

    elementosOcultos.forEach(elemento => {
        // No mostrar botones de decisión médica o elementos específicos
        if (!elemento.id ||
            (!elemento.id.includes('decision-medica') &&
                !elemento.classList.contains('modal') &&
                !elemento.textContent.toLowerCase().includes('decisión médica'))) {

            elemento.style.display = '';
            elemento.classList.remove('seccion-oculta');
            elemento.classList.add('seccion-visible');
            seccionesMostradas++;
        }
    });

    // Ocultar sección de decisión médica
    ocultarBotonesDecision();

    // Mostrar botón de guardar
    const botonesGuardar = document.querySelectorAll('button[type="submit"], input[type="submit"]');
    botonesGuardar.forEach(btn => {
        if (btn.textContent.toLowerCase().includes('guardar') ||
            btn.value && btn.value.toLowerCase().includes('guardar')) {
            btn.style.display = '';
        }
    });

    // Scroll suave hacia las secciones mostradas
    setTimeout(() => {
        const primeraSeccionVisible = document.querySelector('.seccion-visible, .bg-white.rounded-lg.shadow-md:not([style*="display: none"])');
        if (primeraSeccionVisible) {
            primeraSeccionVisible.scrollIntoView({
                behavior: 'smooth',
                block: 'start'
            });
        }
    }, 300);
}

// FUNCIÓN: Validar formulario antes de enviar
function validarFormulario() {
    // Por ahora no hay validaciones específicas requeridas
    // ya que los datos del paciente vienen precargados de las secciones A y B

    // Aquí se pueden agregar validaciones futuras si es necesario
    // Por ejemplo: campos específicos de las secciones médicas

    return true;
}

// FUNCIÓN: Confirmar envío del formulario
function confirmarEnvioFormulario() {
    // En modo modificación, no requerir estado completo
    const esModificacion = verificarSiEsModificacion();

    if (!esModificacion) {
        // Verificar que estamos en estado completo (solo si NO es modificación)
        const estadoActual = sessionStorage.getItem('estadoFormularioMedico');
        if (estadoActual !== 'completo') {
            Swal.fire({
                icon: 'warning',
                title: 'Acción requerida',
                text: 'Debe hacer clic en "Continuar Atención Completa" antes de guardar el formulario.',
                confirmButtonText: 'Aceptar'
            });
            return false;
        }
    }

    // Verificar que existe ate_codigo
    const ateCodigoInput = document.querySelector('input[name="ate_codigo"]');
    if (!ateCodigoInput || !ateCodigoInput.value) {
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: 'No se encontró el código de atención. Por favor, vuelve a la lista y selecciona el paciente nuevamente.',
            confirmButtonText: 'Aceptar'
        });
        return false;
    }

    // Solo validar si es necesario
    if (!validarFormulario()) {
        return false;
    }

    const titulo = esModificacion ? '¿Guardar modificaciones?' : '¿Guardar formulario médico?';
    const mensaje = esModificacion ?
        'Esta acción actualizará la atención del paciente.' :
        'Esta acción completará la atención del paciente.';

    return Swal.fire({
        icon: 'question',
        title: titulo,
        text: mensaje,
        showCancelButton: true,
        confirmButtonText: 'Sí, guardar',
        cancelButtonText: 'Cancelar',
        confirmButtonColor: '#10b981',
        cancelButtonColor: '#6b7280'
    }).then((result) => {
        if (result.isConfirmed) {
            // Limpiar estado al enviar
            sessionStorage.removeItem('estadoFormularioMedico');
            return true;
        }
        return false;
    });
}

// Event listeners principales
document.addEventListener('DOMContentLoaded', function () {
    const formulario = document.getElementById('form');
    if (formulario) {
        formulario.addEventListener('submit', function (e) {
            e.preventDefault(); // Prevenir envío por defecto

            // Llamar a la función de confirmación (retorna una promesa)
            confirmarEnvioFormulario().then((confirmado) => {
                if (confirmado) {
                    // Si confirma, enviar el formulario manualmente
                    formulario.submit();
                }
                // Si no confirma, no hacer nada (el formulario no se envía)
            });
        });
    }
});

// Solo advertir si hay datos Y el formulario está en modo completo Y no es modificación
window.addEventListener('beforeunload', function (e) {
    const estadoActual = sessionStorage.getItem('estadoFormularioMedico');
    const esModificacion = verificarSiEsModificacion();

    // Solo advertir si está en modo completo y hay datos (excepto en modificaciones)
    if (estadoActual === 'completo' && !esModificacion) {
        const inputs = document.querySelectorAll('input:not([type="hidden"]), textarea, select');
        let hayDatos = false;

        for (let input of inputs) {
            if (input.value && input.value.trim() !== '') {
                hayDatos = true;
                break;
            }
        }

        if (hayDatos) {
            e.preventDefault();
            e.returnValue = 'Tienes cambios sin guardar en el formulario médico.';
        }
    }
});

// Funciones globales para compatibilidad
window.confirmarContinuarAtencion = confirmarContinuarAtencion;
window.ocultarSeccionesAvanzadas = ocultarSeccionesAvanzadas;
window.mostrarSeccionesAvanzadas = mostrarSeccionesAvanzadas;
window.verificarSiEsModificacion = verificarSiEsModificacion;

// Función para limpiar estado (debugging)
window.limpiarEstadoFormulario = function () {
    sessionStorage.removeItem('estadoFormularioMedico');
    location.reload();
};