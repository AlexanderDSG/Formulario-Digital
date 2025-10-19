<!-- Buscador -->
<div class="px-20 mb-6">
    <!-- Selector de fuente de datos -->
    <div class="mb-4">
        <p class="text-sm font-medium text-gray-700 mb-2">Fuente de datos:</p>
        <div class="flex space-x-2">
            <label class="relative cursor-pointer">
                <input type="radio" name="fuente_datos" value="local" checked class="sr-only peer">
                <div class="px-3 py-1.5 rounded-full border-2 border-gray-300 text-gray-600 text-xs font-medium transition-all
                peer-checked:bg-blue-500 peer-checked:border-blue-500 peer-checked:text-white
                hover:border-blue-400 hover:text-blue-600 peer-checked:hover:bg-blue-600">
                    Base Local
                </div>
            </label>
            <label class="relative cursor-pointer">
                <input type="radio" name="fuente_datos" value="hospital" class="sr-only peer">
                <div class="px-3 py-1.5 rounded-full border-2 border-gray-300 text-gray-600 text-xs font-medium transition-all
                peer-checked:bg-blue-500 peer-checked:border-blue-500 peer-checked:text-white
                hover:border-blue-400 hover:text-blue-600 peer-checked:hover:bg-blue-600">
                    Base del Hospital
                </div>
            </label>
        </div>
    </div>

    <!-- Campos de búsqueda en línea -->
    <div class="flex flex-wrap items-center gap-3 justify-end">
        <input type="text" placeholder="Ingrese la cédula" id="input-cedula" name="cedula"
            class="w-56 h-9 text-sm shadow rounded-md border border-gray-300 px-3" />
        <input type="text" placeholder="Buscar por apellido" id="buscar_apellido" name="buscar_apellido"
            class="w-56 h-9 text-sm shadow rounded-md border border-gray-300 px-3" />
        <input type="text" placeholder="Buscar por historia" id="input-historia-clinica" name="input-historia-clinica"
            class="w-56 h-9 text-sm shadow rounded-md border border-gray-300 px-3" />
        <button type="button" id="btn-buscar"
            class="bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold py-2 px-6 rounded-md shadow h-9">
            Buscar
        </button>
    </div>
</div>