<?php

namespace App\Models\Admision\GuardarSecciones;

use CodeIgniter\Model;

class SeguroModel extends Model
{
    protected $table = 't_seguro_social';
    protected $primaryKey = 'seg_codigo';
    protected $allowedFields = ['seg_descripcion'];

    public function obtenerTodos()
    {
        return $this->orderBy('seg_codigo')->findAll();
    }
}
