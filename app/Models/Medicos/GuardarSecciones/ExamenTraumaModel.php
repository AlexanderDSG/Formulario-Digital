<?php

namespace App\Models\Medicos\GuardarSecciones;

use CodeIgniter\Model;

class ExamenTraumaModel extends Model
{
    protected $table      = 't_examen_trauma';
    protected $primaryKey = 'tra_codigo';
    protected $returnType = 'array';

    protected $allowedFields = [
        'ate_codigo',
        'tra_descripcion'
    ];
}
