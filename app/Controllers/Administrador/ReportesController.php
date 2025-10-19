<?php

namespace App\Controllers\Administrador;

use App\Controllers\BaseController;
use App\Models\Administrador\UsuarioModel;

class ReportesController extends BaseController
{
    /**
     * Verificar sesión de administrador existente
     */
    public function verificarSesion()
    {
        // Verificar que el usuario esté autenticado como administrador
        if (!session()->get('logged_in') || session()->get('rol_id') != 1) {
            return $this->response->setJSON([
                'success' => false,
                'error' => 'Acceso no autorizado. Solo administradores pueden acceder a reportes.'
            ]);
        }

        return $this->response->setJSON([
            'success' => true,
            'usuario' => session()->get('usu_nombre') . ' ' . session()->get('usu_apellido'),
            'rol' => session()->get('rol_nombre')
        ]);
    }

    /**
     * Obtener vista de reportes para cargar dinámicamente
     */
    public function obtenerVistaReportes()
    {
        // Verificar que sea administrador autenticado
        if (!session()->get('logged_in') || session()->get('rol_id') != 1) {
            return $this->response->setJSON([
                'success' => false,
                'error' => 'Acceso no autorizado'
            ]);
        }

        $data = [
            'usuario_reportes' => session()->get('usu_nombre') . ' ' . session()->get('usu_apellido'),
            'rol_reportes' => session()->get('rol_nombre')
        ];

        $vistaReportes = view('administrador/reportes/contenido_reportes', $data);

        return $this->response->setJSON([
            'success' => true,
            'html' => $vistaReportes
        ]);
    }

    /**
     * Obtener datos para reportes administrativos
     */
    public function obtenerDatos()
    {
        // Verificar que sea administrador autenticado
        if (!session()->get('logged_in') || session()->get('rol_id') != 1) {
            return $this->response->setJSON(['error' => 'No autorizado']);
        }

        $fechaInicio = $this->request->getGet('fecha_inicio');
        $fechaFin = $this->request->getGet('fecha_fin');
        $estado = $this->request->getGet('estado');

        try {
            $db = \Config\Database::connect();

            // Consulta principal para pacientes con formularios completos y finalizados
            $sql = "
                SELECT DISTINCT
                    a.ate_codigo,
                    a.ate_fecha,
                    a.ate_hora,
                    a.ate_colores as triaje_color,

                    p.pac_nombres,
                    p.pac_apellidos,
                    p.pac_cedula,
                    p.pac_grupo_prioritario,
                    p.pac_edad_valor,
                    p.pac_edad_unidad,
                    g.gen_descripcion as sexo,

                    -- Datos de embarazo (de la tabla t_embarazo_parto)
                    emb.emb_no_aplica,

                    -- Nacionalidad y etnia
                    n.nac_descripcion as nacionalidad,
                    gc.gcu_descripcion as etnia,

                    -- Pueblos y nacionalidades indígenas
                    ni.nac_ind_nombre as nacionalidad_indigena,
                    pi.pue_ind_nombre as pueblo_indigena,

                    s.seg_descripcion as seguro,
                    s.seg_codigo,

                    -- Establecimientos
                    'HSVP' as establecimiento_ingreso,
                    egr.egr_establecimiento as establecimiento_egreso,

                    -- Estado del formulario
                    fu.seccion as estado_formulario

                FROM t_atencion a
                INNER JOIN t_paciente p ON a.pac_codigo = p.pac_codigo
                INNER JOIN t_formulario_usuario fu ON a.ate_codigo = fu.ate_codigo
                LEFT JOIN t_genero g ON g.gen_codigo = p.gen_codigo
                LEFT JOIN t_seguro_social s ON p.seg_codigo = s.seg_codigo
                LEFT JOIN t_nacionalidad n ON p.nac_codigo = n.nac_codigo
                LEFT JOIN t_grupo_cultural gc ON p.gcu_codigo = gc.gcu_codigo
                LEFT JOIN t_nacionalidad_indigena ni ON p.nac_ind_codigo = ni.nac_ind_codigo
                LEFT JOIN t_pueblo_indigena pi ON p.pue_ind_codigo = pi.pue_ind_codigo
                LEFT JOIN t_egreso_emergencia egr ON egr.ate_codigo = a.ate_codigo

                LEFT JOIN t_embarazo_parto emb ON emb.ate_codigo = a.ate_codigo
                WHERE fu.seccion IN ('ES', 'ME')
            ";

            $params = [];

            // Aplicar filtros
            if ($fechaInicio && $fechaFin) {
                $sql .= " AND DATE(a.ate_fecha) BETWEEN ? AND ?";
                $params[] = $fechaInicio;
                $params[] = $fechaFin;
            } elseif ($fechaInicio) {
                $sql .= " AND DATE(a.ate_fecha) >= ?";
                $params[] = $fechaInicio;
            } elseif ($fechaFin) {
                $sql .= " AND DATE(a.ate_fecha) <= ?";
                $params[] = $fechaFin;
            }

            if ($estado && $estado !== 'todos') {
                // Filtrar por estado del formulario o triaje
                $sql .= " AND a.ate_colores = ?";
                $params[] = $estado;
            }

            $sql .= " ORDER BY a.ate_fecha DESC";

            $query = $db->query($sql, $params);
            $resultados = $query->getResultArray();

            if (!$resultados) {
                $resultados = [];
            }

            // Procesar datos únicos
            $datosUnicos = [];

            foreach ($resultados as $row) {
                // Calcular si está afiliado
                $pacienteAfiliado = (isset($row['seg_codigo']) && $row['seg_codigo'] && $row['seg_codigo'] != 5) ? 'Sí' : 'No';

                // Formatear datos de embarazo
                $estadoEmbarazo = 'No';
                $semanasGestacion = '-';
                // Si existe registro de embarazo y NO es "no aplica", entonces está embarazada
                if (isset($row['emb_no_aplica']) && ($row['emb_no_aplica'] == 0 || $row['emb_no_aplica'] === null)) {
                    $estadoEmbarazo = 'Sí';
                }

                // Formatear fecha y hora
                $fechaAtencion = '';
                $horaAtencion = '';
                if ($row['ate_fecha']) {
                    $fechaAtencion = date('Y-m-d', strtotime($row['ate_fecha']));
                    // Si hay hora separada, usarla; sino extraer de ate_fecha
                    if ($row['ate_hora']) {
                        $horaAtencion = $row['ate_hora'];
                    } else {
                        $horaAtencion = date('H:i:s', strtotime($row['ate_fecha']));
                    }
                }

                $datosUnicos[] = [
                    'ate_codigo' => $row['ate_codigo'] ?? '',
                    'fecha_atencion' => $fechaAtencion,
                    'hora_atencion' => $horaAtencion,
                    'paciente' => trim(($row['pac_nombres'] ?? '') . ' ' . ($row['pac_apellidos'] ?? '')),
                    'cedula' => $row['pac_cedula'] ?? 'Sin cédula',
                    'sexo' => $row['sexo'] ?? 'No especificado',
                    'edad' => ($row['pac_edad_valor'] ?? '') . ($row['pac_edad_unidad'] ?? 'A'),
                    'triaje_color' => $row['triaje_color'] ?? '',
                    'paciente_afiliado' => $pacienteAfiliado,
                    'grupo_prioritario' => $row['pac_grupo_prioritario'] ? 'Sí' : 'No',
                    'seguro' => $row['seguro'] ?: 'Sin seguro',
                    'nacionalidad' => $row['nacionalidad'] ?? 'No especificada',
                    'etnia' => $row['etnia'] ?? 'No especificada',
                    'nacionalidad_indigena' => $row['nacionalidad_indigena'] ?? '-',
                    'pueblo_indigena' => $row['pueblo_indigena'] ?? '-',
                    'establecimiento_ingreso' => $row['establecimiento_ingreso'] ?? 'No especificado',
                    'establecimiento_egreso' => $row['establecimiento_egreso'] ?? 'No especificado',
                    'embarazada' => $estadoEmbarazo,
                    'semanas_gestacion' => $semanasGestacion,
                    'estado_formulario' => $row['estado_formulario'] ?? ''
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
            log_message('error', 'Error obteniendo datos de reportes administrativos: ' . $e->getMessage());

            return $this->response->setJSON([
                'draw' => intval($this->request->getGet('draw') ?? 1),
                'recordsTotal' => 0,
                'recordsFiltered' => 0,
                'data' => [],
                'error' => 'Error interno del servidor: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Obtener estadísticas de embarazos
     */
    public function obtenerEstadisticasEmbarazos()
    {
        // Verificar que sea administrador autenticado
        if (!session()->get('logged_in') || session()->get('rol_id') != 1) {
            return $this->response->setJSON(['error' => 'No autorizado']);
        }

        $fechaInicio = $this->request->getGet('fecha_inicio');
        $fechaFin = $this->request->getGet('fecha_fin');

        try {
            $db = \Config\Database::connect();

            $sql = "
                SELECT
                    COUNT(CASE WHEN (emb.emb_no_aplica = 0 OR emb.emb_no_aplica IS NULL) AND emb.emb_codigo IS NOT NULL THEN 1 END) as total_embarazadas,
                    COUNT(*) as total_pacientes_mujeres,
                    COUNT(emb.emb_codigo) as total_registros_embarazo,
                    SUM(CASE WHEN emb.emb_no_aplica = 0 THEN 1 ELSE 0 END) as embarazadas_emb_no_aplica_0,
                    SUM(CASE WHEN emb.emb_no_aplica = 1 THEN 1 ELSE 0 END) as no_embarazadas_emb_no_aplica_1,
                    SUM(CASE WHEN emb.emb_no_aplica IS NULL THEN 1 ELSE 0 END) as embarazadas_emb_no_aplica_null
                FROM t_atencion a
                INNER JOIN t_paciente p ON a.pac_codigo = p.pac_codigo
                INNER JOIN t_formulario_usuario fu ON a.ate_codigo = fu.ate_codigo
                LEFT JOIN t_genero g ON g.gen_codigo = p.gen_codigo
                LEFT JOIN t_embarazo_parto emb ON emb.ate_codigo = a.ate_codigo
                WHERE (g.gen_descripcion = 'FEMENINO' OR g.gen_descripcion = 'F')
                AND fu.seccion IN ('ES', 'ME')
            ";

            $params = [];

            if ($fechaInicio && $fechaFin) {
                $sql .= " AND DATE(a.ate_fecha) BETWEEN ? AND ?";
                $params[] = $fechaInicio;
                $params[] = $fechaFin;
            } elseif ($fechaInicio) {
                $sql .= " AND DATE(a.ate_fecha) >= ?";
                $params[] = $fechaInicio;
            } elseif ($fechaFin) {
                $sql .= " AND DATE(a.ate_fecha) <= ?";
                $params[] = $fechaFin;
            }

            $query = $db->query($sql, $params);
            $resultado = $query->getRowArray();

            return $this->response->setJSON([
                'success' => true,
                'data' => $resultado
            ]);

        } catch (\Exception $e) {
            log_message('error', 'Error obteniendo estadísticas de embarazos: ' . $e->getMessage());
            return $this->response->setJSON(['error' => 'Error interno del servidor']);
        }
    }

}