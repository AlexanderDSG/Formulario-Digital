<?php

namespace App\Models\Especialidades;

use CodeIgniter\Model;

class AreaAtencionModel extends Model
{
    protected $table = 't_area_atencion';
    protected $primaryKey = 'are_codigo';

    // Asegurar que coincidan con la estructura de la BD
    protected $allowedFields = [
        'ate_codigo',
        'esp_codigo',
        'are_estado',
        'are_medico_asignado',
        'are_fecha_asignacion',
        'are_hora_asignacion',
        'are_fecha_inicio_atencion',
        'are_hora_inicio_atencion',
        'are_fecha_fin_atencion',
        'are_hora_fin_atencion',
        'are_observaciones',
        'are_prioridad'
    ];

    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    // MÉTODO DE VALIDACIÓN MEJORADO
    public function asignarPacienteAEspecialidad($ate_codigo, $esp_codigo, $medico_id, $observaciones = null)
    {
        // 1. Verificar que ate_codigo existe
        $db = \Config\Database::connect();
        $atencionExiste = $db->table('t_atencion')
            ->where('ate_codigo', $ate_codigo)
            ->get()
            ->getNumRows() > 0;

        if (!$atencionExiste) {
            throw new \Exception("La atención {$ate_codigo} no existe en el sistema");
        }

        // 2. Verificar si ya existe una asignación para esta atención
        $existente = $this->where('ate_codigo', $ate_codigo)->first();
        if ($existente) {
            throw new \Exception('Este paciente ya ha sido asignado a una especialidad.');
        }

        // 3. Verificar que la especialidad existe
        $especialidadExiste = $db->table('t_especialidad')
            ->where('esp_codigo', $esp_codigo)
            ->get()
            ->getNumRows() > 0;

        if (!$especialidadExiste) {
            throw new \Exception("La especialidad {$esp_codigo} no existe");
        }

        // 4. Insertar con datos validados
        $data = [
            'ate_codigo' => $ate_codigo,
            'esp_codigo' => $esp_codigo,
            'are_estado' => 'PENDIENTE',
            'are_fecha_asignacion' => date('Y-m-d'),
            'are_hora_asignacion' => date('H:i:s'),
            'are_medico_asignado' => $medico_id,
            'are_observaciones' => $observaciones,
            'are_prioridad' => 1
        ];

        log_message('info', "Insertando en t_area_atencion: " . json_encode($data));

        $resultado = $this->insert($data);

        if (!$resultado) {
            $errores = $this->errors();
            log_message('error', "Errores al insertar en t_area_atencion: " . json_encode($errores));
            throw new \Exception('Error al insertar: ' . implode(', ', $errores));
        }

        return $resultado;
    }

    /**
     * Obtener pacientes asignados a una especialidad
     */
    public function obtenerPacientesPorEspecialidad($esp_codigo, $estado = null)
    {
        $db = \Config\Database::connect();

        $sql = "
    SELECT 
        t_area_atencion.*,
        t_atencion.ate_fecha,
        t_atencion.ate_hora,
        t_atencion.ate_colores as triaje_color,
        t_paciente.pac_nombres,
        t_paciente.pac_apellidos,
        t_paciente.pac_cedula,
        -- Médico que TOMÓ la atención originalmente
        u_asignado.usu_nombre as medico_nombre,
        u_asignado.usu_apellido as medico_apellido,
        u_asignado.usu_id as medico_id,
        -- Médico que ENVIÓ a observación (para casos de envío)
        u_envia.usu_nombre as medico_envia_nombre,
        u_envia.usu_apellido as medico_envia_apellido,
        t_especialidad.esp_nombre,
        fu.habilitado_por_admin,
        fu.motivo_habilitacion,
        fu.fecha_habilitacion,
        -- DATOS DEL PROCESO PARCIAL
        ppe.ppe_fecha_guardado,
        ppe.ppe_hora_guardado,
        ppe.ppe_observaciones,
        u_proceso.usu_nombre as proceso_especialista_nombre,
        u_proceso.usu_apellido as proceso_especialista_apellido,
        -- DATOS DE OBSERVACIÓN
        oe.obs_motivo as motivo_observacion,
        oe.obs_fecha_envio,
        oe.obs_hora_envio,
        esp_origen.esp_nombre as especialidad_origen_nombre
    FROM t_area_atencion 
    JOIN t_atencion ON t_area_atencion.ate_codigo = t_atencion.ate_codigo
    JOIN t_paciente ON t_atencion.pac_codigo = t_paciente.pac_codigo
    JOIN t_especialidad ON t_area_atencion.esp_codigo = t_especialidad.esp_codigo
    LEFT JOIN t_usuario u_asignado ON t_area_atencion.are_medico_asignado = u_asignado.usu_id
    LEFT JOIN t_formulario_usuario fu ON t_area_atencion.ate_codigo = fu.ate_codigo AND fu.seccion = 'ES'
    LEFT JOIN t_proceso_parcial_especialidad ppe ON t_area_atencion.ate_codigo = ppe.ate_codigo AND ppe.ppe_estado = 'EN_PROCESO'
    LEFT JOIN t_usuario u_proceso ON ppe.usu_id_especialista = u_proceso.usu_id
    -- JOINs para datos de observación
    LEFT JOIN t_observacion_especialidad oe ON t_area_atencion.ate_codigo = oe.ate_codigo AND oe.obs_estado = 'ENVIADO_A_OBSERVACION'
    LEFT JOIN t_usuario u_envia ON oe.usu_id_envia = u_envia.usu_id
    LEFT JOIN t_especialidad esp_origen ON oe.esp_codigo_origen = esp_origen.esp_codigo
    WHERE t_area_atencion.esp_codigo = ?
    ";

        $params = [$esp_codigo];

        if ($estado === 'PENDIENTE') {
            $sql .= " AND t_area_atencion.are_estado = 'PENDIENTE' AND (fu.habilitado_por_admin IS NULL OR fu.habilitado_por_admin = 0)";
        } elseif ($estado === 'EN_ATENCION') {
            $sql .= " AND (t_area_atencion.are_estado = 'EN_ATENCION' OR fu.habilitado_por_admin = 1)";
        } elseif ($estado) {
            $sql .= " AND t_area_atencion.are_estado = ?";
            $params[] = $estado;
        }

        $sql .= "
    ORDER BY 
        CASE WHEN fu.habilitado_por_admin = 1 THEN 1 ELSE 2 END ASC,
        CASE t_atencion.ate_colores 
            WHEN 'ROJO' THEN 1 
            WHEN 'NARANJA' THEN 2
            WHEN 'AMARILLO' THEN 3
            WHEN 'VERDE' THEN 4
            WHEN 'AZUL' THEN 5
            ELSE 6 
        END ASC,
        t_area_atencion.are_fecha_asignacion ASC,
        t_area_atencion.are_hora_asignacion ASC
    ";

        $resultado = $db->query($sql, $params)->getResultArray();

        return $resultado;
    }

    /**
     * Verificar integridad antes de insertar
     */
    public function verificarIntegridad($ate_codigo, $esp_codigo)
    {
        $db = \Config\Database::connect();

        $resultado = [
            'valido' => true,
            'errores' => []
        ];

        // Verificar t_atencion
        $atencionExiste = $db->table('t_atencion')
            ->where('ate_codigo', $ate_codigo)
            ->get()
            ->getNumRows() > 0;

        if (!$atencionExiste) {
            $resultado['valido'] = false;
            $resultado['errores'][] = "ate_codigo {$ate_codigo} no existe en t_atencion";
        }

        // Verificar t_especialidad
        $especialidadExiste = $db->table('t_especialidad')
            ->where('esp_codigo', $esp_codigo)
            ->get()
            ->getNumRows() > 0;

        if (!$especialidadExiste) {
            $resultado['valido'] = false;
            $resultado['errores'][] = "esp_codigo {$esp_codigo} no existe en t_especialidad";
        }

        // Verificar duplicados
        $yaAsignada = $this->where('ate_codigo', $ate_codigo)->first();
        if ($yaAsignada) {
            $resultado['valido'] = false;
            $resultado['errores'][] = "ate_codigo {$ate_codigo} ya está asignado";
        }

        return $resultado;
    }

    // Resto de métodos sin cambios...
    public function tomarAtencion($are_codigo, $medico_id)
    {
        $atencion = $this->find($are_codigo);
        if (!$atencion) {
            throw new \Exception('Atención no encontrada.');
        }

        if ($atencion['are_estado'] == 'EN_ATENCION' && $atencion['are_medico_asignado'] != $medico_id) {
            throw new \Exception('Esta atención ya está siendo tomada por otro médico.');
        }

        $data = [
            'are_estado' => 'EN_ATENCION',
            'are_medico_asignado' => $medico_id,
            'are_fecha_inicio_atencion' => date('Y-m-d'),
            'are_hora_inicio_atencion' => date('H:i:s')
        ];

        return $this->update($are_codigo, $data);
    }

    public function finalizarAtencion($are_codigo, $observaciones = null)
    {
        $data = [
            'are_estado' => 'COMPLETADA',
            'are_fecha_fin_atencion' => date('Y-m-d'),
            'are_hora_fin_atencion' => date('H:i:s'),
            'are_observaciones' => $observaciones
        ];

        return $this->update($are_codigo, $data);
    }

    public function obtenerAtencionesEnCursoPorMedico($medico_id)
    {
        return $this->select('
                t_area_atencion.*,
                t_atencion.ate_colores as triaje_color,
                t_paciente.pac_nombres,
                t_paciente.pac_apellidos,
                t_paciente.pac_cedula,
                t_especialidad.esp_nombre
            ')
            ->join('t_atencion', 't_area_atencion.ate_codigo = t_atencion.ate_codigo')
            ->join('t_paciente', 't_atencion.pac_codigo = t_paciente.pac_codigo')
            ->join('t_especialidad', 't_area_atencion.esp_codigo = t_especialidad.esp_codigo')
            ->where('t_area_atencion.are_medico_asignado', $medico_id)
            ->where('t_area_atencion.are_estado', 'EN_ATENCION')
            ->findAll();
    }

    public function verificarDisponibilidadPaciente($are_codigo)
    {
        $atencion = $this->find($are_codigo);

        if (!$atencion) {
            return ['disponible' => false, 'mensaje' => 'Atención no encontrada'];
        }

        if ($atencion['are_estado'] == 'COMPLETADA') {
            return ['disponible' => false, 'mensaje' => 'Esta atención ya ha sido completada'];
        }

        // Permitir tomar atenciones enviadas a observación
        if ($atencion['are_estado'] == 'EN_ATENCION') {
            $db = \Config\Database::connect();
            $medico = $db->table('t_usuario')
                ->select('usu_nombre, usu_apellido')
                ->where('usu_id', $atencion['are_medico_asignado'])
                ->get()
                ->getRowArray();

            $nombreMedico = $medico ? $medico['usu_nombre'] . ' ' . $medico['usu_apellido'] : 'Médico desconocido';

            return [
                'disponible' => false,
                'mensaje' => "Esta atención está siendo tomada por: $nombreMedico",
                'medico_asignado' => $atencion['are_medico_asignado']
            ];
        }

        // Estados que permiten tomar la atención
        $estadosDisponibles = ['PENDIENTE', 'ENVIADO_A_OBSERVACION'];

        if (in_array($atencion['are_estado'], $estadosDisponibles)) {
            return ['disponible' => true, 'mensaje' => 'Atención disponible'];
        }

        return ['disponible' => false, 'mensaje' => 'Atención no disponible'];
    }

    public function obtenerEstadisticasPorEspecialidad($esp_codigo)
    {
        $db = \Config\Database::connect();

        $query = $db->query("
            SELECT 
                are_estado,
                COUNT(*) as cantidad,
                COUNT(CASE WHEN ate_colores = 'ROJO' THEN 1 END) as rojos,
                COUNT(CASE WHEN ate_colores = 'NARANJA' THEN 1 END) as naranjas,
                COUNT(CASE WHEN ate_colores = 'AMARILLO' THEN 1 END) as amarillos,
                COUNT(CASE WHEN ate_colores = 'VERDE' THEN 1 END) as verdes,
                COUNT(CASE WHEN ate_colores = 'AZUL' THEN 1 END) as azules
            FROM t_area_atencion aa
            JOIN t_atencion a ON aa.ate_codigo = a.ate_codigo
            WHERE aa.esp_codigo = ?
            GROUP BY are_estado
        ", [$esp_codigo]);

        return $query->getResultArray();
    }
    public function obtenerMedicoConEspecialidad($are_codigo)
    {
        $db = \Config\Database::connect();

        $query = $db->query("
        SELECT 
            aa.are_codigo,
            aa.are_medico_asignado,
            aa.are_estado,
            u.usu_id,
            u.usu_nombre,
            u.usu_apellido,
            u.usu_nro_documento,
            e.esp_nombre,
            e.esp_codigo,
            ue.usu_esp_codigo
        FROM t_area_atencion aa
        LEFT JOIN t_usuario u ON aa.are_medico_asignado = u.usu_id
        LEFT JOIN t_usuario_especialidad ue ON u.usu_id = ue.usu_id
        LEFT JOIN t_especialidad e ON ue.esp_codigo = e.esp_codigo
        WHERE aa.are_codigo = ?
        LIMIT 1
    ", [$are_codigo]);

        $resultado = $query->getRowArray();

        if ($resultado) {
            log_message('info', "Médico encontrado: {$resultado['usu_nombre']} {$resultado['usu_apellido']} - Especialidad: {$resultado['esp_nombre']}");
            return $resultado;
        }

        log_message('warning', "No se encontró médico para are_codigo: $are_codigo");
        return null;
    }
    public function obtenerEspecialidadesMedico($usu_id)
    {
        $db = \Config\Database::connect();

        $query = $db->query("
        SELECT 
            e.esp_codigo,
            e.esp_nombre,
            ue.usu_esp_codigo
        FROM t_usuario_especialidad ue
        JOIN t_especialidad e ON ue.esp_codigo = e.esp_codigo
        WHERE ue.usu_id = ?
        ORDER BY e.esp_nombre
    ", [$usu_id]);

        return $query->getResultArray();
    }

    /**
     * Obtener médico con especialidad por ID de usuario
     */
    public function obtenerMedicoEspecialidadPorId($usu_id)
    {
        $db = \Config\Database::connect();

        $query = $db->query("
        SELECT 
            u.usu_id,
            u.usu_nombre,
            u.usu_apellido,
            u.usu_nro_documento,
            e.esp_nombre,
            e.esp_codigo
        FROM t_usuario u
        LEFT JOIN t_usuario_especialidad ue ON u.usu_id = ue.usu_id
        LEFT JOIN t_especialidad e ON ue.esp_codigo = e.esp_codigo
        WHERE u.usu_id = ?
        LIMIT 1
    ", [$usu_id]);

        $resultado = $query->getRowArray();

        if ($resultado) {
            return [
                'usu_id' => $resultado['usu_id'],
                'usu_nombre' => $resultado['usu_nombre'],
                'usu_apellido' => $resultado['usu_apellido'],
                'usu_nro_documento' => $resultado['usu_nro_documento'],
                'esp_nombre' => $resultado['esp_nombre'] ?? 'Sin especialidad',
                'esp_codigo' => $resultado['esp_codigo']
            ];
        }

        return null;
    }

    /**
     * Obtener información completa de un área con datos relacionados
     */
    public function obtenerAreaConDatos($are_codigo)
    {
        return $this->select('
            t_area_atencion.*,
            t_atencion.ate_fecha,
            t_atencion.ate_hora,
            t_atencion.ate_colores as triaje_color,
            t_paciente.pac_nombres,
            t_paciente.pac_apellidos,
            t_paciente.pac_cedula,
            u_asignado.usu_nombre as medico_nombre,
            u_asignado.usu_apellido as medico_apellido,
            t_especialidad.esp_nombre
        ')
            ->join('t_atencion', 't_area_atencion.ate_codigo = t_atencion.ate_codigo')
            ->join('t_paciente', 't_atencion.pac_codigo = t_paciente.pac_codigo')
            ->join('t_especialidad', 't_area_atencion.esp_codigo = t_especialidad.esp_codigo')
            ->join('t_usuario u_asignado', 't_area_atencion.are_medico_asignado = u_asignado.usu_id', 'left')
            ->where('t_area_atencion.are_codigo', $are_codigo)
            ->first();
    }

    /**
     * Verificar si existe área de atención por ate_codigo
     */
    public function existeAreaPorAtencion($ate_codigo)
    {
        return $this->where('ate_codigo', $ate_codigo)->countAllResults() > 0;
    }

    // ==================== MÉTODOS PARA LISTA DE ESPECIALIDADES ====================

    /**
     * Obtener pacientes completados por un especialista
     */
    public function obtenerPacientesCompletadosPorEspecialista($usu_id)
    {
        $db = \Config\Database::connect();

        $query = $db->table('t_formulario_usuario fu')
            ->select('fu.ate_codigo, fu.fecha, fu.observaciones, p.pac_nombres, p.pac_apellidos, p.pac_cedula, a.ate_fecha, a.ate_hora')
            ->join('t_atencion a', 'fu.ate_codigo = a.ate_codigo')
            ->join('t_paciente p', 'a.pac_codigo = p.pac_codigo')
            ->where('fu.usu_id', $usu_id)
            ->where('fu.seccion', 'ES')
            ->orderBy('fu.fecha', 'DESC')
            ->get();

        return $query->getResultArray();
    }

    /**
     * Obtener estadísticas de un especialista
     */
    public function obtenerEstadisticasEspecialista($usu_id)
    {
        $db = \Config\Database::connect();

        // Pacientes pendientes en el sistema
        $pacientesPendientes = $db->table('t_area_atencion')
            ->where('are_estado', 'PENDIENTE')
            ->countAllResults();

        // Pacientes que este especialista tiene en atención
        $pacientesEnAtencion = $db->table('t_area_atencion')
            ->where('are_medico_asignado', $usu_id)
            ->where('are_estado', 'EN_ATENCION')
            ->countAllResults();

        // Pacientes completados por este especialista
        $totalCompletados = $db->table('t_formulario_usuario')
            ->where('usu_id', $usu_id)
            ->where('seccion', 'ES')
            ->countAllResults();

        // Completados hoy
        $completadosHoy = $db->table('t_formulario_usuario')
            ->where('usu_id', $usu_id)
            ->where('seccion', 'ES')
            ->where('DATE(fecha)', date('Y-m-d'))
            ->countAllResults();

        return [
            'pacientes_pendientes_sistema' => $pacientesPendientes,
            'pacientes_en_atencion_personal' => $pacientesEnAtencion,
            'pacientes_completados_total' => $totalCompletados,
            'pacientes_completados_hoy' => $completadosHoy
        ];
    }

    /**
     * Obtener pacientes de enfermería por especialidad
     */
    public function obtenerPacientesEnfermeriaPorEspecialidad($esp_codigo)
    {
        $db = \Config\Database::connect();

        $query = $db->query("
            SELECT DISTINCT
                p.pac_codigo,
                p.pac_nombres,
                p.pac_apellidos,
                p.pac_cedula,
                p.pac_edad_valor as pac_edad,
                p.pac_edad_unidad,
                a.ate_codigo,
                ee.are_codigo_origen as are_codigo,
                aa.are_fecha_asignacion,
                aa.are_hora_asignacion,
                aa.are_estado,
                aa.are_medico_asignado,
                aa.are_observaciones,
                e.esp_nombre as especialidad_nombre,
                ee.enf_fecha_envio,
                ee.enf_hora_envio,
                ee.enf_motivo,
                ee.enf_estado,
                u_envia.usu_nombre as medico_nombre,
                u_envia.usu_apellido as medico_apellido,
                u_recibe.usu_nombre as enfermero_nombre,
                u_recibe.usu_apellido as enfermero_apellido,
                g.gen_descripcion as sexo_descripcion
            FROM t_enfermeria_especialidad ee
            INNER JOIN t_area_atencion aa ON ee.are_codigo_origen = aa.are_codigo
            INNER JOIN t_atencion a ON aa.ate_codigo = a.ate_codigo
            INNER JOIN t_paciente p ON a.pac_codigo = p.pac_codigo
            INNER JOIN t_especialidad e ON aa.esp_codigo = e.esp_codigo
            LEFT JOIN t_usuario u_envia ON ee.usu_id_envia = u_envia.usu_id
            LEFT JOIN t_usuario u_recibe ON ee.usu_id_recibe = u_recibe.usu_id
            LEFT JOIN t_genero g ON p.gen_codigo = g.gen_codigo
            WHERE aa.esp_codigo = ?
                AND ee.enf_estado IN ('ENVIADO_A_ENFERMERIA', 'EN_ATENCION_ENFERMERIA')
            ORDER BY ee.enf_fecha_envio DESC, ee.enf_hora_envio DESC
        ", [$esp_codigo]);

        return $query->getResultArray();
    }

    /**
     * Obtener médicos especialistas trabajando en una especialidad
     */
    public function obtenerMedicosEspecialistasPorEspecialidad($esp_codigo)
    {
        $db = \Config\Database::connect();

        $query = $db->query("
            SELECT DISTINCT
                aa.are_codigo,
                aa.ate_codigo,
                aa.are_medico_asignado,
                aa.are_fecha_asignacion,
                aa.are_hora_asignacion,
                aa.are_hora_inicio_atencion,
                aa.are_estado,
                aa.are_observaciones,
                p.pac_codigo,
                p.pac_nombres,
                p.pac_apellidos,
                p.pac_cedula,
                p.pac_edad_valor as pac_edad,
                p.pac_edad_unidad,
                g.gen_descripcion as sexo_descripcion,
                u.usu_nombre as medico_nombre,
                u.usu_apellido as medico_apellido,
                e.esp_nombre as especialidad_nombre,
                a.ate_colores as triaje_color,
                CASE
                    WHEN aa.are_estado = 'EN_ATENCION' THEN 'EN_ATENCION'
                    WHEN aa.are_estado = 'PENDIENTE' THEN 'PENDIENTE'
                    WHEN aa.are_estado = 'EN_PROCESO' THEN 'EN_PROCESO'
                    ELSE 'OTROS'
                END as estado_categoria
            FROM t_area_atencion aa
            INNER JOIN t_atencion a ON aa.ate_codigo = a.ate_codigo
            INNER JOIN t_paciente p ON a.pac_codigo = p.pac_codigo
            INNER JOIN t_usuario u ON aa.are_medico_asignado = u.usu_id
            INNER JOIN t_especialidad e ON aa.esp_codigo = e.esp_codigo
            LEFT JOIN t_genero g ON p.gen_codigo = g.gen_codigo
            WHERE aa.esp_codigo = ?
                AND aa.are_estado IN ('PENDIENTE', 'EN_ATENCION', 'EN_PROCESO')
                AND aa.are_estado NOT IN ('ENVIADO_A_ENFERMERIA', 'EN_ATENCION_ENFERMERIA')
                AND aa.are_medico_asignado IS NOT NULL
                AND u.rol_id = 5
            ORDER BY
                CASE aa.are_estado
                    WHEN 'EN_ATENCION' THEN 1
                    WHEN 'EN_PROCESO' THEN 2
                    WHEN 'PENDIENTE' THEN 3
                    ELSE 4
                END,
                aa.are_fecha_asignacion DESC,
                aa.are_hora_asignacion DESC
        ", [$esp_codigo]);

        return $query->getResultArray();
    }
}