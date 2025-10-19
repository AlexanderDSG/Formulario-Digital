<?php

namespace App\Models\Enfemeria;

use CodeIgniter\Model;

class ListaEnfermeriaModel extends Model
{
    protected $table = 't_atencion';
    protected $primaryKey = 'ate_codigo';

    /**
     * Obtener ATENCIONES (no pacientes) que AÚN NO tienen constantes vitales registradas
     * IMPORTANTE: Una atención = una visita. Un paciente puede tener múltiples atenciones.
     * 
     * LÓGICA: Si una atención tiene constantes vitales, ya pasó por enfermería
     * y debe estar disponible para el médico, NO para enfermería.
     */
    public function obtenerPacientesConAtencion()
    {
        return $this->select('
                t_paciente.pac_codigo,
                t_paciente.pac_nombres,
                t_paciente.pac_apellidos,
                t_paciente.pac_cedula,
                t_atencion.ate_fecha,
                t_atencion.ate_hora,
                t_atencion.ate_codigo
            ')
            ->join('t_paciente', 't_atencion.pac_codigo = t_paciente.pac_codigo')
            ->join('t_constantes_vitales', 't_atencion.ate_codigo = t_constantes_vitales.ate_codigo', 'left')
            ->where('t_constantes_vitales.con_codigo IS NULL') // SOLO atenciones SIN constantes vitales
            ->orderBy('t_atencion.ate_codigo', 'ASC') // Más reciente primero por ate_codigo
            ->findAll();
    }

    /**
     * Obtener pacientes que YA tienen constantes vitales 
     * (listos para el médico)
     */
    public function obtenerPacientesParaMedico()
    {
        return $this->select('
                t_paciente.pac_codigo,
                t_paciente.pac_nombres,
                t_paciente.pac_apellidos,
                t_paciente.pac_cedula,
                t_atencion.ate_fecha,
                t_atencion.ate_hora,
                t_atencion.ate_codigo,
                t_constantes_vitales.con_codigo,
                t_constantes_vitales.con_presion_arterial,
                t_constantes_vitales.con_pulso,
                t_constantes_vitales.con_frec_respiratoria,
                t_constantes_vitales.con_pulsioximetria
            ')
            ->join('t_paciente', 't_atencion.pac_codigo = t_paciente.pac_codigo')
            ->join('t_constantes_vitales', 't_atencion.ate_codigo = t_constantes_vitales.ate_codigo', 'inner')
            ->where('t_constantes_vitales.con_codigo IS NOT NULL') // SOLO atenciones CON constantes vitales
            ->groupBy('t_atencion.ate_codigo') // En caso de múltiples registros de constantes vitales
            ->orderBy('t_atencion.ate_fecha', 'ASC')
            ->orderBy('t_atencion.ate_hora', 'ASC')
            ->findAll();
    }
    
    /**
     * Verificar si una atención ya tiene constantes vitales
     */
    public function atencionTieneConstantesVitales($ate_codigo)
    {
        $db = \Config\Database::connect();
        $query = $db->query("SELECT con_codigo FROM t_constantes_vitales WHERE ate_codigo = ?", [$ate_codigo]);
        $resultado = $query->getRow();
        
        return !empty($resultado);
    }

    /**
     * Obtener estadísticas del flujo de trabajo
     */
    public function obtenerEstadisticasFlujo()
    {
        $db = \Config\Database::connect();
        
        // Pacientes en admisión (con atención pero sin constantes vitales)
        $enAdmision = $db->query("
            SELECT COUNT(*) as total 
            FROM t_atencion a 
            LEFT JOIN t_constantes_vitales cv ON a.ate_codigo = cv.ate_codigo 
            WHERE cv.con_codigo IS NULL
        ")->getRow()->total;

        // Pacientes que pasaron por enfermería (con constantes vitales)
        $enMedicina = $db->query("
            SELECT COUNT(DISTINCT a.ate_codigo) as total 
            FROM t_atencion a 
            INNER JOIN t_constantes_vitales cv ON a.ate_codigo = cv.ate_codigo
        ")->getRow()->total;

        return [
            'esperando_enfermeria' => $enAdmision,
            'listos_para_medico' => $enMedicina,
            'total_atenciones' => $enAdmision + $enMedicina
        ];
    }
}