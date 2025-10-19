<?php

namespace App\Models\Admision\GuardarSecciones;

use CodeIgniter\Model;

class EstadoCivilModel extends Model
{
    protected $table = 't_estado_civil';
    protected $primaryKey = 'esc_codigo';
    protected $allowedFields = ['esc_descripcion'];

    public function obtenerTodos()
    {
        return $this->orderBy('esc_codigo')->findAll();
    }
}
