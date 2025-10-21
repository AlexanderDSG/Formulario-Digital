<?php

namespace App\Controllers\Admisiones;

use App\Controllers\BaseController;
use App\Models\Admision\PacienteHospitalModel;

class BusquedaHospitalController extends BaseController
{
    public function buscarPorCedula()
    {
        try {
            $cedula = $this->request->getPost('cedula');
            log_message('debug', '🔎 Cedula recibida: ' . $cedula);

            $modelo = new PacienteHospitalModel();
            $datos = $modelo->buscarPorCedula($cedula);

            if ($datos) {
                return $this->response->setJSON(['success' => true, 'datos' => $datos]);
            } else {
                return $this->response->setJSON(['success' => false, 'mensaje' => 'No se encontró la cédula']);
            }
        } catch (\Throwable $e) {
            log_message('error', '⚠ Error en buscarPorCedula: ' . $e->getMessage());

            // Detectar si es un error de conectividad específico
            $mensaje = $e->getMessage();
            if (strpos($mensaje, 'ODBC Driver') !== false || strpos($mensaje, 'TCP Provider') !== false) {
                $mensaje = 'Sistema del hospital no disponible. Verifique: 1) Conexión a la red del hospital, 2) Driver ODBC instalado en el servidor';
            } elseif (strpos($mensaje, 'no disponible') !== false) {
                $mensaje = 'Base de datos del hospital no está disponible en este momento';
            }

            return $this->response->setJSON(['success' => false, 'mensaje' => $mensaje]);
        }
    }


    public function buscarPorApellido()
    {
        $request = \Config\Services::request();
        $apellido = $request->getPost('apellido');

        log_message('debug', '🔍 Buscando apellido en hospital: "' . $apellido . '"');

        if (empty($apellido)) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Apellido requerido'
            ]);
        }

        try {
            $hospitalModel = new PacienteHospitalModel();
            $paciente = $hospitalModel->buscarPorApellido($apellido);

            log_message('debug', '📊 Resultado búsqueda: ' . ($paciente ? 'ENCONTRADO' : 'NO ENCONTRADO'));

            if ($paciente) {
                log_message('debug', '✅ Paciente encontrado: ' . $paciente['apellidos'] . ' ' . $paciente['nombres']);
                return $this->response->setJSON([
                    'success' => true,
                    'datos' => $paciente
                ]);
            } else {
                log_message('debug', '❌ No se encontró paciente con apellido: "' . $apellido . '"');
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'No se encontró paciente con ese apellido en la base del hospital'
                ]);
            }
        } catch (\Exception $e) {
            log_message('error', '❌ Error en búsqueda por apellido hospital: ' . $e->getMessage());

            $mensaje = $e->getMessage();
            if (strpos($mensaje, 'ODBC Driver') !== false || strpos($mensaje, 'TCP Provider') !== false) {
                $mensaje = 'Sistema del hospital no disponible. Verifique conexión de red y driver ODBC';
            } elseif (strpos($mensaje, 'no disponible') !== false) {
                $mensaje = 'Base de datos del hospital no está disponible en este momento';
            } else {
                $mensaje = 'Error al consultar la base de datos del hospital';
            }

            return $this->response->setJSON([
                'success' => false,
                'message' => $mensaje
            ]);
        }
    }

    public function buscarPorHistoria()
    {
        $request = \Config\Services::request();
        $historia = $request->getPost('historia');

        if (empty($historia)) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Historia clínica requerida'
            ]);
        }

        try {
            $hospitalModel = new PacienteHospitalModel();
            $paciente = $hospitalModel->buscarPorHistoria($historia);

            if ($paciente) {
                return $this->response->setJSON([
                    'success' => true,
                    'datos' => $paciente
                ]);
            } else {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'No se encontró paciente con esa historia clínica en la base del hospital'
                ]);
            }
        } catch (\Exception $e) {
            log_message('error', 'Error en búsqueda por historia hospital: ' . $e->getMessage());

            $mensaje = $e->getMessage();
            if (strpos($mensaje, 'ODBC Driver') !== false || strpos($mensaje, 'TCP Provider') !== false) {
                $mensaje = 'Sistema del hospital no disponible. Verifique conexión de red y driver ODBC';
            } elseif (strpos($mensaje, 'no disponible') !== false) {
                $mensaje = 'Base de datos del hospital no está disponible en este momento';
            } else {
                $mensaje = 'Error al consultar la base de datos del hospital';
            }

            return $this->response->setJSON([
                'success' => false,
                'message' => $mensaje
            ]);
        }
    }

    public function autocompletarApellidos()
    {
        $request = \Config\Services::request();
        $term = $request->getGet('term');

        // Requerir mínimo 3 caracteres para reducir carga en BD del hospital
        if (empty($term) || strlen($term) < 3) {
            return $this->response->setJSON([]);
        }

        try {
            $hospitalModel = new PacienteHospitalModel();
            $sugerencias = $hospitalModel->buscarSugerenciasPorApellido($term);

            $resultado = [];
            foreach ($sugerencias as $sugerencia) {
                // Limpiar espacios en blanco adicionales
                $apellidos = trim($sugerencia['apellidos']);
                $nombres = trim($sugerencia['nombres']);

                // Saltar si está vacío
                if (empty($apellidos) && empty($nombres)) {
                    continue;
                }

                // Crear nombre completo con formato consistente
                $nombreCompleto = $apellidos . ' ' . $nombres;
                $nombreCompleto = trim(preg_replace('/\s+/', ' ', $nombreCompleto));

                $resultado[] = [
                    'label' => $nombreCompleto,
                    'value' => $nombreCompleto
                ];
            }

            return $this->response->setJSON($resultado);
        } catch (\Exception $e) {
            log_message('error', 'Error en autocompletar apellidos hospital: ' . $e->getMessage());
            return $this->response->setJSON([]);
        }
    }
}
