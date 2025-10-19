<?php if (session()->get('rol_id') == 1): ?>

<!-- Dashboard de Estadísticas de Atenciones -->
<div class="space-y-6">

    <!-- Tarjetas de Estadísticas Principales -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">

        <!-- Total Atenciones Hoy -->
        <div class="bg-gradient-to-br from-blue-500 to-blue-600 rounded-lg shadow-lg p-6 text-white">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-blue-100 uppercase tracking-wider">Atenciones Hoy</p>
                    <p id="stat-atenciones-hoy" class="text-4xl font-bold mt-2">0</p>
                    <p id="stat-atenciones-hoy-fecha" class="text-xs text-blue-100 mt-1"></p>
                </div>
                <div class="p-3 bg-white rounded-full">
                    <i class="fas fa-calendar-day text-3xl text-blue-600"></i>
                </div>
            </div>
        </div>

        <!-- Total Atenciones Semana -->
        <div class="bg-gradient-to-br from-green-500 to-green-600 rounded-lg shadow-lg p-6 text-white">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-green-100 uppercase tracking-wider">Esta Semana</p>
                    <p id="stat-atenciones-semana" class="text-4xl font-bold mt-2">0</p>
                    <p class="text-xs text-green-100 mt-1">Últimos 7 días</p>
                </div>
                <div class="p-3 bg-white rounded-full">
                    <i class="fas fa-calendar-week text-3xl text-green-600"></i>
                </div>
            </div>
        </div>

        <!-- Total Atenciones Mes -->
        <div class="bg-gradient-to-br from-purple-500 to-purple-600 rounded-lg shadow-lg p-6 text-white">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-purple-100 uppercase tracking-wider">Este Mes</p>
                    <p id="stat-atenciones-mes" class="text-4xl font-bold mt-2">0</p>
                    <p id="stat-atenciones-mes-nombre" class="text-xs text-purple-100 mt-1"></p>
                </div>
                <div class="p-3 bg-white rounded-full">
                    <i class="fas fa-calendar-alt text-3xl text-purple-600"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Gráficos -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

        <!-- Gráfico de Tendencias (Últimos 7 días) -->
        <div class="bg-white rounded-lg shadow-md p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-gray-800">
                    <i class="fas fa-chart-line text-indigo-600 mr-2"></i>
                    Tendencia de Atenciones
                </h3>
                <span class="text-xs text-gray-500">Últimos 7 días</span>
            </div>
            <div class="relative" style="height: 300px;">
                <canvas id="chart-tendencias"></canvas>
            </div>
        </div>

        <!-- Gráfico de Atenciones por Hora del Día -->
        <div class="bg-white rounded-lg shadow-md p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-gray-800">
                    <i class="fas fa-clock text-blue-600 mr-2"></i>
                    Atenciones por Hora
                </h3>
                <span class="text-xs text-gray-500">Hoy</span>
            </div>
            <div class="relative" style="height: 300px;">
                <canvas id="chart-por-hora"></canvas>
            </div>
        </div>
    </div>

</div>

<?php else: ?>
    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded">
        <i class="fas fa-exclamation-triangle mr-2"></i>
        No tienes permisos para acceder a esta sección.
    </div>
<?php endif; ?>
