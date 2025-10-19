<?php

namespace App\Models\Medicos\GuardarSecciones;

use CodeIgniter\Model;

class EmbarazoPartoModel extends Model
{
    protected $table      = 't_embarazo_parto';
    protected $primaryKey = 'emb_codigo';
    protected $returnType = 'array';

    protected $allowedFields = [
        'ate_codigo',
        'emb_no_aplica',
        'emb_numero_gestas',
        'emb_numero_partos',
        'emb_numero_abortos',
        'emb_numero_cesareas',
        'emb_fum',
        'emb_afu',
        'emb_semanas_gestacion',
        'emb_movimiento_fetal',
        'emb_frecuencia_cardiaca_fetal',
        'emb_ruptura_menbranas',
        'emb_tiempo',
        'emb_presentacion',
        'emb_sangrado_vaginal',
        'emb_contracciones',
        'emb_dilatacion',
        'emb_borramiento',
        'emb_plano',
        'emb_pelvis_viable',
        'emb_score_mama',
        'emb_observaciones'
    ];
}
