<?php

namespace App\Models\Medicos\GuardarSecciones;

use CodeIgniter\Model;

class EgresoEmergenciaModel extends Model
{
    protected $table      = 't_egreso_emergencia';
    protected $primaryKey = 'egr_codigo';
    protected $returnType = 'array';

    protected $allowedFields = [
        'ate_codigo',
        'ese_codigo',
        'moe_codigo',
        'tie_codigo',
        'egr_establecimiento',
        'egr_observaciones',
        'egr_dias_reposo',
        'egr_observacion_emergencia'
    ];
}
