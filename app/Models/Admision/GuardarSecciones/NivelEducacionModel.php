<?php

namespace App\Models\Admision\GuardarSecciones;

use CodeIgniter\Model;

class NivelEducacionModel extends Model
{
    protected $table = 't_nivel_educ';
    protected $primaryKey = 'nedu_codigo';
    protected $allowedFields = ['nedu_nivel'];

    public function obtenerTodos()
    {
        return $this->orderBy('nedu_codigo')->findAll();
    }
}