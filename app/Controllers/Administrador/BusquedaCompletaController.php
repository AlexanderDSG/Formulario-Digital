<?php

namespace App\Controllers\Administrador;

use App\Controllers\BaseController;
use App\Models\Medicos\GuardarSecciones\EgresoEmergenciaModel;
use App\Models\Medicos\GuardarSecciones\TratamientoModel;
use App\Models\Administrador\BusquedaCompletaModel;

class BusquedaCompletaController extends BaseController
{
    protected $tratamientoModel;
    protected $EgresoEmergenciaModel;

    public function __construct()
    {
        // Cargar el modelo de tratamientos
        $this->tratamientoModel = new TratamientoModel();
        // Cargar el modelo de egreso emergencia
        $this->EgresoEmergenciaModel = new EgresoEmergenciaModel();
    }

    public function obtenerTratamientos($ate_codigo)
    {
        // VERIFICACIÓN DE PERMISOS CORREGIDA
        if (!session()->get('logged_in')) {
            return $this->response->setJSON(['success' => false, 'message' => 'No autorizado'])
                ->setStatusCode(401);
        }

        // PERMITIR A ADMINISTRADORES
        if (session()->get('rol_id') != 1) {
            return $this->response->setJSON(['success' => false, 'message' => 'No autorizado'])
                ->setStatusCode(401);
        }

        try {
            $tratamientos = $this->tratamientoModel->obtenerPorCodigoAtencion($ate_codigo);

            if (!$tratamientos || empty($tratamientos)) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'No se encontraron tratamientos'
                ]);
            }

            return $this->response->setJSON([
                'success' => true,
                'tratamientos' => $tratamientos,
                'total' => count($tratamientos)
            ]);
        } catch (\Exception $e) {

            return $this->response->setJSON([
                'success' => false,
                'message' => 'Error interno del servidor'
            ]);
        }
    }

    /**
     * FUNCIÓN PRINCIPAL PARA BÚSQUEDA POR FECHA
     */
    public function buscarPorFecha()
    {
        ini_set('memory_limit', '1024M');


        // VERIFICACIÓN DE AUTENTICACIÓN
        if (!session()->get('logged_in')) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'No autorizado - sesión no válida'
            ])->setStatusCode(401);
        }

        // VERIFICACIÓN DE PERMISOS 
        if (session()->get('rol_id') != 1) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Sin permisos para esta sección - se requiere rol de administrador'
            ])->setStatusCode(403);
        }

        try {
            $fecha = $this->request->getPost('fecha');
            $identificador = $this->request->getPost('identificador');


            // VALIDACIONES DE ENTRADA
            if (empty($fecha)) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'La fecha es requerida'
                ]);
            }

            if (empty($identificador)) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'El identificador del paciente (cédula o historia clínica) es requerido'
                ]);
            }

            // VALIDAR FORMATO DE FECHA
            if (!$this->validarFecha($fecha)) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Formato de fecha inválido. Use YYYY-MM-DD'
                ]);
            }

            
            $busquedaModel = new BusquedaCompletaModel();

            //REALIZAR BÚSQUEDA
            $resultados = $busquedaModel->obtenerPorIdentificadorYFecha($identificador, $fecha);

            if (empty($resultados)) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => "No se encontraron datos para el identificador '{$identificador}' en la fecha '{$fecha}'"
                ]);
            }

            //PROCESAR PRIMER RESULTADO
            $data = $resultados[0];


            return $this->response->setJSON([
                'success' => true,
                'message' => 'Datos encontrados exitosamente',
                'data' => $this->formatearDatosParaFormulario($data),
                'debug_info' => [
                    'fecha_consultada' => $fecha,
                    'identificador_consultado' => $identificador,
                    'total_resultados' => count($resultados),
                    'campos_disponibles' => array_keys($data),
                    'timestamp' => date('Y-m-d H:i:s')
                ]
            ]);
        } catch (\Exception $e) {
 
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Error interno del servidor',
                'error_detail' => $e->getMessage(),
                'debug_info' => [
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                    'timestamp' => date('Y-m-d H:i:s')
                ]
            ]);
        }
    }

    /**
     * Formatear datos COMPLETOS para que sean compatibles con el formulario
     */
    private function formatearDatosParaFormulario($data)
    {
        return [
            // ===== DATOS DEL PACIENTE (Tabla t_paciente) =====
            'pac_codigo' => $data['pac_codigo'] ?? '',
            'pac_his_cli' => $data['pac_his_cli'] ?? '',
            'pac_cedula' => $data['pac_cedula'] ?? '',
            'pac_apellidos' => $data['pac_apellidos'] ?? '',
            'pac_nombres' => $data['pac_nombres'] ?? '',
            'pac_fecha_nac' => $data['pac_fecha_nac'] ?? '',
            'pac_lugar_nac' => $data['pac_lugar_nac'] ?? '',
            'pac_edad_valor' => $data['pac_edad_valor'] ?? '',
            'pac_grupo_prioritario' => $data['pac_grupo_prioritario'] ?? '',
            'pac_grupo_sanguineo' => $data['pac_grupo_sanguineo'] ?? '',
            'pac_edad_unidad' => $data['pac_edad_unidad'] ?? '',
            'pac_telefono_fijo' => $data['pac_telefono_fijo'] ?? '',
            'pac_telefono_celular' => $data['pac_telefono_celular'] ?? '',
            'pac_ocupacion' => $data['pac_ocupacion'] ?? '',
            'pac_direccion' => $data['pac_direccion'] ?? '',
            'pac_provincias' => $data['pac_provincias'] ?? '',
            'pac_cantones' => $data['pac_cantones'] ?? '',
            'pac_parroquias' => $data['pac_parroquias'] ?? '',
            'pac_barrio' => $data['pac_barrio'] ?? '',
            'pac_calle_secundaria' => $data['pac_calle_secundaria'] ?? '',
            'pac_referencia' => $data['pac_referencia'] ?? '',
            'pac_avisar_a' => $data['pac_avisar_a'] ?? '',
            'pac_parentezco_avisar_a' => $data['pac_parentezco_avisar_a'] ?? '',
            'pac_direccion_avisar' => $data['pac_direccion_avisar'] ?? '',
            'pac_telefono_avisar_a' => $data['pac_telefono_avisar_a'] ?? '',

            // ===== DATOS DE ATENCIÓN (Tabla t_atencion) =====
            'ate_codigo' => $data['ate_codigo'] ?? '',
            'ate_fecha' => $data['ate_fecha'] ?? '',
            'ate_hora' => $data['ate_hora'] ?? '',
            'ate_colores' => $data['ate_colores'] ?? '',
            'ate_custodia_policial' => $data['ate_custodia_policial'] ?? '',
            'ate_aliento_etilico' => $data['ate_aliento_etilico'] ?? '',
            'ate_fuente_informacion' => $data['ate_fuente_informacion'] ?? '',
            'ate_ins_entrega_paciente' => $data['ate_ins_entrega_paciente'] ?? '',
            'ate_telefono' => $data['ate_telefono'] ?? '',
            'lleg_codigo' => $data['lleg_codigo'] ?? '',

            // ===== DATOS DESCRIPTIVOS (Catálogos con JOIN) =====
            'genero' => $data['genero'] ?? '',
            'seguro' => $data['seguro'] ?? '',
            'grupo_cultural' => $data['grupo_cultural'] ?? '',
            'empresa' => $data['empresa'] ?? '',
            'pueblo_indigena' => $data['pue_ind_nombre'] ?? '',
            'nacionalidad_indigena' => $data['nac_ind_nombre'] ?? '',

            'nacionalidad' => $data['nacionalidad'] ?? '',
            'estado_civil' => $data['estado_civil'] ?? '',
            'nivel_educacion' => $data['nivel_educacion'] ?? '',
            'estado_nivel_educ' => $data['estado_nivel_educ'] ?? '',
            'tipo_documento' => $data['tipo_documento'] ?? '',
            'forma_llegada' => $data['forma_llegada'] ?? '',

            // ===== DATOS DEL ESTABLECIMIENTO Y REGISTRO =====
            'est_num_archivo' => $data['est_num_archivo'] ?? '',
            'usuario_nombre_completo' => $data['usuario_nombre_completo'] ?? '',

            // ===== INICIO DE ATENCIÓN (Tabla t_inicio_atencion) =====
            'iat_fecha' => $data['iat_fecha'] ?? '',
            'iat_hora' => $data['iat_hora'] ?? '',
            'iat_motivo' => $data['iat_motivo'] ?? '',
            'col_codigo' => $data['col_codigo'] ?? '',
            'condicion_llegada' => $data['condicion_llegada'] ?? '',

            // ===== EVENTO (Tabla t_evento) =====
            'eve_fecha' => $data['eve_fecha'] ?? '',
            'eve_hora' => $data['eve_hora'] ?? '',
            'eve_lugar' => $data['eve_lugar'] ?? '',
            'eve_direccion' => $data['eve_direccion'] ?? '',
            'eve_observacion' => $data['eve_observacion'] ?? '',
            'eve_notificacion' => $data['eve_notificacion'] ?? 'no',
            'tev_codigo' => $data['tev_codigo'] ?? '',
            'tipo_evento' => $data['tipo_evento'] ?? '',

            // ===== ANTECEDENTES (Tabla t_antecedente_paciente) =====
            'ap_descripcion' => $data['ap_descripcion'] ?? '',
            'ap_no_aplica' => $data['ap_no_aplica'] ?? '',
            'tan_codigo' => $data['tan_codigo'] ?? '',
            'tipo_antecedente' => $data['tipo_antecedente'] ?? '',

            // ===== CONSTANTES VITALES (Tabla t_constantes_vitales) =====
            'con_sin_constantes' => $data['con_sin_constantes'] ?? 0,
            'con_presion_arterial' => $data['con_presion_arterial'] ?? '',
            'con_pulso' => $data['con_pulso'] ?? '',
            'con_frec_respiratoria' => $data['con_frec_respiratoria'] ?? '',
            'con_pulsioximetria' => $data['con_pulsioximetria'] ?? '',
            'con_perimetro_cefalico' => $data['con_perimetro_cefalico'] ?? '',
            'con_peso' => $data['con_peso'] ?? '',
            'con_talla' => $data['con_talla'] ?? '',
            'con_glucemia_capilar' => $data['con_glucemia_capilar'] ?? '',
            'con_reaccion_pupila_der' => $data['con_reaccion_pupila_der'] ?? '',
            'con_reaccion_pupila_izq' => $data['con_reaccion_pupila_izq'] ?? '',
            'con_t_lleno_capilar' => $data['con_t_lleno_capilar'] ?? '',
            'con_glasgow_ocular' => $data['con_glasgow_ocular'] ?? '',
            'con_glasgow_verbal' => $data['con_glasgow_verbal'] ?? '',
            'con_glasgow_motora' => $data['con_glasgow_motora'] ?? '',

            // ===== PROBLEMA ACTUAL (Tabla t_problema_actual) =====
            'pro_descripcion' => $data['pro_descripcion'] ?? '',

            // ===== EXAMEN FÍSICO (Tabla t_examen_fisico) =====
            'ef_descripcion' => $data['ef_descripcion'] ?? '',
            'ef_presente' => $data['ef_presente'] ?? '',
            'zef_codigo' => $data['zef_codigo'] ?? '',
            'zona_examen' => $data['zona_examen'] ?? '',

            // ===== TRAUMA (Tabla t_examen_trauma) =====
            'tra_descripcion' => $data['tra_descripcion'] ?? '',

            // ===== EMBARAZO Y PARTO (Tabla t_embarazo_parto) =====
            'emb_no_aplica' => $data['emb_no_aplica'] ?? 0,
            'emb_numero_gestas' => $data['emb_numero_gestas'] ?? '',
            'emb_numero_partos' => $data['emb_numero_partos'] ?? '',
            'emb_numero_cesareas' => $data['emb_numero_cesareas'] ?? '',
            'emb_numero_abortos' => $data['emb_numero_abortos'] ?? '',
            'emb_fum' => $data['emb_fum'] ?? '',
            'emb_semanas_gestacion' => $data['emb_semanas_gestacion'] ?? '',
            'emb_movimiento_fetal' => $data['emb_movimiento_fetal'] ?? '',
            'emb_frecuencia_cardiaca_fetal' => $data['emb_frecuencia_cardiaca_fetal'] ?? '',
            'emb_ruptura_menbranas' => $data['emb_ruptura_menbranas'] ?? '',
            'emb_tiempo' => $data['emb_tiempo'] ?? '',
            'emb_afu' => $data['emb_afu'] ?? '',
            'emb_presentacion' => $data['emb_presentacion'] ?? '',
            'emb_sangrado_vaginal' => $data['emb_sangrado_vaginal'] ?? '',
            'emb_contracciones' => $data['emb_contracciones'] ?? '',
            'emb_dilatacion' => $data['emb_dilatacion'] ?? '',
            'emb_borramiento' => $data['emb_borramiento'] ?? '',
            'emb_plano' => $data['emb_plano'] ?? '',
            'emb_pelvis_viable' => $data['emb_pelvis_viable'] ?? '',
            'emb_score_mama' => $data['emb_score_mama'] ?? '',
            'emb_observaciones' => $data['emb_observaciones'] ?? '',

            // ===== EXÁMENES COMPLEMENTARIOS (Tabla t_examenes_complementarios) =====
            'exa_no_aplica' => $data['exa_no_aplica'] ?? 0,
            'exa_observaciones' => $data['exa_observaciones'] ?? '',

            // Campos para manejar múltiples exámenes
            'tipos_examenes_seleccionados' => $data['tipos_examenes_seleccionados'] ?? '',
            'examenes_complementarios' => $data['examenes_complementarios'] ?? [],
            'tipo_id' => $data['tipo_id'] ?? '',
            'tipo_nombre' => $data['tipo_nombre'] ?? '',

            // ===== DIAGNÓSTICOS PRESUNTIVOS (Sección L) - MÚLTIPLES =====
            'diagnosticos_presuntivos' => $data['diagnosticos_presuntivos'] ?? [],

            // Campos enumerados para diagnósticos presuntivos
            'diag_pres_desc1' => $data['diag_pres_desc1'] ?? '',
            'diag_pres_cie1' => $data['diag_pres_cie1'] ?? '',
            'diag_pres_desc2' => $data['diag_pres_desc2'] ?? '',
            'diag_pres_cie2' => $data['diag_pres_cie2'] ?? '',
            'diag_pres_desc3' => $data['diag_pres_desc3'] ?? '',
            'diag_pres_cie3' => $data['diag_pres_cie3'] ?? '',

            // Campos legacy para primer diagnóstico presuntivo
            'diagp_descripcion' => $data['diagp_descripcion'] ?? '',
            'diagp_cie' => $data['diagp_cie'] ?? '',

            // ===== DIAGNÓSTICOS DEFINITIVOS (Sección M) - MÚLTIPLES =====
            'diagnosticos_definitivos' => $data['diagnosticos_definitivos'] ?? [],

            // Campos enumerados para diagnósticos definitivos
            'diag_def_desc1' => $data['diag_def_desc1'] ?? '',
            'diag_def_cie1' => $data['diag_def_cie1'] ?? '',
            'diag_def_desc2' => $data['diag_def_desc2'] ?? '',
            'diag_def_cie2' => $data['diag_def_cie2'] ?? '',
            'diag_def_desc3' => $data['diag_def_desc3'] ?? '',
            'diag_def_cie3' => $data['diag_def_cie3'] ?? '',

            // Campos legacy para primer diagnóstico definitivo
            'diagd_descripcion' => $data['diagd_descripcion'] ?? '',
            'diagd_cie' => $data['diagd_cie'] ?? '',

            // ===== TRATAMIENTO (Tabla t_tratamiento) =====
            'tratamientos' => $data['tratamientos'] ?? [],
            'trat_medicamento' => $data['trat_medicamento'] ?? '',
            'trat_via' => $data['trat_via'] ?? '',
            'trat_dosis' => $data['trat_dosis'] ?? '',
            'trat_posologia' => $data['trat_posologia'] ?? '',
            'trat_dias' => $data['trat_dias'] ?? '',
            'plan_tratamiento' => $data['plan_tratamiento'] ?? '',

            // TRATAMIENTOS
            'trat_med1' => $data['trat_med1'] ?? '',
            'trat_via1' => $data['trat_via1'] ?? '',
            'trat_dosis1' => $data['trat_dosis1'] ?? '',
            'trat_posologia1' => $data['trat_posologia1'] ?? '',
            'trat_dias1' => $data['trat_dias1'] ?? '',

            'trat_med2' => $data['trat_med2'] ?? '',
            'trat_via2' => $data['trat_via2'] ?? '',
            'trat_dosis2' => $data['trat_dosis2'] ?? '',
            'trat_posologia2' => $data['trat_posologia2'] ?? '',
            'trat_dias2' => $data['trat_dias2'] ?? '',

            'trat_med3' => $data['trat_med3'] ?? '',
            'trat_via3' => $data['trat_via3'] ?? '',
            'trat_dosis3' => $data['trat_dosis3'] ?? '',
            'trat_posologia3' => $data['trat_posologia3'] ?? '',
            'trat_dias3' => $data['trat_dias3'] ?? '',

            'trat_med4' => $data['trat_med4'] ?? '',
            'trat_via4' => $data['trat_via4'] ?? '',
            'trat_dosis4' => $data['trat_dosis4'] ?? '',
            'trat_posologia4' => $data['trat_posologia4'] ?? '',
            'trat_dias4' => $data['trat_dias4'] ?? '',

            'trat_med5' => $data['trat_med5'] ?? '',
            'trat_via5' => $data['trat_via5'] ?? '',
            'trat_dosis5' => $data['trat_dosis5'] ?? '',
            'trat_posologia5' => $data['trat_posologia5'] ?? '',
            'trat_dias5' => $data['trat_dias5'] ?? '',

            'trat_med6' => $data['trat_med6'] ?? '',
            'trat_via6' => $data['trat_via6'] ?? '',
            'trat_dosis6' => $data['trat_dosis6'] ?? '',
            'trat_posologia6' => $data['trat_posologia6'] ?? '',
            'trat_dias6' => $data['trat_dias6'] ?? '',

            'trat_med7' => $data['trat_med7'] ?? '',
            'trat_via7' => $data['trat_via7'] ?? '',
            'trat_dosis7' => $data['trat_dosis7'] ?? '',
            'trat_posologia7' => $data['trat_posologia7'] ?? '',
            'trat_dias7' => $data['trat_dias7'] ?? '',

            // ===== EGRESO (Tabla t_egreso_emergencia) =====
            'egr_codigo' => $data['egr_codigo'] ?? '',
            'egr_observaciones' => $data['egr_observaciones'] ?? '',
            'egr_dias_reposo' => $data['egr_dias_reposo'] ?? '',

            // Códigos individuales para compatibilidad
            'ese_codigo' => $data['ese_codigo'] ?? '',
            'moe_codigo' => $data['moe_codigo'] ?? '',
            'tie_codigo' => $data['tie_codigo'] ?? '',
            'egr_establecimiento' => $data['egr_establecimiento'] ?? '',
            'tipo_egreso' => $data['tipo_egreso'] ?? '',

            // ARRAYS PARA MÚLTIPLES CHECKBOXES
            'estados_egreso' => $data['estados_egreso'] ?? [],
            'modalidades_egreso' => $data['modalidades_egreso'] ?? [],
            'tipos_egreso' => $data['tipos_egreso'] ?? [],

            // ===== PROFESIONAL RESPONSABLE - IMÁGENES (Sección P) =====
            'pro_fecha' => $data['pro_fecha'] ?? '',
            'pro_hora' => $data['pro_hora'] ?? '',
            'pro_primer_nombre' => $data['pro_primer_nombre'] ?? '',
            'pro_primer_apellido' => $data['pro_primer_apellido'] ?? '',
            'pro_segundo_apellido' => $data['pro_segundo_apellido'] ?? '',
            'pro_nro_documento' => $data['pro_nro_documento'] ?? '',

            // CAMPOS PARA IMÁGENES
            'pro_firma' => $data['pro_firma'] ?? '',
            'pro_sello' => $data['pro_sello'] ?? '',
            'pro_firma_base64' => $data['pro_firma_base64'] ?? '',
            'pro_sello_base64' => $data['pro_sello_base64'] ?? '',
            'pro_firma_existe' => $data['pro_firma_existe'] ?? false,
            'pro_sello_existe' => $data['pro_sello_existe'] ?? false,

            // ===== CÓDIGOS ADICIONALES PARA SELECTS =====
            'gen_codigo' => $data['gen_codigo'] ?? '',
            'seg_codigo' => $data['seg_codigo'] ?? '',
            'gcu_codigo' => $data['gcu_codigo'] ?? '',
            'emp_codigo' => $data['emp_codigo'] ?? '',
            'nac_codigo' => $data['nac_codigo'] ?? '',
            'esc_codigo' => $data['esc_codigo'] ?? '',
            'pue_ind_codigo' => $data['pue_ind_codigo'] ?? '',
            'nac_ind_codigo' => $data['nac_ind_codigo'] ?? '',

            'nedu_codigo' => $data['nedu_codigo'] ?? '',
            'eneduc_codigo' => $data['eneduc_codigo'] ?? '',
            'tdoc_codigo' => $data['tdoc_codigo'] ?? '',

            // ===== INFORMACIÓN DE DEBUG =====
            'debug_original_keys' => array_keys($data),
            'debug_ate_codigo' => $data['ate_codigo'] ?? 'NO_ENCONTRADO',
            'debug_fecha_atencion' => $data['ate_fecha'] ?? 'NO_ENCONTRADO'
        ];
    }

    /**
     * VALIDAR FORMATO DE FECHA YYYY-MM-DD
     */
    private function validarFecha($fecha)
    {
        $d = \DateTime::createFromFormat('Y-m-d', $fecha);
        return $d && $d->format('Y-m-d') === $fecha;
    }

    // ===== RESTO DE MÉTODOS EXISTENTES =====

    public function obtenerDiagnosticosPresuntivos($ate_codigo)
    {
        if (!session()->get('logged_in') || session()->get('rol_id') != 1) {
            return $this->response->setJSON(['success' => false, 'message' => 'No autorizado'])
                ->setStatusCode(401);
        }

        try {
            $busquedaModel = new BusquedaCompletaModel();
            $diagnosticos = $busquedaModel->obtenerDiagnosticosPresuntivos($ate_codigo);

            if (!$diagnosticos || empty($diagnosticos)) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'No se encontraron diagnósticos presuntivos'
                ]);
            }

            return $this->response->setJSON([
                'success' => true,
                'diagnosticos' => $diagnosticos,
                'total' => count($diagnosticos)
            ]);
        } catch (\Exception $e) {

            return $this->response->setJSON([
                'success' => false,
                'message' => 'Error interno del servidor'
            ]);
        }
    }

    public function obtenerDiagnosticosDefinitivos($ate_codigo)
    {
        if (!session()->get('logged_in') || session()->get('rol_id') != 1) {
            return $this->response->setJSON(['success' => false, 'message' => 'No autorizado'])
                ->setStatusCode(401);
        }

        try {
            $busquedaModel = new BusquedaCompletaModel();
            $diagnosticos = $busquedaModel->obtenerDiagnosticosDefinitivos($ate_codigo);

            if (!$diagnosticos || empty($diagnosticos)) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'No se encontraron diagnósticos definitivos'
                ]);
            }

            return $this->response->setJSON([
                'success' => true,
                'diagnosticos' => $diagnosticos,
                'total' => count($diagnosticos)
            ]);
        } catch (\Exception $e) {

            return $this->response->setJSON([
                'success' => false,
                'message' => 'Error interno del servidor'
            ]);
        }
    }

    public function obtenerDatosEgreso($ate_codigo)
    {
        $egreso = $this->EgresoEmergenciaModel->where('ate_codigo', $ate_codigo)->first();

        if (!$egreso) {
            return $this->response->setJSON(['error' => 'No se encontró información de egreso']);
        }

        $response = [
            'ese_codigo' => (array)$egreso['ese_codigo'],
            'moe_codigo' => (array)$egreso['moe_codigo'],
            'tie_codigo' => (array)$egreso['tie_codigo'],
            'egr_observaciones' => $egreso['egr_observaciones'],
            'egr_dias_reposo' => $egreso['egr_dias_reposo']
        ];

        return $this->response->setJSON($response);
    }

    /**
     *  MÉTODO PARA LISTAR PACIENTES POR FECHA (PARA LA TABLA)
     */
    public function listarPacientesPorFecha()
    {
        if (!session()->get('logged_in')) {
            return $this->response->setJSON(['success' => false, 'message' => 'No autorizado'])->setStatusCode(401);
        }

        if (session()->get('rol_id') != 1) {
            return $this->response->setJSON(['success' => false, 'message' => 'Sin permisos para esta sección'])->setStatusCode(403);
        }

        try {
            $fecha = $this->request->getPost('fecha');

            if (empty($fecha)) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'La fecha es requerida'
                ]);
            }

            if (!$this->validarFecha($fecha)) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Formato de fecha inválido. Use YYYY-MM-DD'
                ]);
            }

            $busquedaModel = new BusquedaCompletaModel();
            $resultados = $busquedaModel->obtenerPorFechaDatetime($fecha);

            if (empty($resultados)) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => "No se encontraron pacientes para la fecha '$fecha'"
                ]);
            }

            $pacientesFormateados = [];
            foreach ($resultados as $registro) {
                $identificador = $registro['pac_cedula'] ?: $registro['pac_his_cli'];

                $pacientesFormateados[] = [
                    'pac_his_cli' => $registro['pac_his_cli'] ?? '',
                    'pac_cedula' => $registro['pac_cedula'] ?? '',
                    'pac_apellidos' => $registro['pac_apellidos'] ?? '',
                    'pac_nombres' => $registro['pac_nombres'] ?? '',
                    'ate_fecha' => $registro['ate_fecha'] ?? '',
                    'ate_hora' => $registro['ate_hora'] ?? '',
                    'identificador' => $identificador,
                    'usuario_nombre_completo' => $registro['usuario_nombre_completo'] ?? '',
                    'est_num_archivo' => $registro['est_num_archivo'] ?? ''
                ];
            }

            return $this->response->setJSON([
                'success' => true,
                'message' => 'Se encontraron ' . count($pacientesFormateados) . ' paciente(s)',
                'data' => $pacientesFormateados,
                'total' => count($pacientesFormateados)
            ]);
        } catch (\Exception $e) {

            return $this->response->setJSON([
                'success' => false,
                'message' => 'Error interno del servidor: ' . $e->getMessage()
            ]);
        }
    }
}
