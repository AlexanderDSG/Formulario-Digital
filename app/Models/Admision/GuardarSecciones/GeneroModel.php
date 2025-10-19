<?php

namespace App\Models\Admision\GuardarSecciones;

use CodeIgniter\Model;

class GeneroModel extends Model
{
    protected $table = 't_genero';
    protected $primaryKey = 'gen_codigo';
    protected $allowedFields = ['gen_descripcion'];

    public function obtenerTodos()
    {
        return $this->orderBy('gen_codigo')->findAll();
    }
}
