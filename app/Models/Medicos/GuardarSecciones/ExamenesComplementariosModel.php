<?php

namespace App\Models\Medicos\GuardarSecciones;

use CodeIgniter\Model;

class ExamenesComplementariosModel extends Model
{
    protected $table      = 't_examenes_complementarios';
    protected $primaryKey = 'exa_id';
    protected $returnType = 'array';

    protected $allowedFields = [
        'ate_codigo',
        'tipo_id',
        'exa_no_aplica',
        'exa_observaciones',
    ];
}
