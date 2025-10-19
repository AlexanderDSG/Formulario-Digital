<?php

namespace App\Models\Admision\GuardarSecciones;

use CodeIgniter\Model;

class EstablecimientoModel extends Model
{
    protected $table = 't_establecimiento'; // Ajusta según tu tabla
    protected $primaryKey = 'estab_codigo';
    protected $allowedFields = ['est_institucion', 'est_unicodigo', 'est_nombre_establecimiento'];

    public function obtenerEstablecimientoActual($establecimientoId = null)
    {
        if (!$establecimientoId) {
            // Podrías obtener de sesión o usar un establecimiento por defecto
            $establecimientoId = session()->get('establecimiento_id') ?? 1;
        }
        
        return $this->find($establecimientoId);
    }
}