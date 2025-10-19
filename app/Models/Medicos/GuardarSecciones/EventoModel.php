<?php

namespace App\Models\Medicos\GuardarSecciones;

use CodeIgniter\Model;

class EventoModel extends Model
{
    protected $table      = 't_evento';
    protected $primaryKey = 'eve_codigo';
    protected $returnType = 'array';

    protected $allowedFields = [
        'ate_codigo',
        'tev_codigo',
        'eve_fecha',
        'eve_hora',
        'eve_lugar',
        'eve_direccion',
        'eve_observacion',
        'eve_notificacion'
        
    ];
}
