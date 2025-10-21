<?php

namespace App\Models\Admision;

use CodeIgniter\Model;

class PacienteHospitalModel extends Model
{
    protected $DBGroup = 'hospital';
    private $connectionAvailable = null;

    /**
     * Verificar si la conexión al hospital está disponible
     */
    private function isConnectionAvailable()
    {
        if ($this->connectionAvailable !== null) {
            return $this->connectionAvailable;
        }

        try {
            // Verificar conectividad
            $this->db->query("SELECT 1");
            $this->connectionAvailable = true;
            return true;
        } catch (\Exception $e) {
            log_message('warning', 'Conexión hospital no disponible: ' . $e->getMessage());
            $this->connectionAvailable = false;
            return false;
        }
    }

    public function buscarPorCedula($cedula)
    {
        if (!$this->isConnectionAvailable()) {
            throw new \Exception('Base de datos del hospital no disponible. Verifique la conexión de red o que el driver ODBC esté instalado.');
        }

        $sql = "SELECT
                nro_historia,
                cedula,
                apellidos,
                nombres,
                estado_civil,
                sexo,
                telefonos,
                fecha_nac,
                id_provincia,
                id_canton,
                id_parroquia,
                id_nacionalidad,
                ocupacion,
                nro_iess,
                direccion,
                nombres_avisar,
                relacion_avisar,
                direccion_avisar,
                telefonos_avisar
            FROM HISTORIAS
            WHERE REPLACE(cedula, '-', '') = REPLACE(?, '-', '')
        ";

        return $this->db->query($sql, [$cedula])->getRowArray();
    }

    public function buscarPorApellido($apellido)
    {
        if (!$this->isConnectionAvailable()) {
            throw new \Exception('Base de datos del hospital no disponible. Verifique la conexión de red o que el driver ODBC esté instalado.');
        }

        // Limpiar espacios extras del término de búsqueda
        $apellidoLimpio = trim(preg_replace('/\s+/', ' ', $apellido));

        // Buscar por coincidencia exacta primero (más rápido)
        $sql = "SELECT TOP 1
                nro_historia,
                cedula,
                LTRIM(RTRIM(apellidos)) as apellidos,
                LTRIM(RTRIM(nombres)) as nombres,
                estado_civil,
                sexo,
                telefonos,
                fecha_nac,
                id_provincia,
                id_canton,
                id_parroquia,
                id_nacionalidad,
                ocupacion,
                nro_iess,
                direccion,
                nombres_avisar,
                relacion_avisar,
                direccion_avisar,
                telefonos_avisar
            FROM HISTORIAS
            WHERE LTRIM(RTRIM(apellidos)) + ' ' + LTRIM(RTRIM(nombres)) = ?
        ";

        $resultado = $this->db->query($sql, [$apellidoLimpio])->getRowArray();

        // Si no encuentra coincidencia exacta, buscar con LIKE
        if (!$resultado) {
            $sql = "SELECT TOP 1
                    nro_historia,
                    cedula,
                    LTRIM(RTRIM(apellidos)) as apellidos,
                    LTRIM(RTRIM(nombres)) as nombres,
                    estado_civil,
                    sexo,
                    telefonos,
                    fecha_nac,
                    id_provincia,
                    id_canton,
                    id_parroquia,
                    id_nacionalidad,
                    ocupacion,
                    nro_iess,
                    direccion,
                    nombres_avisar,
                    relacion_avisar,
                    direccion_avisar,
                    telefonos_avisar
                FROM HISTORIAS
                WHERE LTRIM(RTRIM(apellidos)) + ' ' + LTRIM(RTRIM(nombres)) LIKE ?
                ORDER BY apellidos, nombres
            ";

            $resultado = $this->db->query($sql, ['%' . $apellidoLimpio . '%'])->getRowArray();
        }

        return $resultado;
    }

    public function buscarSugerenciasPorApellido($termino)
    {
        if (!$this->isConnectionAvailable()) {
            return []; // Retornar array vacío si no hay conexión
        }

        // Limpiar el término de búsqueda
        $terminoLimpio = trim($termino);

        // Usar WITH (NOLOCK) para evitar bloqueos en la BD del hospital
        $sql = "SELECT TOP 10
                LTRIM(RTRIM(apellidos)) as apellidos,
                LTRIM(RTRIM(nombres)) as nombres
            FROM HISTORIAS WITH (NOLOCK)
            WHERE LTRIM(RTRIM(apellidos)) + ' ' + LTRIM(RTRIM(nombres)) LIKE ?
            ORDER BY apellidos, nombres
        ";

        return $this->db->query($sql, ['%' . $terminoLimpio . '%'])->getResultArray();
    }

    public function buscarPorHistoria($historia)
    {
        if (!$this->isConnectionAvailable()) {
            throw new \Exception('Base de datos del hospital no disponible. Verifique la conexión de red o que el driver ODBC esté instalado.');
        }

        $sql = "SELECT
                nro_historia,
                cedula,
                apellidos,
                nombres,
                estado_civil,
                sexo,
                telefonos,
                fecha_nac,
                id_provincia,
                id_canton,
                id_parroquia,
                id_nacionalidad,
                ocupacion,
                nro_iess,
                direccion,
                nombres_avisar,
                relacion_avisar,
                direccion_avisar,
                telefonos_avisar
            FROM HISTORIAS
            WHERE nro_historia = ?
        ";

        return $this->db->query($sql, [$historia])->getRowArray();
    }
}