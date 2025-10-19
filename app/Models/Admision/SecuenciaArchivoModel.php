<?php

namespace App\Models\Admision;

use CodeIgniter\Model;

class SecuenciaArchivoModel extends Model
{
    protected $table = 't_establecimiento_registro';
    protected $primaryKey = 'est_reg_codigo';
    protected $allowedFields = [
        'estab_codigo',
        'ate_codigo',
        'est_num_archivo',
        'usu_id',
        'creado_en'
    ];  // Campos que pueden ser modificados
    protected $returnType = 'array';

    // Función para insertar un nuevo registro en la tabla t_establecimiento_registro
    public function insertarRegistro($data)
    {
        return $this->insert($data);
    }
}
