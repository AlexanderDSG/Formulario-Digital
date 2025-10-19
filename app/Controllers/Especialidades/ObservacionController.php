<?php

namespace App\Controllers\Especialidades;

use App\Controllers\BaseController;
use App\Models\Especialidades\AreaAtencionModel;
use App\Models\Especialidades\ObservacionEspecialidadModel;

class ObservacionController extends BaseController
{
    const ESP_OBSERVACION = 5;

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
     * Método para tomar atención en observación
     */
    public function tomarAtencionObservacion()
    {
        if (!session()->get('logged_in') || session()->get('rol_id') != 5) {
            return $this->response->setJSON(['success' => false, 'error' => 'Sin permisos']);
        }

        $are_codigo = $this->request->getPost('are_codigo');
        $usuario = $this->request->getPost('usuario');
        $password = $this->request->getPost('password');

        if (!$are_codigo || !$usuario || !$password) {
            return $this->response->setJSON(['success' => false, 'error' => 'Datos incompletos']);
        }

        try {
            $db = \Config\Database::connect();
            $areaAtencionModel = new AreaAtencionModel();

            // Validar usuario y contraseña
            $especialista = $db->table('t_usuario')
                ->where('usu_usuario', $usuario)
                ->where('rol_id', 5)
                ->where('usu_estado', 'activo')
                ->get()
                ->getRowArray();

            if (!$especialista) {
                return $this->response->setJSON(['success' => false, 'error' => 'Usuario no encontrado']);
            }

            $passwordHash = hash('sha256', $password);
            if ($especialista['usu_password'] !== $passwordHash) {
                return $this->response->setJSON(['success' => false, 'error' => 'Contraseña incorrecta']);
            }

            // Verificar que el área esté disponible
            $area = $areaAtencionModel->find($are_codigo);
            if (!$area || $area['are_estado'] !== 'PENDIENTE') {
                return $this->response->setJSON(['success' => false, 'error' => 'Atención no disponible']);
            }

            // Tomar la atención
            $resultado = $areaAtencionModel->update($are_codigo, [
                'are_medico_asignado' => $especialista['usu_id'],
                'are_estado' => 'EN_ATENCION',
                'are_fecha_inicio_atencion' => $this->getFechaHoy(),
                'are_hora_inicio_atencion' => $this->getHoraActual()
            ]);

            if ($resultado) {              
                $this->actualizarRegistroObservacionRecibida($area['ate_codigo'], $especialista['usu_id']);

                $nombreCompleto = $especialista['usu_nombre'] . ' ' . $especialista['usu_apellido'];

                return $this->response->setJSON([
                    'success' => true,
                    'message' => "Atención tomada por: $nombreCompleto",
                    'redirect_url' => base_url("especialidades/formulario/{$are_codigo}?observacion=1")
                ]);
            } else {
                return $this->response->setJSON(['success' => false, 'error' => 'Error al tomar la atención']);
            }

        } catch (\Exception $e) {
            return $this->response->setJSON(['success' => false, 'error' => 'Error del sistema']);
        }
    }
    /**
     * Enviar paciente a observación desde otra especialidad
     */
    public function enviarAObservacion()
    {
        if (!session()->get('logged_in') || session()->get('rol_id') != 5) {
            return $this->response->setJSON(['success' => false, 'error' => 'Sin permisos']);
        }

        $are_codigo = $this->request->getPost('are_codigo');
        $motivo = $this->request->getPost('motivo_observacion');

        if (!$are_codigo || !$motivo) {
            return $this->response->setJSON([
                'success' => false,
                'error' => 'Código de área y motivo son requeridos'
            ]);
        }

        try {
            $db = \Config\Database::connect();
            $areaAtencionModel = new AreaAtencionModel();
            $observacionModel = new ObservacionEspecialidadModel();

            // Obtener información del área actual
            $areaActual = $areaAtencionModel->find($are_codigo);
            if (!$areaActual) {
                return $this->response->setJSON(['success' => false, 'error' => 'Área de atención no encontrada']);
            }

            // Usar el médico asignado al área, NO el de la sesión
            $usu_id_que_envia = $areaActual['are_medico_asignado'];
            $usu_id_sesion = session()->get('usu_id');

            // Verificar que hay un médico asignado
            if (!$usu_id_que_envia) {
                return $this->response->setJSON([
                    'success' => false,
                    'error' => 'No hay médico asignado a esta atención'
                ]);
            }

            $db->transStart();

            // 1. Crear registro en observación de especialidad
            $observacionData = [
                'ate_codigo' => $areaActual['ate_codigo'],
                'are_codigo_origen' => $are_codigo,
                'esp_codigo_origen' => $areaActual['esp_codigo'],
                'usu_id_envia' => $usu_id_que_envia,
                'obs_motivo' => trim($motivo),
                'obs_fecha_envio' => $this->getFechaHoy(),
                'obs_hora_envio' => $this->getHoraActual(),
                'obs_estado' => 'ENVIADO_A_OBSERVACION'
            ];

            $obs_codigo = $observacionModel->insert($observacionData);           
            $areaAtencionModel->update($are_codigo, [
                'esp_codigo' => self::ESP_OBSERVACION,
                'are_estado' => 'ENVIADO_A_OBSERVACION',
                'are_observaciones' => 'Enviado a observación desde ' . $this->obtenerNombreEspecialidad($areaActual['esp_codigo']) . ' - ' . trim($motivo)
            ]);

            $db->transComplete();

            if ($db->transStatus() === false) {
                throw new \Exception('Error en la transacción');
            }

            return $this->response->setJSON([
                'success' => true,
                'message' => 'Paciente enviado a observación exitosamente',
                'redirect_url' => base_url('especialidades/lista'),
                'obs_codigo' => $obs_codigo
            ]);

        } catch (\Exception $e) {
            return $this->response->setJSON([
                'success' => false,
                'error' => 'Error interno: ' . $e->getMessage()
            ]);
        }
    }
    /**
     * Obtener nombre completo del médico
     */
    private function obtenerNombreMedico($usu_id)
    {
        $db = \Config\Database::connect();
        $medico = $db->table('t_usuario')
            ->select('usu_nombre, usu_apellido')
            ->where('usu_id', $usu_id)
            ->get()
            ->getRowArray();

        return $medico ? $medico['usu_nombre'] . ' ' . $medico['usu_apellido'] : 'Médico desconocido';
    }
    /**
     * Obtener pacientes en observación diferenciando por tipo
     */
    public function obtenerPacientesObservacion()
    {
        if (!session()->get('logged_in') || session()->get('rol_id') != 5) {
            return $this->response->setJSON(['success' => false, 'error' => 'Sin permisos']);
        }

        try {
            $areaAtencionModel = new AreaAtencionModel();

            // Obtener pacientes de observación (especialidad 5)
            $pacientesObservacion = $areaAtencionModel->obtenerPacientesPorEspecialidad(self::ESP_OBSERVACION);

            // Separar por tipo de observación
            $observacionNormal = [];
            $enviadosPorEspecialidad = [];

            foreach ($pacientesObservacion as $paciente) {
                // Verificar si fue enviado desde otra especialidad
                $esEnviado = $this->verificarSiEsEnviado($paciente['ate_codigo']);

                if ($esEnviado) {
                    $paciente['tipo_observacion'] = 'ENVIADO_ESPECIALIDAD';
                    $paciente['especialidad_origen'] = $esEnviado['especialidad_origen'];
                    $paciente['motivo_envio'] = $esEnviado['motivo'];
                    $paciente['fecha_envio'] = $esEnviado['fecha_envio'];
                    $paciente['hora_envio'] = $esEnviado['hora_envio'];
                    $paciente['usuario_que_envio'] = $esEnviado['usuario_que_envio'];
                    $enviadosPorEspecialidad[] = $paciente;
                } else {
                    $paciente['tipo_observacion'] = 'NORMAL';
                    $observacionNormal[] = $paciente;
                }
            }

            return $this->response->setJSON([
                'success' => true,
                'observacion_normal' => $observacionNormal,
                'enviados_por_especialidad' => $enviadosPorEspecialidad,
                'total_normal' => count($observacionNormal),
                'total_enviados' => count($enviadosPorEspecialidad)
            ]);

        } catch (\Exception $e) {
            return $this->response->setJSON([
                'success' => false,
                'error' => 'Error interno'
            ]);
        }
    }

    public function enviarAObservacionConDatos()
    {
        if (!session()->get('logged_in') || session()->get('rol_id') != 5) {
            return $this->response->setJSON(['success' => false, 'error' => 'Sin permisos']);
        }

        $are_codigo = $this->request->getPost('are_codigo');
        $motivo = $this->request->getPost('motivo_observacion');
        $datosFormularioJson = $this->request->getPost('datos_formulario');

        if (!$are_codigo || !$motivo) {
            return $this->response->setJSON([
                'success' => false,
                'error' => 'Código de área y motivo son requeridos'
            ]);
        }

        try {
            $db = \Config\Database::connect();
            $areaAtencionModel = new AreaAtencionModel();
            $observacionModel = new ObservacionEspecialidadModel();

            // Obtener información del área actual
            $areaActual = $areaAtencionModel->find($are_codigo);
            if (!$areaActual) {
                return $this->response->setJSON(['success' => false, 'error' => 'Área de atención no encontrada']);
            }

            // Verificar si el formulario ya está completado antes de permitir envío a observación
            $estadoFormulario = $this->verificarFormularioYaCompletado($areaActual['ate_codigo'], $are_codigo);
            if ($estadoFormulario['completado']) {
                return $this->response->setJSON([
                    'success' => false,
                    'error' => $estadoFormulario['mensaje'],
                    'mostrar_formulario_completado' => true,
                    'datos_completado' => $estadoFormulario
                ]);
            }

            $ate_codigo = $areaActual['ate_codigo'];
            $usu_id_que_envia = $areaActual['are_medico_asignado'];

            if (!$usu_id_que_envia) {
                return $this->response->setJSON([
                    'success' => false,
                    'error' => 'No hay médico asignado a esta atención'
                ]);
            }

            $db->transStart();

            // 1. Guardar datos del formulario si se proporcionaron
            if ($datosFormularioJson) {
                $this->guardarDatosFormularioCompletos($ate_codigo, $usu_id_que_envia, $datosFormularioJson);
            }

            // 2. Crear registro en observación de especialidad
            $observacionData = [
                'ate_codigo' => $ate_codigo,
                'are_codigo_origen' => $are_codigo,
                'esp_codigo_origen' => $areaActual['esp_codigo'],
                'usu_id_envia' => $usu_id_que_envia,
                'obs_motivo' => trim($motivo),
                'obs_fecha_envio' => $this->getFechaHoy(),
                'obs_hora_envio' => $this->getHoraActual(),
                'obs_estado' => 'ENVIADO_A_OBSERVACION'
            ];

            $obs_codigo = $observacionModel->insert($observacionData);

            // 3. Actualizar área actual
            $areaAtencionModel->update($are_codigo, [
                'esp_codigo' => self::ESP_OBSERVACION,
                'are_estado' => 'ENVIADO_A_OBSERVACION',
                'are_observaciones' => 'Enviado a observación desde ' . $this->obtenerNombreEspecialidad($areaActual['esp_codigo']) . ' - ' . trim($motivo)
            ]);

            $db->transComplete();

            if ($db->transStatus() === false) {
                throw new \Exception('Error en la transacción');
            }

            return $this->response->setJSON([
                'success' => true,
                'message' => 'Datos guardados y paciente enviado a observación exitosamente',
                'obs_codigo' => $obs_codigo,
                'redirect_url' => base_url('especialidades/lista')
            ]);

        } catch (\Exception $e) {
            return $this->response->setJSON([
                'success' => false,
                'error' => 'Error interno: ' . $e->getMessage()
            ]);
        }
    }

    private function guardarDatosFormularioCompletos($ate_codigo, $usu_id, $datosFormularioJson)
    {
        try {
            $datos = json_decode($datosFormularioJson, true);

            if (!$datos) {
                return;
            }

            // Guardar secciones E-N como ya tienes
            $this->guardarSeccionConDatos($ate_codigo, $usu_id, 'E', $datos['seccionE'] ?? []);
            $this->guardarSeccionConDatos($ate_codigo, $usu_id, 'F', $datos['seccionF'] ?? []);
            $this->guardarSeccionConDatos($ate_codigo, $usu_id, 'H', $datos['seccionH'] ?? []);
            $this->guardarSeccionConDatos($ate_codigo, $usu_id, 'I', $datos['seccionI'] ?? []);
            $this->guardarSeccionConDatos($ate_codigo, $usu_id, 'J', $datos['seccionJ'] ?? []);
            $this->guardarSeccionConDatos($ate_codigo, $usu_id, 'K', $datos['seccionK'] ?? []);
            $this->guardarSeccionConDatos($ate_codigo, $usu_id, 'L', $datos['seccionL'] ?? []);
            $this->guardarSeccionConDatos($ate_codigo, $usu_id, 'M', $datos['seccionM'] ?? []);
            $this->guardarSeccionConDatos($ate_codigo, $usu_id, 'N', $datos['seccionN'] ?? []);

            // AGREGAR GUARDADO DE SECCIÓN O
            $this->guardarSeccionO($ate_codigo, $usu_id, true); // true = enviado a observación
        } catch (\Exception $e) {
            // Manejar error silenciosamente o registrar si es necesario
        }
    }

    private function guardarSeccionConDatos($ate_codigo, $usu_id, $seccion, $datos)
    {
        if (empty($datos))
            return;

        switch ($seccion) {
            case 'E':
                $this->guardarAntecedentesConDatos($ate_codigo, $datos);
                break;
            case 'F':
                $this->guardarProblemaActualConDatos($ate_codigo, $datos);
                break;
            case 'H':
                $this->guardarExamenFisicoConDatos($ate_codigo, $datos);
                break;
            case 'I':
                $this->guardarExamenTraumaConDatos($ate_codigo, $datos);
                break;
            case 'J':
                $this->guardarEmbarazoPartoConDatos($ate_codigo, $datos);
                break;
            case 'K':
                $this->guardarExamenesComplementariosConDatos($ate_codigo, $datos);
                break;
            case 'L':
                $this->guardarDiagnosticosPresuntivosConDatos($ate_codigo, $datos);
                break;
            case 'M':
                $this->guardarDiagnosticosDefinitivosConDatos($ate_codigo, $datos);
                break;
            case 'N':
                $this->guardarTratamientoConDatos($ate_codigo, $datos);
                break;
        }
    }

    // Implementar métodos específicos para cada sección...
    private function guardarAntecedentesConDatos($ate_codigo, $datos)
    {
        $antecedenteModel = new \App\Models\Medicos\GuardarSecciones\AntecedentePacienteModel();
        $antecedenteModel->where('ate_codigo', $ate_codigo)->delete();

        $noAplica = $datos['no_aplica'] ?? false;

        if ($noAplica) {
            $antecedenteModel->insert([
                'ate_codigo' => $ate_codigo,
                'tan_codigo' => null,
                'ap_descripcion' => null,
                'ap_no_aplica' => 1
            ]);
        } else {
            $antecedentes = $datos['antecedentes'] ?? [];
            $descripcion = $datos['descripcion'] ?? '';

            if (!empty($antecedentes)) {
                foreach ($antecedentes as $antecedente) {
                    $antecedenteModel->insert([
                        'ate_codigo' => $ate_codigo,
                        'tan_codigo' => intval($antecedente),
                        'ap_descripcion' => $descripcion,
                        'ap_no_aplica' => 0
                    ]);
                }
            } else if (!empty($descripcion)) {
                $antecedenteModel->insert([
                    'ate_codigo' => $ate_codigo,
                    'tan_codigo' => 10, // "Otros"
                    'ap_descripcion' => $descripcion,
                    'ap_no_aplica' => 0
                ]);
            }
        }
    }


    // SECCIÓN F: PROBLEMA ACTUAL
    private function guardarProblemaActualConDatos($ate_codigo, $datos)
    {
        $problemaActualModel = new \App\Models\Medicos\GuardarSecciones\ProblemaActualModel();
        $problemaActualModel->where('ate_codigo', $ate_codigo)->delete();

        $descripcion = $datos['descripcion'] ?? '';
        if (!empty($descripcion)) {
            $problemaActualModel->insert([
                'ate_codigo' => $ate_codigo,
                'pro_descripcion' => $descripcion
            ]);
        }
    }

    // SECCIÓN H: EXAMEN FÍSICO
    private function guardarExamenFisicoConDatos($ate_codigo, $datos)
    {
        $examenFisicoModel = new \App\Models\Medicos\GuardarSecciones\ExamenFisicoModel();
        $examenFisicoModel->where('ate_codigo', $ate_codigo)->delete();

        $zonas = $datos['zonas'] ?? [];
        $descripcion = $datos['descripcion'] ?? '';

        if (!empty($zonas)) {
            foreach ($zonas as $zona) {
                $examenFisicoModel->insert([
                    'ate_codigo' => $ate_codigo,
                    'zef_codigo' => intval($zona),
                    'ef_presente' => true,
                    'ef_descripcion' => $descripcion
                ]);
            }
        } else if (!empty($descripcion)) {
            $examenFisicoModel->insert([
                'ate_codigo' => $ate_codigo,
                'zef_codigo' => 1,
                'ef_presente' => false,
                'ef_descripcion' => $descripcion
            ]);
        }
    }

    // SECCIÓN I: EXAMEN DE TRAUMA
    private function guardarExamenTraumaConDatos($ate_codigo, $datos)
    {
        $examenTraumaModel = new \App\Models\Medicos\GuardarSecciones\ExamenTraumaModel();
        $examenTraumaModel->where('ate_codigo', $ate_codigo)->delete();

        $descripcion = $datos['descripcion'] ?? '';
        if (!empty($descripcion)) {
            $examenTraumaModel->insert([
                'ate_codigo' => $ate_codigo,
                'tra_descripcion' => $descripcion
            ]);
        }
    }

    // SECCIÓN J: EMBARAZO Y PARTO
    private function guardarEmbarazoPartoConDatos($ate_codigo, $datos)
    {
        $embarazoPartoModel = new \App\Models\Medicos\GuardarSecciones\EmbarazoPartoModel();
        $embarazoPartoModel->where('ate_codigo', $ate_codigo)->delete();

        $noAplica = $datos['no_aplica'] ?? false;

        // Solo insertar si marcó "No aplica" O si hay datos reales
        $hayDatos = false;

        if ($noAplica) {
            // Si marca "No aplica", insertar registro
            $hayDatos = true;
        } else {
            // Verificar si hay al menos un campo con datos
            $campos = [
                'gestas',
                'partos',
                'abortos',
                'cesareas',
                'fum',
                'semanas_gestacion',
                'movimiento_fetal',
                'fcf',
                'ruptura_membranas',
                'tiempo_ruptura',
                'afu',
                'presentacion',
                'sangrado_vaginal',
                'contracciones',
                'dilatacion',
                'borramiento',
                'plano',
                'pelvis_viable',
                'score_mama',
                'observaciones'
            ];

            foreach ($campos as $campo) {
                $valor = $datos[$campo] ?? null;
                if (!empty($valor) && $valor !== '0' && $valor !== '0000-00-00') {
                    $hayDatos = true;
                    break;
                }
            }
        }

        // Solo insertar si hay datos reales
        if ($hayDatos) {
            $dataEmbarazo = [
                'ate_codigo' => $ate_codigo,
                'emb_no_aplica' => $noAplica ? 1 : 0,
                'emb_numero_gestas' => $noAplica ? null : ($datos['gestas'] ?? null),
                'emb_numero_partos' => $noAplica ? null : ($datos['partos'] ?? null),
                'emb_numero_abortos' => $noAplica ? null : ($datos['abortos'] ?? null),
                'emb_numero_cesareas' => $noAplica ? null : ($datos['cesareas'] ?? null),
                'emb_fum' => $noAplica ? null : ($datos['fum'] ?? null),
                'emb_semanas_gestacion' => $noAplica ? null : ($datos['semanas_gestacion'] ?? null),
                'emb_movimiento_fetal' => $noAplica ? null : ($datos['movimiento_fetal'] ?? null),
                'emb_frecuencia_cardiaca_fetal' => $noAplica ? null : ($datos['fcf'] ?? null),
                'emb_ruptura_menbranas' => $noAplica ? null : ($datos['ruptura_membranas'] ?? null),
                'emb_tiempo' => $noAplica ? null : ($datos['tiempo_ruptura'] ?? null),
                'emb_afu' => $noAplica ? null : ($datos['afu'] ?? null),
                'emb_presentacion' => $noAplica ? null : ($datos['presentacion'] ?? null),
                'emb_sangrado_vaginal' => $noAplica ? null : ($datos['sangrado_vaginal'] ?? null),
                'emb_contracciones' => $noAplica ? null : ($datos['contracciones'] ?? null),
                'emb_dilatacion' => $noAplica ? null : ($datos['dilatacion'] ?? null),
                'emb_borramiento' => $noAplica ? null : ($datos['borramiento'] ?? null),
                'emb_plano' => $noAplica ? null : ($datos['plano'] ?? null),
                'emb_pelvis_viable' => $noAplica ? null : ($datos['pelvis_viable'] ?? null),
                'emb_score_mama' => $noAplica ? null : ($datos['score_mama'] ?? null),
                'emb_observaciones' => $noAplica ? null : ($datos['observaciones'] ?? null)
            ];

            $embarazoPartoModel->insert($dataEmbarazo);
        }
    }

    // SECCIÓN K: EXÁMENES COMPLEMENTARIOS
    private function guardarExamenesComplementariosConDatos($ate_codigo, $datos)
    {
        $examenesComplementariosModel = new \App\Models\Medicos\GuardarSecciones\ExamenesComplementariosModel();
        $examenesComplementariosModel->where('ate_codigo', $ate_codigo)->delete();

        $noAplica = $datos['no_aplica'] ?? false;
        $tipos = $datos['tipos'] ?? [];
        $observaciones = $datos['observaciones'] ?? '';

        if ($noAplica) {
            $examenesComplementariosModel->insert([
                'ate_codigo' => $ate_codigo,
                'tipo_id' => null,
                'exa_no_aplica' => 1,
                'exa_observaciones' => null
            ]);
        } else {
            if (!empty($tipos)) {
                foreach ($tipos as $tipo) {
                    $examenesComplementariosModel->insert([
                        'ate_codigo' => $ate_codigo,
                        'tipo_id' => intval($tipo),
                        'exa_no_aplica' => 0,
                        'exa_observaciones' => $observaciones
                    ]);
                }
            } else if (!empty($observaciones)) {
                $examenesComplementariosModel->insert([
                    'ate_codigo' => $ate_codigo,
                    'tipo_id' => 16, // "Otros"
                    'exa_no_aplica' => 0,
                    'exa_observaciones' => $observaciones
                ]);
            }
        }
    }

    // SECCIÓN L: DIAGNÓSTICOS PRESUNTIVOS
    private function guardarDiagnosticosPresuntivosConDatos($ate_codigo, $datos)
    {
        $diagnosticoPresuntivoModel = new \App\Models\Medicos\GuardarSecciones\DiagnosticoPresuntivoModel();
        $diagnosticoPresuntivoModel->where('ate_codigo', $ate_codigo)->delete();

        $hayDiagnosticos = false;

        for ($i = 1; $i <= 3; $i++) {
            $diagnostico = $datos["diagnostico$i"] ?? null;
            if ($diagnostico && !empty($diagnostico['descripcion'])) {
                $diagnosticoPresuntivoModel->insert([
                    'ate_codigo' => $ate_codigo,
                    'diagp_descripcion' => $diagnostico['descripcion'],
                    'diagp_cie' => !empty($diagnostico['cie']) ? $diagnostico['cie'] : null
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

    // SECCIÓN M: DIAGNÓSTICOS DEFINITIVOS
    private function guardarDiagnosticosDefinitivosConDatos($ate_codigo, $datos)
    {
        $diagnosticoDefinitivoModel = new \App\Models\Medicos\GuardarSecciones\DiagnosticoDefinitivoModel();
        $diagnosticoDefinitivoModel->where('ate_codigo', $ate_codigo)->delete();

        $hayDiagnosticos = false;

        for ($i = 1; $i <= 3; $i++) {
            $diagnostico = $datos["diagnostico$i"] ?? null;
            if ($diagnostico && !empty($diagnostico['descripcion'])) {
                $diagnosticoDefinitivoModel->insert([
                    'ate_codigo' => $ate_codigo,
                    'diagd_descripcion' => $diagnostico['descripcion'],
                    'diagd_cie' => !empty($diagnostico['cie']) ? $diagnostico['cie'] : null
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

    // SECCIÓN N: TRATAMIENTO
    private function guardarTratamientoConDatos($ate_codigo, $datos)
    {
        $tratamientoModel = new \App\Models\Medicos\GuardarSecciones\TratamientoModel();
        $tratamientoModel->where('ate_codigo', $ate_codigo)->delete();

        $planGeneral = $datos['plan_general'] ?? '';
        $tratamientos = $datos['tratamientos'] ?? [];
        $hayTratamientos = false;

        // Guardar tratamientos específicos
        foreach ($tratamientos as $tratamiento) {
            if (!empty($tratamiento['medicamento'])) {
                // Verificar si ya existe este tratamiento para evitar duplicar IDs
                $tratamientoExistente = $tratamientoModel->where([
                    'ate_codigo' => $ate_codigo,
                    'trat_medicamento' => $tratamiento['medicamento']
                ])->first();

                $datosTratamiento = [
                    'ate_codigo' => $ate_codigo,
                    'trat_medicamento' => $tratamiento['medicamento'],
                    'trat_via' => !empty($tratamiento['via']) ? $tratamiento['via'] : null,
                    'trat_dosis' => !empty($tratamiento['dosis']) ? $tratamiento['dosis'] : null,
                    'trat_posologia' => !empty($tratamiento['posologia']) ? $tratamiento['posologia'] : null,
                    'trat_dias' => !empty($tratamiento['dias']) ? intval($tratamiento['dias']) : null,
                    'trat_observaciones' => $planGeneral,
                    'trat_administrado' => intval($tratamiento['administrado'] ?? 0)  // AGREGADO: Campo administrado
                ];

                if ($tratamientoExistente) {
                    // Actualizar registro existente (mantiene el mismo trat_id)
                    $tratamientoModel->update($tratamientoExistente['trat_id'], $datosTratamiento);
                } else {
                    // Insertar nuevo registro
                    $tratamientoModel->insert($datosTratamiento);
                }

                $hayTratamientos = true;
            }
        }

        // Solo guardar si hay plan general con contenido real
        if (!$hayTratamientos && !empty($planGeneral) && trim($planGeneral) !== '') {
            $datosPlan = [
                'ate_codigo' => $ate_codigo,
                'trat_medicamento' => null,
                'trat_via' => null,
                'trat_dosis' => null,
                'trat_posologia' => null,
                'trat_dias' => null,
                'trat_observaciones' => $planGeneral,
                'trat_administrado' => 0
            ];

            $tratamientoModel->insert($datosPlan);
        }
        // Si no hay tratamientos ni plan general, no se inserta nada
    }

    // guardar Sección O cuando se envía a observación
    private function guardarSeccionO($ate_codigo, $usu_id, $enviadoAObservacion = false)
    {
        $egresoEmergenciaModel = new \App\Models\Medicos\GuardarSecciones\EgresoEmergenciaModel();
        $egresoEmergenciaModel->where('ate_codigo', $ate_codigo)->delete();

        // Si hay datos del formulario en la petición, usarlos
        $request = \Config\Services::request();

        // Intentar obtener datos del formulario actual
        $estadosEgreso = [];
        $modalidadesEgreso = [];
        $tiposEgreso = [];
        $observacionesEgreso = '';
        $diasReposo = 0;
        $establecimiento = '';

        // Si hay datos en la petición POST, usarlos
        if ($request->getPost('estados_egreso')) {
            $estadosEgreso = $request->getPost('estados_egreso');
        }
        if ($request->getPost('modalidades_egreso')) {
            $modalidadesEgreso = $request->getPost('modalidades_egreso');
        }
        if ($request->getPost('tipos_egreso')) {
            $tiposEgreso = $request->getPost('tipos_egreso');
        }
        if ($request->getPost('egreso_observacion')) {
            $observacionesEgreso = $request->getPost('egreso_observacion');
        }
        if ($request->getPost('egreso_dias_reposo')) {
            $diasReposo = $request->getPost('egreso_dias_reposo');
        }
        if ($request->getPost('egreso_establecimiento')) {
            $establecimiento = $request->getPost('egreso_establecimiento');
        }

        // Datos comunes
        $datosComunes = [
            'egr_establecimiento' => $establecimiento,
            'egr_observaciones' => $observacionesEgreso ?: ($enviadoAObservacion ? 'Enviado a observación de emergencia' : null),
            'egr_dias_reposo' => intval($diasReposo),
            'egr_observacion_emergencia' => $enviadoAObservacion ? 1 : 0
        ];

        $registrosCreados = 0;

        // Si no hay selecciones del formulario, crear registro básico
        if (empty($estadosEgreso) && empty($modalidadesEgreso) && empty($tiposEgreso)) {
            $data = array_merge($datosComunes, [
                'ate_codigo' => $ate_codigo,
                'ese_codigo' => null,
                'moe_codigo' => $enviadoAObservacion ? 3 : null, // 3 = OBSERVACIÓN DE EMERGENCIA
                'tie_codigo' => null
            ]);

            $resultado = $egresoEmergenciaModel->insert($data);
            if ($resultado) {
                $registrosCreados++;
            }
        } else {
            // Crear registros para cada selección del formulario

            // Estados de egreso
            foreach ($estadosEgreso as $estadoCodigo) {
                $data = array_merge($datosComunes, [
                    'ate_codigo' => $ate_codigo,
                    'ese_codigo' => intval($estadoCodigo),
                    'moe_codigo' => null, // Estados no deben tener modalidad asignada
                    'tie_codigo' => null
                ]);

                $resultado = $egresoEmergenciaModel->insert($data);
                if ($resultado) {
                    $registrosCreados++;
                }
            }

            // Modalidades de egreso
            foreach ($modalidadesEgreso as $modalidadCodigo) {
                $data = array_merge($datosComunes, [
                    'ate_codigo' => $ate_codigo,
                    'ese_codigo' => null,
                    'moe_codigo' => intval($modalidadCodigo),
                    'tie_codigo' => null
                ]);

                $resultado = $egresoEmergenciaModel->insert($data);
                if ($resultado) {
                    $registrosCreados++;
                }
            }

            // Tipos de egreso
            foreach ($tiposEgreso as $tipoCodigo) {
                $data = array_merge($datosComunes, [
                    'ate_codigo' => $ate_codigo,
                    'ese_codigo' => null,
                    'moe_codigo' => null, // Tipos no deben tener modalidad asignada
                    'tie_codigo' => intval($tipoCodigo)
                ]);

                $resultado = $egresoEmergenciaModel->insert($data);
                if ($resultado) {
                    $registrosCreados++;
                }
            }

            // Asegurar que modalidad 3 esté presente si fue enviado a observación
            if ($enviadoAObservacion && !in_array('3', $modalidadesEgreso)) {
                $data = array_merge($datosComunes, [
                    'ate_codigo' => $ate_codigo,
                    'ese_codigo' => null,
                    'moe_codigo' => 3, // Forzar modalidad observación
                    'tie_codigo' => null
                ]);

                $resultado = $egresoEmergenciaModel->insert($data);
                if ($resultado) {
                    $registrosCreados++;
                }
            }
        }

        return $registrosCreados > 0;
    }

    /**
     * Verificar si un paciente fue enviado desde otra especialidad
     */
    private function verificarSiEsEnviado($ate_codigo)
    {
        $db = \Config\Database::connect();

        $query = $db->query("
            SELECT 
                oe.obs_motivo,
                oe.obs_fecha_envio,
                oe.obs_hora_envio,
                e.esp_nombre as especialidad_origen,
                u.usu_nombre,
                u.usu_apellido
            FROM t_observacion_especialidad oe
            JOIN t_especialidad e ON oe.esp_codigo_origen = e.esp_codigo
            JOIN t_usuario u ON oe.usu_id_envia = u.usu_id
            WHERE oe.ate_codigo = ?
            AND oe.obs_estado = 'ENVIADO_A_OBSERVACION'
            ORDER BY oe.obs_fecha_envio DESC, oe.obs_hora_envio DESC
            LIMIT 1
        ", [$ate_codigo]);

        $resultado = $query->getRowArray();

        if ($resultado) {
            return [
                'motivo' => $resultado['obs_motivo'],
                'fecha_envio' => $resultado['obs_fecha_envio'],
                'hora_envio' => $resultado['obs_hora_envio'],
                'especialidad_origen' => $resultado['especialidad_origen'],
                'usuario_que_envio' => $resultado['usu_nombre'] . ' ' . $resultado['usu_apellido']
            ];
        }

        return false;
    }

    /**
     * Obtener nombre de especialidad
     */
    private function obtenerNombreEspecialidad($esp_codigo)
    {
        $db = \Config\Database::connect();
        $especialidad = $db->table('t_especialidad')
            ->select('esp_nombre')
            ->where('esp_codigo', $esp_codigo)
            ->get()
            ->getRowArray();

        return $especialidad['esp_nombre'] ?? 'Especialidad desconocida';
    }

    /**
     * Actualizar registro de observación cuando un médico toma la atención
     */
    private function actualizarRegistroObservacionRecibida($ate_codigo, $usu_id_recibe)
    {
        try {
            $observacionModel = new ObservacionEspecialidadModel();

            // Buscar el registro de observación activo para esta atención
            $registroObservacion = $observacionModel->where([
                'ate_codigo' => $ate_codigo,
                'obs_estado' => 'ENVIADO_A_OBSERVACION'
            ])->first();

            if ($registroObservacion) {
                $datosActualizacion = [
                    'usu_id_recibe' => $usu_id_recibe,
                    'obs_fecha_recepcion' => $this->getFechaHoy(),
                    'obs_hora_recepcion' => $this->getHoraActual(),
                    'obs_estado' => 'RECIBIDO_EN_OBSERVACION'
                ];

                // Actualizar con los datos de recepción
                $observacionModel->update($registroObservacion['obs_codigo'], $datosActualizacion);
            }

        } catch (\Exception $e) {
            // Manejar error silenciosamente
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
                    'mensaje' => "Este formulario de especialidad ya fue completado por: {$nombreEspecialista}. No se puede enviar a observación.",
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