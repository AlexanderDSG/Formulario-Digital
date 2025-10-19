<?php

namespace App\Models\Especialidades;

use CodeIgniter\Model;

class ObservacionEspecialidadModel extends Model
{
    protected $table = 't_observacion_especialidad';
    protected $primaryKey = 'obs_codigo';

    protected $allowedFields = [
        'ate_codigo',
        'are_codigo_origen',
        'esp_codigo_origen',
        'usu_id_envia',
        'obs_motivo',
        'obs_fecha_envio',
        'obs_hora_envio',
        'obs_estado',
        'obs_fecha_recepcion',
        'obs_hora_recepcion',
        'usu_id_recibe'
    ];

    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';
    /**
     * Obtener información completa del envío a observación
     */
    public function obtenerInfoEnvioObservacion($ate_codigo)
    {
        $db = \Config\Database::connect();

        $query = $db->query("
        SELECT 
            oe.obs_motivo as motivo,
            oe.obs_fecha_envio as fecha_envio,
            oe.obs_hora_envio as hora_envio,
            e.esp_nombre as especialidad_origen,
            u.usu_nombre,
            u.usu_apellido
        FROM t_observacion_especialidad oe
        JOIN t_especialidad e ON oe.esp_codigo_origen = e.esp_codigo
        JOIN t_usuario u ON oe.usu_id_envia = u.usu_id
        WHERE oe.ate_codigo = ? 
        AND oe.obs_estado = 'ENVIADO_A_OBSERVACION'
        ORDER BY oe.obs_fecha_envio DESC, oe.obs_hora_envio DESC 
        LIMIT 1
    ", [$ate_codigo]);

        $resultado = $query->getRowArray();

        if ($resultado) {
            return [
                'motivo' => $resultado['motivo'],
                'especialidad_origen' => $resultado['especialidad_origen'],
                'fecha_envio' => $resultado['fecha_envio'],
                'hora_envio' => $resultado['hora_envio'],
                'usuario_que_envio' => $resultado['usu_nombre'] . ' ' . $resultado['usu_apellido']
            ];
        }

        // Si no encuentra datos, retornar valores por defecto
        return [
            'motivo' => 'Motivo no registrado',
            'especialidad_origen' => 'Especialidad no encontrada',
            'fecha_envio' => '',
            'hora_envio' => '',
            'usuario_que_envio' => 'Usuario no encontrado'
        ];
    }

    /**
     * Obtener solo el motivo del envío (método simplificado existente)
     */
    public function obtenerMotivoEnvioObservacion($ate_codigo)
    {
        return $this->select('obs_motivo')
            ->where('ate_codigo', $ate_codigo)
            ->where('obs_estado', 'ENVIADO_A_OBSERVACION')
            ->orderBy('obs_fecha_envio', 'DESC')
            ->orderBy('obs_hora_envio', 'DESC')
            ->first()['obs_motivo'] ?? 'Enviado a observación';
    }

}