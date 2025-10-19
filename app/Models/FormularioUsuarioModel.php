<?php

namespace App\Models;

use CodeIgniter\Model;

class FormularioUsuarioModel extends Model
{
    protected $table      = 't_formulario_usuario';
    protected $primaryKey = 'id';
    protected $returnType = 'array';

    protected $allowedFields = [
        'ate_codigo',
        'usu_id',
        'seccion',
        'fecha',
    ];
}
