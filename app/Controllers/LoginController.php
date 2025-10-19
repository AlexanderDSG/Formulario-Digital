<?php

namespace App\Controllers;

use App\Models\Administrador\UsuarioModel;

class LoginController extends BaseController
{
    public function index()
    {
        return view('auth/login');
    }

    public function ingresar()
    {
        $usuario = $this->request->getPost('usuario');
        $password = $this->request->getPost('password');

        // Validaciones básicas
        if (empty($usuario) || empty($password)) {
            return redirect()->back()->with('error', 'Usuario y contraseña son requeridos');
        }

        $usuarioModel = new UsuarioModel();

        // VERIFICAR PRIMERO SI EL USUARIO ESTÁ ACTIVO
        if (!$usuarioModel->verificarUsuarioActivo($usuario)) {
            return redirect()->back()->with('error', 'Usuario no encontrado o inactivo. Contacta al administrador.');
        }

        // OBTENER DATOS DEL USUARIO CON ROL
        $usuarioData = $usuarioModel->getUsuarioConRolPorNombre($usuario);

        // Verificar credenciales
        if ($usuarioData && hash_equals($usuarioData['usu_password'], hash('sha256', $password))) {

            // Generar un token único para esta sesión específica
            $sessionToken = bin2hex(random_bytes(32));

            // Crear namespace único para el usuario
            $userNamespace = 'user_' . $usuarioData['usu_id'] . '_' . $sessionToken;

            // GUARDAR INFORMACIÓN COMPLETA EN SESIÓN
            session()->set([
                'session_token' => $sessionToken,
                'user_namespace' => $userNamespace,
                'usu_id' => $usuarioData['usu_id'],
                'usu_nombre' => $usuarioData['usu_nombre'],
                'usu_apellido' => $usuarioData['usu_apellido'],
                'usu_usuario' => $usuarioData['usu_usuario'],
                'usu_nro_documento' => $usuarioData['usu_nro_documento'] ?? '',
                'rol_id' => $usuarioData['rol_id'],
                'rol_nombre' => $usuarioData['rol_nombre'],
                'usu_estado' => $usuarioData['usu_estado'],
                'logged_in' => true,
                'login_time' => time()
            ]);

            // Almacenar información adicional en el namespace específico
            session()->set($userNamespace . '_active', true);

            // Log del login exitoso
            log_message('info', "Login exitoso - Usuario: {$usuario}, ID: {$usuarioData['usu_id']}, Rol: {$usuarioData['rol_nombre']}");

            // 🚦 Redirección según rol (con parámetro login para session-timeout)
            switch ($usuarioData['rol_id']) {
                case 1: // ADMISIONISTA
                    return redirect()->to('/administrador?login=1');
                case 2: // ENFERMERÍA
                    return redirect()->to('/admisiones?login=1');
                case 3: // MÉDICO
                    return redirect()->to('/enfermeria?login=1');
                case 4: // ADMINISTRADOR
                    return redirect()->to('/medicos?login=1');
                case 5: // ADMINISTRADOR
                    return redirect()->to('/especialidades?login=1');

                default:
                    log_message('warning', "Rol no autorizado - Usuario: {$usuario}, Rol ID: {$usuarioData['rol_id']}");
                    return redirect()->to('/login')->with('error', 'Rol no autorizado');
            }
        } else {
            // Log del intento fallido
            log_message('warning', "Intento de login fallido - Usuario: {$usuario}");
            return redirect()->back()->with('error', 'Usuario o contraseña incorrectos');
        }
    }

    public function logout()
    {
        // Obtener información del usuario antes del logout
        $usuarioId = session()->get('usu_id');
        $usuarioNombre = session()->get('usu_usuario');

        // Log del logout
        if ($usuarioId) {
            log_message('info', "Logout - Usuario: {$usuarioNombre}, ID: {$usuarioId}");
        }

        // Obtener el namespace del usuario actual antes de destruir la sesión
        $userNamespace = session()->get('user_namespace');

        // Solo remover las variables de sesión específicas del usuario actual
        if ($userNamespace) {
            session()->remove($userNamespace . '_active');
        }

        // Remover solo las variables de sesión del usuario actual
        session()->remove([
            'session_token',
            'user_namespace',
            'usu_id',
            'usu_nombre',
            'usu_apellido',
            'usu_usuario',
            'usu_nro_documento',
            'rol_id',
            'rol_nombre',
            'usu_estado',
            'logged_in',
            'login_time'
        ]);

        // No destruir toda la sesión, solo limpiar las variables del usuario actual
        // session()->destroy(); // Esta línea causa el problema

        return redirect()->to('/login')->with('success', 'Sesión cerrada correctamente');
    }

    /**
     * MÉTODO MEJORADO: Validar sesión
     */
    public function validateSession()
    {
        $userNamespace = session()->get('user_namespace');
        $usuarioId = session()->get('usu_id');

        if (!$userNamespace || !session()->get($userNamespace . '_active') || !$usuarioId) {
            return $this->response->setJSON(['valid' => false, 'message' => 'Sesión inválida']);
        }

        // VERIFICAR QUE EL USUARIO SIGA ACTIVO EN LA BASE DE DATOS
        $usuarioModel = new UsuarioModel();
        $usuario = $usuarioModel->find($usuarioId);

        if (!$usuario || $usuario['usu_estado'] !== 'activo') {
            // Usuario fue desactivado, cerrar sesión
            $this->logout();
            return $this->response->setJSON(['valid' => false, 'message' => 'Usuario desactivado']);
        }

        return $this->response->setJSON([
            'valid' => true,
            'user' => [
                'id' => $usuarioId,
                'nombre' => session()->get('usu_nombre'),
                'rol' => session()->get('rol_nombre')
            ]
        ]);
    }

    /**
     * Verificar permisos por rol
     */
    public function verificarPermisos($rolRequerido)
    {
        if (!session()->get('logged_in')) {
            return false;
        }

        $rolActual = session()->get('rol_id');

        // Si es administrador, tiene acceso a todo
        if ($rolActual == 4) {
            return true;
        }

        // Verificar rol específico
        return $rolActual == $rolRequerido;
    }

    /**
     * Información del usuario logueado
     */
    public function getUsuarioLogueado()
    {
        if (!session()->get('logged_in')) {
            return $this->response->setJSON(['error' => 'No hay usuario logueado']);
        }

        return $this->response->setJSON([
            'usuario' => [
                'id' => session()->get('usu_id'),
                'nombre' => session()->get('usu_nombre'),
                'apellido' => session()->get('usu_apellido'),
                'usuario' => session()->get('usu_usuario'),
                'documento' => session()->get('usu_nro_documento'),
                'rol_id' => session()->get('rol_id'),
                'rol_nombre' => session()->get('rol_nombre'),
                'login_time' => session()->get('login_time')
            ]
        ]);
    }

    /**
     * Endpoint para mantener la sesión activa (ping desde JavaScript)
     */
    public function pingSession()
    {
        // Verificar que es una petición AJAX
        if (!$this->request->isAJAX()) {
            return $this->response->setStatusCode(400)->setJSON(['error' => 'Solo peticiones AJAX']);
        }

        // Verificar que la sesión esté activa
        if (!session()->get('logged_in')) {
            return $this->response->setJSON(['success' => false, 'message' => 'Sesión no válida']);
        }

        // Actualizar la última actividad (opcional)
        session()->set('last_activity', time());

        // Log de la actividad
        log_message('info', 'Ping de sesión recibido para usuario: ' . (session()->get('usu_id') ?? 'unknown'));

        return $this->response->setJSON([
            'success' => true,
            'message' => 'Sesión extendida',
            'timestamp' => time(),
            'user_id' => session()->get('usu_id')
        ]);
    }
}
