<?php

namespace App\Models\Especialidades;

use CodeIgniter\Model;

class ProcesoParcialModel extends Model
{
    protected $table = 't_proceso_parcial_especialidad';
    protected $primaryKey = 'ppe_codigo';

    protected $allowedFields = [
        'ate_codigo',
        'are_codigo',
        'esp_codigo',
        'usu_id_especialista',
        'ppe_estado',
        'ppe_fecha_guardado',
        'ppe_hora_guardado',
        'ppe_observaciones',
        'ppe_seccion_especialista_datos',
        'ppe_firma_especialista',
        'ppe_sello_especialista'
    ];

    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    /**
     * Guardar proceso parcial del especialista
     */
    public function guardarProcesoParcial($ate_codigo, $are_codigo, $esp_codigo, $usu_id, $datosEspecialista, $rutaFirma = null, $rutaSello = null, $observaciones = null)
    {
        // Verificar si ya existe un proceso parcial
        $existente = $this->where('ate_codigo', $ate_codigo)
            ->where('are_codigo', $are_codigo)
            ->first();

        $data = [
            'ate_codigo' => $ate_codigo,
            'are_codigo' => $are_codigo,
            'esp_codigo' => $esp_codigo,
            'usu_id_especialista' => $usu_id,
            'ppe_estado' => 'EN_PROCESO',
            'ppe_fecha_guardado' => date('Y-m-d'),
            'ppe_hora_guardado' => date('H:i:s'),
            'ppe_observaciones' => $observaciones,
            'ppe_seccion_especialista_datos' => json_encode($datosEspecialista),
            'ppe_firma_especialista' => $rutaFirma,
            'ppe_sello_especialista' => $rutaSello
        ];

        if ($existente) {
            // Actualizar registro existente
            log_message('info', "Actualizando proceso parcial existente con nuevo usuario: $usu_id");
            return $this->update($existente['ppe_codigo'], $data);
        } else {
            // Crear nuevo registro
            log_message('info', "Creando nuevo proceso parcial con usuario: $usu_id");
            return $this->insert($data);
        }
    }

    /**
     * Obtener proceso parcial por atención
     */
    public function obtenerProcesoPorAtencion($ate_codigo)
    {
        return $this->select('
                t_proceso_parcial_especialidad.*,
                t_usuario.usu_nombre,
                t_usuario.usu_apellido,
                t_especialidad.esp_nombre
            ')
            ->join('t_usuario', 't_proceso_parcial_especialidad.usu_id_especialista = t_usuario.usu_id')
            ->join('t_especialidad', 't_proceso_parcial_especialidad.esp_codigo = t_especialidad.esp_codigo')
            ->where('ate_codigo', $ate_codigo)
            ->first();
    }

    /**
     * Completar proceso parcial
     */
    public function completarProceso($ate_codigo)
    {
        return $this->where('ate_codigo', $ate_codigo)
            ->set('ppe_estado', 'COMPLETADO')
            ->set('updated_at', date('Y-m-d H:i:s'))
            ->update();
    }

    /**
     * Obtener procesos en curso por especialidad
     */
    public function obtenerProcesosEnCursoPorEspecialidad($esp_codigo)
    {
        return $this->select('
                t_proceso_parcial_especialidad.*,
                t_usuario.usu_nombre,
                t_usuario.usu_apellido,
                t_paciente.pac_nombres,
                t_paciente.pac_apellidos,
                t_paciente.pac_cedula,
                t_atencion.ate_fecha,
                t_atencion.ate_hora,
                t_atencion.ate_colores
            ')
            ->join('t_usuario', 't_proceso_parcial_especialidad.usu_id_especialista = t_usuario.usu_id')
            ->join('t_atencion', 't_proceso_parcial_especialidad.ate_codigo = t_atencion.ate_codigo')
            ->join('t_paciente', 't_atencion.pac_codigo = t_paciente.pac_codigo')
            ->where('t_proceso_parcial_especialidad.esp_codigo', $esp_codigo)
            ->where('t_proceso_parcial_especialidad.ppe_estado', 'EN_PROCESO')
            ->findAll();
    }
}