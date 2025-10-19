<?php

namespace App\Controllers\Medicos;

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
use App\Models\Enfemeria\ConstantesVitalesModel;
use App\Models\Administrador\UsuarioModel;
use App\Models\Admision\GuardarSecciones\NacionalidadIndigenaModel;
use App\Models\Admision\GuardarSecciones\PuebloIndigenaModel;
use App\Models\Administrador\ModificacionesModel;
use App\Models\Medicos\GuardarSecciones\CondicionLlegadaModel;

use Exception;

class DatosMedicosController extends BaseController
{
    // Método principal para cargar el formulario médico con datos precargados

    public function formulario($ate_codigo = null)
    {
        if (!session()->get('logged_in')) {
            return redirect()->to('/login');
        }

        // Verificar que el usuario sea médico (rol_id 4)
        if (session()->get('rol_id') != 4) {
            return redirect()->to('/medicos/lista')->with('error', 'No tiene permisos para acceder a esta sección.');
        }

        if (!$ate_codigo) {
            return redirect()->to(base_url('medicos/lista'))->with('error', 'Debe seleccionar una atención.');
        }


        try {
            $atencionModel = new AtencionModel();
            $atencion = $atencionModel->find($ate_codigo);

            if (!$atencion) {
                return redirect()->to('/medicos/lista')->with('error', 'No se encontró la atención especificada.');
            }

            $usu_id = session()->get('usu_id');


            // NUEVA VERIFICACIÓN: Comprobar si el formulario ya está completado
            $db = \Config\Database::connect();
            $formularioCompletado = $db->table('t_formulario_usuario')
                ->where('ate_codigo', $ate_codigo)
                ->where('seccion', 'ME')
                ->where('habilitado_por_admin !=', 1) // No es modificación habilitada
                ->get()
                ->getRowArray();

            if ($formularioCompletado) {
                return $this->mostrarFormularioYaCompletado($ate_codigo);
            }

            // Verificar permisos usando el modelo de modificaciones
            $modificacionesModel = new ModificacionesModel();
            $accesoPermitido = $modificacionesModel->verificarAccesoMedico($ate_codigo, $usu_id, 'ME');

            if (!$accesoPermitido['acceso']) {
                return redirect()->to('/medicos/lista')->with('error', $accesoPermitido['motivo']);
            }

            // Si es modificación, agregar información al data
            if (isset($accesoPermitido['es_modificacion']) && $accesoPermitido['es_modificacion']) {
                session()->setFlashdata('info', '🔄 Formulario en modo modificación. Puede editar los datos previamente guardados.');
            }

        } catch (\Exception $e) {
            return redirect()->to('/medicos/lista')->with('error', 'Error interno al verificar el formulario.');
        }

        $data = $this->cargarDatosFormulario($ate_codigo);

        if (isset($data['error'])) {
            return redirect()->to(base_url('medicos/lista'))->with('error', $data['error']);
        }

        // Agregar información de contexto de modificación
        $data['script_modificacion'] = "<script>window.esModificacion = true;</script>";
        $data['es_modificacion'] = false;
        $data['mensaje_modificacion'] = '';

        if (isset($data['ate_codigo'])) {
            try {
                $formularioExistente = $db->table('t_formulario_usuario')
                    ->where('ate_codigo', $data['ate_codigo'])
                    ->where('seccion', 'ME')
                    ->get()
                    ->getRowArray();

                if ($formularioExistente && $formularioExistente['habilitado_por_admin'] == 1) {
                    $data['es_modificacion'] = true;
                    $data['mensaje_modificacion'] = 'Este formulario está habilitado para modificación por el administrador.';

                    // CARGAR DATOS GUARDADOS PARA MODIFICACIÓN
                    $datosGuardados = $this->cargarDatosGuardados($data['ate_codigo']);
                    $data['datosFormularioGuardadoMedico'] = $datosGuardados;

                }
            } catch (\Exception $e) {
            }
        }

        // Cargar la vista médica con todos los datos necesarios
        return view('medicos/medico', $data);
    }
    private function mostrarFormularioYaCompletado($ate_codigo)
    {
        try {
            $db = \Config\Database::connect();

            // Obtener información del médico que completó
            $formularioInfo = $db->table('t_formulario_usuario fu')
                ->select('fu.fecha, u.usu_nombre, u.usu_apellido')
                ->join('t_usuario u', 'fu.usu_id = u.usu_id')
                ->where('fu.ate_codigo', $ate_codigo)
                ->where('fu.seccion', 'ME')
                ->orderBy('fu.fecha', 'DESC')
                ->get()
                ->getRowArray();

            // Obtener información del paciente a través de la atención
            $pacienteInfo = $db->table('t_paciente p')
                ->select('p.pac_nombres, p.pac_apellidos, p.pac_cedula')
                ->join('t_atencion a', 'p.pac_codigo = a.pac_codigo')
                ->where('a.ate_codigo', $ate_codigo)
                ->get()
                ->getRowArray();

            $data = [
                'title' => 'Formulario Médico Ya Completado',
                'mensaje_completado' => 'El formulario médico para este paciente ya fue completado anteriormente. No puede ser modificado nuevamente.',
                'ate_codigo' => $ate_codigo,
                'fecha_completado' => $formularioInfo['fecha'] ?? 'Fecha no disponible',
                'medico_que_completo' => ($formularioInfo['usu_nombre'] ?? '') . ' ' . ($formularioInfo['usu_apellido'] ?? ''),
                'paciente_info' => $pacienteInfo,
                'tipo_formulario' => 'medico'
            ];

            return view('medicos/formulario_completado_medico', $data);

        } catch (Exception $e) {
            return redirect()->to('/medicos/lista')->with('error', 'El formulario ya fue completado. No puede acceder nuevamente.');
        }
    }

    // Método para cargar todos los datos necesarios para el formulario médico
    private function cargarDatosFormulario($ate_codigo)
    {

        $data = [];

        $usuarioModel = new UsuarioModel();
        $medicoActual = $usuarioModel->find(session()->get('usu_id'));

        // Separar nombres y apellidos correctamente
        $nombreCompleto = session()->get('usu_nombre');
        $apellidoCompleto = session()->get('usu_apellido');

        // Separar nombres (pueden ser varios)
        $nombres = explode(' ', trim($nombreCompleto));
        $primerNombres = $nombres[0] ?? '';
        $segundoNombre = isset($nombres[1]) ? implode(' ', array_slice($nombres, 1)) : '';

        // Separar apellidos (pueden ser varios)  
        $apellidos = explode(' ', trim($apellidoCompleto));
        $primerApellido = $apellidos[0] ?? '';
        $segundoApellido = $apellidos[1] ?? '';

        // Agregar datos del médico correctamente separados
        $data['medico_actual'] = [
            'primer_nombre' => $primerNombres,
            'segundo_nombre' => $segundoNombre,
            'nombre_completo' => $nombreCompleto,
            'primer_apellido' => $primerApellido,
            'segundo_apellido' => $segundoApellido,
            'apellido_completo' => $apellidoCompleto,
            'documento' => $medicoActual['usu_nro_documento'] ?? ''
        ];

        // Para compatibilidad con código existente
        $nombreCompleto = $nombreCompleto . ' ' . $apellidoCompleto;
        $data['nombre_admisionista'] = $nombreCompleto;
        $data['medico_nombre'] = $nombreCompleto;

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
        try {
            $condicionLlegadaModel = new CondicionLlegadaModel();
            $data['CondicionLlegada'] = $condicionLlegadaModel->findAll();
        } catch (\Exception $e) {
            $data['CondicionLlegada'] = [];
        }
        // Obtener los datos del paciente y su atención MÁS RECIENTE (IGUAL QUE ENFERMERÍA)
        try {
            $pacienteModel = new PacienteModel();
            $atencionModel = new AtencionModel();
            $constantesVitalesModel = new ConstantesVitalesModel();

            // Obtener la atención específica usando ate_codigo
            $atencion = $atencionModel->find($ate_codigo);

            if (!$atencion) {
                return ['error' => 'No se encontró la atención especificada.'];
            }

            // Obtener datos del paciente usando pac_codigo de la atención
            $paciente = $pacienteModel->find($atencion['pac_codigo']);

            if (!$paciente) {
                return ['error' => 'No se encontró el paciente asociado a esta atención.'];
            }


            // Verificar que el paciente tenga constantes vitales (que haya pasado por enfermería)
            $constantesVitales = $constantesVitalesModel->obtenerUltimasPorAtencion($atencion['ate_codigo']);
            if (empty($constantesVitales)) {
                return ['error' => 'Este paciente aún no ha sido evaluado por enfermería. No se pueden cargar los datos médicos.'];
            }

            $data['datos_constantes_vitales_mapeados'] = $this->mapearConstantesVitales($constantesVitales);

            // Obtener datos del admisionista original
            $establecimientoRegistroModel = new EstablecimientoRegistroModel();

            $registroAdmision = $establecimientoRegistroModel
                ->select('t_establecimiento_registro.*, t_usuario.usu_nombre, t_usuario.usu_apellido, t_usuario.usu_usuario')
                ->join('t_usuario', 't_establecimiento_registro.usu_id = t_usuario.usu_id')
                ->where('t_establecimiento_registro.ate_codigo', $atencion['ate_codigo'])
                ->first();

            if ($registroAdmision) {
                $nombreAdmisionista = $registroAdmision['usu_nombre'] . ' ' . $registroAdmision['usu_apellido'];
                $data['admisionista_original'] = $nombreAdmisionista;
                $data['estabArchivo'] = $registroAdmision['est_num_archivo'];

            } else {
                $data['admisionista_original'] = 'No encontrado';
                $data['estabArchivo'] = '00000';
            }


            // Preparar datos para las secciones A, B y G (solo lectura)
            $data['paciente'] = $paciente;
            $data['atencion'] = $atencion;
            $data['ate_codigo'] = $ate_codigo;
            $data['paciente_id'] = $atencion['pac_codigo'];

            // Mapear datos específicos para los formularios
            $data['datos_paciente_mapeados'] = $this->mapearDatosPaciente($paciente);
            $data['datos_atencion_mapeados'] = $this->mapearDatosAtencion($atencion);

            // Agregar datos de constantes vitales para médicos
            $data['datos_constantes_vitales'] = $constantesVitales;

            // Indicar que debe mostrar las secciones en modo lectura y precargar datos
            $data['mostrar_seccion_g'] = true; // Para mostrar la sección G de enfermería
            $data['mostrar_modo_medico'] = true; // Modo médico
            $data['precargar_datos'] = true;

        } catch (\Exception $e) {
            return ['error' => 'Hubo un error al obtener los datos del paciente: ' . $e->getMessage()];
        }

        // Cargar catálogos
        $this->cargarCatalogos($data);

        return $data;
    }
    private function mapearConstantesVitales($constantesVitales)
    {
        if (empty($constantesVitales))
            return [];

        return [
            'cv_sin_vitales' => $constantesVitales['con_sin_constantes'] ? '1' : '0',
            'cv_presion_arterial' => $constantesVitales['con_presion_arterial'] ?? '',
            'cv_pulso' => $constantesVitales['con_pulso'] ?? '',
            'cv_frec_resp' => $constantesVitales['con_frec_respiratoria'] ?? '',
            'cv_pulsioximetria' => $constantesVitales['con_pulsioximetria'] ?? '',
            'cv_perimetro_cefalico' => $constantesVitales['con_perimetro_cefalico'] ?? '',
            'cv_peso' => $constantesVitales['con_peso'] ?? '',
            'cv_talla' => $constantesVitales['con_talla'] ?? '',
            'cv_glicemia' => $constantesVitales['con_glucemia_capilar'] ?? '',
            'cv_reaccion_pupilar_der' => $constantesVitales['con_reaccion_pupila_der'] ?? '',
            'cv_reaccion_pupilar_izq' => $constantesVitales['con_reaccion_pupila_izq'] ?? '',
            'cv_llenado_capilar' => $constantesVitales['con_t_lleno_capilar'] ?? '',
            'cv_glasgow_ocular' => $constantesVitales['con_glasgow_ocular'] ?? '',
            'cv_glasgow_verbal' => $constantesVitales['con_glasgow_verbal'] ?? '',
            'cv_glasgow_motora' => $constantesVitales['con_glasgow_motora'] ?? '',
            // Información adicional
            'con_codigo' => $constantesVitales['con_codigo'] ?? '',
            'fecha_registro' => $constantesVitales['ate_fecha'] ?? '',
            'hora_registro' => $constantesVitales['ate_hora'] ?? ''
        ];
    }
    // Método para mapear datos del paciente (reutilizado de enfermería)
    private function mapearDatosPaciente($paciente)
    {
        if (!$paciente)
            return [];

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

    // Método para mapear datos de atención (reutilizado de enfermería)
    private function mapearDatosAtencion($atencion)
    {
        if (!$atencion)
            return [];

        return [
            'lleg_codigo' => $atencion['lleg_codigo'] ?? '',
            'ate_fuente_informacion' => $atencion['ate_fuente_informacion'] ?? '',
            'ate_ins_entrega_paciente' => $atencion['ate_ins_entrega_paciente'] ?? '',
            'ate_telefono' => $atencion['ate_telefono'] ?? '',
            'ate_colores' => $atencion['ate_colores'] ?? '',
        ];
    }

    // Método para cargar todos los catálogos (reutilizado de enfermería)
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
                $data[$key] = [];
            }
        }
    }

    private function cargarDatosGuardados($ate_codigo)
    {
        $db = \Config\Database::connect();
        $datosGuardados = [];

        try {
            // Configuración de secciones médicas para cargar
            $secciones = [
                'seccionC' => 't_inicio_atencion',
                'seccionD' => 't_evento',
                'seccionE' => 't_antecedente_paciente',
                'seccionF' => 't_problema_actual',
                'seccionH' => 't_examen_fisico',
                'seccionI' => 't_examen_trauma',
                'seccionJ' => 't_embarazo_parto',
                'seccionK' => 't_examenes_complementarios',
                'seccionL' => 't_diagnostico_presuntivo',
                'seccionM' => 't_diagnostico_definitivo',
                'seccionN' => 't_tratamiento',
                'seccionO' => 't_egreso_emergencia',
                'seccionP' => 't_profesional_responsable'
            ];

            // Cargar todas las secciones usando el método auxiliar
            foreach ($secciones as $seccion => $tabla) {
                $datos = $this->cargarSeccionGuardada($db, $tabla, $ate_codigo);
                if (!empty($datos)) {
                    $datosGuardados[$seccion] = $datos;
                }
            }

            // Sección D: Agregar datos adicionales de t_atencion
            $this->agregarDatosAtencionSeccionD($db, $datosGuardados, $ate_codigo);

            return $datosGuardados;

        } catch (\Exception $e) {
            return [];
        }
    }

    /**
     * Método auxiliar para cargar datos de una sección específica
     */
    private function cargarSeccionGuardada($db, $tabla, $ate_codigo)
    {
        try {
            return $db->table($tabla)
                ->where('ate_codigo', $ate_codigo)
                ->get()
                ->getResultArray();
        } catch (\Exception $e) {
            return [];
        }
    }

    /**
     * Método auxiliar para agregar datos adicionales de atención a la sección D
     */
    private function agregarDatosAtencionSeccionD($db, &$datosGuardados, $ate_codigo)
    {
        try {
            $atencionData = $db->table('t_atencion')
                ->select('ate_custodia_policial, ate_aliento_etilico, ate_observaciones')
                ->where('ate_codigo', $ate_codigo)
                ->get()
                ->getRowArray();

            if ($atencionData) {
                // Inicializar seccionD si no existe
                if (!isset($datosGuardados['seccionD'])) {
                    $datosGuardados['seccionD'] = [];
                }

                // Agregar datos de atención a la sección D
                $datosGuardados['seccionD']['ate_custodia_policial'] = $atencionData['ate_custodia_policial'];
                $datosGuardados['seccionD']['ate_aliento_etilico'] = $atencionData['ate_aliento_etilico'];
            }
        } catch (\Exception $e) {
        }
    }
    // Método público para obtener datos del paciente via AJAX (TAMBIÉN ACTUALIZADO)
    public function obtenerDatosPaciente($pacienteId)
    {
        if (!session()->get('logged_in')) {
            return $this->response->setJSON(['error' => 'No autorizado'])->setStatusCode(401);
        }

        if (session()->get('rol_id') != 3) {
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
            $constantesVitalesModel = new ConstantesVitalesModel();

            $pacienteData = $pacienteModel->find($pacienteId);

            // Importante: También aquí obtener la atención MÁS RECIENTE**
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

            $constantesVitales = [];
            if ($atencionData) {
                $constantesVitales = $constantesVitalesModel->obtenerPorAtencion($atencionData['ate_codigo']);
            }

            $response = [
                'success' => true,
                'paciente' => $this->mapearDatosPaciente($pacienteData),
                'atencion' => $this->mapearDatosAtencion($atencionData),
                'constantes_vitales' => $constantesVitales
            ];

            return $this->response->setJSON($response);
        } catch (\Exception $e) {

            return $this->response->setJSON([
                'success' => false,
                'error' => 'Error interno del servidor: ' . $e->getMessage()
            ]);
        }
    }
}
