<?php

namespace App\Controllers\Administrador;

use App\Controllers\BaseController;

class RespaldoController extends BaseController
{
    /**
     * Configuración de respaldos
     */
    private $config = [
        // Ruta donde se guardarán los respaldos
        'ruta_base' => 'C:\Users\Alex\Documents\Respaldos_BD',

        // Retención de respaldos (en días)
        'dias_retencion_diario' => 7,      
        'dias_retencion_semanal' => 30,   
        'dias_retencion_mensual' => 365, 

        // Ruta de mysqldump (MySQL Server 8.0)
        'mysqldump_path' => 'C:\Program Files\MySQL\MySQL Server 8.0\bin\mysqldump.exe',

        // Ruta de mysql (MySQL Server 8.0 para restauración)
        'mysql_path' => 'C:\Program Files\MySQL\MySQL Server 8.0\bin\mysql.exe',
    ];

    public function __construct()
    {
        // Verificar autenticación en el constructor
        if (!session()->get('logged_in') || session()->get('rol_id') != 1) {
            throw new \CodeIgniter\Exceptions\PageNotFoundException('Acceso denegado');
        }
    }

    /**
     * Vista principal de gestión de respaldos
     */
    public function index()
    {
        $data = [
            'titulo' => 'Gestión de Respaldos de Base de Datos',
            'config' => $this->config
        ];

        return view('administrador/respaldos/index', $data);
    }

    /**
     * Crear respaldo manual
     */
    public function crearRespaldoManual()
    {
        try {
            $tipo = $this->request->getPost('tipo') ?? 'manual';
            $descripcion = $this->request->getPost('descripcion') ?? 'Respaldo manual';

            $resultado = $this->ejecutarRespaldo($tipo, $descripcion);

            if ($resultado['success']) {
                return $this->response->setJSON([
                    'success' => true,
                    'message' => 'Respaldo creado exitosamente',
                    'archivo' => $resultado['archivo'],
                    'tamaño' => $resultado['tamaño'],
                    'ruta' => $resultado['ruta']
                ]);
            } else {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => $resultado['error']
                ]);
            }

        } catch (\Exception $e) {
            log_message('error', 'Error en crearRespaldoManual: ' . $e->getMessage());
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Error al crear respaldo: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Ejecutar respaldo de la base de datos
     */
    private function ejecutarRespaldo($tipo = 'manual', $descripcion = '')
    {
        try {
            // Obtener configuración de base de datos
            $db = \Config\Database::connect();
            $hostname = $db->hostname;
            $username = $db->username;
            $password = $db->password;
            $database = $db->database;
            $port = $db->port ?? 3306;

            // Crear estructura de carpetas
            $año = date('Y');
            $mes = date('m');
            $carpetaAño = $this->config['ruta_base'] . DIRECTORY_SEPARATOR . $año;
            $carpetaMes = $carpetaAño . DIRECTORY_SEPARATOR . $mes;

            if (!is_dir($carpetaMes)) {
                mkdir($carpetaMes, 0777, true);
            }

            // Generar nombre del archivo
            $timestamp = date('Y-m-d_His');
            $nombreArchivo = "{$tipo}_{$database}_{$timestamp}.sql";
            $rutaArchivo = $carpetaMes . DIRECTORY_SEPARATOR . $nombreArchivo;

            // Construir comando mysqldump para MySQL 8.0
            $comando = sprintf(
                '"%s" --host=%s --port=%d --user=%s --password=%s --default-character-set=utf8mb4 --single-transaction --routines --triggers --events --add-drop-table %s > "%s" 2>&1',
                $this->config['mysqldump_path'],
                $hostname,
                $port,
                $username,
                $password,
                $database,
                $rutaArchivo
            );

            // Ejecutar respaldo
            exec($comando . ' 2>&1', $output, $returnCode);

            if ($returnCode !== 0) {
                throw new \Exception('Error ejecutando mysqldump: ' . implode("\n", $output));
            }

            // Verificar que el archivo se creó
            if (!file_exists($rutaArchivo) || filesize($rutaArchivo) == 0) {
                throw new \Exception('El archivo de respaldo está vacío o no se creó');
            }

            // Comprimir el archivo
            $rutaComprimida = $rutaArchivo . '.gz';
            $this->comprimirArchivo($rutaArchivo, $rutaComprimida);

            // Eliminar archivo SQL sin comprimir
            if (file_exists($rutaComprimida)) {
                unlink($rutaArchivo);
                $rutaArchivo = $rutaComprimida;
                $nombreArchivo .= '.gz';
            }

            // Registrar en log
            $this->registrarRespaldo([
                'tipo' => $tipo,
                'descripcion' => $descripcion,
                'archivo' => $nombreArchivo,
                'ruta' => $rutaArchivo,
                'tamaño' => filesize($rutaArchivo),
                'fecha' => date('Y-m-d H:i:s'),
                'usuario' => session()->get('nombre_completo')
            ]);

            return [
                'success' => true,
                'archivo' => $nombreArchivo,
                'ruta' => $rutaArchivo,
                'tamaño' => $this->formatearTamaño(filesize($rutaArchivo))
            ];

        } catch (\Exception $e) {
            log_message('error', 'Error en ejecutarRespaldo: ' . $e->getMessage());
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Comprimir archivo SQL con gzip
     */
    private function comprimirArchivo($origen, $destino)
    {
        try {
            $bufferSize = 4096;
            $file = fopen($origen, 'rb');
            $gzFile = gzopen($destino, 'wb9');

            while (!feof($file)) {
                gzwrite($gzFile, fread($file, $bufferSize));
            }

            fclose($file);
            gzclose($gzFile);

            return true;
        } catch (\Exception $e) {
            log_message('error', 'Error comprimiendo archivo: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Registrar respaldo en archivo JSON
     */
    private function registrarRespaldo($datos)
    {
        $archivoLog = $this->config['ruta_base'] . DIRECTORY_SEPARATOR . 'respaldos_log.json';

        $registros = [];
        if (file_exists($archivoLog)) {
            $contenido = file_get_contents($archivoLog);
            $registros = json_decode($contenido, true) ?: [];
        }

        $registros[] = $datos;

        file_put_contents($archivoLog, json_encode($registros, JSON_PRETTY_PRINT));
    }

    /**
     * Listar respaldos disponibles
     */
    public function listarRespaldos()
    {
        try {
            $archivoLog = $this->config['ruta_base'] . DIRECTORY_SEPARATOR . 'respaldos_log.json';

            $respaldos = [];
            if (file_exists($archivoLog)) {
                $contenido = file_get_contents($archivoLog);
                $respaldos = json_decode($contenido, true) ?: [];
            }

            // Ordenar por fecha descendente
            usort($respaldos, function($a, $b) {
                return strtotime($b['fecha']) - strtotime($a['fecha']);
            });

            return $this->response->setJSON([
                'success' => true,
                'respaldos' => $respaldos,
                'total' => count($respaldos)
            ]);

        } catch (\Exception $e) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Error al listar respaldos: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Restaurar base de datos desde respaldo
     */
    public function restaurarRespaldo()
    {
        try {
            $archivo = $this->request->getPost('archivo');
            $confirmarPassword = $this->request->getPost('password');

            // Validar contraseña del administrador
            if (!$this->validarPasswordAdmin($confirmarPassword)) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Contraseña incorrecta'
                ]);
            }

            // Verificar que el archivo existe
            if (!file_exists($archivo)) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'El archivo de respaldo no existe'
                ]);
            }

            // Descomprimir si está comprimido
            $archivoSQL = $archivo;
            if (pathinfo($archivo, PATHINFO_EXTENSION) === 'gz') {
                $archivoSQL = $this->descomprimirArchivo($archivo);
            }

            // Ejecutar restauración
            $resultado = $this->ejecutarRestauracion($archivoSQL);

            // Eliminar archivo temporal descomprimido
            if ($archivoSQL !== $archivo && file_exists($archivoSQL)) {
                unlink($archivoSQL);
            }

            return $this->response->setJSON($resultado);

        } catch (\Exception $e) {
            log_message('error', 'Error en restaurarRespaldo: ' . $e->getMessage());
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Error al restaurar: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Ejecutar restauración de base de datos
     */
    private function ejecutarRestauracion($archivoSQL)
    {
        try {
            $db = \Config\Database::connect();
            $hostname = $db->hostname;
            $username = $db->username;
            $password = $db->password;
            $database = $db->database;
            $port = $db->port ?? 3306;

            // Construir comando mysql
            $comando = sprintf(
                '"%s" --host=%s --port=%d --user=%s --password=%s %s < "%s"',
                $this->config['mysql_path'],
                $hostname,
                $port,
                $username,
                $password,
                $database,
                $archivoSQL
            );

            // Ejecutar restauración
            exec($comando . ' 2>&1', $output, $returnCode);

            if ($returnCode !== 0) {
                throw new \Exception('Error ejecutando mysql: ' . implode("\n", $output));
            }

            return [
                'success' => true,
                'message' => 'Base de datos restaurada exitosamente'
            ];

        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Descomprimir archivo .gz
     */
    private function descomprimirArchivo($archivoGz)
    {
        $archivoSQL = str_replace('.gz', '', $archivoGz);

        $gzFile = gzopen($archivoGz, 'rb');
        $file = fopen($archivoSQL, 'wb');

        while (!gzeof($gzFile)) {
            fwrite($file, gzread($gzFile, 4096));
        }

        fclose($file);
        gzclose($gzFile);

        return $archivoSQL;
    }

    /**
     * Validar contraseña del administrador
     */
    private function validarPasswordAdmin($password)
    {
        $usuarioModel = new \App\Models\Administrador\UsuarioModel();
        $usuario = $usuarioModel->find(session()->get('usuario_id'));

        if (!$usuario) {
            return false;
        }

        return hash_equals(
            hash('sha256', $password),
            $usuario['usu_password']
        );
    }

    /**
     * Eliminar respaldos antiguos según política de retención
     */
    public function limpiarRespaldosAntiguos()
    {
        try {
            $eliminados = 0;
            $archivoLog = $this->config['ruta_base'] . DIRECTORY_SEPARATOR . 'respaldos_log.json';

            if (!file_exists($archivoLog)) {
                return $this->response->setJSON([
                    'success' => true,
                    'eliminados' => 0,
                    'message' => 'No hay respaldos registrados'
                ]);
            }

            $contenido = file_get_contents($archivoLog);
            $respaldos = json_decode($contenido, true) ?: [];
            $respaldosActualizados = [];

            foreach ($respaldos as $respaldo) {
                $fechaRespaldo = strtotime($respaldo['fecha']);
                $diasAntiguo = (time() - $fechaRespaldo) / 86400;

                $debeEliminar = false;

                // Aplicar política de retención según tipo
                if ($respaldo['tipo'] === 'diario' && $diasAntiguo > $this->config['dias_retencion_diario']) {
                    $debeEliminar = true;
                } elseif ($respaldo['tipo'] === 'semanal' && $diasAntiguo > $this->config['dias_retencion_semanal']) {
                    $debeEliminar = true;
                } elseif ($respaldo['tipo'] === 'mensual' && $diasAntiguo > $this->config['dias_retencion_mensual']) {
                    $debeEliminar = true;
                }

                if ($debeEliminar && file_exists($respaldo['ruta'])) {
                    unlink($respaldo['ruta']);
                    $eliminados++;
                } else {
                    $respaldosActualizados[] = $respaldo;
                }
            }

            // Actualizar log
            file_put_contents($archivoLog, json_encode($respaldosActualizados, JSON_PRETTY_PRINT));

            return $this->response->setJSON([
                'success' => true,
                'eliminados' => $eliminados,
                'message' => "Se eliminaron {$eliminados} respaldos antiguos"
            ]);

        } catch (\Exception $e) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Error al limpiar respaldos: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Descargar respaldo
     */
    public function descargarRespaldo($archivo)
    {
        try {
            if (!file_exists($archivo)) {
                throw new \Exception('Archivo no encontrado');
            }

            return $this->response->download($archivo, null);

        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Error al descargar: ' . $e->getMessage());
        }
    }

    /**
     * Obtener estadísticas de respaldos
     */
    public function obtenerEstadisticas()
    {
        try {
            $archivoLog = $this->config['ruta_base'] . DIRECTORY_SEPARATOR . 'respaldos_log.json';

            $respaldos = [];
            if (file_exists($archivoLog)) {
                $contenido = file_get_contents($archivoLog);
                $respaldos = json_decode($contenido, true) ?: [];
            }

            $estadisticas = [
                'total' => count($respaldos),
                'por_tipo' => [
                    'manual' => 0,
                    'diario' => 0,
                    'semanal' => 0,
                    'mensual' => 0
                ],
                'tamaño_total' => 0,
                'ultimo_respaldo' => null
            ];

            foreach ($respaldos as $respaldo) {
                $estadisticas['por_tipo'][$respaldo['tipo']]++;
                $estadisticas['tamaño_total'] += $respaldo['tamaño'];
            }

            if (!empty($respaldos)) {
                usort($respaldos, function($a, $b) {
                    return strtotime($b['fecha']) - strtotime($a['fecha']);
                });
                $estadisticas['ultimo_respaldo'] = $respaldos[0];
            }

            $estadisticas['tamaño_total_formateado'] = $this->formatearTamaño($estadisticas['tamaño_total']);

            return $this->response->setJSON([
                'success' => true,
                'estadisticas' => $estadisticas
            ]);

        } catch (\Exception $e) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Error al obtener estadísticas: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Formatear tamaño de archivo
     */
    private function formatearTamaño($bytes)
    {
        $unidades = ['B', 'KB', 'MB', 'GB', 'TB'];
        $indice = 0;

        while ($bytes >= 1024 && $indice < count($unidades) - 1) {
            $bytes /= 1024;
            $indice++;
        }

        return round($bytes, 2) . ' ' . $unidades[$indice];
    }
}
