<?php

namespace App\Models;

use CodeIgniter\Model;

class AtencionModel extends Model
{
    protected $table      = 't_atencion';
    protected $primaryKey = 'ate_codigo';
    protected $returnType = 'array';

    protected $allowedFields = [
        'ate_fecha',
        'ate_referido',
        'ate_telefono',
        'ate_hora',
        'ate_motivo_policia',
        'ate_otro_motivo',
        'ate_fecha_evento',
        'ate_lugar_evento',
        'ate_direccion_evento',
        'ate_custodia_policial',//se guarda aqui como Si o No
        'ate_observaciones',
        'ate_colores',
        'pac_codigo',
        'lleg_codigo',
        'ate_aliento_etilico',//se guarda aqui como Si o No
        'ate_valor_alcolchek',
        'ate_fuente_informacion',
        'ate_ins_entrega_paciente'
    ];

    /**
     * Obtener datos de atención con información adicional para enfermería
     * Incluye los campos específicos: lleg_codigo, ate_fuente_informacion, 
     * ate_ins_entrega_paciente, ate_telefono
     */
    public function obtenerDatosAtencionEnfermeria($pacienteId)
    {
        return $this->select([
            't_atencion.*',
            'tl.lleg_nombre' // Nombre de la forma de llegada si necesitas el texto
        ])
        ->join('t_llegada tl', 't_atencion.lleg_codigo = tl.lleg_codigo', 'left')
        ->where('pac_codigo', $pacienteId)
        ->first();
    }

    /**
     * Obtener solo los campos específicos solicitados para enfermería
     */
    public function obtenerCamposEspecificosEnfermeria($pacienteId)
    {
        return $this->select([
            'lleg_codigo',
            'ate_fuente_informacion', 
            'ate_ins_entrega_paciente',
            'ate_telefono',
            // Campos adicionales que podrían ser útiles
            'ate_motivo_policia',
            'ate_fecha_evento',
            'ate_fecha',
            'ate_hora',
            'ate_lugar_evento',
            'ate_direccion_evento',
            'ate_custodia_policial',
            'ate_observaciones',
            'ate_aliento_etilico',
            'ate_valor_alcolchek'
        ])
        ->where('pac_codigo', $pacienteId)
        ->first();
    }

    /**
     * Método para obtener atención con validación de existencia
     */
    public function obtenerAtencionPorPaciente($pacienteId)
    {
        if (empty($pacienteId)) {
            return null;
        }

        $atencion = $this->where('pac_codigo', $pacienteId)->first();
        
        if (!$atencion) {
            log_message('info', "No se encontró atención para el paciente ID: $pacienteId");
            return null;
        }

        return $atencion;
    }

    /**
     * Verificar si un paciente tiene atención registrada
     */
    public function pacienteTieneAtencion($pacienteId)
    {
        return $this->where('pac_codigo', $pacienteId)->countAllResults() > 0;
    }
}