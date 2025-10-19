<?php

namespace App\Models\Administrador;

use CodeIgniter\Model;

class BusquedaCompletaModel extends Model
{
    protected $returnType = 'array';
    protected $table = 't_atencion';

    public function obtenerPorIdentificadorYFecha(string $identificador, string $fecha): array
    {
        try {
            $ini = $fecha . ' 00:00:00';
            $fin = date('Y-m-d H:i:s', strtotime($fecha . ' +1 day'));

            // CONSULTA EN DOS PASOS
            // PASO 1: Obtener solo ate_codigo y datos básicos del paciente (RÁPIDO)
            $sql_basico = "SELECT
                a.ate_codigo,
                a.pac_codigo,
                a.ate_fecha,
                a.ate_hora,
                a.ate_colores,
                a.ate_custodia_policial,
                a.ate_aliento_etilico,
                a.ate_fuente_informacion,
                a.ate_ins_entrega_paciente,
                a.ate_telefono,
                a.lleg_codigo,
                p.pac_his_cli,
                p.pac_cedula,
                p.pac_apellidos,
                p.pac_nombres,
                p.pac_fecha_nac,
                p.pac_lugar_nac,
                p.pac_edad_valor,
                p.pac_edad_unidad,
                p.pac_grupo_prioritario,
                p.pac_grupo_sanguineo,
                p.pac_telefono_fijo,
                p.pac_telefono_celular,
                p.pac_ocupacion,
                p.pac_direccion,
                p.pac_provincias,
                p.pac_cantones,
                p.pac_parroquias,
                p.pac_barrio,
                p.pac_calle_secundaria,
                p.pac_referencia,
                p.pac_avisar_a,
                p.pac_parentezco_avisar_a,
                p.pac_direccion_avisar,
                p.pac_telefono_avisar_a,
                p.gen_codigo,
                p.seg_codigo,
                p.gcu_codigo,
                p.emp_codigo,
                p.nac_codigo,
                p.esc_codigo,
                p.nedu_codigo,
                p.eneduc_codigo,
                p.tdoc_codigo,
                p.nac_ind_codigo,
                p.pue_ind_codigo
            FROM t_atencion a
            INNER JOIN t_paciente p ON p.pac_codigo = a.pac_codigo
            WHERE (p.pac_cedula = ? OR p.pac_his_cli = ?)
              AND a.ate_fecha >= ? AND a.ate_fecha < ?
            ORDER BY a.ate_fecha DESC, a.ate_hora DESC
            LIMIT 1";

            $resultado_basico = $this->db->query($sql_basico, [$identificador, $identificador, $ini, $fin]);
            $data = $resultado_basico->getRowArray();

            if (empty($data)) {
                return [];
            }

            $ate_codigo = $data['ate_codigo'];

            // PASO 2: Cargar catálogos y datos relacionados solo si hay resultado
            $data = $this->cargarCatalogosDescriptivos($data);
            $data = $this->cargarDatosMedicos($data, $ate_codigo);

            // AGREGAR CÓDIGOS MÚLTIPLES Y TRATAMIENTOS
            $data = $this->agregarCodigosMultiples($data, $ate_codigo);
            $data = $this->agregarTratamientosCompletos($data, $ate_codigo);
            $data = $this->agregarExamenesComplementarios($data, $ate_codigo);
            $data = $this->agregarDiagnosticosPresuntivos($data, $ate_codigo);
            $data = $this->agregarDiagnosticosDefinitivos($data, $ate_codigo);
            $data = $this->procesarImagenesProfesional($data);

            return [$data];
        } catch (\Exception $e) {
            log_message('error', "ERROR EN MODELO: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Cargar catálogos descriptivos en una sola consulta
     */
    private function cargarCatalogosDescriptivos($data)
    {
        // Solo cargar los códigos que existen
        $codigos = array_filter([
            'gen' => $data['gen_codigo'] ?? null,
            'seg' => $data['seg_codigo'] ?? null,
            'gcu' => $data['gcu_codigo'] ?? null,
            'emp' => $data['emp_codigo'] ?? null,
            'nac' => $data['nac_codigo'] ?? null,
            'esc' => $data['esc_codigo'] ?? null,
            'nedu' => $data['nedu_codigo'] ?? null,
            'eneduc' => $data['eneduc_codigo'] ?? null,
            'tdoc' => $data['tdoc_codigo'] ?? null,
            'lleg' => $data['lleg_codigo'] ?? null,
            'nac_ind' => $data['nac_ind_codigo'] ?? null,
            'pue_ind' => $data['pue_ind_codigo'] ?? null
        ]);

        // Cargar descripciones solo si hay códigos
        if (!empty($codigos['gen'])) {
            $gen = $this->db->query("SELECT gen_descripcion FROM t_genero WHERE gen_codigo = ?", [$codigos['gen']])->getRowArray();
            $data['genero'] = $gen['gen_descripcion'] ?? '';
        }

        if (!empty($codigos['seg'])) {
            $seg = $this->db->query("SELECT seg_descripcion FROM t_seguro_social WHERE seg_codigo = ?", [$codigos['seg']])->getRowArray();
            $data['seguro'] = $seg['seg_descripcion'] ?? '';
        }

        if (!empty($codigos['gcu'])) {
            $gcu = $this->db->query("SELECT gcu_descripcion FROM t_grupo_cultural WHERE gcu_codigo = ?", [$codigos['gcu']])->getRowArray();
            $data['grupo_cultural'] = $gcu['gcu_descripcion'] ?? '';
        }

        if (!empty($codigos['emp'])) {
            $emp = $this->db->query("SELECT emp_descripcion FROM t_empresa WHERE emp_codigo = ?", [$codigos['emp']])->getRowArray();
            $data['empresa'] = $emp['emp_descripcion'] ?? '';
        }

        if (!empty($codigos['nac'])) {
            $nac = $this->db->query("SELECT nac_descripcion FROM t_nacionalidad WHERE nac_codigo = ?", [$codigos['nac']])->getRowArray();
            $data['nacionalidad'] = $nac['nac_descripcion'] ?? '';
        }

        if (!empty($codigos['esc'])) {
            $esc = $this->db->query("SELECT esc_descripcion FROM t_estado_civil WHERE esc_codigo = ?", [$codigos['esc']])->getRowArray();
            $data['estado_civil'] = $esc['esc_descripcion'] ?? '';
        }

        if (!empty($codigos['nedu'])) {
            $nedu = $this->db->query("SELECT nedu_nivel FROM t_nivel_educ WHERE nedu_codigo = ?", [$codigos['nedu']])->getRowArray();
            $data['nivel_educacion'] = $nedu['nedu_nivel'] ?? '';
        }

        if (!empty($codigos['eneduc'])) {
            $eneduc = $this->db->query("SELECT eneduc_estado FROM t_esta_niv_educ WHERE eneduc_codigo = ?", [$codigos['eneduc']])->getRowArray();
            $data['estado_nivel_educ'] = $eneduc['eneduc_estado'] ?? '';
        }

        if (!empty($codigos['tdoc'])) {
            $tdoc = $this->db->query("SELECT tdoc_descripcion FROM t_tipo_documento WHERE tdoc_codigo = ?", [$codigos['tdoc']])->getRowArray();
            $data['tipo_documento'] = $tdoc['tdoc_descripcion'] ?? '';
        }

        if (!empty($codigos['lleg'])) {
            $lleg = $this->db->query("SELECT lleg_descripcion FROM t_llegada WHERE lleg_codigo = ?", [$codigos['lleg']])->getRowArray();
            $data['forma_llegada'] = $lleg['lleg_descripcion'] ?? '';
        }

        if (!empty($codigos['nac_ind'])) {
            $nac_ind = $this->db->query("SELECT nac_ind_nombre FROM t_nacionalidad_indigena WHERE nac_ind_codigo = ?", [$codigos['nac_ind']])->getRowArray();
            $data['nacionalidad_indigena'] = $nac_ind['nac_ind_nombre'] ?? '';
        }

        if (!empty($codigos['pue_ind'])) {
            $pue_ind = $this->db->query("SELECT pue_ind_nombre FROM t_pueblo_indigena WHERE pue_ind_codigo = ?", [$codigos['pue_ind']])->getRowArray();
            $data['pueblo_indigena'] = $pue_ind['pue_ind_nombre'] ?? '';
        }

        return $data;
    }

    /**
     * Cargar datos médicos en consultas separadas y eficientes
     */
    private function cargarDatosMedicos($data, $ate_codigo)
    {
        // Establecimiento y usuario
        $er = $this->db->query("SELECT er.est_num_archivo, CONCAT(u.usu_nombre, ' ', u.usu_apellido) AS usuario_nombre_completo
            FROM t_establecimiento_registro er
            LEFT JOIN t_usuario u ON u.usu_id = er.usu_id
            WHERE er.ate_codigo = ? LIMIT 1", [$ate_codigo])->getRowArray();
        $data['est_num_archivo'] = $er['est_num_archivo'] ?? '';
        $data['usuario_nombre_completo'] = $er['usuario_nombre_completo'] ?? '';

        // Inicio atención
        $iat = $this->db->query("SELECT iat.*, cl.col_descripcion AS condicion_llegada
            FROM t_inicio_atencion iat
            LEFT JOIN t_condicion_llegada cl ON cl.col_codigo = iat.col_codigo
            WHERE iat.ate_codigo = ? LIMIT 1", [$ate_codigo])->getRowArray();
        if ($iat) {
            $data['iat_fecha'] = $iat['iat_fecha'] ?? '';
            $data['iat_hora'] = $iat['iat_hora'] ?? '';
            $data['iat_motivo'] = $iat['iat_motivo'] ?? '';
            $data['col_codigo'] = $iat['col_codigo'] ?? '';
            $data['condicion_llegada'] = $iat['condicion_llegada'] ?? '';
        }

        // Evento
        $ev = $this->db->query("SELECT * FROM t_evento WHERE ate_codigo = ? LIMIT 1", [$ate_codigo])->getRowArray();
        if ($ev) {
            $data['eve_fecha'] = $ev['eve_fecha'] ?? '';
            $data['eve_hora'] = $ev['eve_hora'] ?? '';
            $data['eve_lugar'] = $ev['eve_lugar'] ?? '';
            $data['eve_direccion'] = $ev['eve_direccion'] ?? '';
            $data['eve_observacion'] = $ev['eve_observacion'] ?? '';
            $data['eve_notificacion'] = $ev['eve_notificacion'] ?? '';
        }

        // Antecedentes
        $ap = $this->db->query("SELECT * FROM t_antecedente_paciente WHERE ate_codigo = ? LIMIT 1", [$ate_codigo])->getRowArray();
        if ($ap) {
            $data['ap_descripcion'] = $ap['ap_descripcion'] ?? '';
            $data['ap_no_aplica'] = $ap['ap_no_aplica'] ?? '';
        }

        // Problema actual
        $pa = $this->db->query("SELECT * FROM t_problema_actual WHERE ate_codigo = ? LIMIT 1", [$ate_codigo])->getRowArray();
        if ($pa) {
            $data['pro_descripcion'] = $pa['pro_descripcion'] ?? '';
        }

        // Constantes vitales
        $cv = $this->db->query("SELECT * FROM t_constantes_vitales WHERE ate_codigo = ? LIMIT 1", [$ate_codigo])->getRowArray();
        if ($cv) {
            $data = array_merge($data, $cv);
        }

        // Examen físico
        $ef = $this->db->query("SELECT * FROM t_examen_fisico WHERE ate_codigo = ? LIMIT 1", [$ate_codigo])->getRowArray();
        if ($ef) {
            $data['ef_presente'] = $ef['ef_presente'] ?? '';
            $data['ef_descripcion'] = $ef['ef_descripcion'] ?? '';
        }

        // Trauma
        $tra = $this->db->query("SELECT * FROM t_examen_trauma WHERE ate_codigo = ? LIMIT 1", [$ate_codigo])->getRowArray();
        if ($tra) {
            $data['tra_descripcion'] = $tra['tra_descripcion'] ?? '';
        }

        // Embarazo
        $emb = $this->db->query("SELECT * FROM t_embarazo_parto WHERE ate_codigo = ? LIMIT 1", [$ate_codigo])->getRowArray();
        if ($emb) {
            $data = array_merge($data, $emb);
        }

        // Exámenes complementarios
        $exa = $this->db->query("SELECT * FROM t_examenes_complementarios WHERE ate_codigo = ? LIMIT 1", [$ate_codigo])->getRowArray();
        if ($exa) {
            $data['exa_no_aplica'] = $exa['exa_no_aplica'] ?? '';
            $data['exa_observaciones'] = $exa['exa_observaciones'] ?? '';
        }

        // Diagnósticos (solo primer registro)
        $diagp = $this->db->query("SELECT * FROM t_diagnostico_presuntivo WHERE ate_codigo = ? LIMIT 1", [$ate_codigo])->getRowArray();
        if ($diagp) {
            $data['diagp_descripcion'] = $diagp['diagp_descripcion'] ?? '';
            $data['diagp_cie'] = $diagp['diagp_cie'] ?? '';
        }

        $diagd = $this->db->query("SELECT * FROM t_diagnostico_definitivo WHERE ate_codigo = ? LIMIT 1", [$ate_codigo])->getRowArray();
        if ($diagd) {
            $data['diagd_descripcion'] = $diagd['diagd_descripcion'] ?? '';
            $data['diagd_cie'] = $diagd['diagd_cie'] ?? '';
        }

        // Egreso
        $egr = $this->db->query("SELECT * FROM t_egreso_emergencia WHERE ate_codigo = ? LIMIT 1", [$ate_codigo])->getRowArray();
        if ($egr) {
            $data = array_merge($data, $egr);
        }

        // Profesional
        $pr = $this->db->query("SELECT * FROM t_profesional_responsable WHERE ate_codigo = ? LIMIT 1", [$ate_codigo])->getRowArray();
        if ($pr) {
            $data = array_merge($data, $pr);
        }

        return $data;
    }

    private function agregarCodigosMultiples($data, $ate_codigo)
    {
        try {
            // Tipos de evento
            $sql_eventos = "SELECT GROUP_CONCAT(tev_codigo) AS codigos FROM t_evento WHERE ate_codigo = ?";
            $eventos = $this->db->query($sql_eventos, [$ate_codigo])->getRowArray();
            $data['tev_codigo'] = $eventos['codigos'] ?? '';

            // Antecedentes
            $sql_antecedentes = "SELECT GROUP_CONCAT(tan_codigo) AS codigos FROM t_antecedente_paciente WHERE ate_codigo = ?";
            $antecedentes = $this->db->query($sql_antecedentes, [$ate_codigo])->getRowArray();
            $data['tan_codigo'] = $antecedentes['codigos'] ?? '';

            // Zonas de examen físico
            $sql_zonas = "SELECT GROUP_CONCAT(zef_codigo) AS codigos FROM t_examen_fisico WHERE ate_codigo = ?";
            $zonas = $this->db->query($sql_zonas, [$ate_codigo])->getRowArray();
            $data['zef_codigo'] = $zonas['codigos'] ?? '';

            // Tipos de exámenes
            $sql_examenes = "SELECT GROUP_CONCAT(tipo_id) AS codigos FROM t_examenes_complementarios WHERE ate_codigo = ?";
            $examenes = $this->db->query($sql_examenes, [$ate_codigo])->getRowArray();
            $data['tipo_id'] = $examenes['codigos'] ?? '';

            // Estados, modalidades y tipos de egreso - MÚLTIPLES REGISTROS
            $sql_egreso = "SELECT ese_codigo, moe_codigo, tie_codigo FROM t_egreso_emergencia WHERE ate_codigo = ?";
            $egresos = $this->db->query($sql_egreso, [$ate_codigo])->getResultArray();


            if (!empty($egresos)) {
                // Separar códigos por tipo y crear arrays
                $estadosEgreso = [];
                $modalidadesEgreso = [];
                $tiposEgreso = [];

                foreach ($egresos as $egreso) {
                    if (!empty($egreso['ese_codigo'])) {
                        $estadosEgreso[] = $egreso['ese_codigo'];
                    }
                    if (!empty($egreso['moe_codigo'])) {
                        $modalidadesEgreso[] = $egreso['moe_codigo'];
                    }
                    if (!empty($egreso['tie_codigo'])) {
                        $tiposEgreso[] = $egreso['tie_codigo'];
                    }
                }

                // Asignar arrays de códigos para múltiples checkboxes
                $data['estados_egreso'] = $estadosEgreso;
                $data['modalidades_egreso'] = $modalidadesEgreso;
                $data['tipos_egreso'] = $tiposEgreso;

                // Mantener compatibilidad con código legacy (primer elemento)
                $data['ese_codigo'] = !empty($estadosEgreso) ? $estadosEgreso[0] : '';
                $data['moe_codigo'] = !empty($modalidadesEgreso) ? $modalidadesEgreso[0] : '';
                $data['tie_codigo'] = !empty($tiposEgreso) ? $tiposEgreso[0] : '';

            }

            return $data;
        } catch (\Exception $e) {
            log_message('error', "ERROR agregando códigos múltiples: " . $e->getMessage());
            return $data;
        }
    }
    private function agregarExamenesComplementarios($data, $ate_codigo)
    {
        try {
            // OBTENER TODOS LOS EXÁMENES COMPLEMENTARIOS
            $sql_examenes = "
            SELECT 
                ec.tipo_id,
                ec.exa_no_aplica,
                ec.exa_observaciones,
                te.tipo_nombre
            FROM t_examenes_complementarios ec
            LEFT JOIN t_tipos_examen te ON ec.tipo_id = te.tipo_id
            WHERE ec.ate_codigo = ? 
            ORDER BY ec.tipo_id ASC
        ";

            $examenes = $this->db->query($sql_examenes, [$ate_codigo])->getResultArray();
            $totalExamenes = count($examenes);

            if ($totalExamenes > 0) {
                // FORMATO 1: Array completo de exámenes
                $data['examenes_complementarios'] = $examenes;

                // FORMATO 2: Verificar si hay "No aplica"
                $hayNoAplica = false;
                $tiposSeleccionados = [];
                $observacionesExamenes = '';

                foreach ($examenes as $examen) {
                    if ($examen['exa_no_aplica'] == 1) {
                        $hayNoAplica = true;
                    } else {
                        $tiposSeleccionados[] = $examen['tipo_id'];
                        if (!empty($examen['exa_observaciones'])) {
                            $observacionesExamenes = $examen['exa_observaciones'];
                        }
                    }
                }

                // FORMATO 3: Campos para el formulario
                $data['exa_no_aplica'] = $hayNoAplica ? 1 : 0;
                $data['tipos_examenes_seleccionados'] = implode(',', $tiposSeleccionados);
                $data['exa_observaciones'] = $observacionesExamenes;

                // FORMATO 4: Campo legacy (primer examen)
                $data['tipo_id'] = !empty($tiposSeleccionados) ? $tiposSeleccionados[0] : '';

            } else {
                // LIMPIAR SI NO HAY EXÁMENES
                $data['examenes_complementarios'] = [];
                $data['exa_no_aplica'] = 0;
                $data['tipos_examenes_seleccionados'] = '';
                $data['exa_observaciones'] = '';
                $data['tipo_id'] = '';

            }

            return $data;
        } catch (\Exception $e) {
            log_message('error', "ERROR agregando exámenes complementarios: " . $e->getMessage());
            return $data;
        }
    }
    private function agregarDiagnosticosPresuntivos($data, $ate_codigo)
    {
        try {
            // OBTENER TODOS LOS DIAGNÓSTICOS PRESUNTIVOS PARA EL ATE_CODIGO
            $sql_diagp = "
            SELECT 
                diagp_id,
                diagp_descripcion,
                diagp_cie
            FROM t_diagnostico_presuntivo 
            WHERE ate_codigo = ? 
            ORDER BY diagp_id ASC
            LIMIT 3
        ";

            $diagnosticos = $this->db->query($sql_diagp, [$ate_codigo])->getResultArray();
            $totalDiagnosticos = count($diagnosticos);

            if ($totalDiagnosticos > 0) {
                // FORMATO 1: Array completo de diagnósticos
                $data['diagnosticos_presuntivos'] = $diagnosticos;

                // FORMATO 2: Campos enumerados (diag_pres_desc1, diag_pres_desc2, diag_pres_desc3)
                for ($i = 0; $i < min($totalDiagnosticos, 3); $i++) {
                    $num = $i + 1;
                    $data["diag_pres_desc{$num}"] = $diagnosticos[$i]['diagp_descripcion'] ?? '';
                    $data["diag_pres_cie{$num}"] = $diagnosticos[$i]['diagp_cie'] ?? '';
                }

                // FORMATO 3: Campos legacy (SOLO primer diagnóstico)
                $data['diagp_descripcion'] = $diagnosticos[0]['diagp_descripcion'] ?? '';
                $data['diagp_cie'] = $diagnosticos[0]['diagp_cie'] ?? '';

            } else {
                // LIMPIAR SI NO HAY DIAGNÓSTICOS
                $data['diagnosticos_presuntivos'] = [];
                $data['diagp_descripcion'] = '';
                $data['diagp_cie'] = '';

                for ($i = 1; $i <= 3; $i++) {
                    $data["diag_pres_desc{$i}"] = '';
                    $data["diag_pres_cie{$i}"] = '';
                }

            }

            return $data;
        } catch (\Exception $e) {
            log_message('error', "ERROR agregando diagnósticos presuntivos: " . $e->getMessage());
            return $data;
        }
    }

    /**
     * Agregar diagnósticos definitivos completos
     */
    private function agregarDiagnosticosDefinitivos($data, $ate_codigo)
    {
        try {
            // OBTENER TODOS LOS DIAGNÓSTICOS DEFINITIVOS PARA EL ATE_CODIGO
            $sql_diagd = "
            SELECT 
                diagd_id,
                diagd_descripcion,
                diagd_cie
            FROM t_diagnostico_definitivo 
            WHERE ate_codigo = ? 
            ORDER BY diagd_id ASC
            LIMIT 3
        ";

            $diagnosticos = $this->db->query($sql_diagd, [$ate_codigo])->getResultArray();
            $totalDiagnosticos = count($diagnosticos);

            if ($totalDiagnosticos > 0) {
                // FORMATO 1: Array completo de diagnósticos
                $data['diagnosticos_definitivos'] = $diagnosticos;

                // FORMATO 2: Campos enumerados (diag_def_desc1, diag_def_desc2, diag_def_desc3)
                for ($i = 0; $i < min($totalDiagnosticos, 3); $i++) {
                    $num = $i + 1;
                    $data["diag_def_desc{$num}"] = $diagnosticos[$i]['diagd_descripcion'] ?? '';
                    $data["diag_def_cie{$num}"] = $diagnosticos[$i]['diagd_cie'] ?? '';
                }

                // FORMATO 3: Campos legacy (SOLO primer diagnóstico)
                $data['diagd_descripcion'] = $diagnosticos[0]['diagd_descripcion'] ?? '';
                $data['diagd_cie'] = $diagnosticos[0]['diagd_cie'] ?? '';

            } else {
                // LIMPIAR SI NO HAY DIAGNÓSTICOS
                $data['diagnosticos_definitivos'] = [];
                $data['diagd_descripcion'] = '';
                $data['diagd_cie'] = '';

                for ($i = 1; $i <= 3; $i++) {
                    $data["diag_def_desc{$i}"] = '';
                    $data["diag_def_cie{$i}"] = '';
                }

            }

            return $data;
        } catch (\Exception $e) {
            log_message('error', "ERROR agregando diagnósticos definitivos: " . $e->getMessage());
            return $data;
        }
    }
    /**
     * MÉTODO FINAL: AGREGAR TODOS LOS TRATAMIENTOS EN MÚLTIPLES FORMATOS
     */
    private function agregarTratamientosCompletos($data, $ate_codigo)
    {
        try {
            // OBTENER TODOS LOS TRATAMIENTOS ORDENADOS
            $sql_tratamientos = "
                SELECT 
                    trat_medicamento,
                    trat_via,
                    trat_dosis,
                    trat_posologia,
                    trat_dias,
                    trat_observaciones
                FROM t_tratamiento 
                WHERE ate_codigo = ? 
                ORDER BY trat_id ASC
            ";

            $tratamientos = $this->db->query($sql_tratamientos, [$ate_codigo])->getResultArray();
            $totalTratamientos = count($tratamientos);

            if ($totalTratamientos > 0) {
                // FORMATO 1: Array completo de tratamientos
                $data['tratamientos'] = $tratamientos;

                // FORMATO 2: Campos enumerados (trat_med1, trat_med2, etc.) - HASTA 7
                for ($i = 0; $i < $totalTratamientos && $i < 7; $i++) {
                    $num = $i + 1;
                    $data["trat_med{$num}"] = $tratamientos[$i]['trat_medicamento'] ?? '';
                    $data["trat_via{$num}"] = $tratamientos[$i]['trat_via'] ?? '';
                    $data["trat_dosis{$num}"] = $tratamientos[$i]['trat_dosis'] ?? '';
                    $data["trat_posologia{$num}"] = $tratamientos[$i]['trat_posologia'] ?? '';
                    $data["trat_dias{$num}"] = $tratamientos[$i]['trat_dias'] ?? '';
                }

                // FORMATO 3: Campos legacy (SOLO primer tratamiento)
                $data['trat_medicamento'] = $tratamientos[0]['trat_medicamento'] ?? '';
                $data['trat_via'] = $tratamientos[0]['trat_via'] ?? '';
                $data['trat_dosis'] = $tratamientos[0]['trat_dosis'] ?? '';
                $data['trat_posologia'] = $tratamientos[0]['trat_posologia'] ?? '';
                $data['trat_dias'] = $tratamientos[0]['trat_dias'] ?? '';

                // OBSERVACIONES (del primer tratamiento que las tenga)
                $observacionesGenerales = '';
                foreach ($tratamientos as $trat) {
                    if (!empty($trat['trat_observaciones'])) {
                        $observacionesGenerales = $trat['trat_observaciones'];
                        break;
                    }
                }
                $data['plan_tratamiento'] = $observacionesGenerales;
                $data['trat_observaciones'] = $observacionesGenerales;

            } else {
                // LIMPIAR SI NO HAY TRATAMIENTOS
                $data['tratamientos'] = [];
                $data['trat_observaciones'] = '';
                $data['trat_medicamento'] = '';
                $data['trat_via'] = '';
                $data['trat_dosis'] = '';
                $data['trat_posologia'] = '';
                $data['trat_dias'] = '';

                for ($i = 1; $i <= 7; $i++) {
                    $data["trat_med{$i}"] = '';
                    $data["trat_via{$i}"] = '';
                    $data["trat_dosis{$i}"] = '';
                    $data["trat_posologia{$i}"] = '';
                    $data["trat_dias{$i}"] = '';
                }

            }

            return $data;
        } catch (\Exception $e) {
            log_message('error', "ERROR agregando tratamientos: " . $e->getMessage());
            return $data;
        }
    }
    private function procesarImagenesProfesional($data)
    {
        try {

            // Procesar firma
            if (!empty($data['pro_firma'])) {
                $rutaFirma = $data['pro_firma'];

                // Verificar si el archivo existe - CORREGIDO para usar la ruta completa como en médicos
                $rutaCompleta = FCPATH . $rutaFirma;
                if (file_exists($rutaCompleta)) {
                    // Convertir a base64 para mostrar en el navegador
                    $tipoMime = $this->obtenerTipoMime($rutaCompleta);
                    $contenidoBase64 = base64_encode(file_get_contents($rutaCompleta));
                    $data['pro_firma_base64'] = "data:{$tipoMime};base64,{$contenidoBase64}";
                    $data['pro_firma_ruta'] = base_url($rutaFirma); // Ruta web directa
                    $data['pro_firma_existe'] = true;

                } else {
                    $data['pro_firma_base64'] = '';
                    $data['pro_firma_existe'] = false;
                }
            } else {
                $data['pro_firma_base64'] = '';
                $data['pro_firma_existe'] = false;
            }

            // Procesar sello
            if (!empty($data['pro_sello'])) {
                $rutaSello = $data['pro_sello'];

                // Verificar si el archivo existe - CORREGIDO para usar la ruta completa como en médicos
                $rutaCompleta = FCPATH . $rutaSello;
                if (file_exists($rutaCompleta)) {
                    // Convertir a base64 para mostrar en el navegador
                    $tipoMime = $this->obtenerTipoMime($rutaCompleta);
                    $contenidoBase64 = base64_encode(file_get_contents($rutaCompleta));
                    $data['pro_sello_base64'] = "data:{$tipoMime};base64,{$contenidoBase64}";
                    $data['pro_sello_ruta'] = base_url($rutaSello); // Ruta web directa
                    $data['pro_sello_existe'] = true;

                } else {
                    $data['pro_sello_base64'] = '';
                    $data['pro_sello_existe'] = false;
                }
            } else {
                $data['pro_sello_base64'] = '';
                $data['pro_sello_existe'] = false;
            }

            return $data;
        } catch (\Exception $e) {
            log_message('error', "ERROR procesando imágenes del profesional: " . $e->getMessage());

            // En caso de error, establecer valores por defecto
            $data['pro_firma_base64'] = '';
            $data['pro_firma_existe'] = false;
            $data['pro_sello_base64'] = '';
            $data['pro_sello_existe'] = false;

            return $data;
        }
    }

    /**
     * HELPER: Obtener tipo MIME de un archivo
     */
    private function obtenerTipoMime($rutaArchivo)
    {
        $extension = strtolower(pathinfo($rutaArchivo, PATHINFO_EXTENSION));

        switch ($extension) {
            case 'jpg':
            case 'jpeg':
                return 'image/jpeg';
            case 'png':
                return 'image/png';
            case 'gif':
                return 'image/gif';
            case 'webp':
                return 'image/webp';
            default:
                return 'image/jpeg'; // Por defecto
        }
    }

    /**
     * Obtener datos de evolución y prescripciones por fecha
     * Busca evoluciones para el formulario 005 en la vista dual
     */
    public function obtenerEvolucionPorFecha(string $identificador, string $fecha): array
    {
        try {
            // Solo obtener ate_codigo con consulta ligera en lugar de toda la data pesada
            $ate_codigo = $this->obtenerAteCodigoOptimizado($identificador, $fecha);

            if (!$ate_codigo) {
                return [];
            }

            // Usar obtenerPorAtencion en lugar de obtenerPorFecha
            // Esto trae TODAS las evoluciones relacionadas con la atención, sin importar fechas específicas
            $evolucionModel = new \App\Models\Especialidades\EvolucionPrescripcionesModel();

            $evoluciones = $evolucionModel->obtenerPorAtencion($ate_codigo);

            if (empty($evoluciones)) {
                return [];
            }

            // Formatear datos para el frontend
            $evolucionesFormateadas = [];
            foreach ($evoluciones as $evolucion) {
                $formateada = [
                    'ep_codigo' => $evolucion['ep_codigo'] ?? '',
                    'fecha' => $evolucion['ep_fecha'] ?? '',
                    'hora' => $evolucion['ep_hora'] ?? '',
                    'notas_evolucion' => $evolucion['ep_notas_evolucion'] ?? '',
                    'farmacoterapia' => $evolucion['ep_farmacoterapia'] ?? '',
                    'administrado' => $evolucion['ep_administrado'] ?? 0,
                    'numero_hoja' => $evolucion['ep_numero_hoja'] ?? ''
                ];
                $evolucionesFormateadas[] = $formateada;
            }

            $resultado = [
                'fecha' => $fecha,
                'ate_codigo' => $ate_codigo,
                'evoluciones' => $evolucionesFormateadas
            ];

            return $resultado;

        } catch (\Exception $e) {
            throw $e;
        }
    }

    /**
     * MÉTODO OPTIMIZADO: Obtener solo ate_codigo sin JOINs pesados
     * Usado específicamente para mejorar la velocidad del formulario 005
     */
    private function obtenerAteCodigoOptimizado(string $identificador, string $fecha): ?int
    {
        try {
            $ini = $fecha . ' 00:00:00';
            $fin = date('Y-m-d H:i:s', strtotime($fecha . ' +1 day'));

            // CONSULTA ULTRA-LIGERA: Solo las tablas esenciales
            $sql = "SELECT a.ate_codigo
                    FROM t_atencion a
                    JOIN t_paciente p ON p.pac_codigo = a.pac_codigo
                    WHERE (p.pac_cedula = ? OR p.pac_his_cli = ?)
                      AND a.ate_fecha >= ? AND a.ate_fecha < ?
                    ORDER BY a.ate_fecha DESC, a.ate_hora DESC
                    LIMIT 1";

            $resultado = $this->db->query($sql, [$identificador, $identificador, $ini, $fin]);
            $datos = $resultado->getRowArray();

            if (empty($datos)) {
                return null;
            }

            return (int)$datos['ate_codigo'];

        } catch (\Exception $e) {
            log_message('error', 'Error en obtenerAteCodigoOptimizado: ' . $e->getMessage());
            return null;
        }
    }

}
