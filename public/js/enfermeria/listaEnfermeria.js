$(document).ready(function() {
    
    // VERIFICAR QUE ESTAMOS EN CONTEXTO DE ENFERMERÍA
    if (typeof window.contextoEnfermeria === 'undefined' || !window.contextoEnfermeria) {
        return;
    }
    
    // VERIFICAR QUE NO ESTAMOS EN CONTEXTO MÉDICO
    if (typeof window.contextoMedico !== 'undefined' && window.contextoMedico) {
        return;
    }
    
    // Verificar que las URLs están disponibles
    if (typeof window.APP_URLS_ENFERMERIA === 'undefined') {
        console.error('❌ window.APP_URLS_ENFERMERIA no está definido');
        return;
    }
    
    // Validar que tenemos las URLs específicas de enfermería
    if (!window.APP_URLS_ENFERMERIA.obtenerPacientes || !window.APP_URLS_ENFERMERIA.cargarSeccionG) {
        console.error('❌ URLs de enfermería incompletas');
        return;
    }
    
    
    // FUNCIÓN PARA CARGAR PACIENTES (REUTILIZABLE)
    function cargarPacientesEnfermeria() {
        
        // Actualizar estadísticas
        actualizarUltimaActualizacion();
        
        // Mostrar indicador de carga en el tbody
        $('#tbody-pacientes').html(`
            <tr>
                <td colspan="5" class="px-6 py-8 text-center">
                    <div class="flex flex-col items-center justify-center space-y-3">
                        <svg class="animate-spin w-8 h-8 text-blue-600" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        <p class="text-gray-600 font-medium">Cargando pacientes...</p>
                        <p class="text-sm text-gray-500">Obteniendo datos del servidor</p>
                    </div>
                </td>
            </tr>
        `);
        
        // Hacer la solicitud AJAX para obtener los pacientes
        $.ajax({
            url: window.APP_URLS_ENFERMERIA.obtenerPacientes,
            method: "GET",
            dataType: "json",
            cache: false,
            timeout: 15000,
            success: function(data) {
                
                // Limpiar la tabla antes de llenarla
                $('#tbody-pacientes').empty();
                
                // Actualizar contador de pacientes
                const totalPacientes = Array.isArray(data) ? data.length : 0;
                $('#total-pacientes').text(totalPacientes);
                
                // Recorrer los pacientes y agregar las filas en la tabla
                if (data.length > 0) {
                    data.forEach(function(paciente, index) {
                        const fila = crearFilaPaciente(paciente, index);
                        $('#tbody-pacientes').append(fila);
                    });
                    
                } else {
                    $('#tbody-pacientes').html(`
                        <tr>
                            <td colspan="5" class="px-6 py-8 text-center">
                                <div class="flex flex-col items-center justify-center space-y-3">
                                    <div class="w-16 h-16 bg-green-100 rounded-full flex items-center justify-center">
                                        <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                        </svg>
                                    </div>
                                    <div class="text-center">
                                        <h3 class="text-lg font-medium text-gray-900">¡Excelente trabajo!</h3>
                                        <p class="text-gray-600 mt-1">No hay pacientes pendientes para enfermería</p>
                                        <p class="text-sm text-gray-500 mt-2">Todos los pacientes han sido procesados correctamente</p>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    `);
                }
            },
            error: function(xhr, status, error) {
                console.error('❌ Error al obtener pacientes de enfermería:', {
                    status: status,
                    error: error,
                    responseText: xhr.responseText
                });
                
                $('#tbody-pacientes').html(`
                    <tr>
                        <td colspan="5" class="px-6 py-8 text-center">
                            <div class="flex flex-col items-center justify-center space-y-3">
                                <div class="w-16 h-16 bg-red-100 rounded-full flex items-center justify-center">
                                    <svg class="w-8 h-8 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                                    </svg>
                                </div>
                                <div class="text-center">
                                    <h3 class="text-lg font-medium text-red-900">Error al cargar datos</h3>
                                    <p class="text-red-600 mt-1">No se pudieron obtener los datos de pacientes</p>
                                    <p class="text-sm text-red-500 mt-2">Error: ${error}</p>
                                    <button 
                                        class="mt-3 inline-flex items-center px-3 py-2 border border-red-300 shadow-sm text-sm leading-4 font-medium rounded-md text-red-700 bg-red-50 hover:bg-red-100 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 transition-colors"
                                        onclick="cargarPacientesEnfermeria()"
                                    >
                                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                                        </svg>
                                        Reintentar
                                    </button>
                                </div>
                            </div>
                        </td>
                    </tr>
                `);
                
                // Actualizar contador de pacientes con error
                $('#total-pacientes').text('Error');
            }
        });
    }
    
    // FUNCIÓN PARA CREAR FILA DE PACIENTE
    function crearFilaPaciente(paciente, index) {
        const nombreCompleto = `${paciente.pac_nombres || ''} ${paciente.pac_apellidos || ''}`.trim();
        const cedula = paciente.pac_cedula || 'Sin cédula';
        const fecha = paciente.ate_fecha || 'No registrada';
        const hora = paciente.ate_hora || 'No registrada';
        
        return `
            <tr class="hover:bg-gray-50 transition-colors duration-200">
                <td class="px-6 py-4 whitespace-nowrap">
                    <div class="flex items-center">
                        <div class="flex-shrink-0 h-10 w-10">
                            <div class="h-10 w-10 rounded-full bg-gradient-to-r from-blue-500 to-purple-600 flex items-center justify-center">
                                <span class="text-white font-medium text-sm">${nombreCompleto.charAt(0) || 'P'}</span>
                            </div>
                        </div>
                        <div class="ml-4">
                            <div class="text-sm font-medium text-gray-900">${nombreCompleto || 'Sin nombre'}</div>
                            <div class="text-sm text-gray-500">Paciente #${index + 1}</div>
                        </div>
                    </div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                    <div class="flex items-center">
                        <svg class="w-4 h-4 text-gray-400 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V8a2 2 0 00-2-2h-5m-4 0V4a2 2 0 114 0v2m-4 0a2 2 0 104 0m-4 0v2m4-6v6m6-6v6"></path>
                        </svg>
                        <span class="text-sm text-gray-900 font-mono">${cedula}</span>
                    </div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                    <div class="flex items-center">
                        <svg class="w-4 h-4 text-gray-400 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3a2 2 0 012-2h4a2 2 0 012 2v4m-6 0h6a2 2 0 012 2v9a2 2 0 01-2 2H8a2 2 0 01-2-2v-9a2 2 0 012-2z"></path>
                        </svg>
                        <span class="text-sm text-gray-900">${fecha}</span>
                    </div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                    <div class="flex items-center">
                        <svg class="w-4 h-4 text-gray-400 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        <span class="text-sm text-gray-900">${hora}</span>
                    </div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                    <button 
                        class="btn-cargar-paciente inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-gradient-to-r from-blue-600 to-purple-600 hover:from-blue-700 hover:to-purple-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-all duration-200 transform hover:scale-105"
                        data-ate-codigo="${paciente.ate_codigo || ''}"
                        data-pac-nombres="${paciente.pac_nombres || ''}"
                        data-pac-apellidos="${paciente.pac_apellidos || ''}"
                        data-pac-cedula="${paciente.pac_cedula || ''}"
                    >
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                        <span>Cargar Sección G</span>
                    </button>
                </td>
            </tr>
        `;
    }
    
    // FUNCIÓN PARA ACTUALIZAR ÚLTIMA ACTUALIZACIÓN
    function actualizarUltimaActualizacion() {
        const ahora = new Date();
        const horaFormateada = ahora.toLocaleTimeString('es-ES', {
            hour: '2-digit',
            minute: '2-digit',
            second: '2-digit'
        });
        $('#ultima-actualizacion').text(horaFormateada);
    }
    
    // FUNCIÓN PARA MOSTRAR LOADING EN BOTÓN DE RECARGAR
    function mostrarLoadingRecargar() {
        const $btnRecargar = $('#btn-recargar');
        const $btnText = $('#btn-text');
        
        $btnRecargar.prop('disabled', true);
        $btnRecargar.removeClass('hover:bg-blue-700').addClass('bg-blue-400 cursor-not-allowed');
        $btnText.html(`
            <svg class="animate-spin w-4 h-4 mr-2" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            Recargando...
        `);
    }
    
    // FUNCIÓN PARA OCULTAR LOADING EN BOTÓN DE RECARGAR
    function ocultarLoadingRecargar() {
        const $btnRecargar = $('#btn-recargar');
        const $btnText = $('#btn-text');
        
        $btnRecargar.prop('disabled', false);
        $btnRecargar.removeClass('bg-blue-400 cursor-not-allowed').addClass('hover:bg-blue-700');
        $btnText.html(`
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
            </svg>
            Recargar
        `);
    }
    
    // CARGAR PACIENTES AL INICIO
    cargarPacientesEnfermeria();

    // Event listener para botón de recargar
    $(document).on('click', '#btn-recargar', function(e) {
        e.preventDefault();
        
        // Prevenir múltiples clics
        if ($(this).prop('disabled')) {
            return;
        }
        
        mostrarLoadingRecargar();
        
        // Ejecutar la recarga
        setTimeout(() => {
            cargarPacientesEnfermeria();
            setTimeout(() => {
                ocultarLoadingRecargar();
            }, 800);
        }, 300);
    });

    // Event delegation para manejar clics en botones "Cargar Sección G"
    $(document).on('click', '.btn-cargar-paciente', function(e) {
        e.preventDefault();
        
        const atecodigo = $(this).data('ate-codigo');
        
        if (!atecodigo) {
            mostrarNotificacion('Error: No se pudo obtener el código de atención', 'error');
            return;
        }


        // Mostrar indicador de carga en el botón
        const $boton = $(this);
        const textoOriginal = $boton.html();
        
        $boton.html(`
            <svg class="animate-spin w-4 h-4 mr-2" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            Cargando...
        `);
        $boton.prop('disabled', true);
        $boton.removeClass('hover:from-blue-700 hover:to-purple-700 hover:scale-105').addClass('opacity-75 cursor-not-allowed');

        // Redirigir al formulario después de un breve delay
        setTimeout(() => {
            window.location.href = window.APP_URLS_ENFERMERIA.cargarSeccionG + atecodigo;
        }, 800);
    });
    
    // FUNCIÓN PARA MOSTRAR NOTIFICACIONES
    function mostrarNotificacion(mensaje, tipo = 'info') {
        const colores = {
            'success': 'bg-green-50 border-green-200 text-green-800',
            'error': 'bg-red-50 border-red-200 text-red-800',
            'warning': 'bg-yellow-50 border-yellow-200 text-yellow-800',
            'info': 'bg-blue-50 border-blue-200 text-blue-800'
        };
        
        const iconos = {
            'success': `<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>`,
            'error': `<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"></path>`,
            'warning': `<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"></path>`,
            'info': `<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>`
        };
        
        const notificacion = $(`
            <div class="fixed top-4 right-4 max-w-sm w-full ${colores[tipo]} border rounded-lg shadow-lg z-50 transform translate-x-full transition-transform duration-300" id="notificacion-${Date.now()}">
                <div class="p-4">
                    <div class="flex items-start">
                        <div class="flex-shrink-0">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                ${iconos[tipo]}
                            </svg>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm font-medium">${mensaje}</p>
                        </div>
                        <div class="ml-auto pl-3">
                            <button class="inline-flex text-gray-400 hover:text-gray-600 focus:outline-none" onclick="$(this).closest('.fixed').remove()">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        `);
        
        $('body').append(notificacion);
        
        // Animar entrada
        setTimeout(() => {
            notificacion.removeClass('translate-x-full');
        }, 100);
        
        // Auto-ocultar después de 5 segundos
        setTimeout(() => {
            notificacion.addClass('translate-x-full');
            setTimeout(() => {
                notificacion.remove();
            }, 300);
        }, 5000);
    }
    
    // FUNCIÓN REUTILIZABLE PARA REFRESCAR LA TABLA
    window.refrescarTablaPacientesEnfermeria = function() {
        cargarPacientesEnfermeria();
    };
    
    // Mantener compatibilidad con función genérica pero solo en contexto de enfermería
    if (typeof window.refrescarTablaPacientes === 'undefined') {
        window.refrescarTablaPacientes = window.refrescarTablaPacientesEnfermeria;
    }
    
    // AUTO-REFRESCAR CADA 30 SEGUNDOS
    let intervalId = setInterval(function() {
        cargarPacientesEnfermeria();
    }, 30000); // 30 segundos
    
    // LIMPIAR INTERVAL AL SALIR DE LA PÁGINA
    $(window).on('beforeunload', function() {
        if (intervalId) {
            clearInterval(intervalId);
        }
    });
    
});