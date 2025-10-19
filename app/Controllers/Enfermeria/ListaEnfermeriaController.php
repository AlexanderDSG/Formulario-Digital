<?php

namespace App\Controllers\Enfermeria;

use App\Controllers\BaseController;

use App\Models\PacienteModel;
use App\Models\Enfemeria\ListaEnfermeriaModel;

class ListaEnfermeriaController extends BaseController
{
  public function listaEnfermeria()
    {
        if (!session()->get('logged_in')) {
            return redirect()->to('/login');
        }

        // Verificar que el usuario tenga el rol correcto (rol_id 2 para enfermería)
        if (session()->get('rol_id') != 3) {
            return redirect()->to('/dashboard')->with('error', 'No tiene permisos para acceder a esta sección.');
        }

        $data = [
            'title' => 'Lista de Pacientes - Enfermería'
        ];

        return view('enfermeria/listaEnfermeria', $data);
    }
    public function obtenerPacientes()
    {
        $model = new ListaEnfermeriaModel();
        $pacientes = $model->obtenerPacientesConAtencion();

        return $this->response->setJSON($pacientes);
    }
   
}
