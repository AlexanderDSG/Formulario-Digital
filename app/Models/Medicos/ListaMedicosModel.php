<?php

namespace App\Models\Medicos;

use CodeIgniter\Model;

class ListaMedicosModel extends Model
{
    protected $table = 't_atencion';
    protected $primaryKey = 'ate_codigo';

    /**
     * Obtener pacientes que ya tienen constantes vitales registradas
     * (que ya pasaron por enfermería) y que NO han sido completados por NINGÚN médico
     */
    // En ListaMedicosModel.php - método obtenerPacientesConConstantesVitales

    public function obtenerPacientesConConstantesVitales($usu_id = null)
    {
        if (!$usu_id) {
            // Si no se proporciona usu_id, devolver todos los pacientes con constantes vitales
            return $this->select('
            t_paciente.pac_codigo,
            t_paciente.pac_nombres,
            t_paciente.pac_apellidos,
            t_paciente.pac_cedula,
            t_atencion.ate_fecha,
            t_atencion.ate_hora,
            t_atencion.ate_codigo,
            t_atencion.ate_colores as triaje_color,
            COUNT(t_constantes_vitales.con_codigo) as total_constantes
        ')
                ->join('t_paciente', 't_atencion.pac_codigo = t_paciente.pac_codigo')
                ->join('t_constantes_vitales', 't_atencion.ate_codigo = t_constantes_vitales.ate_codigo')
                ->groupBy('t_atencion.ate_codigo')
                ->having('total_constantes >', 0)
                ->orderBy("
                CASE t_atencion.ate_colores 
                    WHEN 'ROJO' THEN 1 
                    WHEN 'NARANJA' THEN 2
                    WHEN 'AMARILLO' THEN 3
                    WHEN 'VERDE' THEN 4
                    WHEN 'AZUL' THEN 5
                    ELSE 6 
                END", 'ASC')
                ->orderBy('t_atencion.ate_fecha', 'ASC')
                ->orderBy('t_atencion.ate_hora', 'ASC')
                ->findAll();
        }

        $db = \Config\Database::connect();
        $query = $db->query("
        SELECT 
            p.pac_codigo,
            p.pac_nombres,
            p.pac_apellidos,
            p.pac_cedula,
            a.ate_fecha,
            a.ate_hora,
            a.ate_codigo,
            a.ate_colores as triaje_color,
            fu.habilitado_por_admin,
            fu.usu_id as formulario_usu_id,
            fu.fecha_habilitacion,
            fu.motivo_habilitacion,
            fu.admin_que_habilito,
            CASE 
                WHEN fu.habilitado_por_admin = 1 THEN 'MODIFICACION_HABILITADA'
                WHEN fu.ate_codigo IS NULL THEN 'PRIMERA_VEZ'
                ELSE 'COMPLETADO'
            END as tipo_acceso
        FROM t_atencion a
        INNER JOIN t_paciente p ON a.pac_codigo = p.pac_codigo
        INNER JOIN t_constantes_vitales cv ON a.ate_codigo = cv.ate_codigo
        LEFT JOIN t_formulario_usuario fu ON a.ate_codigo = fu.ate_codigo AND fu.seccion = 'ME'
        WHERE (
            -- 1. Primera vez (no existe registro ME)
            fu.ate_codigo IS NULL
            OR
            -- 2. Modificación habilitada por admin - PRIORIZAR HABILITADOS
            (fu.habilitado_por_admin = 1 AND fu.seccion = 'ME')
        )
        -- Filtrar para asegurar que solo aparezcan registros habilitados
        AND (fu.ate_codigo IS NULL OR fu.habilitado_por_admin = 1)
        -- Incluir TODOS los estados de área de atención
        AND a.ate_codigo NOT IN (
            SELECT ate_codigo FROM t_area_atencion
            WHERE are_estado IN ('PENDIENTE', 'EN_ATENCION', 'COMPLETADA', 'ENVIADO_A_OBSERVACION', 'ENVIADO_A_ENFERMERIA', 'EN_ATENCION_ENFERMERIA')
        )
        -- También excluir pacientes enviados a observación directamente
        AND a.ate_codigo NOT IN (
            SELECT ate_codigo FROM t_observacion_especialidad 
            WHERE obs_estado = 'ENVIADO_A_OBSERVACION'
        )
        -- Exclusión de procesos parciales
        AND a.ate_codigo NOT IN (
            SELECT ate_codigo FROM t_proceso_parcial_especialidad 
            WHERE ppe_estado = 'EN_PROCESO'
        )
        -- Permitir modificaciones habilitadas aunque estén en especialidades
        OR fu.habilitado_por_admin = 1
        
        ORDER BY 
            -- Prioridad: modificaciones habilitadas primero
            CASE WHEN fu.habilitado_por_admin = 1 THEN 1 ELSE 2 END ASC,
            -- Luego por color de triaje
            CASE a.ate_colores 
                WHEN 'ROJO' THEN 1 
                WHEN 'NARANJA' THEN 2
                WHEN 'AMARILLO' THEN 3
                WHEN 'VERDE' THEN 4
                WHEN 'AZUL' THEN 5
                ELSE 6 
            END ASC,
            a.ate_fecha ASC, 
            a.ate_hora ASC
    ");

        return $query->getResultArray();
    }

    /**
     * Verificar si un paciente fue completado por CUALQUIER médico
     */
    public function pacienteCompletadoPorAlgunMedico($ate_codigo)
    {
        $db = \Config\Database::connect();

        return $db->table('t_formulario_usuario')
            ->where('ate_codigo', $ate_codigo)
            ->where('seccion', 'ME')
            ->get()
            ->getNumRows() > 0;
    }

    /**
     * Verificar si un paciente fue completado por un médico específico
     */
    public function pacienteCompletadoPorMedico($ate_codigo, $usu_id)
    {
        $db = \Config\Database::connect();

        return $db->table('t_formulario_usuario')
            ->where('ate_codigo', $ate_codigo)
            ->where('usu_id', $usu_id)
            ->where('seccion', 'ME')
            ->get()
            ->getNumRows() > 0;
    }

}