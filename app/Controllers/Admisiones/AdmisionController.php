<?php

namespace App\Controllers\Admisiones;

use App\Models\PacienteModel;
use App\Models\AtencionModel;
use App\Models\Admision\EstablecimientoRegistroModel;
use App\Models\Admision\PacienteHospitalModel;
use CodeIgniter\Controller;
use App\Controllers\BaseController;

class AdmisionController extends BaseController
{
    public function guardarAdmisiones()
    {
        $request = \Config\Services::request();
        $pacienteModel = new PacienteModel();
        $atencionModel = new AtencionModel();
        $registroModel = new EstablecimientoRegistroModel();
        $hospitalModel = new PacienteHospitalModel();

        // Capturar datos del formulario
        $estabArchivo = $this->generarNumeroArchivo();

        $estabCodigo = 1;    //el estab_codigo es fijo (1)
        $usuId = session()->get('usu_id');


        // Datos obligatorios
        $apellido1 = $request->getPost('pac_apellido1');
        $apellido2 = $request->getPost('pac_apellido2');
        $nombre1 = $request->getPost('pac_nombre1');
        $nombre2 = $request->getPost('pac_nombre2');

        if (empty($apellido1) || empty($nombre1)) {
            return redirect()->back()->with('error', '⚠️ Apellidos y Nombres son obligatorios.');
        }

        $formaLlegada = $request->getPost('forma_llegada');
        if (empty($formaLlegada)) {
            return redirect()->back()->with('error', '⚠️ La forma de llegada es obligatoria.');
        }

        // Obtener cédula del formulario
        $cedula = $request->getPost('estab_historia_clinica');

        // Si no hay cédula, generar una temporal
        if (empty($cedula) || trim($cedula) === '') {
            $cedula = 'N/A-' . random_int(1, 9999);
        }

        // BÚSQUEDA DE PACIENTE EXISTENTE
        $paciente = null;

        // 1. Buscar por historia clínica del buscador (campo oculto)
        $historiaClinica = $request->getPost('cod-historia');
        if (!empty($historiaClinica)) {
            $paciente = $pacienteModel->where('pac_his_cli', $historiaClinica)->first();
        }

        // 2. Buscar por cédula si es válida
        if (empty($paciente) && !empty($cedula) && $cedula !== 'N/A' && !str_contains($cedula, 'N/A-')) {
            $paciente = $pacienteModel->where('pac_cedula', $cedula)->first();
        }

        // 3. Si no se encontró por cédula, buscar por apellidos y nombres
        if (empty($paciente)) {
            $apellidos = trim($apellido1 . ' ' . $apellido2);
            $nombres = trim($nombre1 . ' ' . $nombre2);

            if (!empty($apellidos) && !empty($nombres)) {
                $paciente = $pacienteModel
                    ->where('pac_apellidos', $apellidos)
                    ->where('pac_nombres', $nombres)
                    ->first();
            }
        }

        if (!$paciente) {
            // Si el paciente no existe, insertamos un nuevo paciente y su atención
            $this->insertarPacienteYAtencion($estabArchivo, $estabCodigo, $usuId);
            return redirect()->back()->with('mensaje', '✅ Nuevo paciente y atención registrados correctamente.');
        } else {
            // Si el paciente ya existe, se actualizan sus datos y se crea la nueva atención
            $this->actualizarPacienteYAtencion($paciente['pac_codigo'], $estabArchivo, $estabCodigo, $usuId);
            return redirect()->back()->with('mensaje', '✅ Datos del paciente actualizados y nueva atención registrada.');
        }
    }

    private function insertarPacienteYAtencion($estabArchivo, $estabCodigo, $usuId)
    {
        $request = \Config\Services::request();

        $pacienteModel = new PacienteModel();

        $apellido1 = $request->getPost('pac_apellido1');
        $apellido2 = $request->getPost('pac_apellido2');
        $nombre1 = $request->getPost('pac_nombre1');
        $nombre2 = $request->getPost('pac_nombre2');
        $apellidos = trim($apellido1 . ' ' . $apellido2);
        $nombres = trim($nombre1 . ' ' . $nombre2);

        $cedula = $request->getPost('estab_historia_clinica') ?: ('TEMP-' . time() . '-' . random_int(100, 999));

        $nacCodigo = $request->getPost('pac_nacionalidad');

        $lugarNacimiento = null;
        $lugarNacProv = null;
        $lugarNacCant = null;
        $lugarNacParr = null;

        if ($nacCodigo == 1) {
            // Usar los nombres correctos de los campos del formulario
            $lugarNacProv = $request->getPost('nac_provincia');
            $lugarNacCant = $request->getPost('nac_canton');
            $lugarNacParr = $request->getPost('nac_parroquia');

            // Construir texto legible
            $lugarNacimiento = $this->construirLugarNacimiento(
                $lugarNacProv,
                $lugarNacCant,
                $lugarNacParr
            );
        } else {
            $lugarNacimiento = trim($request->getPost('pac_lugar_nacimiento'));
        }

        $data = [
            'pac_cedula' => $cedula,
            'pac_apellidos' => $apellidos,
            'pac_nombres' => $nombres,
            'pac_edad_valor' => $request->getPost('pac_edad_valor'),
            'pac_edad_unidad' => $request->getPost('pac_edad_unidad'),
            'pac_fecha_nac' => $request->getPost('pac_fecha_nacimiento'),
            'pac_lugar_nac' => $lugarNacimiento,
            // Asegurarse de que los códigos se guarden correctamente
            'prov_codigo' => !empty($lugarNacProv) ? $lugarNacProv : null,
            'cant_codigo' => !empty($lugarNacCant) ? $lugarNacCant : null,
            'parr_codigo' => !empty($lugarNacParr) ? $lugarNacParr : null,
            'pac_ocupacion' => $request->getPost('pac_ocupacion'),
            'pac_telefono_fijo' => $request->getPost('pac_telefono_fijo'),
            'pac_telefono_celular' => $request->getPost('pac_telefono_celular'),
            'pac_provincias' => $request->getPost('res_provincia'),
            'pac_cantones' => $request->getPost('res_canton'),
            'pac_parroquias' => $request->getPost('res_parroquia'),
            'pac_barrio' => $request->getPost('res_barrio_sector'),
            'pac_direccion' => $request->getPost('res_calle_principal'),
            'pac_calle_secundaria' => $request->getPost('res_calle_secundaria'),
            'pac_referencia' => $request->getPost('res_referencia'),
            'pac_avisar_a' => $request->getPost('contacto_emerg_nombre'),
            'pac_parentezco_avisar_a' => $request->getPost('contacto_emerg_parentesco'),
            'pac_direccion_avisar' => $request->getPost('contacto_emerg_direccion'),
            'pac_telefono_avisar_a' => $request->getPost('contacto_emerg_telefono'),
            'pac_grupo_prioritario' => $request->getPost('pac_grupo_prioritario') === 'si' ? 1 : 0,
            'pac_grupo_sanguineo' => $request->getPost('pac_grupo_prioritario_especifique'),
            'gen_codigo' => $request->getPost('pac_sexo'),
            'esc_codigo' => $request->getPost('pac_estado_civil'),
            'emp_codigo' => $request->getPost('pac_tipo_empresa'),
            'nac_codigo' => $request->getPost('pac_nacionalidad'),
            'gcu_codigo' => $request->getPost('pac_etnia'),
            'seg_codigo' => $request->getPost('pac_seguro'),
            'nedu_codigo' => $request->getPost('pac_nivel_educacion'),
            'eneduc_codigo' => $request->getPost('pac_estado_educacion'),
            'tdoc_codigo' => $request->getPost('pac_tipo_documento'),
            'nac_ind_codigo' => $request->getPost('pac_nacionalidad_indigena'),
            'pue_ind_codigo' => $request->getPost('pac_pueblo_indigena'),
        ];

        try {
            $resultado = $pacienteModel->insert($data);

            if ($resultado) {
                $pacienteId = $pacienteModel->getInsertID();
                $historiaAsignada = $this->manejarHistoriaClinica($pacienteId, $cedula);
                $ateCodigo = $this->guardarAtencion($pacienteId);

                if ($ateCodigo) {
                    $registroGuardado = $this->guardarEstablecimientoRegistro($estabArchivo, $estabCodigo, $ateCodigo, $usuId);
                }
            }
        } catch (\Exception $e) {
            log_message('error', 'Excepción crítica al insertar paciente: ' . $e->getMessage());
            throw $e;
        }
    }

    private function construirLugarNacimiento($provCodigo, $cantCodigo, $parrCodigo)
    {
        if (empty($provCodigo)) {
            return null;
        }

        $provinciaModel = new \App\Models\Admision\GuardarSecciones\ProvinciaModel();
        $cantonModel = new \App\Models\Admision\GuardarSecciones\CantonModel();
        $parroquiaModel = new \App\Models\Admision\GuardarSecciones\ParroquiaModel();

        $provincia = $provinciaModel->find($provCodigo);
        $canton = !empty($cantCodigo) ? $cantonModel->find($cantCodigo) : null;
        $parroquia = !empty($parrCodigo) ? $parroquiaModel->find($parrCodigo) : null;

        $partes = ['ECUADOR'];
        if ($provincia) $partes[] = $provincia['prov_nombre'];
        if ($canton) $partes[] = $canton['cant_nombre'];
        if ($parroquia) $partes[] = $parroquia['parr_nombre'];

        return implode(', ', $partes);
    }

    private function actualizarPacienteYAtencion($pacienteId, $estabArchivo, $estabCodigo, $usuId)
    {
        $request = \Config\Services::request();
      
        $pacienteModel = new PacienteModel();

        $apellido1 = $request->getPost('pac_apellido1');
        $apellido2 = $request->getPost('pac_apellido2');
        $nombre1 = $request->getPost('pac_nombre1');
        $nombre2 = $request->getPost('pac_nombre2');
        $apellidos = trim($apellido1 . ' ' . $apellido2);
        $nombres = trim($nombre1 . ' ' . $nombre2);
        $cedula = $request->getPost('estab_historia_clinica');

        $nacCodigo = $request->getPost('pac_nacionalidad');

        $lugarNacimiento = null;
        $lugarNacProv = null;
        $lugarNacCant = null;
        $lugarNacParr = null;

        if ($nacCodigo == 1) {
            // Usar los nombres correctos de los campos del formulario
            $lugarNacProv = $request->getPost('nac_provincia');
            $lugarNacCant = $request->getPost('nac_canton');
            $lugarNacParr = $request->getPost('nac_parroquia');

            $lugarNacimiento = $this->construirLugarNacimiento(
                $lugarNacProv,
                $lugarNacCant,
                $lugarNacParr
            );
        } else {
            $lugarNacimiento = trim($request->getPost('pac_lugar_nacimiento'));
        }

        $data = [
            'pac_cedula' => $cedula,
            'pac_apellidos' => $apellidos,
            'pac_nombres' => $nombres,
            'pac_edad_valor' => $request->getPost('pac_edad_valor'),
            'pac_edad_unidad' => $request->getPost('pac_edad_unidad'),
            'pac_fecha_nac' => $request->getPost('pac_fecha_nacimiento'),
            'pac_lugar_nac' => $lugarNacimiento,
            // Asegurarse de que los códigos se actualicen correctamente
            'prov_codigo' => !empty($lugarNacProv) ? $lugarNacProv : null,
            'cant_codigo' => !empty($lugarNacCant) ? $lugarNacCant : null,
            'parr_codigo' => !empty($lugarNacParr) ? $lugarNacParr : null,
            'pac_ocupacion' => $request->getPost('pac_ocupacion'),
            'pac_telefono_fijo' => $request->getPost('pac_telefono_fijo'),
            'pac_telefono_celular' => $request->getPost('pac_telefono_celular'),
            'pac_provincias' => $request->getPost('res_provincia'),
            'pac_cantones' => $request->getPost('res_canton'),
            'pac_parroquias' => $request->getPost('res_parroquia'),
            'pac_barrio' => $request->getPost('res_barrio_sector'),
            'pac_direccion' => $request->getPost('res_calle_principal'),
            'pac_calle_secundaria' => $request->getPost('res_calle_secundaria'),
            'pac_referencia' => $request->getPost('res_referencia'),
            'pac_avisar_a' => $request->getPost('contacto_emerg_nombre'),
            'pac_parentezco_avisar_a' => $request->getPost('contacto_emerg_parentesco'),
            'pac_direccion_avisar' => $request->getPost('contacto_emerg_direccion'),
            'pac_telefono_avisar_a' => $request->getPost('contacto_emerg_telefono'),
            'pac_grupo_prioritario' => $request->getPost('pac_grupo_prioritario') === 'si' ? 1 : 0,
            'pac_grupo_sanguineo' => $request->getPost('pac_grupo_prioritario_especifique'),
            'gen_codigo' => $request->getPost('pac_sexo'),
            'esc_codigo' => $request->getPost('pac_estado_civil'),
            'emp_codigo' => $request->getPost('pac_tipo_empresa'),
            'nac_codigo' => $request->getPost('pac_nacionalidad'),
            'gcu_codigo' => $request->getPost('pac_etnia'),
            'seg_codigo' => $request->getPost('pac_seguro'),
            'nedu_codigo' => $request->getPost('pac_nivel_educacion'),
            'eneduc_codigo' => $request->getPost('pac_estado_educacion'),
            'tdoc_codigo' => $request->getPost('pac_tipo_documento'),
            'nac_ind_codigo' => $request->getPost('pac_nacionalidad_indigena'),
            'pue_ind_codigo' => $request->getPost('pac_pueblo_indigena'),
        ];

        $pacienteModel->update($pacienteId, $data);
        $historiaAsignada = $this->manejarHistoriaClinica($pacienteId, $cedula);
        $ateCodigo = $this->guardarAtencion($pacienteId);

        if ($ateCodigo) {
            $this->guardarEstablecimientoRegistro($estabArchivo, $estabCodigo, $ateCodigo, $usuId);
        }
    }

    private function guardarAtencion($pacienteId)
    {
        $request = \Config\Services::request();
        $atencionModel = new AtencionModel();

        $llegada = $request->getPost('forma_llegada');
        if (empty($llegada)) {
            return null; // No guardar atención si no hay forma de llegada
        }

        // Usar la zona horaria configurada de la aplicación
        $timezone = config('App')->appTimezone ?? 'America/Guayaquil';
        $fechaHoy = new \DateTime('now', new \DateTimeZone($timezone));

        $dataAtencion = [
            'ate_fecha' => $fechaHoy->format('Y-m-d'),
            'ate_hora' => $fechaHoy->format('H:i:s'),
            'ate_referido' => 'NO',
            'ate_motivo_policia' => 'NO',
            'ate_otro_motivo' => 'NINGUNO',
            'ate_fecha_evento' => $fechaHoy->format('Y-m-d'),
            'ate_lugar_evento' => 'NO ESPECIFICADO',
            'ate_direccion_evento' => 'NO ESPECIFICADA',
            'ate_custodia_policial' => 'NO',
            'ate_observaciones' => null,
            'ate_colores' => null,
            'ate_aliento_etilico' => 'NO',
            'ate_valor_alcolchek' => null,
            'ate_fuente_informacion' => $request->getPost('fuente_informacion'),
            'ate_ins_entrega_paciente' => $request->getPost('entrega_paciente_nombre_inst'),
            'ate_telefono' => $request->getPost('entrega_paciente_telefono'),
            'lleg_codigo' => $llegada,
            'pac_codigo' => $pacienteId
        ];

        try {
            $resultado = $atencionModel->insert($dataAtencion);

            if ($resultado) {
                $nuevoAteCodigo = $atencionModel->getInsertID();
                return $nuevoAteCodigo;
            } else {
                return null;
            }
        } catch (\Exception $e) {
            log_message('error', 'Excepción crítica al guardar atención: ' . $e->getMessage());
            return null;
        }
    }

    public function guardarDesdeHospital()
    {
        $data = $this->request->getJSON(true);
        $pacienteModel = new PacienteModel();

        $existe = $pacienteModel->where('pac_cedula', $data['cedula'])->first();

        if ($existe) {
            return $this->response->setJSON(['status' => 'existe']);
        }

        $pacienteModel->insert([
            'pac_cedula' => $data['cedula'],
            'pac_apellido1' => explode(" ", $data['apellidos'])[0],
            'pac_apellido2' => explode(" ", $data['apellidos'])[1] ?? '',
            'pac_nombre1' => explode(" ", $data['nombres'])[0],
            'pac_nombre2' => explode(" ", $data['nombres'])[1] ?? '',
            'pac_fecha_nac' => $data['fecha_nac'],
            'pac_telefono' => $data['telefono'],
            'pac_direccion' => $data['direccion']
        ]);

        return $this->response->setJSON(['status' => 'ok']);
    }



    private function guardarEstablecimientoRegistro($estabArchivo, $estabCodigo, $ateCodigo, $usuId)
    {
        $establecimientoRegistroModel = new EstablecimientoRegistroModel();

        // Datos a insertar en la tabla t_establecimiento_registro
        $data = [
            'estab_codigo' => $estabCodigo,
            'ate_codigo' => $ateCodigo,
            'est_num_archivo' => $estabArchivo,
            'usu_id' => $usuId
        ];

        // Guardar en la base de datos (ahora permite duplicados)
        if ($establecimientoRegistroModel->save($data)) {
            return true;
        } else {
            return false;
        }
    }



    public function generarNumeroArchivo()
    {
        $establecimientoRegistroModel = new EstablecimientoRegistroModel();

        // Obtener el último registro de archivo
        $ultimoRegistro = $establecimientoRegistroModel->orderBy('creado_en', 'DESC')->first();

        // Si no hay registros, el primer número de archivo será 00001
        if (!$ultimoRegistro) {
            return '00001';
        }

        $ultimaFecha = new \DateTime($ultimoRegistro['creado_en']);
        $hoy = new \DateTime();

        // Comparar solo las fechas (sin hora) para reiniciar a las 00:00
        $ultimaFechaSoloFecha = $ultimaFecha->format('Y-m-d');
        $fechaHoy = $hoy->format('Y-m-d');

        // Si es un día diferente, reiniciar el número de archivo
        if ($fechaHoy > $ultimaFechaSoloFecha) {
            return '00001';
        }

        // Si es el mismo día, incrementar el número de archivo
        $nuevoNumero = (int) $ultimoRegistro['est_num_archivo'] + 1;
        return str_pad($nuevoNumero, 5, '0', STR_PAD_LEFT);
    }

    private function manejarHistoriaClinica($pacienteId, $cedula)
    {
        $pacienteModel = new PacienteModel();
        $hospitalModel = new PacienteHospitalModel();

        // 1. Verificar si ya tiene historia clínica
        $pacienteActual = $pacienteModel->find($pacienteId);
        if (!empty($pacienteActual['pac_his_cli'])) {
            return $pacienteActual['pac_his_cli'];
        }

        // 2. Buscar en la BD del hospital si hay cédula válida
        if (!empty($cedula) && !str_contains($cedula, 'N/A') && !str_contains($cedula, 'TEMP-')) {
            try {
                $pacienteHospital = $hospitalModel->buscarPorCedula($cedula);
                if ($pacienteHospital && !empty($pacienteHospital['nro_historia'])) {
                    $historiaHospital = $pacienteHospital['nro_historia'];

                    // Verificar si la historia del hospital ya existe en la base de datos local
                    $existeEnLocal = $pacienteModel
                        ->where('pac_his_cli', $historiaHospital)
                        ->where('pac_codigo !=', $pacienteId)
                        ->first();

                    if ($existeEnLocal) {
                        // Historia duplicada - usar secuencia local
                        $nuevaHistoria = $pacienteModel->generarSiguienteHistoriaClinica();
                        $pacienteModel->update($pacienteId, ['pac_his_cli' => $nuevaHistoria]);
                        log_message('info', "Historia duplicada {$historiaHospital} - asignada secuencial {$nuevaHistoria} a paciente {$pacienteId}");
                        return $nuevaHistoria;
                    } else {
                        // Historia no existe en local - usar la del hospital
                        $pacienteModel->update($pacienteId, ['pac_his_cli' => $historiaHospital]);
                        log_message('info', "Historia clínica {$historiaHospital} del hospital asignada a paciente {$pacienteId}");
                        return $historiaHospital;
                    }
                }
            } catch (\Exception $e) {
                log_message('error', 'Error crítico consultando hospital: ' . $e->getMessage());
            }
        }

        // 3. Generar siguiente número secuencial
        $nuevaHistoria = $pacienteModel->asignarHistoriaClinicaSecuencial($pacienteId);
        return $nuevaHistoria;
    }
}
