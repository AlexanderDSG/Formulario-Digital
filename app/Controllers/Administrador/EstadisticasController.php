<?php

namespace App\Controllers\Administrador;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;

class EstadisticasController extends BaseController
{
    protected $db;
    protected $atencionModel;

    public function __construct()
    {
        $this->db = \Config\Database::connect();
        $this->atencionModel = new \App\Models\AtencionModel();
    }

    /**
     * Obtener estadísticas de atenciones para el dashboard
     */
    public function obtenerEstadisticas()
    {
        try {
            $hoy = date('Y-m-d');
            $hace7Dias = date('Y-m-d', strtotime('-6 days'));
            $inicioMes = date('Y-m-01');

            // Atenciones de hoy
            $atencionesHoy = $this->db->table('t_atencion')
                ->where('DATE(ate_fecha)', $hoy)
                ->countAllResults();

            // Atenciones de la semana (últimos 7 días)
            $atencionesSemana = $this->db->table('t_atencion')
                ->where('ate_fecha >=', $hace7Dias)
                ->where('ate_fecha <=', $hoy)
                ->countAllResults();

            // Atenciones del mes
            $atencionesMes = $this->db->table('t_atencion')
                ->where('DATE(ate_fecha) >=', $inicioMes)
                ->where('DATE(ate_fecha) <=', $hoy)
                ->countAllResults();

            return $this->response->setJSON([
                'success' => true,
                'data' => [
                    'hoy' => $atencionesHoy,
                    'semana' => $atencionesSemana,
                    'mes' => $atencionesMes,
                    'fecha_hoy' => $hoy,
                    'mes_nombre' => $this->obtenerNombreMes(date('n'))
                ]
            ]);

        } catch (\Exception $e) {
            return $this->response->setJSON([
                'success' => false,
                'error' => 'Error al obtener estadísticas: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Obtener datos para el gráfico de tendencias (últimos 7 días)
     */
    public function obtenerTendencias()
    {
        try {
            $datos = [];
            $labels = [];

            for ($i = 6; $i >= 0; $i--) {
                $fecha = date('Y-m-d', strtotime("-$i days"));
                $count = $this->db->table('t_atencion')
                    ->where('DATE(ate_fecha)', $fecha)
                    ->countAllResults();

                $labels[] = $this->formatearFechaCorta($fecha);
                $datos[] = $count;
            }

            return $this->response->setJSON([
                'success' => true,
                'labels' => $labels,
                'data' => $datos
            ]);

        } catch (\Exception $e) {
            return $this->response->setJSON([
                'success' => false,
                'error' => 'Error al obtener tendencias: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Obtener datos para el gráfico de atenciones por hora del día
     */
    public function obtenerAtencionesPorHora()
    {
        try {
            $hoy = date('Y-m-d');
            $datos = array_fill(0, 24, 0); // Array de 24 horas inicializado en 0

            $query = $this->db->query("
                SELECT
                    HOUR(ate_hora) as hora,
                    COUNT(*) as total
                FROM t_atencion
                WHERE DATE(ate_fecha) = ?
                GROUP BY HOUR(ate_hora)
                ORDER BY hora
            ", [$hoy]);

            $resultados = $query->getResultArray();

            foreach ($resultados as $row) {
                $hora = (int)$row['hora'];
                $datos[$hora] = (int)$row['total'];
            }

            return $this->response->setJSON([
                'success' => true,
                'labels' => $this->generarLabelsHoras(),
                'data' => $datos
            ]);

        } catch (\Exception $e) {
            return $this->response->setJSON([
                'success' => false,
                'error' => 'Error al obtener atenciones por hora: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Generar labels para las 24 horas
     */
    private function generarLabelsHoras()
    {
        $labels = [];
        for ($i = 0; $i < 24; $i++) {
            $labels[] = sprintf('%02d:00', $i);
        }
        return $labels;
    }

    /**
     * Formatear fecha corta (ejemplo: "Lun 15")
     */
    private function formatearFechaCorta($fecha)
    {
        $timestamp = strtotime($fecha);
        $dias = ['Dom', 'Lun', 'Mar', 'Mié', 'Jue', 'Vie', 'Sáb'];
        $diaSemana = $dias[date('w', $timestamp)];
        $dia = date('d', $timestamp);
        return "$diaSemana $dia";
    }

    /**
     * Obtener nombre del mes en español
     */
    private function obtenerNombreMes($numeroMes)
    {
        $meses = [
            1 => 'Enero', 2 => 'Febrero', 3 => 'Marzo', 4 => 'Abril',
            5 => 'Mayo', 6 => 'Junio', 7 => 'Julio', 8 => 'Agosto',
            9 => 'Septiembre', 10 => 'Octubre', 11 => 'Noviembre', 12 => 'Diciembre'
        ];
        return $meses[$numeroMes] ?? '';
    }
}
