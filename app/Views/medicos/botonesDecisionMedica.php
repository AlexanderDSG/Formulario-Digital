<!-- DECISIÓN MÉDICA -->
<div class="bg-white rounded-lg shadow-md mb-6" id="decision-medica">
    <div class="bg-yellow-500 text-white px-6 py-4">
        <h3 class="text-xl font-bold flex items-center">
            <i class="fas fa-stethoscope mr-3"></i>
            Decisión Médica
        </h3>
    </div>
    <div class="p-6">
        <div class="bg-blue-50 border-l-4 border-blue-400 p-4 mb-4">
            <p class="text-blue-800">
                <strong>Evaluación inicial completada.</strong>
                Seleccione el curso de acción según la gravedad del caso:
            </p>
        </div>

        <div class="grid md:grid-cols-2 gap-4">
            <!-- Botón para continuar atención completa -->
            <button type="button" id="btn-continuar-atencion"
                class="bg-green-500 hover:bg-green-600 text-white px-6 py-3 rounded-lg font-semibold flex flex-col items-center justify-center">
                <i class="fas fa-user-md mb-2 text-2xl"></i>
                <span>Continuar Atención Completa</span>
                <small class="text-green-100 mt-1">Casos estables - Completar formulario</small>
            </button>

            <!-- Botón para guardar y enviar a especialista -->
            <button type="button" id="btn-guardar-enviar-especialista"
                class="bg-red-500 hover:bg-red-600 text-white px-6 py-3 rounded-lg font-semibold flex flex-col items-center justify-center">
                <i class="fas fa-hospital mb-2 text-2xl"></i>
                <span>Enviar a Especialista</span>
                <small class="text-red-100 mt-1">Casos graves - Enviar a Area de Especialidad</small>
            </button>
        </div>
    </div>
</div>