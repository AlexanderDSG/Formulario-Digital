<?php

namespace App\Models\Administrador;

use CodeIgniter\Model;

class UsuarioModel extends Model
{
    protected $table = 't_usuario';
    protected $primaryKey = 'usu_id';
    protected $allowedFields = [
        'usu_nombre', 
        'usu_apellido', 
        'usu_usuario', 
        'usu_password', 
        'usu_nro_documento',
        'rol_id',
        'usu_estado'
    ];

    protected $useTimestamps = false; // Manejamos manualmente con actualizado_en

    /**
     * Obtener solo usuarios activos
     */
    public function obtenerUsuariosActivos()
    {
        return $this->where('usu_estado', 'activo')->findAll();
    }

    /**
     * Obtener solo usuarios inactivos
     */
    public function obtenerUsuariosInactivos()
    {
        return $this->where('usu_estado', 'inactivo')->findAll();
    }

    /**
     * Desactivar usuario (soft delete)
     */
    public function desactivarUsuario($id)
{
    try {
        return $this->update($id, [
            'usu_estado' => 'inactivo',
            'actualizado_en' => date('Y-m-d H:i:s')
        ]);
    } catch (\Exception $e) {
        log_message('error', 'Error al desactivar usuario ID ' . $id . ': ' . $e->getMessage());
        return false;
    }
}

    /**
     * Reactivar usuario
     */
    public function reactivarUsuario($id)
{
    try {
        return $this->update($id, [
            'usu_estado' => 'activo',
            'actualizado_en' => date('Y-m-d H:i:s')
        ]);
    } catch (\Exception $e) {
        log_message('error', 'Error al reactivar usuario ID ' . $id . ': ' . $e->getMessage());
        return false;
    }
}

    /**
     * MÉTODO PRINCIPAL: Obtener usuarios activos con información del rol
     */
    public function obtenerUsuariosActivosConRol()
    {
        return $this->select('t_usuario.*, t_rol.rol_nombre')
            ->join('t_rol', 't_rol.rol_id = t_usuario.rol_id', 'left')
            ->where('t_usuario.usu_estado', 'activo')
            ->findAll();
    }

    /**
     * MÉTODO PRINCIPAL: Obtener usuarios inactivos con información del rol
     */
    public function obtenerUsuariosInactivosConRol()
    {
        return $this->select('t_usuario.*, t_rol.rol_nombre')
            ->join('t_rol', 't_rol.rol_id = t_usuario.rol_id', 'left')
            ->where('t_usuario.usu_estado', 'inactivo')
            ->findAll();
    }

    /**
     * Verificar si usuario está activo
     */
    public function verificarUsuarioActivo($nombreUsuario)
    {
        return $this->where('usu_usuario', $nombreUsuario)
            ->where('usu_estado', 'activo')
            ->countAllResults() > 0;
    }

    /**
     * MÉTODO PARA LOGIN: Obtener usuario con rol por nombre
     */
    public function getUsuarioConRolPorNombre($nombreUsuario)
    {
        return $this->select('t_usuario.*, t_rol.rol_nombre')
            ->join('t_rol', 't_rol.rol_id = t_usuario.rol_id', 'left')
            ->where('t_usuario.usu_usuario', $nombreUsuario)
            ->where('t_usuario.usu_estado', 'activo')
            ->first();
    }

    /**
     * FILTRAR usuarios por tipo de rol
     */
    public function obtenerUsuariosPorTipo($tipo)
    {
        $query = $this->select('t_usuario.*, t_rol.rol_nombre')
            ->join('t_rol', 't_rol.rol_id = t_usuario.rol_id', 'left')
            ->where('t_usuario.usu_estado', 'activo');

        switch ($tipo) {
            case 'administradores':
                $query->where('t_usuario.rol_id', 1);
                break;
            case 'admisionistas':
                $query->where('t_usuario.rol_id', 2);
                break;
            case 'enfermeras':
                $query->where('t_usuario.rol_id', 3);
                break;
            case 'medicos':
                $query->where('t_usuario.rol_id', 4);
                break;
            case 'especialistas':
                $query->where('t_usuario.rol_id', 5);
                break;
            default:
                // Si es 'todos' o cualquier otro valor, no filtrar por rol
                break;
        }

        return $query->findAll();
    }

    /**
     * CONTAR usuarios por tipo para estadísticas
     */
    public function contarUsuariosPorTipo($tipo)
    {
        $query = $this->where('usu_estado', 'activo');

        switch ($tipo) {
            case 'administradores':
                $query->where('rol_id', 1);
                break;
            case 'admisionistas':
                $query->where('rol_id', 2);
                break;
            case 'enfermeras':
                $query->where('rol_id', 3);
                break;
            case 'medicos':
                $query->where('rol_id', 4);
                break;
            case 'especialistas':
                $query->where('rol_id', 5);
                break;
            case 'todos':
            default:
                // No filtrar por rol, contar todos
                break;
        }

        return $query->countAllResults();
    }
}