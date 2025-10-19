
<?php if (session()->get('rol_id') == 1): ?>

    <!-- Main Container adaptado para vista dual con escala optimizada -->
    <div class="w-full px-2 space-y-4 text-sm scale-90 origin-top">

        <!-- Page Title - Compacto para vista dual -->
        <div class="mb-4">
            <div class="bg-green-600 text-white px-4 py-2 rounded-lg shadow-sm">
                <h1 class="text-lg font-semibold flex items-center">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z">
                        </path>
                    </svg>
                    SNS-MSP / HCU-form.005/2021 - EVOLUCIÓN Y PRESCRIPCIONES
                </h1>
                <p class="text-xs opacity-90 mt-1">
                    <?php if (isset($cedula)): ?>
                        Paciente: <?= htmlspecialchars($cedula) ?> |
                    <?php endif; ?>
                    <?php if (isset($primer_nombre) && isset($primer_apellido)): ?>
                        <?= htmlspecialchars($primer_nombre . ' ' . $primer_apellido) ?>
                    <?php endif; ?>
                </p>
            </div>
        </div>

        <!-- A. DATOS DEL ESTABLECIMIENTO Y USUARIO/PACIENTE -->
        <div class="bg-white border border-gray-200 rounded-lg shadow-sm mb-4">
            <div class="bg-green-600 text-white px-4 py-3 rounded-t-lg">
                <h2 class="font-semibold text-base">A. DATOS DEL ESTABLECIMIENTO Y USUARIO / PACIENTE</h2>
            </div>
            <div class="p-4">
                <!-- Primera fila -->
                <div class="grid grid-cols-1 md:grid-cols-6 gap-4 mb-6">
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">INSTITUCIÓN DEL SISTEMA</label>
                        <input type="text"
                            class="w-full px-3 py-2 text-sm border border-gray-300 rounded-md bg-gray-50 focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                            readonly value="<?= esc($estab_institucion) ?>">
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">UNICÓDIGO</label>
                        <input type="text"
                            class="w-full px-3 py-2 text-sm border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                            name="ep_unicodigo" value="<?= esc($estab_unicode) ?>" readonly>
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">ESTABLECIMIENTO DE SALUD</label>
                        <input type="text"
                            class="w-full px-3 py-2 text-sm border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                            name="ep_establecimiento" value="<?= esc($estab_nombre) ?>""
                            readonly>
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">NÚMERO DE HISTORIA CLÍNICA
                            ÚNICA</label>
                        <input type="text"
                            class="w-full px-3 py-2 text-sm border border-gray-300 rounded-md bg-gray-50 focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                            id="ep_historia_clinica" name="ep_historia_clinica" readonly>
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">NÚMERO DE ARCHIVO</label>
                        <input type="text"
                            class="w-full px-3 py-2 text-sm border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                            id="ep_numero_archivo" name="ep_numero_archivo" readonly>
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">NO. HOJA</label>
                        <input type="text"
                            class="w-full px-3 py-2 text-sm border border-gray-300 rounded-md bg-gray-50 focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                            id="ep_numero_hoja" name="ep_numero_hoja" readonly>
                    </div>
                </div>

                <!-- Segunda fila - Datos del paciente -->
                <div class="grid grid-cols-1 md:grid-cols-6 gap-4 mb-6">
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">PRIMER APELLIDO</label>
                        <input type="text"
                            class="w-full px-3 py-2 text-sm border border-gray-300 rounded-md bg-gray-50 focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                            id="ep_primer_apellido" name="ep_primer_apellido"
                            readonly>
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">SEGUNDO APELLIDO</label>
                        <input type="text"
                            class="w-full px-3 py-2 text-sm border border-gray-300 rounded-md bg-gray-50 focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                            id="ep_segundo_apellido" name="ep_segundo_apellido"
                            readonly>
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">PRIMER NOMBRE</label>
                        <input type="text"
                            class="w-full px-3 py-2 text-sm border border-gray-300 rounded-md bg-gray-50 focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                            id="ep_primer_nombre" name="ep_primer_nombre"
                            readonly>
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">SEGUNDO NOMBRE</label>
                        <input type="text"
                            class="w-full px-3 py-2 text-sm border border-gray-300 rounded-md bg-gray-50 focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                            id="ep_segundo_nombre" name="ep_segundo_nombre"
                            readonly>
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">SEXO</label>
                        <input type="text"
                            class="w-full px-3 py-2 text-sm border border-gray-300 rounded-md bg-gray-50 focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                            id="ep_sexo" name="ep_sexo" readonly>
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">EDAD</label>
                        <input type="text"
                            class="w-full px-3 py-2 text-sm border border-gray-300 rounded-md bg-gray-50 focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                            id="ep_edad" name="ep_edad" readonly>
                    </div>
                </div>

                <!-- Condición de edad -->
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-3">CONDICIÓN EDAD (MARCAR)</label>
                    <div class="flex space-x-8 text-sm">
                        <label class="inline-flex items-center">
                            <input type="radio" name="ep_condicion_edad" value="H"
                                class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 focus:ring-blue-500 focus:ring-2"
                                <?= (isset($condicion_edad) && $condicion_edad == 'H') ? 'checked' : '' ?>>
                            <span class="ml-2 text-gray-700">H (Horas)</span>
                        </label>
                        <label class="inline-flex items-center">
                            <input type="radio" name="ep_condicion_edad" value="D"
                                class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 focus:ring-blue-500 focus:ring-2"
                                <?= (isset($condicion_edad) && $condicion_edad == 'D') ? 'checked' : '' ?>>
                            <span class="ml-2 text-gray-700">D (Días)</span>
                        </label>
                        <label class="inline-flex items-center">
                            <input type="radio" name="ep_condicion_edad" value="M"
                                class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 focus:ring-blue-500 focus:ring-2"
                                <?= (isset($condicion_edad) && $condicion_edad == 'M') ? 'checked' : '' ?>>
                            <span class="ml-2 text-gray-700">M (Meses)</span>
                        </label>
                        <label class="inline-flex items-center">
                            <input type="radio" name="ep_condicion_edad" value="A"
                                class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 focus:ring-blue-500 focus:ring-2"
                                <?= (isset($condicion_edad) && $condicion_edad == 'A') ? 'checked' : '' ?>>
                            <span class="ml-2 text-gray-700">A (Años)</span>
                        </label>
                    </div>
                </div>
            </div>
        </div>

        <!-- B. EVOLUCIÓN Y PRESCRIPCIONES -->
        <div class="bg-white border border-gray-200 rounded-lg shadow-sm mb-4">
            <div class="bg-blue-500 text-white px-4 py-2 rounded-t-lg">
                <h2 class="font-semibold text-base mb-1">B. EVOLUCIÓN Y PRESCRIPCIONES</h2>
                <p class="text-sm opacity-90">FIRMAR AL PIE DE CADA EVOLUCIÓN Y PRESCRIPCIÓN | REGISTRAR CON ROJO LA
                    ADMINISTRACIÓN DE FÁRMACOS Y COLOCACIÓN DE DISPOSITIVOS MÉDICOS</p>
            </div>
            <div class="p-0">
                <div class="overflow-x-auto">
                    <table class="w-full border-collapse text-sm">
                        <thead>
                            <tr class="bg-gray-700 text-white">
                                <th rowspan="2" class="border border-gray-400 px-4 py-4 text-center align-middle min-w-32">
                                    <div class="font-semibold">FECHA</div>
                                    <div class="text-xs opacity-75 mt-1">(aaaa-mm-dd)</div>
                                </th>
                                <th rowspan="2" class="border border-gray-400 px-4 py-4 text-center align-middle min-w-24">
                                    <div class="font-semibold">HORA</div>
                                    <div class="text-xs opacity-75 mt-1">(hh:mm)</div>
                                </th>
                                <th class="border border-gray-400 px-4 py-3 text-center">
                                    <div class="font-semibold">1. EVOLUCIÓN</div>
                                </th>
                                <th colspan="2" class="border border-gray-400 px-4 py-3 text-center">
                                    <div class="font-semibold">2. PRESCRIPCIONES</div>
                                </th>
                            </tr>
                            <tr class="bg-gray-700 text-white">
                                <th class="border border-gray-400 px-4 py-3 text-center">
                                    <div class="font-semibold">NOTAS DE EVOLUCIÓN</div>
                                </th>
                                <th class="border border-gray-400 px-4 py-3 text-center">
                                    <div class="font-semibold">FARMACOTERAPIA E INDICACIONES</div>
                                    <div class="text-xs opacity-75 mt-1">(Para enfermería y otro profesional de salud)</div>
                                </th>
                                <th class="border border-gray-400 px-4 py-3 text-center min-w-24">
                                    <div class="font-semibold">ADMINISTR. FÁRMACOS DISPOSITIVO</div>
                                </th>
                            </tr>
                        </thead>
                        <tbody id="tablaEvolucionPrescripcionesBody">
                            <!-- Las filas se generarán dinámicamente aquí o se cargarán desde la base de datos -->
                            <tr>
                                <td colspan="5" class="border border-gray-400 px-4 py-8 text-center text-gray-500">
                                    <div class="flex flex-col items-center">
                                        <i class="fas fa-file-medical text-4xl mb-4 text-gray-300"></i>
                                        <p class="text-lg">No hay registros de evolución y prescripciones</p>
                                        <p class="text-sm mt-2">Los registros aparecerán aquí cuando se agreguen datos</p>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    </div>

<?php else: ?>
    <div class="bg-red-100 text-red-700 p-4 rounded border-l-4 border-red-500">
        <div class="flex items-center">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
            No tienes permisos para acceder a esta sección.
        </div>
    </div>
<?php endif; ?>
