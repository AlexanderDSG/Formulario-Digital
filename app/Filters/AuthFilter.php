<?php

namespace App\Filters;

use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\Filters\FilterInterface;

class AuthFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        // Obtener info para debug
        $uri = $request->getUri();
        $currentPath = $uri->getPath();

        // DEBUG: Log información de sesión
        log_message('info', "AuthFilter - Ruta: {$currentPath}");
        log_message('info', "AuthFilter - Logged in: " . (session()->get('logged_in') ? 'YES' : 'NO'));
        log_message('info', "AuthFilter - User ID: " . (session()->get('usu_id') ?? 'NULL'));
        log_message('info', "AuthFilter - Rol ID: " . (session()->get('rol_id') ?? 'NULL'));
        log_message('info', "AuthFilter - Is AJAX: " . ($request->isAJAX() ? 'YES' : 'NO'));

        // Verificar si hay una sesión activa
        if (!session()->get('logged_in')) {
            log_message('warning', "AuthFilter - No hay sesión activa para ruta: {$currentPath}");
            if ($request->isAJAX()) {
                return service('response')->setJSON(['success' => false, 'error' => 'Sesión no válida'])->setStatusCode(401);
            }
            return redirect()->to('/login');
        }

        // Validar acceso según la ruta y el usuario logueado
        $currentUserId = session()->get('usu_id');
        $currentRolId = session()->get('rol_id');
        
        // Verificar si el usuario actual tiene permisos para esta ruta
        // CORREGIDO: Usar strpos para buscar en cualquier parte de la ruta, no solo al inicio
        if (strpos($currentPath, '/administrador') !== false && $currentRolId != 1) {
            // Para peticiones AJAX, devolver JSON en lugar de redireccionar
            if ($request->isAJAX()) {
                return service('response')->setJSON(['success' => false, 'error' => 'No autorizado'])->setStatusCode(403);
            }
            return redirect()->to('/acceso-denegado');
        }

        if (strpos($currentPath, '/admisiones') !== false && $currentRolId != 2) {
            if ($request->isAJAX()) {
                return service('response')->setJSON(['success' => false, 'error' => 'No autorizado'])->setStatusCode(403);
            }
            return redirect()->to('/acceso-denegado');
        }

        if (strpos($currentPath, '/enfermeria') !== false && $currentRolId != 3) {
            if ($request->isAJAX()) {
                return service('response')->setJSON(['success' => false, 'error' => 'No autorizado'])->setStatusCode(403);
            }
            return redirect()->to('/acceso-denegado');
        }

        if (strpos($currentPath, '/medicos') !== false && $currentRolId != 4) {
            if ($request->isAJAX()) {
                return service('response')->setJSON(['success' => false, 'error' => 'No autorizado'])->setStatusCode(403);
            }
            return redirect()->to('/acceso-denegado');
        }

        if (strpos($currentPath, '/especialidades') !== false && $currentRolId != 5) {
            if ($request->isAJAX()) {
                return service('response')->setJSON(['success' => false, 'error' => 'No autorizado'])->setStatusCode(403);
            }
            return redirect()->to('/acceso-denegado');
        }
        // Validación adicional con argumentos si se proporcionan
        if ($arguments) {
            $rolPermitido = $arguments[0];
            if ($currentRolId != $rolPermitido) {
                return redirect()->to('/acceso-denegado');
            }
        }
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null) {}
}