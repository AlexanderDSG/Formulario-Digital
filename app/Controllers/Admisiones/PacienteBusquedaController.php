<?php

namespace App\Controllers\Admisiones;

use App\Controllers\BaseController;
use App\Models\Admision\PacienteBusquedaModel;

class PacienteBusquedaController extends BaseController
{
    public function buscarPorCedula()
    {
        $cedula = $this->request->getPost('cedula');

        $model = new PacienteBusquedaModel();
        $resultado = $model->buscarPorCedula($cedula);

        if (!empty($resultado) && !empty($resultado[0]['pac_his_cli'])) {
            $data = $resultado[0];
            
            return $this->response->setJSON(['success' => true, 'data' => $data]);
        }

        return $this->response->setJSON(['success' => false, 'message' => 'No se encontró paciente con historia clínica.']);
    }

    public function buscarPorApellido()
    {
        $apellido = $this->request->getPost('apellido');

        $model = new PacienteBusquedaModel();
        $resultado = $model->buscarPorApellido($apellido);

        if (!empty($resultado) && !empty($resultado[0]['pac_his_cli'])) {
            $data = $resultado[0];
            
            return $this->response->setJSON(['success' => true, 'data' => $data]);
        }

        return $this->response->setJSON(['success' => false, 'message' => 'No se encontró paciente con historia clínica.']);
    }

    public function autocompletarApellidos()
    {
        $termino = $this->request->getGet('term');
        $model = new PacienteBusquedaModel();

        $resultados = $model->buscarSugerenciasPorApellido($termino);

        $sugerencias = array_map(function ($fila) {
            return [
                'label' => $fila['pac_apellidos'] . ' ' . $fila['pac_nombres'],
                'value' => $fila['pac_apellidos'] . ' ' . $fila['pac_nombres']
            ];
        }, $resultados);

        return $this->response->setJSON($sugerencias);
    }

    public function buscarPorHistoria()
    {
        $historia = $this->request->getPost('historia');

        $model = new PacienteBusquedaModel();
        $resultado = $model->buscarPorHistoria($historia);

        if (!empty($resultado)) {
            $data = $resultado[0];
            
            return $this->response->setJSON(['success' => true, 'data' => $data]);
        }

        return $this->response->setJSON(['success' => false, 'message' => 'No se encontró paciente con ese número de historia clínica.']);
    }
}