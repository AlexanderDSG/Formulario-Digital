<?php

namespace App\Models\Medicos\GuardarSecciones;

use CodeIgniter\Model;

class TratamientoModel extends Model
{
    protected $table      = 't_tratamiento';
    protected $primaryKey = 'trat_id';
    protected $returnType = 'array';

    protected $allowedFields = [
    'ate_codigo',
    'trat_medicamento',
    'trat_via',
    'trat_dosis',
    'trat_posologia',
    'trat_dias',
    'trat_observaciones',
    'trat_administrado'
    ];
    // Método para obtener todos los tratamientos de un paciente por `ate_codigo`
    public function obtenerPorCodigoAtencion($ate_codigo)
    {
        return $this->where('ate_codigo', $ate_codigo)->findAll(); // Obtiene todos los tratamientos relacionados con ese código
    }
}
