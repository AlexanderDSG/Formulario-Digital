<?php

namespace App\Models\Medicos\GuardarSecciones;

use CodeIgniter\Model;

class DiagnosticoDefinitivoModel extends Model
{
    protected $table      = 't_diagnostico_definitivo';
    protected $primaryKey = 'diagd_id';
    protected $returnType = 'array';

    protected $allowedFields = [
        'ate_codigo',
        'diagd_descripcion',
        'diagd_cie'
    ];
}
