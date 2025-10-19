<?php

namespace App\Models\Admision\GuardarSecciones;

use CodeIgniter\Model;

class EmpresaModel extends Model
{
    protected $table = 't_empresa';
    protected $primaryKey = 'emp_codigo';
    protected $allowedFields = ['emp_descripcion'];

    public function obtenerTodos()
    {
        return $this->orderBy('emp_codigo')->findAll();
    }
}
