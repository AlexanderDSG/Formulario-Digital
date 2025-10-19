<?php

namespace App\Controllers\Especialidades;

use App\Controllers\BaseController;
use App\Models\AtencionModel;
use App\Models\Medicos\GuardarSecciones\InicioAtencionModel;
use App\Models\Medicos\GuardarSecciones\EventoModel;
use App\Models\Medicos\GuardarSecciones\AntecedentePacienteModel;
use App\Models\Medicos\GuardarSecciones\ProblemaActualModel;
use App\Models\Medicos\GuardarSecciones\ExamenFisicoModel;
use App\Models\Medicos\GuardarSecciones\ExamenTraumaModel;
use App\Models\Medicos\GuardarSecciones\EmbarazoPartoModel;
use App\Models\Medicos\GuardarSecciones\ExamenesComplementariosModel;
use App\Models\Medicos\GuardarSecciones\DiagnosticoPresuntivoModel;
use App\Models\Medicos\GuardarSecciones\DiagnosticoDefinitivoModel;
use App\Models\Medicos\GuardarSecciones\TratamientoModel;
use App\Models\Medicos\GuardarSecciones\EgresoEmergenciaModel;
use App\Models\Medicos\GuardarSecciones\ProfesionalResponsableModel;
use App\Models\Medicos\ListaMedicosModel;
use App\Models\FormularioUsuarioModel;
use App\Models\Especialidades\EspecialidadModel;
use App\Models\Especialidades\AreaAtencionModel;
use App\Models\Especialidades\ObservacionEspecialidadModel;
use App\Models\Administrador\ModificacionesModel;

//aqui solo se guardara las secciones para el especialista q seria E,F,H,I,J,K,L,M,O,P

class EspecialidadController extends BaseController
{
    private function getFechaHoy()
    {
        $timezone = config('App')->appTimezone ?? 'America/Guayaquil';
        return (new \DateTime('now', new \DateTimeZone($timezone)))->format('Y-m-d');
    }

    private function getHoraActual()
    {
        $timezone = config('App')->appTimezone ?? 'America/Guayaquil';
        return (new \DateTime('now', new \DateTimeZone($timezone)))->format('H:i:s');
    }

    public function guardarFormulario()
    {
        if (!session()->get('logged_in')) {
            return redirect()->to('/login');
        }

        // SOLO rol_id 5 (ESPECIALISTA) puede usar esta funcionalidad
        if (session()->get('rol_id') != 5) {
            return redirect()->to('/especialidades/lista')->with('error', 'No tiene permisos para realizar esta acción.');
        }

        $are_codigo = $this->request->getPost('are_codigo');
        $ate_codigo = $this->request->getPost('ate_codigo');
        $esp_codigo = $this->request->getPost('esp_codigo');

        // Obtener el usu_id del especialista que realmente tomó la atención
        $db = \Config\Database::connect();
        $areaAtencion = $db->table('t_area_atencion')->where('are_codigo', $are_codigo)->get()->getRowArray();
        if (!$areaAtencion) {
            return redirect()->to('/especialidades/lista')->with('error', 'Área de atención no encontrada.');
        }
        $usu_id = $areaAtencion['are_medico_asignado']; // Este es el especialista que tomó la atención

        // VALIDACIONES
        if (empty($are_codigo) || empty($ate_codigo)) {
            return redirect()->to('/especialidades/lista')->with('error', 'Códigos de atención no válidos.');
        }

        // Verificar si el área ya está completada
        $areaCompletada = $db->table('t_area_atencion')
            ->where('are_codigo', $are_codigo)
            ->where('are_estado', 'COMPLETADA')
            ->get()
            ->getRowArray();

        if ($areaCompletada) {
            return redirect()->to('/especialidades/lista')->with('error', 'Esta atención ya fue completada. No se puede modificar.');
        }

        // Verificar si es una modificación habilitada ANTES de verificar si existe
        $modificacionesModel = new ModificacionesModel();
        $permisoAcceso = $modificacionesModel->verificarAccesoMedico($ate_codigo, $usu_id, 'ES');


        // Verificar si ya existe en formulario_usuario
        $formularioExistente = $db->table('t_formulario_usuario')
            ->where('ate_codigo', $ate_codigo)
            ->where('seccion', 'ES')
            ->get()
            ->getRowArray();

        // Permitir guardar si es modificación habilitada
        if ($formularioExistente) {
            $esModificacion = $formularioExistente['habilitado_por_admin'] == 1;

            if (!$esModificacion) {
                // No es modificación - no permitir guardar
                return redirect()->to('/especialidades/lista')
                    ->with('error', 'Este formulario de especialidad ya fue completado anteriormente.');
            } else {
                // Es modificación habilitada - permitir continuar
            }
        }

        // Verificar que el área existe y es accesible
        $areaAtencionModel = new AreaAtencionModel();
        $areaAtencion = $areaAtencionModel->find($are_codigo);

        if (!$areaAtencion) {
            return redirect()->to('/especialidades/lista')->with('error', 'Área de atención no encontrada.');
        }

        // Verificar acceso (permite tanto médicos asignados como modificaciones habilitadas)
        $tieneAcceso = false;
        $motivoAcceso = '';

        if ($areaAtencion['are_medico_asignado'] == $usu_id) {
            $tieneAcceso = true;
            $motivoAcceso = 'Médico asignado originalmente';
        } elseif ($areaAtencion['are_estado'] == 'PENDIENTE' && empty($areaAtencion['are_medico_asignado'])) {
            $tieneAcceso = true;
            $motivoAcceso = 'Atención disponible sin asignar';

            // Asignar automáticamente al usuario actual
            $areaAtencionModel->update($are_codigo, [
                'are_medico_asignado' => $usu_id,
                'are_estado' => 'EN_ATENCION',
                'are_fecha_inicio_atencion' => $this->getFechaHoy(),
                'are_hora_inicio_atencion' => $this->getHoraActual()
            ]);
        } elseif ($permisoAcceso['acceso']) {
            $tieneAcceso = true;
            $motivoAcceso = 'Acceso por modificación habilitada';
        }

        if (!$tieneAcceso) {
            return redirect()->to('/especialidades/lista')->with('error', 'No tiene permisos para esta atención.');
        }


        // 🔍 DEBUG - Ver todos los datos POST relacionados con tratamientos
        for ($i = 1; $i <= 7; $i++) {
            $med = $this->request->getPost("trat_med$i");
            $adm = $this->request->getPost("trat_administrado$i");
            if (!empty($med)) {
            }
        }

        $db->transStart();

        try {
            // GUARDAR SOLO LAS SECCIONES QUE PUEDE EDITAR EL ESPECIALISTA:
            $this->guardarSeccionE($ate_codigo, $usu_id);
            $this->guardarSeccionF($ate_codigo, $usu_id);
            $this->guardarSeccionH($ate_codigo, $usu_id);
            $this->guardarSeccionI($ate_codigo, $usu_id);
            $this->guardarSeccionJ($ate_codigo, $usu_id);
            $this->guardarSeccionK($ate_codigo, $usu_id);
            $this->guardarSeccionL($ate_codigo, $usu_id);
            $this->guardarSeccionM($ate_codigo, $usu_id);
            $this->guardarSeccionN($ate_codigo, $usu_id);
            $this->guardarSeccionO($ate_codigo, $usu_id);
            $this->guardarSeccionP($ate_codigo, $usu_id);

            // GUARDAR OBSERVACIONES FINALES DE LA ESPECIALIDAD
            $this->guardarObservacionesEspecialidad($are_codigo, $ate_codigo, $usu_id);

            // FINALIZAR EL ÁREA DE ATENCIÓN
            $this->finalizarAtencionEspecialidad($are_codigo, $usu_id);

            // Manejar modificación o primera vez
            if ($formularioExistente && $formularioExistente['habilitado_por_admin'] == 1) {
                // Es una modificación - registrar que se usó
                $modificacionesModel->registrarModificacionUsada($ate_codigo, 'ES');

                $mensajeExito = "🔄 ¡Modificación de especialidad completada con éxito! " .
                    "Los cambios han sido guardados correctamente. " .
                    "La modificación ha sido registrada como utilizada.";
            } else {
                // Es la primera vez - registrar como completado
                $this->registrarFormularioCompletadoEspecialista($ate_codigo, $are_codigo, $usu_id);

                $mensajeExito = "🎉 ¡Evaluación de especialidad completada con éxito! " .
                    "El paciente ha sido dado de alta de la especialidad. " .
                    "Todos los datos se han guardado correctamente en el sistema.";
            }

            $db->transComplete();

            if ($db->transStatus() === false) {
                throw new \Exception('Error en la transacción del formulario de especialidad');
            }

            return redirect()->to('/especialidades/lista')->with('mensaje', $mensajeExito);

        } catch (\Exception $e) {
            $db->transRollback();
            return redirect()->to('/especialidades/lista')->with('error', 'Error crítico al guardar: ' . $e->getMessage());
        }
    }

    private function guardarObservacionesEspecialidad($are_codigo, $ate_codigo, $usu_id)
    {
        $observacionesFinales = $this->request->getPost('observaciones_finales');

        if (!empty($observacionesFinales)) {
            $areaAtencionModel = new AreaAtencionModel();
            $areaAtencionModel->update($are_codigo, [
                'are_observaciones' => $observacionesFinales,
                'are_fecha_fin_atencion' => $this->getFechaHoy(),
                'are_hora_fin_atencion' => $this->getHoraActual()
            ]);
        }
    }

    /**
     * Finalizar la atención en el área de especialidad
     */
    private function finalizarAtencionEspecialidad($are_codigo, $usu_id)
    {
        $areaAtencionModel = new AreaAtencionModel();

        $updateData = [
            'are_estado' => 'COMPLETADA',
            'are_fecha_fin_atencion' => $this->getFechaHoy(),
            'are_hora_fin_atencion' => $this->getHoraActual()
        ];

        $resultado = $areaAtencionModel->update($are_codigo, $updateData);

        // Si es especialidad de observación, marcar observación como COMPLETADO
        if ($resultado) {
            $areaAtencion = $areaAtencionModel->find($are_codigo);
            if ($areaAtencion && $areaAtencion['esp_codigo'] == 5) {
                $this->marcarObservacionCompletada($areaAtencion['ate_codigo']);
            }
        }

        return $resultado;
    }


    /**
     * Registrar que el formulario fue completado por el especialista
     */
    private function registrarFormularioCompletadoEspecialista($ate_codigo, $are_codigo, $usu_id)
    {

        $modificacionesModel = new ModificacionesModel();

        // Verificar si ya existe registro
        $formularioExistente = $modificacionesModel
            ->where('ate_codigo', $ate_codigo)
            ->where('usu_id', $usu_id)
            ->where('seccion', 'ES')
            ->first();

        if ($formularioExistente) {
            // Es una modificación - usar modificación directa o habilitada
            if ($formularioExistente['habilitado_por_admin'] == 1) {
                // Modificación habilitada por admin
                $resultado = $modificacionesModel->registrarModificacionUsada($ate_codigo, 'ES');
            } else {
                // Modificación directa del mismo especialista
                $resultado = $modificacionesModel->usarModificacionDirecta($ate_codigo, 'ES', $usu_id);
            }
        } else {
            // Primera vez - crear registro con 3 oportunidades
            $resultado = $modificacionesModel->crearRegistroFormulario(
                $ate_codigo,
                $usu_id,
                'ES',
                "Completado en especialidad - are_codigo: $are_codigo - primera vez"
            );
        }

        if (!$resultado) {
            throw new \Exception("No se pudo registrar la finalización del formulario de especialidad");
        }

        return $resultado;
    }



    private function guardarSeccionE($ate_codigo, $usu_id)
    {
        $antecedenteModel = new AntecedentePacienteModel();
        $antecedenteModel->where('ate_codigo', $ate_codigo)->delete();

        $noAplica = $this->request->getPost('ant_no_aplica') ? 1 : 0;
        $antecedentesSeleccionados = $this->request->getPost('antecedentes') ?: [];
        foreach ($antecedentesSeleccionados as $tipoAntecedente) {
            // Inserta cada antecedente seleccionado
        }
        $descripcionAntecedentes = $this->request->getPost('ant_descripcion') ?: '';

        if ($noAplica == 1) {
            $antecedenteModel->insert([
                'ate_codigo' => $ate_codigo,
                'tan_codigo' => null,
                'ap_descripcion' => null,
                'ap_no_aplica' => 1
            ]);
        } else {
            if (!empty($antecedentesSeleccionados)) {
                foreach ($antecedentesSeleccionados as $tipoAntecedente) {
                    $antecedenteModel->insert([
                        'ate_codigo' => $ate_codigo,
                        'tan_codigo' => intval($tipoAntecedente),
                        'ap_descripcion' => $descripcionAntecedentes,
                        'ap_no_aplica' => 0
                    ]);
                }
            } else if (!empty($descripcionAntecedentes)) {
                $antecedenteModel->insert([
                    'ate_codigo' => $ate_codigo,
                    'tan_codigo' => 10,
                    'ap_descripcion' => $descripcionAntecedentes,
                    'ap_no_aplica' => 0
                ]);
            }
        }
    }

    private function guardarSeccionF($ate_codigo, $usu_id)
    {
        $problemaActualModel = new ProblemaActualModel();
        $problemaActualModel->where('ate_codigo', $ate_codigo)->delete();

        $descripcionProblema = $this->request->getPost('ep_descripcion_actual') ?: '';
        if (!empty($descripcionProblema)) {
            $problemaActualModel->insert([
                'ate_codigo' => $ate_codigo,
                'pro_descripcion' => $descripcionProblema
            ]);
        }
    }

    private function guardarSeccionH($ate_codigo, $usu_id)
    {
        $examenFisicoModel = new ExamenFisicoModel();
        $examenFisicoModel->where('ate_codigo', $ate_codigo)->delete();

        $zonasSeleccionadas = $this->request->getPost('zonas_examen_fisico') ?: [];
        foreach ($zonasSeleccionadas as $zona) {
            // Inserta cada zona seleccionada
        }
        $descripcionExamen = $this->request->getPost('ef_descripcion') ?: '';

        if (!empty($zonasSeleccionadas)) {
            foreach ($zonasSeleccionadas as $zona) {
                $examenFisicoModel->insert([
                    'ate_codigo' => $ate_codigo,
                    'zef_codigo' => intval($zona),
                    'ef_presente' => true,
                    'ef_descripcion' => $descripcionExamen
                ]);
            }
        } else if (!empty($descripcionExamen)) {
            $examenFisicoModel->insert([
                'ate_codigo' => $ate_codigo,
                'zef_codigo' => 1,
                'ef_presente' => false,
                'ef_descripcion' => $descripcionExamen
            ]);
        }
    }

    private function guardarSeccionI($ate_codigo, $usu_id)
    {
        $examenTraumaModel = new ExamenTraumaModel();
        $examenTraumaModel->where('ate_codigo', $ate_codigo)->delete();

        $descripcionTrauma = $this->request->getPost('eft_descripcion') ?: '';
        if (!empty($descripcionTrauma)) {
            $examenTraumaModel->insert([
                'ate_codigo' => $ate_codigo,
                'tra_descripcion' => $descripcionTrauma
            ]);
        }
    }
    private function guardarSeccionJ($ate_codigo, $usu_id)
    {

        $embarazoPartoModel = new EmbarazoPartoModel();
        $embarazoPartoModel->where('ate_codigo', $ate_codigo)->delete();

        // CAPTURAR CORRECTAMENTE EL CHECKBOX "NO APLICA"
        $noAplica = $this->request->getPost('emb_no_aplica') ? 1 : 0;


        // Solo insertar si marcó "No aplica" O si hay datos reales
        $hayDatos = false;

        if ($noAplica == 1) {
            // Si marca "No aplica", insertar registro
            $hayDatos = true;
        } else {
            // Verificar si hay al menos un campo con datos
            $campos = [
                'emb_gestas',
                'emb_partos',
                'emb_abortos',
                'emb_cesareas',
                'emb_fum',
                'emb_semanas_gestacion',
                'emb_movimiento_fetal',
                'emb_fcf',
                'emb_ruptura_membranas',
                'emb_tiempo_ruptura',
                'emb_afu',
                'emb_presentacion',
                'emb_sangrado_vaginal',
                'emb_contracciones',
                'emb_dilatacion',
                'emb_borramiento',
                'emb_plano',
                'emb_pelvis_viable',
                'emb_score_mama',
                'emb_observaciones'
            ];

            foreach ($campos as $campo) {
                $valor = $this->request->getPost($campo);
                if (!empty($valor) && $valor !== '0' && $valor !== '0000-00-00') {
                    $hayDatos = true;
                    break;
                }
            }
        }

        // Solo insertar si hay datos reales
        if ($hayDatos) {
            $data = [
                'ate_codigo' => $ate_codigo,
                'emb_no_aplica' => $noAplica,
                'emb_numero_gestas' => $noAplica ? null : $this->request->getPost('emb_gestas'),
                'emb_numero_partos' => $noAplica ? null : $this->request->getPost('emb_partos'),
                'emb_numero_abortos' => $noAplica ? null : $this->request->getPost('emb_abortos'),
                'emb_numero_cesareas' => $noAplica ? null : $this->request->getPost('emb_cesareas'),
                'emb_fum' => $noAplica ? null : $this->request->getPost('emb_fum'),
                'emb_semanas_gestacion' => $noAplica ? null : $this->request->getPost('emb_semanas_gestacion'),
                'emb_movimiento_fetal' => $noAplica ? null : $this->request->getPost('emb_movimiento_fetal'),
                'emb_frecuencia_cardiaca_fetal' => $noAplica ? null : $this->request->getPost('emb_fcf'),
                'emb_ruptura_menbranas' => $noAplica ? null : $this->request->getPost('emb_ruptura_membranas'),
                'emb_tiempo' => $noAplica ? null : $this->request->getPost('emb_tiempo_ruptura'),
                'emb_afu' => $noAplica ? null : $this->request->getPost('emb_afu'),
                'emb_presentacion' => $noAplica ? null : $this->request->getPost('emb_presentacion'),
                'emb_sangrado_vaginal' => $noAplica ? null : $this->request->getPost('emb_sangrado_vaginal'),
                'emb_contracciones' => $noAplica ? null : $this->request->getPost('emb_contracciones'),
                'emb_dilatacion' => $noAplica ? null : $this->request->getPost('emb_dilatacion'),
                'emb_borramiento' => $noAplica ? null : $this->request->getPost('emb_borramiento'),
                'emb_plano' => $noAplica ? null : $this->request->getPost('emb_plano'),
                'emb_pelvis_viable' => $noAplica ? null : $this->request->getPost('emb_pelvis_viable'),
                'emb_score_mama' => $noAplica ? null : $this->request->getPost('emb_score_mama'),
                'emb_observaciones' => $noAplica ? null : $this->request->getPost('emb_observaciones')
            ];

            $resultado = $embarazoPartoModel->insert($data);
        } else {
        }
    }

    private function guardarSeccionK($ate_codigo, $usu_id)
    {

        $examenesComplementariosModel = new ExamenesComplementariosModel();
        $examenesComplementariosModel->where('ate_codigo', $ate_codigo)->delete();

        // CAPTURAR CORRECTAMENTE LOS DATOS
        $noAplica = $this->request->getPost('exc_no_aplica') ? 1 : 0;
        $tiposExamenes = $this->request->getPost('tipos_examenes') ?: [];
        foreach ($tiposExamenes as $tipoExamen) {
            // Inserta cada examen seleccionado
        }
        $observaciones = $this->request->getPost('exc_observaciones') ?: '';


        if ($noAplica == 1) {
            // Si marca "No aplica", guardar solo este registro
            $resultado = $examenesComplementariosModel->insert([
                'ate_codigo' => $ate_codigo,
                'tipo_id' => null,
                'exa_no_aplica' => 1,
                'exa_observaciones' => null
            ]);
        } else {
            // Si NO marca "No aplica", guardar los exámenes seleccionados
            if (!empty($tiposExamenes)) {
                foreach ($tiposExamenes as $tipoExamen) {
                    $resultado = $examenesComplementariosModel->insert([
                        'ate_codigo' => $ate_codigo,
                        'tipo_id' => intval($tipoExamen), // IMPORTANTE: Usar el valor del checkbox
                        'exa_no_aplica' => 0,
                        'exa_observaciones' => $observaciones
                    ]);
                }
            } else if (!empty($observaciones)) {
                // Si hay observaciones pero no hay exámenes seleccionados
                $resultado = $examenesComplementariosModel->insert([
                    'ate_codigo' => $ate_codigo,
                    'tipo_id' => 16, // "Otros"
                    'exa_no_aplica' => 0,
                    'exa_observaciones' => $observaciones
                ]);
            }
        }
    }
    private function guardarSeccionL($ate_codigo, $usu_id)
    {

        $diagnosticoPresuntivoModel = new DiagnosticoPresuntivoModel();
        $diagnosticoPresuntivoModel->where('ate_codigo', $ate_codigo)->delete();

        $hayDiagnosticos = false;

        for ($i = 1; $i <= 3; $i++) {
            $descripcion = $this->request->getPost("diag_pres_desc$i");
            $cie = $this->request->getPost("diag_pres_cie$i");

            // Solo insertar si hay descripción
            if (!empty($descripcion)) {
                $resultado = $diagnosticoPresuntivoModel->insert([
                    'ate_codigo' => $ate_codigo,
                    'diagp_descripcion' => $descripcion,
                    'diagp_cie' => !empty($cie) ? $cie : null
                ]);

                if ($resultado) {
                    $hayDiagnosticos = true;
                }
            }
        }

        // Si no hay diagnósticos específicos, insertar registro por defecto
        if (!$hayDiagnosticos) {
            $diagnosticoPresuntivoModel->insert([
                'ate_codigo' => $ate_codigo,
                'diagp_descripcion' => null,
                'diagp_cie' => null
            ]);
        }

        return true;
    }

    // === SECCIÓN M: DIAGNÓSTICOS DEFINITIVOS MEJORADA ===
    private function guardarSeccionM($ate_codigo, $usu_id)
    {

        $diagnosticoDefinitivoModel = new DiagnosticoDefinitivoModel();
        $diagnosticoDefinitivoModel->where('ate_codigo', $ate_codigo)->delete();

        $hayDiagnosticos = false;

        for ($i = 1; $i <= 3; $i++) {
            $descripcion = $this->request->getPost("diag_def_desc$i");
            $cie = $this->request->getPost("diag_def_cie$i");

            // Solo insertar si hay descripción
            if (!empty($descripcion)) {
                $resultado = $diagnosticoDefinitivoModel->insert([
                    'ate_codigo' => $ate_codigo,
                    'diagd_descripcion' => $descripcion,
                    'diagd_cie' => !empty($cie) ? $cie : null
                ]);

                if ($resultado) {
                    $hayDiagnosticos = true;
                }
            }
        }

        // Si no hay diagnósticos específicos, insertar registro por defecto
        if (!$hayDiagnosticos) {
            $diagnosticoDefinitivoModel->insert([
                'ate_codigo' => $ate_codigo,
                'diagd_descripcion' => null,
                'diagd_cie' => null
            ]);
        }

        return true;
    }

    // === SECCIÓN N: TRATAMIENTO MEJORADA ===
    private function guardarSeccionN($ate_codigo, $usu_id)
    {

        $tratamientoModel = new TratamientoModel();

        // PRESERVAR estado de trat_administrado - IGUAL QUE ENFERMERÍA ESPECIALIDAD
        $tratamientosExistentes = $tratamientoModel->where('ate_codigo', $ate_codigo)->findAll();
        $tratamientosExistentesPorMedicamento = [];


        foreach ($tratamientosExistentes as $trat) {
            if (!empty($trat['trat_medicamento'])) {
                $tratamientosExistentesPorMedicamento[$trat['trat_medicamento']] = $trat;
            }
        }

        // Capturar las observaciones generales del textarea
        $observacionesGenerales = $this->request->getPost('plan_tratamiento') ?: '';

        $hayTratamientos = false;
        $medicamentosEnFormulario = [];

        // Procesar cada tratamiento (1 al 7) - IGUAL QUE ENFERMERÍA ESPECIALIDAD
        for ($i = 1; $i <= 7; $i++) {
            $medicamento = $this->request->getPost("trat_med$i");
            $via = $this->request->getPost("trat_via$i");
            $dosis = $this->request->getPost("trat_dosis$i");
            $posologia = $this->request->getPost("trat_posologia$i");
            $dias = $this->request->getPost("trat_dias$i");

            // Solo procesar si hay al menos el medicamento
            if (!empty($medicamento)) {
                $medicamentosEnFormulario[] = $medicamento;

                // PRESERVAR estado administrado del tratamiento existente O del POST
                $tratamientoExistente = $tratamientosExistentesPorMedicamento[$medicamento] ?? null;
                $administradoPost = intval($this->request->getPost("trat_administrado$i") ?: 0);


                // Si existe el tratamiento, mantener su estado de administrado, sino usar el del POST
                $administrado = $tratamientoExistente ? intval($tratamientoExistente['trat_administrado']) : $administradoPost;

                $datosTratamiento = [
                    'ate_codigo' => $ate_codigo,
                    'trat_medicamento' => $medicamento,
                    'trat_via' => !empty($via) ? $via : null,
                    'trat_dosis' => !empty($dosis) ? $dosis : null,
                    'trat_posologia' => !empty($posologia) ? $posologia : null,
                    'trat_dias' => !empty($dias) ? intval($dias) : null,
                    'trat_observaciones' => $observacionesGenerales,
                    'trat_administrado' => $administrado
                ];

                if ($tratamientoExistente) {
                    // ACTUALIZAR registro existente (mantiene el mismo trat_id)
                    $resultado = $tratamientoModel->update($tratamientoExistente['trat_id'], $datosTratamiento);
                } else {
                    // INSERTAR nuevo registro solo si no existe
                    $resultado = $tratamientoModel->insert($datosTratamiento);
                }

                if ($resultado) {
                    $hayTratamientos = true;
                }
            }
        }

        // ELIMINAR tratamientos que ya no están en el formulario
        foreach ($tratamientosExistentesPorMedicamento as $medicamento => $tratamiento) {
            if (!in_array($medicamento, $medicamentosEnFormulario) && $medicamento !== 'Plan de tratamiento') {
                $tratamientoModel->delete($tratamiento['trat_id']);
            }
        }

        // MANEJAR "Plan de tratamiento" de la misma forma que enfermería
        if (!$hayTratamientos && !empty($observacionesGenerales) && trim($observacionesGenerales) !== '') {
            $planExistente = $tratamientosExistentesPorMedicamento['Plan de tratamiento'] ?? null;

            $datosPlan = [
                'ate_codigo' => $ate_codigo,
                'trat_medicamento' => null,
                'trat_via' => null,
                'trat_dosis' => null,
                'trat_posologia' => null,
                'trat_dias' => null,
                'trat_observaciones' => $observacionesGenerales,
                'trat_administrado' => 0
            ];

            if ($planExistente) {
                // Actualizar plan existente
                $resultado = $tratamientoModel->update($planExistente['trat_id'], $datosPlan);
            } else {
                // Insertar nuevo plan
                $resultado = $tratamientoModel->insert($datosPlan);
            }
        } else if (empty($observacionesGenerales) && isset($tratamientosExistentesPorMedicamento['Plan de tratamiento'])) {
            // Si no hay observaciones pero existía un plan, eliminarlo
            $planExistente = $tratamientosExistentesPorMedicamento['Plan de tratamiento'];
            $tratamientoModel->delete($planExistente['trat_id']);
        }

        return true;
    }

    private function guardarSeccionO($ate_codigo, $usu_id)
    {

        $egresoEmergenciaModel = new EgresoEmergenciaModel();
        $egresoEmergenciaModel->where('ate_codigo', $ate_codigo)->delete();

        // Verificar si fue enviado a observación
        $observacionModel = new ObservacionEspecialidadModel();
        $envioObservacion = $observacionModel
            ->where('ate_codigo', $ate_codigo)
            ->where('obs_estado', 'ENVIADO_A_OBSERVACION')
            ->first();

        // CORRECCIÓN: Obtener TODOS los valores seleccionados, no solo el primero
        $estadosEgreso = $this->request->getPost('estados_egreso') ?: [];
        $modalidadesEgreso = $this->request->getPost('modalidades_egreso') ?: [];
        $tiposEgreso = $this->request->getPost('tipos_egreso') ?: [];

        // Datos comunes para todos los registros
        $datosComunes = [
            'egr_establecimiento' => $this->request->getPost('egreso_establecimiento'),
            'egr_observaciones' => $this->request->getPost('egreso_observacion'),
            'egr_dias_reposo' => $this->request->getPost('egreso_dias_reposo') ?: 0,
            'egr_observacion_emergencia' => $envioObservacion ? 1 : 0
        ];


        // NUEVA LÓGICA: Crear todas las combinaciones necesarias
        $registrosCreados = 0;

        // Si no hay selecciones, crear un registro básico
        if (empty($estadosEgreso) && empty($modalidadesEgreso) && empty($tiposEgreso)) {
            $data = array_merge($datosComunes, [
                'ate_codigo' => $ate_codigo,
                'ese_codigo' => null,
                'moe_codigo' => $envioObservacion ? 3 : null, // 3 = OBSERVACIÓN DE EMERGENCIA
                'tie_codigo' => null
            ]);

            $resultado = $egresoEmergenciaModel->insert($data);
            if ($resultado)
                $registrosCreados++;

        } else {
            // Crear registros para cada combinación seleccionada

            // Prioridad 1: Si hay estados de egreso seleccionados
            if (!empty($estadosEgreso)) {
                foreach ($estadosEgreso as $estadoCodigo) {
                    $data = array_merge($datosComunes, [
                        'ate_codigo' => $ate_codigo,
                        'ese_codigo' => intval($estadoCodigo),
                        'moe_codigo' => $envioObservacion ? 3 : null,
                        'tie_codigo' => null
                    ]);

                    $resultado = $egresoEmergenciaModel->insert($data);
                    if ($resultado)
                        $registrosCreados++;

                }
            }

            // Prioridad 2: Si hay modalidades de egreso seleccionadas
            if (!empty($modalidadesEgreso)) {
                foreach ($modalidadesEgreso as $modalidadCodigo) {
                    // Si fue enviado a observación, asegurarse que modalidad 3 esté incluida
                    if ($envioObservacion && $modalidadCodigo != 3) {
                        continue; // Solo permitir modalidad 3 si fue enviado a observación
                    }

                    $data = array_merge($datosComunes, [
                        'ate_codigo' => $ate_codigo,
                        'ese_codigo' => null,
                        'moe_codigo' => intval($modalidadCodigo),
                        'tie_codigo' => null
                    ]);

                    $resultado = $egresoEmergenciaModel->insert($data);
                    if ($resultado)
                        $registrosCreados++;

                }
            }

            // Prioridad 3: Si hay tipos de egreso seleccionados
            if (!empty($tiposEgreso)) {
                foreach ($tiposEgreso as $tipoCodigo) {
                    $data = array_merge($datosComunes, [
                        'ate_codigo' => $ate_codigo,
                        'ese_codigo' => null,
                        'moe_codigo' => $envioObservacion ? 3 : null,
                        'tie_codigo' => intval($tipoCodigo)
                    ]);

                    $resultado = $egresoEmergenciaModel->insert($data);
                    if ($resultado)
                        $registrosCreados++;

                }
            }

            // CASO ESPECIAL: Si fue enviado a observación pero no se marcó modalidad 3
            if ($envioObservacion && !in_array('3', $modalidadesEgreso)) {
                $data = array_merge($datosComunes, [
                    'ate_codigo' => $ate_codigo,
                    'ese_codigo' => null,
                    'moe_codigo' => 3, // Forzar modalidad observación
                    'tie_codigo' => null
                ]);

                $resultado = $egresoEmergenciaModel->insert($data);
                if ($resultado)
                    $registrosCreados++;

            }
        }


        return $registrosCreados > 0;
    }

    private function guardarSeccionP($ate_codigo, $usu_id)
    {
        $profesionalResponsableModel = new ProfesionalResponsableModel();

        // Obtener datos existentes antes de eliminar
        $datosExistentes = $profesionalResponsableModel->where('ate_codigo', $ate_codigo)->first();

        $profesionalResponsableModel->where('ate_codigo', $ate_codigo)->delete();

        // Manejar subida de archivos o mantener existentes
        $rutaFirma = $this->subirArchivoFirma('prof_firma', 'firmas');
        $rutaSello = $this->subirArchivoFirma('prof_sello', 'sellos');

        // Si no se subió nuevo archivo, mantener el existente
        if (!$rutaFirma && $datosExistentes && $datosExistentes['pro_firma']) {
            $rutaFirma = $datosExistentes['pro_firma'];
        }

        if (!$rutaSello && $datosExistentes && $datosExistentes['pro_sello']) {
            $rutaSello = $datosExistentes['pro_sello'];
        }

        $data = [
            'ate_codigo' => $ate_codigo,
            'pro_fecha' => $this->request->getPost('prof_fecha') ?: $this->getFechaHoy(),
            'pro_hora' => $this->request->getPost('prof_hora') ?: $this->getHoraActual(),
            'pro_primer_nombre' => $this->request->getPost('prof_primer_nombre') ?: '',
            'pro_primer_apellido' => $this->request->getPost('prof_primer_apellido') ?: '',
            'pro_segundo_apellido' => $this->request->getPost('prof_segundo_apellido') ?: '',
            'pro_nro_documento' => $this->request->getPost('prof_documento') ?: '',
            'pro_firma' => $rutaFirma,
            'pro_sello' => $rutaSello,
            'pro_firma_tipo' => $rutaFirma ? pathinfo($rutaFirma, PATHINFO_EXTENSION) : null,
            'pro_sello_tipo' => $rutaSello ? pathinfo($rutaSello, PATHINFO_EXTENSION) : null
        ];

        return $profesionalResponsableModel->insert($data);
    }

    // Método auxiliar para subir archivos
    private function subirArchivoFirma($nombreCampo, $carpeta)
    {
        $archivo = $this->request->getFile($nombreCampo);

        if ($archivo && $archivo->isValid() && !$archivo->hasMoved()) {
            // Validar que sea una imagen
            if (!in_array($archivo->getMimeType(), ['image/png', 'image/jpeg', 'image/jpg'])) {
                return null;
            }

            // Validar tamaño (2MB máximo)
            if ($archivo->getSizeByUnit('mb') > 2) {
                return null;
            }

            // Crear directorio si no existe
            $rutaCarpeta = ROOTPATH . 'uploads/' . $carpeta . '/';
            if (!is_dir($rutaCarpeta)) {
                mkdir($rutaCarpeta, 0755, true);
            }

            // Generar nombre único para el archivo
            $nombreArchivo = $carpeta . '_' . time() . '_' . random_int(1000, 9999) . '.' . $archivo->getExtension();
            $rutaCompleta = $rutaCarpeta . $nombreArchivo;

            // Mover el archivo
            if ($archivo->move($rutaCarpeta, $nombreArchivo)) {
                return 'uploads/' . $carpeta . '/' . $nombreArchivo; // Ruta relativa para la BD
            } else {
                return null;
            }
        }

        return null;
    }

    public function verificarDisponibilidad($are_codigo)
    {
        if (!session()->get('logged_in')) {
            return $this->response->setJSON(['error' => 'No autorizado']);
        }

        if (session()->get('rol_id') != 5) {
            return $this->response->setJSON(['error' => 'Sin permisos']);
        }

        try {
            $areaAtencionModel = new AreaAtencionModel();
            $resultado = $areaAtencionModel->verificarDisponibilidadPaciente($are_codigo);

            return $this->response->setJSON($resultado);

        } catch (\Exception $e) {
            return $this->response->setJSON([
                'disponible' => false,
                'mensaje' => 'Error al verificar disponibilidad'
            ]);
        }
    }

    /**
     * Tomar atención de un paciente en especialidad
     */
    public function tomarAtencion($are_codigo)
    {
        if (!session()->get('logged_in')) {
            return redirect()->to('/login');
        }

        if (session()->get('rol_id') != 5) {
            return redirect()->to('/especialidades')->with('error', 'Sin permisos para esta acción');
        }

        try {
            $areaAtencionModel = new AreaAtencionModel();

            // CRÍTICO: El médico que toma la atención es el de la sesión actual
            $medico_actual_id = session()->get('usu_id');

            // Verificar disponibilidad
            $disponibilidad = $areaAtencionModel->verificarDisponibilidadPaciente($are_codigo);

            if (!$disponibilidad['disponible']) {
                return redirect()->to('/especialidades')->with('error', $disponibilidad['mensaje']);
            }

            // USAR EL MÉTODO DEL MODEL QUE YA TIENES
            $resultado = $areaAtencionModel->tomarAtencion($are_codigo, $medico_actual_id);

            if ($resultado) {

                // Redireccionar al formulario de esa atención
                return redirect()->to("/especialidades/formulario/$are_codigo")
                    ->with('success', 'Atención asignada correctamente');
            } else {
                return redirect()->to('/especialidades')->with('error', 'Error al tomar la atención');
            }

        } catch (\Exception $e) {
            return redirect()->to('/especialidades')->with('error', $e->getMessage());
        }
    }

    /**
     * Validar contraseña para continuar atención (CORREGIDA PARA MODIFICACIONES)
     */
    /**
     * Validar contraseña para continuar atención - CORREGIDO para distinguir casos
     */
    public function validarContrasena()
    {
        if (!session()->get('logged_in')) {
            return $this->response->setJSON(['success' => false, 'error' => 'No autorizado']);
        }

        if (session()->get('rol_id') != 5) {
            return $this->response->setJSON(['success' => false, 'error' => 'Sin permisos']);
        }

        if ($this->request->getMethod() !== 'POST') {
            return $this->response->setJSON(['success' => false, 'error' => 'Método no permitido']);
        }

        $are_codigo = $this->request->getPost('are_codigo');
        $password = $this->request->getPost('password');

        if (!$are_codigo || !$password) {
            return $this->response->setJSON(['success' => false, 'error' => 'Datos incompletos']);
        }

        try {
            $db = \Config\Database::connect();
            $modificacionesModel = new ModificacionesModel();

            // Obtener el área de atención
            $areaAtencionModel = new AreaAtencionModel();
            $areaAtencion = $areaAtencionModel->find($are_codigo);

            if (!$areaAtencion || !$areaAtencion['are_medico_asignado']) {
                return $this->response->setJSON([
                    'success' => false,
                    'error' => 'No se encontró médico asignado a esta atención'
                ]);
            }

            $ate_codigo = $areaAtencion['ate_codigo'];

            // Determinar qué tipo de validación hacer
            $esModificacion = $modificacionesModel->verificarAccesoMedico($ate_codigo, session()->get('usu_id'), 'ES');

            $tipoValidacion = '';
            $medicoParaValidar = null;

            if ($esModificacion['acceso'] && isset($esModificacion['es_modificacion']) && $esModificacion['es_modificacion']) {
                // CASO 1: Es una modificación habilitada - validar contraseña del médico ORIGINAL
                $tipoValidacion = 'MODIFICACION';
                $medicoParaValidar = $areaAtencion['are_medico_asignado']; // Médico original asignado
            } else {
                // CASO 2: Atención normal - validar contraseña del médico que TIENE la atención
                $tipoValidacion = 'ATENCION_NORMAL';
                $medicoParaValidar = $areaAtencion['are_medico_asignado']; // Médico que tiene la atención
            }

            // Obtener datos del médico a validar
            $medicoAValidar = $db->table('t_usuario')
                ->where('usu_id', $medicoParaValidar)
                ->get()
                ->getRowArray();

            if (!$medicoAValidar) {
                return $this->response->setJSON([
                    'success' => false,
                    'error' => 'Médico no encontrado en el sistema'
                ]);
            }

            // Validar contraseña
            $passwordHash = hash('sha256', $password);

            if ($medicoAValidar['usu_password'] !== $passwordHash) {
                // Registrar intento fallido
                $motivoFallo = $tipoValidacion === 'MODIFICACION' ?
                    "Intento de modificación con contraseña incorrecta del médico original: " . $medicoAValidar['usu_nombre'] :
                    "Intento de acceso con contraseña incorrecta del médico asignado: " . $medicoAValidar['usu_nombre'];

                $this->registrarLogCambio(
                    't_area_atencion',
                    $are_codigo,
                    'INTENTO_ACCESO_FALLIDO',
                    session()->get('usu_id'),
                    $motivoFallo
                );

                $mensajeError = $tipoValidacion === 'MODIFICACION' ?
                    'Contraseña incorrecta del médico que atendió originalmente al paciente.' :
                    'Contraseña incorrecta del médico asignado a esta atención.';

                return $this->response->setJSON([
                    'success' => false,
                    'error' => $mensajeError
                ]);
            }

            // CONTRASEÑA CORRECTA - PERMITIR ACCESO

            // Registrar acceso validado
            $usuarioActual = session()->get('usu_nombre') . ' ' . session()->get('usu_apellido');

            if ($tipoValidacion === 'MODIFICACION') {
                $accion = 'MODIFICACION_VALIDADA';
                $descripcion = "Acceso para modificación autorizado con contraseña del médico original {$medicoAValidar['usu_nombre']} - Accedido por: {$usuarioActual}";
                $mensaje = 'Validación exitosa. Acceso autorizado para modificación.';
            } else {
                $accion = 'ACCESO_VALIDADO';
                $descripcion = "Acceso autorizado con contraseña del médico asignado {$medicoAValidar['usu_nombre']} - Accedido por: {$usuarioActual}";
                $mensaje = 'Validación exitosa. Acceso autorizado.';
            }

            $this->registrarLogCambio(
                't_area_atencion',
                $are_codigo,
                $accion,
                session()->get('usu_id'),
                $descripcion
            );

            return $this->response->setJSON([
                'success' => true,
                'message' => $mensaje,
                'redirect_url' => base_url("especialidades/formulario/{$are_codigo}"),
                'tipo_validacion' => $tipoValidacion
            ]);

        } catch (\Exception $e) {
            return $this->response->setJSON([
                'success' => false,
                'error' => 'Error de validación: ' . $e->getMessage()
            ]);
        }
    }

    public function tomarAtencionConCredenciales()
    {
        if ($this->request->getMethod() !== 'POST') {
            return $this->response->setJSON(['success' => false, 'error' => 'Método no permitido']);
        }

        $are_codigo = $this->request->getPost('are_codigo');
        $usuario = $this->request->getPost('usuario');
        $password = $this->request->getPost('password');

        if (!$are_codigo || !$usuario || !$password) {
            return $this->response->setJSON(['success' => false, 'error' => 'Todos los campos son obligatorios']);
        }

        try {
            $db = \Config\Database::connect();

            // Buscar especialista por usuario
            $especialista = $db->table('t_usuario')
                ->where('usu_usuario', $usuario)
                ->where('rol_id', 5) // Solo especialistas
                ->where('usu_estado', 'activo')
                ->get()
                ->getRowArray();

            if (!$especialista) {
                return $this->response->setJSON([
                    'success' => false,
                    'error' => 'Usuario no encontrado o no es especialista activo'
                ]);
            }

            // Validar contraseña con SHA256
            $passwordHash = hash('sha256', $password);
            if ($especialista['usu_password'] !== $passwordHash) {
                return $this->response->setJSON([
                    'success' => false,
                    'error' => 'Contraseña incorrecta'
                ]);
            }

            // Verificar que el área existe
            $areaAtencionModel = new AreaAtencionModel();
            $areaAtencion = $areaAtencionModel->find($are_codigo);

            if (!$areaAtencion) {
                return $this->response->setJSON([
                    'success' => false,
                    'error' => 'Área de atención no encontrada'
                ]);
            }

            // Permitir tanto PENDIENTE como ENVIADO_A_OBSERVACION
            $estadosPermitidos = ['PENDIENTE', 'ENVIADO_A_OBSERVACION'];

            if (!in_array($areaAtencion['are_estado'], $estadosPermitidos)) {
                return $this->response->setJSON([
                    'success' => false,
                    'error' => 'Esta atención ya no está disponible'
                ]);
            }

            // Verificar si ya está siendo atendida por otro médico
            if ($areaAtencion['are_estado'] === 'EN_ATENCION' && $areaAtencion['are_medico_asignado'] != $especialista['usu_id']) {
                return $this->response->setJSON([
                    'success' => false,
                    'error' => 'Esta atención ya está siendo tomada por otro médico'
                ]);
            }

            // Tomar la atención
            $resultado = $areaAtencionModel->update($are_codigo, [
                'are_medico_asignado' => $especialista['usu_id'],
                'are_estado' => 'EN_ATENCION',
                'are_fecha_inicio_atencion' => $this->getFechaHoy(),
                'are_hora_inicio_atencion' => $this->getHoraActual()
            ]);

            if ($resultado) {
                // Registrar en log
                $nombreCompleto = $especialista['usu_nombre'] . ' ' . $especialista['usu_apellido'];
                $this->registrarLogCambio(
                    't_area_atencion',
                    $are_codigo,
                    'ATENCION_TOMADA',
                    $especialista['usu_id'],
                    "Especialista $nombreCompleto tomó la atención con credenciales"
                );


                // Si es especialidad de observación (esp_codigo = 5), actualizar registro de observación
                if ($areaAtencion['esp_codigo'] == 5) {
                    $this->actualizarRegistroObservacionRecibida($areaAtencion['ate_codigo'], $especialista['usu_id']);
                }

                return $this->response->setJSON([
                    'success' => true,
                    'message' => "Atención tomada por: $nombreCompleto",
                    'redirect_url' => base_url("especialidades/formulario/{$are_codigo}")
                ]);
            } else {
                return $this->response->setJSON([
                    'success' => false,
                    'error' => 'Error al tomar la atención'
                ]);
            }

        } catch (\Exception $e) {
            return $this->response->setJSON([
                'success' => false,
                'error' => 'Error del sistema'
            ]);
        }
    }


    private function registrarLogCambio($tabla, $registro_id, $accion, $usuario_id, $descripcion, $datos_anteriores = null, $datos_nuevos = null)
    {
        try {
            $db = \Config\Database::connect();

            $data = [
                'tabla_afectada' => $tabla,
                'registro_id' => $registro_id,
                'accion' => $accion,
                'usuario_id' => $usuario_id,
                'descripcion' => $descripcion,
                'datos_anteriores' => $datos_anteriores ? json_encode($datos_anteriores) : null,
                'datos_nuevos' => $datos_nuevos ? json_encode($datos_nuevos) : null,
                'ip_address' => $this->request->getIPAddress(),
                'user_agent' => substr($this->request->getUserAgent(), 0, 500),
                'fecha_cambio' => date('Y-m-d H:i:s')
            ];

            $db->table('t_log_cambios')->insert($data);

        } catch (\Exception $e) {
            // No lanzar excepción para no interrumpir el flujo principal
        }
    }

    /**
     * Actualizar registro de observación cuando un médico toma la atención
     * Basado en el patrón de enfermería de especialidad
     */
    private function actualizarRegistroObservacionRecibida($ate_codigo, $usu_id_recibe)
    {
        try {

            $observacionModel = new ObservacionEspecialidadModel();

            // Buscar el registro de observación activo para esta atención (igual que enfermería)
            $registroObservacion = $observacionModel->where([
                'ate_codigo' => $ate_codigo,
                'obs_estado' => 'ENVIADO_A_OBSERVACION'
            ])->first();

            if ($registroObservacion) {
                // Actualizar con los datos de recepción (igual que enfermería)
                $resultado = $observacionModel->update($registroObservacion['obs_codigo'], [
                    'obs_estado' => 'EN_ATENCION_OBSERVACION',
                    'obs_fecha_recepcion' => $this->getFechaHoy(),
                    'obs_hora_recepcion' => $this->getHoraActual(),
                    'usu_id_recibe' => $usu_id_recibe
                ]);

                if ($resultado) {
                } else {
                }
            } else {
            }

        } catch (\Exception $e) {
        }
    }

    /**
     * Marcar observación como completada cuando se finaliza la atención
     */
    private function marcarObservacionCompletada($ate_codigo)
    {
        try {

            $observacionModel = new ObservacionEspecialidadModel();

            // Buscar el registro de observación activo para esta atención
            $registroObservacion = $observacionModel->where([
                'ate_codigo' => $ate_codigo,
                'obs_estado' => 'EN_ATENCION_OBSERVACION'
            ])->first();

            if ($registroObservacion) {
                // Actualizar estado a COMPLETADO
                $resultado = $observacionModel->update($registroObservacion['obs_codigo'], [
                    'obs_estado' => 'COMPLETADO'
                ]);

                if ($resultado) {
                } else {
                }
            } else {
            }

        } catch (\Exception $e) {
        }
    }


}
