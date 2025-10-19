<?php

namespace App\Models;

use CodeIgniter\Model;

class PacienteModel extends Model
{
    protected $table      = 't_paciente';
    protected $primaryKey = 'pac_codigo';

    protected $allowedFields = [
        'pac_his_cli',
        'pac_apellidos',
        'pac_nombres',
        'pac_edad_valor',
        'pac_edad_unidad',
        'pac_cedula',
        'pac_direccion',
        'pac_telefono_fijo',           
        'pac_telefono_celular',        
        'pac_fecha_nac',
        'pac_lugar_nac',
        'prov_codigo',
        'cant_codigo',
        'parr_codigo',
        'pac_instruccion',
        'pac_ocupacion',
        'pac_avisar_a',
        'pac_parentezco_avisar_a',
        'pac_direccion_avisar',
        'pac_telefono_avisar_a',
        'pac_grupo_prioritario',       
        'pac_grupo_sanguineo',
        'pac_provincias',
        'pac_cantones',
        'pac_parroquias',
        'pac_barrio',
        'pac_calle_secundaria',        
        'pac_referencia',              
        'gen_codigo',
        'zon_codigo',
        'seg_codigo',
        'gcu_codigo',
        'emp_codigo',
        'nac_codigo',
        'esc_codigo',
        'nedu_codigo',
        'eneduc_codigo',
        'tdoc_codigo',
        'nac_ind_codigo',              
        'pue_ind_codigo'               
    ];

    protected $returnType = 'array';

    /**
     * Generar la siguiente historia clínica secuencial
     */
    public function generarSiguienteHistoriaClinica()
    {
        $db = \Config\Database::connect();
        
        // Obtener el máximo número de historia clínica actual
        $query = $db->query("SELECT MAX(pac_his_cli) as max_historia FROM t_paciente WHERE pac_his_cli IS NOT NULL");
        $resultado = $query->getRowArray();
        
        if ($resultado && !empty($resultado['max_historia'])) {
            $siguienteNumero = intval($resultado['max_historia']) + 1;
        } else {
            $siguienteNumero = 1; // Empezar en 1 si no hay historias
        }

        log_message('info', "Siguiente historia clínica secuencial: $siguienteNumero");
        return $siguienteNumero;
    }

    /**
     * Asignar historia clínica secuencial
     */
    public function asignarHistoriaClinicaSecuencial($pacienteId)
    {
        // Verificar si el paciente ya tiene historia clínica
        $paciente = $this->find($pacienteId);
        if (!empty($paciente['pac_his_cli'])) {
            log_message('info', 'El paciente ya tiene historia clínica: ' . $paciente['pac_his_cli']);
            return $paciente['pac_his_cli'];
        }

        // Generar siguiente número secuencial
        $nuevaHistoria = $this->generarSiguienteHistoriaClinica();

        // Asignar al paciente
        $resultado = $this->update($pacienteId, ['pac_his_cli' => $nuevaHistoria]);

        if ($resultado) {
            log_message('info', "Historia clínica secuencial asignada: $nuevaHistoria para paciente ID: $pacienteId");
            return $nuevaHistoria;
        } else {
            log_message('error', "Error al asignar historia clínica secuencial para paciente ID: $pacienteId");
            return false;
        }
    }
}