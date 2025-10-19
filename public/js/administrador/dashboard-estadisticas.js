/**
 * DASHBOARD DE ESTADÍSTICAS DE ATENCIONES
 * Gestiona la visualización de estadísticas y gráficos del panel de administrador
 */

// Variables globales para los gráficos
let chartTendencias = null;
let chartPorHora = null;

/**
 * Inicializar el dashboard de estadísticas
 */
function inicializarDashboardEstadisticas() {
    cargarEstadisticasPrincipales();
    cargarGraficoTendencias();
    cargarGraficoPorHora();

    // Actualizar cada 5 minutos
    setInterval(() => {
        actualizarDashboardEstadisticas();
    }, 5 * 60 * 1000);
}

/**
 * Cargar estadísticas principales (tarjetas)
 */
function cargarEstadisticasPrincipales() {
    const baseUrl = BASE_URL || window.location.origin + '/Formulario-Digital/';

    fetch(baseUrl + 'administrador/estadisticas/obtenerEstadisticas', {
        method: 'GET',
        headers: {
            'Content-Type': 'application/json'
        }
    })
    .then(response => response.json())
    .then(result => {
        if (result.success) {
            const data = result.data;

            // Actualizar tarjetas principales
            actualizarElemento('stat-atenciones-hoy', data.hoy);
            actualizarElemento('stat-atenciones-semana', data.semana);
            actualizarElemento('stat-atenciones-mes', data.mes);

            // Actualizar información adicional
            actualizarElemento('stat-atenciones-hoy-fecha', formatearFecha(data.fecha_hoy));
            actualizarElemento('stat-atenciones-mes-nombre', data.mes_nombre);

            // Estadísticas adicionales
            actualizarElemento('stat-completadas', data.completadas_hoy);
            actualizarElemento('stat-en-proceso', data.en_proceso_hoy);

            // Tiempo promedio
            if (data.tiempo_promedio) {
                const tiempo = formatearTiempo(data.tiempo_promedio);
                actualizarElemento('stat-tiempo-promedio', tiempo);
            } else {
                actualizarElemento('stat-tiempo-promedio', '--');
            }
        } else {
            console.error('Error al cargar estadísticas:', result.error);
        }
    })
    .catch(error => {
        console.error('Error de conexión:', error);
    });
}

/**
 * Cargar gráfico de tendencias (últimos 7 días)
 */
function cargarGraficoTendencias() {
    const baseUrl = BASE_URL || window.location.origin + '/Formulario-Digital/';

    fetch(baseUrl + 'administrador/estadisticas/obtenerTendencias', {
        method: 'GET',
        headers: {
            'Content-Type': 'application/json'
        }
    })
    .then(response => response.json())
    .then(result => {
        if (result.success) {
            renderizarGraficoTendencias(result.labels, result.data);
        } else {
            console.error('Error al cargar tendencias:', result.error);
        }
    })
    .catch(error => {
        console.error('Error de conexión:', error);
    });
}

/**
 * Cargar gráfico de atenciones por hora
 */
function cargarGraficoPorHora() {
    const baseUrl = BASE_URL || window.location.origin + '/Formulario-Digital/';

    fetch(baseUrl + 'administrador/estadisticas/obtenerAtencionesPorHora', {
        method: 'GET',
        headers: {
            'Content-Type': 'application/json'
        }
    })
    .then(response => response.json())
    .then(result => {
        if (result.success) {
            renderizarGraficoPorHora(result.labels, result.data);

            // Actualizar hora pico
            if (result.hora_pico) {
                actualizarElemento('stat-hora-pico', result.hora_pico);
            } else {
                actualizarElemento('stat-hora-pico', '--');
            }
        } else {
            console.error('Error al cargar atenciones por hora:', result.error);
        }
    })
    .catch(error => {
        console.error('Error de conexión:', error);
    });
}

/**
 * Renderizar gráfico de tendencias con Chart.js
 */
function renderizarGraficoTendencias(labels, data) {
    const canvas = document.getElementById('chart-tendencias');
    if (!canvas) return;

    const ctx = canvas.getContext('2d');

    // Destruir gráfico anterior si existe
    if (chartTendencias) {
        chartTendencias.destroy();
    }

    chartTendencias = new Chart(ctx, {
        type: 'line',
        data: {
            labels: labels,
            datasets: [{
                label: 'Atenciones',
                data: data,
                borderColor: 'rgb(99, 102, 241)',
                backgroundColor: 'rgba(99, 102, 241, 0.1)',
                borderWidth: 2,
                fill: true,
                tension: 0.4,
                pointRadius: 4,
                pointHoverRadius: 6,
                pointBackgroundColor: 'rgb(99, 102, 241)',
                pointBorderColor: '#fff',
                pointBorderWidth: 2
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                },
                tooltip: {
                    backgroundColor: 'rgba(0, 0, 0, 0.8)',
                    padding: 12,
                    titleFont: {
                        size: 14
                    },
                    bodyFont: {
                        size: 13
                    },
                    callbacks: {
                        label: function(context) {
                            return 'Atenciones: ' + context.parsed.y;
                        }
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        stepSize: 1,
                        font: {
                            size: 11
                        }
                    },
                    grid: {
                        color: 'rgba(0, 0, 0, 0.05)'
                    }
                },
                x: {
                    ticks: {
                        font: {
                            size: 11
                        }
                    },
                    grid: {
                        display: false
                    }
                }
            }
        }
    });
}

/**
 * Renderizar gráfico de atenciones por hora con Chart.js
 */
function renderizarGraficoPorHora(labels, data) {
    const canvas = document.getElementById('chart-por-hora');
    if (!canvas) return;

    const ctx = canvas.getContext('2d');

    // Destruir gráfico anterior si existe
    if (chartPorHora) {
        chartPorHora.destroy();
    }

    chartPorHora = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: labels,
            datasets: [{
                label: 'Atenciones',
                data: data,
                backgroundColor: 'rgba(59, 130, 246, 0.8)',
                borderColor: 'rgb(59, 130, 246)',
                borderWidth: 1,
                borderRadius: 4
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                },
                tooltip: {
                    backgroundColor: 'rgba(0, 0, 0, 0.8)',
                    padding: 12,
                    titleFont: {
                        size: 14
                    },
                    bodyFont: {
                        size: 13
                    },
                    callbacks: {
                        label: function(context) {
                            return 'Atenciones: ' + context.parsed.y;
                        }
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        stepSize: 1,
                        font: {
                            size: 10
                        }
                    },
                    grid: {
                        color: 'rgba(0, 0, 0, 0.05)'
                    }
                },
                x: {
                    ticks: {
                        font: {
                            size: 9
                        },
                        maxRotation: 90,
                        minRotation: 45
                    },
                    grid: {
                        display: false
                    }
                }
            }
        }
    });
}

/**
 * Actualizar elemento del DOM
 */
function actualizarElemento(id, valor) {
    const elemento = document.getElementById(id);
    if (elemento) {
        elemento.textContent = valor;
    }
}

/**
 * Formatear fecha
 */
function formatearFecha(fecha) {
    const fechaObj = new Date(fecha + 'T00:00:00');
    const opciones = { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' };
    return fechaObj.toLocaleDateString('es-ES', opciones);
}

/**
 * Formatear tiempo en minutos a formato legible
 */
function formatearTiempo(minutos) {
    if (minutos < 60) {
        return minutos + ' min';
    }

    const horas = Math.floor(minutos / 60);
    const mins = minutos % 60;

    if (mins === 0) {
        return horas + ' h';
    }

    return horas + 'h ' + mins + 'm';
}

/**
 * Actualizar todo el dashboard
 */
function actualizarDashboardEstadisticas() {
    cargarEstadisticasPrincipales();
    cargarGraficoTendencias();
    cargarGraficoPorHora();
}

/**
 * Limpiar estadísticas (llamado cuando se cambia de vista)
 */
function limpiarEstadisticasAtenciones() {
    if (chartTendencias) {
        chartTendencias.destroy();
        chartTendencias = null;
    }

    if (chartPorHora) {
        chartPorHora.destroy();
        chartPorHora = null;
    }
}

// Exportar funciones globalmente
window.inicializarDashboardEstadisticas = inicializarDashboardEstadisticas;
window.limpiarEstadisticasAtenciones = limpiarEstadisticasAtenciones;
window.actualizarDashboardEstadisticas = actualizarDashboardEstadisticas;
