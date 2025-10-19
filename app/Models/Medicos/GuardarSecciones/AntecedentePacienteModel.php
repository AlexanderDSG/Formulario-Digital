<?php

namespace App\Models\Medicos\GuardarSecciones;

use CodeIgniter\Model;

class AntecedentePacienteModel extends Model
{
    protected $table      = 't_antecedente_paciente';
    protected $primaryKey = 'ap_codigo';
    protected $returnType = 'array';

    protected $allowedFields = [
        'ate_codigo',
        'tan_codigo',
        'ap_descripcion',
        'ap_no_aplica'
    ];
}
