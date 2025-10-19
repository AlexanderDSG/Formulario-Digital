<?php

namespace App\Models\Enfemeria;

use CodeIgniter\Model;

class ConstantesVitalesModel extends Model
{
    protected $table = 't_constantes_vitales';
    protected $primaryKey = 'con_codigo';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';

    protected $allowedFields = [
        'ate_codigo',
        'con_sin_constantes', 
        'con_presion_arterial',
        'con_pulso',
        'con_frec_respiratoria',
        'con_pulsioximetria',
        'con_perimetro_cefalico',
        'con_peso',
        'con_talla',
        'con_glucemia_capilar',
        'con_reaccion_pupila_der',
        'con_reaccion_pupila_izq',
        'con_t_lleno_capilar',
        'con_glasgow_ocular',
        'con_glasgow_verbal',
        'con_glasgow_motora'
    ];

    
    // VALIDACIÓN MODIFICADA - Solo verificar que ate_codigo exista, pero permitir duplicados
    protected $validationRules = [
        'ate_codigo' => 'required|integer|is_not_unique[t_atencion.ate_codigo]'
    ];

    protected $validationMessages = [
        'ate_codigo' => [
            'required' => 'El código de atención es obligatorio.',
            'integer' => 'El código de atención debe ser un número.',
            'is_not_unique' => 'La atención especificada no existe.'
        ]
    ];

    protected $useTimestamps = false;

    /**
     * MÉTODO MODIFICADO: Verifica existencia pero NO bloquea el insert
     */
    public function existeParaAtencion($ate_codigo)
    {
        $resultado = $this->where('ate_codigo', (int)$ate_codigo)->first();
        log_message('info', "Verificando constantes vitales para atención $ate_codigo: " . ($resultado ? 'EXISTE' : 'NO EXISTE'));
        return !empty($resultado);
    }

    /**
     * MÉTODO MODIFICADO: Permite múltiples inserts para la misma atención
     */
    public function insertarNuevo($datos)
    {
        // **ELIMINADA LA VERIFICACIÓN QUE BLOQUEABA DUPLICADOS**
        // Ahora siempre permite insertar nuevos registros

        log_message('info', 'Insertando nuevas constantes vitales (múltiples permitidos) para ate_codigo: ' . $datos['ate_codigo']);
        return $this->insert($datos, true);
    }

    /**
     * Obtener las constantes vitales MÁS RECIENTES por atención
     */
    public function obtenerUltimasPorAtencion($ate_codigo)
    {
        return $this->select('
                t_constantes_vitales.*,
                t_atencion.ate_fecha,
                t_atencion.ate_hora
            ')
            ->join('t_atencion', 't_constantes_vitales.ate_codigo = t_atencion.ate_codigo')
            ->where('t_constantes_vitales.ate_codigo', $ate_codigo)
            ->orderBy('t_constantes_vitales.con_codigo', 'DESC') // Más reciente primero
            ->first();
    }

    /**
     * Obtener TODAS las constantes vitales de una atención (historial completo)
     */
    public function obtenerHistorialPorAtencion($ate_codigo)
    {
        return $this->select('
                t_constantes_vitales.*,
                t_atencion.ate_fecha,
                t_atencion.ate_hora
            ')
            ->join('t_atencion', 't_constantes_vitales.ate_codigo = t_atencion.ate_codigo')
            ->where('t_constantes_vitales.ate_codigo', $ate_codigo)
            ->orderBy('t_constantes_vitales.con_codigo', 'DESC') // Más reciente primero
            ->findAll();
    }

    /**
     * Obtener constantes vitales por atención con fecha de la atención (método existente mantenido)
     */
    public function obtenerPorAtencion($ate_codigo)
    {
        return $this->obtenerUltimasPorAtencion($ate_codigo); // Redirige al método más específico
    }

    /**
     * Contar cuántos registros de constantes vitales tiene una atención
     */
    public function contarRegistrosPorAtencion($ate_codigo)
    {
        return $this->where('ate_codigo', (int)$ate_codigo)->countAllResults();
    }
}
