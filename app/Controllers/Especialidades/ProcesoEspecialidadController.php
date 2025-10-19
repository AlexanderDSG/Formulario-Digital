<?php

namespace App\Controllers\Especialidades;

use App\Controllers\BaseController;
use App\Models\Especialidades\ProcesoParcialModel;
use App\Models\Especialidades\ObservacionEspecialidadModel;
use App\Models\Especialidades\AreaAtencionModel;

class ProcesoEspecialidadController extends BaseController
{
    private function getFechaHoy()
    {
        $timezone = config('App')->appTimezone ?? 'America/Guayaquil';
        return (new \DateTime('now', new \DateTimeZone($timezone)))->format('Y-m-d');
    }

    private function getHoraActual()
    {
        $timezone = config('App')->appTimezone ?? 'America/Guayaquil';
        return (new \DateTime('now', new \DateTimeZone($timezone)))->format('H:i:s');
    }

    /**
     * Guardar proceso parcial del especialista
     */
    public function guardarProcesoParcial()
    {
        if (!session()->get('logged_in') || session()->get('rol_id') != 5) {
            return $this->response->setJSON(['success' => false, 'error' => 'No autorizado']);
        }

        $ate_codigo = $this->request->getPost('ate_codigo');
        $are_codigo = $this->request->getPost('are_codigo');
        $esp_codigo = $this->request->getPost('esp_codigo');

        if (!$ate_codigo || !$are_codigo) {
            return $this->response->setJSON(['success' => false, 'error' => 'Datos incompletos']);
        }

        // Verificar si el formulario ya está completado antes de permitir guardar proceso parcial
        $estadoFormulario = $this->verificarFormularioYaCompletado($ate_codigo, $are_codigo);
        if ($estadoFormulario['completado']) {
            return $this->response->setJSON([
                'success' => false,
                'error' => $estadoFormulario['mensaje'],
                'mostrar_formulario_completado' => true,
                'datos_completado' => $estadoFormulario
            ]);
        }

        $db = \Config\Database::connect();
        $db->transStart();

        $areaAtencionModel = new AreaAtencionModel();
        $areaAtencion = $areaAtencionModel->find($are_codigo);

        if (!$areaAtencion || !$areaAtencion['are_medico_asignado']) {
            return $this->response->setJSON([
                'success' => false,
                'error' => 'No se encontró médico asignado a esta atención'
            ]);
        }

        // Usar el médico ASIGNADO al área, no el de la sesión
        $usu_id = $areaAtencion['are_medico_asignado'];

        $usuarioSesion = session()->get('usu_id'); // Este sigue siendo Especialista1 (ID 6)

        try {            
            // 1. GUARDAR TODAS LAS SECCIONES E-N

            // Incluir los modelos necesarios
            $especialidadController = new \App\Controllers\Especialidades\EspecialidadController();

            // Llamar a los métodos de guardado de secciones
            $this->guardarSeccionE($ate_codigo, $usu_id);
            $this->guardarSeccionF($ate_codigo, $usu_id);
            $this->guardarSeccionH($ate_codigo, $usu_id);
            $this->guardarSeccionI($ate_codigo, $usu_id);
            $this->guardarSeccionJ($ate_codigo, $usu_id);
            $this->guardarSeccionK($ate_codigo, $usu_id);
            $this->guardarSeccionL($ate_codigo, $usu_id);
            $this->guardarSeccionM($ate_codigo, $usu_id);
            $this->guardarSeccionN($ate_codigo, $usu_id);

            // 2. CAPTURAR DATOS DEL ESPECIALISTA

            $datosEspecialista = [
                'primer_nombre' => $this->request->getPost('esp_primer_nombre_n'),
                'primer_apellido' => $this->request->getPost('esp_primer_apellido_n'),
                'segundo_apellido' => $this->request->getPost('esp_segundo_apellido_n'),
                'documento' => $this->request->getPost('esp_documento_n'),
                'especialidad' => $this->request->getPost('esp_especialidad_n'),
                'fecha' => $this->request->getPost('esp_fecha_n'),
                'hora' => $this->request->getPost('esp_hora_n')
            ];

            // 3. MANEJAR ARCHIVOS

            // Obtener proceso existente para mantener firmas/sellos anteriores si no se suben nuevos
            $procesoParcialModel = new ProcesoParcialModel();
            $procesoExistentePrevio = $procesoParcialModel->obtenerProcesoPorAtencion($ate_codigo);

            $rutaFirma = $this->subirArchivoEspecialista('esp_firma_n', 'firmas');
            $rutaSello = $this->subirArchivoEspecialista('esp_sello_n', 'sellos');

            // Si no se subió archivo nuevo, mantener el existente
            if (!$rutaFirma && $procesoExistentePrevio) {
                $rutaFirma = $procesoExistentePrevio['ppe_firma_especialista'];
            }
            if (!$rutaSello && $procesoExistentePrevio) {
                $rutaSello = $procesoExistentePrevio['ppe_sello_especialista'];
            }

            // 4. GUARDAR PROCESO PARCIAL

            $observaciones = $this->request->getPost('observaciones_proceso') ?: 'Proceso guardado parcialmente';

            $resultado = $procesoParcialModel->guardarProcesoParcial(
                $ate_codigo,
                $are_codigo,
                $esp_codigo,
                $usu_id,
                $datosEspecialista,
                $rutaFirma,
                $rutaSello,
                $observaciones
            );

            if (!$resultado) {
                throw new \Exception('Error al guardar en tabla de proceso parcial');
            }

            // 5. ACTUALIZAR ESTADO

            $areaAtencionModel = new AreaAtencionModel();
            $updateResult = $areaAtencionModel->update($are_codigo, [
                'are_estado' => 'EN_PROCESO',
                'are_observaciones' => 'Proceso guardado parcialmente por: ' . session()->get('usu_nombre') . ' ' . session()->get('usu_apellido') . ' - ' . date('Y-m-d H:i:s')
            ]);

            if (!$updateResult) {
                throw new \Exception('Error al actualizar estado del área');
            }

            $db->transComplete();

            if ($db->transStatus() === false) {
                throw new \Exception('Error en la transacción');
            }
            return $this->response->setJSON([
                'success' => true,
                'message' => 'Proceso guardado correctamente. Las secciones E-N han sido guardadas. Otro especialista puede continuar la atención.',
                'redirect_url' => base_url('especialidades/lista'),
                'debug_info' => [
                    'secciones_guardadas' => 'E, F, H, I, J, K, L, M, N',
                    'especialista' => $datosEspecialista['primer_nombre'] . ' ' . $datosEspecialista['primer_apellido'],
                    'fecha_guardado' => date('Y-m-d H:i:s')
                ]
            ]);
        } catch (\Exception $e) {
            $db->transRollback();
            return $this->response->setJSON([
                'success' => false,
                'error' => 'Error interno: ' . $e->getMessage()
            ]);
        }
    }

    private function guardarSeccionE($ate_codigo, $usu_id)
    {
        $antecedenteModel = new \App\Models\Medicos\GuardarSecciones\AntecedentePacienteModel();
        $antecedenteModel->where('ate_codigo', $ate_codigo)->delete();

        $noAplica = $this->request->getPost('ant_no_aplica') ? 1 : 0;
        $antecedentesSeleccionados = $this->request->getPost('antecedentes') ?: [];
        $descripcionAntecedentes = $this->request->getPost('ant_descripcion') ?: '';

        if ($noAplica == 1) {
            $antecedenteModel->insert([
                'ate_codigo' => $ate_codigo,
                'tan_codigo' => null,
                'ap_descripcion' => null,
                'ap_no_aplica' => 1
            ]);
        } else {
            if (!empty($antecedentesSeleccionados)) {
                foreach ($antecedentesSeleccionados as $tipoAntecedente) {
                    $antecedenteModel->insert([
                        'ate_codigo' => $ate_codigo,
                        'tan_codigo' => intval($tipoAntecedente),
                        'ap_descripcion' => $descripcionAntecedentes,
                        'ap_no_aplica' => 0
                    ]);
                }
            } else if (!empty($descripcionAntecedentes)) {
                $antecedenteModel->insert([
                    'ate_codigo' => $ate_codigo,
                    'tan_codigo' => 10,
                    'ap_descripcion' => $descripcionAntecedentes,
                    'ap_no_aplica' => 0
                ]);
            }
        }
    }

    private function guardarSeccionF($ate_codigo, $usu_id)
    {
        $problemaActualModel = new \App\Models\Medicos\GuardarSecciones\ProblemaActualModel();
        $problemaActualModel->where('ate_codigo', $ate_codigo)->delete();

        $descripcionProblema = $this->request->getPost('ep_descripcion_actual') ?: '';
        if (!empty($descripcionProblema)) {
            $problemaActualModel->insert([
                'ate_codigo' => $ate_codigo,
                'pro_descripcion' => $descripcionProblema
            ]);
        }
    }

    private function guardarSeccionH($ate_codigo, $usu_id)
    {
        $examenFisicoModel = new \App\Models\Medicos\GuardarSecciones\ExamenFisicoModel();
        $examenFisicoModel->where('ate_codigo', $ate_codigo)->delete();

        $zonasSeleccionadas = $this->request->getPost('zonas_examen_fisico') ?: [];
        $descripcionExamen = $this->request->getPost('ef_descripcion') ?: '';

        if (!empty($zonasSeleccionadas)) {
            foreach ($zonasSeleccionadas as $zona) {
                $examenFisicoModel->insert([
                    'ate_codigo' => $ate_codigo,
                    'zef_codigo' => intval($zona),
                    'ef_presente' => true,
                    'ef_descripcion' => $descripcionExamen
                ]);
            }
        } else if (!empty($descripcionExamen)) {
            $examenFisicoModel->insert([
                'ate_codigo' => $ate_codigo,
                'zef_codigo' => 1,
                'ef_presente' => false,
                'ef_descripcion' => $descripcionExamen
            ]);
        }
    }

    private function guardarSeccionI($ate_codigo, $usu_id)
    {
        $examenTraumaModel = new \App\Models\Medicos\GuardarSecciones\ExamenTraumaModel();
        $examenTraumaModel->where('ate_codigo', $ate_codigo)->delete();

        $descripcionTrauma = $this->request->getPost('eft_descripcion') ?: '';
        if (!empty($descripcionTrauma)) {
            $examenTraumaModel->insert([
                'ate_codigo' => $ate_codigo,
                'tra_descripcion' => $descripcionTrauma
            ]);
        }
    }

    private function guardarSeccionJ($ate_codigo, $usu_id)
    {
        $embarazoPartoModel = new \App\Models\Medicos\GuardarSecciones\EmbarazoPartoModel();
        $embarazoPartoModel->where('ate_codigo', $ate_codigo)->delete();

        $noAplica = $this->request->getPost('emb_no_aplica') ? 1 : 0;

        // Solo insertar si marcó "No aplica" O si hay datos reales
        $hayDatos = false;

        if ($noAplica == 1) {
            // Si marca "No aplica", insertar registro
            $hayDatos = true;
        } else {
            // Verificar si hay al menos un campo con datos
            $campos = [
                'emb_gestas',
                'emb_partos',
                'emb_abortos',
                'emb_cesareas',
                'emb_fum',
                'emb_semanas_gestacion',
                'emb_movimiento_fetal',
                'emb_fcf',
                'emb_ruptura_membranas',
                'emb_tiempo_ruptura',
                'emb_afu',
                'emb_presentacion',
                'emb_sangrado_vaginal',
                'emb_contracciones',
                'emb_dilatacion',
                'emb_borramiento',
                'emb_plano',
                'emb_pelvis_viable',
                'emb_score_mama',
                'emb_observaciones'
            ];

            foreach ($campos as $campo) {
                $valor = $this->request->getPost($campo);
                if (!empty($valor) && $valor !== '0' && $valor !== '0000-00-00') {
                    $hayDatos = true;
                    break;
                }
            }
        }

        // Solo insertar si hay datos reales
        if ($hayDatos) {
            $data = [
                'ate_codigo' => $ate_codigo,
                'emb_no_aplica' => $noAplica,
                'emb_numero_gestas' => $noAplica ? null : $this->request->getPost('emb_gestas'),
                'emb_numero_partos' => $noAplica ? null : $this->request->getPost('emb_partos'),
                'emb_numero_abortos' => $noAplica ? null : $this->request->getPost('emb_abortos'),
                'emb_numero_cesareas' => $noAplica ? null : $this->request->getPost('emb_cesareas'),
                'emb_fum' => $noAplica ? null : $this->request->getPost('emb_fum'),
                'emb_semanas_gestacion' => $noAplica ? null : $this->request->getPost('emb_semanas_gestacion'),
                'emb_movimiento_fetal' => $noAplica ? null : $this->request->getPost('emb_movimiento_fetal'),
                'emb_frecuencia_cardiaca_fetal' => $noAplica ? null : $this->request->getPost('emb_fcf'),
                'emb_ruptura_menbranas' => $noAplica ? null : $this->request->getPost('emb_ruptura_membranas'),
                'emb_tiempo' => $noAplica ? null : $this->request->getPost('emb_tiempo_ruptura'),
                'emb_afu' => $noAplica ? null : $this->request->getPost('emb_afu'),
                'emb_presentacion' => $noAplica ? null : $this->request->getPost('emb_presentacion'),
                'emb_sangrado_vaginal' => $noAplica ? null : $this->request->getPost('emb_sangrado_vaginal'),
                'emb_contracciones' => $noAplica ? null : $this->request->getPost('emb_contracciones'),
                'emb_dilatacion' => $noAplica ? null : $this->request->getPost('emb_dilatacion'),
                'emb_borramiento' => $noAplica ? null : $this->request->getPost('emb_borramiento'),
                'emb_plano' => $noAplica ? null : $this->request->getPost('emb_plano'),
                'emb_pelvis_viable' => $noAplica ? null : $this->request->getPost('emb_pelvis_viable'),
                'emb_score_mama' => $noAplica ? null : $this->request->getPost('emb_score_mama'),
                'emb_observaciones' => $noAplica ? null : $this->request->getPost('emb_observaciones')
            ];

            $embarazoPartoModel->insert($data);
        }
    }

    private function guardarSeccionK($ate_codigo, $usu_id)
    {
        $examenesComplementariosModel = new \App\Models\Medicos\GuardarSecciones\ExamenesComplementariosModel();
        $examenesComplementariosModel->where('ate_codigo', $ate_codigo)->delete();

        $noAplica = $this->request->getPost('exc_no_aplica') ? 1 : 0;
        $tiposExamenes = $this->request->getPost('tipos_examenes') ?: [];
        $observaciones = $this->request->getPost('exc_observaciones') ?: '';

        if ($noAplica == 1) {
            $examenesComplementariosModel->insert([
                'ate_codigo' => $ate_codigo,
                'tipo_id' => null,
                'exa_no_aplica' => 1,
                'exa_observaciones' => null
            ]);
        } else {
            if (!empty($tiposExamenes)) {
                foreach ($tiposExamenes as $tipoExamen) {
                    $examenesComplementariosModel->insert([
                        'ate_codigo' => $ate_codigo,
                        'tipo_id' => intval($tipoExamen),
                        'exa_no_aplica' => 0,
                        'exa_observaciones' => $observaciones
                    ]);
                }
            } else if (!empty($observaciones)) {
                $examenesComplementariosModel->insert([
                    'ate_codigo' => $ate_codigo,
                    'tipo_id' => 16,
                    'exa_no_aplica' => 0,
                    'exa_observaciones' => $observaciones
                ]);
            }
        }
    }

    private function guardarSeccionL($ate_codigo, $usu_id)
    {
        $diagnosticoPresuntivoModel = new \App\Models\Medicos\GuardarSecciones\DiagnosticoPresuntivoModel();
        $diagnosticoPresuntivoModel->where('ate_codigo', $ate_codigo)->delete();

        $hayDiagnosticos = false;

        for ($i = 1; $i <= 3; $i++) {
            $descripcion = $this->request->getPost("diag_pres_desc$i");
            $cie = $this->request->getPost("diag_pres_cie$i");

            if (!empty($descripcion)) {
                $diagnosticoPresuntivoModel->insert([
                    'ate_codigo' => $ate_codigo,
                    'diagp_descripcion' => $descripcion,
                    'diagp_cie' => !empty($cie) ? $cie : null
                ]);
                $hayDiagnosticos = true;
            }
        }

        if (!$hayDiagnosticos) {
            $diagnosticoPresuntivoModel->insert([
                'ate_codigo' => $ate_codigo,
                'diagp_descripcion' => null,
                'diagp_cie' => null
            ]);
        }
    }

    private function guardarSeccionM($ate_codigo, $usu_id)
    {
        $diagnosticoDefinitivoModel = new \App\Models\Medicos\GuardarSecciones\DiagnosticoDefinitivoModel();
        $diagnosticoDefinitivoModel->where('ate_codigo', $ate_codigo)->delete();

        $hayDiagnosticos = false;

        for ($i = 1; $i <= 3; $i++) {
            $descripcion = $this->request->getPost("diag_def_desc$i");
            $cie = $this->request->getPost("diag_def_cie$i");

            if (!empty($descripcion)) {
                $diagnosticoDefinitivoModel->insert([
                    'ate_codigo' => $ate_codigo,
                    'diagd_descripcion' => $descripcion,
                    'diagd_cie' => !empty($cie) ? $cie : null
                ]);
                $hayDiagnosticos = true;
            }
        }

        if (!$hayDiagnosticos) {
            $diagnosticoDefinitivoModel->insert([
                'ate_codigo' => $ate_codigo,
                'diagd_descripcion' => null,
                'diagd_cie' => null
            ]);
        }
    }

    private function guardarSeccionN($ate_codigo, $usu_id)
    {
        $tratamientoModel = new \App\Models\Medicos\GuardarSecciones\TratamientoModel();

        // PRESERVAR estado de trat_administrado - IGUAL QUE ENFERMERÍA ESPECIALIDAD
        $tratamientosExistentes = $tratamientoModel->where('ate_codigo', $ate_codigo)->findAll();
        $tratamientosExistentesPorMedicamento = [];
        foreach ($tratamientosExistentes as $trat) {
            if (!empty($trat['trat_medicamento'])) {
                $tratamientosExistentesPorMedicamento[$trat['trat_medicamento']] = $trat;
            }
        }

        $observacionesGenerales = $this->request->getPost('plan_tratamiento') ?: '';
        $hayTratamientos = false;
        $medicamentosEnFormulario = [];

        // Procesar cada tratamiento (1 al 7) - PRESERVANDO trat_administrado
        for ($i = 1; $i <= 7; $i++) {
            $medicamento = $this->request->getPost("trat_med$i");
            $via = $this->request->getPost("trat_via$i");
            $dosis = $this->request->getPost("trat_dosis$i");
            $posologia = $this->request->getPost("trat_posologia$i");
            $dias = $this->request->getPost("trat_dias$i");

            if (!empty($medicamento)) {
                $medicamentosEnFormulario[] = $medicamento;

                // PRESERVAR estado administrado del tratamiento existente O del POST
                $tratamientoExistente = $tratamientosExistentesPorMedicamento[$medicamento] ?? null;
                $administradoPost = intval($this->request->getPost("trat_administrado$i") ?: 0);
                // Si existe el tratamiento, mantener su estado de administrado, sino usar el del POST
                $administrado = $tratamientoExistente ? intval($tratamientoExistente['trat_administrado']) : $administradoPost;
                $datosTratamiento = [
                    'ate_codigo' => $ate_codigo,
                    'trat_medicamento' => $medicamento,
                    'trat_via' => !empty($via) ? $via : null,
                    'trat_dosis' => !empty($dosis) ? $dosis : null,
                    'trat_posologia' => !empty($posologia) ? $posologia : null,
                    'trat_dias' => !empty($dias) ? intval($dias) : null,
                    'trat_observaciones' => $observacionesGenerales,
                    'trat_administrado' => $administrado  // PRESERVAR ESTADO
                ];

                if ($tratamientoExistente) {
                    // ACTUALIZAR registro existente (mantiene el mismo trat_id)
                    $resultado = $tratamientoModel->update($tratamientoExistente['trat_id'], $datosTratamiento);
                } else {
                    // INSERTAR nuevo registro
                    $resultado = $tratamientoModel->insert($datosTratamiento);
                }

                if ($resultado) {
                    $hayTratamientos = true;
                }
            }
        }

        // BORRAR tratamientos que ya no están en el formulario
        foreach ($tratamientosExistentesPorMedicamento as $medicamento => $tratamiento) {
            if (!in_array($medicamento, $medicamentosEnFormulario) && $medicamento !== 'Plan de tratamiento') {
                $tratamientoModel->delete($tratamiento['trat_id']);
            }
        }

        // MANEJAR "Plan de tratamiento" de la misma forma
        if (!$hayTratamientos && !empty($observacionesGenerales) && trim($observacionesGenerales) !== '') {
            $planExistente = $tratamientosExistentesPorMedicamento['Plan de tratamiento'] ?? null;

            $datosPlan = [
                'ate_codigo' => $ate_codigo,
                'trat_medicamento' => null,
                'trat_via' => null,
                'trat_dosis' => null,
                'trat_posologia' => null,
                'trat_dias' => null,
                'trat_observaciones' => $observacionesGenerales,
                'trat_administrado' => 0
            ];

            if ($planExistente) {
                $tratamientoModel->update($planExistente['trat_id'], $datosPlan);
            } else {
                $tratamientoModel->insert($datosPlan);
            }
        } else if (empty($observacionesGenerales) && isset($tratamientosExistentesPorMedicamento['Plan de tratamiento'])) {
            // Si no hay observaciones pero existía un plan, eliminarlo
            $planExistente = $tratamientosExistentesPorMedicamento['Plan de tratamiento'];
            $tratamientoModel->delete($planExistente['trat_id']);
        }
    }
    /**
     * Continuar proceso parcial
     */
    public function continuarProceso($are_codigo)
    {
        if (!session()->get('logged_in') || session()->get('rol_id') != 5) {
            return redirect()->to('/especialidades/lista')->with('error', 'No autorizado');
        }

        try {
            $areaAtencionModel = new AreaAtencionModel();
            $areaAtencion = $areaAtencionModel->find($are_codigo);

            if (!$areaAtencion || $areaAtencion['are_estado'] !== 'EN_PROCESO') {
                return redirect()->to('/especialidades/lista')->with('error', 'Proceso no encontrado o ya completado');
            }

            // Verificar que el usuario actual pueda continuar
            $procesoParcialModel = new ProcesoParcialModel();
            $proceso = $procesoParcialModel->obtenerProcesoPorAtencion($areaAtencion['ate_codigo']);

            if (!$proceso) {
                return redirect()->to('/especialidades/lista')->with('error', 'No se encontró información del proceso');
            }

            // Cambiar estado a EN_ATENCION para que el especialista actual pueda continuar
            $areaAtencionModel->update($are_codigo, [
                'are_estado' => 'EN_ATENCION',
                'are_medico_asignado' => session()->get('usu_id'), // Asignar al especialista actual
                'are_observaciones' => 'Continuando proceso de: ' . $proceso['usu_nombre'] . ' ' . $proceso['usu_apellido']
            ]);

            // Redirigir al formulario con parámetro especial para mostrar que es continuación
            return redirect()->to("/especialidades/formulario/{$are_codigo}?continuar_proceso=1")
                ->with('success', 'Continuando proceso de: ' . $proceso['usu_nombre'] . ' ' . $proceso['usu_apellido']);
        } catch (\Exception $e) {
            return redirect()->to('/especialidades/lista')->with('error', 'Error al continuar proceso');
        }
    }

    /**
     * Validar si el usuario actual puede continuar el proceso
     */
    public function validarContinuarProceso()
    {
        if (!session()->get('logged_in') || session()->get('rol_id') != 5) {
            return $this->response->setJSON(['success' => false, 'error' => 'No autorizado']);
        }

        $are_codigo = $this->request->getPost('are_codigo');

        if (!$are_codigo) {
            return $this->response->setJSON(['success' => false, 'error' => 'Código de área requerido']);
        }

        try {
            // Obtener información del área de atención
            $areaAtencionModel = new AreaAtencionModel();
            $areaAtencion = $areaAtencionModel->find($are_codigo);

            if (!$areaAtencion || $areaAtencion['are_estado'] !== 'EN_PROCESO') {
                return $this->response->setJSON([
                    'success' => false,
                    'error' => 'Proceso no encontrado o ya completado'
                ]);
            }

            // Obtener información del proceso parcial
            $procesoParcialModel = new ProcesoParcialModel();
            $proceso = $procesoParcialModel->obtenerProcesoPorAtencion($areaAtencion['ate_codigo']);

            if (!$proceso) {
                return $this->response->setJSON([
                    'success' => false,
                    'error' => 'No se encontró información del proceso'
                ]);
            }

            $especialistaQueGuardo = $proceso['usu_id_especialista'];

            // Determinar tipo de validación necesaria
            return $this->response->setJSON([
                'success' => true,
                'tipo_validacion' => 'DIFERENTE_ESPECIALISTA',
                'mensaje' => 'Este proceso fue guardado por: ' . $proceso['usu_nombre'] . ' ' . $proceso['usu_apellido'] .
                    'Para continuar, ingrese sus credenciales y se le asignará la atención.',
                'proceso_de' => $proceso['usu_nombre'] . ' ' . $proceso['usu_apellido'],
                'especialista_original_id' => $especialistaQueGuardo
            ]);
        } catch (\Exception $e) {
            return $this->response->setJSON([
                'success' => false,
                'error' => 'Error interno del servidor'
            ]);
        }
    }
    /**
     * Continuar proceso con validación
     */
    public function continuarProcesoConValidacion()
    {
        if (!session()->get('logged_in') || session()->get('rol_id') != 5) {
            return $this->response->setJSON(['success' => false, 'error' => 'No autorizado']);
        }

        $are_codigo = $this->request->getPost('are_codigo');
        $password = $this->request->getPost('password');
        $usuario = $this->request->getPost('usuario'); // Opcional - solo para diferentes especialistas
        $tipoValidacion = $this->request->getPost('tipo_validacion');

        if (!$are_codigo || !$password) {
            return $this->response->setJSON(['success' => false, 'error' => 'Datos incompletos']);
        }

        try {
            $db = \Config\Database::connect();
            $areaAtencionModel = new AreaAtencionModel();
            $areaAtencion = $areaAtencionModel->find($are_codigo);

            if (!$areaAtencion) {
                return $this->response->setJSON(['success' => false, 'error' => 'Área no encontrada']);
            }

            // Obtener proceso parcial
            $procesoParcialModel = new ProcesoParcialModel();
            $proceso = $procesoParcialModel->obtenerProcesoPorAtencion($areaAtencion['ate_codigo']);

            if (!$proceso) {
                return $this->response->setJSON(['success' => false, 'error' => 'Proceso no encontrado']);
            }

            $usuarioActualId = session()->get('usu_id');
            $especialistaQueGuardo = $proceso['usu_id_especialista'];

            // Determinar qué usuario validar
            $usuarioParaValidar = null;

            if ($tipoValidacion === 'MISMO_ESPECIALISTA') {
                // Mismo especialista - validar contraseña del usuario actual
                $usuarioParaValidar = $db->table('t_usuario')
                    ->where('usu_id', $usuarioActualId)
                    ->get()
                    ->getRowArray();
            } else {
                // Diferente especialista - validar con usuario y contraseña proporcionados
                if (!$usuario) {
                    return $this->response->setJSON(['success' => false, 'error' => 'Usuario requerido']);
                }

                $usuarioParaValidar = $db->table('t_usuario')
                    ->where('usu_usuario', $usuario)
                    ->where('rol_id', 5) // Solo especialistas
                    ->where('usu_estado', 'activo')
                    ->get()
                    ->getRowArray();
            }

            if (!$usuarioParaValidar) {
                return $this->response->setJSON(['success' => false, 'error' => 'Usuario no encontrado']);
            }

            // Validar contraseña
            $passwordHash = hash('sha256', $password);
            if ($usuarioParaValidar['usu_password'] !== $passwordHash) {
                return $this->response->setJSON(['success' => false, 'error' => 'Contraseña incorrecta']);
            }

            // CLAVE: Actualizar área de atención para el especialista que va a continuar
            $especialistaQueContinua = $tipoValidacion === 'MISMO_ESPECIALISTA' ? $usuarioActualId : $usuarioParaValidar['usu_id'];

            $areaAtencionModel->update($are_codigo, [
                'are_estado' => 'EN_ATENCION',  // CAMBIAR ESTADO A EN_ATENCION
                'are_medico_asignado' => $especialistaQueContinua,
                'are_fecha_inicio_atencion' => $this->getFechaHoy(),
                'are_hora_inicio_atencion' => $this->getHoraActual(),
                'are_observaciones' => "Proceso continuado por: " . $usuarioParaValidar['usu_nombre'] . ' ' . $usuarioParaValidar['usu_apellido'] .
                    " - Proceso original de: " . $proceso['usu_nombre'] . ' ' . $proceso['usu_apellido']
            ]);
            return $this->response->setJSON([
                'success' => true,
                'message' => $tipoValidacion === 'MISMO_ESPECIALISTA' ?
                    'Continuando su proceso guardado' :
                    "Proceso tomado. Original de: {$proceso['usu_nombre']} {$proceso['usu_apellido']}",
                'redirect_url' => base_url("especialidades/formulario/{$are_codigo}?continuar_proceso=1")
            ]);
        } catch (\Exception $e) {
            return $this->response->setJSON(['success' => false, 'error' => 'Error del sistema']);
        }
    }


    /**
     * Subir archivo de especialista
     */
    private function subirArchivoEspecialista($nombreCampo, $carpeta)
    {
        $archivo = $this->request->getFile($nombreCampo);

        if ($archivo && $archivo->isValid() && !$archivo->hasMoved()) {
            // Validar que sea una imagen
            if (!in_array($archivo->getMimeType(), ['image/png', 'image/jpeg', 'image/jpg'])) {
                return null;
            }

            // Validar tamaño (2MB máximo)
            if ($archivo->getSizeByUnit('mb') > 2) {
                return null;
            }

            // Crear directorio si no existe
            $rutaCarpeta = ROOTPATH . 'uploads/' . $carpeta . '/';
            if (!is_dir($rutaCarpeta)) {
                mkdir($rutaCarpeta, 0755, true);
            }

            // Generar nombre único para el archivo
            $nombreArchivo = $carpeta . '_' . time() . '_' . random_int(1000, 9999) . '.' . $archivo->getExtension();
            $rutaCompleta = $rutaCarpeta . $nombreArchivo;

            // Mover el archivo
            if ($archivo->move($rutaCarpeta, $nombreArchivo)) {
                return 'uploads/' . $carpeta . '/' . $nombreArchivo; // Ruta relativa para la BD
            }
        }

        return null;
    }

    /**
     * Verificar si existe proceso parcial guardado
     */
    public function verificarProcesoParcial()
    {
        if (!session()->get('logged_in') || session()->get('rol_id') != 5) {
            return $this->response->setJSON(['success' => false, 'error' => 'No autorizado']);
        }

        $ate_codigo = $this->request->getPost('ate_codigo');

        if (!$ate_codigo) {
            return $this->response->setJSON(['success' => false, 'error' => 'Código de atención requerido']);
        }

        try {
            $procesoParcialModel = new ProcesoParcialModel();
            $proceso = $procesoParcialModel->obtenerProcesoPorAtencion($ate_codigo);

            $tieneProcesoParcial = false;
            $datosProceso = null;

            if ($proceso && $proceso['ppe_estado'] === 'EN_PROCESO') {
                $tieneProcesoParcial = true;
                $datosProceso = [
                    'fecha_guardado' => $proceso['ppe_fecha_guardado'],
                    'hora_guardado' => $proceso['ppe_hora_guardado'],
                    'especialista' => $proceso['usu_nombre'] . ' ' . $proceso['usu_apellido'],
                    'observaciones' => $proceso['ppe_observaciones']
                ];
            }

            return $this->response->setJSON([
                'success' => true,
                'tiene_proceso_parcial' => $tieneProcesoParcial,
                'datos_proceso' => $datosProceso
            ]);
        } catch (\Exception $e) {
            return $this->response->setJSON(['success' => false, 'error' => 'Error interno del servidor']);
        }
    }

    /**
     * Verificar si el formulario de especialidad ya está completado
     * (Método compartido para consistencia entre controladores)
     */
    private function verificarFormularioYaCompletado($ate_codigo, $are_codigo)
    {
        try {
            $db = \Config\Database::connect();

            // Verificar si el área de atención ya está completada
            $areaCompletada = $db->table('t_area_atencion')
                ->where('are_codigo', $are_codigo)
                ->where('are_estado', 'COMPLETADA')
                ->get()
                ->getRowArray();

            if ($areaCompletada) {
                return [
                    'completado' => true,
                    'mensaje' => 'Esta atención de especialidad ya ha sido completada.',
                    'fecha_completado' => $areaCompletada['are_fecha_fin_atencion'] ?? '',
                    'hora_completado' => $areaCompletada['are_hora_fin_atencion'] ?? ''
                ];
            }

            // Verificar si existe registro en formulario_usuario para especialidad
            $formularioCompletado = $db->table('t_formulario_usuario')
                ->where('ate_codigo', $ate_codigo)
                ->where('seccion', 'ES')
                ->get()
                ->getRowArray();

            if ($formularioCompletado) {
                $tieneModificacionHabilitada = $formularioCompletado['habilitado_por_admin'] == 1;

                if ($tieneModificacionHabilitada) {
                    return ['completado' => false];
                }

                $especialistaQueCompleto = $db->table('t_usuario')
                    ->where('usu_id', $formularioCompletado['usu_id'])
                    ->get()
                    ->getRowArray();

                $nombreEspecialista = 'Usuario desconocido';
                if ($especialistaQueCompleto) {
                    $nombreEspecialista = trim($especialistaQueCompleto['usu_nombre'] . ' ' . $especialistaQueCompleto['usu_apellido']);
                }

                return [
                    'completado' => true,
                    'mensaje' => "Este formulario de especialidad ya fue completado por: {$nombreEspecialista}. No se puede guardar un proceso parcial.",
                    'fecha_completado' => $formularioCompletado['fecha'] ?? '',
                    'especialista' => $nombreEspecialista
                ];
            }

            return ['completado' => false];
        } catch (\Exception $e) {
            return ['completado' => false];
        }
    }
}
