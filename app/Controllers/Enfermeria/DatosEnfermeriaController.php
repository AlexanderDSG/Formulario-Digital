<?php

namespace App\Controllers\Enfermeria;

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
use App\Models\Admision\EstablecimientoRegistroModel;
use App\Models\PacienteModel;
use App\Models\AtencionModel;
use App\Models\Admision\GuardarSecciones\NacionalidadIndigenaModel;
use App\Models\Admision\GuardarSecciones\PuebloIndigenaModel;

// Método principal para cargar la sección A y B con datos precargados
class DatosEnfermeriaController extends BaseController
{
        public function formulario($ate_codigo = null)
    {
        if (!session()->get('logged_in')) {
            return redirect()->to('/login');
        }

        if (session()->get('rol_id') != 3) {
            return redirect()->to('/enfermeria/lista')->with('error', 'No tiene permisos para acceder a esta sección.');
        }

        if (!$ate_codigo) {
            return redirect()->to(base_url('enfermeria/lista'))->with('error', 'Debe seleccionar una atención.');
        }

        $data = $this->cargarDatosFormulario($ate_codigo);

        if (isset($data['error'])) {
            return redirect()->to(base_url('enfermeria/lista'))->with('error', $data['error']);
        }

        return view('enfermeria/enfermeria', $data);
    }

    // Método para cargar todos los datos necesarios para el formulario
    private function cargarDatosFormulario($ate_codigo)
    {
        $data = [];
        $nombreCompleto = session()->get('usu_nombre') . ' ' . session()->get('usu_apellido');
        $data['nombre_admisionista'] = $nombreCompleto;
        $data['enfermeria_nombre'] = $nombreCompleto;

        try {
            $establecimientoModel = new EstablecimientoModel();
            $establecimiento = $establecimientoModel->obtenerEstablecimientoActual();

            $data['estab_institucion'] = $establecimiento['est_institucion'] ?? '';
            $data['estab_unicode'] = $establecimiento['est_unicodigo'] ?? '';
            $data['estab_nombre'] = $establecimiento['est_nombre_establecimiento'] ?? '';
        } catch (\Exception $e) {
            log_message('error', 'Error cargando datos del establecimiento: ' . $e->getMessage());
            $data['estab_institucion'] = '';
            $data['estab_unicode'] = '';
            $data['estab_nombre_establecimiento'] = '';
        }

        // Obtener los datos usando ate_codigo específico
        try {
            $pacienteModel = new PacienteModel();
            $atencionModel = new AtencionModel();

            // Obtener la atención específica
            $atencion = $atencionModel->find($ate_codigo);

            if (!$atencion) {
                return ['error' => 'No se encontró la atención especificada.'];
            }

            // Obtener datos del paciente usando pac_codigo de la atención
            $paciente = $pacienteModel->find($atencion['pac_codigo']);

            if (!$paciente) {
                return ['error' => 'No se encontró el paciente asociado a esta atención.'];
            }

            // Agregar ate_codigo para el formulario
            $data['ate_codigo'] = $ate_codigo;
            
            // Verificar si esta atención YA tiene constantes vitales
            $db = \Config\Database::connect();
            $constantesExistentes = $db->query(
                "SELECT con_codigo FROM t_constantes_vitales WHERE ate_codigo = ?",
                [$ate_codigo]
            )->getRow();

            if ($constantesExistentes) {
                return ['error' => 'Esta atención (#' . $ate_codigo . ') ya tiene constantes vitales registradas. El paciente debe estar disponible para medicina, no para enfermería.'];
            }

            // NUEVA FUNCIONALIDAD: Obtener datos del admisionista original
            $establecimientoRegistroModel = new EstablecimientoRegistroModel();
            
            // Buscar el registro de admisión con los datos del usuario que lo creó
            $registroAdmision = $establecimientoRegistroModel
                ->select('t_establecimiento_registro.*, t_usuario.usu_nombre, t_usuario.usu_apellido, t_usuario.usu_usuario')
                ->join('t_usuario', 't_establecimiento_registro.usu_id = t_usuario.usu_id')
                ->where('t_establecimiento_registro.ate_codigo', $atencion['ate_codigo'])
                ->first();
            
            if ($registroAdmision) {
                // Nombre del admisionista para el input adm_admisionista_nombre y número de archivo
                $nombreAdmisionista = $registroAdmision['usu_nombre'] . ' ' . $registroAdmision['usu_apellido'];
                $data['admisionista_original'] = $nombreAdmisionista;
                $data['estabArchivo'] = $registroAdmision['est_num_archivo'];
            } else {
                $data['admisionista_original'] = 'No encontrado';
                $data['estabArchivo'] = '00000';
            }

            // Obtener el siguiente número de archivo para enfermería (si se necesita nuevo registro)
            if (!isset($data['estabArchivo'])) {
                try {
                    $establecimientoRegistroModel = new EstablecimientoRegistroModel();
                    $numeroArchivo = $establecimientoRegistroModel->generarNumeroArchivo();
                    $data['estabArchivo'] = str_pad($numeroArchivo, 5, '0', STR_PAD_LEFT);
                } catch (\Exception $e) {
                    log_message('error', 'Error obteniendo el número de archivo: ' . $e->getMessage());
                    $data['estabArchivo'] = '00001';
                }
            }

            // Preparar datos para las secciones A y B (solo lectura)
            $data['paciente'] = $paciente;
            $data['atencion'] = $atencion;
            $data['paciente_id'] = $atencion['pac_codigo'];

            // Mapear datos específicos para los formularios
            $data['datos_paciente_mapeados'] = $this->mapearDatosPaciente($paciente);
            $data['datos_atencion_mapeados'] = $this->mapearDatosAtencion($atencion);

            // Indicar que debe mostrar la sección G y precargar datos
            $data['mostrar_seccion_g'] = true;
            $data['precargar_datos'] = true;
        } catch (\Exception $e) {
            log_message('error', 'Error crítico obteniendo datos: ' . $e->getMessage());
            return ['error' => 'Hubo un error al obtener los datos del paciente: ' . $e->getMessage()];
        }

        // Cargar catálogos
        $this->cargarCatalogos($data);

        return $data;
    }

    // Método para mapear datos del paciente basado en los campos reales del modelo
    private function mapearDatosPaciente($paciente)
    {
        if (!$paciente) return [];

        // Dividir nombres y apellidos
        $apellidosTokens = (!empty($paciente['pac_apellidos'])) ? explode(' ', trim($paciente['pac_apellidos'])) : [];
        $nombresTokens = (!empty($paciente['pac_nombres'])) ? explode(' ', trim($paciente['pac_nombres'])) : [];

        $apellido1 = $apellidosTokens[0] ?? '';
        $apellido2 = $apellidosTokens[1] ?? '';
        $nombre1 = $nombresTokens[0] ?? '';
        $nombre2 = $nombresTokens[1] ?? '';

        // Mapeo basado en campos reales de t_paciente
        $datosMapeados = [
            // Datos básicos
            'pac_his_cli' => $paciente['pac_his_cli'] ?? '',
            'apellido1' => $apellido1,
            'apellido2' => $apellido2,
            'nombre1' => $nombre1,
            'nombre2' => $nombre2,

            // Cédula para historia clínica
            'pac_cedula' => $paciente['pac_cedula'] ?? '',
            'pac_edad_valor' => $paciente['pac_edad_valor'] ?? '',
            'pac_edad_unidad' => $paciente['pac_edad_unidad'] ?? '',
           
            // Campos que existen en el modelo
            'grupo_prioritario' => $paciente['pac_grupo_prioritario'] ?? '',
            'grupo_sanguineo' => $paciente['pac_grupo_sanguineo'] ?? '',
            'tipo_documento' => $paciente['tdoc_codigo'] ?? '',
            'estado_civil' => $paciente['esc_codigo'] ?? '',
            'sexo' => $paciente['gen_codigo'] ?? '',
            'telefono_fijo' => $paciente['pac_telefono_fijo'] ?? '',
            'telefono_celular' => $paciente['pac_telefono_celular'] ?? '',
            'fecha_nacimiento' => $paciente['pac_fecha_nac'] ?? '',
            'lugar_nacimiento' => $paciente['pac_lugar_nac'] ?? '',
            'nacionalidad' => $paciente['nac_codigo'] ?? '',
            'etnia' => $paciente['gcu_codigo'] ?? '',
            'nivel_educacion' => $paciente['nedu_codigo'] ?? '',
            'estado_educacion' => $paciente['eneduc_codigo'] ?? '',
            'tipo_empresa' => $paciente['emp_codigo'] ?? '',
            'ocupacion' => $paciente['pac_ocupacion'] ?? '',
            'seguro' => $paciente['seg_codigo'] ?? '',
            'nacionalidadIndigena' => $paciente['nac_ind_codigo'] ?? '',
            'puebloIndigena' => $paciente['pue_ind_codigo'] ?? '',

            // Dirección de residencia (campos existentes)
            'res_direccion' => $paciente['pac_direccion'] ?? '',
            'res_provincia' => $paciente['pac_provincias'] ?? '',
            'res_canton' => $paciente['pac_cantones'] ?? '',
            'res_parroquia' => $paciente['pac_parroquias'] ?? '',
            'res_barrio_sector' => $paciente['pac_barrio'] ?? '',
            'res_calle_secundaria' => $paciente['pac_calle_secundaria'] ?? '',
            'res_referencia' => $paciente['pac_referencia'] ?? '',

            // Contacto de emergencia (campos existentes)
            'contacto_emerg_nombre' => $paciente['pac_avisar_a'] ?? '',
            'contacto_emerg_parentesco' => $paciente['pac_parentezco_avisar_a'] ?? '',
            'contacto_emerg_direccion' => $paciente['pac_direccion_avisar'] ?? '',
            'contacto_emerg_telefono' => $paciente['pac_telefono_avisar_a'] ?? ''
        ];

        return $datosMapeados;
    }
    // Método para mapear datos de atención específicos que solicitas
    private function mapearDatosAtencion($atencion)
    {
        if (!$atencion) return [];

        return [
            'lleg_codigo' => $atencion['lleg_codigo'] ?? '',
            'ate_fuente_informacion' => $atencion['ate_fuente_informacion'] ?? '',
            'ate_ins_entrega_paciente' => $atencion['ate_ins_entrega_paciente'] ?? '',
            'ate_telefono' => $atencion['ate_telefono'] ?? ''

        ];
    }

    // Método para cargar todos los catálogos
    private function cargarCatalogos(&$data)
    {
        $catalogos = [
            'tiposDocumento' => TipoDocumentoModel::class,
            'generos' => GeneroModel::class,
            'seguros' => SeguroModel::class,
            'estadoCiviles' => EstadoCivilModel::class,
            'nacionalidades' => NacionalidadModel::class,
            'empresas' => EmpresaModel::class,
            'etnias' => EtniaModel::class,
            'nivelesEducacion' => NivelEducacionModel::class,
            'estadosEducacion' => EstadoEducacionModel::class,
            'formasLlegada' => LlegadaModel::class,
            'nacionalidadIndigena' => NacionalidadIndigenaModel::class,
            'puebloIndigena' => PuebloIndigenaModel::class
        ];

        foreach ($catalogos as $key => $modelClass) {
            try {
                $model = new $modelClass();
                $data[$key] = $model->findAll();
            } catch (\Exception $e) {
                log_message('error', "Error cargando $modelClass: " . $e->getMessage());
                $data[$key] = [];
            }
        }
    }

    // Método público para obtener datos del paciente via AJAX (mantener para compatibilidad)
    public function obtenerDatosPaciente($pacienteId)
    {
        if (!session()->get('logged_in')) {
            return $this->response->setJSON(['error' => 'No autorizado'])->setStatusCode(401);
        }

        if (session()->get('rol_id') != 2) {
            return $this->response->setJSON(['error' => 'Sin permisos para esta sección'])->setStatusCode(403);
        }

        try {
            if (empty($pacienteId) || !is_numeric($pacienteId)) {
                return $this->response->setJSON([
                    'success' => false,
                    'error' => 'ID de paciente inválido'
                ]);
            }

            $pacienteModel = new PacienteModel();
            $atencionModel = new AtencionModel();

            $pacienteData = $pacienteModel->find($pacienteId);
            
            // **OBTENER LA ATENCIÓN MÁS RECIENTE TAMBIÉN AQUÍ**
            $atencionData = $atencionModel
                ->where('pac_codigo', $pacienteId)
                ->orderBy('ate_codigo', 'DESC')
                ->first();

            if (!$pacienteData) {
                return $this->response->setJSON([
                    'success' => false,
                    'error' => 'No se encontraron datos del paciente.'
                ]);
            }

            $response = [
                'success' => true,
                'paciente' => $this->mapearDatosPaciente($pacienteData),
                'atencion' => $this->mapearDatosAtencion($atencionData)
            ];

            return $this->response->setJSON($response);
        } catch (\Exception $e) {
            log_message('error', 'Error crítico en obtenerDatosPaciente: ' . $e->getMessage());

            return $this->response->setJSON([
                'success' => false,
                'error' => 'Error interno del servidor: ' . $e->getMessage()
            ]);
        }
    }
    
}