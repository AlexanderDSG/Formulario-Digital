<?php
/**
 * Modal de Evolución y Prescripciones - Médicos
 * Replica los campos exactos de los formularios SNS-MSP / HCU-form.005/2021
 */

namespace App\Controllers\Medicos;

use App\Controllers\BaseController;
use App\Models\Admision\GuardarSecciones\EstablecimientoModel;
use App\Models\Admision\EstablecimientoRegistroModel;
use App\Models\PacienteModel;
use App\Models\Especialidades\EvolucionPrescripcionesModel;

class ModalEvolucionPrescripcionesController extends BaseController
{
    protected $evolucionModel;

    public function __construct()
    {
        $this->evolucionModel = new EvolucionPrescripcionesModel();
    }

    public function modalEvolucionPrescripciones()
    {
        if (!session()->get('logged_in') || session()->get('rol_id') != 4) {
            return $this->response->setJSON(['error' => 'No autorizado']);
        }

        try {
            // Obtener datos enviados por AJAX
            $input = json_decode(file_get_contents('php://input'), true);


            // Extraer y limpiar datos
            $ate_codigo = $input['ate_codigo'] ?? '';
            $pac_datos = $input['pac_datos'] ?? [];

            // Convertir a string si es necesario
            $ate_codigo = (string)$ate_codigo;


            // Validar datos mínimos - ate_codigo es necesario
            if (empty($ate_codigo)) {
                return $this->response->setJSON(['error' => 'Código de atención requerido']);
            }

            // Obtener datos completos del paciente desde la base de datos
            $pacienteData = $this->obtenerDatosPacientePorAtencion($ate_codigo);

            // Obtener datos del establecimiento
            $establecimientoModel = new EstablecimientoModel();
            $establecimiento = $establecimientoModel->obtenerEstablecimientoActual();

            // Obtener número de archivo
            $establecimientoRegistroModel = new EstablecimientoRegistroModel();
            $registroAdmision = $establecimientoRegistroModel->where('ate_codigo', $ate_codigo)->first();

            // Procesar datos del paciente de forma segura
            // Primero intentar usar los datos desde BD, luego desde pac_datos
            $primer_apellido = $pacienteData['primer_apellido'] ?? (is_array($pac_datos['primer_apellido'] ?? '') ? '' : ($pac_datos['primer_apellido'] ?? ''));
            $segundo_apellido = $pacienteData['segundo_apellido'] ?? (is_array($pac_datos['segundo_apellido'] ?? '') ? '' : ($pac_datos['segundo_apellido'] ?? ''));
            $primer_nombre = $pacienteData['primer_nombre'] ?? (is_array($pac_datos['primer_nombre'] ?? '') ? '' : ($pac_datos['primer_nombre'] ?? ''));
            $segundo_nombre = $pacienteData['segundo_nombre'] ?? (is_array($pac_datos['segundo_nombre'] ?? '') ? '' : ($pac_datos['segundo_nombre'] ?? ''));
            $edad = $pacienteData['edad'] ?? (is_array($pac_datos['edad'] ?? '') ? '' : ($pac_datos['edad'] ?? ''));
            $cedula = $pacienteData['cedula'] ?? (is_array($pac_datos['cedula'] ?? '') ? '' : ($pac_datos['cedula'] ?? ''));
            $sexo = $pacienteData['sexo'] ?? (is_array($pac_datos['sexo'] ?? '') ? '' : ($pac_datos['sexo'] ?? ''));

            // Obtener condición de edad directamente desde BD (ya viene de admisión)
            $condicion_edad = $pacienteData['condicion_edad'] ?? 'A';

            // Datos del establecimiento
            $institucion = $establecimiento['est_institucion'] ?? '';
            $unicodigo = $establecimiento['est_unicodigo'] ?? '';
            $nombre_establecimiento = $establecimiento['est_nombre_establecimiento'] ?? '';
            $numero_archivo = $registroAdmision['est_num_archivo'] ?? '00000';

            // Cargar datos existentes usando el modelo
            $evolucion_prescripciones_existentes = [];
            if (!empty($ate_codigo)) {
                $evolucion_prescripciones_existentes = $this->evolucionModel->obtenerPorAtencion($ate_codigo);
            }

            // Obtener o generar número de hoja para esta atención
            $numero_hoja = $this->evolucionModel->obtenerNumeroHojaParaAtencion($ate_codigo);

            $data = [
                'ate_codigo' => $ate_codigo,
                'primer_apellido' => $primer_apellido,
                'segundo_apellido' => $segundo_apellido,
                'primer_nombre' => $primer_nombre,
                'segundo_nombre' => $segundo_nombre,
                'edad' => $edad,
                'cedula' => $cedula,
                'sexo' => $sexo,
                'condicion_edad' => $condicion_edad,
                'institucion' => $institucion,
                'unicodigo' => $unicodigo,
                'nombre_establecimiento' => $nombre_establecimiento,
                'numero_archivo' => $numero_archivo,
                'numero_hoja' => $numero_hoja,
                'evolucion_prescripciones_existentes' => $evolucion_prescripciones_existentes
            ];

            return view('medicos/modal_evolucion_prescripciones', $data);

        } catch (\Exception $e) {
            return $this->response->setJSON(['error' => 'Error interno: ' . $e->getMessage()]);
        }
    }

    /**
     * Obtener datos completos del paciente por código de atención
     */
    private function obtenerDatosPacientePorAtencion($ate_codigo)
    {
        try {
            $db = \Config\Database::connect();

            // Obtener datos del paciente desde las tablas correctas según tu BD
            $query = $db->table('t_atencion a')
                ->select('p.pac_nombres, p.pac_apellidos, p.pac_cedula, p.pac_edad_valor, p.pac_edad_unidad, p.gen_codigo, g.gen_descripcion')
                ->join('t_paciente p', 'a.pac_codigo = p.pac_codigo', 'left')
                ->join('t_genero g', 'p.gen_codigo = g.gen_codigo', 'left')
                ->where('a.ate_codigo', $ate_codigo)
                ->get();

            $resultado = $query->getRowArray();

            if ($resultado) {

                // Procesar nombres y apellidos según el formato de tu formulario
                $nombres = explode(' ', trim($resultado['pac_nombres'] ?? ''), 2);
                $apellidos = explode(' ', trim($resultado['pac_apellidos'] ?? ''), 2);

                // Obtener sexo desde la descripción del género
                $sexo = '';
                $genDescripcion = strtoupper($resultado['gen_descripcion'] ?? '');
                if (strpos($genDescripcion, 'MASCULINO') !== false || strpos($genDescripcion, 'HOMBRE') !== false) {
                    $sexo = 'M';
                } elseif (strpos($genDescripcion, 'FEMENINO') !== false || strpos($genDescripcion, 'MUJER') !== false) {
                    $sexo = 'F';
                }

                // Obtener condición de edad desde pac_edad_unidad
                $condicion_edad = $resultado['pac_edad_unidad'] ?? 'A';
                $edad = $resultado['pac_edad_valor'] ?? '';

                return [
                    'primer_nombre' => $nombres[0] ?? '',
                    'segundo_nombre' => $nombres[1] ?? '',
                    'primer_apellido' => $apellidos[0] ?? '',
                    'segundo_apellido' => $apellidos[1] ?? '',
                    'cedula' => $resultado['pac_cedula'] ?? '',
                    'edad' => $edad,
                    'sexo' => $sexo,
                    'condicion_edad' => $condicion_edad
                ];
            }

            return [];

        } catch (\Exception $e) {
            return [];
        }
    }

    /**
     * Obtener evoluciones con información del usuario que las registró
     */
    public function obtenerEvolucionesConUsuario()
    {
        if (!session()->get('logged_in') || session()->get('rol_id') != 4) {
            return $this->response->setJSON(['success' => false, 'error' => 'No autorizado']);
        }

        try {
            $datos = json_decode($this->request->getBody(), true);
            $ate_codigo = $datos['ate_codigo'] ?? null;

            if (!$ate_codigo) {
                return $this->response->setJSON(['success' => false, 'error' => 'Código de atención requerido']);
            }

            $evolucionModel = new EvolucionPrescripcionesModel();
            $evoluciones = $evolucionModel->obtenerPorAtencion($ate_codigo);

            return $this->response->setJSON([
                'success' => true,
                'data' => $evoluciones
            ]);

        } catch (\Exception $e) {
            return $this->response->setJSON(['success' => false, 'error' => $e->getMessage()]);
        }
    }

    /**
     * Guardar evolución y prescripciones
     */
    public function guardarEvolucionPrescripciones()
    {
        if (!session()->get('logged_in') || session()->get('rol_id') != 4) {
            return $this->response->setJSON(['success' => false, 'error' => 'No autorizado']);
        }

        try {
            $datos = json_decode($this->request->getBody(), true);
            $ate_codigo = $datos['ate_codigo'] ?? null;
            $filas = $datos['filas'] ?? [];

            if (!$ate_codigo || empty($filas)) {
                return $this->response->setJSON(['success' => false, 'error' => 'Datos insuficientes']);
            }

            // Usar el modelo de evoluciones
            $evolucionModel = new EvolucionPrescripcionesModel();

            $registrosGuardados = 0;
            foreach ($filas as $fila) {
                // Usar el número de hoja que viene en los datos, o generar uno si no viene
                $numero_hoja = $fila['ep_numero_hoja'] ?? $evolucionModel->obtenerNumeroHojaParaAtencion($ate_codigo);

                $datos_evolucion = [
                    'ate_codigo' => $ate_codigo,
                    'ep_fecha' => $fila['ep_fecha'],
                    'ep_hora' => $fila['ep_hora'],
                    'ep_notas_evolucion' => $fila['ep_notas_evolucion'],
                    'ep_farmacoterapia' => $fila['ep_farmacoterapia'],
                    'ep_administrado' => $fila['ep_administrado'],
                    'ep_orden' => $fila['ep_orden'],
                    'ep_numero_hoja' => $numero_hoja
                ];

                if (isset($fila['ep_codigo']) && !empty($fila['ep_codigo'])) {
                    // Actualizar registro existente
                    $evolucionModel->update($fila['ep_codigo'], $datos_evolucion);
                } else {
                    // Crear nuevo registro
                    $evolucionModel->insert($datos_evolucion);
                }
                $registrosGuardados++;
            }

            return $this->response->setJSON([
                'success' => true,
                'message' => "Se guardaron {$registrosGuardados} registros de evolución",
                'registros_guardados' => $registrosGuardados
            ]);

        } catch (\Exception $e) {
            return $this->response->setJSON(['success' => false, 'error' => $e->getMessage()]);
        }
    }

    /**
     * Contar registros de evolución y prescripciones
     */
    public function contarEvolucionPrescripciones()
    {
        if (!session()->get('logged_in') || session()->get('rol_id') != 4) {
            return $this->response->setJSON(['success' => false, 'error' => 'No autorizado']);
        }

        try {
            $datos = json_decode($this->request->getBody(), true);
            $ate_codigo = $datos['ate_codigo'] ?? null;

            if (!$ate_codigo) {
                return $this->response->setJSON(['success' => false, 'error' => 'Código de atención requerido']);
            }

            $evolucionModel = new \App\Models\Especialidades\EvolucionPrescripcionesModel();
            $count = $evolucionModel->contarPorAtencion($ate_codigo);

            return $this->response->setJSON([
                'success' => true,
                'count' => $count
            ]);

        } catch (\Exception $e) {
            return $this->response->setJSON(['success' => false, 'error' => $e->getMessage()]);
        }
    }

    /**
     * Obtener registros de evolución y prescripciones
     */
    public function obtenerEvolucionPrescripciones($ate_codigo)
    {
        if (!session()->get('logged_in') || session()->get('rol_id') != 4) {
            return $this->response->setJSON(['success' => false, 'error' => 'No autorizado']);
        }

        try {
            $resultados = $this->evolucionModel->obtenerPorAtencion($ate_codigo);

            return $this->response->setJSON([
                'success' => true,
                'data' => $resultados
            ]);

        } catch (\Exception $e) {
            return $this->response->setJSON([
                'success' => false,
                'error' => 'Error al obtener registros'
            ]);
        }
    }

    /**
     * Marcar farmacoterapia como administrada
     */
    public function marcarAdministrado()
    {
        if (!session()->get('logged_in') || session()->get('rol_id') != 4) {
            return $this->response->setJSON(['success' => false, 'error' => 'No autorizado']);
        }

        try {
            $input = json_decode(file_get_contents('php://input'), true);
            $ep_codigo = $input['ep_codigo'] ?? '';

            if (!$ep_codigo) {
                return $this->response->setJSON(['success' => false, 'error' => 'Código de evolución requerido']);
            }

            $resultado = $this->evolucionModel->marcarComoAdministrado($ep_codigo);

            if ($resultado) {
                return $this->response->setJSON([
                    'success' => true,
                    'message' => 'Marcado como administrado correctamente'
                ]);
            } else {
                return $this->response->setJSON([
                    'success' => false,
                    'error' => 'No se pudo actualizar el registro'
                ]);
            }

        } catch (\Exception $e) {
            return $this->response->setJSON([
                'success' => false,
                'error' => 'Error al marcar como administrado'
            ]);
        }
    }

    /**
     * Obtener estadísticas de evoluciones
     */
    public function obtenerEstadisticas($ate_codigo)
    {
        if (!session()->get('logged_in') || session()->get('rol_id') != 4) {
            return $this->response->setJSON(['success' => false, 'error' => 'No autorizado']);
        }

        try {
            $estadisticas = $this->evolucionModel->obtenerEstadisticas($ate_codigo);

            return $this->response->setJSON([
                'success' => true,
                'data' => $estadisticas
            ]);

        } catch (\Exception $e) {
            return $this->response->setJSON([
                'success' => false,
                'error' => 'Error al obtener estadísticas'
            ]);
        }
    }

    /**
     * Buscar evoluciones por texto
     */
    public function buscarPorTexto()
    {
        if (!session()->get('logged_in') || session()->get('rol_id') != 4) {
            return $this->response->setJSON(['success' => false, 'error' => 'No autorizado']);
        }

        try {
            $input = json_decode(file_get_contents('php://input'), true);
            $ate_codigo = $input['ate_codigo'] ?? '';
            $texto = $input['texto'] ?? '';

            if (!$ate_codigo || !$texto) {
                return $this->response->setJSON(['success' => false, 'error' => 'Parámetros incompletos']);
            }

            $resultados = $this->evolucionModel->buscarPorTexto($ate_codigo, $texto);

            return $this->response->setJSON([
                'success' => true,
                'data' => $resultados
            ]);

        } catch (\Exception $e) {
            return $this->response->setJSON([
                'success' => false,
                'error' => 'Error en la búsqueda'
            ]);
        }
    }

    /**
     * Obtener evoluciones pendientes de administración
     */
    public function obtenerPendientesAdministracion($ate_codigo)
    {
        if (!session()->get('logged_in') || session()->get('rol_id') != 4) {
            return $this->response->setJSON(['success' => false, 'error' => 'No autorizado']);
        }

        try {
            $pendientes = $this->evolucionModel->obtenerPendientesAdministracion($ate_codigo);

            return $this->response->setJSON([
                'success' => true,
                'data' => $pendientes,
                'count' => count($pendientes)
            ]);

        } catch (\Exception $e) {
            return $this->response->setJSON([
                'success' => false,
                'error' => 'Error al obtener pendientes'
            ]);
        }
    }

    /**
     * Generar reporte de evoluciones
     */
    public function generarReporte()
    {
        if (!session()->get('logged_in') || session()->get('rol_id') != 4) {
            return $this->response->setJSON(['success' => false, 'error' => 'No autorizado']);
        }

        try {
            $input = json_decode(file_get_contents('php://input'), true);
            $fecha_inicio = $input['fecha_inicio'] ?? '';
            $fecha_fin = $input['fecha_fin'] ?? '';
            $especialidad = $input['especialidad'] ?? null;

            if (!$fecha_inicio || !$fecha_fin) {
                return $this->response->setJSON(['success' => false, 'error' => 'Fechas requeridas']);
            }

            $reporte = $this->evolucionModel->obtenerParaReporte($fecha_inicio, $fecha_fin, $especialidad);

            return $this->response->setJSON([
                'success' => true,
                'data' => $reporte,
                'total_registros' => count($reporte)
            ]);

        } catch (\Exception $e) {
            return $this->response->setJSON([
                'success' => false,
                'error' => 'Error al generar reporte'
            ]);
        }
    }
}