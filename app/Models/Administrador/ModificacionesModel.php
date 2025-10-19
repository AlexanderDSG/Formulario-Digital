<?php

namespace App\Models\Administrador;

use CodeIgniter\Model;

class ModificacionesModel extends Model
{
    protected $table = 't_formulario_usuario';
    protected $primaryKey = 'id';

    protected $allowedFields = [
        'ate_codigo',
        'usu_id',
        'seccion',
        'fecha',
        'modificaciones_permitidas',
        'modificaciones_usadas',
        'habilitado_por_admin',
        'admin_que_habilito',
        'motivo_habilitacion',
        'fecha_habilitacion'
    ];

    /**
     * Obtener pacientes que pueden ser modificados (formularios completados) - ACTUALIZADO PARA DATATABLES
     */
    public function obtenerPacientesModificables($fechaInicio = null, $fechaFin = null, $estado = null)
    {
        $db = \Config\Database::connect();

        // Construir query base
        $whereConditions = ["fu.seccion IN ('ME', 'ES')"];
        $params = [];

        // Agregar filtros de fecha si se proporcionan
        if (!empty($fechaInicio)) {
            $whereConditions[] = "DATE(a.ate_fecha) >= ?";
            $params[] = $fechaInicio;
        }

        if (!empty($fechaFin)) {
            $whereConditions[] = "DATE(a.ate_fecha) <= ?";
            $params[] = $fechaFin;
        }

        // Agregar filtro de estado si se proporciona
        if (!empty($estado) && $estado !== 'todos') {
            $whereConditions[] = "fu.seccion = ?";
            $params[] = $estado;
        }

        $whereClause = implode(' AND ', $whereConditions);

        $query = "
        SELECT
            fu.ate_codigo,
            fu.usu_id,
            fu.seccion,
            fu.fecha,
            fu.modificaciones_permitidas,
            fu.modificaciones_usadas,
            fu.habilitado_por_admin,
            fu.motivo_habilitacion,
            fu.fecha_habilitacion,

            -- Datos del paciente
            p.pac_nombres,
            p.pac_apellidos,
            p.pac_cedula,
            p.pac_his_cli,

            -- Datos de la atención
            a.ate_fecha,
            a.ate_hora,

            -- Datos del profesional que completó
            u.usu_nombre,
            u.usu_apellido,
            r.rol_nombre,

            -- Datos del admin que habilitó (si aplica)
            admin.usu_nombre as admin_nombre,
            admin.usu_apellido as admin_apellido,

            -- Determinar si puede modificar - LÓGICA COMPLETA
            CASE
                WHEN fu.modificaciones_usadas >= fu.modificaciones_permitidas THEN 0  -- NO puede si ya alcanzó el límite
                WHEN fu.habilitado_por_admin = 1 THEN 1  -- SÍ puede si está habilitado por admin
                WHEN fu.modificaciones_usadas < fu.modificaciones_permitidas THEN 1  -- SÍ puede si tiene modificaciones restantes
                ELSE 0
            END as puede_modificar,

            -- Motivo del bloqueo si aplica
            CASE
                WHEN fu.modificaciones_usadas >= fu.modificaciones_permitidas THEN 'Límite de modificaciones alcanzado (3/3)'
                WHEN fu.habilitado_por_admin = 1 THEN 'Habilitado por administrador'
                WHEN fu.modificaciones_usadas < fu.modificaciones_permitidas THEN 'Puede modificar naturalmente'
                ELSE 'Sin modificaciones disponibles'
            END as motivo_bloqueo,

            -- Estado para el botón del admin
            CASE
                WHEN fu.modificaciones_usadas >= fu.modificaciones_permitidas THEN 'BLOQUEADO'
                WHEN fu.habilitado_por_admin = 1 THEN 'HABILITADO'
                ELSE 'PUEDE_HABILITAR'
            END as estado_boton_admin,

            -- Información del último usuario que modificó
            CONCAT(u.usu_nombre, ' ', u.usu_apellido) as ultimo_usuario,
            fu.fecha as fecha_ultima_modificacion,

            -- Contador de modificaciones
            fu.modificaciones_usadas as modificaciones_count,

            -- Tipo de profesional
            CASE
                WHEN fu.seccion = 'ME' THEN 'Médico de Triaje'
                WHEN fu.seccion = 'ES' THEN 'Médico Especialista'
                ELSE 'Otro'
            END as tipo_profesional

        FROM t_formulario_usuario fu
        JOIN t_atencion a ON fu.ate_codigo = a.ate_codigo
        JOIN t_paciente p ON a.pac_codigo = p.pac_codigo
        JOIN t_usuario u ON fu.usu_id = u.usu_id
        JOIN t_rol r ON u.rol_id = r.rol_id
        LEFT JOIN t_usuario admin ON fu.admin_que_habilito = admin.usu_id

        WHERE $whereClause

        ORDER BY
            fu.habilitado_por_admin DESC,  -- Modificaciones habilitadas primero
            fu.fecha DESC
        ";

        try {
            return $db->query($query, $params)->getResultArray();
        } catch (\Exception $e) {
            throw $e;
        }
    }
    /**
     * Habilitar modificación para un formulario específico
     * Con reenvío de atención
     */
    public function habilitarModificacion($ate_codigo, $seccion, $admin_id, $motivo)
    {
        $db = \Config\Database::connect();
        $db->transStart();

        try {
            // 1. Buscar el registro del formulario
            $formulario = $db->table('t_formulario_usuario')
                ->where('ate_codigo', $ate_codigo)
                ->where('seccion', $seccion)
                ->get()
                ->getRowArray();

            if (!$formulario) {
                throw new \Exception("No se encontró formulario para ate_codigo: {$ate_codigo}, sección: {$seccion}");
            }

            // 2. Verificar que no esté ya habilitado
            if ($formulario['habilitado_por_admin'] == 1) {
                return true;
            }

            // 3. Actualizar para habilitar modificación
            $updateData = [
                'habilitado_por_admin' => 1,
                'admin_que_habilito' => $admin_id,
                'motivo_habilitacion' => $motivo,
                'fecha_habilitacion' => date('Y-m-d H:i:s')
            ];

            $resultado = $db->table('t_formulario_usuario')
                ->where('ate_codigo', $ate_codigo)
                ->where('seccion', $seccion)
                ->update($updateData);

            if (!$resultado) {
                $error = $db->error();
                throw new \Exception("Error al actualizar t_formulario_usuario: " . json_encode($error));
            }

            // 4. REENVIAR LA ATENCIÓN A LA LISTA CORRESPONDIENTE
            try {
                $this->reenviarAtencion($ate_codigo, $seccion);
            } catch (\Exception $e) {
                // Continuar si hay error en reenvío
            }

            // 5. Registrar en historial (si la tabla existe)
            try {
                $this->registrarEnHistorial($ate_codigo, $formulario['usu_id'], $seccion, 'HABILITADO_MODIFICACION', $motivo, $admin_id);
            } catch (\Exception $e) {
                // No fallar por esto
            }

            $db->transComplete();

            if ($db->transStatus() === false) {
                $dbError = $db->error();
                throw new \Exception('Error en transacción de base de datos: ' . json_encode($dbError));
            }

            return true;

        } catch (\Exception $e) {
            $db->transRollback();
            throw $e;
        }
    }

    /**
     * Reenviar atención a la lista correspondiente
     */
    private function reenviarAtencion($ate_codigo, $seccion)
    {
        $db = \Config\Database::connect();

        try {
            // Verificar que la atención existe
            $atencionExiste = $db->table('t_atencion')
                ->where('ate_codigo', $ate_codigo)
                ->get()
                ->getNumRows() > 0;

            if (!$atencionExiste) {
                throw new \Exception("Atención {$ate_codigo} no existe en t_atencion");
            }

            if ($seccion === 'ME') {
                // Actualizar estado de atención para indicar que está en modificación
                $fields = $db->getFieldNames('t_atencion');
                if (in_array('estado', $fields)) {
                    $db->table('t_atencion')
                        ->where('ate_codigo', $ate_codigo)
                        ->update([
                            'estado' => 'Disponible para modificación',
                            'fecha_ultimo_cambio' => date('Y-m-d H:i:s')
                        ]);
                }

                // Eliminar asignaciones a especialidad si existen (para que vuelva a médicos)
                $db->table('t_area_atencion')
                    ->where('ate_codigo', $ate_codigo)
                    ->delete();

            } elseif ($seccion === 'ES') {
                $areaAtencion = $db->table('t_area_atencion')
                    ->where('ate_codigo', $ate_codigo)
                    ->get()
                    ->getRowArray();

                if ($areaAtencion) {
                    // Cambiar estado a EN_ATENCION para que aparezca en lista de especialistas
                    $db->table('t_area_atencion')
                        ->where('ate_codigo', $ate_codigo)
                        ->update([
                            'are_estado' => 'EN_ATENCION',
                            'are_medico_asignado' => $areaAtencion['are_medico_asignado'],
                            'are_fecha_inicio_atencion' => $areaAtencion['are_fecha_inicio_atencion'],
                            'are_hora_inicio_atencion' => $areaAtencion['are_hora_inicio_atencion']
                        ]);
                }
            }

        } catch (\Exception $e) {
            // Continuar si hay error
        }
    }
    /**
     * Deshabilitar modificación después de usarla
     * Este método se llama DESPUÉS de que el médico guarde los cambios
     */
    private function deshabilitarDespuesDeUsar($ate_codigo, $seccion)
    {
        $db = \Config\Database::connect();

        try {
            if ($seccion === 'ME') {
                $db->table('t_formulario_usuario')
                    ->where('ate_codigo', $ate_codigo)
                    ->where('seccion', 'ME')
                    ->update([
                        'habilitado_por_admin' => 0,
                        'fecha_habilitacion' => null,
                        'admin_que_habilito' => null,
                        'motivo_habilitacion' => 'Modificación utilizada - habilitación desactivada'
                    ]);

                // Actualizar estado de atención
                $fields = $db->getFieldNames('t_atencion');
                if (in_array('estado', $fields)) {
                    $db->table('t_atencion')
                        ->where('ate_codigo', $ate_codigo)
                        ->update([
                            'estado' => 'Modificación completada',
                            'fecha_ultimo_cambio' => date('Y-m-d H:i:s')
                        ]);
                }

            } elseif ($seccion === 'ES') {
                $db->table('t_formulario_usuario')
                    ->where('ate_codigo', $ate_codigo)
                    ->where('seccion', 'ES')
                    ->update([
                        'habilitado_por_admin' => 0,
                        'fecha_habilitacion' => null,
                        'admin_que_habilito' => null,
                        'motivo_habilitacion' => 'Modificación utilizada - habilitación desactivada'
                    ]);

                // Cambiar estado del área de atención a COMPLETADA
                $areaAtencion = $db->table('t_area_atencion')
                    ->where('ate_codigo', $ate_codigo)
                    ->get()
                    ->getRowArray();

                if ($areaAtencion) {
                    $db->table('t_area_atencion')
                        ->where('ate_codigo', $ate_codigo)
                        ->update([
                            'are_estado' => 'COMPLETADA',
                            'are_fecha_fin_atencion' => date('Y-m-d'),
                            'are_hora_fin_atencion' => date('H:i:s')
                        ]);
                }
            }

        } catch (\Exception $e) {
            // Continuar si hay error
        }
    }
    /**
     * Verificar si un médico puede acceder/modificar un formulario
     * Esta función debe ser llamada ANTES de mostrar el formulario
     */
    public function verificarAccesoMedico($ate_codigo, $usu_id, $seccion)
    {
        $db = \Config\Database::connect();

        // Buscar el registro del formulario
        $formulario = $db->table('t_formulario_usuario')
            ->where('ate_codigo', $ate_codigo)
            ->where('seccion', $seccion)
            ->get()
            ->getRowArray();

        if (!$formulario) {
            return [
                'acceso' => true,
                'motivo' => 'Primera vez - acceso permitido',
                'es_primera_vez' => true,
                'puede_guardar' => true,
                'oportunidades_restantes' => 3,
                'oportunidades_usadas' => 0,
                'oportunidades_permitidas' => 3
            ];
        }

        // Calcular oportunidades
        $modificacionesUsadas = (int) $formulario['modificaciones_usadas'];
        $modificacionesPermitidas = (int) $formulario['modificaciones_permitidas'];
        $oportunidadesRestantes = max(0, $modificacionesPermitidas - $modificacionesUsadas);

        // Verificar PRIMERO si está habilitado por admin
        if ($formulario['habilitado_por_admin'] == 1) {
            return [
                'acceso' => true,
                'motivo' => 'Modificación habilitada por administrador',
                'es_modificacion' => true,
                'puede_guardar' => true,
                'admin_que_habilito' => $formulario['admin_que_habilito'],
                'fecha_habilitacion' => $formulario['fecha_habilitacion'],
                'motivo_habilitacion' => $formulario['motivo_habilitacion'],
                'oportunidades_usadas' => $modificacionesUsadas,
                'oportunidades_permitidas' => $modificacionesPermitidas,
                'oportunidades_restantes' => $oportunidadesRestantes
            ];
        }

        // Si es del mismo usuario que lo completó originalmente
        if ($formulario['usu_id'] == $usu_id) {
            if ($oportunidadesRestantes > 0) {
                return [
                    'acceso' => true,
                    'motivo' => "Puede modificar - {$oportunidadesRestantes} oportunidades restantes",
                    'es_modificacion_directa' => true,
                    'puede_guardar' => true,
                    'oportunidades_usadas' => $modificacionesUsadas,
                    'oportunidades_permitidas' => $modificacionesPermitidas,
                    'oportunidades_restantes' => $oportunidadesRestantes
                ];
            } else {
                return [
                    'acceso' => true,
                    'motivo' => 'Formulario completado - sin modificaciones restantes (solo lectura)',
                    'es_solo_lectura' => true,
                    'puede_guardar' => false,
                    'oportunidades_usadas' => $modificacionesUsadas,
                    'oportunidades_permitidas' => $modificacionesPermitidas,
                    'oportunidades_restantes' => 0
                ];
            }
        } else {
            // Formulario completado por otro médico
            return [
                'acceso' => false,
                'motivo' => 'Esta atención ya ha sido completada por otro médico.',
                'puede_guardar' => false,
                'medico_original' => $formulario['usu_id'],
                'oportunidades_usadas' => $modificacionesUsadas,
                'oportunidades_permitidas' => $modificacionesPermitidas,
                'oportunidades_restantes' => $oportunidadesRestantes
            ];
        }
    }


    /**
     * Registrar que se usó una modificación
     */
    public function registrarModificacionUsada($ate_codigo, $seccion)
    {
        $db = \Config\Database::connect();

        // Corrección: Usar 'ES' no 'ESP' para especialistas
        $seccionCorrecta = $seccion === 'ESP' ? 'ES' : $seccion;

        $formulario = $db->table('t_formulario_usuario')
            ->where('ate_codigo', $ate_codigo)
            ->where('seccion', $seccionCorrecta)
            ->get()
            ->getRowArray();

        if ($formulario) {
            $modificacionesUsadas = (int) $formulario['modificaciones_usadas'];
            $modificacionesPermitidas = (int) $formulario['modificaciones_permitidas'];
            $nuevasUsadas = $modificacionesUsadas + 1;

            // Actualizar modificaciones usadas
            $updateData = [
                'modificaciones_usadas' => $nuevasUsadas
            ];

            // SIEMPRE deshabilitar después de usar una modificación habilitada por admin
            if ($formulario['habilitado_por_admin'] == 1) {
                $updateData['habilitado_por_admin'] = 0;
                $updateData['fecha_habilitacion'] = null;
                $updateData['admin_que_habilito'] = null;
                $updateData['motivo_habilitacion'] = 'Modificación utilizada - habilitación desactivada';
            }

            $updateResult = $db->table('t_formulario_usuario')
                ->where('ate_codigo', $ate_codigo)
                ->where('seccion', $seccionCorrecta)
                ->update($updateData);

            // Llamar al método para quitar de listas después de usar
            if ($formulario['habilitado_por_admin'] == 1) {
                $this->deshabilitarDespuesDeUsar($ate_codigo, $seccionCorrecta);
            }

            // Registrar en historial
            $oportunidadesRestantes = max(0, $modificacionesPermitidas - $nuevasUsadas);
            $motivoHistorial = "Modificación {$nuevasUsadas} de {$modificacionesPermitidas} utilizada. Restantes: {$oportunidadesRestantes}";

            if ($formulario['habilitado_por_admin'] == 1) {
                $motivoHistorial .= " - Habilitación por admin DESACTIVADA";
            }

            $this->registrarEnHistorial($ate_codigo, $formulario['usu_id'], $seccionCorrecta, 'MODIFICADO', $motivoHistorial);

            return $updateResult;
        }

        return false;
    }
    /**
     * Usar modificación directa (cuando el mismo médico modifica sin habilitación admin)
     */
    public function usarModificacionDirecta($ate_codigo, $seccion, $usu_id)
    {
        $db = \Config\Database::connect();
        $seccionCorrecta = $seccion === 'ESP' ? 'ES' : $seccion;

        $formulario = $db->table('t_formulario_usuario')
            ->where('ate_codigo', $ate_codigo)
            ->where('seccion', $seccionCorrecta)
            ->where('usu_id', $usu_id)
            ->get()
            ->getRowArray();

        if ($formulario) {
            $modificacionesUsadas = (int) $formulario['modificaciones_usadas'];
            $modificacionesPermitidas = (int) $formulario['modificaciones_permitidas'];
            $nuevasUsadas = $modificacionesUsadas + 1;

            if ($nuevasUsadas <= $modificacionesPermitidas) {
                $updateResult = $db->table('t_formulario_usuario')
                    ->where('ate_codigo', $ate_codigo)
                    ->where('seccion', $seccionCorrecta)
                    ->where('usu_id', $usu_id)
                    ->update([
                        'modificaciones_usadas' => $nuevasUsadas
                    ]);

                $oportunidadesRestantes = $modificacionesPermitidas - $nuevasUsadas;
                $motivoHistorial = "Modificación directa {$nuevasUsadas} de {$modificacionesPermitidas}. Restantes: {$oportunidadesRestantes}";

                $this->registrarEnHistorial($ate_codigo, $usu_id, $seccionCorrecta, 'MODIFICACION_DIRECTA', $motivoHistorial);

                return $updateResult;
            }
        }

        return false;
    }
    /**
     * Crear registro inicial del formulario con 3 oportunidades
     */
    public function crearRegistroFormulario($ate_codigo, $usu_id, $seccion, $observaciones = '')
    {
        try {
            $data = [
                'ate_codigo' => $ate_codigo,
                'usu_id' => $usu_id,
                'seccion' => $seccion,
                'fecha' => date('Y-m-d H:i:s'),
                'observaciones' => $observaciones,
                'modificaciones_permitidas' => 3,
                'modificaciones_usadas' => 0,
                'habilitado_por_admin' => 0
            ];

            $resultado = $this->insert($data);

            if (!$resultado) {
                $error = $this->db->error();
                throw new \Exception("Error en base de datos: " . json_encode($error));
            }

            return $resultado;

        } catch (\Exception $e) {
            throw $e;
        }
    }
    /**
     * Obtener historial de modificaciones
     */
    public function obtenerHistorialModificaciones($ate_codigo)
    {
        $db = \Config\Database::connect();

        // Verificar si la tabla existe antes de usarla
        if (!$this->tablaHistorialExiste()) {
            return []; // Retornar array vacío si la tabla no existe
        }

        return $db->query("
            SELECT 
                hm.*,
                u.usu_nombre,
                u.usu_apellido,
                admin.usu_nombre as admin_nombre,
                admin.usu_apellido as admin_apellido
            FROM t_historial_modificaciones hm
            JOIN t_usuario u ON hm.usu_id = u.usu_id
            LEFT JOIN t_usuario admin ON hm.admin_responsable = admin.usu_id
            WHERE hm.ate_codigo = ?
            ORDER BY hm.fecha_accion DESC
        ", [$ate_codigo])->getResultArray();
    }

    /**
     * Obtener estadísticas para el dashboard de modificaciones
     */
    public function obtenerEstadisticasModificaciones()
    {
        $db = \Config\Database::connect();

        $stats = [];

        // Total formularios completados (ME y ES)
        $stats['total_formularios'] = $db->table('t_formulario_usuario')
            ->whereIn('seccion', ['ME', 'ES'])
            ->countAllResults();

        // Pueden modificar (habilitados por admin O con modificaciones restantes)
        $puedenModificar = $db->query("
            SELECT COUNT(*) as total
            FROM t_formulario_usuario
            WHERE seccion IN ('ME', 'ES')
            AND (
                habilitado_por_admin = 1
                OR modificaciones_usadas < modificaciones_permitidas
            )
        ")->getRow();
        $stats['pueden_modificar'] = $puedenModificar ? $puedenModificar->total : 0;

        // Bloqueados (límite alcanzado y no habilitados por admin)
        $bloqueados = $db->query("
            SELECT COUNT(*) as total
            FROM t_formulario_usuario
            WHERE seccion IN ('ME', 'ES')
            AND modificaciones_usadas >= modificaciones_permitidas
            AND habilitado_por_admin = 0
        ")->getRow();
        $stats['bloqueados'] = $bloqueados ? $bloqueados->total : 0;

        // Modificaciones realizadas hoy
        $modificacionesHoy = $db->query("
            SELECT COUNT(*) as total
            FROM t_formulario_usuario
            WHERE seccion IN ('ME', 'ES')
            AND DATE(fecha_habilitacion) = CURDATE()
        ")->getRow();
        $stats['modificaciones_hoy'] = $modificacionesHoy ? $modificacionesHoy->total : 0;

        return $stats;
    }

    /**
     * Obtener formulario específico para verificaciones
     */
    public function obtenerFormulario($ate_codigo, $seccion)
    {
        $db = \Config\Database::connect();

        return $db->table('t_formulario_usuario')
            ->where('ate_codigo', $ate_codigo)
            ->where('seccion', $seccion)
            ->get()
            ->getRowArray();
    }

    /**
     * Verificar si puede ser modificado
     */

    public function puedeSerModificado($ate_codigo, $seccion)
    {
        $db = \Config\Database::connect();

        $formulario = $db->table('t_formulario_usuario')
            ->where('ate_codigo', $ate_codigo)
            ->where('seccion', $seccion)
            ->get()
            ->getRowArray();

        if (!$formulario) {
            return [
                'puede_modificar' => true,
                'motivo' => 'Primera vez - puede completar formulario'
            ];
        }

        // Verificar PRIMERO si está habilitado por admin
        if ($formulario['habilitado_por_admin'] == 1) {
            return [
                'puede_modificar' => true,
                'motivo' => 'Habilitado por administrador para modificación'
            ];
        }

        // Solo después verificar si ya fue completado
        if ($formulario['modificaciones_usadas'] >= $formulario['modificaciones_permitidas']) {
            return [
                'puede_modificar' => false,
                'motivo' => 'Límite de modificaciones alcanzado'
            ];
        }

        // Si existe pero no está habilitado
        return [
            'puede_modificar' => false,
            'motivo' => 'Formulario completado - contacte al administrador'
        ];
    }

    /**
     * Verificar si la tabla de historial existe
     */
    private function tablaHistorialExiste()
    {
        $db = \Config\Database::connect();

        try {
            $query = $db->query("SHOW TABLES LIKE 't_historial_modificaciones'");
            return $query->getNumRows() > 0;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Registrar en historial de modificaciones
     */
    private function registrarEnHistorial($ate_codigo, $usu_id, $seccion, $accion, $motivo = null, $admin_responsable = null)
    {
        $db = \Config\Database::connect();

        try {
            // Verificar si la tabla existe antes de insertar
            if (!$this->tablaHistorialExiste()) {
                return;
            }

            $data = [
                'ate_codigo' => $ate_codigo,
                'usu_id' => $usu_id,
                'seccion' => $seccion,
                'accion' => $accion,
                'motivo' => $motivo,
                'admin_responsable' => $admin_responsable,
                'fecha_accion' => date('Y-m-d H:i:s')
            ];

            $db->table('t_historial_modificaciones')->insert($data);

        } catch (\Exception $e) {
            // No lanzar excepción para no afectar el flujo principal
        }
    }
}