<?php

namespace App\Models\Admision;

use CodeIgniter\Model;

class EstablecimientoRegistroModel extends Model
{
    protected $table = 't_establecimiento_registro';
    protected $primaryKey = 'est_reg_codigo';
    protected $allowedFields = [
        'estab_codigo',
        'ate_codigo',
        'est_num_archivo',
        'usu_id'
    ];

    //creado_en esta la fecha y la hora ahi mismo 
    public function generarNumeroArchivo()
    {
        $ultimoRegistro = $this->orderBy('creado_en', 'DESC')->first();

        if (!$ultimoRegistro) {
            return 1;
        }

        $ultimaFecha = new \DateTime($ultimoRegistro['creado_en']);
        $hoy = new \DateTime();

        // Comparar solo las fechas (sin hora) para reiniciar a las 00:00
        $ultimaFechaSoloFecha = $ultimaFecha->format('Y-m-d');
        $fechaHoy = $hoy->format('Y-m-d');

        // Si es un día diferente, reiniciar el contador
        if ($fechaHoy > $ultimaFechaSoloFecha) {
            return 1;
        }

        // Si es el mismo día, incrementamos el último número de archivo
        $ultimoNumero = (int) $ultimoRegistro['est_num_archivo'];
        $nuevoNumero = $ultimoNumero + 1;
        return $nuevoNumero;
    }

    /**
     * Obtener información del registro de admisión con datos del usuario
     */
    public function obtenerRegistroConUsuario($ateCodigo)
    {
        return $this->select('t_establecimiento_registro.*, t_usuario.usu_nombre, t_usuario.usu_apellido, t_usuario.usu_usuario')
            ->join('t_usuario', 't_establecimiento_registro.usu_id = t_usuario.usu_id')
            ->where('t_establecimiento_registro.ate_codigo', $ateCodigo)
            ->first();
    }
}