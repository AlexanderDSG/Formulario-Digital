<?php

namespace App\Models\Especialidades;

use CodeIgniter\Model;

class EspecialidadModel extends Model
{
    protected $table = 't_especialidad';
    protected $primaryKey = 'esp_codigo';
    protected $allowedFields = [
        'esp_nombre',
        'esp_descripcion',
        'esp_color_triaje',
        'esp_activo',
        'esp_orden_prioridad'
    ];
    protected $useTimestamps = false;

    /**
     * Obtener todas las especialidades activas ordenadas por prioridad
     */
    public function obtenerEspecialidadesActivas()
    {
        return $this->where('esp_activo', 1)
                   ->orderBy('esp_orden_prioridad', 'ASC')
                   ->findAll();
    }

    /**
     * Obtener especialidad por color de triaje
     */
    public function obtenerPorColorTriaje($color)
    {
        return $this->where('esp_color_triaje', $color)
                   ->where('esp_activo', 1)
                   ->first();
    }

    /**
     * Obtener especialidades con conteo de pacientes
     */
    public function obtenerConContePacientes()
    {
        $db = \Config\Database::connect();
        
        $query = $db->query("
            SELECT 
                e.*,
                COUNT(CASE WHEN ar.are_estado = 'PENDIENTE' THEN 1 END) as pacientes_pendientes,
                COUNT(CASE WHEN ar.are_estado = 'EN_ATENCION' THEN 1 END) as pacientes_en_atencion,
                COUNT(ar.are_codigo) as total_pacientes
            FROM t_especialidad e
            LEFT JOIN t_area_especialidad ae ON e.esp_codigo = ae.esp_codigo
            LEFT JOIN t_area_atencion ar ON ae.area_codigo = ar.area_codigo
            WHERE e.esp_activo = 1
            GROUP BY e.esp_codigo
            ORDER BY e.esp_orden_prioridad ASC
        ");
        
        return $query->getResultArray();
    }
}