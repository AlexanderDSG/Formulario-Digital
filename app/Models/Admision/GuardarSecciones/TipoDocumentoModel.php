<?php

namespace App\Models\Admision\GuardarSecciones;

use CodeIgniter\Model;

class TipoDocumentoModel extends Model
{
    protected $table = 't_tipo_documento';
    protected $primaryKey = 'tdoc_codigo';
    protected $allowedFields = ['tdoc_descripcion'];

    public function obtenerTodos()
    {
        return $this->orderBy('tdoc_codigo')->findAll();
    }
}