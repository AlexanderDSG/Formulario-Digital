<?php

namespace App\Models\Medicos\GuardarSecciones;

use CodeIgniter\Model;

class ExamenFisicoModel extends Model
{
    protected $table      = 't_examen_fisico';
    protected $primaryKey = 'ef_codigo';
    protected $returnType = 'array';

    protected $allowedFields = [
        'ate_codigo',
        'zef_codigo',
        'ef_presente',
        'ef_descripcion'
    ];
}
