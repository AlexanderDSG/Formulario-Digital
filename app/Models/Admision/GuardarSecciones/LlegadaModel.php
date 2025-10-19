<?php

namespace App\Models\Admision\GuardarSecciones;

use CodeIgniter\Model;

class LlegadaModel extends Model
{
    protected $table      = 't_llegada';
    protected $primaryKey = 'lleg_codigo';
    protected $allowedFields = ['lleg_descripcion'];

    public function obtenerTodas()
    {
        return $this->orderBy('lleg_codigo')->findAll();
    }
}
