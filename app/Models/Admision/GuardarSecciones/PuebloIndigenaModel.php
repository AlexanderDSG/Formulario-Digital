<?php

namespace App\Models\Admision\GuardarSecciones;

use CodeIgniter\Model;

class PuebloIndigenaModel extends Model
{
    protected $table = 't_pueblo_indigena';
    protected $primaryKey = 'pue_ind_codigo';
    protected $allowedFields = ['pue_ind_nombre'];

    public function obtenerTodos()
    {
        return $this->orderBy('pue_ind_codigo')->findAll();
    }
}
