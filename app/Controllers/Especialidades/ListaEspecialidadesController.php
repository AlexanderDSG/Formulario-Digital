<?php

namespace App\Controllers\Especialidades;

use App\Controllers\BaseController;
use App\Models\Especialidades\{EspecialidadModel, AreaAtencionModel, ObservacionEspecialidadModel};

class ListaEspecialidadesController extends BaseController
{
    // ==================== VALIDACIÓN DE ACCESO ====================

    private function validarAcceso()
    {
        if (!session()->get('logged_in')) {
            return ['valido' => false, 'redireccion' => '/login'];
        }

        // SOLO rol_id 5 (ESPECIALISTA) puede acceder
        if (session()->get('rol_id') != 5) {
            return ['valido' => false, 'redireccion' => '/', 'error' => 'No tiene permisos para acceder a las especialidades médicas.'];
        }

        return ['valido' => true];
    }

    private function validarAccesoAjax()
    {
        if (!session()->get('logged_in') || session()->get('rol_id') != 5) {
            return ['valido' => false, 'mensaje' => 'No autorizado'];
        }
        return ['valido' => true];
    }

    // ==================== VISTA PRINCIPAL ====================

    /**
     * Vista principal de especialidades con pestañas
     */
    public function index()
    {
        $acceso = $this->validarAcceso();
        if (!$acceso['valido']) {
            return redirect()->to($acceso['redireccion'])
                ->with('error', $acceso['error'] ?? 'No autorizado');
        }

        $data = [];

        try {
            $especialidadModel = new EspecialidadModel();
            $areaAtencionModel = new AreaAtencionModel();

            $especialidades = $especialidadModel->obtenerEspecialidadesActivas();

            // Agregar conteos iniciales a cada especialidad
            foreach ($especialidades as &$especialidad) {
                $pacientes = $areaAtencionModel->obtenerPacientesPorEspecialidad($especialidad['esp_codigo']);

                // Filtrar por estados relevantes para médicos
                $pacientesRelevantes = array_filter($pacientes, function ($p) {
                    return !in_array($p['are_estado'], ['ENVIADO_A_ENFERMERIA', 'EN_ATENCION_ENFERMERIA']);
                });

                $pendientes = array_filter($pacientesRelevantes, function ($p) {
                    return in_array($p['are_estado'], ['PENDIENTE', 'ENVIADO_A_OBSERVACION']);
                });

                $enAtencion = array_filter($pacientesRelevantes, function ($p) {
                    return $p['are_estado'] === 'EN_ATENCION';
                });

                $enProceso = array_filter($pacientesRelevantes, function ($p) {
                    return $p['are_estado'] === 'EN_PROCESO';
                });

                $especialidad['pacientes_pendientes'] = count($pendientes);
                $especialidad['pacientes_en_atencion'] = count($enAtencion);
                $especialidad['pacientes_en_proceso'] = count($enProceso);
                $especialidad['total_pacientes'] = count($pacientesRelevantes);
            }

            $data['especialidades'] = $especialidades;

        } catch (\Exception $e) {
            $data['especialidades'] = [];
            $data['error'] = 'Error al cargar las especialidades: ' . $e->getMessage();
        }

        return view('especialidades/listaEspecialidades', $data);
    }

    // ==================== MÉTODOS AJAX ====================

    /**
     * Obtener pacientes de una especialidad específica vía AJAX
     */
    public function obtenerPacientesEspecialidad($esp_codigo = null)
    {
        $acceso = $this->validarAccesoAjax();
        if (!$acceso['valido']) {
            return $this->response->setJSON(['success' => false, 'error' => $acceso['mensaje']]);
        }

        if (!$esp_codigo) {
            return $this->response->setJSON(['success' => false, 'error' => 'Código de especialidad requerido']);
        }

        try {
            $areaAtencionModel = new AreaAtencionModel();
            $pacientes = $areaAtencionModel->obtenerPacientesPorEspecialidad($esp_codigo);

            $resultado = $this->clasificarPacientesPorEstado($pacientes);

            return $this->response->setJSON([
                'success' => true,
                'pendientes' => $resultado['pendientes'],
                'en_proceso' => $resultado['en_proceso'],
                'en_atencion' => $resultado['en_atencion'],
                'continuando_proceso' => $resultado['continuando_proceso'],
                'total' => $resultado['total']
            ]);

        } catch (\Exception $e) {
            return $this->response->setJSON([
                'success' => false,
                'error' => 'Error interno: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Obtener estadísticas de una especialidad específica
     */
    public function obtenerEstadisticas($esp_codigo)
    {
        $acceso = $this->validarAccesoAjax();
        if (!$acceso['valido']) {
            return $this->response->setJSON(['error' => $acceso['mensaje']]);
        }

        try {
            $areaAtencionModel = new AreaAtencionModel();
            $estadisticas = $areaAtencionModel->obtenerEstadisticasPorEspecialidad($esp_codigo);

            return $this->response->setJSON([
                'success' => true,
                'estadisticas' => $estadisticas
            ]);

        } catch (\Exception $e) {
            return $this->response->setJSON([
                'success' => false,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Ver pacientes completados por el especialista actual
     */
    public function pacientesCompletados()
    {
        $acceso = $this->validarAccesoAjax();
        if (!$acceso['valido']) {
            return $this->response->setJSON(['error' => $acceso['mensaje']]);
        }

        $usu_id = session()->get('usu_id');
        $nombreEspecialista = session()->get('usu_nombre') . ' ' . session()->get('usu_apellido');

        try {
            $areaAtencionModel = new AreaAtencionModel();
            $pacientesCompletados = $areaAtencionModel->obtenerPacientesCompletadosPorEspecialista($usu_id);

            return $this->response->setJSON([
                'success' => true,
                'especialista' => $nombreEspecialista,
                'usu_id' => $usu_id,
                'total_completados' => count($pacientesCompletados),
                'pacientes_completados' => $pacientesCompletados
            ]);

        } catch (\Exception $e) {
            return $this->response->setJSON([
                'success' => false,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Estadísticas del especialista actual
     */
    public function estadisticasEspecialista()
    {
        $acceso = $this->validarAccesoAjax();
        if (!$acceso['valido']) {
            return $this->response->setJSON(['error' => $acceso['mensaje']]);
        }

        $usu_id = session()->get('usu_id');
        $nombreEspecialista = session()->get('usu_nombre') . ' ' . session()->get('usu_apellido');

        try {
            $areaAtencionModel = new AreaAtencionModel();
            $estadisticas = $areaAtencionModel->obtenerEstadisticasEspecialista($usu_id);

            return $this->response->setJSON([
                'success' => true,
                'especialista' => $nombreEspecialista,
                'usu_id' => $usu_id,
                'estadisticas' => $estadisticas
            ]);

        } catch (\Exception $e) {
            return $this->response->setJSON([
                'success' => false,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Obtener atenciones en curso del especialista actual
     */
    public function misAtenciones()
    {
        $acceso = $this->validarAccesoAjax();
        if (!$acceso['valido']) {
            return $this->response->setJSON(['error' => $acceso['mensaje']]);
        }

        try {
            $areaAtencionModel = new AreaAtencionModel();
            $usu_id = session()->get('usu_id');

            $misAtenciones = $areaAtencionModel->obtenerAtencionesEnCursoPorMedico($usu_id);

            return $this->response->setJSON([
                'success' => true,
                'atenciones' => $this->formatearPacientesParaVista($misAtenciones)
            ]);

        } catch (\Exception $e) {
            return $this->response->setJSON([
                'success' => false,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Obtener pacientes de enfermería para una especialidad
     */
    public function obtenerPacientesEnfermeria($esp_codigo = null)
    {
        $acceso = $this->validarAccesoAjax();
        if (!$acceso['valido']) {
            return $this->response->setJSON(['success' => false, 'error' => $acceso['mensaje']]);
        }

        if (!$esp_codigo) {
            return $this->response->setJSON(['success' => false, 'error' => 'Código de especialidad requerido']);
        }

        try {
            $areaAtencionModel = new AreaAtencionModel();
            $pacientesEnfermeria = $areaAtencionModel->obtenerPacientesEnfermeriaPorEspecialidad($esp_codigo);

            $resultado = $this->organizarPacientesEnfermeria($pacientesEnfermeria);

            return $this->response->setJSON([
                'success' => true,
                'data' => [
                    'asignados' => $resultado['asignados'],
                    'pendientes' => $resultado['pendientes'],
                    'especiales' => [],
                    'seguimiento' => []
                ],
                'total' => count($pacientesEnfermeria),
                'debug_counts' => [
                    'asignados' => count($resultado['asignados']),
                    'pendientes' => count($resultado['pendientes'])
                ]
            ]);

        } catch (\Exception $e) {
            return $this->response->setJSON([
                'success' => false,
                'error' => 'Error al obtener datos de enfermería: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Obtener médicos especialistas para una especialidad
     */
    public function obtenerMedicosEspecialidad($esp_codigo = null)
    {
        $acceso = $this->validarAccesoAjax();
        if (!$acceso['valido']) {
            return $this->response->setJSON(['success' => false, 'error' => $acceso['mensaje']]);
        }

        if (!$esp_codigo) {
            return $this->response->setJSON(['success' => false, 'error' => 'Código de especialidad requerido']);
        }

        try {
            $areaAtencionModel = new AreaAtencionModel();
            $pacientesMedicos = $areaAtencionModel->obtenerMedicosEspecialistasPorEspecialidad($esp_codigo);

            $resultado = $this->organizarPacientesMedicos($pacientesMedicos);

            return $this->response->setJSON([
                'success' => true,
                'data' => [
                    'en_atencion' => $resultado['en_atencion'],
                    'en_proceso' => $resultado['en_proceso'],
                    'pendientes' => $resultado['pendientes'],
                    'continuando_proceso' => []
                ],
                'total' => count($pacientesMedicos),
                'debug_counts' => [
                    'en_atencion' => count($resultado['en_atencion']),
                    'en_proceso' => count($resultado['en_proceso']),
                    'pendientes' => count($resultado['pendientes'])
                ]
            ]);

        } catch (\Exception $e) {
            return $this->response->setJSON([
                'success' => false,
                'error' => 'Error al obtener datos de médicos especialistas: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Clasificar pacientes por estado
     */
    private function clasificarPacientesPorEstado($pacientes)
    {
        $pendientes = [];
        $enProceso = [];
        $enAtencionNormal = [];
        $continuandoProceso = [];

        foreach ($pacientes as $paciente) {
            if ($paciente['are_estado'] === 'PENDIENTE') {
                $pendientes[] = $paciente;

            } elseif ($paciente['are_estado'] === 'ENVIADO_A_OBSERVACION') {
                $observacionModel = new ObservacionEspecialidadModel();
                $paciente['tipo_especial'] = 'enviado_observacion';

                $infoEnvio = $observacionModel->obtenerInfoEnvioObservacion($paciente['ate_codigo']);
                $paciente['motivo_envio'] = $infoEnvio['motivo'];
                $paciente['especialidad_origen'] = $infoEnvio['especialidad_origen'];
                $paciente['fecha_envio'] = $infoEnvio['fecha_envio'];
                $paciente['hora_envio'] = $infoEnvio['hora_envio'];
                $paciente['usuario_que_envio'] = $infoEnvio['usuario_que_envio'];

                $pendientes[] = $paciente;

            } elseif ($paciente['are_estado'] === 'ENVIADO_A_ENFERMERIA') {
                continue; // No mostrar en lista médica

            } elseif ($paciente['are_estado'] === 'EN_PROCESO') {
                if ($paciente['ppe_fecha_guardado']) {
                    $especialistaNombre = trim(($paciente['proceso_especialista_nombre'] ?? '') . ' ' . ($paciente['proceso_especialista_apellido'] ?? ''));

                    $paciente['info_proceso'] = [
                        'especialista_nombre' => $especialistaNombre,
                        'fecha_guardado' => $paciente['ppe_fecha_guardado'],
                        'hora_guardado' => $paciente['ppe_hora_guardado'],
                        'observaciones' => $paciente['ppe_observaciones'],
                        'especialidad_nombre' => $paciente['esp_nombre']
                    ];
                }
                $enProceso[] = $paciente;

            } elseif ($paciente['are_estado'] === 'EN_ATENCION') {
                $tieneProcesoParcial = !empty($paciente['ppe_fecha_guardado']);

                if ($tieneProcesoParcial) {
                    $medicoQueGuardo = $paciente['usu_id_especialista'] ?? null;
                    $medicoActual = $paciente['are_medico_asignado'];

                    $esContinuacion = ($medicoQueGuardo != $medicoActual) ||
                        (strpos($paciente['are_observaciones'] ?? '', 'Proceso continuado') !== false);

                    if ($esContinuacion) {
                        $especialistaNombre = trim(($paciente['proceso_especialista_nombre'] ?? '') . ' ' . ($paciente['proceso_especialista_apellido'] ?? ''));

                        $paciente['info_proceso'] = [
                            'especialista_nombre' => $especialistaNombre,
                            'fecha_guardado' => $paciente['ppe_fecha_guardado'],
                            'hora_guardado' => $paciente['ppe_hora_guardado'],
                            'especialista_original' => $especialistaNombre
                        ];
                        $paciente['es_continuacion_proceso'] = true;

                        $continuandoProceso[] = $paciente;
                    } else {
                        $enAtencionNormal[] = $paciente;
                    }
                } else {
                    $fueTomaDoDesdeObservacion = (
                        $paciente['esp_codigo'] == 5 ||
                        strpos($paciente['are_observaciones'] ?? '', 'Enviado a observación') !== false ||
                        !empty($paciente['motivo_observacion'])
                    );

                    if ($fueTomaDoDesdeObservacion) {
                        $paciente['tipo_atencion'] = 'observacion';
                        $paciente['observacion_info'] = [
                            'especialidad_origen' => $paciente['especialidad_origen_nombre'] ?? 'Especialidad origen',
                            'motivo' => $paciente['motivo_observacion'] ?? 'Motivo no registrado'
                        ];
                    }

                    $enAtencionNormal[] = $paciente;
                }
            }
        }

        return [
            'pendientes' => $pendientes,
            'en_proceso' => $enProceso,
            'en_atencion' => $enAtencionNormal,
            'continuando_proceso' => $continuandoProceso,
            'total' => count($pendientes) + count($enProceso) + count($enAtencionNormal) + count($continuandoProceso)
        ];
    }

    /**
     * Organizar pacientes de enfermería
     */
    private function organizarPacientesEnfermeria($pacientesEnfermeria)
    {
        $asignados = [];
        $pendientes = [];
        $procesados = [];

        foreach ($pacientesEnfermeria as $paciente) {
            // Evitar duplicados por are_codigo
            if (isset($procesados[$paciente['are_codigo']])) {
                continue;
            }
            $procesados[$paciente['are_codigo']] = true;

            $nombreCompleto = trim(($paciente['pac_nombres'] ?? '') . ' ' . ($paciente['pac_apellidos'] ?? ''));
            $medicoQueEnvio = trim(($paciente['medico_nombre'] ?? '') . ' ' . ($paciente['medico_apellido'] ?? ''));
            $enfermeroAsignado = '';

            if ($paciente['enfermero_nombre'] && $paciente['enfermero_apellido']) {
                $enfermeroAsignado = trim($paciente['enfermero_nombre'] . ' ' . $paciente['enfermero_apellido']);
            }

            $dataPaciente = [
                'ate_codigo' => $paciente['ate_codigo'],
                'are_codigo' => $paciente['are_codigo'],
                'pac_codigo' => $paciente['pac_codigo'],
                'pac_nombres' => $nombreCompleto,
                'pac_documento' => 'CI: ' . ($paciente['pac_cedula'] ?? 'No registrada'),
                'pac_edad' => ($paciente['pac_edad'] ?? '') . ' ' . ($paciente['pac_edad_unidad'] ?? 'años'),
                'pac_sexo' => $paciente['sexo_descripcion'] ?? 'No especificado',
                'especialidad' => $paciente['especialidad_nombre'],
                'fecha_envio' => $paciente['enf_fecha_envio'],
                'hora_envio' => $paciente['enf_hora_envio'],
                'medico_que_envio' => $medicoQueEnvio ?: 'Médico no identificado',
                'motivo_envio' => $paciente['enf_motivo'] ?? 'Sin motivo especificado',
                'estado_enfermeria' => $paciente['enf_estado'],
                'estado_area' => $paciente['are_estado'],
                'prioridad' => 'Normal'
            ];

            // Clasificar según el estado
            if ($paciente['enf_estado'] === 'EN_ATENCION_ENFERMERIA') {
                $dataPaciente['enfermero_asignado'] = $enfermeroAsignado;
                $asignados[] = $dataPaciente;
            } else if ($paciente['enf_estado'] === 'ENVIADO_A_ENFERMERIA') {
                $pendientes[] = $dataPaciente;
            } else {
                if ($paciente['are_estado'] === 'EN_ATENCION_ENFERMERIA') {
                    $dataPaciente['enfermero_asignado'] = $enfermeroAsignado;
                    $asignados[] = $dataPaciente;
                } else {
                    $pendientes[] = $dataPaciente;
                }
            }
        }

        return [
            'asignados' => $asignados,
            'pendientes' => $pendientes
        ];
    }

    /**
     * Organizar pacientes por médicos
     */
    private function organizarPacientesMedicos($pacientesMedicos)
    {
        $enAtencion = [];
        $enProceso = [];
        $pendientes = [];

        foreach ($pacientesMedicos as $paciente) {
            $nombrePaciente = trim(($paciente['pac_nombres'] ?? '') . ' ' . ($paciente['pac_apellidos'] ?? ''));
            $nombreMedico = trim(($paciente['medico_nombre'] ?? '') . ' ' . ($paciente['medico_apellido'] ?? ''));

            $dataPaciente = [
                'ate_codigo' => $paciente['ate_codigo'],
                'are_codigo' => $paciente['are_codigo'],
                'pac_codigo' => $paciente['pac_codigo'],
                'pac_nombres' => $nombrePaciente,
                'pac_documento' => 'CI: ' . ($paciente['pac_cedula'] ?? 'No registrada'),
                'pac_edad' => ($paciente['pac_edad'] ?? '') . ' ' . ($paciente['pac_edad_unidad'] ?? 'años'),
                'pac_sexo' => $paciente['sexo_descripcion'] ?? 'No especificado',
                'medico_asignado' => $nombreMedico,
                'medico_id' => $paciente['are_medico_asignado'],
                'especialidad' => $paciente['especialidad_nombre'],
                'fecha_asignacion' => $paciente['are_fecha_asignacion'],
                'hora_asignacion' => $paciente['are_hora_asignacion'],
                'hora_inicio_atencion' => $paciente['are_hora_inicio_atencion'],
                'triaje_color' => $paciente['triaje_color'] ?? 'VERDE',
                'estado_area' => $paciente['are_estado'],
                'observaciones' => $paciente['are_observaciones'] ?? ''
            ];

            switch ($paciente['are_estado']) {
                case 'EN_ATENCION':
                    $enAtencion[] = $dataPaciente;
                    break;
                case 'EN_PROCESO':
                    $enProceso[] = $dataPaciente;
                    break;
                case 'PENDIENTE':
                    $pendientes[] = $dataPaciente;
                    break;
            }
        }

        return [
            'en_atencion' => $enAtencion,
            'en_proceso' => $enProceso,
            'pendientes' => $pendientes
        ];
    }

    /**
     * Formatear datos de pacientes para la vista
     */
    private function formatearPacientesParaVista($pacientes)
    {
        $pacientesFormateados = [];

        foreach ($pacientes as $paciente) {
            $pacientesFormateados[] = [
                'are_codigo' => $paciente['are_codigo'],
                'ate_codigo' => $paciente['ate_codigo'],
                'pac_codigo' => $paciente['pac_codigo'] ?? 0,
                'pac_nombres' => $paciente['pac_nombres'] ?? 'N/A',
                'pac_apellidos' => $paciente['pac_apellidos'] ?? '',
                'pac_cedula' => $paciente['pac_cedula'] ?? 'No registrada',
                'triaje_color' => $paciente['triaje_color'] ?? $paciente['ate_colores'] ?? 'VERDE',
                'are_fecha_asignacion' => $paciente['are_fecha_asignacion'] ?? date('Y-m-d'),
                'are_hora_asignacion' => $paciente['are_hora_asignacion'] ?? date('H:i:s'),
                'are_hora_inicio_atencion' => $paciente['are_hora_inicio_atencion'] ?? null,
                'medico_nombre' => ($paciente['medico_nombre'] ?? '') . ' ' . ($paciente['medico_apellido'] ?? ''),
                'medico_actual' => $paciente['are_medico_asignado'] ?? null,
                'esp_nombre' => $paciente['esp_nombre'] ?? 'Especialidad',
                'area_nombre' => $paciente['area_nombre'] ?? 'Área'
            ];
        }

        return $pacientesFormateados;
    }
}
