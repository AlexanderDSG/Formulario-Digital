<?php

namespace App\Models\Medicos\GuardarSecciones;

use CodeIgniter\Model;

class DiagnosticoPresuntivoModel extends Model
{
    protected $table      = 't_diagnostico_presuntivo';
    protected $primaryKey = 'diagp_id';
    protected $returnType = 'array';

    protected $allowedFields = [
        'ate_codigo',
        'diagp_descripcion',
        'diagp_cie',
    ];
}
