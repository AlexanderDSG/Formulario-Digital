
<div class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50"
                        id="modalAsignarEspecialidad">
                        <div class="bg-white rounded-lg shadow-xl max-w-2xl w-full mx-4 max-h-screen overflow-y-auto">
                            <div class="bg-yellow-500 text-white px-6 py-4 flex justify-between items-center">
                                <h4 class="text-xl font-bold flex items-center">
                                    <i class="fas fa-hospital mr-3"></i>
                                    Asignar a Especialidad
                                </h4>
                                <button type="button" class="text-white hover:text-gray-200 text-2xl"
                                    onclick="cerrarModal('modalAsignarEspecialidad')">
                                    <span>&times;</span>
                                </button>
                            </div>

                            <div class="p-6">
                                <form id="form-asignar-especialidad">
                                    <input type="hidden" id="ate_codigo_asignar" name="ate_codigo">

                                    <!-- Información del paciente -->
                                    <div class="grid md:grid-cols-2 gap-4 mb-6">
                                        <div>
                                            <h5 id="paciente-nombre-modal" class="text-xl font-bold text-blue-600 mb-2">
                                            </h5>
                                            <p class="text-gray-700 mb-2">
                                                <strong>Cédula:</strong> <span id="paciente-cedula-modal"></span>
                                            </p>
                                            <p class="text-gray-700 mb-2">
                                                <strong>Triaje:</strong> <span id="paciente-triaje-modal"></span>
                                            </p>
                                        </div>
                                    </div>

                                    <!-- Selección de especialidad -->
                                    <div class="mb-4">
                                        <label for="esp_codigo_asignar" class="block text-gray-700 font-semibold mb-2">
                                            <i class="fas fa-stethoscope mr-2"></i>
                                            Especialidad de destino:
                                        </label>
                                        <select
                                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                            id="esp_codigo_asignar" name="esp_codigo" required>
                                            <option value="">Seleccione una especialidad...</option>
                                        </select>
                                    </div>

                                    <!-- Observaciones -->
                                    <div class="mb-4">
                                        <label for="observaciones_asignar" class="block text-gray-700 font-semibold mb-2">
                                            Observaciones:
                                        </label>
                                        <textarea
                                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                            id="observaciones_asignar" name="observaciones" rows="3"
                                            placeholder="Motivo de derivación, instrucciones especiales, etc."></textarea>
                                    </div>

                                    <!-- Información importante -->
                                    <div class="bg-blue-50 border-l-4 border-blue-400 p-4 rounded">
                                        <div class="flex items-start">
                                            <i class="fas fa-info-circle text-blue-400 mr-3 mt-1"></i>
                                            <div>
                                                <p class="text-blue-800">
                                                    <strong>Importante:</strong> Una vez asignado a especialidad, el
                                                    paciente aparecerá en la lista correspondiente y podrá ser tomado
                                                    por los médicos de esa área.
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                </form>
                            </div>
                            <!-- Botones del modal -->
                    <div class="bg-gray-50 px-6 py-4 flex justify-end space-x-3">
                        <button type="button"
                            class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-lg transition-colors duration-200 flex items-center"
                            onclick="cerrarModal('modalAsignarEspecialidad')">
                            <i class="fas fa-times mr-2"></i> Cancelar
                        </button>
                        <button type="button"
                            class="bg-yellow-500 hover:bg-yellow-600 text-white px-4 py-2 rounded-lg transition-colors duration-200 flex items-center"
                            onclick="confirmarAsignacion()">
                            <i class="fas fa-paper-plane mr-2"></i> Asignar a Especialidad
                        </button>
                    </div>