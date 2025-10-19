<?php

namespace App\Models\Admision\GuardarSecciones;

use CodeIgniter\Model;

class ProvinciaModel extends Model
{
    protected $table = 't_provincia';
    protected $primaryKey = 'prov_codigo';
    protected $allowedFields = ['prov_codigo', 'prov_nombre'];
    protected $useAutoIncrement = false;

    public function obtenerTodasProvincias()
    {
        return $this->orderBy('prov_nombre', 'ASC')->findAll();
    }
}