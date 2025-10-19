<?php

namespace App\Controllers\Administrador;

use App\Controllers\BaseController;
use App\Models\Administrador\UsuarioModel;

/**
 * Controlador dedicado únicamente a operaciones CRUD de usuarios
 */
class DatosUsuarioController extends BaseController
{
    protected $usuarioModel;

    public function __construct()
    {
        $this->usuarioModel = new UsuarioModel();
    }

    /**
     * Mostrar formulario de creación de usuario
     */
    public function crearUsuario()
    {
        if (!session()->get('logged_in') || session()->get('rol_id') != 1) {
            return redirect()->to('/login');
        }

        // Cargar especialidades disponibles
        $data['especialidades'] = [];
        try {
            $db = \Config\Database::connect();
            $especialidades = $db->table('t_especialidad')
                ->where('esp_activo', 1)
                ->orderBy('esp_orden_prioridad', 'ASC')
                ->get()
                ->getResultArray();
            $data['especialidades'] = $especialidades;
        } catch (\Exception $e) {
        }

        return view('administrador/usuarios/usuarioCrear', $data);
    }

    /**
     * Ver usuarios eliminados temporalmente
     */
    public function usuariosEliminados()
    {
        if (!session()->get('logged_in') || session()->get('rol_id') != 1) {
            return redirect()->to('/login');
        }

        try {
            $data['usuarios'] = $this->usuarioModel->obtenerUsuariosInactivosConRol();

            return view('administrador/usuarios/usuariosEliminados', $data);

        } catch (\Exception $e) {
            session()->setFlashdata('error', 'Error al cargar los usuarios eliminados');
            return redirect()->to('administrador');
        }
    }
    /**
     * Mostrar formulario de edición de usuario
     */
    public function editarUsuario($id)
    {
        if (!session()->get('logged_in') || session()->get('rol_id') != 1) {
            return redirect()->to('/login');
        }

        $usuario = $this->usuarioModel->find($id);

        if (!$usuario || $usuario['usu_estado'] == 'inactivo') {
            session()->setFlashdata('error', 'Usuario no encontrado o inactivo');
            return redirect()->to('administrador');
        }

        $data['usuario'] = $usuario;

        // Cargar especialidades disponibles y del usuario
        try {
            $db = \Config\Database::connect();

            // Todas las especialidades disponibles
            $data['especialidades'] = $db->table('t_especialidad')
                ->where('esp_activo', 1)
                ->orderBy('esp_orden_prioridad', 'ASC')
                ->get()
                ->getResultArray();

            // Especialidades asignadas al usuario actual
            $data['especialidades_usuario'] = $db->table('t_usuario_especialidad')
                ->where('usu_id', $id)
                ->where('ue_activo', 1)
                ->get()
                ->getResultArray();

        } catch (\Exception $e) {
            $data['especialidades'] = [];
            $data['especialidades_usuario'] = [];
        }

        return view('administrador/usuarios/usuarioEditar', $data);
    }

    /**
     * Insertar nuevo usuario
     */
    public function insertarUsuario()
    {
        if (!session()->get('logged_in') || session()->get('rol_id') != 1) {
            return redirect()->to('/login');
        }

        // Validaciones básicas
        $rules = [
            'usu_nombre' => 'required|min_length[2]|max_length[50]',
            'usu_apellido' => 'required|min_length[2]|max_length[50]',
            'usu_usuario' => [
                'label' => 'Usario',
                'rules' => 'required|min_length[6]|max_length[20]|is_unique[t_usuario.usu_usuario]',
                'errors' => [
                    'required' => 'El {field} es obligatorio.',
                    'min_length' => 'El {field} debe tener al menos 6 caracteres.',
                    'is_unique' => 'El {field} ya está registrado.'
                ]
            ],
            'usu_password' => [
                'label' => 'Contraseña',
                'rules' => 'required|min_length[6]',
                'errors' => [
                    'required' => 'La {field} es obligatoria.',
                    'min_length' => 'La {field} debe tener al menos 6 caracteres.',
                ]
            ],
            'usu_nro_documento' => [
                'label' => 'Cédula',
                'rules' => 'required|min_length[8]|max_length[11]|is_unique[t_usuario.usu_nro_documento]',
                'errors' => [
                    'required' => 'La {field} es obligatoria.',
                    'min_length' => 'La {field} debe tener al menos 8 caracteres.',
                    'max_length' => 'La {field} no puede superar los 11 caracteres.',
                    'is_unique' => 'La {field} ya está registrada.'
                ]
            ],
            'rol_id' => 'required|integer|in_list[1,2,3,4,5]'
        ];

        // Validación adicional para médicos especialistas
        $especialidades = $this->request->getPost('especialidades');
        if ($this->request->getPost('rol_id') == '5') {
            if (empty($especialidades) || !is_array($especialidades)) {
                session()->setFlashdata('error', 'Debe seleccionar al menos una especialidad para el médico especialista');
                session()->setFlashdata('errors', ['especialidades' => 'Especialidades requeridas']);
                return redirect()->back()->withInput();
            }
        }

        if (!$this->validate($rules)) {
            session()->setFlashdata('error', 'Por favor corrige los errores en el formulario');
            session()->setFlashdata('errors', $this->validator->getErrors());
            return redirect()->back()->withInput();
        }

        $db = \Config\Database::connect();
        $db->transStart();

        try {
            // Crear usuario
            $userData = [
                'usu_nombre' => $this->request->getPost('usu_nombre'),
                'usu_apellido' => $this->request->getPost('usu_apellido'),
                'usu_usuario' => $this->request->getPost('usu_usuario'),
                'usu_password' => hash('sha256', $this->request->getPost('usu_password')),
                'usu_nro_documento' => $this->request->getPost('usu_nro_documento'),
                'rol_id' => $this->request->getPost('rol_id'),
                'usu_estado' => 'activo'
            ];

            if (!$this->usuarioModel->save($userData)) {
                throw new \Exception('Error al crear el usuario');
            }

            $usuarioId = $this->usuarioModel->getInsertID();

            // Si es médico especialista, asignar especialidades
            if ($this->request->getPost('rol_id') == '5' && !empty($especialidades)) {
                $this->asignarEspecialidades($usuarioId, $especialidades);
            }

            $db->transComplete();

            if ($db->transStatus() === false) {
                throw new \Exception('Error en la transacción');
            }

            session()->setFlashdata('success', 'Usuario creado exitosamente' .
                ($this->request->getPost('rol_id') == '5' ? ' con especialidades asignadas' : ''));
            return redirect()->to('administrador');

        } catch (\Exception $e) {
            $db->transRollback();
            session()->setFlashdata('error', 'Error del sistema: ' . $e->getMessage());
            return redirect()->back()->withInput();
        }
    }

    /**
     * Actualizar usuario existente
     */
    public function actualizarUsuario($id)
    {
        if (!session()->get('logged_in') || session()->get('rol_id') != 1) {
            return redirect()->to('/login');
        }

        $usuario = $this->usuarioModel->find($id);
        if (!$usuario || $usuario['usu_estado'] == 'inactivo') {
            session()->setFlashdata('error', 'Usuario no encontrado');
            return redirect()->to('administrador');
        }

        // Validación adicional para especialidades
        $especialidades = $this->request->getPost('especialidades');
        if ($this->request->getPost('rol_id') == '5') {
            if (empty($especialidades) || !is_array($especialidades)) {
                session()->setFlashdata('error', 'Debe seleccionar al menos una especialidad para el médico especialista');
                return redirect()->back()->withInput();
            }
        }
        $rules = [
            'usu_nombre' => 'required|min_length[2]|max_length[50]',
            'usu_apellido' => 'required|min_length[2]|max_length[50]',
            'usu_usuario' => [
                'label' => 'Usario',
                'rules' => "required|min_length[6]|max_length[20]|is_unique[t_usuario.usu_usuario,usu_id,{$id}]",
                'errors' => [
                    'required' => 'El {field} es obligatorio.',
                    'min_length' => 'El {field} debe tener al menos 6 caracteres.',
                    'is_unique' => 'El {field} ya está registrado.'
                ]
            ],
            'usu_nro_documento' => [
                'label' => 'Cédula',
                'rules' => "required|min_length[8]|max_length[11]|is_unique[t_usuario.usu_nro_documento,usu_id,{$id}]",
                'errors' => [
                    'required' => 'La {field} es obligatoria.',
                    'min_length' => 'La {field} debe tener al menos 8 caracteres.',
                    'max_length' => 'La {field} no puede superar los 11 caracteres.',
                    'is_unique' => 'La {field} ya está registrada.'
                ]
            ],
            'rol_id' => 'required|integer|in_list[1,2,3,4,5]'
        ];


        if (!$this->validate($rules)) {
            session()->setFlashdata('error', 'Por favor corrige los errores en el formulario');
            session()->setFlashdata('errors', $this->validator->getErrors());
            return redirect()->back()->withInput();
        }

        $db = \Config\Database::connect();
        $db->transStart();

        try {
            $data = [
                'usu_nombre' => $this->request->getPost('usu_nombre'),
                'usu_apellido' => $this->request->getPost('usu_apellido'),
                'usu_usuario' => $this->request->getPost('usu_usuario'),
                'usu_nro_documento' => $this->request->getPost('usu_nro_documento'),
                'rol_id' => $this->request->getPost('rol_id')
            ];

            // Solo actualizar contraseña si se proporciona
            $newPassword = $this->request->getPost('usu_password');
            if (!empty($newPassword)) {
                $data['usu_password'] = hash('sha256', $newPassword);
            }

            if (!$this->usuarioModel->update($id, $data)) {
                throw new \Exception('Error al actualizar el usuario');
            }

            // Actualizar especialidades si es médico especialista
            if ($this->request->getPost('rol_id') == '5') {
                $this->actualizarEspecialidades($id, $especialidades);
            } else {
                // Si ya no es especialista, desactivar todas sus especialidades
                $this->desactivarTodasEspecialidades($id);
            }

            $db->transComplete();

            if ($db->transStatus() === false) {
                throw new \Exception('Error en la transacción');
            }

            session()->setFlashdata('success', 'Usuario actualizado exitosamente');
            return redirect()->to('administrador');

        } catch (\Exception $e) {
            $db->transRollback();
            session()->setFlashdata('error', 'Error del sistema: ' . $e->getMessage());
            return redirect()->back()->withInput();
        }
    }

    /**
     * Desactivar usuario (soft delete)
     */
    public function desactivarUsuario($id)
    {
        if (!session()->get('logged_in') || session()->get('rol_id') != 1) {
            if ($this->request->isAJAX()) {
                return $this->response->setJSON(['success' => false, 'message' => 'No autorizado']);
            }
            return redirect()->to('/login');
        }

        $usuario = $this->usuarioModel->find($id);
        if (!$usuario) {
            if ($this->request->isAJAX()) {
                return $this->response->setJSON(['success' => false, 'message' => 'Usuario no encontrado']);
            }
            session()->setFlashdata('error', 'Usuario no encontrado');
            return redirect()->to('administrador');
        }

        // No permitir desactivar al propio administrador
        if ($id == session()->get('usu_id')) {
            if ($this->request->isAJAX()) {
                return $this->response->setJSON(['success' => false, 'message' => 'No puedes desactivar tu propia cuenta']);
            }
            session()->setFlashdata('error', 'No puedes desactivar tu propia cuenta');
            return redirect()->to('administrador');
        }

        try {
            if ($this->usuarioModel->desactivarUsuario($id)) {
                // Desactivar todas las especialidades del usuario
                $this->desactivarTodasEspecialidades($id);

                if ($this->request->isAJAX()) {
                    return $this->response->setJSON([
                        'success' => true,
                        'message' => 'Usuario desactivado exitosamente',
                        'usuario_desactivado' => [
                            'id' => $id,
                            'nombre' => $usuario['usu_nombre'] . ' ' . $usuario['usu_apellido']
                        ]
                    ]);
                }

                session()->setFlashdata('success', 'Usuario desactivado exitosamente');
            } else {
                if ($this->request->isAJAX()) {
                    return $this->response->setJSON(['success' => false, 'message' => 'Error al desactivar el usuario']);
                }
                session()->setFlashdata('error', 'Error al desactivar el usuario');
            }
        } catch (\Exception $e) {
            if ($this->request->isAJAX()) {
                return $this->response->setJSON(['success' => false, 'message' => 'Error del sistema']);
            }
            session()->setFlashdata('error', 'Error del sistema');
        }

        return redirect()->to('administrador');
    }

    /**
     * Reactivar usuario
     */
    public function reactivarUsuario($id)
    {
        // Asegurar que siempre devuelva JSON
        $this->response->setContentType('application/json');

        if (!session()->get('logged_in') || session()->get('rol_id') != 1) {
            return $this->response->setJSON(['success' => false, 'message' => 'No autorizado']);
        }

        $usuario = $this->usuarioModel->find($id);
        if (!$usuario || $usuario['usu_estado'] != 'inactivo') {
            return $this->response->setJSON(['success' => false, 'message' => 'Usuario no encontrado o ya está activo']);
        }

        try {
            if ($this->usuarioModel->reactivarUsuario($id)) {
                // Reactivar las especialidades del usuario (solo la primera como principal)
                $this->reactivarEspecialidadesUsuario($id);

                return $this->response->setJSON([
                    'success' => true,
                    'message' => 'Usuario reactivado exitosamente',
                    'usuario_reactivado' => [
                        'id' => $id,
                        'nombre' => $usuario['usu_nombre'] . ' ' . $usuario['usu_apellido']
                    ]
                ]);
            } else {
                return $this->response->setJSON(['success' => false, 'message' => 'Error al reactivar el usuario']);
            }
        } catch (\Exception $e) {
            return $this->response->setJSON(['success' => false, 'message' => 'Error del sistema: ' . $e->getMessage()]);
        }
    }

    // Método eliminarDefinitivamente() removido - Ya no se permite eliminar definitivamente usuarios

    // MÉTODOS PRIVADOS DE APOYO

    /**
     * Asignar especialidades a un usuario
     */
    private function asignarEspecialidades($usuarioId, $especialidades)
    {
        $db = \Config\Database::connect();

        foreach ($especialidades as $index => $espCodigo) {
            $data = [
                'usu_id' => $usuarioId,
                'esp_codigo' => intval($espCodigo),
                'ue_es_principal' => ($index === 0) ? 1 : 0, // La primera será principal
                'ue_activo' => 1,
                'ue_fecha_asignacion' => date('Y-m-d')
            ];

            $db->table('t_usuario_especialidad')->insert($data);
        }
    }

    /**
     * Actualizar especialidades de un usuario
     * Estrategia: Reutilizar registros existentes y actualizar el esp_codigo
     */
    private function actualizarEspecialidades($usuarioId, $especialidades)
    {
        $db = \Config\Database::connect();

        // Obtener todos los registros existentes del usuario (ordenados por ue_codigo)
        $registrosExistentes = $db->table('t_usuario_especialidad')
            ->where('usu_id', $usuarioId)
            ->orderBy('ue_codigo', 'ASC')
            ->get()
            ->getResultArray();

        // Si no hay especialidades seleccionadas, desactivar todos los registros existentes
        if (empty($especialidades)) {
            $db->table('t_usuario_especialidad')
                ->where('usu_id', $usuarioId)
                ->update(['ue_activo' => 0, 'ue_es_principal' => 0]);
            return;
        }

        // Caso 1: Ya existen registros - REUTILIZARLOS
        if (!empty($registrosExistentes)) {
            // Actualizar los registros existentes con las nuevas especialidades
            foreach ($especialidades as $index => $espCodigo) {
                if (isset($registrosExistentes[$index])) {
                    // Actualizar registro existente
                    $db->table('t_usuario_especialidad')
                        ->where('ue_codigo', $registrosExistentes[$index]['ue_codigo'])
                        ->update([
                            'esp_codigo' => intval($espCodigo),
                            'ue_activo' => 1,
                            'ue_es_principal' => ($index === 0) ? 1 : 0,
                            'ue_fecha_asignacion' => date('Y-m-d')
                        ]);
                } else {
                    // Si hay más especialidades que registros, crear nuevos
                    $db->table('t_usuario_especialidad')->insert([
                        'usu_id' => $usuarioId,
                        'esp_codigo' => intval($espCodigo),
                        'ue_es_principal' => ($index === 0) ? 1 : 0,
                        'ue_activo' => 1,
                        'ue_fecha_asignacion' => date('Y-m-d')
                    ]);
                }
            }

            // Si hay más registros existentes que especialidades, desactivar los sobrantes
            if (count($registrosExistentes) > count($especialidades)) {
                for ($i = count($especialidades); $i < count($registrosExistentes); $i++) {
                    $db->table('t_usuario_especialidad')
                        ->where('ue_codigo', $registrosExistentes[$i]['ue_codigo'])
                        ->update(['ue_activo' => 0, 'ue_es_principal' => 0]);
                }
            }
        }
        // Caso 2: No existen registros - CREAR NUEVOS
        else {
            foreach ($especialidades as $index => $espCodigo) {
                $db->table('t_usuario_especialidad')->insert([
                    'usu_id' => $usuarioId,
                    'esp_codigo' => intval($espCodigo),
                    'ue_es_principal' => ($index === 0) ? 1 : 0,
                    'ue_activo' => 1,
                    'ue_fecha_asignacion' => date('Y-m-d')
                ]);
            }
        }
    }

    /**
     * Desactivar todas las especialidades de un usuario
     */
    private function desactivarTodasEspecialidades($usuarioId)
    {
        $db = \Config\Database::connect();
        $db->table('t_usuario_especialidad')
            ->where('usu_id', $usuarioId)
            ->update([
                'ue_activo' => 0,
                'ue_es_principal' => 0
            ]);
    }

    /**
     * Reactivar todas las especialidades de un usuario
     */
    private function reactivarEspecialidadesUsuario($usuarioId)
    {
        $db = \Config\Database::connect();

        // Obtener todas las especialidades inactivas del usuario ordenadas por ue_codigo
        $especialidades = $db->table('t_usuario_especialidad')
            ->where('usu_id', $usuarioId)
            ->where('ue_activo', 0)
            ->orderBy('ue_codigo', 'ASC')
            ->get()
            ->getResultArray();

        if (!empty($especialidades)) {
            // Reactivar la primera especialidad como principal
            $db->table('t_usuario_especialidad')
                ->where('ue_codigo', $especialidades[0]['ue_codigo'])
                ->update([
                    'ue_activo' => 1,
                    'ue_es_principal' => 1
                ]);

            // Reactivar las demás especialidades como no principales
            for ($i = 1; $i < count($especialidades); $i++) {
                $db->table('t_usuario_especialidad')
                    ->where('ue_codigo', $especialidades[$i]['ue_codigo'])
                    ->update([
                        'ue_activo' => 1,
                        'ue_es_principal' => 0
                    ]);
            }
        }
    }
}