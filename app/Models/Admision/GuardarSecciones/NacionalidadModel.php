<?php

namespace App\Models\Admision\GuardarSecciones;

use CodeIgniter\Model;

class NacionalidadModel extends Model
{
    protected $table = 't_nacionalidad';
    protected $primaryKey = 'nac_codigo';
    protected $allowedFields = ['nac_descripcion'];

    public function obtenerTodos()
    {
        return $this->orderBy('nac_codigo')->findAll();
    }
}
