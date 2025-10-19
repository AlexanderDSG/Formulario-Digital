<?php

namespace App\Controllers\Administrador;

use App\Controllers\BaseController;
use App\Models\Administrador\ModificacionesModel;

class ModificacionesController extends BaseController
{
    protected $modificacionesModel;

    public function __construct()
    {
        $this->modificacionesModel = new ModificacionesModel();
    }

    /**
     * Obtener vista de modificaciones para cargar dinámicamente
     */
    public function obtenerVistaModificaciones()
    {
        // Verificar que sea administrador autenticado
        if (!session()->get('logged_in') || session()->get('rol_id') != 1) {
            return $this->response->setJSON([
                'success' => false,
                'error' => 'Acceso no autorizado'
            ]);
        }

        try {
            $data = [
                'usuario_modificaciones' => session()->get('usu_nombre') . ' ' . session()->get('usu_apellido'),
                'rol_modificaciones' => session()->get('rol_nombre')
            ];

            $vistaModificaciones = view('administrador/modificaciones/contenido_modificaciones', $data);

            return $this->response->setJSON([
                'success' => true,
                'html' => $vistaModificaciones
            ]);

        } catch (\Exception $e) {
            return $this->response->setJSON([
                'success' => false,
                'error' => 'Error interno del servidor'
            ]);
        }
    }

    /**
     * Habilitar modificación para un paciente específico
     */

    public function habilitarModificacion()
    {
        if (!session()->get('logged_in') || session()->get('rol_id') != 1) {
            return $this->response->setJSON(['success' => false, 'error' => 'No autorizado']);
        }

        if ($this->request->getMethod() !== 'POST') {
            return $this->response->setJSON(['success' => false, 'error' => 'Método no permitido']);
        }

        $ate_codigo = $this->request->getPost('ate_codigo');
        $seccion = $this->request->getPost('seccion');
        $motivo = $this->request->getPost('motivo');
        $admin_id = session()->get('usu_id');

        // Validaciones básicas
        if (empty($ate_codigo) || empty($seccion) || empty($motivo)) {
            return $this->response->setJSON([
                'success' => false,
                'error' => 'Todos los campos son obligatorios'
            ]);
        }

        // Validación de sección - Mantener ES como está en BD
        $seccionesValidas = ['ME', 'ES'];
        if (!in_array($seccion, $seccionesValidas)) {
            return $this->response->setJSON([
                'success' => false,
                'error' => "Sección no válida: {$seccion}. Válidas: " . implode(', ', $seccionesValidas)
            ]);
        }

        try {
            // Verificar límite de modificaciones ANTES de permitir habilitar
            $formulario = $this->modificacionesModel->obtenerFormulario($ate_codigo, $seccion);

            if ($formulario && $formulario['modificaciones_usadas'] >= $formulario['modificaciones_permitidas']) {
                return $this->response->setJSON([
                    'success' => false,
                    'error' => "No se puede habilitar más modificaciones. Límite alcanzado: {$formulario['modificaciones_usadas']}/{$formulario['modificaciones_permitidas']}"
                ]);
            }

            // Verificar que puede ser modificado
            $puedeModificar = $this->modificacionesModel->puedeSerModificado($ate_codigo, $seccion);

            // Solo rechazar si ya alcanzó el límite absoluto
            if (!$puedeModificar['puede_modificar'] && strpos($puedeModificar['motivo'], 'Límite') !== false) {
                return $this->response->setJSON([
                    'success' => false,
                    'error' => $puedeModificar['motivo']
                ]);
            }

            // Habilitar modificación
            $resultado = $this->modificacionesModel->habilitarModificacion($ate_codigo, $seccion, $admin_id, $motivo);

            if ($resultado) {
                return $this->response->setJSON([
                    'success' => true,
                    'message' => 'Modificación habilitada correctamente. El médico ahora puede editar el formulario una vez más. La atención ha sido reenviada a la lista correspondiente.',
                    'debug' => [
                        'ate_codigo' => $ate_codigo,
                        'seccion' => $seccion,
                        'admin_id' => $admin_id
                    ]
                ]);
            } else {
                return $this->response->setJSON([
                    'success' => false,
                    'error' => 'No se pudo habilitar la modificación'
                ]);
            }

        } catch (\Exception $e) {
            return $this->response->setJSON([
                'success' => false,
                'error' => 'Error interno: ' . $e->getMessage()
            ]);
        }
    }
    /**
     * Obtener historial de modificaciones de un paciente
     */
    public function obtenerHistorial($ate_codigo)
    {
        if (!session()->get('logged_in') || session()->get('rol_id') != 1) {
            return $this->response->setJSON(['success' => false, 'error' => 'No autorizado']);
        }

        try {
            $historial = $this->modificacionesModel->obtenerHistorialModificaciones($ate_codigo);

            return $this->response->setJSON([
                'success' => true,
                'historial' => $historial
            ]);

        } catch (\Exception $e) {
            return $this->response->setJSON([
                'success' => false,
                'error' => 'Error al obtener historial'
            ]);
        }
    }

    /**
     * Verificar estado de modificación de un formulario
     */
    public function verificarEstado()
    {
        if (!session()->get('logged_in') || session()->get('rol_id') != 1) {
            return $this->response->setJSON(['success' => false, 'error' => 'No autorizado']);
        }

        $ate_codigo = $this->request->getGet('ate_codigo');
        $seccion = $this->request->getGet('seccion');

        if (empty($ate_codigo) || empty($seccion)) {
            return $this->response->setJSON([
                'success' => false,
                'error' => 'Parámetros incompletos'
            ]);
        }

        try {
            $puedeModificar = $this->modificacionesModel->puedeSerModificado($ate_codigo, $seccion);

            return $this->response->setJSON([
                'success' => true,
                'puede_modificar' => $puedeModificar['puede_modificar'],
                'motivo' => $puedeModificar['motivo']
            ]);

        } catch (\Exception $e) {
            return $this->response->setJSON([
                'success' => false,
                'error' => 'Error al verificar estado'
            ]);
        }
    }

    /**
     * Obtener datos de modificaciones para DataTables
     */
    public function obtenerDatosModificaciones()
    {
        // Verificar que sea administrador autenticado
        if (!session()->get('logged_in') || session()->get('rol_id') != 1) {
            return $this->response->setJSON(['error' => 'No autorizado']);
        }

        $fechaInicio = $this->request->getGet('fecha_inicio');
        $fechaFin = $this->request->getGet('fecha_fin');
        $estado = $this->request->getGet('estado');

        try {
            $pacientes = $this->modificacionesModel->obtenerPacientesModificables($fechaInicio, $fechaFin, $estado);

            // Formatear datos para DataTables
            $datosFormateados = [];
            foreach ($pacientes as $paciente) {
                $datosFormateados[] = [
                    'ate_codigo' => $paciente['ate_codigo'] ?? '',
                    'fecha_atencion' => $paciente['ate_fecha'] ? date('Y-m-d', strtotime($paciente['ate_fecha'])) : '',
                    'hora_atencion' => $paciente['ate_hora'] ?? '',
                    'paciente' => trim(($paciente['pac_nombres'] ?? '') . ' ' . ($paciente['pac_apellidos'] ?? '')),
                    'cedula' => $paciente['pac_cedula'] ?? 'Sin cédula',
                    'estado_formulario' => $paciente['seccion'] ?? '',
                    'modificaciones_realizadas' => $paciente['modificaciones_count'] ?? 0,
                    'modificaciones_permitidas' => $paciente['modificaciones_permitidas'] ?? 3,
                    'puede_modificar' => $paciente['puede_modificar'] ?? false,
                    'motivo_bloqueo' => $paciente['motivo_bloqueo'] ?? '',
                    'ultimo_usuario' => $paciente['ultimo_usuario'] ?? '',
                    'fecha_ultima_modificacion' => $paciente['fecha_ultima_modificacion'] ?? '',
                    'estado_boton_admin' => $paciente['estado_boton_admin'] ?? 'PUEDE_HABILITAR'
                ];
            }

            return $this->response->setJSON([
                'draw' => intval($this->request->getGet('draw') ?? 1),
                'recordsTotal' => count($datosFormateados),
                'recordsFiltered' => count($datosFormateados),
                'data' => $datosFormateados,
                'success' => true
            ]);

        } catch (\Exception $e) {
            return $this->response->setJSON([
                'draw' => intval($this->request->getGet('draw') ?? 1),
                'recordsTotal' => 0,
                'recordsFiltered' => 0,
                'data' => [],
                'error' => 'Error interno del servidor',
                'errorDetails' => [
                    'message' => $e->getMessage(),
                    'file' => basename($e->getFile()),
                    'line' => $e->getLine(),
                    'timestamp' => date('Y-m-d H:i:s')
                ]
            ]);
        }
    }

    /**
     * Cargar vista interna para el panel de administrador - SIMPLIFICADO
     */
    public function cargarVistaInterna()
    {
        if (!session()->get('logged_in') || session()->get('rol_id') != 1) {
            return $this->response->setJSON(['success' => false, 'error' => 'No autorizado']);
        }

        try {
            // Solo retornar un contenedor básico - el HTML se genera en JavaScript
            return $this->response->setJSON([
                'success' => true,
                'message' => 'Vista lista para cargar'
            ]);

        } catch (\Exception $e) {
            return $this->response->setJSON([
                'success' => false,
                'error' => 'Error interno del servidor'
            ]);
        }
    }

    /**
     * Obtener estadísticas de modificaciones
     */
    public function obtenerEstadisticas()
    {
        if (!session()->get('logged_in') || session()->get('rol_id') != 1) {
            return $this->response->setJSON(['success' => false, 'error' => 'No autorizado']);
        }

        try {
            $estadisticas = $this->modificacionesModel->obtenerEstadisticasModificaciones();

            return $this->response->setJSON([
                'success' => true,
                'estadisticas' => $estadisticas
            ]);

        } catch (\Exception $e) {
            return $this->response->setJSON([
                'success' => false,
                'error' => 'Error al obtener estadísticas'
            ]);
        }
    }
}