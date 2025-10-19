<?php

namespace App\Controllers\Admisiones;

use App\Controllers\BaseController;

// Importar modelos existentes
use App\Models\Admision\GuardarSecciones\TipoDocumentoModel;
use App\Models\Admision\GuardarSecciones\GeneroModel;
use App\Models\Admision\GuardarSecciones\SeguroModel;
use App\Models\Admision\GuardarSecciones\EstadoCivilModel;
use App\Models\Admision\GuardarSecciones\NacionalidadModel;
use App\Models\Admision\GuardarSecciones\EmpresaModel;
use App\Models\Admision\GuardarSecciones\EtniaModel;
use App\Models\Admision\GuardarSecciones\NivelEducacionModel;
use App\Models\Admision\GuardarSecciones\EstadoEducacionModel;
use App\Models\Admision\GuardarSecciones\LlegadaModel;
use App\Models\Admision\GuardarSecciones\EstablecimientoModel;
use App\Models\Admision\EstablecimientoRegistroModel;
use App\Models\Admision\GuardarSecciones\NacionalidadIndigenaModel;
use App\Models\Admision\GuardarSecciones\PuebloIndigenaModel;

use App\Models\Admision\GuardarSecciones\ProvinciaModel;
use App\Models\Admision\GuardarSecciones\CantonModel;
use App\Models\Admision\GuardarSecciones\ParroquiaModel;
use App\Models\Admision\GuardarSecciones\ParroquiaUrbanaModel;

class DatosAdmisionesController extends BaseController
{
    public function index()
    {
        if (!session()->get('logged_in')) {
            return redirect()->to('/login');
        }

        // Datos básicos del usuario
        $nombreCompleto = session()->get('usu_nombre') . ' ' . session()->get('usu_apellido');
        $data = [
            'nombre_admisionista' => $nombreCompleto,
            'admisionista_nombre' => session()->get('rol_nombre') === 'ADMISIONISTA' ? $nombreCompleto : ''
        ];

        // Cargar datos del establecimiento
        $data = array_merge($data, $this->cargarDatosModelo(
            EstablecimientoModel::class,
            'establecimiento',
            'obtenerEstablecimientoActual',
            []
        ));

        // Procesar datos del establecimiento
        if (!empty($data['establecimiento'])) {
            $establecimiento = $data['establecimiento'];
            $data['estab_institucion'] = $establecimiento['est_institucion'] ?? '';
            $data['estab_unicode'] = $establecimiento['est_unicodigo'] ?? '';
            $data['estab_nombre'] = $establecimiento['est_nombre_establecimiento'] ?? '';
            unset($data['establecimiento']);
        } else {
            $data['estab_institucion'] = '';
            $data['estab_unicode'] = '';
            $data['estab_nombre'] = '';
        }

        // Obtener número de archivo
        try {
            $establecimientoRegistroModel = new EstablecimientoRegistroModel();
            $numeroArchivo = $establecimientoRegistroModel->generarNumeroArchivo();
            $data['estabArchivo'] = str_pad($numeroArchivo, 5, '0', STR_PAD_LEFT);
        } catch (\Exception $e) {
            $data['estabArchivo'] = '00001';
        }

        // Cargar todos los catálogos
        $data = array_merge($data, $this->cargarCatalogos());

        return view('admision\admisiones', $data);
    }

    private function cargarDatosModelo($modelClass, $nombreClave, $metodo = 'findAll', $valorPorDefecto = [])
    {
        try {
            $modelo = new $modelClass();
            return [$nombreClave => $modelo->$metodo()];
        } catch (\Exception $e) {
            return [$nombreClave => $valorPorDefecto];
        }
    }

    private function cargarCatalogos()
    {
        $catalogos = [
            [TipoDocumentoModel::class, 'tiposDocumento'],
            [GeneroModel::class, 'generos'],
            [SeguroModel::class, 'seguros'],
            [EstadoCivilModel::class, 'estadoCiviles'],
            [NacionalidadModel::class, 'nacionalidades'],
            [NacionalidadIndigenaModel::class, 'nacionalidadIndigena'],
            [PuebloIndigenaModel::class, 'puebloIndigena'],
            [EmpresaModel::class, 'empresas'],
            [EtniaModel::class, 'etnias'],
            [NivelEducacionModel::class, 'nivelesEducacion'],
            [EstadoEducacionModel::class, 'estadosEducacion'],
            [LlegadaModel::class, 'formasLlegada'],
            [ProvinciaModel::class, 'provincias', 'obtenerTodasProvincias'],
            [CantonModel::class, 'cantones', 'obtenerTodasCantones'],
            [ParroquiaModel::class, 'parroquias', 'obtenerTodasParroquia'],
        ];

        $data = [];
        foreach ($catalogos as $catalogoInfo) {
            $modelClass = $catalogoInfo[0];
            $nombreClave = $catalogoInfo[1];
            $metodo = $catalogoInfo[2] ?? 'findAll';
            
            $data = array_merge($data, $this->cargarDatosModelo($modelClass, $nombreClave, $metodo));
        }

        return $data;
    }
}