<?php

namespace App\Controllers\Medicos;

use App\Controllers\BaseController;
use App\Models\AtencionModel;
use App\Models\Medicos\GuardarSecciones\InicioAtencionModel;
use App\Models\Medicos\GuardarSecciones\EventoModel;
use App\Models\Medicos\GuardarSecciones\AntecedentePacienteModel;
use App\Models\Medicos\GuardarSecciones\ProblemaActualModel;
use App\Models\Medicos\GuardarSecciones\ExamenFisicoModel;
use App\Models\Medicos\GuardarSecciones\ExamenTraumaModel;
use App\Models\Medicos\GuardarSecciones\EmbarazoPartoModel;
use App\Models\Medicos\GuardarSecciones\ExamenesComplementariosModel;
use App\Models\Medicos\GuardarSecciones\DiagnosticoPresuntivoModel;
use App\Models\Medicos\GuardarSecciones\DiagnosticoDefinitivoModel;
use App\Models\Medicos\GuardarSecciones\TratamientoModel;
use App\Models\Medicos\GuardarSecciones\EgresoEmergenciaModel;
use App\Models\Medicos\GuardarSecciones\ProfesionalResponsableModel;
use App\Models\Medicos\ListaMedicosModel;
use App\Models\FormularioUsuarioModel;
use App\Models\Especialidades\EspecialidadModel;
use App\Models\Especialidades\AreaAtencionModel;
use App\Models\Administrador\ModificacionesModel;
use Exception;

class MedicosController extends BaseController
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
     * Método modificado para guardar formulario médico
     */
    public function guardarMedico()
    {
        if (!session()->get('logged_in')) {
            return redirect()->to('/login');
        }

        // SOLO rol_id 4 (MEDICO_TRIAJE) puede usar esta funcionalidad
        if (session()->get('rol_id') != 4) {
            return redirect()->to('/medicos/lista')->with('error', 'No tiene permisos para realizar esta acción.');
        }

        $ate_codigo = $this->request->getPost('ate_codigo');
        $usu_id = session()->get('usu_id');

        // VALIDACIÓN MEJORADA
        if (empty($ate_codigo)) {
            return redirect()->to('/medicos/lista')->with('error', 'Código de atención no válido.');
        }

        // Verificar si ya fue completado ANTES de procesar
        $db = \Config\Database::connect();
        $formularioExistente = $db->table('t_formulario_usuario')
            ->where('ate_codigo', $ate_codigo)
            ->where('seccion', 'ME')
            ->where('habilitado_por_admin !=', 1) // No es modificación habilitada
            ->get()
            ->getRowArray();

        if ($formularioExistente) {
            return $this->mostrarFormularioCompletado($ate_codigo, 'El formulario médico ya fue completado anteriormente.');
        }

        // Verificar permisos de modificación
        $permisoModificacion = $this->verificarPermisoModificacion($ate_codigo, $usu_id);

        if (!$permisoModificacion['permitido']) {
            return redirect()->to('/medicos/lista')->with('error', $permisoModificacion['mensaje']);
        }

        // Verificar que la atención existe
        $atencionModel = new AtencionModel();
        $atencion = $atencionModel->find($ate_codigo);
        if (!$atencion) {
            return redirect()->to('/medicos/lista')->with('error', 'La atención especificada no existe.');
        }

        $db->transStart();

        try {
            // Guardar TODAS las secciones (formulario completo)
            $this->guardarSeccionC($ate_codigo, $usu_id);
            $this->guardarSeccionD($ate_codigo, $usu_id);
            $this->guardarSeccionE($ate_codigo, $usu_id);
            $this->guardarSeccionF($ate_codigo, $usu_id);
            $this->guardarSeccionH($ate_codigo, $usu_id);
            $this->guardarSeccionI($ate_codigo, $usu_id);
            $this->guardarSeccionJ($ate_codigo, $usu_id);
            $this->guardarSeccionK($ate_codigo, $usu_id);
            $this->guardarSeccionL($ate_codigo, $usu_id);
            $this->guardarSeccionM($ate_codigo, $usu_id);
            $this->guardarSeccionN($ate_codigo, $usu_id);
            $this->guardarSeccionO($ate_codigo, $usu_id);
            $this->guardarSeccionP($ate_codigo, $usu_id);

            // MANEJO DIFERENCIADO PARA MODIFICACIONES VS PRIMERA VEZ
            $modificacionesModel = new ModificacionesModel();

            // Verificar si es modificación
            $formularioModificacion = $db->table('t_formulario_usuario')
                ->where('ate_codigo', $ate_codigo)
                ->where('seccion', 'ME')
                ->get()
                ->getRowArray();

            if ($formularioModificacion && $formularioModificacion['habilitado_por_admin'] == 1) {
                // Es una modificación - registrar que se usó
                $modificacionesModel->registrarModificacionUsada($ate_codigo, 'ME');

                $mensaje = '🔄 Formulario médico modificado exitosamente. La modificación ha sido registrada en el sistema.';
            } else {
                // Es la primera vez - registrar como completado
                $this->registrarFormularioCompletado($ate_codigo, $usu_id);
                $mensaje = 'Formulario médico completado exitosamente.';
            }

            $db->transComplete();

            // En lugar de redirect normal, mostrar página de completado
            return $this->mostrarFormularioCompletado($ate_codigo, $mensaje);

        } catch (Exception $e) {
            $db->transRollback();
            return redirect()->to('/medicos/lista')->with('error', 'Error crítico al guardar los datos: ' . $e->getMessage());
        }
    }
    private function mostrarFormularioCompletado($ate_codigo, $mensaje)
    {
        try {
            $db = \Config\Database::connect();

            // Obtener información del médico que completó
            $formularioInfo = $db->table('t_formulario_usuario fu')
                ->select('fu.fecha, u.usu_nombre, u.usu_apellido')
                ->join('t_usuario u', 'fu.usu_id = u.usu_id')
                ->where('fu.ate_codigo', $ate_codigo)
                ->where('fu.seccion', 'ME')
                ->orderBy('fu.fecha', 'DESC')
                ->get()
                ->getRowArray();

            // Obtener información del paciente
            $pacienteInfo = $db->table('t_atencion a')
                ->select('p.pac_nombres, p.pac_apellidos, p.pac_cedula')
                ->join('t_paciente p', 'a.pac_codigo = p.pac_codigo')
                ->where('a.ate_codigo', $ate_codigo)
                ->get()
                ->getRowArray();

            $data = [
                'title' => 'Formulario Médico Completado',
                'mensaje_completado' => $mensaje,
                'ate_codigo' => $ate_codigo,
                'fecha_completado' => $formularioInfo['fecha'] ?? date('Y-m-d H:i:s'),
                'medico_que_completo' => ($formularioInfo['usu_nombre'] ?? '') . ' ' . ($formularioInfo['usu_apellido'] ?? ''),
                'paciente_info' => $pacienteInfo,
                'tipo_formulario' => 'medico' // Para distinguir del de especialidades
            ];

            return view('medicos/formulario_completado_medico', $data);

        } catch (Exception $e) {
            return redirect()->to('/medicos/lista')->with('mensaje', $mensaje);
        }
    }
    // En MedicosController.php 
    public function obtenerPacientesTriaje()
    {
        if (!session()->get('logged_in')) {
            return $this->response->setJSON(['error' => 'No autorizado']);
        }

        if (session()->get('rol_id') != 4) {
            return $this->response->setJSON(['error' => 'Sin permisos']);
        }

        try {
            $listaMedicosModel = new ListaMedicosModel();
            $areaAtencionModel = new AreaAtencionModel();

            // Cambio: NO pasar usu_id como parámetro
            // Obtener pacientes con constantes vitales que NO han sido completados por NINGÚN médico
            // y que NO han sido asignados a especialidades
            $pacientesConConstantes = $listaMedicosModel->obtenerPacientesConConstantesVitales();

            $pacientesTriaje = [];

            foreach ($pacientesConConstantes as $paciente) {
                // Verificar que no haya sido asignado a especialidad (doble verificación)
                $yaAsignado = $areaAtencionModel->where('ate_codigo', $paciente['ate_codigo'])->first();

                // Verificar que no haya sido completado por ningún médico (doble verificación)
                $yaCompletado = $listaMedicosModel->pacienteCompletadoPorAlgunMedico($paciente['ate_codigo']);

                if (!$yaAsignado && !$yaCompletado) {
                    $pacientesTriaje[] = $paciente;
                }
            }


            return $this->response->setJSON($pacientesTriaje);

        } catch (Exception $e) {
            return $this->response->setJSON(['error' => 'Error interno del servidor']);
        }
    }

    /**
     * Tomar atención rápida desde el área de triaje
     */
    public function tomarAtencionRapida($ate_codigo = null)
    {
        if (!session()->get('logged_in')) {
            return redirect()->to('/login');
        }

        // DEBUG: Log específico para troubleshooting de Carla

        if (session()->get('rol_id') != 4) {
            return redirect()->to('/medicos/lista')->with('error', 'Sin permisos para tomar atenciones.');
        }

        if (!$ate_codigo) {
            return redirect()->to('/medicos/lista')->with('error', 'Código de atención requerido.');
        }

        try {

            // Verificar que la atención existe
            $atencionModel = new AtencionModel();
            $atencion = $atencionModel->find($ate_codigo);

            if (!$atencion) {
                return redirect()->to('/medicos/lista')->with('error', 'Atención no encontrada.');
            }

            // DEBUG: Log información de la atención encontrada
            $pacienteInfo = isset($atencion['pac_codigo']) ? $atencion['pac_codigo'] : 'N/A';
            $fechaInfo = isset($atencion['ate_fecha']) ? $atencion['ate_fecha'] : 'N/A';

            // Importante: Verificar permisos para modificación
            $usu_id = session()->get('usu_id');
            $permisoModificacion = $this->verificarPermisoModificacion($ate_codigo, $usu_id);

            if (!$permisoModificacion['permitido']) {
                return redirect()->to('/medicos/lista')->with('error', $permisoModificacion['mensaje']);
            }

            // Si es modificación habilitada, mostrar mensaje informativo
            if (isset($permisoModificacion['es_modificacion']) && $permisoModificacion['es_modificacion']) {
                session()->setFlashdata('info', '🔄 Está accediendo a una modificación habilitada. Puede editar el formulario previamente completado.');
            }

            // Obtener el paciente asociado para logs
            $pacienteId = $atencion['pac_codigo'];


            // USAR ATE_CODIGO NO PACIENTE_ID
            return redirect()->to("/medicos/formulario/$ate_codigo");

        } catch (Exception $e) {
            return redirect()->to('/medicos/lista')->with('error', 'Error al tomar la atención: ' . $e->getMessage());
        }
    }

    /**
     * Asignar paciente a especialidad - FUSIONADO DEL TRIAJE
     */
    public function asignarAEspecialidad()
    {
        if ($this->request->getMethod() !== 'POST') {
            return $this->response->setJSON([
                'success' => false,
                'error' => 'Método no permitido'
            ]);
        }

        $ate_codigo = $this->request->getPost('ate_codigo');
        $esp_codigo = $this->request->getPost('esp_codigo');
        $observaciones = $this->request->getPost('observaciones');


        if (empty($ate_codigo)) {
            return $this->response->setJSON([
                'success' => false,
                'error' => 'Código de atención requerido'
            ]);
        }

        if (empty($esp_codigo)) {
            return $this->response->setJSON([
                'success' => false,
                'error' => 'Especialidad requerida'
            ]);
        }

        $db = \Config\Database::connect();
        $db->transStart();

        try {
            // 1. Verificar que la atención existe
            $atencionExiste = $db->table('t_atencion')
                ->where('ate_codigo', $ate_codigo)
                ->get()
                ->getNumRows() > 0;

            if (!$atencionExiste) {
                throw new Exception("La atención {$ate_codigo} no existe en el sistema");
            }

            // 2. Verificar que no esté ya asignada
            $yaAsignada = $db->table('t_area_atencion')
                ->where('ate_codigo', $ate_codigo)
                ->get()
                ->getNumRows() > 0;

            if ($yaAsignada) {
                throw new Exception('Esta atención ya ha sido asignada a una especialidad');
            }

            // 3. Verificar que la especialidad existe
            $especialidadExiste = $db->table('t_especialidad')
                ->where('esp_codigo', $esp_codigo)
                ->where('esp_activo', 1)
                ->get()
                ->getNumRows() > 0;

            if (!$especialidadExiste) {
                throw new Exception('La especialidad seleccionada no existe o no está activa');
            }

            // 4. SOLO INSERTAR EN t_area_atencion (sin UPDATE a t_atencion)
            $areaAtencionData = [
                'ate_codigo' => $ate_codigo,
                'esp_codigo' => $esp_codigo,
                'are_estado' => 'PENDIENTE',
                'are_fecha_asignacion' => $this->getFechaHoy(),
                'are_hora_asignacion' => $this->getHoraActual(),
                'are_observaciones' => $observaciones,
                'are_medico_asignado' => null,
                'are_prioridad' => 1,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ];


            $builder = $db->table('t_area_atencion');
            $insertResult = $builder->insert($areaAtencionData);

            if (!$insertResult) {
                throw new Exception('Error al insertar en t_area_atencion');
            }

            $are_codigo = $db->insertID();

            if (!$are_codigo) {
                throw new Exception('Error al obtener ID del registro insertado');
            }

            // 5. SALTAR EL UPDATE a t_atencion por ahora

            $db->transComplete();

            if ($db->transStatus() === false) {
                throw new Exception('Error en la transacción');
            }

            $formularioUsuarioModel = new FormularioUsuarioModel();
            $evaluacionInicial = $formularioUsuarioModel
                ->where('ate_codigo', $ate_codigo)
                ->where('seccion', 'ME_INICIAL')
                ->first();

            $mensaje = 'Paciente asignado correctamente a la especialidad';
            if ($evaluacionInicial) {
                $mensaje .= '. La evaluación inicial ha sido guardada y estará disponible para el especialista.';
            }

            return $this->response->setJSON([
                'success' => true,
                'message' => $mensaje,
                'are_codigo' => $are_codigo,
                'ate_codigo' => $ate_codigo,
                'esp_codigo' => $esp_codigo,
                'evaluacion_inicial_guardada' => !empty($evaluacionInicial)
            ]);

        } catch (Exception $e) {
            $db->transRollback();

            return $this->response->setJSON([
                'success' => false,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Verificar si el médico puede modificar el formulario
     */
    private function verificarPermisoModificacion($ate_codigo, $usu_id)
    {
        $modificacionesModel = new ModificacionesModel();

        // Verificar acceso para médicos (sección ME)
        $resultado = $modificacionesModel->verificarAccesoMedico($ate_codigo, $usu_id, 'ME');


        // Importante: Si está habilitado para modificación, SIEMPRE permitir acceso
        if (isset($resultado['es_modificacion']) && $resultado['es_modificacion']) {
            return [
                'permitido' => true,
                'mensaje' => 'Modificación habilitada por administrador',
                'es_modificacion' => true,
                'redirigir' => false
            ];
        }

        // Si es solo lectura, permitir ver pero informar que no puede modificar
        if (isset($resultado['es_solo_lectura']) && $resultado['es_solo_lectura']) {
            return [
                'permitido' => true,
                'mensaje' => 'Formulario completado - modo solo lectura',
                'es_solo_lectura' => true,
                'puede_guardar' => false,
                'redirigir' => false
            ];
        }

        // Si no tiene acceso
        if (!$resultado['acceso']) {
            return [
                'permitido' => false,
                'mensaje' => $resultado['motivo'],
                'redirigir' => true
            ];
        }

        // Acceso normal (primera vez)
        return [
            'permitido' => true,
            'mensaje' => 'Acceso permitido',
            'es_primera_vez' => true,
            'redirigir' => false
        ];
    }
    /**
     * Método para verificar si un médico puede acceder al formulario
     */
    public function verificarAccesoFormulario($pacienteId = null)
    {
        if (!session()->get('logged_in')) {
            return $this->response->setJSON(['acceso' => false, 'motivo' => 'No autorizado']);
        }

        if (session()->get('rol_id') != 4) {
            return $this->response->setJSON(['acceso' => false, 'motivo' => 'Sin permisos']);
        }

        if (!$pacienteId) {
            return $this->response->setJSON(['acceso' => false, 'motivo' => 'ID de paciente requerido']);
        }

        try {
            // Obtener la atención más reciente del paciente
            $atencionModel = new AtencionModel();
            $atencion = $atencionModel->where('pac_codigo', $pacienteId)
                ->orderBy('ate_codigo', 'DESC')
                ->first();

            if (!$atencion) {
                return $this->response->setJSON(['acceso' => false, 'motivo' => 'No se encontró atención para este paciente']);
            }

            $ate_codigo = $atencion['ate_codigo'];
            $usu_id = session()->get('usu_id');

            // Verificar permisos de modificación
            $permisoModificacion = $this->verificarPermisoModificacion($ate_codigo, $usu_id);

            return $this->response->setJSON([
                'acceso' => $permisoModificacion['permitido'],
                'motivo' => $permisoModificacion['mensaje'],
                'ate_codigo' => $ate_codigo
            ]);

        } catch (\Exception $e) {
            return $this->response->setJSON(['acceso' => false, 'motivo' => 'Error interno del servidor']);
        }
    }
    /**
     * Obtener estadísticas de triaje - FUSIONADO DEL TRIAJE
     */
    public function obtenerEstadisticasTriaje()
    {
        if (!session()->get('logged_in')) {
            return $this->response->setJSON(['error' => 'No autorizado']);
        }

        if (session()->get('rol_id') != 4) {
            return $this->response->setJSON(['error' => 'Sin permisos']);
        }

        try {
            $db = \Config\Database::connect();

            // Pacientes en triaje (pendientes de decisión)
            $pacientesTriaje = $db->query("
                SELECT COUNT(*) as total
                FROM t_atencion a
                JOIN t_constantes_vitales cv ON a.ate_codigo = cv.ate_codigo
                LEFT JOIN t_area_atencion aa ON a.ate_codigo = aa.ate_codigo
                LEFT JOIN t_formulario_usuario fu ON a.ate_codigo = fu.ate_codigo AND fu.seccion = 'ME'
                WHERE aa.ate_codigo IS NULL AND fu.ate_codigo IS NULL
            ")->getRowArray()['total'];

            // Pacientes por color de triaje pendientes
            $porColor = $db->query("
                SELECT 
                    a.ate_colores as color,
                    COUNT(*) as cantidad
                FROM t_atencion a
                JOIN t_constantes_vitales cv ON a.ate_codigo = cv.ate_codigo
                LEFT JOIN t_area_atencion aa ON a.ate_codigo = aa.ate_codigo
                LEFT JOIN t_formulario_usuario fu ON a.ate_codigo = fu.ate_codigo AND fu.seccion = 'ME'
                WHERE aa.ate_codigo IS NULL AND fu.ate_codigo IS NULL
                GROUP BY a.ate_colores
            ")->getResultArray();

            // Pacientes completados por este médico hoy
            $usu_id = session()->get('usu_id');
            $completadosHoy = $db->query("
                SELECT COUNT(*) as total
                FROM t_formulario_usuario fu
                WHERE fu.usu_id = ? 
                AND fu.seccion = 'ME'
                AND DATE(fu.fecha) = CURDATE()
            ", [$usu_id])->getRowArray()['total'];

            return $this->response->setJSON([
                'success' => true,
                'estadisticas' => [
                    'pacientes_triaje' => $pacientesTriaje,
                    'por_color' => $porColor,
                    'completados_hoy' => $completadosHoy
                ]
            ]);

        } catch (Exception $e) {
            return $this->response->setJSON([
                'success' => false,
                'error' => $e->getMessage()
            ]);
        }
    }

    // ===============================================
    // MÉTODOS AUXILIARES
    // ===============================================

    /**
     * Cargar especialidades disponibles
     */
    public function obtenerEspecialidades()
    {
        if (!session()->get('logged_in') || session()->get('rol_id') != 4) {
            return $this->response->setJSON(['error' => 'No autorizado']);
        }

        try {
            $especialidadModel = new EspecialidadModel();
            $especialidades = $especialidadModel->where('esp_activo', 1)
                ->orderBy('esp_orden_prioridad', 'ASC')
                ->findAll();

            return $this->response->setJSON([
                'success' => true,
                'especialidades' => $especialidades
            ]);
        } catch (Exception $e) {
            return $this->response->setJSON([
                'success' => false,
                'error' => $e->getMessage()
            ]);
        }
    }
    private function registrarFormularioCompletado($ate_codigo, $usu_id)
    {

        $modificacionesModel = new ModificacionesModel();
        $db = \Config\Database::connect();

        // Verificar si ya existe registro
        $formularioExistente = $db->table('t_formulario_usuario')
            ->where('ate_codigo', $ate_codigo)
            ->where('seccion', 'ME')
            ->get()
            ->getRowArray();

        if ($formularioExistente) {
            // Es una modificación
            if ($formularioExistente['habilitado_por_admin'] == 1) {
                // Modificación habilitada por admin
                $resultado = $modificacionesModel->registrarModificacionUsada($ate_codigo, 'ME');
            } else {
                // Modificación directa del mismo médico
                $resultado = $modificacionesModel->usarModificacionDirecta($ate_codigo, 'ME', $usu_id);
            }
        } else {
            // Primera vez - crear registro y MANTENER en 0
            $resultado = $modificacionesModel->crearRegistroFormulario(
                $ate_codigo,
                $usu_id,
                'ME',
                "Completado en triaje médico - primera vez"
            );

            // CAMBIO: NO incrementar después del primer guardado
            // Mantener modificaciones_usadas = 0 para mostrar 0/3

        }

        if (!$resultado) {
            if ($formularioExistente) {
            }
            throw new \Exception("No se pudo registrar la finalización del formulario médico - revisar logs para más detalles");
        }

        return $resultado;
    }

    // ... resto de métodos sin cambios ...
    private function guardarSeccionC($ate_codigo, $usu_id)
    {
        $inicioAtencionModel = new InicioAtencionModel();
        $inicioAtencionModel->where('ate_codigo', $ate_codigo)->delete();

        $data = [
            'ate_codigo' => $ate_codigo,
            'iat_fecha' => $this->request->getPost('inicio_atencion_fecha') ?: $this->getFechaHoy(),
            'iat_hora' => $this->request->getPost('inicio_atencion_hora') ?: $this->getHoraActual(),
            'col_codigo' => $this->request->getPost('inicio_atencion_condicion') ?: 1,
            'iat_motivo' => $this->request->getPost('inicio_atencion_motivo') ?: '',
            'usu_id' => $usu_id
        ];

        $inicioAtencionModel->insert($data);
    }

    private function guardarSeccionD($ate_codigo, $usu_id)
    {

        // Actualizar tabla de atención
        $atencionModel = new AtencionModel();
        $dataAtencion = [
            'ate_custodia_policial' => $this->request->getPost('acc_custodia_policial') ? 'SI' : 'NO',
            'ate_observaciones' => $this->request->getPost('acc_observaciones') ?: '',
            'ate_aliento_etilico' => $this->request->getPost('acc_sugestivo_alcohol') ? 'SI' : 'NO'
        ];
        $atencionModel->update($ate_codigo, $dataAtencion);

        // Guardar eventos seleccionados
        $eventoModel = new EventoModel();
        $eventoModel->where('ate_codigo', $ate_codigo)->delete();
        $tiposEvento = $this->request->getPost('tipos_evento') ?: [];


        if (!empty($tiposEvento)) {
            // Capturar las observaciones del textarea


            foreach ($tiposEvento as $tipoEvento) {
                $eventoData = [
                    'ate_codigo' => $ate_codigo,
                    'tev_codigo' => intval($tipoEvento),
                    'eve_fecha' => $this->request->getPost('acc_fecha_evento') ?: $this->getFechaHoy(),
                    'eve_hora' => $this->request->getPost('acc_hora_evento') ?: $this->getHoraActual(),
                    'eve_lugar' => $this->request->getPost('acc_lugar_evento') ?: '',
                    'eve_direccion' => $this->request->getPost('acc_direccion_evento') ?: '',
                    'eve_observacion' => $this->request->getPost('acc_observaciones') ?: '',
                    'eve_notificacion' => $this->request->getPost('acc_notificacion_custodia') ?: 'no'
                ];
                $resultado = $eventoModel->insert($eventoData);
            }
        }
    }

    /**
     * Guardar solo las secciones C y D para envío a especialista
     * NO registrar en t_formulario_usuario (no está completado)
     */
    public function guardarSeccionesIniciales()
    {
        if (!session()->get('logged_in')) {
            return $this->response->setJSON(['success' => false, 'error' => 'No autorizado']);
        }

        if (session()->get('rol_id') != 4) {
            return $this->response->setJSON(['success' => false, 'error' => 'No tiene permisos']);
        }

        $ate_codigo = $this->request->getPost('ate_codigo');
        $usu_id = session()->get('usu_id');

        if (empty($ate_codigo)) {
            return $this->response->setJSON(['success' => false, 'error' => 'Código de atención requerido']);
        }

        // Verificar que la atención existe
        $atencionModel = new AtencionModel();
        $atencion = $atencionModel->find($ate_codigo);
        if (!$atencion) {
            return $this->response->setJSON(['success' => false, 'error' => 'Atención no encontrada']);
        }

        $db = \Config\Database::connect();
        $db->transStart();

        try {
            // Guardar SOLO secciones C y D (evaluación inicial)
            $this->guardarSeccionC($ate_codigo, $usu_id);
            $this->guardarSeccionD($ate_codigo, $usu_id);

            // Importante: NO guardar NADA en t_formulario_usuario
            // El médico NO completó el formulario, solo hizo evaluación inicial
            // Solo el especialista guardará en t_formulario_usuario cuando complete

            $db->transComplete();

            if ($db->transStatus() === false) {
                throw new Exception('Error en la transacción');
            }


            return $this->response->setJSON([
                'success' => true,
                'message' => 'Evaluación inicial guardada correctamente. Lista para envío a especialista.'
            ]);

        } catch (Exception $e) {
            $db->transRollback();

            return $this->response->setJSON([
                'success' => false,
                'error' => 'Error al guardar: ' . $e->getMessage()
            ]);
        }
    }
    private function guardarSeccionE($ate_codigo, $usu_id)
    {
        $antecedenteModel = new AntecedentePacienteModel();
        $antecedenteModel->where('ate_codigo', $ate_codigo)->delete();

        $noAplica = $this->request->getPost('ant_no_aplica') ? 1 : 0;
        $antecedentesSeleccionados = $this->request->getPost('antecedentes') ?: [];
        foreach ($antecedentesSeleccionados as $tipoAntecedente) {
            // Inserta cada antecedente seleccionado
        }
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
        $problemaActualModel = new ProblemaActualModel();
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
        $examenFisicoModel = new ExamenFisicoModel();
        $examenFisicoModel->where('ate_codigo', $ate_codigo)->delete();

        $zonasSeleccionadas = $this->request->getPost('zonas_examen_fisico') ?: [];
        foreach ($zonasSeleccionadas as $zona) {
            // Inserta cada zona seleccionada
        }
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
        $examenTraumaModel = new ExamenTraumaModel();
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

        $embarazoPartoModel = new EmbarazoPartoModel();
        $embarazoPartoModel->where('ate_codigo', $ate_codigo)->delete();

        // CAPTURAR CORRECTAMENTE EL CHECKBOX "NO APLICA"
        $noAplica = $this->request->getPost('emb_no_aplica') ? 1 : 0;


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

        $resultado = $embarazoPartoModel->insert($data);
    }

    private function guardarSeccionK($ate_codigo, $usu_id)
    {

        $examenesComplementariosModel = new ExamenesComplementariosModel();
        $examenesComplementariosModel->where('ate_codigo', $ate_codigo)->delete();

        // CAPTURAR CORRECTAMENTE LOS DATOS
        $noAplica = $this->request->getPost('exc_no_aplica') ? 1 : 0;
        $tiposExamenes = $this->request->getPost('tipos_examenes') ?: [];
        foreach ($tiposExamenes as $tipoExamen) {
            // Inserta cada examen seleccionado
        }
        $observaciones = $this->request->getPost('exc_observaciones') ?: '';


        if ($noAplica == 1) {
            // Si marca "No aplica", guardar solo este registro
            $resultado = $examenesComplementariosModel->insert([
                'ate_codigo' => $ate_codigo,
                'tipo_id' => null,
                'exa_no_aplica' => 1,
                'exa_observaciones' => null
            ]);
        } else {
            // Si NO marca "No aplica", guardar los exámenes seleccionados
            if (!empty($tiposExamenes)) {
                foreach ($tiposExamenes as $tipoExamen) {
                    $resultado = $examenesComplementariosModel->insert([
                        'ate_codigo' => $ate_codigo,
                        'tipo_id' => intval($tipoExamen), // IMPORTANTE: Usar el valor del checkbox
                        'exa_no_aplica' => 0,
                        'exa_observaciones' => $observaciones
                    ]);
                }
            } else if (!empty($observaciones)) {
                // Si hay observaciones pero no hay exámenes seleccionados
                $resultado = $examenesComplementariosModel->insert([
                    'ate_codigo' => $ate_codigo,
                    'tipo_id' => 16, // "Otros"
                    'exa_no_aplica' => 0,
                    'exa_observaciones' => $observaciones
                ]);
            }
        }
    }
    private function guardarSeccionL($ate_codigo, $usu_id)
    {

        $diagnosticoPresuntivoModel = new DiagnosticoPresuntivoModel();
        $diagnosticoPresuntivoModel->where('ate_codigo', $ate_codigo)->delete();

        $hayDiagnosticos = false;

        for ($i = 1; $i <= 3; $i++) {
            $descripcion = $this->request->getPost("diag_pres_desc$i");
            $cie = $this->request->getPost("diag_pres_cie$i");

            // Solo insertar si hay descripción
            if (!empty($descripcion)) {
                $resultado = $diagnosticoPresuntivoModel->insert([
                    'ate_codigo' => $ate_codigo,
                    'diagp_descripcion' => $descripcion,
                    'diagp_cie' => !empty($cie) ? $cie : null
                ]);

                if ($resultado) {
                    $hayDiagnosticos = true;
                }
            }
        }

        // Si no hay diagnósticos específicos, insertar registro por defecto
        if (!$hayDiagnosticos) {
            $diagnosticoPresuntivoModel->insert([
                'ate_codigo' => $ate_codigo,
                'diagp_descripcion' => null,
                'diagp_cie' => null
            ]);
        }

        return true;
    }

    // === SECCIÓN M: DIAGNÓSTICOS DEFINITIVOS MEJORADA ===
    private function guardarSeccionM($ate_codigo, $usu_id)
    {

        $diagnosticoDefinitivoModel = new DiagnosticoDefinitivoModel();
        $diagnosticoDefinitivoModel->where('ate_codigo', $ate_codigo)->delete();

        $hayDiagnosticos = false;

        for ($i = 1; $i <= 3; $i++) {
            $descripcion = $this->request->getPost("diag_def_desc$i");
            $cie = $this->request->getPost("diag_def_cie$i");

            // Solo insertar si hay descripción
            if (!empty($descripcion)) {
                $resultado = $diagnosticoDefinitivoModel->insert([
                    'ate_codigo' => $ate_codigo,
                    'diagd_descripcion' => $descripcion,
                    'diagd_cie' => !empty($cie) ? $cie : null
                ]);

                if ($resultado) {
                    $hayDiagnosticos = true;
                }
            }
        }

        // Si no hay diagnósticos específicos, insertar registro por defecto
        if (!$hayDiagnosticos) {
            $diagnosticoDefinitivoModel->insert([
                'ate_codigo' => $ate_codigo,
                'diagd_descripcion' => null,
                'diagd_cie' => null
            ]);
        }

        return true;
    }

    // === SECCIÓN N: TRATAMIENTO MEJORADA ===
    private function guardarSeccionN($ate_codigo, $usu_id)
    {

        $tratamientoModel = new TratamientoModel();

        // PRESERVAR estado de trat_administrado antes de borrar
        $tratamientosExistentes = $tratamientoModel->where('ate_codigo', $ate_codigo)->findAll();
        $estadosAdministrado = [];

        foreach ($tratamientosExistentes as $trat) {
            if (!empty($trat['trat_medicamento'])) {
                // Usar el medicamento como clave para preservar el estado
                $estadosAdministrado[$trat['trat_medicamento']] = $trat['trat_administrado'] ?? 0;
            }
        }


        $tratamientoModel->where('ate_codigo', $ate_codigo)->delete();

        // Capturar las observaciones generales del textarea
        $observacionesGenerales = $this->request->getPost('plan_tratamiento') ?: '';

        $hayTratamientos = false;

        // Procesar cada tratamiento (1 al 7)
        for ($i = 1; $i <= 7; $i++) {
            $medicamento = $this->request->getPost("trat_med$i");
            $via = $this->request->getPost("trat_via$i");
            $dosis = $this->request->getPost("trat_dosis$i");
            $posologia = $this->request->getPost("trat_posologia$i");
            $dias = $this->request->getPost("trat_dias$i");

            // CAPTURAR estado de administrado desde el formulario
            $administradoFromForm = $this->request->getPost("trat_administrado$i");

            // Solo insertar si hay al menos el medicamento
            if (!empty($medicamento)) {
                // USAR valor del formulario si existe, sino preservar el anterior, sino 0
                $administrado = 0; // Valor por defecto

                if (!empty($administradoFromForm)) {
                    // Si viene del formulario (checkbox marcado), usar ese valor
                    $administrado = 1;
                } elseif (isset($estadosAdministrado[$medicamento])) {
                    // Si no viene del formulario pero existía antes, preservar el estado anterior
                    $administrado = $estadosAdministrado[$medicamento];
                }

                $resultado = $tratamientoModel->insert([
                    'ate_codigo' => $ate_codigo,
                    'trat_medicamento' => $medicamento,
                    'trat_via' => !empty($via) ? $via : null,
                    'trat_dosis' => !empty($dosis) ? $dosis : null,
                    'trat_posologia' => !empty($posologia) ? $posologia : null,
                    'trat_dias' => !empty($dias) ? intval($dias) : null,
                    'trat_observaciones' => $observacionesGenerales,
                    'trat_administrado' => $administrado  // VALOR ACTUALIZADO
                ]);

                if ($resultado) {
                    $hayTratamientos = true;
                }
            }
        }

        // Si no hay tratamientos específicos PERO hay observaciones, insertar registro por defecto
        if (!$hayTratamientos && !empty($observacionesGenerales)) {
            $resultado = $tratamientoModel->insert([
                'ate_codigo' => $ate_codigo,
                'trat_medicamento' => null,
                'trat_via' => null,
                'trat_dosis' => null,
                'trat_posologia' => null,
                'trat_dias' => null,
                'trat_observaciones' => $observacionesGenerales
            ]);

            if ($resultado) {
            }
        }
        // Si no hay tratamientos NI observaciones, no insertar registro
        else if (!$hayTratamientos && empty($observacionesGenerales)) {
            $resultado = $tratamientoModel->insert([
                'ate_codigo' => $ate_codigo,
                'trat_medicamento' => null,
                'trat_via' => null,
                'trat_dosis' => null,
                'trat_posologia' => null,
                'trat_dias' => null,
                'trat_observaciones' => null
            ]);

            if ($resultado) {
            }
        }

        return true;
    }

    private function guardarSeccionO($ate_codigo, $usu_id)
    {

        $egresoEmergenciaModel = new EgresoEmergenciaModel();
        $egresoEmergenciaModel->where('ate_codigo', $ate_codigo)->delete();

        // CAPTURAR CORRECTAMENTE LOS ARRAYS DE CHECKBOXES - TODOS LOS SELECCIONADOS
        $estadosEgreso = $this->request->getPost('estados_egreso') ?: [];
        $modalidadesEgreso = $this->request->getPost('modalidades_egreso') ?: [];
        $tiposEgreso = $this->request->getPost('tipos_egreso') ?: [];

        // Datos comunes para todos los registros
        $datosComunes = [
            'ate_codigo' => $ate_codigo,
            'egr_establecimiento' => $this->request->getPost('egreso_establecimiento') ?: null,
            'egr_observaciones' => $this->request->getPost('egreso_observacion') ?: null,
            'egr_dias_reposo' => $this->request->getPost('egreso_dias_reposo') ?: 0,
            'egr_observacion_emergencia' => 0 // Campo requerido por la tabla
        ];

        $totalInserciones = 0;
        $registrosGuardados = [];

        // Guardar un registro por cada estado de egreso seleccionado
        foreach ($estadosEgreso as $estadoCodigo) {
            if (!empty($estadoCodigo)) {
                $data = array_merge($datosComunes, [
                    'ese_codigo' => intval($estadoCodigo),
                    'moe_codigo' => null,
                    'tie_codigo' => null
                ]);

                if ($egresoEmergenciaModel->insert($data)) {
                    $totalInserciones++;
                    $registrosGuardados[] = "Estado: $estadoCodigo";
                }
            }
        }

        // Guardar un registro por cada modalidad de egreso seleccionada
        foreach ($modalidadesEgreso as $modalidadCodigo) {
            if (!empty($modalidadCodigo)) {
                $data = array_merge($datosComunes, [
                    'ese_codigo' => null,
                    'moe_codigo' => intval($modalidadCodigo),
                    'tie_codigo' => null
                ]);

                if ($egresoEmergenciaModel->insert($data)) {
                    $totalInserciones++;
                    $registrosGuardados[] = "Modalidad: $modalidadCodigo";
                }
            }
        }

        // Guardar un registro por cada tipo de egreso seleccionado
        foreach ($tiposEgreso as $tipoCodigo) {
            if (!empty($tipoCodigo)) {
                $data = array_merge($datosComunes, [
                    'ese_codigo' => null,
                    'moe_codigo' => null,
                    'tie_codigo' => intval($tipoCodigo)
                ]);

                if ($egresoEmergenciaModel->insert($data)) {
                    $totalInserciones++;
                    $registrosGuardados[] = "Tipo: $tipoCodigo";
                }
            }
        }

        // Si no hay checkboxes seleccionados, insertar un registro vacío para mantener la referencia
        if ($totalInserciones === 0) {
            $dataVacia = array_merge($datosComunes, [
                'ese_codigo' => null,
                'moe_codigo' => null,
                'tie_codigo' => null
            ]);

            $totalInserciones = $egresoEmergenciaModel->insert($dataVacia) ? 1 : 0;
            $registrosGuardados[] = "Registro vacío";
        }


        return $totalInserciones > 0;
    }

    private function guardarSeccionP($ate_codigo, $usu_id)
    {
        $profesionalResponsableModel = new ProfesionalResponsableModel();

        // Obtener datos existentes antes de eliminar
        $datosExistentes = $profesionalResponsableModel->where('ate_codigo', $ate_codigo)->first();

        $profesionalResponsableModel->where('ate_codigo', $ate_codigo)->delete();

        // Manejar subida de archivos o mantener existentes
        $rutaFirma = $this->subirArchivoFirma('prof_firma', 'firmas');
        $rutaSello = $this->subirArchivoFirma('prof_sello', 'sellos');

        // Si no se subió nuevo archivo, mantener el existente
        if (!$rutaFirma && $datosExistentes && $datosExistentes['pro_firma']) {
            $rutaFirma = $datosExistentes['pro_firma'];
        }

        if (!$rutaSello && $datosExistentes && $datosExistentes['pro_sello']) {
            $rutaSello = $datosExistentes['pro_sello'];
        }

        $data = [
            'ate_codigo' => $ate_codigo,
            'pro_fecha' => $this->request->getPost('prof_fecha') ?: $this->getFechaHoy(),
            'pro_hora' => $this->request->getPost('prof_hora') ?: $this->getHoraActual(),
            'pro_primer_nombre' => $this->request->getPost('prof_primer_nombre') ?: '',
            'pro_primer_apellido' => $this->request->getPost('prof_primer_apellido') ?: '',
            'pro_segundo_apellido' => $this->request->getPost('prof_segundo_apellido') ?: '',
            'pro_nro_documento' => $this->request->getPost('prof_documento') ?: '',
            'pro_firma' => $rutaFirma,
            'pro_sello' => $rutaSello,
            'pro_firma_tipo' => $rutaFirma ? pathinfo($rutaFirma, PATHINFO_EXTENSION) : null,
            'pro_sello_tipo' => $rutaSello ? pathinfo($rutaSello, PATHINFO_EXTENSION) : null
        ];

        return $profesionalResponsableModel->insert($data);
    }

    // Método auxiliar para subir archivos
    private function subirArchivoFirma($nombreCampo, $carpeta)
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
            } else {
                return null;
            }
        }

        return null;
    }


    public function verificarGuardado($ate_codigo = null)
    {
        if (!session()->get('logged_in') || session()->get('rol_id') != 4) {
            return $this->response->setJSON(['error' => 'No autorizado']);
        }

        $usu_id = session()->get('usu_id');

        if (!$ate_codigo) {
            return $this->response->setJSON(['error' => 'ate_codigo requerido']);
        }

        try {
            $db = \Config\Database::connect();

            // 1. Verificar si existe el registro MEDICO_COMPLETO
            $formularioQuery = $db->table('t_formulario_usuario')
                ->where('ate_codigo', $ate_codigo)
                ->where('usu_id', $usu_id)
                ->where('seccion', 'ME')
                ->get();

            $registroExiste = $formularioQuery->getNumRows() > 0;
            $datosRegistro = $formularioQuery->getResultArray();

            // 2. Verificar la consulta del modelo manualmente
            $consultaManual = $db->query("
            SELECT t_atencion.ate_codigo, 
                   t_paciente.pac_nombres,
                   t_paciente.pac_apellidos
            FROM t_atencion 
            JOIN t_paciente ON t_atencion.pac_codigo = t_paciente.pac_codigo
            JOIN t_constantes_vitales ON t_atencion.ate_codigo = t_constantes_vitales.ate_codigo
            WHERE t_atencion.ate_codigo = ?
            AND t_atencion.ate_codigo NOT IN (
                SELECT ate_codigo 
                FROM t_formulario_usuario 
                WHERE usu_id = ? 
                AND seccion = 'ME'
            )
            GROUP BY t_atencion.ate_codigo
        ", [$ate_codigo, $usu_id]);

            $apareceEnConsulta = $consultaManual->getNumRows() > 0;

            // 3. Verificar usando el modelo
            $listaMedicosModel = new ListaMedicosModel();
            $pacientesDelModelo = $listaMedicosModel->obtenerPacientesConConstantesVitales($usu_id);

            $apareceEnModelo = false;
            foreach ($pacientesDelModelo as $p) {
                if ($p['ate_codigo'] == $ate_codigo) {
                    $apareceEnModelo = true;
                    break;
                }
            }

            return $this->response->setJSON([
                'success' => true,
                'ate_codigo' => $ate_codigo,
                'usu_id' => $usu_id,
                'registro_existe' => $registroExiste,
                'datos_registro' => $datosRegistro,
                'aparece_en_consulta_manual' => $apareceEnConsulta,
                'aparece_en_modelo' => $apareceEnModelo,
                'total_pacientes_modelo' => count($pacientesDelModelo),
                'fecha_verificacion' => date('Y-m-d H:i:s')
            ]);
        } catch (Exception $e) {
            return $this->response->setJSON([
                'success' => false,
                'error' => $e->getMessage()
            ]);
        }
    }

}
