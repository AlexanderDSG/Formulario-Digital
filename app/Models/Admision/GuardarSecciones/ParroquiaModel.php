<?php

namespace App\Models\Admision\GuardarSecciones;

use CodeIgniter\Model;

class ParroquiaModel extends Model
{
    protected $table = 't_parroquia';
    protected $primaryKey = 'parr_codigo';
    protected $allowedFields = ['parr_codigo', 'parr_nombre', 'cant_codigo'];
    protected $useAutoIncrement = false;

    /**
     * Obtener todas las parroquias rurales
     */
    public function obtenerTodasParroquias()
    {
        return $this->orderBy('parr_nombre', 'ASC')->findAll();
    }

    /**
     * Obtener parroquias combinadas (rurales + urbanas)
     */
    public function obtenerParroquiasCombinadas($cantCodigo)
    {
        $sql = "
            SELECT 
                p.parr_codigo AS codigo,
                p.parr_nombre AS nombre
            FROM t_parroquia p
            WHERE p.cant_codigo = ?

            UNION ALL

            SELECT 
                pu.parr_urb_codigo AS codigo,
                CONCAT(pu.parr_urb_nombre, ' (Urbana)') AS nombre
            FROM t_parroquia_urbana pu
            INNER JOIN t_parroquia p ON p.parr_codigo = pu.parr_codigo
            WHERE p.cant_codigo = ?

            ORDER BY nombre ASC
        ";

        $query = $this->db->query($sql, [$cantCodigo, $cantCodigo]);
        return $query->getResultArray();
    }
}