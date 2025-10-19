<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
// Mostrar login al inicio
$routes->get('/', 'LoginController::index');

// Login
$routes->get('/login', 'LoginController::index');
$routes->post('/login/ingresar', 'LoginController::ingresar');
$routes->get('/logout', 'LoginController::logout');


// Rutas API para obtener cantones y parroquias
$routes->get('api/ubicacion/cantones/(:segment)', 'Api\UbicacionController::obtenerCantones/$1');
$routes->get('api/ubicacion/parroquias/(:segment)', 'Api\UbicacionController::obtenerParroquias/$1');

// Solo Admisiones
$routes->group('admisiones', ['namespace' => 'App\Controllers\Admisiones'], function ($routes) {
    // Vista principal de admisiones
    $routes->get('/', 'DatosAdmisionesController::index');

    //Busqueda de la bdd local bd_emergencia
    $routes->post('formulario/buscarPorCedula', 'PacienteBusquedaController::buscarPorCedula');
    $routes->post('formulario/buscarPorApellido', 'PacienteBusquedaController::buscarPorApellido');
    $routes->post('formulario/buscarPorHistoria', 'PacienteBusquedaController::buscarPorHistoria');
    //Autocompletado con JQuery
    $routes->get('formulario/autocompletar-apellidos', 'PacienteBusquedaController::autocompletarApellidos');

    //Guardar datos en la bdd local bd_emergencia
    $routes->post('formulario/guardarAdmisiones', 'AdmisionController::guardarAdmisiones');
    //Fin Admisiones

    // Hospital
    //Busqueda de la bdd del hospital
    $routes->post('busqueda-hospital/cedula', 'BusquedaHospitalController::buscarPorCedula');
    $routes->post('busqueda-hospital/apellido', 'BusquedaHospitalController::buscarPorApellido');
    $routes->post('busqueda-hospital/historia', 'BusquedaHospitalController::buscarPorHistoria');
    //Autocompletado hospital apellidos
    $routes->get('busqueda-hospital/autocompletar-apellidos', 'BusquedaHospitalController::autocompletarApellidos');
    //Fin Hospital
});

// Solo Enfermería
$routes->group('enfermeria', ['namespace' => 'App\Controllers\Enfermeria'], function ($routes) {
    // Rutas existentes
    $routes->get('/', 'ListaEnfermeriaController::listaEnfermeria');
    $routes->get('lista', 'ListaEnfermeriaController::listaEnfermeria');
    $routes->get('obtenerPacientes', 'ListaEnfermeriaController::obtenerPacientes');
    $routes->get('formulario/(:num)', 'DatosEnfermeriaController::formulario/$1');
    $routes->get('obtenerDatosPaciente/(:num)', 'DatosEnfermeriaController::obtenerDatosPaciente/$1');

    $routes->post('guardarEnfermeria', 'EnfermeriaController::guardarEnfermeria');
});

// Solo Médico
$routes->group('medicos', ['namespace' => 'App\Controllers\Medicos'], function ($routes) {
    // Rutas existentes que ya funcionan
    $routes->get('/', 'ListaMedicosController::listaMedicos');
    $routes->get('lista', 'ListaMedicosController::listaMedicos');
    $routes->get('obtenerPacientes', 'ListaMedicosController::obtenerPacientes');
    $routes->get('formulario/(:num)', 'DatosMedicosController::formulario/$1');
    $routes->get('obtenerDatosPaciente/(:num)', 'DatosMedicosController::obtenerDatosPaciente/$1');

    // Rutas de formulario completo
    $routes->get('formularioCompleto', 'MedicoCompletoController::index');
    $routes->post('guardarFormularioCompleto', 'MedicoCompletoController::guardarFormularioCompleto');

    // Ruta existente de guardar
    $routes->post('guardarMedico', 'MedicosController::guardarMedico');

    // Rutas para funcionalidad de triaje consolidada
    $routes->get('obtenerPacientesTriaje', 'MedicosController::obtenerPacientesTriaje');
    $routes->post('asignarAEspecialidad', 'MedicosController::asignarAEspecialidad');
    $routes->post('guardarSeccionesIniciales', 'MedicosController::guardarSeccionesIniciales');
    $routes->get('tomarAtencionRapida/(:num)', 'MedicosController::tomarAtencionRapida/$1');
    $routes->get('obtenerEstadisticasTriaje', 'MedicosController::obtenerEstadisticasTriaje');
    $routes->get('obtenerEspecialidades', 'MedicosController::obtenerEspecialidades');

    // ===== RUTAS PARA EVOLUCIÓN Y PRESCRIPCIONES - MÉDICOS =====
    $routes->post('modalEvolucionPrescripciones', 'ModalEvolucionPrescripcionesController::modalEvolucionPrescripciones');
    $routes->post('guardarEvolucionPrescripciones', 'ModalEvolucionPrescripcionesController::guardarEvolucionPrescripciones');
    $routes->post('obtenerEvolucionesConUsuario', 'ModalEvolucionPrescripcionesController::obtenerEvolucionesConUsuario');
    $routes->post('contarEvolucionPrescripciones', 'ModalEvolucionPrescripcionesController::contarEvolucionPrescripciones');

});

// RUTAS PARA ESPECIALIDADES MÉDICAS (actualizar sección existente)
$routes->group('especialidades', ['namespace' => 'App\Controllers\Especialidades'], function ($routes) {

    // ===== VISTA PRINCIPAL Y LISTADOS =====
    $routes->get('/', 'ListaEspecialidadesController::index');
    $routes->get('lista', 'ListaEspecialidadesController::index');
    $routes->get('obtenerPacientesEspecialidad/(:num)', 'ListaEspecialidadesController::obtenerPacientesEspecialidad/$1');
    $routes->get('obtenerPacientesEnfermeria/(:num)', 'ListaEspecialidadesController::obtenerPacientesEnfermeria/$1');
    $routes->get('obtenerMedicosEspecialidad/(:num)', 'ListaEspecialidadesController::obtenerMedicosEspecialidad/$1');
    $routes->get('verificarDisponibilidad/(:num)', 'EspecialidadController::verificarDisponibilidad/$1');
    $routes->post('tomarAtencion/(:num)', 'EspecialidadController::tomarAtencion/$1');
    $routes->post('validarContrasena', 'EspecialidadController::validarContrasena');
    $routes->post('tomarAtencionConCredenciales', 'EspecialidadController::tomarAtencionConCredenciales');
    $routes->get('formulario/(:num)', 'DatosEspecialidadController::formulario/$1');
    $routes->get('obtenerDatosPaciente/(:num)', 'DatosEspecialidadController::obtenerDatosPaciente/$1');
    $routes->post('guardarFormulario', 'EspecialidadController::guardarFormulario');

    // ===== RUTAS ENFERMERÍA ESPECIALIDAD =====
    $routes->post('enfermeria/recibirDatos', 'EnfermeriaEspecialidadController::recibirDatosEspecialista');
    $routes->post('enviarAEnfermeria', 'EnfermeriaEspecialidadController::enviarAEnfermeria');
    $routes->post('tomarAtencionEnfermeria', 'EnfermeriaEspecialidadController::tomarAtencionEnfermeria');
    $routes->post('validarAccesoEnfermeria', 'EnfermeriaEspecialidadController::validarAccesoEnfermeria');

    // ===== RUTAS PARA PROCESOS PARCIALES =====
    $routes->post('guardarProcesoParcial', 'ProcesoEspecialidadController::guardarProcesoParcial');
    $routes->post('verificarProcesoParcial', 'ProcesoEspecialidadController::verificarProcesoParcial');
    $routes->post('validarContinuarProceso', 'ProcesoEspecialidadController::validarContinuarProceso');
    $routes->post('continuarProcesoConValidacion', 'ProcesoEspecialidadController::continuarProcesoConValidacion');
    $routes->get('continuar-proceso/(:num)', 'ProcesoEspecialidadController::continuarProceso/$1');

    // ===== RUTAS PARA OBSERVACIÓN =====
    $routes->post('observacion/enviarAObservacion', 'ObservacionController::enviarAObservacion');
    $routes->get('obtenerPacientes', 'ObservacionController::obtenerPacientesObservacion');
    $routes->post('observacion/tomarAtencionObservacion', 'ObservacionController::tomarAtencionObservacion');
    $routes->post('observacion/enviarAObservacionConDatos', 'ObservacionController::enviarAObservacionConDatos');

    // ===== RUTAS PARA EVOLUCIÓN Y PRESCRIPCIONES =====
    $routes->post('modalEvolucionPrescripciones', 'ModalEvolucionPrescripcionesController::modalEvolucionPrescripciones');
    $routes->post('guardarEvolucionPrescripciones', 'ModalEvolucionPrescripcionesController::guardarEvolucionPrescripciones');
    $routes->post('obtenerEvolucionesConUsuario', 'ModalEvolucionPrescripcionesController::obtenerEvolucionesConUsuario');
    $routes->post('contarEvolucionPrescripciones', 'ModalEvolucionPrescripcionesController::contarEvolucionPrescripciones');
    $routes->get('obtenerEvolucionPrescripciones/(:segment)', 'ModalEvolucionPrescripcionesController::obtenerEvolucionPrescripciones/$1');
    $routes->post('marcarAdministrado', 'ModalEvolucionPrescripcionesController::marcarAdministrado');
    $routes->get('obtenerEstadisticasEvolucion/(:segment)', 'ModalEvolucionPrescripcionesController::obtenerEstadisticas/$1');
    $routes->post('buscarEvolucionPorTexto', 'ModalEvolucionPrescripcionesController::buscarPorTexto');
    $routes->get('obtenerPendientesAdministracion/(:segment)', 'ModalEvolucionPrescripcionesController::obtenerPendientesAdministracion/$1');
    $routes->post('generarReporteEvolucion', 'ModalEvolucionPrescripcionesController::generarReporte');

    // ===== RUTAS PARA REPORTES DE ESPECIALIDADES ===== 
    // Autenticación para acceso a reportes
    $routes->post('reportes/autenticar', 'ReportesController::autenticar');

    // Dashboard principal de reportes (requiere autenticación)
    $routes->get('reportes/dashboard', 'ReportesController::dashboard');

    $routes->post('reportes/guardarDiagnosticos', 'ReportesController::guardarDiagnosticos');

    // Obtener datos para la tabla de reportes
    $routes->get('reportes/obtenerDatosAlternativo', 'ReportesController::obtenerDatosAlternativo');
    // Obtener especialidades para filtros
    $routes->get('reportes/obtenerEspecialidades', 'ReportesController::obtenerEspecialidades');

    // Cerrar sesión de reportes
    $routes->get('reportes/cerrarSesion', 'ReportesController::cerrarSesion');

    // ===== RUTAS EXISTENTES RESTANTES =====
    $routes->get('historial/(:num)', 'ListaEspecialidadesController::historialAreaAtencion/$1');
    $routes->get('obtenerEspecialidades', 'ListaEspecialidadesController::obtenerEspecialidades');
    $routes->post('cambiarEstado/(:num)', 'EspecialidadController::cambiarEstado/$1');
});

//RUTAS DE ADMINISTRADOR
$routes->group('administrador', ['namespace' => 'App\Controllers\Administrador'], function ($routes) {

    // ===== DASHBOARD PRINCIPAL =====
    $routes->get('/', 'PanelAdminController::index');
    $routes->post('panel/filtrarUsuarios', 'PanelAdminController::filtrarUsuarios');
    $routes->post('panel/obtenerEstadisticas', 'PanelAdminController::obtenerEstadisticas');

    // ===== ESTADÍSTICAS DE ATENCIONES =====
    $routes->get('estadisticas/obtenerEstadisticas', 'EstadisticasController::obtenerEstadisticas');
    $routes->get('estadisticas/obtenerTendencias', 'EstadisticasController::obtenerTendencias');
    $routes->get('estadisticas/obtenerAtencionesPorHora', 'EstadisticasController::obtenerAtencionesPorHora');

    // ===== GESTIÓN DE USUARIOS (EXISTENTES) =====
    $routes->get('usuarios', 'DatosUsuarioController::index');
    $routes->get('usuarios/crear', 'DatosUsuarioController::crearUsuario');
    $routes->get('usuarios/editar/(:num)', 'DatosUsuarioController::editarUsuario/$1');
    $routes->get('usuarios/eliminados', 'DatosUsuarioController::usuariosEliminados');
    $routes->post('usuarios/guardar', 'DatosUsuarioController::insertarUsuario');
    $routes->post('usuarios/actualizar/(:num)', 'DatosUsuarioController::actualizarUsuario/$1');

    // ===== GESTIÓN DE USUARIOS =====
    // Gestión de estado de usuarios
    $routes->post('usuarios/desactivar/(:num)', 'DatosUsuarioController::desactivarUsuario/$1');
    $routes->post('usuarios/reactivar/(:num)', 'DatosUsuarioController::reactivarUsuario/$1');

    // ===== CONTROL DE MODIFICACIONES =====
    $routes->get('panel/showPatients', 'PanelAdminController::showPatients');
    $routes->get('modificaciones/obtenerVistaModificaciones', 'ModificacionesController::obtenerVistaModificaciones');
    $routes->get('modificaciones/obtenerDatosModificaciones', 'ModificacionesController::obtenerDatosModificaciones');
    $routes->get('modificaciones/obtenerEstadisticas', 'ModificacionesController::obtenerEstadisticas');
    $routes->post('modificaciones/habilitarModificacion', 'ModificacionesController::habilitarModificacion');
    $routes->get('modificaciones/obtenerHistorial/(:segment)', 'ModificacionesController::obtenerHistorial/$1');
    $routes->get('modificaciones/verificarEstado', 'ModificacionesController::verificarEstado');

    // ===== HISTORIAL DE PACIENTES =====
    $routes->get('buscar-historial', 'HistorialController::index');
    $routes->get('historial/ajaxPacientes', 'HistorialController::ajaxPacientes');

    // ===== RUTAS PARA EL FORMULARIO COMPLETO =====
    $routes->get('formulario/dual/(:segment)', 'DatosPacientesController::vistaDual/$1');

    // ===== RUTAS PARA BÚSQUEDA POR FECHA =====
    $routes->post('datos-pacientes/listar-por-fecha', 'BusquedaCompletaController::listarPacientesPorFecha');
    $routes->post('datos-pacientes/buscar-por-fecha', 'BusquedaCompletaController::buscarPorFecha');
    $routes->post('datos-pacientes/buscar-evolucion-por-fecha', 'DatosPacientesController::buscarEvolucionPorFecha');

    // ===== REPORTES ADMINISTRATIVOS =====
    $routes->get('reportes/obtenerVistaReportes', 'ReportesController::obtenerVistaReportes');
    $routes->get('reportes/obtenerDatos', 'ReportesController::obtenerDatos');
    $routes->get('reportes/obtenerEstadisticasEmbarazos', 'ReportesController::obtenerEstadisticasEmbarazos');

    // ===== GENERACIÓN DE PDFs ORGANIZADOS POR MES =====
    $routes->post('pdf/guardar-005', 'GeneradorPDFController::guardarPDF005');
    $routes->post('pdf/guardar-008', 'GeneradorPDFController::guardarPDF008');
    $routes->get('pdf/listar/(:segment)', 'GeneradorPDFController::listarPDFsPorMes/$1');

    // ===== GESTIÓN DE RESPALDOS DE BASE DE DATOS =====
    $routes->get('respaldos', 'RespaldoController::index');
    $routes->post('respaldos/crear-manual', 'RespaldoController::crearRespaldoManual');
    $routes->get('respaldos/listar', 'RespaldoController::listarRespaldos');
    $routes->post('respaldos/restaurar', 'RespaldoController::restaurarRespaldo');
    $routes->post('respaldos/limpiar-antiguos', 'RespaldoController::limpiarRespaldosAntiguos');
    $routes->get('respaldos/estadisticas', 'RespaldoController::obtenerEstadisticas');
    $routes->get('respaldos/descargar/(:any)', 'RespaldoController::descargarRespaldo/$1');
});



// ===== ENDPOINT PARA MANTENER SESIÓN ACTIVA =====
$routes->post('ping-session', 'LoginController::pingSession');