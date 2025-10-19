<?php

namespace App\Controllers\Api;

use App\Controllers\BaseController;
use App\Models\Admision\GuardarSecciones\ProvinciaModel;
use App\Models\Admision\GuardarSecciones\CantonModel;
use App\Models\Admision\GuardarSecciones\ParroquiaModel;

class UbicacionController extends BaseController
{
    public function obtenerCantones($provCodigo)
    {
        $cantonModel = new CantonModel();
        $cantones = $cantonModel->obtenerCantonesPorProvincia($provCodigo);
        
        return $this->response->setJSON([
            'status' => 'success',
            'data' => $cantones
        ]);
    }
    
    public function obtenerParroquias($cantCodigo)
    {
        $parroquiaModel = new ParroquiaModel();
        $parroquias = $parroquiaModel->obtenerParroquiasCombinadas($cantCodigo);
        
        return $this->response->setJSON([
            'status' => 'success',
            'data' => $parroquias
        ]);
    }
}