<?php

namespace App\Models\Medicos\GuardarSecciones;

use CodeIgniter\Model;

class CondicionLlegadaModel extends Model
{
    protected $table = 't_condicion_llegada';
    protected $primaryKey = 'col_codigo';
    protected $allowedFields = ['col_descripcion'];

    public function obtenerTodas()
    {
        return $this->orderBy('col_codigo')->findAll();
    }
}