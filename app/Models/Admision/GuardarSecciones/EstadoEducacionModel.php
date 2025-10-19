<?php

namespace App\Models\Admision\GuardarSecciones;

use CodeIgniter\Model;

class EstadoEducacionModel extends Model
{
    protected $table = 't_esta_niv_educ';
    protected $primaryKey = 'eneduc_codigo';
    protected $allowedFields = ['eneduc_estado'];

    public function obtenerTodos()
    {
        return $this->orderBy('eneduc_codigo')->findAll();
    }
}