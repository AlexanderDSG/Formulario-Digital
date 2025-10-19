<?php

namespace App\Controllers\Especialidades;

use App\Controllers\BaseController;
use App\Models\Especialidades\AreaAtencionModel;

class EnfermeriaEspecialidadController extends BaseController
{
    /**
     * Recibir datos del especialista y guardarlos en tablas correspondientes
     */
    public function recibirDatosEspecialista()
    {
        try {
            if (!$this->request->isAJAX()) {
                throw new \Exception('Acceso no autorizado');
            }

            $input = json_decode(file_get_contents('php://input'), true);
            $ate_codigo = $input['ate_codigo'] ?? null;
            $are_codigo = $input['are_codigo'] ?? null;
            $datos_formulario = $input['datos_formulario'] ?? [];
            $esDevolucionEnfermeria = $input['esDevolucionEnfermeria'] ?? false;
            $usuario_envia = $input['usuario_envia'] ?? [];
            $origen = $input['origen'] ?? 'medico';
            $destino = $input['destino'] ?? 'enfermeria';

            if (!$ate_codigo || !$are_codigo) {
                throw new \Exception('Datos requeridos faltantes');
            }

            $tipoOperacion = $esDevolucionEnfermeria ? 'DEVOLVER A MÉDICO' : 'ENVIAR A ENFERMERÍA';

            $usuario_id = session()->get('usu_id');
            if (!$usuario_id) {
                throw new \Exception('Usuario no autenticado');
            }

            $db = \Config\Database::connect();
            $db->transStart();

            // 1. Guardar datos del formulario en sus tablas correspondientes
            if (!empty($datos_formulario)) {
                $this->guardarDatosFormularioEnTablas($ate_codigo, $datos_formulario);
            }

            if ($esDevolucionEnfermeria) {
                // Enfermería devuelve a médico

                // Actualizar estado para devolución a médico
                $this->devolverAMedico($ate_codigo, $are_codigo, $usuario_envia);

                $mensaje = 'Paciente devuelto al médico correctamente';
            } else {
                // Médico envía a enfermería

                // 2. Crear registro en enfermería de especialidad
                $this->crearRegistroEnfermeria($ate_codigo, $are_codigo);

                // 3. Actualizar estado del área de atención
                $this->actualizarEstadoArea($are_codigo);

                $mensaje = 'Paciente enviado a enfermería de especialidad correctamente';
            }

            $db->transComplete();

            if ($db->transStatus() === FALSE) {
                throw new \Exception('Error en la transacción de base de datos');
            }


            return $this->response->setJSON([
                'success' => true,
                'message' => $mensaje,
                'esDevolucionEnfermeria' => $esDevolucionEnfermeria,
                'ate_codigo' => $ate_codigo
            ]);

        } catch (\Exception $e) {

            return $this->response->setJSON([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
    }

    /**
     * Guardar datos del formulario en tablas correspondientes
     */
    private function guardarDatosFormularioEnTablas($ate_codigo, $datos_formulario)
    {
        try {
            if (!$datos_formulario) {
                return;
            }

            // Guardar cada sección en su tabla correspondiente
            $this->guardarSeccionConDatos($ate_codigo, 'E', $datos_formulario['seccionE'] ?? []);
            $this->guardarSeccionConDatos($ate_codigo, 'F', $datos_formulario['seccionF'] ?? []);
            $this->guardarSeccionConDatos($ate_codigo, 'H', $datos_formulario['seccionH'] ?? []);
            $this->guardarSeccionConDatos($ate_codigo, 'I', $datos_formulario['seccionI'] ?? []);
            $this->guardarSeccionConDatos($ate_codigo, 'J', $datos_formulario['seccionJ'] ?? []);
            $this->guardarSeccionConDatos($ate_codigo, 'K', $datos_formulario['seccionK'] ?? []);
            $this->guardarSeccionConDatos($ate_codigo, 'L', $datos_formulario['seccionL'] ?? []);
            $this->guardarSeccionConDatos($ate_codigo, 'M', $datos_formulario['seccionM'] ?? []);
            $this->guardarSeccionConDatos($ate_codigo, 'N', $datos_formulario['seccionN'] ?? []);


        } catch (\Exception $e) {
            throw $e;
        }
    }

    /**
     * Guardar sección específica con sus datos
     */
    private function guardarSeccionConDatos($ate_codigo, $seccion, $datos)
    {
        if (empty($datos)) {
            return;
        }

        try {
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


        } catch (\Exception $e) {
            throw $e;
        }
    }

    /**
     * Crear registro en tabla de enfermería especialidad
     */
    private function crearRegistroEnfermeria($ate_codigo, $are_codigo)
    {
        $areaAtencionModel = new AreaAtencionModel();
        $enfermeriaModel = new \App\Models\Especialidades\EnfermeriaEspecialidadModel();

        // Obtener información del área actual
        $areaActual = $areaAtencionModel->find($are_codigo);
        if (!$areaActual) {
            throw new \Exception('Área de atención no encontrada');
        }

        $usu_id_que_envia = $areaActual['are_medico_asignado'];
        if (!$usu_id_que_envia) {
            throw new \Exception('No hay médico asignado a esta atención');
        }

        // Crear registro en enfermería de especialidad
        $enfermeriaData = [
            'ate_codigo' => $ate_codigo,
            'are_codigo_origen' => $are_codigo,
            'esp_codigo_origen' => $areaActual['esp_codigo'],
            'usu_id_envia' => $usu_id_que_envia,
            'enf_motivo' => 'Enviado a enfermería de especialidad para cuidados y seguimiento',
            'enf_fecha_envio' => date('Y-m-d'),
            'enf_hora_envio' => date('H:i:s'),
            'enf_estado' => 'ENVIADO_A_ENFERMERIA'
        ];

        $enf_codigo = $enfermeriaModel->insert($enfermeriaData);

        return $enf_codigo;
    }

    /**
     * Actualizar estado del área de atención
     */
    private function actualizarEstadoArea($are_codigo)
    {
        $areaAtencionModel = new AreaAtencionModel();

        $datosActualizacion = [
            'are_estado' => 'ENVIADO_A_ENFERMERIA',
            'are_fecha_fin' => date('Y-m-d'),
            'are_hora_fin' => date('H:i:s')
        ];

        $resultado = $areaAtencionModel->update($are_codigo, $datosActualizacion);

        if (!$resultado) {
            throw new \Exception('No se pudo actualizar el estado del área');
        }

    }

    /**
     * Devolver paciente de enfermería a médico
     */
    private function devolverAMedico($ate_codigo, $are_codigo, $usuario_envia)
    {
        $areaAtencionModel = new AreaAtencionModel();

        // Obtener información del área para determinar el estado correcto
        $areaAtencion = $areaAtencionModel->find($are_codigo);
        if (!$areaAtencion) {
            throw new \Exception('Área de atención no encontrada');
        }

        // Determinar el estado correcto según el estado anterior y el contexto
        $estadoAnterior = $areaAtencion['are_estado'] ?? '';

        // Lógica de estados cuando enfermería devuelve a médico:
        // El estado siempre es EN_ATENCION, pero la clasificación en las listas
        // depende de si tiene proceso guardado (aparece en "Continuando Proceso" vs "En Atención")
        $nuevoEstado = 'EN_ATENCION';


        $datosActualizacion = [
            'are_estado' => $nuevoEstado,
            'are_fecha_devolucion_enfermeria' => date('Y-m-d'),
            'are_hora_devolucion_enfermeria' => date('H:i:s'),
            'are_observaciones_enfermeria' => 'Devuelto desde enfermería de especialidad - ' . ($usuario_envia['contexto'] ?? 'Enfermería')
        ];

        $resultado = $areaAtencionModel->update($are_codigo, $datosActualizacion);

        if (!$resultado) {
            throw new \Exception('No se pudo actualizar el estado del área para devolución');
        }

        // Actualizar registro de enfermería
        $this->actualizarRegistroEnfermeria($ate_codigo, 'DEVUELTO_A_MEDICO');

    }

    /**
     * Verificar si existe proceso anterior (para determinar CONTINUANDO_PROCESO vs EN_ATENCION)
     */
    private function verificarProcesoAnterior($ate_codigo)
    {
        $db = \Config\Database::connect();

        try {
            // Verificar si ya hay datos guardados en alguna de las secciones E-N
            // (indica que el médico ya trabajó en este paciente)
            $secciones = [
                't_antecedente_paciente',
                't_problema_actual',
                't_examen_fisico',
                't_examen_trauma',
                't_embarazo_parto',
                't_examenes_complementarios',
                't_diagnostico_presuntivo',
                't_diagnostico_definitivo',
                't_tratamiento'
            ];

            foreach ($secciones as $tabla) {
                $resultado = $db->table($tabla)
                    ->where('ate_codigo', $ate_codigo)
                    ->get()
                    ->getRowArray();

                if (!empty($resultado)) {
                    return true;
                }
            }

            // Si no hay datos en ninguna sección, es primera vez
            return false;

        } catch (\Exception $e) {
            // En caso de error, asumir que es primera vez
            return false;
        }
    }

    /**
     * Actualizar registro de enfermería
     */
    private function actualizarRegistroEnfermeria($ate_codigo, $nuevoEstado)
    {
        $db = \Config\Database::connect();

        $db->table('t_enfermeria_especialidad')
            ->where('ate_codigo', $ate_codigo)
            ->where('enf_estado', 'EN_ATENCION_ENFERMERIA')
            ->update([
                'enf_estado' => $nuevoEstado,
                'enf_fecha_devolucion' => date('Y-m-d'),
                'enf_hora_devolucion' => date('H:i:s')
            ]);

    }

    // ========================================
    // MÉTODOS ESPECÍFICOS POR SECCIÓN (E-N)
    // ========================================

    /**
     * Guardar Sección E - Antecedentes
     */
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

    /**
     * Guardar Sección F - Problema Actual
     */
    private function guardarProblemaActualConDatos($ate_codigo, $datos)
    {
        $problemaModel = new \App\Models\Medicos\GuardarSecciones\ProblemaActualModel();
        $problemaModel->where('ate_codigo', $ate_codigo)->delete();

        $descripcion = trim($datos['descripcion'] ?? '');
        if (!empty($descripcion)) {
            $problemaModel->insert([
                'ate_codigo' => $ate_codigo,
                'pro_descripcion' => $descripcion
            ]);
        }
    }

    /**
     * Guardar Sección H - Examen Físico
     */
    private function guardarExamenFisicoConDatos($ate_codigo, $datos)
    {
        $examenModel = new \App\Models\Medicos\GuardarSecciones\ExamenFisicoModel();
        $examenModel->where('ate_codigo', $ate_codigo)->delete();

        $zonas = $datos['zonas'] ?? [];
        $descripcion = $datos['descripcion'] ?? '';

        if (!empty($zonas)) {
            foreach ($zonas as $zona) {
                $examenModel->insert([
                    'ate_codigo' => $ate_codigo,
                    'zef_codigo' => intval($zona),
                    'ef_descripcion' => $descripcion
                ]);
            }
        } else if (!empty($descripcion)) {
            $examenModel->insert([
                'ate_codigo' => $ate_codigo,
                'zef_codigo' => 1, // Zona por defecto
                'ef_descripcion' => $descripcion
            ]);
        }
    }

    /**
     * Guardar Sección I - Examen Trauma
     */
    private function guardarExamenTraumaConDatos($ate_codigo, $datos)
    {
        $traumaModel = new \App\Models\Medicos\GuardarSecciones\ExamenTraumaModel();
        $traumaModel->where('ate_codigo', $ate_codigo)->delete();

        $descripcion = trim($datos['descripcion'] ?? '');
        if (!empty($descripcion)) {
            $traumaModel->insert([
                'ate_codigo' => $ate_codigo,
                'tra_descripcion' => $descripcion
            ]);
        }
    }

    /**
     * Guardar Sección J - Embarazo y Parto
     */
    private function guardarEmbarazoPartoConDatos($ate_codigo, $datos)
    {
        $embarazoModel = new \App\Models\Medicos\GuardarSecciones\EmbarazoPartoModel();
        $embarazoModel->where('ate_codigo', $ate_codigo)->delete();

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
            $embarazoData = [
                'ate_codigo' => $ate_codigo,
                'emb_no_aplica' => $noAplica ? 1 : 0,
                'emb_numero_gestas' => $datos['gestas'] ?? null,
                'emb_numero_partos' => $datos['partos'] ?? null,
                'emb_numero_abortos' => $datos['abortos'] ?? null,
                'emb_numero_cesareas' => $datos['cesareas'] ?? null,
                'emb_fum' => $datos['fum'] ?? null,
                'emb_semanas_gestacion' => $datos['semanas_gestacion'] ?? null,
                'emb_movimiento_fetal' => $datos['movimiento_fetal'] ?? null,
                'emb_frecuencia_cardiaca_fetal' => $datos['fcf'] ?? null,
                'emb_ruptura_menbranas' => $datos['ruptura_membranas'] ?? null,
                'emb_tiempo' => $datos['tiempo_ruptura'] ?? null,
                'emb_afu' => $datos['afu'] ?? null,
                'emb_presentacion' => $datos['presentacion'] ?? null,
                'emb_sangrado_vaginal' => $datos['sangrado_vaginal'] ?? null,
                'emb_contracciones' => $datos['contracciones'] ?? null,
                'emb_dilatacion' => $datos['dilatacion'] ?? null,
                'emb_borramiento' => $datos['borramiento'] ?? null,
                'emb_plano' => $datos['plano'] ?? null,
                'emb_pelvis_viable' => $datos['pelvis_viable'] ?? null,
                'emb_score_mama' => $datos['score_mama'] ?? null,
                'emb_observaciones' => $datos['observaciones'] ?? null
            ];

            $embarazoModel->insert($embarazoData);
        } else {
        }
    }

    /**
     * Guardar Sección K - Exámenes Complementarios
     */
    private function guardarExamenesComplementariosConDatos($ate_codigo, $datos)
    {
        $examenModel = new \App\Models\Medicos\GuardarSecciones\ExamenesComplementariosModel();

        $examenModel->where('ate_codigo', $ate_codigo)->delete();

        $noAplica = $datos['no_aplica'] ?? false;
        $tipos = $datos['tipos'] ?? [];
        $observaciones = $datos['observaciones'] ?? '';

        if ($noAplica) {
            $examenModel->insert([
                'ate_codigo' => $ate_codigo,
                'tipo_id' => null,
                'exa_observaciones' => null,
                'exa_no_aplica' => 1
            ]);
        } else if (!empty($tipos)) {
            foreach ($tipos as $tipo) {
                $examenModel->insert([
                    'ate_codigo' => $ate_codigo,
                    'tipo_id' => intval($tipo),
                    'exa_observaciones' => $observaciones,
                    'exa_no_aplica' => 0
                ]);
            }
        } else if (!empty($observaciones)) {
            $examenModel->insert([
                'ate_codigo' => $ate_codigo,
                'tipo_id' => 1, // Tipo por defecto
                'exa_observaciones' => $observaciones,
                'exa_no_aplica' => 0
            ]);
        }
    }

    /**
     * Guardar Sección L - Diagnósticos Presuntivos
     */
    private function guardarDiagnosticosPresuntivosConDatos($ate_codigo, $datos)
    {
        $diagModel = new \App\Models\Medicos\GuardarSecciones\DiagnosticoPresuntivoModel();
        $diagModel->where('ate_codigo', $ate_codigo)->delete();

        for ($i = 1; $i <= 3; $i++) {
            $diagnostico = $datos["diagnostico$i"] ?? [];
            $descripcion = trim($diagnostico['descripcion'] ?? '');
            $cie = trim($diagnostico['cie'] ?? '');

            if (!empty($descripcion)) {
                $diagModel->insert([
                    'ate_codigo' => $ate_codigo,
                    'diagp_descripcion' => $descripcion,
                    'diagp_cie' => $cie ?: null
                ]);
            }
        }
    }

    /**
     * Guardar Sección M - Diagnósticos Definitivos
     */
    private function guardarDiagnosticosDefinitivosConDatos($ate_codigo, $datos)
    {
        $diagModel = new \App\Models\Medicos\GuardarSecciones\DiagnosticoDefinitivoModel();
        $diagModel->where('ate_codigo', $ate_codigo)->delete();

        for ($i = 1; $i <= 3; $i++) {
            $diagnostico = $datos["diagnostico$i"] ?? [];
            $descripcion = trim($diagnostico['descripcion'] ?? '');
            $cie = trim($diagnostico['cie'] ?? '');

            if (!empty($descripcion)) {
                $diagModel->insert([
                    'ate_codigo' => $ate_codigo,
                    'diagd_descripcion' => $descripcion,
                    'diagd_cie' => $cie ?: null
                ]);
            }
        }
    }

    /**
     * Guardar Sección N - Tratamiento
     */
    private function guardarTratamientoConDatos($ate_codigo, $datos)
    {
        $tratModel = new \App\Models\Medicos\GuardarSecciones\TratamientoModel();

        // NO BORRAR TODOS - Usar lógica de UPDATE como EspecialidadController para preservar trat_id
        $tratamientosExistentes = $tratModel->where('ate_codigo', $ate_codigo)->findAll();
        $tratamientosExistentesPorMedicamento = [];


        foreach ($tratamientosExistentes as $trat) {
            if (!empty($trat['trat_medicamento'])) {
                $tratamientosExistentesPorMedicamento[$trat['trat_medicamento']] = $trat;
            }
        }

        $planGeneral = trim($datos['plan_general'] ?? '');
        $tratamientos = $datos['tratamientos'] ?? [];
        $medicamentosEnFormulario = [];

        // 🔍 DEBUG: Ver exactamente qué datos envía el frontend

        // NUEVA LÓGICA: Identificar por trat_id directo enviado desde el formulario
        foreach ($tratamientos as $index => $tratamiento) {
            $medicamento = trim($tratamiento['medicamento'] ?? '');
            $tratId = isset($tratamiento['trat_id']) && !empty($tratamiento['trat_id']) ? intval($tratamiento['trat_id']) : null;

            if (!empty($medicamento)) {

                $datosTratamiento = [
                    'ate_codigo' => $ate_codigo,
                    'trat_medicamento' => $medicamento,
                    'trat_via' => $tratamiento['via'] ?? null,
                    'trat_dosis' => $tratamiento['dosis'] ?? null,
                    'trat_posologia' => $tratamiento['posologia'] ?? null,
                    'trat_dias' => !empty($tratamiento['dias']) ? intval($tratamiento['dias']) : null,
                    'trat_observaciones' => $planGeneral,
                    'trat_administrado' => intval($tratamiento['administrado'] ?? 0)
                ];

                if ($tratId) {
                    // ACTUALIZAR usando el trat_id específico (MANTIENE EL MISMO ID)
                    $resultado = $tratModel->update($tratId, $datosTratamiento);

                    // Verificar que la actualización fue exitosa
                    if (!$resultado) {
                    }
                } else {
                    // INSERTAR nuevo registro solo si no tiene trat_id
                    $nuevoId = $tratModel->insert($datosTratamiento);
                }
            }
        }

        // MANEJAR Plan de tratamiento por separado
        if (!empty($planGeneral) && trim($planGeneral) !== '') {
            $planExistente = $tratamientosExistentesPorMedicamento['Plan de tratamiento'] ?? null;

            $datosPlan = [
                'ate_codigo' => $ate_codigo,
                'trat_medicamento' => null,
                'trat_observaciones' => $planGeneral,
                'trat_via' => null,
                'trat_dosis' => null,
                'trat_posologia' => null,
                'trat_dias' => null,
                'trat_administrado' => 0
            ];

            if ($planExistente) {
                $tratModel->update($planExistente['trat_id'], $datosPlan);
            } else {
                $tratModel->insert($datosPlan);
            }
        }

    }

    /**
     * Enviar paciente a enfermería de especialidades
     */
    public function enviarAEnfermeria()
    {
        try {
            if (!request()->isAJAX()) {
                throw new \Exception('Acceso no autorizado');
            }

            $input = json_decode(file_get_contents('php://input'), true);
            $ate_codigo = $input['ate_codigo'] ?? null;
            $are_codigo = $input['are_codigo'] ?? null;
            $datos_formulario = $input['datos_formulario'] ?? [];

            if (!$ate_codigo || !$are_codigo) {
                throw new \Exception('Datos requeridos faltantes');
            }


            $usuario_id = session()->get('usu_id');
            if (!$usuario_id) {
                throw new \Exception('Usuario no autenticado');
            }

            $db = \Config\Database::connect();
            $areaAtencionModel = new AreaAtencionModel();
            $enfermeriaModel = new \App\Models\Especialidades\EnfermeriaEspecialidadModel();

            // Obtener información del área actual
            $areaActual = $areaAtencionModel->find($are_codigo);
            if (!$areaActual) {
                throw new \Exception('Área de atención no encontrada');
            }

            $usu_id_que_envia = $areaActual['are_medico_asignado'];
            if (!$usu_id_que_envia) {
                throw new \Exception('No hay médico asignado a esta atención');
            }


            $db->transStart();

            // 1. Guardar datos del formulario en tablas correspondientes
            if (!empty($datos_formulario)) {
                $this->guardarDatosFormularioEnTablas($ate_codigo, $datos_formulario);
            }

            // 2. Crear registro en enfermería de especialidad
            $enfermeriaData = [
                'ate_codigo' => $areaActual['ate_codigo'],
                'are_codigo_origen' => $are_codigo,
                'esp_codigo_origen' => $areaActual['esp_codigo'],
                'usu_id_envia' => $usu_id_que_envia,
                'enf_motivo' => 'Enviado a enfermería de especialidad para cuidados y seguimiento',
                'enf_fecha_envio' => date('Y-m-d'),
                'enf_hora_envio' => date('H:i:s'),
                'enf_estado' => 'ENVIADO_A_ENFERMERIA'
            ];

            $enf_codigo = $enfermeriaModel->insert($enfermeriaData);

            // 3. Actualizar área - cambiar estado
            $datosActualizacion = [
                'are_estado' => 'ENVIADO_A_ENFERMERIA',
                'are_observaciones' => 'Enviado a enfermería de especialidad - ' . $this->obtenerNombreEspecialidad($areaActual['esp_codigo'])
            ];

            $resultadoUpdate = $areaAtencionModel->update($are_codigo, $datosActualizacion);

            if (!$resultadoUpdate) {
                throw new \Exception('Error al actualizar el estado del área de atención');
            }


            // 4. Verificar que se actualizó correctamente
            $areaVerificacion = $areaAtencionModel->find($are_codigo);

            $db->transComplete();

            if ($db->transStatus() === false) {
                throw new \Exception('Error en la transacción');
            }


            return response()->setJSON([
                'success' => true,
                'message' => 'Paciente enviado a enfermería exitosamente',
                'redirect_url' => base_url('especialidades/lista'),
                'enf_codigo' => $enf_codigo,
                'debug' => [
                    'are_codigo' => $are_codigo,
                    'estado_anterior' => $areaActual['are_estado'],
                    'estado_nuevo' => 'ENVIADO_A_ENFERMERIA',
                    'update_resultado' => $resultadoUpdate
                ]
            ]);

        } catch (\Exception $e) {
            return response()->setJSON([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
    }

    /**
     * Tomar atención de enfermería
     */
    public function tomarAtencionEnfermeria($request = null, $response = null)
    {
        try {
            $request = $request ?: request();
            $response = $response ?: response();

            if (!$request->isAJAX()) {
                throw new \Exception('Acceso no autorizado');
            }

            // Obtener datos del POST
            $are_codigo = $request->getPost('are_codigo');
            $usuario = $request->getPost('usuario');
            $password = $request->getPost('password');

            if (!$are_codigo) {
                throw new \Exception('Código de área requerido');
            }

            if (!$usuario || !$password) {
                throw new \Exception('Usuario y contraseña son requeridos');
            }

            $db = \Config\Database::connect();

            // Validar credenciales del usuario
            $usuarioData = $db->table('t_usuario')
                ->where('usu_usuario', $usuario)
                ->where('usu_estado', 'activo')
                ->get()
                ->getRowArray();

            if (!$usuarioData) {
                throw new \Exception('Usuario no encontrado o inactivo');
            }

            // Verificar contraseña
            if (!hash_equals(hash('sha256', $password), $usuarioData['usu_password'])) {
                throw new \Exception('Contraseña incorrecta');
            }

            // Verificar que el usuario sea enfermero (rol_id = 3) o especialista (rol_id = 5)
            if (!in_array($usuarioData['rol_id'], [3, 5])) {
                throw new \Exception('Solo enfermeros y especialistas pueden tomar atenciones de enfermería');
            }

            $usuario_id = $usuarioData['usu_id'];

            $areaAtencionModel = new AreaAtencionModel();
            $enfermeriaModel = new \App\Models\Especialidades\EnfermeriaEspecialidadModel();

            // Verificar que el área existe y está en estado correcto
            $area = $areaAtencionModel->find($are_codigo);
            if (!$area) {
                throw new \Exception('Área de atención no encontrada');
            }

            if ($area['are_estado'] !== 'ENVIADO_A_ENFERMERIA') {
                throw new \Exception('Esta atención no está disponible para enfermería');
            }

            $db->transStart();

            // 1. Actualizar el área de atención - NO cambiar are_medico_asignado (mantener médico original)
            $areaAtencionModel->update($are_codigo, [
                // NO modificar are_medico_asignado - debe mantenerse el médico original
                'are_estado' => 'EN_ATENCION_ENFERMERIA',
                'are_fecha_inicio_atencion' => date('Y-m-d'),
                'are_hora_inicio_atencion' => date('H:i:s'),
                'are_observaciones' => 'Atención tomada por enfermería'
            ]);

            // 2. Actualizar el registro en enfermería_especialidad
            $registroEnfermeria = $enfermeriaModel->where('are_codigo_origen', $are_codigo)
                ->where('enf_estado', 'ENVIADO_A_ENFERMERIA')
                ->first();

            if ($registroEnfermeria) {
                $enfermeriaModel->update($registroEnfermeria['enf_codigo'], [
                    'enf_estado' => 'EN_ATENCION_ENFERMERIA',
                    'enf_fecha_recepcion' => date('Y-m-d'),
                    'enf_hora_recepcion' => date('H:i:s'),
                    'usu_id_recibe' => $usuario_id
                ]);
            }

            $db->transComplete();

            if ($db->transStatus() === false) {
                throw new \Exception('Error en la transacción');
            }

            // Usar los datos del usuario ya validado
            $nombreUsuario = $usuarioData['usu_nombre'] . ' ' . $usuarioData['usu_apellido'];


            return $response->setJSON([
                'success' => true,
                'message' => "Atención tomada exitosamente por: $nombreUsuario",
                'redirect_url' => base_url("especialidades/formulario/{$are_codigo}?enfermeria=1")
            ]);

        } catch (\Exception $e) {
            return $response->setJSON([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
    }

    /**
     * Validar acceso de enfermería para ver formulario
     */
    public function validarAccesoEnfermeria($request = null, $response = null)
    {
        try {
            $request = $request ?: request();
            $response = $response ?: response();

            if (!$request->isAJAX()) {
                throw new \Exception('Acceso no autorizado');
            }

            // Obtener datos del POST
            $are_codigo = $request->getPost('are_codigo');
            $password = $request->getPost('password');

            if (!$are_codigo || !$password) {
                throw new \Exception('Datos requeridos faltantes');
            }

            $usuario_id = session()->get('usu_id');
            if (!$usuario_id) {
                throw new \Exception('Usuario no autenticado');
            }

            $db = \Config\Database::connect();
            $areaAtencionModel = new AreaAtencionModel();

            // Obtener información del área
            $area = $areaAtencionModel->find($are_codigo);
            if (!$area) {
                throw new \Exception('Área de atención no encontrada');
            }

            // Verificar que el área esté en estado correcto
            if ($area['are_estado'] !== 'EN_ATENCION_ENFERMERIA') {
                throw new \Exception('Esta atención no está disponible para enfermería');
            }

            // Obtener el enfermero que tomó la atención desde la tabla enfermería_especialidad
            $registroEnfermeria = $db->table('t_enfermeria_especialidad')
                ->where('are_codigo_origen', $are_codigo)
                ->where('enf_estado', 'EN_ATENCION_ENFERMERIA')
                ->get()
                ->getRowArray();

            if (!$registroEnfermeria || !$registroEnfermeria['usu_id_recibe']) {
                throw new \Exception('No hay enfermero asignado a esta atención');
            }

            $enfermeroId = $registroEnfermeria['usu_id_recibe'];

            // Obtener datos del enfermero que tomó la atención
            $enfermero = $db->table('t_usuario')
                ->where('usu_id', $enfermeroId)
                ->whereIn('rol_id', [3, 5]) // Rol de enfermería (3) o especialista (5)
                ->where('usu_estado', 'activo')
                ->get()
                ->getRowArray();

            if (!$enfermero) {
                throw new \Exception('Enfermero que tomó la atención no encontrado o inactivo');
            }

            // Validar contraseña del enfermero que tomó la atención
            $passwordHash = hash('sha256', $password);
            if ($enfermero['usu_password'] !== $passwordHash) {
                throw new \Exception('Contraseña incorrecta del enfermero asignado');
            }

            // CONTRASEÑA CORRECTA - PERMITIR ACCESO
            $usuarioActual = session()->get('usu_nombre') . ' ' . session()->get('usu_apellido');

            return $response->setJSON([
                'success' => true,
                'message' => 'Validación exitosa. Acceso autorizado.',
                'redirect_url' => base_url("especialidades/formulario/{$are_codigo}?enfermeria=1")
            ]);

        } catch (\Exception $e) {
            return $response->setJSON([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
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
}