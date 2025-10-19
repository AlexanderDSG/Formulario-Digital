<?php

namespace App\Models\Especialidades;

use CodeIgniter\Model;

class EnfermeriaEspecialidadModel extends Model
{
    protected $table = 't_enfermeria_especialidad';
    protected $primaryKey = 'enf_codigo';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = false;
    protected $protectFields = true;
    protected $allowedFields = [
        'ate_codigo',
        'are_codigo_origen',
        'esp_codigo_origen',
        'usu_id_envia',
        'enf_motivo',
        'enf_fecha_envio',
        'enf_hora_envio',
        'enf_estado',
        'enf_fecha_recepcion',
        'enf_hora_recepcion',
        'usu_id_recibe',
        'enf_observaciones_enfermeria',
        'enf_datos_especialista',
    ];

    protected $useTimestamps = false;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    protected $validationRules = [
        'ate_codigo' => 'required|integer',
        'are_codigo_origen' => 'required|integer',
        'esp_codigo_origen' => 'required|integer',
        'usu_id_envia' => 'required|integer',
        'enf_fecha_envio' => 'required|valid_date',
        'enf_hora_envio' => 'required',
        'enf_estado' => 'in_list[ENVIADO_A_ENFERMERIA,EN_ATENCION_ENFERMERIA,COMPLETADO]'
    ];

    protected $validationMessages = [];
    protected $skipValidation = false;

    /**
     * Obtener información del envío a enfermería
     */
    public function obtenerInfoEnvioEnfermeria($ate_codigo)
    {
        $db = \Config\Database::connect();
        
        $query = $db->query("
            SELECT 
                ee.enf_motivo,
                ee.enf_fecha_envio,
                ee.enf_hora_envio,
                ee.enf_estado,
                e.esp_nombre as especialidad_origen,
                u.usu_nombre,
                u.usu_apellido
            FROM t_enfermeria_especialidad ee
            JOIN t_especialidad e ON ee.esp_codigo_origen = e.esp_codigo
            JOIN t_usuario u ON ee.usu_id_envia = u.usu_id
            WHERE ee.ate_codigo = ?
            AND ee.enf_estado = 'ENVIADO_A_ENFERMERIA'
            ORDER BY ee.enf_fecha_envio DESC, ee.enf_hora_envio DESC
            LIMIT 1
        ", [$ate_codigo]);

        $resultado = $query->getRowArray();

        if ($resultado) {
            return [
                'motivo' => $resultado['enf_motivo'],
                'fecha_envio' => $resultado['enf_fecha_envio'],
                'hora_envio' => $resultado['enf_hora_envio'],
                'estado' => $resultado['enf_estado'],
                'especialidad_origen' => $resultado['especialidad_origen'],
                'usuario_que_envio' => $resultado['usu_nombre'] . ' ' . $resultado['usu_apellido']
            ];
        }

        return [
            'motivo' => null,
            'fecha_envio' => null,
            'hora_envio' => null,
            'estado' => null,
            'especialidad_origen' => null,
            'usuario_que_envio' => null
        ];
    }

    /**
     * Marcar como recibido por enfermería
     */
    public function marcarComoRecibido($enf_codigo, $usu_id_recibe)
    {
        return $this->update($enf_codigo, [
            'enf_estado' => 'EN_ATENCION_ENFERMERIA',
            'enf_fecha_recepcion' => date('Y-m-d'),
            'enf_hora_recepcion' => date('H:i:s'),
            'usu_id_recibe' => $usu_id_recibe
        ]);
    }

    /**
     * Completar atención de enfermería
     */
    public function completarAtencionEnfermeria($enf_codigo, $observaciones = null)
    {
        $data = [
            'enf_estado' => 'COMPLETADO'
        ];
        
        if ($observaciones) {
            $data['enf_observaciones_enfermeria'] = $observaciones;
        }
        
        return $this->update($enf_codigo, $data);
    }

    /**
     * Obtener estadísticas de enfermería por especialidad
     */
    public function obtenerEstadisticasPorEspecialidad($esp_codigo)
    {
        $db = \Config\Database::connect();
        
        $query = $db->query("
            SELECT 
                enf_estado,
                COUNT(*) as cantidad
            FROM t_enfermeria_especialidad ee
            WHERE ee.esp_codigo_origen = ?
            GROUP BY enf_estado
        ", [$esp_codigo]);

        $resultados = $query->getResultArray();
        
        $estadisticas = [
            'ENVIADO_A_ENFERMERIA' => 0,
            'EN_ATENCION_ENFERMERIA' => 0,
            'COMPLETADO' => 0
        ];

        foreach ($resultados as $resultado) {
            $estadisticas[$resultado['enf_estado']] = $resultado['cantidad'];
        }

        return $estadisticas;
    }
}