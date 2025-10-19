<?php

namespace App\Controllers\Especialidades;

use App\Controllers\BaseController;
use App\Models\Administrador\UsuarioModel;

class ReportesController extends BaseController
{
    /**
     * Autenticar usuario para acceso a reportes
     */
    public function autenticar()
    {
        if ($this->request->getMethod() !== 'POST') {
            return $this->response->setJSON(['success' => false, 'error' => 'Método no permitido']);
        }

        $usuario = $this->request->getPost('usuario');
        $password = $this->request->getPost('password');

        if (!$usuario || !$password) {
            return $this->response->setJSON(['success' => false, 'error' => 'Usuario y contraseña son requeridos']);
        }

        try {
            $usuarioModel = new UsuarioModel();

            // Buscar usuario activo con datos del rol
            $user = $usuarioModel->select('t_usuario.*, t_rol.rol_nombre')
                ->join('t_rol', 't_rol.rol_id = t_usuario.rol_id')
                ->where('t_usuario.usu_usuario', $usuario)
                ->where('t_usuario.usu_estado', 'activo')
                ->first();

            if (!$user) {
                return $this->response->setJSON(['success' => false, 'error' => 'Credenciales incorrectas']);
            }

            // Verificar contraseña (SHA256)
            $passwordHash = hash('sha256', $password);
            if ($user['usu_password'] !== $passwordHash) {
                return $this->response->setJSON(['success' => false, 'error' => 'Credenciales incorrectas']);
            }

            // Verificar que tenga permisos (roles administrativos, médicos y especialistas)
            $rolesPermitidos = [1, 4, 5]; // ADMINISTRADOR, MEDICO_TRIAJE, MEDICO_ESPECIALISTA
            if (!in_array($user['rol_id'], $rolesPermitidos)) {
                return $this->response->setJSON(['success' => false, 'error' => 'Sin permisos para acceder a reportes']);
            }

            // Guardar sesión temporal para reportes (válida por 2 horas)
            session()->set([
                'reportes_authenticated' => true,
                'reportes_user_id' => $user['usu_id'],
                'reportes_user_name' => $user['usu_nombre'] . ' ' . $user['usu_apellido'],
                'reportes_user_rol' => $user['rol_nombre'],
                'reportes_rol_id' => $user['rol_id'],
                'reportes_timestamp' => time()
            ]);
            return $this->response->setJSON([
                'success' => true,
                'message' => 'Autenticación exitosa',
                'usuario' => $user['usu_nombre'] . ' ' . $user['usu_apellido'],
                'rol' => $user['rol_nombre']
            ]);

        } catch (\Exception $e) {
            return $this->response->setJSON(['success' => false, 'error' => 'Error interno del servidor']);
        }
    }

    /**
     * Dashboard principal de reportes
     */
    public function dashboard()
    {
        // Verificar autenticación
        if (!$this->verificarAutenticacionReportes()) {
            return redirect()->to('/especialidades/lista')->with('error', 'Acceso no autorizado');
        }

        $data = [
            'title' => 'Reportes de Especialidades Médicas',
            'usuario_reportes' => session()->get('reportes_user_name'),
            'rol_reportes' => session()->get('reportes_user_rol'),
            'rol_id' => session()->get('reportes_rol_id')
        ];

        return view('especialidades/reportes/dashboard', $data);
    }

    /**
     * Versión alternativa del método obtenerDatos sin GROUP BY complejos
     */
    public function obtenerDatosAlternativo()
    {
        if (!$this->verificarAutenticacionReportes()) {
            return $this->response->setJSON(['error' => 'No autorizado']);
        }

        $fechaInicio = $this->request->getGet('fecha_inicio');
        $fechaFin = $this->request->getGet('fecha_fin');
        $especialidad = $this->request->getGet('especialidad');
        $estado = $this->request->getGet('estado');

        try {
            $db = \Config\Database::connect();

            // Consulta principal sin subconsultas complejas
            $sql = "
                SELECT
                    
                    aa.are_codigo,
                    aa.ate_codigo,
                    aa.are_fecha_asignacion as fecha_ingreso,
                    aa.are_hora_asignacion as hora_ingreso,
                    aa.are_fecha_inicio_atencion as fecha_atencion,
                    aa.are_hora_inicio_atencion as hora_atencion,
                    aa.are_fecha_fin_atencion as fecha_alta,
                    aa.are_hora_fin_atencion as hora_alta,
                    aa.are_estado,
                    
                    p.pac_nombres,
                    p.pac_apellidos,
                    p.pac_cedula,
                    p.pac_grupo_prioritario,
                    p.pac_edad_valor,
                    p.pac_edad_unidad,
                    
                    -- Nacionalidad y etnia
                    n.nac_descripcion as nacionalidad,
                    gc.gcu_descripcion as etnia,
                    
                    -- Pueblos y nacionalidades indígenas
                    ni.nac_ind_nombre as nacionalidad_indigena,
                    pi.pue_ind_nombre as pueblo_indigena,
                    e.esp_nombre as especialidad,
                    
                    a.ate_colores as triaje_color,
                    
                    s.seg_descripcion as seguro,
                    s.seg_codigo,
                    
                    u_med.usu_nombre as medico_nombre,
                    u_med.usu_apellido as medico_apellido
                    
                FROM t_area_atencion aa
                INNER JOIN t_atencion a ON aa.ate_codigo = a.ate_codigo
                INNER JOIN t_paciente p ON a.pac_codigo = p.pac_codigo
                INNER JOIN t_especialidad e ON aa.esp_codigo = e.esp_codigo
                LEFT JOIN t_seguro_social s ON p.seg_codigo = s.seg_codigo
                LEFT JOIN t_usuario u_med ON aa.are_medico_asignado = u_med.usu_id
                LEFT JOIN t_nacionalidad n ON p.nac_codigo = n.nac_codigo
                LEFT JOIN t_grupo_cultural gc ON p.gcu_codigo = gc.gcu_codigo
                LEFT JOIN t_nacionalidad_indigena ni ON p.nac_ind_codigo = ni.nac_ind_codigo
                LEFT JOIN t_pueblo_indigena pi ON p.pue_ind_codigo = pi.pue_ind_codigo
                WHERE 1=1
            ";

            $params = [];

            // Aplicar filtros
            if ($fechaInicio && $fechaFin) {
                $sql .= " AND aa.are_fecha_asignacion BETWEEN ? AND ?";
                $params[] = $fechaInicio;
                $params[] = $fechaFin;
            } elseif ($fechaInicio) {
                $sql .= " AND aa.are_fecha_asignacion >= ?";
                $params[] = $fechaInicio;
            } elseif ($fechaFin) {
                $sql .= " AND aa.are_fecha_asignacion <= ?";
                $params[] = $fechaFin;
            }

            if ($especialidad && $especialidad !== 'todas') {
                $sql .= " AND aa.esp_codigo = ?";
                $params[] = $especialidad;
            }

            if ($estado && $estado !== 'todos') {
                $sql .= " AND aa.are_estado = ?";
                $params[] = $estado;
            }

            $sql .= " ORDER BY aa.are_fecha_asignacion DESC, aa.are_hora_asignacion DESC";

            $query = $db->query($sql, $params);
            $resultados = $query->getResultArray();

            if (!$resultados) {
                $resultados = [];
            }

            // Usar un array para evitar duplicados por are_codigo
            $datosUnicos = [];
            $areCodigosVistos = [];

            foreach ($resultados as $row) {
                $are_codigo = $row['are_codigo'];

                // Saltar si ya procesamos este are_codigo
                if (in_array($are_codigo, $areCodigosVistos)) {
                    continue;
                }
                $areCodigosVistos[] = $are_codigo;

                $ate_codigo = $row['ate_codigo'];

                $diagsPresuntivos = $db->query(
                    "SELECT diagp_descripcion, diagp_cie 
                            FROM t_diagnostico_presuntivo 
                            WHERE ate_codigo = ? 
                            LIMIT 3",
                    [$ate_codigo]
                )->getResultArray();

                // Obtener TODOS los diagnósticos definitivos (máximo 3)
                $diagsDefinitivos = $db->query(
                    "SELECT diagd_descripcion, diagd_cie 
                            FROM t_diagnostico_definitivo 
                            WHERE ate_codigo = ? 
                            LIMIT 3",
                    [$ate_codigo]
                )->getResultArray();


                // Obtener contador de modificaciones para este registro
                $modificaciones = $db->query(
                    "SELECT mdr_contador 
                        FROM t_modificaciones_diagnosticos_reportes 
                        WHERE ate_codigo = ?",
                    [$ate_codigo]
                )->getRow();

                // Obtener TODOS los datos de egreso para este ate_codigo
                $egresoResults = $db->query("
                    SELECT
                        ee.egr_observaciones,
                        ee.egr_dias_reposo,
                        ese.ese_descripcion as estado_egreso,
                        moe.moe_descripcion as modalidad_egreso,
                        tie.tie_descripcion as tipo_egreso
                    FROM t_egreso_emergencia ee
                    LEFT JOIN t_estado_egreso ese ON ee.ese_codigo = ese.ese_codigo
                    LEFT JOIN t_modalidad_egreso moe ON ee.moe_codigo = moe.moe_codigo
                    LEFT JOIN t_tipo_egreso tie ON ee.tie_codigo = tie.tie_codigo
                    WHERE ee.ate_codigo = ?
                    ORDER BY ee.egr_codigo
                ", [$ate_codigo])->getResultArray();

                // Agrupar los valores múltiples
                $estadosEgreso = [];
                $modalidadesEgreso = [];
                $tiposEgreso = [];
                $observaciones = '';
                $diasReposo = '0';

                foreach ($egresoResults as $egresoRow) {
                    // Recopilar estados únicos
                    if (!empty($egresoRow['estado_egreso']) && !in_array($egresoRow['estado_egreso'], $estadosEgreso)) {
                        $estadosEgreso[] = $egresoRow['estado_egreso'];
                    }

                    // Recopilar modalidades únicas
                    if (!empty($egresoRow['modalidad_egreso']) && !in_array($egresoRow['modalidad_egreso'], $modalidadesEgreso)) {
                        $modalidadesEgreso[] = $egresoRow['modalidad_egreso'];
                    }

                    // Recopilar tipos únicos
                    if (!empty($egresoRow['tipo_egreso']) && !in_array($egresoRow['tipo_egreso'], $tiposEgreso)) {
                        $tiposEgreso[] = $egresoRow['tipo_egreso'];
                    }

                    // Tomar la primera observación y días de reposo no vacíos
                    if (empty($observaciones) && !empty($egresoRow['egr_observaciones'])) {
                        $observaciones = $egresoRow['egr_observaciones'];
                    }
                    if ($diasReposo === '0' && !empty($egresoRow['egr_dias_reposo'])) {
                        $diasReposo = $egresoRow['egr_dias_reposo'];
                    }
                }

                // Crear objeto egreso consolidado
                $egreso = (object) [
                    'estado_egreso' => !empty($estadosEgreso) ? implode(',', $estadosEgreso) : null,
                    'modalidad_egreso' => !empty($modalidadesEgreso) ? implode(',', $modalidadesEgreso) : null,
                    'tipo_egreso' => !empty($tiposEgreso) ? implode(',', $tiposEgreso) : null,
                    'egr_observaciones' => $observaciones,
                    'egr_dias_reposo' => $diasReposo
                ];

                // Calcular si está afiliado
                $pacienteAfiliado = (isset($row['seg_codigo']) && $row['seg_codigo'] && $row['seg_codigo'] != 5) ? 'Sí' : 'No';

                $datosUnicos[] = [
                    'ate_codigo' => $row['ate_codigo'] ?? '',
                    'are_codigo' => $row['are_codigo'] ?? '',
                    'fecha_ingreso' => $row['fecha_ingreso'] ?? '',
                    'hora_ingreso' => $row['hora_ingreso'] ?? '',
                    'hora_atencion' => $row['hora_atencion'] ?: '-',
                    'hora_alta' => $row['hora_alta'] ?: '-',
                    'paciente' => trim(($row['pac_nombres'] ?? '') . ' ' . ($row['pac_apellidos'] ?? '')),
                    'cedula' => $row['pac_cedula'] ?? 'Sin cédula',
                    'triaje_color' => $row['triaje_color'] ?? '',
                    'especialidad' => $row['especialidad'] ?? '',
                    'estado' => $row['are_estado'] ?? '',
                    'paciente_afiliado' => $pacienteAfiliado,
                    'grupo_prioritario' => $row['pac_grupo_prioritario'] ? 'Sí' : 'No',
                    'seguro' => $row['seguro'] ?: 'Sin seguro',
                    'estado_egreso' => $egreso ? $egreso->estado_egreso : '-',
                    'modalidad_egreso' => $egreso ? $egreso->modalidad_egreso : '-',
                    'tipo_egreso' => $egreso ? $egreso->tipo_egreso : '-',
                    'medico_asignado' => $row['medico_nombre'] ? trim($row['medico_nombre'] . ' ' . ($row['medico_apellido'] ?? '')) : '-',
                    'dias_reposo' => $egreso ? ($egreso->egr_dias_reposo ?: '0') : '0',
                    'observaciones_egreso' => $egreso ? ($egreso->egr_observaciones ?: '-') : '-',
                    'edad' => ($row['pac_edad_valor'] ?? '') . ($row['pac_edad_unidad'] ?? 'A'),
                    'nacionalidad' => $row['nacionalidad'] ?? 'No especificada',
                    'etnia' => $row['etnia'] ?? 'No especificada',
                    'nacionalidad_indigena' => $row['nacionalidad_indigena'] ?? '-',
                    'pueblo_indigena' => $row['pueblo_indigena'] ?? '-',
                    // Diagnósticos como arrays
                    'diagnosticos_presuntivos' => $diagsPresuntivos ?: [],
                    'diagnosticos_definitivos' => $diagsDefinitivos ?: [],
                    // Contador de modificaciones
                    'modificaciones_usadas' => $modificaciones ? $modificaciones->mdr_contador : 0,
                    'modificaciones_permitidas' => 3
                ];
            }

            return $this->response->setJSON([
                'draw' => intval($this->request->getGet('draw') ?? 1),
                'recordsTotal' => count($datosUnicos),
                'recordsFiltered' => count($datosUnicos),
                'data' => $datosUnicos,
                'success' => true
            ]);

        } catch (\Exception $e) {
            return $this->response->setJSON([
                'draw' => intval($this->request->getGet('draw') ?? 1),
                'recordsTotal' => 0,
                'recordsFiltered' => 0,
                'data' => [],
                'error' => 'Error interno del servidor: ' . $e->getMessage()
            ]);
        }
    }
    public function guardarDiagnosticos()
    {
        if (!$this->verificarAutenticacionReportes()) {
            return $this->response->setJSON(['error' => 'No autorizado']);
        }

        $ate_codigo = $this->request->getPost('ate_codigo');
        $diagnosticos_presuntivos = $this->request->getPost('diagnosticos_presuntivos');
        $diagnosticos_definitivos = $this->request->getPost('diagnosticos_definitivos');
        $usuario_id = session()->get('reportes_user_id');

        // Validaciones adicionales - Debug extendido
        if (empty($ate_codigo)) {
            return $this->response->setJSON(['error' => 'Código de atención requerido']);
        }

        if (empty($usuario_id)) {
            return $this->response->setJSON(['error' => 'Usuario no identificado']);
        }

        try {
            $db = \Config\Database::connect();

            // Verificar que la atención existe
            $atencion = $db->table('t_atencion')->where('ate_codigo', $ate_codigo)->get()->getRowArray();
            if (!$atencion) {
                return $this->response->setJSON([
                    'error' => 'Atención no encontrada',
                    'debug' => [
                        'ate_codigo_buscado' => $ate_codigo,
                        'tipo_ate_codigo' => gettype($ate_codigo)
                    ]
                ]);
            }

            $db->transStart();

            // Verificar límite de modificaciones
            $modificaciones = $db->table('t_modificaciones_diagnosticos_reportes')
                ->where('ate_codigo', $ate_codigo)
                ->get()
                ->getRowArray();

            if ($modificaciones && $modificaciones['mdr_contador'] >= 3) {
                return $this->response->setJSON(['error' => 'Límite de modificaciones alcanzado']);
            }

            // Log para debugging        // Eliminar diagnósticos anteriores
            $deleted_presuntivos = $db->table('t_diagnostico_presuntivo')->where('ate_codigo', $ate_codigo)->delete();
            $deleted_definitivos = $db->table('t_diagnostico_definitivo')->where('ate_codigo', $ate_codigo)->delete();
            $insertados_presuntivos = 0;
            $insertados_definitivos = 0;

            // Insertar diagnósticos presuntivos
            if (is_array($diagnosticos_presuntivos) && !empty($diagnosticos_presuntivos)) {
                foreach ($diagnosticos_presuntivos as $diag) {
                    if (!empty($diag['descripcion']) && trim($diag['descripcion']) !== '') {
                        $data = [
                            'ate_codigo' => $ate_codigo,
                            'diagp_descripcion' => trim($diag['descripcion']),
                            'diagp_cie' => !empty($diag['cie']) ? trim($diag['cie']) : null
                        ];

                        $result = $db->table('t_diagnostico_presuntivo')->insert($data);
                        if ($result) {
                            $insertados_presuntivos++;
                        } else {
                        }
                    }
                }
            }

            // Insertar diagnósticos definitivos
            if (is_array($diagnosticos_definitivos) && !empty($diagnosticos_definitivos)) {
                foreach ($diagnosticos_definitivos as $diag) {
                    if (!empty($diag['descripcion']) && trim($diag['descripcion']) !== '') {
                        $data = [
                            'ate_codigo' => $ate_codigo,
                            'diagd_descripcion' => trim($diag['descripcion']),
                            'diagd_cie' => !empty($diag['cie']) ? trim($diag['cie']) : null
                        ];

                        $result = $db->table('t_diagnostico_definitivo')->insert($data);
                        if ($result) {
                            $insertados_definitivos++;
                        } else {
                        }
                    }
                }
            }        // Actualizar o insertar contador de modificaciones
            if ($modificaciones) {
                $update_result = $db->table('t_modificaciones_diagnosticos_reportes')
                    ->where('ate_codigo', $ate_codigo)
                    ->update([
                        'mdr_contador' => $modificaciones['mdr_contador'] + 1,
                        'mdr_fecha_ultima_modificacion' => date('Y-m-d H:i:s'),
                        'usu_id' => $usuario_id
                    ]);

                if (!$update_result) {
                }
            } else {
                $insert_result = $db->table('t_modificaciones_diagnosticos_reportes')->insert([
                    'ate_codigo' => $ate_codigo,
                    'usu_id' => $usuario_id,
                    'mdr_contador' => 1,
                    'mdr_fecha_ultima_modificacion' => date('Y-m-d H:i:s')
                ]);

                if (!$insert_result) {
                }
            }

            $db->transComplete();

            if ($db->transStatus() === FALSE) {
                return $this->response->setJSON(['error' => 'Error al guardar los diagnósticos - Transacción fallida']);
            }

            $nuevo_contador = ($modificaciones ? $modificaciones['mdr_contador'] : 0) + 1;

            return $this->response->setJSON([
                'success' => true,
                'message' => 'Diagnósticos guardados correctamente',
                'modificaciones_restantes' => 3 - $nuevo_contador,
                'debug_info' => [
                    'insertados_presuntivos' => $insertados_presuntivos,
                    'insertados_definitivos' => $insertados_definitivos,
                    'ate_codigo' => $ate_codigo
                ]
            ]);

        } catch (\Exception $e) {
            return $this->response->setJSON(['error' => 'Error al guardar: ' . $e->getMessage()]);
        }
    }
    /**
     * Obtener especialidades para filtros
     */
    public function obtenerEspecialidades()
    {
        if (!$this->verificarAutenticacionReportes()) {
            return $this->response->setJSON(['error' => 'No autorizado']);
        }

        try {
            $db = \Config\Database::connect();
            $especialidades = $db->table('t_especialidad')
                ->where('esp_activo', 1)
                ->orderBy('esp_nombre')
                ->get()
                ->getResultArray();

            return $this->response->setJSON($especialidades);

        } catch (\Exception $e) {
            return $this->response->setJSON(['error' => 'Error interno del servidor']);
        }
    }

    /**
     * Cerrar sesión de reportes
     */
    public function cerrarSesion()
    {
        $usuarioReportes = session()->get('reportes_user_name');

        session()->remove([
            'reportes_authenticated',
            'reportes_user_id',
            'reportes_user_name',
            'reportes_user_rol',
            'reportes_rol_id',
            'reportes_timestamp'
        ]);
        return redirect()->to('/especialidades/lista')->with('mensaje', 'Sesión de reportes cerrada correctamente');
    }

    /**
     * Verificar autenticación para reportes
     */
    private function verificarAutenticacionReportes()
    {
        $authenticated = session()->get('reportes_authenticated');
        $timestamp = session()->get('reportes_timestamp');

        if (!$authenticated || !$timestamp) {
            return false;
        }

        // Verificar que la sesión no haya expirado (2 horas)
        if ((time() - $timestamp) > 7200) {
            session()->remove([
                'reportes_authenticated',
                'reportes_user_id',
                'reportes_user_name',
                'reportes_user_rol',
                'reportes_rol_id',
                'reportes_timestamp'
            ]);
            return false;
        }

        return true;
    }
}