<?php

namespace App\Models\Admision;

use CodeIgniter\Model;

class PacienteBusquedaModel extends Model
{
    protected $db;

    public function __construct()
    {
        parent::__construct();
        $this->db = \Config\Database::connect();
    }

    protected $table = 't_paciente';
    protected $primaryKey = 'pac_codigo';

    public function buscarPorCedula($cedula)
    {
        return $this->db->query("SELECT 
            p.pac_codigo,
            p.pac_his_cli,
            p.pac_cedula                AS cedula,
            p.pac_apellidos,
            p.pac_nombres,
            p.pac_edad_valor            AS edad_numero,
            p.pac_edad_unidad           AS edad_unidad,

            td.tdoc_descripcion         AS tipo_documento,
            ec.esc_descripcion          AS estado_civil,
            g.gen_descripcion           AS genero,
            p.pac_telefono_fijo         AS telefono_fijo,
            p.pac_telefono_celular      AS telefono_celular,
            p.pac_fecha_nac,
            p.pac_lugar_nac,
            p.prov_codigo        AS lugar_nac_provincia,
            p.cant_codigo        AS lugar_nac_canton,
            p.parr_codigo        AS lugar_nac_parroquia,
            n.nac_descripcion           AS nacionalidad,
            n.nac_codigo                AS nacionalidad_codigo,
            ni.nac_ind_nombre           AS nacionalidad_indigena,
            pi.pue_ind_nombre           AS pueblo_indigena,
            gc.gcu_descripcion          AS etnia,
            nedu.nedu_nivel             AS nivel_educativo,
            ened.eneduc_estado          AS estado_nivel_educativo,
            e.emp_descripcion           AS tipo_empresa,
            p.pac_ocupacion,
            s.seg_descripcion           AS seguro,
            p.pac_provincias            AS provincia,
            p.pac_cantones              AS canton,
            p.pac_parroquias            AS parroquia,
            p.pac_barrio                AS barrio,
            p.pac_direccion             AS direccion,
            p.pac_calle_secundaria      AS calle_secundaria,
            p.pac_referencia            AS referencia,
            p.pac_avisar_a,
            p.pac_parentezco_avisar_a,
            p.pac_direccion_avisar,
            p.pac_telefono_avisar_a,
            p.pac_grupo_prioritario     AS grupo_prioritario,
            p.pac_grupo_sanguineo       AS grupo_sanguineo,
            l.lleg_descripcion          AS forma_llegada,
            a.ate_fuente_informacion    AS fuente_informacion,
            a.ate_ins_entrega_paciente  AS institucion_entrega,
            a.ate_telefono              AS telefono_atencion

        FROM t_paciente p
        LEFT JOIN t_atencion a ON p.pac_codigo = a.pac_codigo
        LEFT JOIN t_genero g ON p.gen_codigo = g.gen_codigo
        LEFT JOIN t_seguro_social s ON p.seg_codigo = s.seg_codigo
        LEFT JOIN t_grupo_cultural gc ON p.gcu_codigo = gc.gcu_codigo
        LEFT JOIN t_empresa e ON p.emp_codigo = e.emp_codigo
        LEFT JOIN t_nacionalidad n ON p.nac_codigo = n.nac_codigo
        LEFT JOIN t_nacionalidad_indigena ni ON p.nac_ind_codigo = ni.nac_ind_codigo
        LEFT JOIN t_pueblo_indigena pi ON p.pue_ind_codigo = pi.pue_ind_codigo
        LEFT JOIN t_estado_civil ec ON p.esc_codigo = ec.esc_codigo
        LEFT JOIN t_nivel_educ nedu ON p.nedu_codigo = nedu.nedu_codigo
        LEFT JOIN t_esta_niv_educ ened ON p.eneduc_codigo = ened.eneduc_codigo
        LEFT JOIN t_tipo_documento td ON p.tdoc_codigo = td.tdoc_codigo
        LEFT JOIN t_llegada l ON a.lleg_codigo = l.lleg_codigo

        WHERE p.pac_cedula = ?
          AND p.pac_his_cli IS NOT NULL
        LIMIT 1", [$cedula])->getResultArray();
    }

    public function buscarPorApellido($apellido)
    {
        return $this->db->query("SELECT 
            p.pac_codigo,
            p.pac_his_cli,
            p.pac_cedula                AS cedula,
            p.pac_apellidos,
            p.pac_nombres,
            p.pac_edad_valor            AS edad_numero,
            p.pac_edad_unidad           AS edad_unidad,

            td.tdoc_descripcion         AS tipo_documento,
            ec.esc_descripcion          AS estado_civil,
            g.gen_descripcion           AS genero,
            p.pac_telefono_fijo         AS telefono_fijo,
            p.pac_telefono_celular      AS telefono_celular,
            p.pac_fecha_nac,
            p.pac_lugar_nac,
            p.prov_codigo        AS lugar_nac_provincia,
            p.cant_codigo        AS lugar_nac_canton,
            p.parr_codigo        AS lugar_nac_parroquia,
            n.nac_descripcion           AS nacionalidad,
            n.nac_codigo                AS nacionalidad_codigo,
            ni.nac_ind_nombre           AS nacionalidad_indigena,
            pi.pue_ind_nombre           AS pueblo_indigena,
            gc.gcu_descripcion          AS etnia,
            nedu.nedu_nivel             AS nivel_educativo,
            ened.eneduc_estado          AS estado_nivel_educativo,
            e.emp_descripcion           AS tipo_empresa,
            p.pac_ocupacion,
            s.seg_descripcion           AS seguro,
            p.pac_provincias            AS provincia,
            p.pac_cantones              AS canton,
            p.pac_parroquias            AS parroquia,
            p.pac_barrio                AS barrio,
            p.pac_direccion             AS direccion,
            p.pac_calle_secundaria      AS calle_secundaria,
            p.pac_referencia            AS referencia,
            p.pac_avisar_a,
            p.pac_parentezco_avisar_a,
            p.pac_direccion_avisar,
            p.pac_telefono_avisar_a,
            p.pac_grupo_prioritario     AS grupo_prioritario,
            p.pac_grupo_sanguineo       AS grupo_sanguineo,
            l.lleg_descripcion          AS forma_llegada,
            a.ate_fuente_informacion    AS fuente_informacion,
            a.ate_ins_entrega_paciente  AS institucion_entrega,
            a.ate_telefono              AS telefono_atencion

        FROM t_paciente p
        LEFT JOIN t_atencion a ON p.pac_codigo = a.pac_codigo
        LEFT JOIN t_genero g ON p.gen_codigo = g.gen_codigo
        LEFT JOIN t_seguro_social s ON p.seg_codigo = s.seg_codigo
        LEFT JOIN t_grupo_cultural gc ON p.gcu_codigo = gc.gcu_codigo
        LEFT JOIN t_empresa e ON p.emp_codigo = e.emp_codigo
        LEFT JOIN t_nacionalidad n ON p.nac_codigo = n.nac_codigo
        LEFT JOIN t_nacionalidad_indigena ni ON p.nac_ind_codigo = ni.nac_ind_codigo
        LEFT JOIN t_pueblo_indigena pi ON p.pue_ind_codigo = pi.pue_ind_codigo
        LEFT JOIN t_estado_civil ec ON p.esc_codigo = ec.esc_codigo
        LEFT JOIN t_nivel_educ nedu ON p.nedu_codigo = nedu.nedu_codigo
        LEFT JOIN t_esta_niv_educ ened ON p.eneduc_codigo = ened.eneduc_codigo
        LEFT JOIN t_tipo_documento td ON p.tdoc_codigo = td.tdoc_codigo
        LEFT JOIN t_llegada l ON a.lleg_codigo = l.lleg_codigo
        WHERE CONCAT(p.pac_apellidos, ' ', p.pac_nombres) LIKE ? AND p.pac_his_cli IS NOT NULL
        LIMIT 1", ['%' . $apellido . '%'])->getResultArray();
    }

    public function buscarSugerenciasPorApellido($termino)
    {
        return $this->db->table('t_paciente')
            ->select('pac_apellidos, pac_nombres')
            ->like('CONCAT(pac_apellidos, " ", pac_nombres)', $termino)
            ->limit(15)
            ->get()
            ->getResultArray();
    }

    public function buscarPorHistoria($historia)
    {
        return $this->db->query("SELECT 
            p.pac_codigo,
            p.pac_his_cli,
            p.pac_cedula                AS cedula,
            p.pac_apellidos,
            p.pac_nombres,
            p.pac_edad_valor            AS edad_numero,
            p.pac_edad_unidad           AS edad_unidad,

            td.tdoc_descripcion         AS tipo_documento,
            ec.esc_descripcion          AS estado_civil,
            g.gen_descripcion           AS genero,
            p.pac_telefono_fijo         AS telefono_fijo,
            p.pac_telefono_celular      AS telefono_celular,
            p.pac_fecha_nac,
            p.pac_lugar_nac,
            p.prov_codigo        AS lugar_nac_provincia,
            p.cant_codigo        AS lugar_nac_canton,
            p.parr_codigo        AS lugar_nac_parroquia,
            n.nac_descripcion           AS nacionalidad,
            n.nac_codigo                AS nacionalidad_codigo,
            ni.nac_ind_nombre           AS nacionalidad_indigena,
            pi.pue_ind_nombre           AS pueblo_indigena,
            gc.gcu_descripcion          AS etnia,
            nedu.nedu_nivel             AS nivel_educativo,
            ened.eneduc_estado          AS estado_nivel_educativo,
            e.emp_descripcion           AS tipo_empresa,
            p.pac_ocupacion,
            s.seg_descripcion           AS seguro,
            p.pac_provincias            AS provincia,
            p.pac_cantones              AS canton,
            p.pac_parroquias            AS parroquia,
            p.pac_barrio                AS barrio,
            p.pac_direccion             AS direccion,
            p.pac_calle_secundaria      AS calle_secundaria,
            p.pac_referencia            AS referencia,
            p.pac_avisar_a,
            p.pac_parentezco_avisar_a,
            p.pac_direccion_avisar,
            p.pac_telefono_avisar_a,
            p.pac_grupo_prioritario     AS grupo_prioritario,
            p.pac_grupo_sanguineo       AS grupo_sanguineo,
            l.lleg_descripcion          AS forma_llegada,
            a.ate_fuente_informacion    AS fuente_informacion,
            a.ate_ins_entrega_paciente  AS institucion_entrega,
            a.ate_telefono              AS telefono_atencion

        FROM t_paciente p
        LEFT JOIN t_atencion a ON p.pac_codigo = a.pac_codigo
        LEFT JOIN t_genero g ON p.gen_codigo = g.gen_codigo
        LEFT JOIN t_seguro_social s ON p.seg_codigo = s.seg_codigo
        LEFT JOIN t_grupo_cultural gc ON p.gcu_codigo = gc.gcu_codigo
        LEFT JOIN t_empresa e ON p.emp_codigo = e.emp_codigo
        LEFT JOIN t_nacionalidad n ON p.nac_codigo = n.nac_codigo
        LEFT JOIN t_nacionalidad_indigena ni ON p.nac_ind_codigo = ni.nac_ind_codigo
        LEFT JOIN t_pueblo_indigena pi ON p.pue_ind_codigo = pi.pue_ind_codigo
        LEFT JOIN t_estado_civil ec ON p.esc_codigo = ec.esc_codigo
        LEFT JOIN t_nivel_educ nedu ON p.nedu_codigo = nedu.nedu_codigo
        LEFT JOIN t_esta_niv_educ ened ON p.eneduc_codigo = ened.eneduc_codigo
        LEFT JOIN t_tipo_documento td ON p.tdoc_codigo = td.tdoc_codigo
        LEFT JOIN t_llegada l ON a.lleg_codigo = l.lleg_codigo
        WHERE p.pac_his_cli = ?
        LIMIT 1", [$historia])->getResultArray();
    }

    
}