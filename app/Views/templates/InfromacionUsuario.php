
<?php
// Mapeo de roles según tu base de datos
$roles = [
    1 => ['nombre' => 'ADMINISTRADOR', 'color' => 'bg-purple-100 text-purple-800'],
    2 => ['nombre' => 'ADMISIONISTA', 'color' => 'bg-green-100 text-green-800'],
    3 => ['nombre' => 'ENFERMERÍA', 'color' => 'bg-blue-100 text-blue-800'],
    4 => ['nombre' => 'MÉDICO TRIAJE', 'color' => 'bg-orange-100 text-orange-800'],
    5 => ['nombre' => 'MÉDICO ESPECIALISTA', 'color' => 'bg-yellow-100 text-yellow-800']
];

// Obtener rol actual del usuario
$rolId = session()->get('rol_id');
$rolInfo = $roles[$rolId] ?? ['nombre' => 'USUARIO', 'color' => 'bg-gray-100 text-gray-800'];

?>

<!-- Header de información del usuario -->
<div class="flex items-center justify-between">
    <div class="flex items-center space-x-4">
        <div class="w-10 h-10 bg-blue-600 rounded-full flex items-center justify-center">
            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
            </svg>
        </div>
        <div>
            <h2 class="text-lg font-semibold text-gray-900">
                <?= esc(session()->get('usu_nombre')) ?> <?= esc(session()->get('usu_apellido')) ?>
            </h2>
            <div class="flex items-center space-x-2">
                <span
                    class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium <?= $rolInfo['color'] ?>">

                    <?= $rolInfo['nombre'] ?>
                </span>
                <?php if (!empty(session()->get('usu_nro_documento'))): ?>
                    <span class="text-xs text-gray-500">
                        • <?= esc(session()->get('usu_nro_documento')) ?>
                    </span>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <div class="text-right">
        <?= $this->include('templates/cerrar_sesion') ?>
        <p class="text-sm text-gray-500">Sesión iniciada</p>
        <p class="text-sm font-medium text-gray-900" id="fecha-hora-actual">
            <?= date('d/m/Y H:i:s') ?>
        </p>
    </div>
</div>

<!-- Script para actualizar la hora en tiempo real -->
<script>
    document.addEventListener('DOMContentLoaded', function () {
        function actualizarHora() {
            const ahora = new Date();
            const fecha = ahora.toLocaleDateString('es-ES', {
                day: '2-digit',
                month: '2-digit',
                year: 'numeric'
            });
            const hora = ahora.toLocaleTimeString('es-ES', {
                hour: '2-digit',
                minute: '2-digit',
                second: '2-digit'
            });

            const elemento = document.getElementById('fecha-hora-actual');
            if (elemento) {
                elemento.textContent = fecha + ' ' + hora;
            }
        }

        // Actualizar cada segundo
        setInterval(actualizarHora, 1000);
        actualizarHora(); // Ejecutar inmediatamente
    });
</script>