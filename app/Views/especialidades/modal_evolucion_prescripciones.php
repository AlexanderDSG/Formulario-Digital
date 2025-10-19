<!-- Modal de Evolución y Prescripciones con Tailwind CSS -->
<div class="fixed inset-0 z-50 overflow-y-auto hidden" id="modalEvolucionPrescripciones" aria-labelledby="modalEvolucionPrescripcionesLabel">
    <!-- Backdrop -->
    <div class="fixed inset-0 bg-black bg-opacity-50 transition-opacity"></div>

    <!-- Modal Container -->
    <div class="flex min-h-full items-center justify-center p-4">
        <div class="relative bg-white rounded-lg shadow-xl w-full max-w-7xl max-h-screen overflow-hidden">
            
            <!-- Header del Modal -->
            <div class="bg-blue-600 text-white px-6 py-4 flex items-center justify-between">
                <h5 class="text-lg font-semibold flex items-center">
                    <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                    SNS-MSP / HCU-form.005/2021 - EVOLUCIÓN Y PRESCRIPCIONES
                </h5>
                <button type="button" class="text-white hover:text-gray-200 text-2xl font-bold leading-none transition-colors" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>

            <!-- Body del Modal -->
            <div class="p-6 max-h-96 overflow-y-auto">
                
                <!-- A. DATOS DEL ESTABLECIMIENTO Y USUARIO/PACIENTE -->
                <div class="bg-white border border-gray-200 rounded-lg shadow-sm mb-6">
                    <div class="bg-green-600 text-white px-4 py-2 rounded-t-lg">
                        <h6 class="font-semibold text-sm">A. DATOS DEL ESTABLECIMIENTO Y USUARIO / PACIENTE</h6>
                    </div>
                    <div class="p-4">
                        <!-- Primera fila -->
                        <div class="grid grid-cols-1 md:grid-cols-6 gap-4 mb-4">
                            <div>
                                <label class="block text-xs font-semibold text-gray-700 mb-1">INSTITUCIÓN DEL SISTEMA</label>
                                <input type="text" class="w-full px-2 py-1 text-xs border border-gray-300 rounded-md bg-gray-50 focus:ring-2 focus:ring-blue-500 focus:border-blue-500" readonly value="MSP">
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-gray-700 mb-1">UNICÓDIGO</label>
                                <input type="text" class="w-full px-2 py-1 text-xs border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-blue-500" name="ep_unicodigo" value="<?= htmlspecialchars($unicodigo ?? '') ?>" readonly>
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-gray-700 mb-1">ESTABLECIMIENTO DE SALUD</label>
                                <input type="text" class="w-full px-2 py-1 text-xs border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-blue-500" name="ep_establecimiento" value="<?= htmlspecialchars($nombre_establecimiento ?? '') ?>" readonly> 
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-gray-700 mb-1">NÚMERO DE HISTORIA CLÍNICA ÚNICA</label>
                                <input type="text" class="w-full px-2 py-1 text-xs border border-gray-300 rounded-md bg-gray-50 focus:ring-2 focus:ring-blue-500 focus:border-blue-500" name="ep_historia_clinica" value="<?= htmlspecialchars($cedula ?? '') ?>" readonly>
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-gray-700 mb-1">NÚMERO DE ARCHIVO</label>
                                <input type="text" class="w-full px-2 py-1 text-xs border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-blue-500" name="ep_numero_archivo" value="<?= htmlspecialchars($numero_archivo ?? '') ?>" readonly>
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-gray-700 mb-1">NO. HOJA</label>
                                <input type="text" class="w-full px-2 py-1 text-xs border border-gray-300 rounded-md bg-gray-50 focus:ring-2 focus:ring-blue-500 focus:border-blue-500" name="ep_numero_hoja" value="<?= htmlspecialchars($numero_hoja ?? '00001') ?>" readonly>
                            </div>
                        </div>

                        <!-- Segunda fila - Datos del paciente -->
                        <div class="grid grid-cols-1 md:grid-cols-6 gap-4 mb-4">
                            <div>
                                <label class="block text-xs font-semibold text-gray-700 mb-1">PRIMER APELLIDO</label>
                                <input type="text" class="w-full px-2 py-1 text-xs border border-gray-300 rounded-md bg-gray-50 focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                       value="<?= htmlspecialchars(is_array($primer_apellido ?? '') ? '' : ($primer_apellido ?? '')) ?>" readonly>
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-gray-700 mb-1">SEGUNDO APELLIDO</label>
                                <input type="text" class="w-full px-2 py-1 text-xs border border-gray-300 rounded-md bg-gray-50 focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                       value="<?= htmlspecialchars(is_array($segundo_apellido ?? '') ? '' : ($segundo_apellido ?? '')) ?>" readonly>
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-gray-700 mb-1">PRIMER NOMBRE</label>
                                <input type="text" class="w-full px-2 py-1 text-xs border border-gray-300 rounded-md bg-gray-50 focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                       value="<?= htmlspecialchars(is_array($primer_nombre ?? '') ? '' : ($primer_nombre ?? '')) ?>" readonly>
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-gray-700 mb-1">SEGUNDO NOMBRE</label>
                                <input type="text" class="w-full px-2 py-1 text-xs border border-gray-300 rounded-md bg-gray-50 focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                       value="<?= htmlspecialchars(is_array($segundo_nombre ?? '') ? '' : ($segundo_nombre ?? '')) ?>" readonly>
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-gray-700 mb-1">SEXO</label>
                                <input type="text" class="w-full px-2 py-1 text-xs border border-gray-300 rounded-md bg-gray-50 focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                       value="<?= htmlspecialchars($sexo ?? '') ?>" readonly>
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-gray-700 mb-1">EDAD</label>
                                <input type="text" class="w-full px-2 py-1 text-xs border border-gray-300 rounded-md bg-gray-50 focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                       value="<?= htmlspecialchars(is_array($edad ?? '') ? '' : ($edad ?? '')) ?>" readonly>
                            </div>
                        </div>

                        
                        <!-- Condición de edad -->
                        <div>
                            <label class="block text-xs font-semibold text-gray-700 mb-2">CONDICIÓN EDAD (MARCAR)</label>
                            <div class="flex space-x-6 text-xs">
                                <label class="inline-flex items-center">
                                    <input type="radio" name="ep_condicion_edad" value="H" class="w-3 h-3 text-blue-600 bg-gray-100 border-gray-300 focus:ring-blue-500 focus:ring-2" <?= (isset($condicion_edad) && $condicion_edad == 'H') ? 'checked' : '' ?>>
                                    <span class="ml-2 text-gray-700">H (Horas)</span>
                                </label>
                                <label class="inline-flex items-center">
                                    <input type="radio" name="ep_condicion_edad" value="D" class="w-3 h-3 text-blue-600 bg-gray-100 border-gray-300 focus:ring-blue-500 focus:ring-2" <?= (isset($condicion_edad) && $condicion_edad == 'D') ? 'checked' : '' ?>>
                                    <span class="ml-2 text-gray-700">D (Días)</span>
                                </label>
                                <label class="inline-flex items-center">
                                    <input type="radio" name="ep_condicion_edad" value="M" class="w-3 h-3 text-blue-600 bg-gray-100 border-gray-300 focus:ring-blue-500 focus:ring-2" <?= (isset($condicion_edad) && $condicion_edad == 'M') ? 'checked' : '' ?>>
                                    <span class="ml-2 text-gray-700">M (Meses)</span>
                                </label>
                                <label class="inline-flex items-center">
                                    <input type="radio" name="ep_condicion_edad" value="A" class="w-3 h-3 text-blue-600 bg-gray-100 border-gray-300 focus:ring-blue-500 focus:ring-2" <?= (isset($condicion_edad) && $condicion_edad == 'A') ? 'checked' : '' ?>>
                                    <span class="ml-2 text-gray-700">A (Años)</span>
                                </label>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- B. EVOLUCIÓN Y PRESCRIPCIONES -->
                <div class="bg-white border border-gray-200 rounded-lg shadow-sm">
                    <div class="bg-blue-500 text-white px-4 py-3 rounded-t-lg">
                        <h6 class="font-semibold text-sm mb-1">B. EVOLUCIÓN Y PRESCRIPCIONES</h6>
                        <p class="text-xs opacity-90">FIRMAR AL PIE DE CADA EVOLUCIÓN Y PRESCRIPCIÓN | REGISTRAR CON ROJO LA ADMINISTRACIÓN DE FÁRMACOS Y COLOCACIÓN DE DISPOSITIVOS MÉDICOS</p>
                    </div>
                    <div class="p-0">
                        <div class="overflow-x-auto">
                            <table class="w-full border-collapse text-xs">
                                <thead>
                                    <tr class="bg-gray-700 text-white">
                                        <th rowspan="2" class="border border-gray-400 px-3 py-3 text-center align-middle min-w-24">
                                            <div class="font-semibold">FECHA</div>
                                            <div class="text-xs opacity-75 mt-1">(aaaa-mm-dd)</div>
                                        </th>
                                        <th rowspan="2" class="border border-gray-400 px-3 py-3 text-center align-middle min-w-20">
                                            <div class="font-semibold">HORA</div>
                                            <div class="text-xs opacity-75 mt-1">(hh:mm)</div>
                                        </th>
                                        <th class="border border-gray-400 px-3 py-2 text-center">
                                            <div class="font-semibold">1. EVOLUCIÓN</div>
                                        </th>
                                        <th colspan="2" class="border border-gray-400 px-3 py-2 text-center">
                                            <div class="font-semibold">2. PRESCRIPCIONES</div>
                                        </th>
                                        <th class="border border-gray-400 px-3 py-2 text-center">
                                            <div class="font-semibold">ACCIONES</div>
                                        </th>
                                    </tr>
                                    <tr class="bg-gray-700 text-white">
                                        <th class="border border-gray-400 px-3 py-2 text-center">
                                            <div class="font-semibold">NOTAS DE EVOLUCIÓN</div>
                                        </th>
                                        <th class="border border-gray-400 px-3 py-2 text-center">
                                            <div class="font-semibold">FARMACOTERAPIA E INDICACIONES</div>
                                            <div class="text-xs opacity-75 mt-1">(Para enfermería y otro profesional de salud)</div>
                                        </th>
                                        <th class="border border-gray-400 px-3 py-2 text-center min-w-20">
                                            <div class="font-semibold">ADMINISTR. FÁRMACOS DISPOSITIVO</div>
                                        </th>
                                        <th class="border border-gray-400 px-3 py-2 text-center min-w-16">
                                            <div class="font-semibold">ACCIONES</div>
                                        </th>
                                    </tr>
                                </thead>
                                <tbody id="tablaEvolucionPrescripcionesBody">
                                    <!-- Las filas se generarán dinámicamente aquí -->
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Botones para agregar filas -->
                <div class="text-center mt-6 space-x-4">
                    <button type="button"
                            class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg transition-colors duration-200 shadow-md hover:shadow-lg"
                            onclick="addNewRow()">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                        </svg>
                        Agregar Fila
                    </button>
                    <button type="button"
                            class="inline-flex items-center px-4 py-2 bg-gray-600 hover:bg-gray-700 text-white text-sm font-medium rounded-lg transition-colors duration-200 shadow-md hover:shadow-lg"
                            onclick="clearTable()">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                        </svg>
                        Limpiar Todo
                    </button>
                </div>

            </div>

            <!-- Footer del Modal -->
            <div class="bg-gray-50 px-6 py-4 flex items-center justify-end space-x-3 border-t border-gray-200">
                <button type="button" class="inline-flex items-center px-4 py-2 bg-gray-600 hover:bg-gray-700 text-white text-sm font-medium rounded-lg transition-colors duration-200 shadow-md hover:shadow-lg" data-dismiss="modal">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                    Cancelar
                </button>
                
                <button type="button" class="inline-flex items-center px-4 py-2 bg-green-600 hover:bg-green-700 text-white text-sm font-medium rounded-lg transition-colors duration-200 shadow-md hover:shadow-lg" id="btnGuardarEvolucionPrescripciones" onclick="guardarEvolucionPrescripciones()">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4"></path>
                    </svg>
                    Guardar
                </button>
            </div>
        </div>
    </div>
</div>


