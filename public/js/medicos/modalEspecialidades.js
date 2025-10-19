// ========================================
// MODALESPECIALIDADES.JS - FUNCIONALIDAD DEL MODAL
// ========================================

// Variable global para almacenar las especialidades
window.especialidadesDisponibles = [];

// FUNCIÓN: Inicializar modal de especialidades
function inicializarModalEspecialidades() {
    
    // Cargar especialidades al inicializar
    cargarEspecialidadesModal();
    
    // Configurar event listeners solo para el botón de especialista
    configurarEventListeners();
}

// FUNCIÓN: Configurar event listeners (solo para especialista)
function configurarEventListeners() {
    const btnEspecialista = document.getElementById('btn-enviar-especialista');
    const btnGuardarEnviarEspecialista = document.getElementById('btn-guardar-enviar-especialista');
    
    // Botón original (solo abrir modal sin guardar)
    if (btnEspecialista) {
        btnEspecialista.addEventListener('click', function() {
            llenarDatosModalPaciente();
            abrirModal('modalAsignarEspecialidad');
        });
    }

    // Botón para guardar secciones C y D antes de enviar
    if (btnGuardarEnviarEspecialista) {
        btnGuardarEnviarEspecialista.addEventListener('click', function() {
            guardarSeccionesIniciales();
        });
    }

    // Event listener para cerrar modal con ESC
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            cerrarModal('modalAsignarEspecialidad');
        }
    });
    
    // Event listeners para limpiar errores cuando el usuario interactúe
    configurarLimpiadorErrores();
}
function configurarLimpiadorErrores() {
    // Limpiar errores al cambiar condición de llegada
    const condicionSelect = document.querySelector('select[name="inicio_atencion_condicion"]');
    if (condicionSelect) {
        condicionSelect.addEventListener('change', function() {
            if (this.classList.contains('error-campo')) {
                this.classList.remove('border-2', 'border-red-500', 'bg-red-50', 'error-campo');
                this.classList.add('border-gray-300');
                const contenedor = this.closest('td');
                if (contenedor) {
                    contenedor.classList.remove('border-2', 'border-red-500', 'rounded');
                }
            }
        });
    }
    
    // Limpiar errores al escribir en motivo de atención
    const motivoTextarea = document.querySelector('textarea[name="inicio_atencion_motivo"]');
    if (motivoTextarea) {
        motivoTextarea.addEventListener('input', function() {
            if (this.classList.contains('error-campo')) {
                this.classList.remove('border-2', 'border-red-500', 'bg-red-50', 'error-campo');
                this.classList.add('border-gray-300');
                const contenedor = this.closest('td');
                if (contenedor) {
                    contenedor.classList.remove('border-2', 'border-red-500', 'rounded');
                }
            }
        });
    }
    
    // Limpiar errores al seleccionar cualquier tipo de evento
    const checkboxesTipoEvento = document.querySelectorAll('input[name="tipos_evento[]"]');
    checkboxesTipoEvento.forEach(checkbox => {
        checkbox.addEventListener('change', function() {
            // Si se selecciona al menos uno, limpiar errores de todos
            const algunoSeleccionado = document.querySelectorAll('input[name="tipos_evento[]"]:checked').length > 0;
            if (algunoSeleccionado) {
                checkboxesTipoEvento.forEach(cb => {
                    const contenedor = cb.closest('td') || cb.closest('.checkbox-group');
                    if (contenedor && contenedor.classList.contains('border-red-500')) {
                        contenedor.classList.remove('border-2', 'border-red-500', 'bg-red-50', 'rounded', 'p-2', 'error-campo');
                    }
                });
            }
        });
    });
}
function guardarSeccionesIniciales() {
    
    // VALIDAR CAMPOS OBLIGATORIOS ANTES DE PROCEDER
    const validacion = validarCamposObligatorios();
    if (!validacion.valido) {
        mostrarErroresValidacion(validacion.errores);
        return; // Detener el proceso si hay errores
    }
    
    // Mostrar indicador de carga
    const btn = document.getElementById('btn-guardar-enviar-especialista');
    const textoOriginal = btn.innerHTML;
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i> Guardando...';
    
    // Preparar datos del formulario
    const formData = new FormData();
    
    // Agregar ate_codigo
    const ate_codigo = document.querySelector('input[name="ate_codigo"]')?.value;
    if (ate_codigo) {
        formData.append('ate_codigo', ate_codigo);
    }
    
    // Recopilar datos de sección C (Inicio de Atención)
    recopilarDatosSeccionC(formData);
    
    // Recopilar datos de sección D (Accidentes/Violencias)
    recopilarDatosSeccionD(formData);
    
    // Enviar datos al servidor
    fetch(window.base_url + 'medicos/guardarSeccionesIniciales', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        
        if (data.success) {
            // Llenar datos del paciente en el modal
            llenarDatosModalPaciente();
            // Abrir modal para seleccionar especialidad
            abrirModal('modalAsignarEspecialidad');
        } else {
            Swal.fire({
                icon: 'error',
                title: 'Error al guardar',
                text: data.error || 'Error desconocido',
                confirmButtonText: 'Aceptar'
            });
        }
    })
    .catch(error => {
        console.error('Error:', error);
        Swal.fire({
            icon: 'error',
            title: 'Error de conexión',
            text: error.message,
            confirmButtonText: 'Aceptar'
        });
    })
    .finally(() => {
        // Restaurar botón
        btn.disabled = false;
        btn.innerHTML = textoOriginal;
    });
}
function mostrarErroresValidacion(errores) {
    // Limpiar estilos de error previos
    limpiarEstilosError();
    
    // Construir mensaje de error
    let mensajeCompleto = 'Por favor complete los siguientes campos obligatorios:\n\n';
    
    errores.forEach((error, index) => {
        mensajeCompleto += `${index + 1}. ${error.mensaje}\n`;
        
        // Resaltar el campo con error
        if (error.elemento) {
            marcarCampoConError(error.elemento, error.campo);
        }
    });

    // Mostrar alerta
    Swal.fire({
        icon: 'warning',
        title: 'Campos requeridos',
        html: mensajeCompleto.replace(/\n/g, '<br>'),
        confirmButtonText: 'Aceptar'
    }).then(() => {
        // Hacer focus en el primer campo con error
        if (errores.length > 0 && errores[0].elemento) {
            errores[0].elemento.focus();

            // Si es un checkbox, hacer scroll hasta el primer checkbox de tipos_evento
            if (errores[0].campo === 'tipos_evento') {
                const primerCheckbox = document.querySelector('input[name="tipos_evento[]"]');
                if (primerCheckbox) {
                    primerCheckbox.scrollIntoView({ behavior: 'smooth', block: 'center' });
                }
            }
        }
    });
}
function marcarCampoConError(elemento, tipoCampo) {
    if (!elemento) return;
    
    if (tipoCampo === 'tipos_evento') {
        // Para checkboxes, resaltar todos los tipos_evento
        const todosLosCheckboxes = document.querySelectorAll('input[name="tipos_evento[]"]');
        todosLosCheckboxes.forEach(checkbox => {
            const contenedor = checkbox.closest('td') || checkbox.closest('.checkbox-group');
            if (contenedor) {
                contenedor.style.border = '2px solid #ef4444';
                contenedor.style.backgroundColor = '#fef2f2';
                contenedor.style.borderRadius = '4px';
                contenedor.classList.add('error-campo');
            }
        });
    } else {
        // Para otros campos
        elemento.style.border = '2px solid #ef4444';
        elemento.style.backgroundColor = '#fef2f2';
        elemento.classList.add('error-campo');
        
        // Si es un select o textarea, también resaltar el contenedor
        const contenedor = elemento.closest('td');
        if (contenedor) {
            contenedor.style.border = '2px solid #ef4444';
            contenedor.style.borderRadius = '4px';
        }
    }
}

// NUEVA FUNCIÓN: Limpiar estilos de error
function limpiarEstilosError() {
    // Limpiar campos individuales
    const camposConError = document.querySelectorAll('.error-campo');
    camposConError.forEach(campo => {
        campo.style.border = '';
        campo.style.backgroundColor = '';
        campo.classList.remove('error-campo');
    });
    
    // Limpiar contenedores de checkboxes
    const contenedoresCheckbox = document.querySelectorAll('td');
    contenedoresCheckbox.forEach(contenedor => {
        if (contenedor.style.border === '2px solid rgb(239, 68, 68)') {
            contenedor.style.border = '';
            contenedor.style.backgroundColor = '';
            contenedor.style.borderRadius = '';
        }
    });
}
function validarCamposObligatorios() {
    const errores = [];
    
    // 1. Validar tipos_evento[] (al menos uno debe estar seleccionado)
    const tiposEventoChecked = document.querySelectorAll('input[name="tipos_evento[]"]:checked');
    if (tiposEventoChecked.length === 0) {
        errores.push({
            campo: 'tipos_evento',
            mensaje: 'Debe seleccionar al menos un tipo de evento en la Sección D',
            elemento: document.querySelector('input[name="tipos_evento[]"]')
        });
    }
    
    // 2. Validar inicio_atencion_condicion (debe tener un valor seleccionado)
    const condicionLlegada = document.querySelector('select[name="inicio_atencion_condicion"]');
    if (!condicionLlegada || !condicionLlegada.value || condicionLlegada.value === '') {
        errores.push({
            campo: 'inicio_atencion_condicion',
            mensaje: 'Debe seleccionar la condición de llegada en la Sección C',
            elemento: condicionLlegada
        });
    }
    
    // 3. Validar inicio_atencion_motivo (debe tener contenido)
    const motivoAtencion = document.querySelector('textarea[name="inicio_atencion_motivo"]');
    if (!motivoAtencion || !motivoAtencion.value.trim()) {
        errores.push({
            campo: 'inicio_atencion_motivo',
            mensaje: 'Debe describir el motivo de atención en la Sección C',
            elemento: motivoAtencion
        });
    }
    
    return {
        valido: errores.length === 0,
        errores: errores
    };
}
// FUNCIÓN AUXILIAR: Recopilar datos de sección C
function recopilarDatosSeccionC(formData) {
    // Fecha y hora de inicio de atención
    const fecha = document.querySelector('input[name="inicio_atencion_fecha"]')?.value;
    const hora = document.querySelector('input[name="inicio_atencion_hora"]')?.value;
    const condicion = document.querySelector('select[name="inicio_atencion_condicion"]')?.value;
    const motivo = document.querySelector('textarea[name="inicio_atencion_motivo"]')?.value;
    
    if (fecha) formData.append('inicio_atencion_fecha', fecha);
    if (hora) formData.append('inicio_atencion_hora', hora);
    if (condicion) formData.append('inicio_atencion_condicion', condicion);
    if (motivo) formData.append('inicio_atencion_motivo', motivo);
}


// FUNCIÓN AUXILIAR: Recopilar datos de sección D
function recopilarDatosSeccionD(formData) {
    // Checkboxes de tipos de evento (OBLIGATORIO)
    const tiposEvento = [];
    document.querySelectorAll('input[name="tipos_evento[]"]:checked').forEach(checkbox => {
        tiposEvento.push(checkbox.value);
    });
    
    if (tiposEvento.length > 0) {
        tiposEvento.forEach(tipo => {
            formData.append('tipos_evento[]', tipo);
        });
    }
    
    // Campos obligatorios
    const fechaEvento = document.querySelector('input[name="acc_fecha_evento"]')?.value;
    const horaEvento = document.querySelector('input[name="acc_hora_evento"]')?.value;
    
    if (fechaEvento) formData.append('acc_fecha_evento', fechaEvento);
    if (horaEvento) formData.append('acc_hora_evento', horaEvento);
    
    // Campos opcionales
    const lugarEvento = document.querySelector('input[name="acc_lugar_evento"]')?.value;
    const direccionEvento = document.querySelector('input[name="acc_direccion_evento"]')?.value;
    const observaciones = document.querySelector('textarea[name="acc_observaciones"]')?.value;
    
    if (lugarEvento) formData.append('acc_lugar_evento', lugarEvento);
    if (direccionEvento) formData.append('acc_direccion_evento', direccionEvento);
    if (observaciones) formData.append('acc_observaciones', observaciones);
    
    // Radio buttons - Custodia policial
    const custodiaPolicial = document.querySelector('input[name="acc_custodia_policial"]:checked');
    if (custodiaPolicial) {
        formData.append('acc_custodia_policial', custodiaPolicial.value === 'si' ? '1' : '0');
    }
    
    // Radio buttons - Notificación custodia
    const notificacionCustodia = document.querySelector('input[name="acc_notificacion_custodia"]:checked');
    if (notificacionCustodia) {
        formData.append('acc_notificacion_custodia', notificacionCustodia.value);
    }
    
    // Checkbox - Sugestivo de alcohol
    const sugestivoAlcohol = document.querySelector('input[name="acc_sugestivo_alcohol"]:checked');
    if (sugestivoAlcohol) {
        formData.append('acc_sugestivo_alcohol', '1');
    }
}
// FUNCIÓN: Abrir modal
function abrirModal(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) {
        modal.classList.remove('hidden');
        modal.classList.add('flex');
        
        // Focus en el select de especialidades
        setTimeout(() => {
            const selectEsp = document.getElementById('esp_codigo_asignar');
            if (selectEsp) selectEsp.focus();
        }, 100);
    } else {
        console.error(`Modal ${modalId} no encontrado`);
    }
}

// FUNCIÓN: Cerrar modal
function cerrarModal(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) {
        modal.classList.add('hidden');
        modal.classList.remove('flex');
        
        // Limpiar formulario
        const form = document.getElementById('form-asignar-especialidad');
        if (form) {
            const observaciones = document.getElementById('observaciones_asignar');
            if (observaciones) observaciones.value = '';
            
            const especialidad = document.getElementById('esp_codigo_asignar');
            if (especialidad) especialidad.value = '';
        }
    }
}

// FUNCIÓN: Llenar datos del paciente en el modal
function llenarDatosModalPaciente() {
    
    // Obtener datos del paciente desde los campos del formulario
    const nombre1 = document.querySelector('input[name="pac_nombre1"]')?.value || '';
    const apellido1 = document.querySelector('input[name="pac_apellido1"]')?.value || '';
    const nombreCompleto = `${nombre1} ${apellido1}`.trim();
    
    // Buscar cédula en diferentes campos posibles
    let cedula = document.querySelector('input[name="pac_cedula"]')?.value ||
                document.querySelector('#estab_historia_clinica')?.value ||
                document.querySelector('input[name="historia_clinica"]')?.value ||
                'No registrada';
    
    // Obtener triaje
    const triajeSelect = document.querySelector('select[name="cv_triaje_color"]');
    const triaje = triajeSelect?.value || triajeSelect?.selectedOptions[0]?.text || 'Sin triaje';
    
    // Obtener código de atención
    const ate_codigo = document.querySelector('input[name="ate_codigo"]')?.value;
    
    // Llenar campos del modal
    const nombreModal = document.getElementById('paciente-nombre-modal');
    const cedulaModal = document.getElementById('paciente-cedula-modal');
    const triajeModal = document.getElementById('paciente-triaje-modal');
    const ateCodigoInput = document.getElementById('ate_codigo_asignar');
    
    if (nombreModal) nombreModal.textContent = nombreCompleto || 'Paciente sin nombre';
    if (cedulaModal) cedulaModal.textContent = cedula;
    if (triajeModal) triajeModal.innerHTML = determinarColorTriaje(triaje);
    if (ateCodigoInput) ateCodigoInput.value = ate_codigo || '';
    
    
}

// FUNCIÓN: Determinar color de triaje
function determinarColorTriaje(color) {
    if (!color || color === 'Sin triaje' || color === '') {
        return '<span class="inline-block bg-gray-100 text-gray-800 px-2 py-1 rounded-full text-xs font-semibold">Sin triaje</span>';
    }
    
    const colores = {
        'ROJO': { class: 'bg-red-100 text-red-800', text: 'ROJO' },
        'NARANJA': { class: 'bg-orange-100 text-orange-800', text: 'NARANJA' },
        'AMARILLO': { class: 'bg-yellow-100 text-yellow-800', text: 'AMARILLO' },
        'VERDE': { class: 'bg-green-100 text-green-800', text: 'VERDE' },
        'AZUL': { class: 'bg-blue-100 text-blue-800', text: 'AZUL' },
    };
    
    const colorConfig = colores[color.toUpperCase()] || { 
        class: 'bg-gray-100 text-gray-800', 
        text: color 
    };
    
    return `<span class="inline-block ${colorConfig.class} px-2 py-1 rounded-full text-xs font-semibold">${colorConfig.text}</span>`;
}

// FUNCIÓN: Cargar especialidades en el modal
function cargarEspecialidadesModal() {
    
    const select = document.getElementById('esp_codigo_asignar');
    if (!select) {
        console.error('Select esp_codigo_asignar no encontrado');
        return;
    }
    
    // Mostrar indicador de carga
    select.innerHTML = '<option value="">Cargando especialidades...</option>';
    select.disabled = true;
    
    fetch(window.base_url + 'medicos/obtenerEspecialidades')
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP ${response.status}: ${response.statusText}`);
            }
            return response.json();
        })
        .then(data => {
            
            // Restablecer select
            select.disabled = false;
            select.innerHTML = '<option value="">Seleccione una especialidad...</option>';
            
            if (data.success && data.especialidades && Array.isArray(data.especialidades)) {
                // Guardar especialidades globalmente
                window.especialidadesDisponibles = data.especialidades;
                
                data.especialidades.forEach(esp => {
                    const option = document.createElement('option');
                    option.value = esp.esp_codigo;
                    option.textContent = esp.esp_nombre;
                    select.appendChild(option);
                });
                
            } else {
                select.innerHTML = '<option value="">No hay especialidades disponibles</option>';
                console.warn('No se encontraron especialidades:', data);
            }
        })
        .catch(error => {
            console.error('Error cargando especialidades:', error);
            
            // Habilitar select y mostrar error
            select.disabled = false;
            select.innerHTML = '<option value="">Error al cargar especialidades</option>';
            
            // Fallback con especialidades básicas
            const especialidadesFallback = [
                { esp_codigo: 1, esp_nombre: 'Críticos' },
                { esp_codigo: 2, esp_nombre: 'Púrpura' },
                { esp_codigo: 3, esp_nombre: 'Clínica' },
                { esp_codigo: 4, esp_nombre: 'Aislamiento' },
                { esp_codigo: 5, esp_nombre: 'Observación' },
                { esp_codigo: 6, esp_nombre: 'Ginecología' },
                { esp_codigo: 7, esp_nombre: 'Traumatología' }
            ];
            
            setTimeout(() => {
                select.innerHTML = '<option value="">Seleccione una especialidad...</option>';
                especialidadesFallback.forEach(esp => {
                    const option = document.createElement('option');
                    option.value = esp.esp_codigo;
                    option.textContent = esp.esp_nombre;
                    select.appendChild(option);
                });
            }, 2000);
        });
}

// FUNCIÓN: Confirmar asignación
function confirmarAsignacion() {
    
    const especialidad = document.getElementById('esp_codigo_asignar')?.value;
    const observaciones = document.getElementById('observaciones_asignar')?.value || '';
    const ate_codigo = document.getElementById('ate_codigo_asignar')?.value;
    
    
    // Validaciones
    if (!especialidad || especialidad === '') {
        Swal.fire({
            icon: 'warning',
            title: 'Campo requerido',
            text: 'Debe seleccionar una especialidad',
            confirmButtonText: 'Aceptar'
        }).then(() => {
            document.getElementById('esp_codigo_asignar').focus();
        });
        return;
    }

    if (!ate_codigo || ate_codigo === '') {
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: 'No se encontró el código de atención',
            confirmButtonText: 'Aceptar'
        });
        console.error('Código de atención vacío');
        return;
    }
    
    // Deshabilitar botón mientras se procesa
    const btnConfirmar = document.querySelector('button[onclick="confirmarAsignacion()"]');
    if (btnConfirmar) {
        btnConfirmar.disabled = true;
        btnConfirmar.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i> Enviando...';
    }
    
    // Preparar datos
    const formData = new URLSearchParams();
    formData.append('ate_codigo', ate_codigo);
    formData.append('esp_codigo', especialidad);
    formData.append('observaciones', observaciones);
    
    fetch(window.base_url + 'medicos/asignarAEspecialidad', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: formData
    })
    .then(response => {
        if (!response.ok) {
            throw new Error(`HTTP ${response.status}: ${response.statusText}`);
        }
        return response.json();
    })
    .then(data => {

        if (data.success) {
            Swal.fire({
                icon: 'success',
                title: 'Éxito',
                text: '¡Paciente enviado correctamente a especialidad!',
                confirmButtonText: 'Aceptar'
            }).then(() => {
                // Cerrar modal
                cerrarModal('modalAsignarEspecialidad');

                // Redirigir a la lista
                window.location.href = window.base_url + 'medicos/lista';
            });
        } else {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: data.error || data.message || 'Error desconocido',
                confirmButtonText: 'Aceptar'
            });
            console.error('Error en respuesta:', data);
        }
    })
    .catch(error => {
        console.error('Error en la petición:', error);
        Swal.fire({
            icon: 'error',
            title: 'Error de conexión',
            text: error.message,
            confirmButtonText: 'Aceptar'
        });
    })
    .finally(() => {
        // Rehabilitar botón
        if (btnConfirmar) {
            btnConfirmar.disabled = false;
            btnConfirmar.innerHTML = '<i class="fas fa-paper-plane mr-2"></i> Asignar a Especialidad';
        }
    });
}

// FUNCIÓN: Validar formulario antes de enviar
function validarFormularioAsignacion() {
    const especialidad = document.getElementById('esp_codigo_asignar')?.value;
    const ate_codigo = document.getElementById('ate_codigo_asignar')?.value;
    
    let errores = [];
    
    if (!ate_codigo) {
        errores.push('Código de atención requerido');
    }
    
    if (!especialidad) {
        errores.push('Especialidad requerida');
    }
    
    return {
        valido: errores.length === 0,
        errores: errores
    };
}

// FUNCIÓN GLOBAL para compatibilidad con PHP (si se necesita)
function confirmarEnviarEspecialista() {
    // Llenar datos del paciente en el modal
    llenarDatosModalPaciente();
    
    // Abrir modal
    abrirModal('modalAsignarEspecialidad');
}

// Inicializar cuando el DOM esté listo
document.addEventListener('DOMContentLoaded', function() {
    inicializarModalEspecialidades();
});

// Exportar funciones para uso externo si es necesario
window.modalEspecialidades = {
    inicializar: inicializarModalEspecialidades,
    abrir: abrirModal,
    cerrar: cerrarModal,
    confirmar: confirmarAsignacion,
    cargarEspecialidades: cargarEspecialidadesModal
};

// Funciones globales para compatibilidad
window.confirmarEnviarEspecialista = confirmarEnviarEspecialista;
window.confirmarAsignacion = confirmarAsignacion;
window.cerrarModal = cerrarModal;