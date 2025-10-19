<?php

namespace App\Models\Admision\GuardarSecciones;

use CodeIgniter\Model;

class CantonModel extends Model
{
    protected $table = 't_canton';
    protected $primaryKey = 'cant_codigo';
    protected $allowedFields = ['cant_nombre', 'prov_codigo'];
    protected $useAutoIncrement = false;

    public function obtenerTodosCantones()
    {
        return $this->orderBy('cant_nombre', 'ASC')->findAll();
    }

    /**
     * Obtener cantones filtrados por provincia
     */
    public function obtenerCantonesPorProvincia($provCodigo)
    {
        return $this->where('prov_codigo', $provCodigo)
                    ->orderBy('cant_nombre', 'ASC')
                    ->findAll();
    }
}