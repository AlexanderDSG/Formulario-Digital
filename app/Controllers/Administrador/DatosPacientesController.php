<?php

namespace App\Controllers\Administrador;

use App\Controllers\BaseController;
use App\Models\Admision\GuardarSecciones\TipoDocumentoModel;
use App\Models\Admision\GuardarSecciones\GeneroModel;
use App\Models\Admision\GuardarSecciones\SeguroModel;
use App\Models\Admision\GuardarSecciones\EstadoCivilModel;
use App\Models\Admision\GuardarSecciones\NacionalidadModel;
use App\Models\Admision\GuardarSecciones\EmpresaModel;
use App\Models\Admision\GuardarSecciones\EtniaModel;
use App\Models\Admision\GuardarSecciones\NivelEducacionModel;
use App\Models\Admision\GuardarSecciones\EstadoEducacionModel;
use App\Models\Admision\GuardarSecciones\LlegadaModel;
use App\Models\Admision\GuardarSecciones\EstablecimientoModel;
use App\Models\EstablecimientoRegistroModel;
use App\Models\Admision\GuardarSecciones\NacionalidadIndigenaModel;
use App\Models\Admision\GuardarSecciones\PuebloIndigenaModel;
use App\Models\Medicos\GuardarSecciones\CondicionLlegadaModel;
class DatosPacientesController extends BaseController
{

    /**
     * Vista dual que muestra formulario 008 + 005 lado a lado
     */
    public function vistaDual($identificador = null)
    {
        if (!session()->get('logged_in')) {
            return redirect()->to('/login');
        }

        if (session()->get('rol_id') != 1) {
            return redirect()->to('/login')->with('error', 'No tienes permisos para acceder a esta sección');
        }

        // Preparar los datos para la vista - COPIANDO TODA LA LÓGICA DEL verFormulario()
        $data = [];
        $nombreCompleto = session()->get('usu_nombre') . ' ' . session()->get('usu_apellido');
        $data['nombre_admisionista'] = $nombreCompleto;

        // IMPORTANTE: Pasar el identificador a la vista
        $data['identificador_paciente'] = $identificador;

        // LÓGICA OPTIMIZADA PARA DETERMINAR CÉDULA VS HISTORIA CLÍNICA
        // Usar lógica simple y rápida basada en el formato del identificador
        if (is_numeric($identificador) && strlen($identificador) >= 8) {
            // Si es numérico y tiene 8+ dígitos, probablemente es cédula
            $data['cedula_paciente'] = $identificador;
            $data['historia_clinica'] = '';
        } else {
            // Si no es numérico o es corto, probablemente es historia clínica
            $data['cedula_paciente'] = '';
            $data['historia_clinica'] = $identificador;
        }

        // NOTA: Los datos reales del paciente se cargan después via AJAX, no aquí
        // Esto permite que la página se cargue rápidamente

        try {
            $establecimientoModel = new EstablecimientoModel();
            $establecimiento = $establecimientoModel->obtenerEstablecimientoActual();

            $data['estab_institucion'] = $establecimiento['est_institucion'] ?? '';
            $data['estab_unicode'] = $establecimiento['est_unicodigo'] ?? '';
            $data['estab_nombre'] = $establecimiento['est_nombre_establecimiento'] ?? '';
        } catch (\Exception $e) {
            $data['estab_institucion'] = '';
            $data['estab_unicode'] = '';
            $data['estab_nombre_establecimiento'] = '';
        }

        // Cargar todos los catálogos
        $data = array_merge($data, $this->cargarCatalogos());

        // OPTIMIZACIÓN: Datos del formulario 005 se cargan via AJAX
        // En lugar de hacer consultas costosas aquí, se cargan cuando se busca una fecha específica
        // Valores por defecto vacíos para carga rápida
        $data['cedula'] = '';
        $data['primer_apellido'] = '';
        $data['segundo_apellido'] = '';
        $data['primer_nombre'] = '';
        $data['segundo_nombre'] = '';
        $data['sexo'] = '';
        $data['edad'] = '';
        $data['condicion_edad'] = 'A';
        $data['numero_archivo'] = '';

        return view('administrador/formulario_dual', $data);
    }

    /**
     * Endpoint para buscar datos de evolución y prescripciones por fecha
     */
    public function buscarEvolucionPorFecha()
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setJSON(['success' => false, 'message' => 'Petición inválida']);
        }

        if (!session()->get('logged_in') || session()->get('rol_id') != 1) {
            return $this->response->setJSON(['success' => false, 'message' => 'Sin permisos']);
        }

        try {
            $json = $this->request->getJSON(true);

            $fecha = $json['fecha'] ?? null;
            $identificador = $json['identificador'] ?? '';

            if (!$fecha) {
                return $this->response->setJSON(['success' => false, 'message' => 'Fecha requerida']);
            }

            // Obtener evoluciones usando el modelo real
            $evoluciones = $this->obtenerEvolucionesPorFecha($identificador, $fecha);

            // Obtener datos del paciente para sección A del formulario 005
            $datosPaciente = $this->obtenerDatosPaciente($identificador, $fecha);

            if (empty($evoluciones)) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => "No se encontraron datos para el identificador '{$identificador}' en la fecha '{$fecha}'"
                ]);
            }

            return $this->response->setJSON([
                'success' => true,
                'data' => [
                    'fecha' => $fecha,
                    'evoluciones' => $evoluciones,
                    'paciente' => $datosPaciente
                ]
            ]);

        } catch (\Exception $e) {
            return $this->response->setJSON(['success' => false, 'message' => 'Error interno del servidor: ' . $e->getMessage()]);
        }
    }

    /**
     * Método auxiliar para obtener evoluciones por fecha
     */
    private function obtenerEvolucionesPorFecha($identificador, $fecha)
    {
        try {
            $busquedaModel = new \App\Models\Administrador\BusquedaCompletaModel();
            $resultadoEvolucion = $busquedaModel->obtenerEvolucionPorFecha($identificador, $fecha);

            if (empty($resultadoEvolucion) || empty($resultadoEvolucion['evoluciones'])) {
                return [];
            }

            // Los datos ya vienen formateados correctamente del modelo
            $evoluciones = $resultadoEvolucion['evoluciones'];

            // Ordenar por hora si es necesario
            usort($evoluciones, function ($a, $b) {
                return strcmp($a['hora'] ?? '', $b['hora'] ?? '');
            });

            return $evoluciones;

        } catch (\Exception $e) {
            return [];
        }
    }

    /**
     * Obtener datos básicos del paciente para formulario 005
     */
    private function obtenerDatosPaciente($identificador, $fecha = null)
    {
        try {
            $busquedaModel = new \App\Models\Administrador\BusquedaCompletaModel();

            // Si no se proporciona fecha, usar fecha actual como fallback
            $fechaBusqueda = $fecha ?? date('Y-m-d');
            $resultados = $busquedaModel->obtenerPorIdentificadorYFecha($identificador, $fechaBusqueda);

            if (empty($resultados)) {
                // Si no encuentra con fecha específica, buscar cualquier atención del paciente

                // Buscar directamente en la tabla de pacientes con JOIN para obtener género
                $db = \Config\Database::connect();
                $sql = "SELECT
                    p.pac_cedula,
                    p.pac_his_cli,
                    p.pac_nombres,
                    p.pac_apellidos,
                    p.pac_edad_valor,
                    p.pac_edad_unidad,
                    g.gen_descripcion as genero
                FROM t_paciente p
                LEFT JOIN t_genero g ON g.gen_codigo = p.gen_codigo
                WHERE p.pac_cedula = ? OR p.pac_his_cli = ?
                ORDER BY p.pac_codigo DESC
                LIMIT 1";

                $resultado = $db->query($sql, [$identificador, $identificador]);
                $datos = $resultado->getRowArray();

                if (empty($datos)) {
                    return null;
                }

                // Dividir nombres y apellidos que vienen juntos en la BD (respaldo)
                $nombresCompletos = trim($datos['pac_nombres'] ?? '');
                $apellidosCompletos = trim($datos['pac_apellidos'] ?? '');

                // Separar nombres (primer y segundo)
                $nombres = explode(' ', $nombresCompletos, 2);
                $primerNombre = $nombres[0] ?? '';
                $segundoNombre = $nombres[1] ?? '';

                // Separar apellidos (primer y segundo)
                $apellidos = explode(' ', $apellidosCompletos, 2);
                $primerApellido = $apellidos[0] ?? '';
                $segundoApellido = $apellidos[1] ?? '';

                // Mapear los datos al formato esperado
                $datosPacienteRespaldo = [
                    'cedula' => $datos['pac_cedula'] ?? '',
                    'historia_clinica' => $datos['pac_his_cli'] ?? '',
                    'primer_nombre' => $primerNombre,
                    'segundo_nombre' => $segundoNombre,
                    'primer_apellido' => $primerApellido,
                    'segundo_apellido' => $segundoApellido,
                    'sexo' => $datos['genero'] ?? '',
                    'edad' => $datos['pac_edad_valor'] ?? '',
                    'condicion_edad' => $datos['pac_edad_unidad'] ?? 'A',
                    'numero_archivo' => ''
                ];

                return $datosPacienteRespaldo;
            }

            // Si encontró resultados con la fecha, extraer datos del paciente
            $data = $resultados[0];

            // Dividir nombres y apellidos que vienen juntos en la BD
            $nombresCompletos = trim($data['pac_nombres'] ?? '');
            $apellidosCompletos = trim($data['pac_apellidos'] ?? '');

            // Separar nombres (primer y segundo)
            $nombres = explode(' ', $nombresCompletos, 2);
            $primerNombre = $nombres[0] ?? '';
            $segundoNombre = $nombres[1] ?? '';

            // Separar apellidos (primer y segundo)
            $apellidos = explode(' ', $apellidosCompletos, 2);
            $primerApellido = $apellidos[0] ?? '';
            $segundoApellido = $apellidos[1] ?? '';

            // Obtener número de hoja de la primera evolución de esta atención
            $numeroHoja = '';
            if (!empty($data['ate_codigo'])) {
                $evolucionModel = new \App\Models\Especialidades\EvolucionPrescripcionesModel();
                $primeraEvolucion = $evolucionModel->where('ate_codigo', $data['ate_codigo'])
                    ->orderBy('ep_fecha', 'ASC')
                    ->orderBy('ep_hora', 'ASC')
                    ->first();
                if ($primeraEvolucion) {
                    $numeroHoja = $primeraEvolucion['ep_numero_hoja'] ?? '';
                }
            }

            // Mapear campos de la base de datos a los nombres esperados por el frontend
            $datosPaciente = [
                'cedula' => $data['pac_cedula'] ?? '',
                'historia_clinica' => $data['pac_his_cli'] ?? '',
                'primer_nombre' => $primerNombre,
                'segundo_nombre' => $segundoNombre,
                'primer_apellido' => $primerApellido,
                'segundo_apellido' => $segundoApellido,
                'sexo' => $data['genero'] ?? '',
                'edad' => $data['pac_edad_valor'] ?? '',
                'condicion_edad' => $data['pac_edad_unidad'] ?? 'A',
                'numero_archivo' => $data['est_num_archivo'] ?? '',
                'numero_hoja' => $numeroHoja
            ];

            return $datosPaciente;

        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Método auxiliar para cargar datos de modelos con manejo de errores
     */
    private function cargarDatosModelo($modelClass, $nombreClave, $metodo = 'findAll', $valorPorDefecto = [])
    {
        try {
            $modelo = new $modelClass();
            return [$nombreClave => $modelo->$metodo()];
        } catch (\Exception $e) {
            return [$nombreClave => $valorPorDefecto];
        }
    }

    /**
     * Cargar todos los catálogos necesarios
     */
    private function cargarCatalogos()
    {
        $catalogos = [
            [TipoDocumentoModel::class, 'tiposDocumento'],
            [GeneroModel::class, 'generos'],
            [SeguroModel::class, 'seguros'],
            [EstadoCivilModel::class, 'estadoCiviles'],
            [NacionalidadModel::class, 'nacionalidades'],
            [NacionalidadIndigenaModel::class, 'nacionalidadIndigena'],
            [PuebloIndigenaModel::class, 'puebloIndigena'],
            [EmpresaModel::class, 'empresas'],
            [EtniaModel::class, 'etnias'],
            [NivelEducacionModel::class, 'nivelesEducacion'],
            [EstadoEducacionModel::class, 'estadosEducacion'],
            [LlegadaModel::class, 'formasLlegada'],
            [CondicionLlegadaModel::class, 'CondicionLlegada']
        ];

        $data = [];
        foreach ($catalogos as [$modelClass, $nombreClave]) {
            $data = array_merge($data, $this->cargarDatosModelo($modelClass, $nombreClave));
        }

        return $data;
    }

}
