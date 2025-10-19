<?php

namespace App\Models\Admision\GuardarSecciones;


use CodeIgniter\Model;

class EtniaModel extends Model
{
    protected $table = 't_grupo_cultural';
    protected $primaryKey = 'gcu_codigo';
    protected $allowedFields = ['gcu_descripcion'];

    public function obtenerTodos()
    {
        return $this->orderBy('gcu_codigo')->findAll();
    }
}
