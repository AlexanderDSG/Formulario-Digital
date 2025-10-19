<?php

namespace App\Controllers\Enfermeria;

use App\Controllers\BaseController;
use App\Models\PacienteModel;
use App\Models\AtencionModel;
use App\Models\Enfemeria\ConstantesVitalesModel;

class EnfermeriaController extends BaseController
{
    public function guardarEnfermeria()
{
    if (!session()->get('logged_in')) {
        return redirect()->to('/login');
    }

    $request = \Config\Services::request();
    
    $constantesVitalesModel = new ConstantesVitalesModel();
    $atencionModel = new AtencionModel();

    try {
        $ateCodigo = $request->getPost('ate_codigo');
        
        if (empty($ateCodigo)) {
            return redirect()->back()->with('error', 'No se encontró el código de atención.');
        }

        // Verificar que la atención existe
        $atencion = $atencionModel->find($ateCodigo);
        if (!$atencion) {
            return redirect()->back()->with('error', 'La atención especificada no existe.');
        }

        // **VALIDACIÓN OBLIGATORIA DEL COLOR DE TRIAJE**
        $colorTriaje = $request->getPost('cv_triaje_color');
        if (empty($colorTriaje)) {
            return redirect()->back()->with('error', 'El color de triaje es obligatorio. Debe seleccionar un color.');
        }

        // **VALIDACIÓN DE CAMPOS MÍNIMOS REQUERIDOS**
        $sinConstantes = $request->getPost('cv_sin_vitales');

        if (!$sinConstantes) {
            // Si no está marcado "Sin Constantes Vitales", verificar que al menos algunos campos estén llenos
            $camposImportantes = [
                'cv_presion_arterial' => 'Presión Arterial',
                'cv_pulso' => 'Pulso',
                'cv_frec_resp' => 'Frecuencia Respiratoria',
                'cv_pulsioximetria' => 'Pulsioximetría',
                'cv_peso' => 'Peso',
                'cv_talla' => 'Talla'
            ];

            $camposLlenos = 0;
            $camposLlenosNombres = [];

            foreach ($camposImportantes as $campo => $nombre) {
                $valor = $request->getPost($campo);
                if (!empty($valor) && trim($valor) !== '') {
                    $camposLlenos++;
                    $camposLlenosNombres[] = $nombre;
                }
            }

            // Requerir al menos 2 campos de constantes vitales
            if ($camposLlenos < 2) {
                $camposFaltantes = array_diff($camposImportantes, $camposLlenosNombres);
                return redirect()->back()->with('error',
                    'Debe llenar al menos 2 campos de constantes vitales básicas (Presión Arterial, Pulso, Frecuencia Respiratoria, Pulsioximetría, Peso, Talla) o marcar "Sin Constantes Vitales".'
                );
            }
        }

        // **ELIMINADA LA VERIFICACIÓN DE CONSTANTES EXISTENTES**
        // Ahora permitimos múltiples registros de constantes vitales para la misma atención

        // **PREPARAR DATOS PARA INSERTAR**
        $datosConstantesVitales = [
            'ate_codigo' => (int)$ateCodigo,
            'con_sin_constantes' => $request->getPost('cv_sin_vitales') ? true : false,
            'con_presion_arterial' => $request->getPost('cv_presion_arterial'),
            'con_pulso' => $request->getPost('cv_pulso') ? (int)$request->getPost('cv_pulso') : null,
            'con_frec_respiratoria' => $request->getPost('cv_frec_resp') ? (int)$request->getPost('cv_frec_resp') : null,
            'con_pulsioximetria' => $request->getPost('cv_pulsioximetria') ? (float)$request->getPost('cv_pulsioximetria') : null,
            'con_perimetro_cefalico' => $request->getPost('cv_perimetro_cefalico') ? (float)$request->getPost('cv_perimetro_cefalico') : null,
            'con_peso' => $request->getPost('cv_peso') ? (float)$request->getPost('cv_peso') : null,
            'con_talla' => $request->getPost('cv_talla') ? (float)$request->getPost('cv_talla') : null,
            'con_glucemia_capilar' => $request->getPost('cv_glicemia') ? (float)$request->getPost('cv_glicemia') : null,
            'con_reaccion_pupila_der' => $request->getPost('cv_reaccion_pupilar_der'),
            'con_reaccion_pupila_izq' => $request->getPost('cv_reaccion_pupilar_izq'),
            'con_t_lleno_capilar' => $request->getPost('cv_llenado_capilar'),
            'con_glasgow_ocular' => $request->getPost('cv_glasgow_ocular') ? (int)$request->getPost('cv_glasgow_ocular') : null,
            'con_glasgow_verbal' => $request->getPost('cv_glasgow_verbal') ? (int)$request->getPost('cv_glasgow_verbal') : null,
            'con_glasgow_motora' => $request->getPost('cv_glasgow_motora') ? (int)$request->getPost('cv_glasgow_motora') : null
        ];

        // **ACTUALIZAR COLOR DE TRIAJE (AHORA OBLIGATORIO)**
        $atencionModel->update($ateCodigo, ['ate_colores' => $colorTriaje]);

        // Limpiar campos vacíos pero mantener ate_codigo
        $datosConstantesVitales = array_filter($datosConstantesVitales, function($value, $key) {
            if ($key === 'ate_codigo') {
                return true; // Siempre mantener ate_codigo
            }
            return $value !== null && $value !== '';
        }, ARRAY_FILTER_USE_BOTH);

        // Asegurar que siempre tenga ate_codigo
        $datosConstantesVitales['ate_codigo'] = (int)$ateCodigo;

        if (count($datosConstantesVitales) <= 1) { // Solo ate_codigo
            return redirect()->back()->with('error', 'Debe ingresar al menos un signo vital.');
        }

        // **SIEMPRE HACER INSERT (PERMITIR MÚLTIPLES REGISTROS)**
        $resultado = $constantesVitalesModel->insert($datosConstantesVitales);

        if ($resultado) {
            $nuevoConId = $constantesVitalesModel->getInsertID();

            return redirect()->to(base_url('enfermeria/lista'))->with('mensaje',
                " Constantes vitales registradas correctamente con color de triaje: $colorTriaje"
            );

        } else {
            $errores = $constantesVitalesModel->errors();
            return redirect()->back()->with('error', 'Error al insertar constantes vitales: ' . implode(', ', $errores));
        }

    } catch (\Exception $e) {
        log_message('error', 'Error crítico en guardarEnfermeria: ' . $e->getMessage());
        return redirect()->back()->with('error', 'Error del sistema: ' . $e->getMessage());
    }
}

    /**
     * Método para obtener las constantes vitales más recientes
     */
    public function obtenerConstantesVitales($ateCodigo)
    {
        $constantesVitalesModel = new ConstantesVitalesModel();
        return $constantesVitalesModel->obtenerUltimasPorAtencion($ateCodigo);
    }

    /**
     * Método para obtener el historial completo de constantes vitales
     */
    public function obtenerHistorialConstantesVitales($ateCodigo)
    {
        $constantesVitalesModel = new ConstantesVitalesModel();
        return $constantesVitalesModel->obtenerHistorialPorAtencion($ateCodigo);
    }

    /**
     * Método para obtener datos completos del paciente y atención
     */
    public function obtenerDatosCompletos($ateCodigo)
    {
        $atencionModel = new AtencionModel();
        $pacienteModel = new PacienteModel();
        $constantesVitalesModel = new ConstantesVitalesModel();

        $atencion = $atencionModel->find($ateCodigo);
        
        if (!$atencion) {
            return null;
        }

        $paciente = $pacienteModel->find($atencion['pac_codigo']);
        $constantesVitalesRecientes = $constantesVitalesModel->obtenerUltimasPorAtencion($ateCodigo);
        $historialConstantes = $constantesVitalesModel->obtenerHistorialPorAtencion($ateCodigo);

        return [
            'atencion' => $atencion,
            'paciente' => $paciente,
            'constantes_vitales_recientes' => $constantesVitalesRecientes,
            'historial_constantes_vitales' => $historialConstantes
        ];
    }
}