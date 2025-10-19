<?php

namespace App\Controllers\Administrador;

use App\Controllers\BaseController;
use App\Models\Administrador\UsuarioModel;
use App\Models\PacienteModel;
use DateTime;
use Exception;

class HistorialController extends BaseController
{
    protected $usuarioModel;

    public function __construct()
    {
        $this->usuarioModel = new UsuarioModel();
    }

    public function index()
    {
        return view('administrador/ListaHistorial');
    }

    /**
     * Solo mostrar pacientes con formulario médico completo
     * Implementación para DataTables server-side processing
     */
    public function ajaxPacientes()
    {
        $request = service('request');

        // Verificar permisos de administrador (rol_id = 1)
        if (!session()->get('logged_in') || session()->get('rol_id') != 1) {
            return $this->response->setJSON([
                "draw" => intval($request->getGet('draw') ?? 0),
                "recordsTotal" => 0,
                "recordsFiltered" => 0,
                "data" => [],
                "error" => "No autorizado"
            ])->setStatusCode(401);
        }

        // Configuración de columnas para DataTables
        $columns = [
            'pac_his_cli',
            'pac_cedula',
            'pac_apellidos',
            'pac_nombres',
            'ultima_atencion',
            null // columna de acciones (no ordenable)
        ];

        // Parámetros de DataTables
        $start = intval($request->getGet('start') ?? 0);
        $length = intval($request->getGet('length') ?? 25);
        $orderColumnIndex = intval($request->getGet('order')[0]['column'] ?? 4);
        $orderColumn = $columns[$orderColumnIndex] ?? 'ultima_atencion';
        $orderDir = $request->getGet('order')[0]['dir'] ?? 'desc';
        $search = $request->getGet('search')['value'] ?? '';

        try {
            $db = \Config\Database::connect();

            // CONSULTA PRINCIPAL: Solo pacientes con formulario médico completo
            $baseSql = "
                SELECT DISTINCT 
                    p.pac_codigo,
                    p.pac_his_cli,
                    p.pac_cedula,
                    p.pac_apellidos,
                    p.pac_nombres,
                    MAX(a.ate_fecha) as ultima_atencion
                FROM t_paciente p
                INNER JOIN t_atencion a ON p.pac_codigo = a.pac_codigo
                INNER JOIN t_formulario_usuario fu ON a.ate_codigo = fu.ate_codigo
                WHERE (fu.seccion = 'ME' OR fu.seccion = 'ES')
                  AND p.pac_his_cli IS NOT NULL
            ";

            // Agregar filtro de búsqueda si existe
            $whereConditions = [];
            $params = [];

            if (!empty($search)) {
                $whereConditions[] = "(
                    p.pac_cedula LIKE ? OR 
                    p.pac_apellidos LIKE ? OR 
                    p.pac_nombres LIKE ? OR
                    p.pac_his_cli LIKE ?
                )";
                $searchParam = "%{$search}%";
                $params = array_fill(0, 4, $searchParam);
            }

            if (!empty($whereConditions)) {
                $baseSql .= " AND " . implode(" AND ", $whereConditions);
            }

            $baseSql .= " GROUP BY p.pac_codigo, p.pac_his_cli, p.pac_cedula, p.pac_apellidos, p.pac_nombres";

            // CONTAR TOTAL FILTRADO
            $countSql = "SELECT COUNT(*) as total FROM ({$baseSql}) as subquery";
            $totalFilteredResult = $db->query($countSql, $params);
            $totalFiltered = $totalFilteredResult->getRow()->total ?? 0;

            // APLICAR ORDENACIÓN Y PAGINACIÓN
            // Validar columna de ordenación para evitar SQL injection
            $validOrderColumns = ['pac_his_cli', 'pac_cedula', 'pac_apellidos', 'pac_nombres', 'ultima_atencion'];
            if (!in_array($orderColumn, $validOrderColumns)) {
                $orderColumn = 'ultima_atencion';
            }

            $orderDir = ($orderDir === 'asc') ? 'asc' : 'desc';

            $finalSql = $baseSql . " ORDER BY {$orderColumn} {$orderDir} LIMIT {$length} OFFSET {$start}";

            $result = $db->query($finalSql, $params);
            $data = $result->getResultArray();

            // CONTAR TOTAL SIN FILTROS (solo pacientes con formulario completo)
            $totalSql = "
                SELECT COUNT(DISTINCT p.pac_codigo) as total
                FROM t_paciente p
                INNER JOIN t_atencion a ON p.pac_codigo = a.pac_codigo
                INNER JOIN t_formulario_usuario fu ON a.ate_codigo = fu.ate_codigo
                WHERE (fu.seccion = 'ME' OR fu.seccion = 'ES')
                  AND p.pac_his_cli IS NOT NULL
            ";
            $totalResult = $db->query($totalSql);
            $totalData = $totalResult->getRow()->total ?? 0;

            // Formatear datos para DataTables
            $formattedData = [];
            foreach ($data as $row) {
                // FECHA CORRECTAMENTE PARA EVITAR PROBLEMAS DE TIMEZONE
                $ultimaAtencion = $row['ultima_atencion'] ?? '';
                if (!empty($ultimaAtencion)) {
                    try {
                        // Asegurar formato YYYY-MM-DD consistente
                        $fecha = new DateTime($ultimaAtencion);
                        $ultimaAtencion = $fecha->format('Y-m-d');
                    } catch (Exception $e) {
                        // Si no se puede parsear, mantener el valor original
                    }
                }

                $formattedData[] = [
                    'pac_his_cli' => $row['pac_his_cli'] ?? '',
                    'pac_cedula' => $row['pac_cedula'] ?? '',
                    'pac_apellidos' => $row['pac_apellidos'] ?? '',
                    'pac_nombres' => $row['pac_nombres'] ?? '',
                    'ultima_atencion' => $ultimaAtencion,
                    'pac_codigo' => $row['pac_codigo'] ?? ''
                ];
            }

            return $this->response->setJSON([
                "draw" => intval($request->getGet('draw') ?? 0),
                "recordsTotal" => intval($totalData),
                "recordsFiltered" => intval($totalFiltered),
                "data" => $formattedData
            ]);

        } catch (Exception $e) {
            return $this->response->setJSON([
                "draw" => intval($request->getGet('draw') ?? 0),
                "recordsTotal" => 0,
                "recordsFiltered" => 0,
                "data" => [],
                "error" => "Error interno del servidor: " . $e->getMessage()
            ])->setStatusCode(500);
        }
    }

    /**
     * Método adicional para obtener detalles de un paciente específico
     */
    public function obtenerDetallePaciente($pac_codigo)
    {
        if (!session()->get('logged_in') || session()->get('rol_id') != 1) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'No autorizado'
            ])->setStatusCode(401);
        }

        try {
            $db = \Config\Database::connect();

            $sql = "
                SELECT 
                    p.*,
                    a.ate_fecha,
                    a.ate_hora,
                    COUNT(fu.seccion) as secciones_completadas
                FROM t_paciente p
                LEFT JOIN t_atencion a ON p.pac_codigo = a.pac_codigo
                LEFT JOIN t_formulario_usuario fu ON a.ate_codigo = fu.ate_codigo
                WHERE p.pac_codigo = ?
                GROUP BY p.pac_codigo, a.ate_codigo
                ORDER BY a.ate_fecha DESC
                LIMIT 1
            ";

            $result = $db->query($sql, [$pac_codigo]);
            $paciente = $result->getRowArray();

            if (!$paciente) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Paciente no encontrado'
                ]);
            }

            return $this->response->setJSON([
                'success' => true,
                'data' => $paciente
            ]);

        } catch (Exception $e) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Error interno del servidor'
            ])->setStatusCode(500);
        }
    }
}