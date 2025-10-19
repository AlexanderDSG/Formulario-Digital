<?php

namespace App\Models\Especialidades;

use CodeIgniter\Model;

class EvolucionPrescripcionesModel extends Model
{
    protected $table = 't_evolucion_prescripciones';
    protected $primaryKey = 'ep_codigo';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = false;
    protected $protectFields = true;

    protected $allowedFields = [
        'ate_codigo',
        'ep_fecha',
        'ep_hora',
        'ep_notas_evolucion',
        'ep_farmacoterapia',
        'ep_administrado',
        'ep_orden',
        'ep_numero_hoja',
        'usu_id_registro'
    ];

    protected $useTimestamps = true;
    protected $createdField = 'fecha_registro';
    protected $updatedField = 'fecha_modificacion';

    protected $validationRules = [
        'ate_codigo' => 'required|integer',
        'ep_fecha' => 'permit_empty|valid_date',
        'ep_hora' => 'permit_empty|regex_match[/^([01]?[0-9]|2[0-3]):[0-5][0-9](:[0-5][0-9])?$/]',
        'ep_notas_evolucion' => 'permit_empty|string|max_length[65535]',
        'ep_farmacoterapia' => 'permit_empty|string|max_length[65535]',
        'ep_administrado' => 'permit_empty|in_list[0,1]',
        'ep_orden' => 'permit_empty|integer',
        'ep_numero_hoja' => 'permit_empty|string|max_length[10]',
        'usu_id_registro' => 'permit_empty|integer'
    ];

    protected $validationMessages = [
        'ate_codigo' => [
            'required' => 'El código de atención es obligatorio',
            'integer' => 'El código de atención debe ser un número válido'
        ],
        'ep_fecha' => [
            'valid_date' => 'La fecha debe tener un formato válido'
        ],
        'ep_hora' => [
            'regex_match' => 'La hora debe tener el formato HH:MM o HH:MM:SS'
        ],
        'ep_numero_hoja' => [
            'max_length' => 'El número de hoja no puede exceder 10 caracteres'
        ]
    ];

    protected $skipValidation = false;
    protected $cleanValidationRules = true;

    protected $allowCallbacks = true;
    protected $beforeInsert = ['setDefaultValues'];
    protected $afterInsert = [];
    protected $beforeUpdate = ['setDefaultValues'];
    protected $afterUpdate = [];
    protected $beforeFind = [];
    protected $afterFind = [];
    protected $beforeDelete = [];
    protected $afterDelete = [];

    /**
     * Establecer valores por defecto antes de insertar/actualizar
     */
    protected function setDefaultValues(array $data)
    {
        if (isset($data['data'])) {
            // Establecer usuario de registro si no está presente
            if (!isset($data['data']['usu_id_registro']) && session()->get('usu_id')) {
                $data['data']['usu_id_registro'] = session()->get('usu_id');
            }

            // Establecer orden por defecto si no está presente
            if (!isset($data['data']['ep_orden'])) {
                $data['data']['ep_orden'] = 1;
            }

            // Normalizar valor de administrado
            if (isset($data['data']['ep_administrado'])) {
                $data['data']['ep_administrado'] = $data['data']['ep_administrado'] ? 1 : 0;
            }
        }

        return $data;
    }

    /**
     * Obtener todas las evoluciones de una atención específica
     */
    public function obtenerPorAtencion($ate_codigo)
    {
        return $this->where('ate_codigo', $ate_codigo)
                   ->orderBy('ep_fecha', 'ASC')
                   ->orderBy('ep_hora', 'ASC')
                   ->orderBy('ep_orden', 'ASC')
                   ->findAll();
    }

    /**
     * Obtener evoluciones con información del usuario que las registró
     */
    public function obtenerConUsuario($ate_codigo)
    {
        return $this->select('t_evolucion_prescripciones.*, 
                             t_usuario.usu_nombre, 
                             t_usuario.usu_apellido,
                             t_especialidad.esp_nombre')
                   ->join('t_usuario', 't_evolucion_prescripciones.usu_id_registro = t_usuario.usu_id', 'left')
                   ->join('t_usuario_especialidad', 't_usuario.usu_id = t_usuario_especialidad.usu_id', 'left')
                   ->join('t_especialidad', 't_usuario_especialidad.esp_codigo = t_especialidad.esp_codigo', 'left')
                   ->where('t_evolucion_prescripciones.ate_codigo', $ate_codigo)
                   ->orderBy('t_evolucion_prescripciones.ep_fecha', 'ASC')
                   ->orderBy('t_evolucion_prescripciones.ep_hora', 'ASC')
                   ->orderBy('t_evolucion_prescripciones.ep_orden', 'ASC')
                   ->findAll();
    }

    /**
     * Contar registros de evolución para una atención
     */
    public function contarPorAtencion($ate_codigo)
    {
        return $this->where('ate_codigo', $ate_codigo)->countAllResults();
    }

    /**
     * Obtener la última evolución registrada para una atención
     */
    public function obtenerUltima($ate_codigo)
    {
        return $this->where('ate_codigo', $ate_codigo)
                   ->orderBy('ep_fecha', 'DESC')
                   ->orderBy('ep_hora', 'DESC')
                   ->orderBy('ep_orden', 'DESC')
                   ->first();
    }

    /**
     * Obtener evoluciones por fecha específica
     */
    public function obtenerPorFecha($ate_codigo, $fecha)
    {
        return $this->where('ate_codigo', $ate_codigo)
                   ->where('ep_fecha', $fecha)
                   ->orderBy('ep_hora', 'ASC')
                   ->orderBy('ep_orden', 'ASC')
                   ->findAll();
    }

    /**
     * Obtener evoluciones pendientes de administración
     */
    public function obtenerPendientesAdministracion($ate_codigo)
    {
        return $this->where('ate_codigo', $ate_codigo)
                   ->where('ep_administrado', 0)
                   ->where('ep_farmacoterapia !=', '')
                   ->whereNotNull('ep_farmacoterapia')
                   ->orderBy('ep_fecha', 'ASC')
                   ->orderBy('ep_hora', 'ASC')
                   ->findAll();
    }

    /**
     * Marcar farmacoterapia como administrada
     */
    public function marcarComoAdministrado($ep_codigo)
    {
        return $this->update($ep_codigo, [
            'ep_administrado' => 1
        ]);
    }

    /**
     * Eliminar todas las evoluciones de una atención
     */
    public function eliminarPorAtencion($ate_codigo)
    {
        return $this->where('ate_codigo', $ate_codigo)->delete();
    }

    /**
     * Guardar múltiples evoluciones en una transacción (actualizar existentes, insertar nuevos)
     */
    public function guardarMultiples($ate_codigo, $evoluciones, $datos_adicionales = [])
    {
        $db = \Config\Database::connect();
        $db->transStart();

        try {
            // Obtener o generar el número de hoja para esta atención
            $numeroHoja = $this->obtenerNumeroHojaParaAtencion($ate_codigo);
            log_message('info', "📄 Número de hoja asignado para atención $ate_codigo: $numeroHoja");

            // Obtener registros existentes para esta atención
            $existentes = $this->obtenerPorAtencion($ate_codigo);
            $existentesPorCodigo = [];
            $existentesPorOrden = [];

            foreach ($existentes as $existente) {
                $existentesPorCodigo[$existente['ep_codigo']] = $existente;
                $existentesPorOrden[$existente['ep_orden']] = $existente;
            }

            $registrosActualizados = 0;
            $registrosInsertados = 0;
            $registrosProcesados = [];

            foreach ($evoluciones as $index => $evolucion) {
                log_message('info', "🔄 Procesando evolución $index: " . json_encode($evolucion));

                // Solo procesar si tiene algún contenido
                if (!empty($evolucion['ep_fecha']) ||
                    !empty($evolucion['ep_notas_evolucion']) ||
                    !empty($evolucion['ep_farmacoterapia'])) {

                    $orden = $evolucion['ep_orden'] ?? ($index + 1);
                    $ep_codigo_existente = $evolucion['ep_codigo'] ?? null;

                    $data = array_merge([
                        'ate_codigo' => $ate_codigo,
                        'ep_fecha' => $evolucion['ep_fecha'] ?? null,
                        'ep_hora' => $evolucion['ep_hora'] ?? null,
                        'ep_notas_evolucion' => $evolucion['ep_notas_evolucion'] ?? '',
                        'ep_farmacoterapia' => $evolucion['ep_farmacoterapia'] ?? '',
                        'ep_administrado' => isset($evolucion['ep_administrado']) ?
                                           ($evolucion['ep_administrado'] ? 1 : 0) : 0,
                        'ep_orden' => $orden,
                        'ep_numero_hoja' => $numeroHoja,
                        'usu_id_registro' => session()->get('usu_id')
                    ], $datos_adicionales);

                    log_message('info', "📦 Datos preparados para guardar: " . json_encode($data));

                    log_message('info', "🔍 Buscando registro - ep_codigo_existente: '$ep_codigo_existente'");
                    log_message('info', "🔍 Registros existentes disponibles: " . json_encode(array_keys($existentesPorCodigo)));

                    // Priorizar actualización por ep_codigo si está disponible
                    if ($ep_codigo_existente && isset($existentesPorCodigo[$ep_codigo_existente])) {
                        log_message('info', "Encontrado registro existente con ep_codigo: $ep_codigo_existente");

                        // Verificar errores de validación antes de actualizar
                        $this->skipValidation = false;

                        // Actualizar registro existente usando ep_codigo
                        try {
                            $resultado = $this->update($ep_codigo_existente, $data);
                            if ($resultado) {
                                $registrosActualizados++;
                                log_message('info', "Actualizado registro ep_codigo: $ep_codigo_existente con ep_administrado: " . $data['ep_administrado']);
                            } else {
                                $errores = $this->errors();
                                log_message('error', "Error al actualizar registro ep_codigo: $ep_codigo_existente");
                                log_message('error', "📝 Errores de validación: " . json_encode($errores));
                                log_message('error', "📦 Datos que causaron error: " . json_encode($data));
                            }
                        } catch (\Exception $e) {
                            log_message('error', "💥 Excepción al actualizar ep_codigo $ep_codigo_existente: " . $e->getMessage());
                        }
                        $registrosProcesados[] = $ep_codigo_existente;
                        // Remover de ambas listas para no eliminarlo después
                        unset($existentesPorCodigo[$ep_codigo_existente]);
                        foreach ($existentesPorOrden as $key => $existente) {
                            if ($existente['ep_codigo'] == $ep_codigo_existente) {
                                unset($existentesPorOrden[$key]);
                                break;
                            }
                        }
                    } else if (isset($existentesPorOrden[$orden])) {
                        log_message('info', "No se encontró por ep_codigo, usando fallback por orden: $orden");
                        // Fallback: actualizar por orden si no hay ep_codigo
                        $registro_existente = $existentesPorOrden[$orden];
                        $ep_codigo = $registro_existente['ep_codigo'];
                        if ($this->update($ep_codigo, $data)) {
                            $registrosActualizados++;
                            log_message('info', "Actualizado registro por orden $orden (ep_codigo: $ep_codigo) con ep_administrado: " . $data['ep_administrado']);
                        }
                        $registrosProcesados[] = $ep_codigo;
                        unset($existentesPorOrden[$orden]);
                        unset($existentesPorCodigo[$ep_codigo]);
                    } else {
                        log_message('info', "🆕 No se encontró registro existente, insertando nuevo");
                        // Si no existe, insertarlo
                        if ($ep_codigo_nuevo = $this->insert($data)) {
                            $registrosInsertados++;
                            log_message('info', "Insertado nuevo registro con ep_administrado: " . $data['ep_administrado']);
                        } else {
                            log_message('error', "Error al insertar nuevo registro");
                        }
                    }
                } else {
                    log_message('info', "Saltando evolución $index - sin contenido");
                }
            }

            // Eliminar registros que ya no están en el formulario (filas vacías eliminadas)
            $registrosEliminados = 0;
            $registrosSobrantes = array_merge($existentesPorCodigo, $existentesPorOrden);
            foreach ($registrosSobrantes as $registroSobrante) {
                if ($this->delete($registroSobrante['ep_codigo'])) {
                    $registrosEliminados++;
                    log_message('info', "Eliminado registro ep_codigo: " . $registroSobrante['ep_codigo']);
                }
            }

            $db->transComplete();

            if ($db->transStatus() === false) {
                throw new \Exception('Error en la transacción');
            }

            log_message('info', "Evoluciones procesadas - Actualizados: {$registrosActualizados}, Insertados: {$registrosInsertados}, Eliminados: {$registrosEliminados}");

            return [
                'success' => true,
                'registros_insertados' => $registrosInsertados,
                'registros_actualizados' => $registrosActualizados,
                'registros_eliminados' => $registrosEliminados,
                'total_procesados' => $registrosActualizados + $registrosInsertados
            ];

        } catch (\Exception $e) {
            $db->transRollback();
            log_message('error', 'Error en guardarMultiples: ' . $e->getMessage());
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Obtener estadísticas de evoluciones para una atención
     */
    public function obtenerEstadisticas($ate_codigo)
    {
        $total = $this->contarPorAtencion($ate_codigo);
        $administrados = $this->where('ate_codigo', $ate_codigo)
                             ->where('ep_administrado', 1)
                             ->countAllResults();
        $pendientes = $total - $administrados;

        $primera = $this->where('ate_codigo', $ate_codigo)
                       ->orderBy('ep_fecha', 'ASC')
                       ->orderBy('ep_hora', 'ASC')
                       ->first();

        $ultima = $this->obtenerUltima($ate_codigo);

        return [
            'total_registros' => $total,
            'administrados' => $administrados,
            'pendientes' => $pendientes,
            'primera_fecha' => $primera ? $primera['ep_fecha'] : null,
            'ultima_fecha' => $ultima ? $ultima['ep_fecha'] : null
        ];
    }

    /**
     * Buscar evoluciones por contenido de texto
     */
    public function buscarPorTexto($ate_codigo, $texto)
    {
        return $this->where('ate_codigo', $ate_codigo)
                   ->groupStart()
                       ->like('ep_notas_evolucion', $texto)
                       ->orLike('ep_farmacoterapia', $texto)
                   ->groupEnd()
                   ->orderBy('ep_fecha', 'DESC')
                   ->orderBy('ep_hora', 'DESC')
                   ->findAll();
    }

    /**
     * Generar el siguiente número de hoja para el día actual
     * Lógica: Secuencial GLOBAL por atención del día (médicos + especialidades), reinicia cada 24 horas (00:00)
     */
    public function generarNumeroHoja()
    {
        $fechaActual = date('Y-m-d');

        // Obtener el máximo número de hoja usado en el día actual (global entre médicos y especialidades)
        $ultimoNumero = $this->select('MAX(CAST(ep_numero_hoja AS UNSIGNED)) as max_numero')
                           ->where('DATE(fecha_registro)', $fechaActual)
                           ->where('ep_numero_hoja IS NOT NULL')
                           ->where('ep_numero_hoja != ""')
                           ->first();

        // Si hay números existentes, usar el siguiente; sino empezar en 1
        $siguienteNumero = ($ultimoNumero && $ultimoNumero['max_numero']) ?
                          $ultimoNumero['max_numero'] + 1 : 1;

        // Formatear con ceros a la izquierda (00001)
        return str_pad($siguienteNumero, 5, '0', STR_PAD_LEFT);
    }

    /**
     * Verificar si una atención ya tiene hoja del día actual
     */
    public function tieneHojaDelDia($ate_codigo)
    {
        $fechaActual = date('Y-m-d');

        return $this->where('ate_codigo', $ate_codigo)
                   ->where('DATE(fecha_registro)', $fechaActual)
                   ->first();
    }

    /**
     * Obtener o generar número de hoja para una atención
     */
    public function obtenerNumeroHojaParaAtencion($ate_codigo)
    {
        // Verificar si ya tiene una hoja del día actual
        $hojaExistente = $this->tieneHojaDelDia($ate_codigo);

        if ($hojaExistente) {
            // Si ya tiene hoja del día, usar el mismo número
            return $hojaExistente['ep_numero_hoja'];
        } else {
            // Si no tiene hoja del día, generar nuevo número
            return $this->generarNumeroHoja();
        }
    }

    /**
     * Obtener evoluciones para reporte
     */
    public function obtenerParaReporte($fecha_inicio, $fecha_fin, $especialidad = null)
    {
        $builder = $this->select('t_evolucion_prescripciones.*, 
                                 t_atencion.pac_codigo,
                                 t_paciente.pac_nombres, 
                                 t_paciente.pac_apellidos,
                                 t_paciente.pac_cedula,
                                 t_usuario.usu_nombre,
                                 t_usuario.usu_apellido,
                                 t_especialidad.esp_nombre')
                       ->join('t_atencion', 't_evolucion_prescripciones.ate_codigo = t_atencion.ate_codigo')
                       ->join('t_paciente', 't_atencion.pac_codigo = t_paciente.pac_codigo')
                       ->join('t_usuario', 't_evolucion_prescripciones.usu_id_registro = t_usuario.usu_id', 'left')
                       ->join('t_usuario_especialidad', 't_usuario.usu_id = t_usuario_especialidad.usu_id', 'left')
                       ->join('t_especialidad', 't_usuario_especialidad.esp_codigo = t_especialidad.esp_codigo', 'left')
                       ->where('t_evolucion_prescripciones.ep_fecha >=', $fecha_inicio)
                       ->where('t_evolucion_prescripciones.ep_fecha <=', $fecha_fin);

        if ($especialidad) {
            $builder->where('t_especialidad.esp_codigo', $especialidad);
        }

        return $builder->orderBy('t_evolucion_prescripciones.ep_fecha', 'DESC')
                      ->orderBy('t_evolucion_prescripciones.ep_hora', 'DESC')
                      ->findAll();
    }
}