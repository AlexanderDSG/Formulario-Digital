<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

class RespaldoAutomatico extends BaseCommand
{
    protected $group       = 'Respaldos';
    protected $name        = 'respaldo:automatico';
    protected $description = 'Ejecuta un respaldo automático de la base de datos MySQL';

    protected $usage = 'respaldo:automatico [tipo]';
    protected $arguments = [
        'tipo' => 'Tipo de respaldo: diario, semanal, mensual (por defecto: diario)'
    ];

    /**
     * Configuración de respaldos (debe coincidir con RespaldoController)
     */
    private $config = [
        'ruta_base' => 'C:\Respaldos_BD',
        'mysqldump_path' => 'C:\Program Files\MySQL\MySQL Server 8.0\bin\mysqldump.exe',
        'dias_retencion_diario' => 7,
        'dias_retencion_semanal' => 30,
        'dias_retencion_mensual' => 365,
    ];

    public function run(array $params)
    {
        CLI::write('===========================================', 'yellow');
        CLI::write('  RESPALDO AUTOMÁTICO DE BASE DE DATOS', 'yellow');
        CLI::write('===========================================', 'yellow');
        CLI::newLine();

        $tipo = $params[0] ?? 'diario';

        // Validar tipo
        if (!in_array($tipo, ['diario', 'semanal', 'mensual'])) {
            CLI::error('Tipo de respaldo inválido. Use: diario, semanal o mensual');
            return;
        }

        CLI::write("Tipo de respaldo: {$tipo}", 'green');
        CLI::write("Fecha/Hora: " . date('Y-m-d H:i:s'), 'green');
        CLI::newLine();

        try {
            // Ejecutar respaldo
            CLI::write('Iniciando respaldo...', 'cyan');
            $resultado = $this->ejecutarRespaldo($tipo);

            if ($resultado['success']) {
                CLI::write('✓ Respaldo completado exitosamente', 'green');
                CLI::write("  Archivo: {$resultado['archivo']}", 'white');
                CLI::write("  Tamaño: {$resultado['tamaño']}", 'white');
                CLI::write("  Ruta: {$resultado['ruta']}", 'white');
                CLI::newLine();

                // Limpiar respaldos antiguos
                CLI::write('Limpiando respaldos antiguos...', 'cyan');
                $limpieza = $this->limpiarRespaldosAntiguos();
                CLI::write("✓ {$limpieza['eliminados']} respaldos antiguos eliminados", 'green');

                // Guardar en log del sistema
                $this->guardarLogSistema($resultado, $tipo);

                CLI::newLine();
                CLI::write('=== PROCESO COMPLETADO ===', 'green');
            } else {
                CLI::error('✗ Error al crear respaldo: ' . $resultado['error']);
                $this->enviarAlertaError($resultado['error']);
            }

        } catch (\Exception $e) {
            CLI::error('✗ Excepción: ' . $e->getMessage());
            $this->enviarAlertaError($e->getMessage());
        }
    }

    /**
     * Ejecutar respaldo de la base de datos
     */
    private function ejecutarRespaldo($tipo)
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
            CLI::write('Comprimiendo archivo...', 'cyan');
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
                'descripcion' => "Respaldo automático {$tipo}",
                'archivo' => $nombreArchivo,
                'ruta' => $rutaArchivo,
                'tamaño' => filesize($rutaArchivo),
                'fecha' => date('Y-m-d H:i:s'),
                'usuario' => 'SISTEMA'
            ]);

            return [
                'success' => true,
                'archivo' => $nombreArchivo,
                'ruta' => $rutaArchivo,
                'tamaño' => $this->formatearTamaño(filesize($rutaArchivo))
            ];

        } catch (\Exception $e) {
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
        $bufferSize = 4096;
        $file = fopen($origen, 'rb');
        $gzFile = gzopen($destino, 'wb9');

        while (!feof($file)) {
            gzwrite($gzFile, fread($file, $bufferSize));
        }

        fclose($file);
        gzclose($gzFile);
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

        file_put_contents($archivoLog, json_encode($registros, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    }

    /**
     * Limpiar respaldos antiguos según política de retención
     */
    private function limpiarRespaldosAntiguos()
    {
        $eliminados = 0;
        $archivoLog = $this->config['ruta_base'] . DIRECTORY_SEPARATOR . 'respaldos_log.json';

        if (!file_exists($archivoLog)) {
            return ['eliminados' => 0];
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
        file_put_contents($archivoLog, json_encode($respaldosActualizados, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

        return ['eliminados' => $eliminados];
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

    /**
     * Guardar en log del sistema
     */
    private function guardarLogSistema($resultado, $tipo)
    {
        $mensaje = sprintf(
            "[RESPALDO %s] Archivo: %s | Tamaño: %s | Ruta: %s",
            strtoupper($tipo),
            $resultado['archivo'],
            $resultado['tamaño'],
            $resultado['ruta']
        );

        log_message('info', $mensaje);
    }

    /**
     * Enviar alerta de error (puedes implementar envío de correo aquí)
     */
    private function enviarAlertaError($error)
    {
        $mensaje = sprintf(
            "[RESPALDO FALLIDO] Fecha: %s | Error: %s",
            date('Y-m-d H:i:s'),
            $error
        );

        log_message('critical', $mensaje);

        // TODO: Implementar envío de correo electrónico
        // mail('admin@hospital.com', 'Error en Respaldo BD', $mensaje);
    }
}
