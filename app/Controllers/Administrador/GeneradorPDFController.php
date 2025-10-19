<?php

namespace App\Controllers\Administrador;

use App\Controllers\BaseController;

class GeneradorPDFController extends BaseController
{
    /**
     * Ruta base donde se guardarán los PDFs
     * Por defecto: C:\Users\Alex\Documents\PDF
     */
    private $rutaBasePDF = 'C:\PDF-Formulario-008-005';

    public function __construct()
    {
        // Puedes cambiar la ruta base aquí si lo necesitas
        // $this->rutaBasePDF = 'C:\otra\ruta\PDF';
    }

    /**
     * Guarda un PDF del formulario 005 organizando por mes
     */
    public function guardarPDF005()
    {
        // Verificar autenticación
        if (!session()->get('logged_in')) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'No autorizado'
            ])->setStatusCode(401);
        }

        try {
            // Obtener datos enviados desde JavaScript
            $json = $this->request->getJSON();

            if (!$json || !isset($json->pdfBase64)) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Datos del PDF no recibidos'
                ]);
            }

            $pdfBase64 = $json->pdfBase64;
            $nombreArchivo = $json->nombreArchivo ?? 'Formulario_005_' . date('Y-m-d_His') . '.pdf';

            // Crear carpeta por mes (formato: 2025-01)
            $carpetaMes = date('Y-m'); // Ejemplo: 2025-01
            $rutaCompletaMes = $this->rutaBasePDF . DIRECTORY_SEPARATOR . $carpetaMes;

            // Crear carpeta si no existe
            if (!is_dir($rutaCompletaMes)) {
                if (!mkdir($rutaCompletaMes, 0777, true)) {
                    return $this->response->setJSON([
                        'success' => false,
                        'message' => 'No se pudo crear la carpeta del mes: ' . $carpetaMes
                    ]);
                }
            }

            // Decodificar el PDF desde base64
            $pdfData = base64_decode(preg_replace('/^data:application\/pdf;base64,/', '', $pdfBase64));

            // Ruta completa del archivo
            $rutaArchivo = $rutaCompletaMes . DIRECTORY_SEPARATOR . $nombreArchivo;

            // Guardar el archivo
            if (file_put_contents($rutaArchivo, $pdfData) === false) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Error al guardar el archivo PDF'
                ]);
            }

            return $this->response->setJSON([
                'success' => true,
                'message' => 'PDF guardado exitosamente',
                'ruta' => $rutaArchivo,
                'carpeta_mes' => $carpetaMes
            ]);

        } catch (\Exception $e) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Error al procesar el PDF: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Guarda un PDF del formulario 008 organizando por mes
     */
    public function guardarPDF008()
    {
        // Verificar autenticación
        if (!session()->get('logged_in')) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'No autorizado'
            ])->setStatusCode(401);
        }

        try {
            // Obtener datos enviados desde JavaScript
            $json = $this->request->getJSON();

            if (!$json || !isset($json->pdfBase64)) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Datos del PDF no recibidos'
                ]);
            }

            $pdfBase64 = $json->pdfBase64;
            $nombreArchivo = $json->nombreArchivo ?? 'formulario_008_' . date('Y-m-d_His') . '.pdf';

            // Crear carpeta por mes (formato: 2025-01)
            $carpetaMes = date('Y-m'); // Ejemplo: 2025-01
            $rutaCompletaMes = $this->rutaBasePDF . DIRECTORY_SEPARATOR . $carpetaMes;

            // Crear carpeta si no existe
            if (!is_dir($rutaCompletaMes)) {
                if (!mkdir($rutaCompletaMes, 0777, true)) {
                    return $this->response->setJSON([
                        'success' => false,
                        'message' => 'No se pudo crear la carpeta del mes: ' . $carpetaMes
                    ]);
                }
            }

            // Decodificar el PDF desde base64
            $pdfData = base64_decode(preg_replace('/^data:application\/pdf;base64,/', '', $pdfBase64));

            // Ruta completa del archivo
            $rutaArchivo = $rutaCompletaMes . DIRECTORY_SEPARATOR . $nombreArchivo;

            // Guardar el archivo
            if (file_put_contents($rutaArchivo, $pdfData) === false) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Error al guardar el archivo PDF'
                ]);
            }

            return $this->response->setJSON([
                'success' => true,
                'message' => 'PDF guardado exitosamente',
                'ruta' => $rutaArchivo,
                'carpeta_mes' => $carpetaMes
            ]);

        } catch (\Exception $e) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Error al procesar el PDF: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Obtener lista de PDFs de un mes específico
     */
    public function listarPDFsPorMes($mes = null)
    {
        // Verificar autenticación
        if (!session()->get('logged_in')) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'No autorizado'
            ])->setStatusCode(401);
        }

        try {
            $mes = $mes ?? date('Y-m');
            $rutaCompletaMes = $this->rutaBasePDF . DIRECTORY_SEPARATOR . $mes;

            if (!is_dir($rutaCompletaMes)) {
                return $this->response->setJSON([
                    'success' => true,
                    'message' => 'No hay PDFs para este mes',
                    'archivos' => []
                ]);
            }

            $archivos = array_diff(scandir($rutaCompletaMes), ['.', '..']);
            $archivos = array_values(array_filter($archivos, function($archivo) {
                return pathinfo($archivo, PATHINFO_EXTENSION) === 'pdf';
            }));

            return $this->response->setJSON([
                'success' => true,
                'mes' => $mes,
                'total' => count($archivos),
                'archivos' => $archivos
            ]);

        } catch (\Exception $e) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Error al listar PDFs: ' . $e->getMessage()
            ]);
        }
    }
}
