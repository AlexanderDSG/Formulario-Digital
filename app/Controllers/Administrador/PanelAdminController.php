<?php

namespace App\Controllers\Administrador;

use App\Controllers\BaseController;
use App\Models\Administrador\UsuarioModel;

class PanelAdminController extends BaseController
{
    protected $usuarioModel;

    public function __construct()
    {
        $this->usuarioModel = new UsuarioModel();
    }

    /**
     * Página principal del panel de administrador
     */
    public function index()
    {
        if (!session()->get('logged_in') || session()->get('rol_id') != 1) {
            return redirect()->to('/login');
        }

        return view('administrador/PanelAdmin');
    }
    /**
     * Mostrar pacientes con formularios completados
     */
    public function showPatients()
    {
        if (!session()->get('logged_in') || session()->get('rol_id') != 1) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'No autorizado'
            ]);
        }

        try {
            $modificacionesModel = new \App\Models\Administrador\ModificacionesModel();
            $pacientes = $modificacionesModel->obtenerPacientesModificables();

            // Formatear los datos para la vista
            $pacientesFormateados = [];
            foreach ($pacientes as $paciente) {
                $pacientesFormateados[] = [
                    'pac_his_cli' => $paciente['pac_his_cli'] ?? 'N/A',
                    'pac_cedula' => $paciente['pac_cedula'] ?? 'N/A',
                    'pac_apellidos' => $paciente['pac_apellidos'] ?? '',
                    'pac_nombres' => $paciente['pac_nombres'] ?? '',
                    'ate_fecha' => $paciente['ate_fecha'] ?? '',
                    'ate_codigo' => $paciente['ate_codigo'] ?? '',
                    'tipo_profesional' => $paciente['tipo_profesional'] ?? 'N/A',
                    'habilitado_por_admin' => $paciente['habilitado_por_admin'] ?? 0,
                    'puede_modificar' => $paciente['habilitado_por_admin'] == 1 ? 'Sí' : 'No'
                ];
            }

            return $this->response->setJSON([
                'success' => true,
                'pacientes' => $pacientesFormateados,
                'total' => count($pacientesFormateados)
            ]);

        } catch (\Exception $e) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Error al cargar pacientes'
            ]);
        }
    }
    /**
     * Filtrar usuarios por tipo de rol (AJAX)
     */
    public function filtrarUsuarios()
    {
        if (!session()->get('logged_in') || session()->get('rol_id') != 1) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'No autorizado'
            ]);
        }

        $tipoUsuario = $this->request->getPost('tipo');

        if (empty($tipoUsuario)) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Tipo de usuario requerido'
            ]);
        }

        try {
            // Filtrar usuarios según el tipo
            $usuarios = $this->usuarioModel->obtenerUsuariosPorTipo($tipoUsuario);

            // Obtener el nombre del rol para el título
            $nombreTipo = $this->obtenerNombreTipo($tipoUsuario);

            return $this->response->setJSON([
                'success' => true,
                'usuarios' => $usuarios,
                'tipo' => $tipoUsuario,
                'nombreTipo' => $nombreTipo,
                'totalUsuarios' => count($usuarios)
            ]);

        } catch (\Exception $e) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Error al cargar usuarios'
            ]);
        }
    }

    /**
     * Obtener estadísticas para el dashboard
     */
    public function obtenerEstadisticas()
    {
        if (!session()->get('logged_in') || session()->get('rol_id') != 1) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'No autorizado'
            ]);
        }

        try {
            $estadisticas = [
                'total_usuarios' => $this->usuarioModel->contarUsuariosPorTipo('todos'),
                'administradores' => $this->usuarioModel->contarUsuariosPorTipo('administradores'),
                'admisionistas' => $this->usuarioModel->contarUsuariosPorTipo('admisionistas'),
                'enfermeras' => $this->usuarioModel->contarUsuariosPorTipo('enfermeras'),
                'medicos' => $this->usuarioModel->contarUsuariosPorTipo('medicos'),
                'especialistas' => $this->usuarioModel->contarUsuariosPorTipo('especialistas')
            ];

            return $this->response->setJSON([
                'success' => true,
                'estadisticas' => $estadisticas
            ]);

        } catch (\Exception $e) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Error al cargar estadísticas'
            ]);
        }
    }


    /**
     * Obtener nombre legible del tipo de usuario
     */
    private function obtenerNombreTipo($tipo)
    {
        $nombres = [
            'administradores' => 'Administradores',
            'admisionistas' => 'Admisionistas',
            'enfermeras' => 'Enfermeras',
            'medicos' => 'Médicos',
            'especialistas' => 'Médicos Especialistas',
            'todos' => 'Todos los Usuarios'
        ];

        return $nombres[$tipo] ?? 'Usuarios';
    }
}