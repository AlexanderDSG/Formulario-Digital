<?php

namespace App\Controllers\Especialidades;

use App\Controllers\BaseController;
use App\Models\Admision\GuardarSecciones\{
    TipoDocumentoModel, GeneroModel, SeguroModel, EstadoCivilModel,
    NacionalidadModel, EmpresaModel, EtniaModel, NivelEducacionModel,
    EstadoEducacionModel, LlegadaModel, EstablecimientoModel,
    NacionalidadIndigenaModel, PuebloIndigenaModel
};
use App\Models\Admision\EstablecimientoRegistroModel;
use App\Models\{PacienteModel, AtencionModel};
use App\Models\Enfemeria\ConstantesVitalesModel;
use App\Models\Administrador\{UsuarioModel, ModificacionesModel};
use App\Models\Especialidades\AreaAtencionModel;
use App\Models\Medicos\GuardarSecciones\CondicionLlegadaModel;

class DatosEspecialidadController extends BaseController
{
     // ==================== MÉTODO PRINCIPAL ====================

    public function formulario($are_codigo = null)
    {
        // Validaciones iniciales
        if (!session()->get('logged_in')) {
            return redirect()->to('/login');
        }

        $contexto = $this->determinarContextoAcceso();

        if (!$contexto['tiene_acceso']) {
            return redirect()->to('/especialidades/lista')->with('error', 'No tiene permisos para acceder a esta sección.');
        }

        if (!$are_codigo) {
            return redirect()->to(base_url('/especialidades/lista'))->with('error', 'Debe seleccionar un paciente de especialidad.');
        }

        // Obtener área de atención y validar
        $areaAtencionModel = new AreaAtencionModel();
        $areaAtencion = $areaAtencionModel->find($are_codigo);

        if (!$areaAtencion) {
            return redirect()->to('/especialidades/lista')->with('error', 'Área de atención no encontrada.');
        }

        $ate_codigo = $areaAtencion['ate_codigo'];
        $esObservacion = ($areaAtencion['esp_codigo'] == 5);

        // Obtener proceso existente y permisos
        $procesoParcialModel = new \App\Models\Especialidades\ProcesoParcialModel();
        $procesoExistente = $procesoParcialModel->obtenerProcesoPorAtencion($ate_codigo);
        $esContinuacionProceso = $procesoExistente && $procesoExistente['ppe_estado'] === 'EN_PROCESO';

        // Verificar permisos de acceso
        $permisoAcceso = $this->verificarPermisosAcceso($esObservacion, $ate_codigo, $esContinuacionProceso);

        if (!$permisoAcceso['acceso'] && !$esContinuacionProceso) {
            return redirect()->to('/especialidades/lista')->with('error', $permisoAcceso['motivo']);
        }

        // Inicializar datos base
        $data = $this->inicializarDatosProceso($esContinuacionProceso, $procesoExistente, $permisoAcceso, $ate_codigo);

        // Configurar contextos específicos
        $this->configurarContextoEnfermeria($data, $contexto, $ate_codigo);
        $this->configurarContextoObservacion($data, $esObservacion, $ate_codigo, $procesoExistente);

        // Verificar si formulario completado
        if (!($data['es_modificacion'] ?? false) && !($data['es_continuacion_proceso'] ?? false) && !$esObservacion) {
            $estadoFormulario = $this->verificarFormularioYaCompletado($ate_codigo, $are_codigo);

            if ($estadoFormulario['completado']) {
                return view('especialidades/formulario_completado', array_merge($data, $estadoFormulario, [
                    'formulario_completado' => true,
                    'are_codigo' => $are_codigo,
                    'ate_codigo' => $ate_codigo
                ]));
            }
        }

        // Cargar datos del formulario
        $datosModificacion = [
            'es_modificacion' => $data['es_modificacion'] ?? false,
            'motivo_modificacion' => $data['motivo_modificacion'] ?? '',
            'fecha_habilitacion' => $data['fecha_habilitacion'] ?? ''
        ];

        $data = array_merge($data, $this->cargarDatosFormularioEspecialidad($are_codigo));

        // Restaurar datos de modificación
        $data = array_merge($data, $datosModificacion);

        if (isset($data['error'])) {
            return redirect()->to(base_url('/especialidades/lista'))->with('error', $data['error']);
        }

        return view('especialidades/formulario', $data);
    }
    // ==================== CONFIGURACIONES DE MAPEO ====================

    private $pacienteMappingConfig = [
        'basic' => ['pac_his_cli', 'pac_cedula', 'pac_edad_valor', 'pac_edad_unidad'],
        'personal' => ['pac_grupo_prioritario', 'pac_grupo_sanguineo', 'pac_telefono_fijo', 'pac_telefono_celular', 'pac_fecha_nac', 'pac_lugar_nac', 'pac_ocupacion'],
        'codes' => ['tdoc_codigo' => 'tipo_documento', 'esc_codigo' => 'estado_civil', 'gen_codigo' => 'sexo', 'nac_codigo' => 'nacionalidad', 'gcu_codigo' => 'etnia', 'nedu_codigo' => 'nivel_educacion', 'eneduc_codigo' => 'estado_educacion', 'emp_codigo' => 'tipo_empresa', 'seg_codigo' => 'seguro', 'nac_ind_codigo' => 'nacionalidadIndigena', 'pue_ind_codigo' => 'puebloIndigena'],
        'address' => ['pac_direccion' => 'res_direccion', 'pac_provincias' => 'res_provincia', 'pac_cantones' => 'res_canton', 'pac_parroquias' => 'res_parroquia', 'pac_barrio' => 'res_barrio_sector', 'pac_calle_secundaria' => 'res_calle_secundaria', 'pac_referencia' => 'res_referencia'],
        'emergency' => ['pac_avisar_a' => 'contacto_emerg_nombre', 'pac_parentezco_avisar_a' => 'contacto_emerg_parentesco', 'pac_direccion_avisar' => 'contacto_emerg_direccion', 'pac_telefono_avisar_a' => 'contacto_emerg_telefono']
    ];

    private $atencionMappingConfig = ['lleg_codigo', 'ate_fuente_informacion', 'ate_ins_entrega_paciente', 'ate_telefono', 'ate_colores', 'ate_custodia_policial', 'ate_aliento_etilico'];

    private $constantesVitalesMappingConfig = [
        'con_sin_constantes' => 'cv_sin_vitales', 'con_presion_arterial' => 'cv_presion_arterial', 'con_pulso' => 'cv_pulso',
        'con_frec_respiratoria' => 'cv_frec_resp', 'con_pulsioximetria' => 'cv_pulsioximetria', 'con_perimetro_cefalico' => 'cv_perimetro_cefalico',
        'con_peso' => 'cv_peso', 'con_talla' => 'cv_talla', 'con_glucemia_capilar' => 'cv_glicemia',
        'con_reaccion_pupila_der' => 'cv_reaccion_pupilar_der', 'con_reaccion_pupila_izq' => 'cv_reaccion_pupilar_izq',
        'con_t_lleno_capilar' => 'cv_llenado_capilar', 'con_glasgow_ocular' => 'cv_glasgow_ocular',
        'con_glasgow_verbal' => 'cv_glasgow_verbal', 'con_glasgow_motora' => 'cv_glasgow_motora'
    ];

    private $catalogModels = [
        'tiposDocumento' => TipoDocumentoModel::class, 'generos' => GeneroModel::class, 'seguros' => SeguroModel::class,
        'estadoCiviles' => EstadoCivilModel::class, 'nacionalidades' => NacionalidadModel::class, 'empresas' => EmpresaModel::class,
        'etnias' => EtniaModel::class, 'nivelesEducacion' => NivelEducacionModel::class, 'estadosEducacion' => EstadoEducacionModel::class,
        'formasLlegada' => LlegadaModel::class, 'nacionalidadIndigena' => NacionalidadIndigenaModel::class,
        'puebloIndigena' => PuebloIndigenaModel::class, 'CondicionLlegada' => CondicionLlegadaModel::class
    ];

    private $seccionesConfig = [
        'C' => ['tabla' => 't_inicio_atencion', 'campos' => ['iat_fecha', 'iat_hora', 'col_codigo', 'iat_motivo', 'col_descripcion'], 'clave_datos' => 'datosSeccionCPrevios', 'clave_mapeados' => 'datos_seccionC_mapeados'],
        'D' => ['tabla' => 't_evento', 'join' => ['t_tipo_evento', 't_evento.tev_codigo = t_tipo_evento.tev_codigo', 'left'], 'select' => 't_evento.*, t_tipo_evento.tev_descripcion', 'multiple' => true, 'clave_datos' => 'datosSeccionDPrevios', 'clave_mapeados' => 'datos_seccionD_mapeados'],
        'E' => ['tabla' => 't_antecedente_paciente', 'multiple' => true, 'clave_datos' => 'datosSeccionEPrevios', 'clave_mapeados' => 'datos_seccionE_mapeados'],
        'F' => ['tabla' => 't_problema_actual', 'multiple' => true, 'clave_datos' => 'datosSeccionFPrevios', 'clave_mapeados' => 'datos_seccionF_mapeados'],
        'G' => ['tabla' => 't_atencion', 'campos' => ['ate_colores', 'ate_observaciones_triaje'], 'clave_datos' => 'datosSeccionGPrevios', 'clave_mapeados' => 'datos_seccionG_mapeados'],
        'H' => ['tabla' => 't_examen_fisico', 'multiple' => true, 'clave_datos' => 'datosSeccionHPrevios', 'clave_mapeados' => 'datos_seccionH_mapeados'],
        'I' => ['tabla' => 't_examen_trauma', 'multiple' => true, 'clave_datos' => 'datosSeccionIPrevios', 'clave_mapeados' => 'datos_seccionI_mapeados'],
        'J' => ['tabla' => 't_embarazo_parto', 'multiple' => true, 'clave_datos' => 'datosSeccionJPrevios', 'clave_mapeados' => 'datos_seccionJ_mapeados'],
        'K' => ['tabla' => 't_examenes_complementarios', 'multiple' => true, 'clave_datos' => 'datosSeccionKPrevios', 'clave_mapeados' => 'datos_seccionK_mapeados'],
        'L' => ['tabla' => 't_diagnostico_presuntivo', 'multiple' => true, 'clave_datos' => 'datosSeccionLPrevios', 'clave_mapeados' => 'datos_seccionL_mapeados'],
        'M' => ['tabla' => 't_diagnostico_definitivo', 'multiple' => true, 'clave_datos' => 'datosSeccionMPrevios', 'clave_mapeados' => 'datos_seccionM_mapeados'],
        'N' => ['tabla' => 't_tratamiento', 'multiple' => true, 'clave_datos' => 'datosSeccionNPrevios', 'clave_mapeados' => 'datos_seccionN_mapeados'],
        'O' => ['tabla' => 't_egreso_emergencia', 'multiple' => true, 'clave_datos' => 'datosSeccionOPrevios', 'clave_mapeados' => 'datos_seccionO_mapeados'],
        'P' => ['tabla' => 't_profesional_responsable', 'multiple' => true, 'clave_datos' => 'datosSeccionPPrevios', 'clave_mapeados' => 'datos_seccionP_mapeados']
    ];

   

    // ==================== VALIDACIONES Y PERMISOS ====================

    private function determinarContextoAcceso()
    {
        $rol_id = session()->get('rol_id');
        $parametroEnfermeria = $this->request->getGet('enfermeria');

        $esEnfermeria = ($rol_id == 3) || ($parametroEnfermeria == '1');
        $esEspecialista = ($rol_id == 5);

        return [
            'tiene_acceso' => $esEnfermeria || $esEspecialista,
            'es_enfermeria' => $esEnfermeria,
            'es_especialista' => $esEspecialista,
            'rol_id' => $rol_id
        ];
    }

    private function verificarPermisosAcceso($esObservacion, $ate_codigo, $esContinuacionProceso)
    {
        $usu_id = session()->get('usu_id');
        $modificacionesModel = new ModificacionesModel();

        if (!$esObservacion) {
            return $modificacionesModel->verificarAccesoMedico($ate_codigo, $usu_id, 'ES');
        }

        // Para observación, crear permiso básico preservando estado de modificación
        $permisoModificacion = $modificacionesModel->verificarAccesoMedico($ate_codigo, $usu_id, 'ES');

        return [
            'acceso' => true,
            'es_modificacion' => $permisoModificacion['es_modificacion'] ?? false,
            'motivo' => 'Acceso a observación',
            'motivo_habilitacion' => $permisoModificacion['motivo_habilitacion'] ?? '',
            'fecha_habilitacion' => $permisoModificacion['fecha_habilitacion'] ?? ''
        ];
    }

    // ==================== CONFIGURACIÓN DE CONTEXTOS ====================

    private function configurarContextoEnfermeria(&$data, $contexto, $ate_codigo)
    {
        if ($contexto['es_enfermeria']) {
            $data['contextoEnfermeria'] = true;
            $data['esEnfermeriaEspecialidad'] = true;
            $data['ocultarSeccionesOyP'] = true;
            $data['precargar_datos'] = true;
            $data['datosFormularioGuardadoEspecialista'] = $this->cargarDatosGuardados($ate_codigo) ?: [];
        } else {
            $data['contextoEnfermeria'] = false;
            $data['esEnfermeriaEspecialidad'] = false;
            $data['ocultarSeccionesOyP'] = true;
            $datosEspecialista = $this->cargarDatosGuardados($ate_codigo);
            $data['datosFormularioGuardadoEspecialista'] = !empty($datosEspecialista) ? $datosEspecialista : [];
        }
    }

    private function configurarContextoObservacion(&$data, $esObservacion, $ate_codigo, $procesoExistente)
    {
        if (!$esObservacion) {
            $data['contextoObservacion'] = false;
            $data['esObservacionEmergencia'] = false;
            return;
        }

        $data['contextoObservacion'] = true;
        $data['esObservacionEmergencia'] = true;
        $data['especialidad_codigo'] = 5;
        $data['datosObservacionGuardados'] = $this->cargarDatosCompletosParaObservacion($ate_codigo);

        //  Verificar datos cargados
        log_message('info', '🔍 DEBUG Observación - datosObservacionGuardados: ' . json_encode($data['datosObservacionGuardados']));
        log_message('info', '🔍 DEBUG Observación - seccionE: ' . json_encode($data['datosObservacionGuardados']['seccionE'] ?? 'NO EXISTE'));
        log_message('info', '🔍 DEBUG Observación - seccionJ: ' . json_encode($data['datosObservacionGuardados']['seccionJ'] ?? 'NO EXISTE'));
        log_message('info', '🔍 DEBUG Observación - seccionK: ' . json_encode($data['datosObservacionGuardados']['seccionK'] ?? 'NO EXISTE'));

        // Verificar envío desde especialidad
        $observacionModel = new \App\Models\Especialidades\ObservacionEspecialidadModel();
        $envioObservacion = $observacionModel->where('ate_codigo', $ate_codigo)
            ->where('obs_estado', 'ENVIADO_A_OBSERVACION')
            ->first();

        if ($envioObservacion) {
            $data['esEnviadoDesdeEspecialidad'] = true;
            $data['motivoEnvio'] = $envioObservacion['obs_motivo'];
            $data['fechaEnvio'] = $envioObservacion['obs_fecha_envio'];
            $data['horaEnvio'] = $envioObservacion['obs_hora_envio'];
        }

        // Cargar datos del médico que guardó
        if ($procesoExistente) {
            $datosEspecialista = $procesoExistente['ppe_seccion_especialista_datos']
                ? json_decode($procesoExistente['ppe_seccion_especialista_datos'], true)
                : null;

            $data['medico_que_guardo_especialidad'] = [
                'primer_nombre' => $datosEspecialista['primer_nombre'] ?? '',
                'primer_apellido' => $datosEspecialista['primer_apellido'] ?? '',
                'segundo_apellido' => $datosEspecialista['segundo_apellido'] ?? '',
                'documento' => $datosEspecialista['documento'] ?? '',
                'especialidad' => $datosEspecialista['especialidad'] ?? '',
                'firma_url' => $procesoExistente['ppe_firma_especialista'] ?? '',
                'sello_url' => $procesoExistente['ppe_sello_especialista'] ?? ''
            ];
        }
    }

    // ==================== INICIALIZACIÓN DE DATOS ====================

    private function inicializarDatosProceso($esContinuacionProceso, $procesoExistente, $permisoAcceso, $ate_codigo)
    {
        $data = [
            'es_modificacion' => false,
            'es_continuacion_proceso' => false,
            'motivo_modificacion' => '',
            'fecha_habilitacion' => ''
        ];

        // Verificar modificación
        if (isset($permisoAcceso['es_modificacion']) && $permisoAcceso['es_modificacion']) {
            $data['es_modificacion'] = true;
            $data['motivo_modificacion'] = $permisoAcceso['motivo_habilitacion'] ?? 'Modificación habilitada por administrador';
            $data['fecha_habilitacion'] = $permisoAcceso['fecha_habilitacion'] ?? '';

            if ($procesoExistente) {
                $datosEspecialista = $procesoExistente['ppe_seccion_especialista_datos']
                    ? json_decode($procesoExistente['ppe_seccion_especialista_datos'], true)
                    : null;

                $data['medico_que_completo_formulario'] = $this->construirDatosMedico($datosEspecialista, $procesoExistente);
            }
        }

        // Verificar continuación de proceso
        if ($esContinuacionProceso) {
            $datosEspecialista = $procesoExistente['ppe_seccion_especialista_datos']
                ? json_decode($procesoExistente['ppe_seccion_especialista_datos'], true)
                : null;

            $data['es_continuacion_proceso'] = true;
            $data['proceso_original'] = $procesoExistente;
            $data['mensaje_continuacion'] = "Continuando proceso iniciado por: " .
                $procesoExistente['usu_nombre'] . ' ' . $procesoExistente['usu_apellido'];
            $data['medico_que_guardo_proceso'] = array_merge(
                $this->construirDatosMedico($datosEspecialista, $procesoExistente),
                [
                    'fecha_guardado' => $datosEspecialista['fecha'] ?? '',
                    'hora_guardado' => $datosEspecialista['hora'] ?? ''
                ]
            );
        }

        $data['datosFormularioGuardadoEspecialista'] = $this->cargarDatosGuardados($ate_codigo);

        return $data;
    }

    private function construirDatosMedico($datosEspecialista, $procesoExistente)
    {
        return [
            'primer_nombre' => $datosEspecialista['primer_nombre'] ?? '',
            'primer_apellido' => $datosEspecialista['primer_apellido'] ?? '',
            'segundo_apellido' => $datosEspecialista['segundo_apellido'] ?? '',
            'documento' => $datosEspecialista['documento'] ?? '',
            'especialidad' => $datosEspecialista['especialidad'] ?? '',
            'firma_url' => $procesoExistente['ppe_firma_especialista'] ?? '',
            'sello_url' => $procesoExistente['ppe_sello_especialista'] ?? ''
        ];
    }

    // ==================== CARGA DE DATOS DEL FORMULARIO ====================

    private function cargarDatosFormularioEspecialidad($are_codigo)
    {
        try {
            $areaAtencionModel = new AreaAtencionModel();
            $areaAtencion = $areaAtencionModel->find($are_codigo);

            if (!$areaAtencion) {
                return ['error' => 'Área de atención no encontrada.'];
            }

            $ate_codigo = $areaAtencion['ate_codigo'];

            // Obtener entidades principales
            $atencionModel = new AtencionModel();
            $atencion = $atencionModel->find($ate_codigo);

            if (!$atencion) {
                return ['error' => 'Atención no encontrada.'];
            }

            $pacienteModel = new PacienteModel();
            $paciente = $pacienteModel->find($atencion['pac_codigo']);

            if (!$paciente) {
                return ['error' => 'Paciente no encontrado.'];
            }

            $constantesVitalesModel = new ConstantesVitalesModel();
            $constantesVitales = $constantesVitalesModel->obtenerUltimasPorAtencion($ate_codigo);

            if (empty($constantesVitales)) {
                return ['error' => 'Este paciente no ha sido evaluado por enfermería.'];
            }

            // Construir array de datos
            $usuarioModel = new UsuarioModel();
            $especialistaActual = $usuarioModel->find(session()->get('usu_id'));

            $data = [];
            $this->configurarDatosEspecialista($data, $especialistaActual, $areaAtencion);
            $this->configurarDatosEstablecimiento($data);
            $this->obtenerDatosAdmisionistaOriginal($data, $ate_codigo);
            $this->verificarYPrecargarDatosMedicoTriaje($data, $ate_codigo);

            $seccionC = $data['datosSeccionCPrevios'] ?? [];
            $seccionD = $data['datosSeccionDPrevios'] ?? [];

            $data = array_merge($data, [
                'are_codigo' => $are_codigo,
                'ate_codigo' => $ate_codigo,
                'area_atencion' => $areaAtencion,
                'paciente' => $paciente,
                'atencion' => $atencion,
                'paciente_id' => $atencion['pac_codigo'],
                'es_especialidad' => true,
                'mostrar_seccion_g' => true,
                'precargar_datos' => true,
                'datos_paciente_mapeados' => $this->mapearDatosOptimizado($paciente, 'paciente'),
                'datos_atencion_mapeados' => $this->mapearDatosOptimizado($atencion, 'atencion'),
                'datos_constantes_vitales_mapeados' => $this->mapearDatosOptimizado($constantesVitales, 'constantes'),
                'datos_seccionC_mapeados' => $this->mapearSeccionC($seccionC),
                'datos_seccionD_mapeados' => $this->mapearSeccionD($seccionD),
                'datos_seccionG_mapeados' => $this->mapearSeccionG($atencion)
            ]);

            $this->cargarCatalogos($data);

            return $data;

        } catch (\Exception $e) {
            return ['error' => 'Error interno: ' . $e->getMessage()];
        }
    }

    // ==================== CARGA DE DATOS PARA OBSERVACIÓN ====================

    private function cargarDatosCompletosParaObservacion($ate_codigo)
    {
        $datosCompletos = [];

        try {
            $db = \Config\Database::connect();

            // Sección E: Antecedentes
            $seccionE = $db->table('t_antecedente_paciente')->where('ate_codigo', $ate_codigo)->get()->getResultArray();
            log_message('info', '🔍 DEBUG - seccionE BD RAW: ' . json_encode($seccionE));
            if (!empty($seccionE)) {
                $datosCompletos['seccionE'] = $this->procesarAntecedentes($seccionE);
                log_message('info', '🔍 DEBUG - seccionE PROCESADA: ' . json_encode($datosCompletos['seccionE']));
            } else {
                log_message('info', 'DEBUG - seccionE VACÍA en BD');
            }

            // Sección F: Problema actual
            $seccionF = $db->table('t_problema_actual')->where('ate_codigo', $ate_codigo)->get()->getRowArray();
            if (!empty($seccionF)) {
                $datosCompletos['seccionF'] = ['descripcion' => $seccionF['pro_descripcion'] ?? ''];
            }

            // Sección H: Examen físico
            $seccionH = $db->table('t_examen_fisico')->where('ate_codigo', $ate_codigo)->get()->getResultArray();
            if (!empty($seccionH)) {
                $datosCompletos['seccionH'] = $this->procesarExamenFisico($seccionH);
            }

            // Sección I: Examen trauma
            $seccionI = $db->table('t_examen_trauma')->where('ate_codigo', $ate_codigo)->get()->getRowArray();
            if (!empty($seccionI)) {
                $datosCompletos['seccionI'] = ['descripcion' => $seccionI['tra_descripcion'] ?? ''];
            }

            // Sección J: Embarazo
            $seccionJ = $db->table('t_embarazo_parto')->where('ate_codigo', $ate_codigo)->get()->getRowArray();
            log_message('info', '🔍 DEBUG - seccionJ BD RAW: ' . json_encode($seccionJ));
            if (!empty($seccionJ)) {
                $datosCompletos['seccionJ'] = $this->procesarEmbarazo($seccionJ);
                log_message('info', '🔍 DEBUG - seccionJ PROCESADA: ' . json_encode($datosCompletos['seccionJ']));
            } else {
                log_message('info', 'DEBUG - seccionJ VACÍA en BD');
            }

            // Sección K: Exámenes complementarios
            $seccionK = $db->table('t_examenes_complementarios')->where('ate_codigo', $ate_codigo)->get()->getResultArray();
            if (!empty($seccionK)) {
                $datosCompletos['seccionK'] = $this->procesarExamenesComplementarios($seccionK);
            }

            // Secciones L y M: Diagnósticos
            $seccionL = $db->table('t_diagnostico_presuntivo')->where('ate_codigo', $ate_codigo)->get()->getResultArray();
            if (!empty($seccionL)) {
                $datosCompletos['seccionL'] = $this->procesarDiagnosticos($seccionL);
            }

            $seccionM = $db->table('t_diagnostico_definitivo')->where('ate_codigo', $ate_codigo)->get()->getResultArray();
            if (!empty($seccionM)) {
                $datosCompletos['seccionM'] = $this->procesarDiagnosticos($seccionM);
            }

            // Sección N: Tratamiento
            $seccionN = $db->table('t_tratamiento')->where('ate_codigo', $ate_codigo)->get()->getResultArray();
            if (!empty($seccionN)) {
                $datosCompletos['seccionN'] = $this->procesarTratamiento($seccionN);
            }

            // Sección O: Egreso
            $seccionO = $db->table('t_egreso_emergencia')->where('ate_codigo', $ate_codigo)->get()->getResultArray();
            if (!empty($seccionO)) {
                $datosCompletos['seccionO'] = $this->procesarEgreso($seccionO);
            }

        } catch (\Exception $e) {
            log_message('error', 'Error cargando datos observación: ' . $e->getMessage());
        }

        return $datosCompletos;
    }

    // Métodos helper para procesar secciones de observación
    private function procesarAntecedentes($seccionE)
    {
        $data = ['no_aplica' => false, 'antecedentes' => [], 'descripcion' => ''];

        foreach ($seccionE as $antecedente) {
            if ($antecedente['ap_no_aplica'] == 1) {
                $data['no_aplica'] = true;
                break;
            }
            if (!empty($antecedente['tan_codigo'])) {
                $data['antecedentes'][] = $antecedente['tan_codigo'];
            }
            if (!empty($antecedente['ap_descripcion']) && $data['descripcion'] === '') {
                $data['descripcion'] = $antecedente['ap_descripcion'];
            }
        }

        return $data;
    }

    private function procesarExamenFisico($seccionH)
    {
        $data = ['zonas' => [], 'descripcion' => ''];

        foreach ($seccionH as $examen) {
            if (!empty($examen['zef_codigo'])) {
                $data['zonas'][] = $examen['zef_codigo'];
            }
            if (!empty($examen['ef_descripcion']) && $data['descripcion'] === '') {
                $data['descripcion'] = $examen['ef_descripcion'];
            }
        }

        return $data;
    }

    private function procesarEmbarazo($seccionJ)
    {
        return [
            'no_aplica' => ($seccionJ['emb_no_aplica'] == 1),
            'gestas' => $seccionJ['emb_numero_gestas'] ?? '',
            'partos' => $seccionJ['emb_numero_partos'] ?? '',
            'abortos' => $seccionJ['emb_numero_abortos'] ?? '',
            'cesareas' => $seccionJ['emb_numero_cesareas'] ?? '',
            'fum' => $seccionJ['emb_fum'] ?? '',
            'semanas_gestacion' => $seccionJ['emb_semanas_gestacion'] ?? '',
            'movimiento_fetal' => $seccionJ['emb_movimiento_fetal'] ?? '',
            'fcf' => $seccionJ['emb_frecuencia_cardiaca_fetal'] ?? '',
            'ruptura_membranas' => $seccionJ['emb_ruptura_menbranas'] ?? '',
            'tiempo_ruptura' => $seccionJ['emb_tiempo'] ?? '',
            'afu' => $seccionJ['emb_afu'] ?? '',
            'presentacion' => $seccionJ['emb_presentacion'] ?? '',
            'sangrado_vaginal' => $seccionJ['emb_sangrado_vaginal'] ?? '',
            'contracciones' => $seccionJ['emb_contracciones'] ?? '',
            'dilatacion' => $seccionJ['emb_dilatacion'] ?? '',
            'borramiento' => $seccionJ['emb_borramiento'] ?? '',
            'plano' => $seccionJ['emb_plano'] ?? '',
            'pelvis_viable' => $seccionJ['emb_pelvis_viable'] ?? '',
            'score_mama' => $seccionJ['emb_score_mama'] ?? '',
            'observaciones' => $seccionJ['emb_observaciones'] ?? ''
        ];
    }

    private function procesarExamenesComplementarios($seccionK)
    {
        $data = ['no_aplica' => false, 'tipos' => [], 'observaciones' => ''];

        foreach ($seccionK as $examen) {
            if ($examen['exa_no_aplica'] == 1) {
                $data['no_aplica'] = true;
                break;
            }
            if (!empty($examen['tipo_id'])) {
                $data['tipos'][] = $examen['tipo_id'];
            }
            if (!empty($examen['exa_observaciones']) && $data['observaciones'] === '') {
                $data['observaciones'] = $examen['exa_observaciones'];
            }
        }

        return $data;
    }

    private function procesarDiagnosticos($seccion)
    {
        $data = [];
        $contador = 1;

        foreach ($seccion as $diagnostico) {
            if ($contador <= 3) {
                $campo = isset($diagnostico['diagp_descripcion']) ? 'diagp' : 'diagd';
                $data["diagnostico$contador"] = [
                    'descripcion' => $diagnostico["{$campo}_descripcion"] ?? '',
                    'cie' => $diagnostico["{$campo}_cie"] ?? ''
                ];
                $contador++;
            }
        }

        return $data;
    }

    private function procesarTratamiento($seccionN)
    {
        $data = ['plan_general' => '', 'tratamientos' => []];

        foreach ($seccionN as $tratamiento) {
            if (!empty($tratamiento['trat_medicamento'])) {
                $data['tratamientos'][] = [
                    'trat_id' => $tratamiento['trat_id'] ?? null,
                    'medicamento' => $tratamiento['trat_medicamento'],
                    'via' => $tratamiento['trat_via'] ?? '',
                    'dosis' => $tratamiento['trat_dosis'] ?? '',
                    'posologia' => $tratamiento['trat_posologia'] ?? '',
                    'dias' => $tratamiento['trat_dias'] ?? '',
                    'administrado' => intval($tratamiento['trat_administrado'] ?? 0)
                ];
            }
            if (!empty($tratamiento['trat_observaciones']) && $data['plan_general'] === '') {
                $data['plan_general'] = $tratamiento['trat_observaciones'];
            }
        }

        return $data;
    }

    private function procesarEgreso($seccionO)
    {
        $estadosEgreso = [];
        $modalidadesEgreso = [];
        $tiposEgreso = [];
        $datosComunes = [];

        foreach ($seccionO as $registro) {
            if (!empty($registro['ese_codigo'])) $estadosEgreso[] = $registro['ese_codigo'];
            if (!empty($registro['moe_codigo'])) $modalidadesEgreso[] = $registro['moe_codigo'];
            if (!empty($registro['tie_codigo'])) $tiposEgreso[] = $registro['tie_codigo'];

            if (empty($datosComunes)) {
                $datosComunes = [
                    'egr_establecimiento' => $registro['egr_establecimiento'] ?? '',
                    'egr_observaciones' => $registro['egr_observaciones'] ?? '',
                    'egr_dias_reposo' => $registro['egr_dias_reposo'] ?? 0,
                    'egr_observacion_emergencia' => $registro['egr_observacion_emergencia'] ?? 0
                ];
            }
        }

        return array_merge($datosComunes, [
            'estados_egreso' => array_unique($estadosEgreso),
            'modalidades_egreso' => array_unique($modalidadesEgreso),
            'tipos_egreso' => array_unique($tiposEgreso)
        ]);
    }

    // ==================== MAPEO DE DATOS ====================

    private function mapearDatosOptimizado($datos, $tipo)
    {
        if (!$datos || !is_array($datos)) {
            return [];
        }

        switch ($tipo) {
            case 'paciente':
                return $this->mapearPaciente($datos);
            case 'atencion':
                return $this->mapearAtencion($datos);
            case 'constantes':
                return $this->mapearConstantes($datos);
            default:
                return [];
        }
    }

    private function mapearPaciente($datos)
    {
        $apellidosTokens = !empty($datos['pac_apellidos']) ? explode(' ', trim($datos['pac_apellidos'])) : [];
        $nombresTokens = !empty($datos['pac_nombres']) ? explode(' ', trim($datos['pac_nombres'])) : [];

        $resultado = [
            'apellido1' => $apellidosTokens[0] ?? '',
            'apellido2' => $apellidosTokens[1] ?? '',
            'nombre1' => $nombresTokens[0] ?? '',
            'nombre2' => $nombresTokens[1] ?? ''
        ];

        foreach ($this->pacienteMappingConfig['basic'] as $campo) {
            $resultado[$campo] = $datos[$campo] ?? '';
        }

        foreach ($this->pacienteMappingConfig['personal'] as $campo) {
            $resultado[$campo] = $datos[$campo] ?? '';
        }

        foreach ($this->pacienteMappingConfig['codes'] as $origen => $destino) {
            $resultado[$destino] = $datos[$origen] ?? '';
        }

        foreach ($this->pacienteMappingConfig['address'] as $origen => $destino) {
            $resultado[$destino] = $datos[$origen] ?? '';
        }

        foreach ($this->pacienteMappingConfig['emergency'] as $origen => $destino) {
            $resultado[$destino] = $datos[$origen] ?? '';
        }

        return $resultado;
    }

    private function mapearAtencion($datos)
    {
        $resultado = [];
        foreach ($this->atencionMappingConfig as $campo) {
            $resultado[$campo] = $datos[$campo] ?? '';
        }
        return $resultado;
    }

    private function mapearConstantes($datos)
    {
        $resultado = [];

        foreach ($this->constantesVitalesMappingConfig as $origen => $destino) {
            if ($origen === 'con_sin_constantes') {
                $resultado[$destino] = ($datos[$origen] ?? false) ? '1' : '0';
            } else {
                $resultado[$destino] = $datos[$origen] ?? '';
            }
        }

        return array_merge($resultado, [
            'con_codigo' => $datos['con_codigo'] ?? '',
            'fecha_registro' => $datos['ate_fecha'] ?? '',
            'hora_registro' => $datos['ate_hora'] ?? ''
        ]);
    }

    // ==================== CARGA DE DATOS GUARDADOS ====================

    private function cargarDatosGuardados($ate_codigo)
    {
        $datosGuardados = [];
        $seccionesEditables = ['E', 'F', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P'];

        foreach ($this->seccionesConfig as $letra => $config) {
            if (in_array($letra, $seccionesEditables)) {
                $db = \Config\Database::connect();

                try {
                    $query = $db->table($config['tabla'])->where('ate_codigo', $ate_codigo);
                    $resultados = isset($config['multiple']) && $config['multiple']
                        ? $query->get()->getResultArray()
                        : $query->get()->getRowArray();

                    if ($resultados) {
                        $datosGuardados['seccion' . $letra] = $resultados;
                    }
                } catch (\Exception $e) {
                    log_message('error', "Error cargando sección $letra: " . $e->getMessage());
                }
            }
        }

        return $datosGuardados;
    }

    private function verificarYPrecargarDatosMedicoTriaje(&$data, $ate_codigo)
    {
        $db = \Config\Database::connect();

        $seccionC = $db->table('t_inicio_atencion')->where('ate_codigo', $ate_codigo)->get()->getRowArray();
        $data['datosSeccionCPrevios'] = $seccionC ?: [];

        $seccionD = $db->table('t_evento')
            ->select('t_evento.*, t_tipo_evento.tev_descripcion')
            ->join('t_tipo_evento', 't_evento.tev_codigo = t_tipo_evento.tev_codigo', 'left')
            ->where('t_evento.ate_codigo', $ate_codigo)
            ->get()
            ->getResultArray();

        $data['datosSeccionDPrevios'] = $seccionD;
    }

    private function mapearSeccionC($seccionC)
    {
        if (empty($seccionC)) return [];

        return [
            'iat_fecha' => $seccionC['iat_fecha'] ?? '',
            'iat_hora' => $seccionC['iat_hora'] ?? '',
            'col_codigo' => $seccionC['col_codigo'] ?? '',
            'iat_motivo' => $seccionC['iat_motivo'] ?? '',
            'col_descripcion' => $seccionC['col_descripcion'] ?? ''
        ];
    }

    private function mapearSeccionD($seccionD)
    {
        if (empty($seccionD)) return [];

        $eventosMapeados = [];
        $tiposEventos = [];
        $primerEvento = null;

        foreach ($seccionD as $evento) {
            $eventoMapeado = [
                'eve_codigo' => $evento['eve_codigo'] ?? '',
                'tev_codigo' => $evento['tev_codigo'] ?? '',
                'tev_descripcion' => $evento['tev_descripcion'] ?? '',
                'eve_fecha' => $evento['eve_fecha'] ?? '',
                'eve_hora' => $evento['eve_hora'] ?? '',
                'eve_lugar' => $evento['eve_lugar'] ?? '',
                'eve_direccion' => $evento['eve_direccion'] ?? '',
                'eve_observacion' => $evento['eve_observacion'] ?? '',
                'eve_notificacion' => $evento['eve_notificacion'] ?? 'no'
            ];

            $eventosMapeados[] = $eventoMapeado;

            if (!empty($evento['tev_codigo'])) {
                $tiposEventos[] = $evento['tev_codigo'];
            }

            if ($primerEvento === null) {
                $primerEvento = $eventoMapeado;
            }
        }

        return [
            'evento_principal' => $primerEvento,
            'todos_eventos' => $eventosMapeados,
            'tipos_eventos' => $tiposEventos
        ];
    }

    private function mapearSeccionG($atencion)
    {
        if (!$atencion) return [];

        return [
            'ate_colores' => $atencion['ate_colores'] ?? '',
            'ate_observaciones_triaje' => $atencion['ate_observaciones_triaje'] ?? ''
        ];
    }

    // ==================== CONFIGURACIÓN DE ESPECIALISTA Y ESTABLECIMIENTO ====================

    private function configurarDatosEspecialista(&$data, $especialistaActual, $areaAtencion = null)
    {
        $medicoResponsableId = $areaAtencion['are_medico_asignado'] ?? null;
        $usuarioSesionId = session()->get('usu_id');
        $esMedicoResponsable = ($medicoResponsableId == $usuarioSesionId);

        $medico = null;
        if ($medicoResponsableId) {
            $areaAtencionModel = new AreaAtencionModel();
            $medico = $areaAtencionModel->obtenerMedicoEspecialidadPorId($medicoResponsableId);
        }

        if (!$medico) {
            $medico = [
                'usu_nombre' => session()->get('usu_nombre') ?? '',
                'usu_apellido' => session()->get('usu_apellido') ?? '',
                'usu_nro_documento' => '',
                'esp_nombre' => 'Sin especialidad',
                'esp_codigo' => null
            ];

            if ($usuarioSesionId) {
                $areaAtencionModel = new AreaAtencionModel();
                $medicoSesion = $areaAtencionModel->obtenerMedicoEspecialidadPorId($usuarioSesionId);
                if ($medicoSesion) {
                    $medico = array_merge($medico, $medicoSesion);
                }
            }
        }

        $nombres = explode(' ', trim($medico['usu_nombre']));
        $apellidos = explode(' ', trim($medico['usu_apellido']));

        $data['medico_actual'] = [
            'id' => $medicoResponsableId ?? $usuarioSesionId,
            'primer_nombre' => $nombres[0] ?? '',
            'segundo_nombre' => isset($nombres[1]) ? implode(' ', array_slice($nombres, 1)) : '',
            'nombre_completo' => $medico['usu_nombre'],
            'primer_apellido' => $apellidos[0] ?? '',
            'segundo_apellido' => $apellidos[1] ?? '',
            'apellido_completo' => $medico['usu_apellido'],
            'documento' => $medico['usu_nro_documento'] ?? '',
            'especialidad_nombre' => $medico['esp_nombre'] ?? 'Sin especialidad',
            'especialidad_codigo' => $medico['esp_codigo'],
            'es_responsable' => $esMedicoResponsable,
            'firma_url' => $medico['usu_firma'] ?? '',
            'sello_url' => $medico['usu_sello'] ?? ''
        ];

        $data['medico_nombre'] = trim($medico['usu_nombre'] . ' ' . $medico['usu_apellido']);
        $data['medico_especialidad'] = $medico['esp_nombre'] ?? 'Sin especialidad';

        $data['medico_responsable'] = [
            'id' => $medicoResponsableId,
            'nombre' => $medico['usu_nombre'],
            'apellido' => $medico['usu_apellido'],
            'nombre_completo' => trim($medico['usu_nombre'] . ' ' . $medico['usu_apellido']),
            'especialidad' => $medico['esp_nombre'] ?? 'Sin especialidad',
            'especialidad_codigo' => $medico['esp_codigo'],
            'documento' => $medico['usu_nro_documento'] ?? '',
            'are_estado' => $areaAtencion['are_estado'] ?? 'EN_ATENCION'
        ];
    }

    private function configurarDatosEstablecimiento(&$data)
    {
        try {
            $establecimientoModel = new EstablecimientoModel();
            $establecimiento = $establecimientoModel->obtenerEstablecimientoActual();

            $data['estab_institucion'] = $establecimiento['est_institucion'] ?? '';
            $data['estab_unicode'] = $establecimiento['est_unicodigo'] ?? '';
            $data['estab_nombre'] = $establecimiento['est_nombre_establecimiento'] ?? '';
        } catch (\Exception $e) {
            $data['estab_institucion'] = '';
            $data['estab_unicode'] = '';
            $data['estab_nombre'] = '';
        }
    }

    private function obtenerDatosAdmisionistaOriginal(&$data, $ate_codigo)
    {
        $establecimientoRegistroModel = new EstablecimientoRegistroModel();

        $registroAdmision = $establecimientoRegistroModel
            ->select('t_establecimiento_registro.*, t_usuario.usu_nombre, t_usuario.usu_apellido')
            ->join('t_usuario', 't_establecimiento_registro.usu_id = t_usuario.usu_id')
            ->where('t_establecimiento_registro.ate_codigo', $ate_codigo)
            ->first();

        if ($registroAdmision) {
            $nombreAdmisionista = $registroAdmision['usu_nombre'] . ' ' . $registroAdmision['usu_apellido'];
            $data['nombre_admisionista'] = $nombreAdmisionista;
            $data['admisionistaEspecialidades'] = $nombreAdmisionista;
            $data['estabArchivo'] = $registroAdmision['est_num_archivo'];
        } else {
            $data['admisionistaEspecialidades'] = 'No encontrado';
            $data['estabArchivo'] = '00000';
        }
    }

    private function cargarCatalogos(&$data)
    {
        foreach ($this->catalogModels as $key => $modelClass) {
            try {
                $model = new $modelClass();
                $data[$key] = $model->findAll();
            } catch (\Exception $e) {
                $data[$key] = [];
            }
        }
    }

    // ==================== VERIFICACIÓN DE FORMULARIO COMPLETADO ====================

    private function verificarFormularioYaCompletado($ate_codigo, $are_codigo)
    {
        try {
            $db = \Config\Database::connect();

            $areaCompletada = $db->table('t_area_atencion')
                ->where('are_codigo', $are_codigo)
                ->where('are_estado', 'COMPLETADA')
                ->get()
                ->getRowArray();

            if ($areaCompletada) {
                return [
                    'completado' => true,
                    'mensaje' => 'Esta atención de especialidad ya ha sido completada.',
                    'fecha_completado' => $areaCompletada['are_fecha_fin_atencion'] ?? '',
                    'hora_completado' => $areaCompletada['are_hora_fin_atencion'] ?? ''
                ];
            }

            $formularioCompletado = $db->table('t_formulario_usuario')
                ->where('ate_codigo', $ate_codigo)
                ->where('seccion', 'ES')
                ->get()
                ->getRowArray();

            if ($formularioCompletado) {
                if ($formularioCompletado['habilitado_por_admin'] == 1) {
                    return ['completado' => false];
                }

                $especialistaQueCompleto = $db->table('t_usuario')
                    ->where('usu_id', $formularioCompletado['usu_id'])
                    ->get()
                    ->getRowArray();

                $nombreEspecialista = 'Usuario desconocido';
                if ($especialistaQueCompleto) {
                    $nombreEspecialista = trim($especialistaQueCompleto['usu_nombre'] . ' ' . $especialistaQueCompleto['usu_apellido']);
                }

                return [
                    'completado' => true,
                    'mensaje' => "Este formulario de especialidad ya fue completado por: {$nombreEspecialista}",
                    'fecha_completado' => $formularioCompletado['fecha'] ?? '',
                    'especialista' => $nombreEspecialista
                ];
            }

            return ['completado' => false];

        } catch (\Exception $e) {
            return ['completado' => false];
        }
    }

    // ==================== MÉTODO AJAX PÚBLICO ====================

    public function obtenerDatosPaciente($pacienteId)
    {
        if (!session()->get('logged_in')) {
            return $this->response->setJSON(['error' => 'No autorizado'])->setStatusCode(401);
        }

        if (session()->get('rol_id') != 5) {
            return $this->response->setJSON(['error' => 'Sin permisos para esta sección'])->setStatusCode(403);
        }

        try {
            if (empty($pacienteId) || !is_numeric($pacienteId)) {
                return $this->response->setJSON(['success' => false, 'error' => 'ID de paciente inválido']);
            }

            $pacienteModel = new PacienteModel();
            $atencionModel = new AtencionModel();
            $constantesVitalesModel = new ConstantesVitalesModel();

            $pacienteData = $pacienteModel->find($pacienteId);

            $atencionData = $atencionModel
                ->where('pac_codigo', $pacienteId)
                ->orderBy('ate_codigo', 'DESC')
                ->first();

            if (!$pacienteData) {
                return $this->response->setJSON(['success' => false, 'error' => 'No se encontraron datos del paciente.']);
            }

            $constantesVitales = [];
            if ($atencionData) {
                $constantesVitales = $constantesVitalesModel->obtenerPorAtencion($atencionData['ate_codigo']);
            }

            return $this->response->setJSON([
                'success' => true,
                'paciente' => $this->mapearDatosOptimizado($pacienteData, 'paciente'),
                'atencion' => $this->mapearDatosOptimizado($atencionData, 'atencion'),
                'constantes_vitales' => $constantesVitales
            ]);

        } catch (\Exception $e) {
            return $this->response->setJSON([
                'success' => false,
                'error' => 'Error interno del servidor: ' . $e->getMessage()
            ]);
        }
    }
}
