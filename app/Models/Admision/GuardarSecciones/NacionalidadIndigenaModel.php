<?php

namespace App\Models\Admision\GuardarSecciones;

use CodeIgniter\Model;

class NacionalidadIndigenaModel extends Model
{
    protected $table = 't_nacionalidad_indigena';
    protected $primaryKey = 'nac_ind_codigo';
    protected $allowedFields = ['nac_ind_nombre'];

    public function obtenerTodos()
    {
        return $this->orderBy('nac_ind_codigo')->findAll();
    }
}
