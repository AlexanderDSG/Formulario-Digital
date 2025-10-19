<?php

namespace App\Controllers\Medicos;

use App\Controllers\BaseController;
use App\Models\PacienteModel;
use App\Models\Medicos\ListaMedicosModel;

class ListaMedicosController extends BaseController
{
    public function listaMedicos()
    {
        if (!session()->get('logged_in')) {
            return redirect()->to('/login');
        }

        // SOLO rol_id 4 (MEDICO_TRIAJE) puede acceder
        if (session()->get('rol_id') != 4) {
            return redirect()->to('/dashboard')->with('error', 'Solo los médicos pueden acceder a esta sección.');
        }

        $data = [
            'title' => 'Lista de Pacientes - Médicos'
        ];

        return view('medicos/listaMedicos', $data);
    }

    public function obtenerPacientes()
    {
        if (!session()->get('logged_in')) {
            return $this->response->setJSON(['error' => 'No autorizado']);
        }

        // Solo rol_id 4
        if (session()->get('rol_id') != 4) {
            return $this->response->setJSON(['error' => 'Solo médicos tienen acceso']);
        }

        try {
            $model = new ListaMedicosModel();
            $usu_id = session()->get('usu_id');

            // Llamar la función que incluye modificaciones habilitadas
            $pacientes = $model->obtenerPacientesConConstantesVitales($usu_id);

            // Procesar y enriquecer datos para identificar modificaciones
            foreach ($pacientes as &$paciente) {
                if ($paciente['habilitado_por_admin'] == 1) {
                    $paciente['es_modificacion'] = true;
                    $paciente['mensaje_modificacion'] = 'Modificación habilitada por administrador';
                } else {
                    $paciente['es_modificacion'] = false;
                }
            }

            return $this->response->setJSON($pacientes);

        } catch (\Exception $e) {
            return $this->response->setJSON([
                'error' => 'Error interno del servidor: ' . $e->getMessage()
            ]);
        }
    }
}
