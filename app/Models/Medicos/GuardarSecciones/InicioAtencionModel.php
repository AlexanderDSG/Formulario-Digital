<?php

namespace App\Models\Medicos\GuardarSecciones;

use CodeIgniter\Model;

class InicioAtencionModel extends Model
{
    protected $table      = 't_inicio_atencion';
    protected $primaryKey = 'iat_codigo';
    protected $returnType = 'array';

    protected $allowedFields = [
        'ate_codigo',
        'iat_fecha',
        'iat_hora',
        'col_codigo',
        'iat_motivo',
        'usu_id'
    ];
}
