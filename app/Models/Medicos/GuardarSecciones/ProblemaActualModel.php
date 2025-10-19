<?php

namespace App\Models\Medicos\GuardarSecciones;

use CodeIgniter\Model;

class ProblemaActualModel extends Model
{
    protected $table      = 't_problema_actual';
    protected $primaryKey = 'pro_codigo';
    protected $returnType = 'array';

    protected $allowedFields = [
        'ate_codigo',
        'pro_descripcion'
    ];
}
