<?php

namespace App\Models\Medicos\GuardarSecciones;

use CodeIgniter\Model;

class ProfesionalResponsableModel extends Model
{
    protected $table      = 't_profesional_responsable';
    protected $primaryKey = 'pro_id';
    protected $returnType = 'array';

    protected $allowedFields = [
    'ate_codigo',
    'pro_fecha',
    'pro_hora',
    'pro_primer_nombre',
    'pro_primer_apellido',
    'pro_segundo_apellido',
    'pro_nro_documento',
    'pro_firma',           // Ahora almacena la ruta del archivo
    'pro_sello',           // Ahora almacena la ruta del archivo
    'pro_firma_tipo',      // ← AGREGAR
    'pro_sello_tipo',      // ← AGREGAR
    'pro_fecha_subida'     // ← AGREGAR (se llena automáticamente)
];
}
