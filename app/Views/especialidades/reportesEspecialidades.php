<!-- Modificación para el archivo ListaEspecialidades.php -->
<!-- Reemplazar la sección del header verde con esta versión mejorada -->

<!-- Header Principal con botón de reportes -->
<div class="bg-white rounded-lg shadow-md mb-6 relative">

    <!-- Botón de Reportes posicionado sobre la línea verde -->
    <button id="btnReportes"
            class="absolute left-4 top-2 bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors duration-200 flex items-center shadow-lg z-10">
        <i class="fas fa-chart-line mr-2"></i>
        Reportes
    </button>

    <!-- Modal de Autenticación para Reportes -->
    <div id="modalAutenticacionReportes" class="fixed inset-0 bg-black bg-opacity-50 z-50 hidden flex items-center justify-center">
        <div class="bg-white rounded-lg p-6 w-96 max-w-md mx-4 shadow-2xl">
            <div class="text-center mb-6">
                <div class="w-16 h-16 bg-blue-100 rounded-full mx-auto mb-4 flex items-center justify-center">
                    <i class="fas fa-chart-line text-blue-600 text-2xl"></i>
                </div>
                <h2 class="text-xl font-bold text-gray-800">Acceso a Reportes</h2>
                <p class="text-gray-600 text-sm mt-2">Ingrese sus credenciales para acceder al módulo de reportes de especialidades</p>
            </div>
            
            <form id="formAutenticacionReportes">
                <div class="mb-4">
                    <label for="usuarioReportes" class="block text-sm font-medium text-gray-700 mb-2">
                        <i class="fas fa-user mr-1 text-blue-600"></i> Usuario
                    </label>
                    <input type="text" 
                           id="usuarioReportes" 
                           name="usuario" 
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-colors"
                           placeholder="Ingrese su usuario"
                           required>
                </div>
                
                <div class="mb-6">
                    <label for="passwordReportes" class="block text-sm font-medium text-gray-700 mb-2">
                        <i class="fas fa-lock mr-1 text-blue-600"></i> Contraseña
                    </label>
                    <input type="password" 
                           id="passwordReportes" 
                           name="password" 
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-colors"
                           placeholder="Ingrese su contraseña"
                           required>
                </div>
                
                <div id="errorAutenticacionReportes" class="hidden bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded mb-4">
                    <div class="flex items-center">
                        <i class="fas fa-exclamation-circle mr-2"></i>
                        <span id="textoErrorReportes">Credenciales incorrectas</span>
                    </div>
                </div>
                
                <div class="flex space-x-3">
                    <button type="button" 
                            id="btnCancelarReportes"
                            class="flex-1 px-4 py-2 border border-gray-300 text-gray-700 rounded-md hover:bg-gray-50 transition-colors">
                        <i class="fas fa-times mr-2"></i>Cancelar
                    </button>
                    <button type="submit" 
                            id="btnIngresarReportes"
                            class="flex-1 px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 transition-colors disabled:opacity-50">
                        <span class="btn-text">
                            <i class="fas fa-chart-line mr-2"></i>Acceder
                        </span>
                        <span class="loading-icon hidden">
                            <i class="fas fa-spinner fa-spin mr-2"></i>Verificando...
                        </span>
                    </button>
                </div>
            </form>
        </div>
    </div>
    
    <!-- JavaScript para el modal de reportes -->
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const btnReportes = document.getElementById('btnReportes');
        const modalReportes = document.getElementById('modalAutenticacionReportes');
        const formReportes = document.getElementById('formAutenticacionReportes');
        const btnCancelar = document.getElementById('btnCancelarReportes');
        const errorDiv = document.getElementById('errorAutenticacionReportes');
        const btnIngresar = document.getElementById('btnIngresarReportes');

        // Abrir modal de autenticación
        btnReportes.addEventListener('click', function() {
            modalReportes.classList.remove('hidden');
            modalReportes.classList.add('flex');
            document.getElementById('usuarioReportes').focus();
            limpiarFormulario();
        });

        // Cerrar modal
        btnCancelar.addEventListener('click', function() {
            cerrarModal();
        });

        // Cerrar modal al hacer clic fuera
        modalReportes.addEventListener('click', function(e) {
            if (e.target === modalReportes) {
                cerrarModal();
            }
        });

        // Cerrar con Escape
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape' && !modalReportes.classList.contains('hidden')) {
                cerrarModal();
            }
        });

        // Manejar envío del formulario
        formReportes.addEventListener('submit', function(e) {
            e.preventDefault();
            autenticarParaReportes();
        });

        // Enter en el campo de contraseña
        document.getElementById('passwordReportes').addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                autenticarParaReportes();
            }
        });

        function cerrarModal() {
            modalReportes.classList.add('hidden');
            modalReportes.classList.remove('flex');
            limpiarFormulario();
        }

        function limpiarFormulario() {
            formReportes.reset();
            errorDiv.classList.add('hidden');
            restaurarBoton();
        }

        function mostrarCarga() {
            btnIngresar.disabled = true;
            btnIngresar.querySelector('.btn-text').classList.add('hidden');
            btnIngresar.querySelector('.loading-icon').classList.remove('hidden');
        }

        function restaurarBoton() {
            btnIngresar.disabled = false;
            btnIngresar.querySelector('.btn-text').classList.remove('hidden');
            btnIngresar.querySelector('.loading-icon').classList.add('hidden');
        }

        function mostrarError(mensaje) {
            document.getElementById('textoErrorReportes').textContent = mensaje;
            errorDiv.classList.remove('hidden');
            restaurarBoton();
            
            // Enfocar el campo de usuario si hay error
            document.getElementById('usuarioReportes').focus();
        }

        function autenticarParaReportes() {
            const usuario = document.getElementById('usuarioReportes').value.trim();
            const password = document.getElementById('passwordReportes').value;

            if (!usuario || !password) {
                mostrarError('Por favor complete todos los campos');
                return;
            }

            mostrarCarga();

            // Construir la URL correcta
            const baseUrl = window.base_url || '<?= base_url() ?>';
            const url = baseUrl + 'especialidades/reportes/autenticar';

            // Hacer petición AJAX al servidor
            fetch(url, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `usuario=${encodeURIComponent(usuario)}&password=${encodeURIComponent(password)}`
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                return response.json();
            })
            .then(data => {
                if (data.success) {
                    // Autenticación exitosa - redirigir a reportes en nueva pestaña
                    const reportesUrl = baseUrl + 'especialidades/reportes/dashboard';
                    window.open(reportesUrl, '_blank', 'noopener,noreferrer');
                    cerrarModal();
                    
                    // Mostrar mensaje de éxito
                    mostrarNotificacion(`Acceso autorizado para ${data.usuario}`, 'success');
                } else {
                    mostrarError(data.error || 'Credenciales incorrectas');
                }
            })
            .catch(error => {
                console.error('Error en autenticación de reportes:', error);
                mostrarError('Error de conexión. Verifique su red e intente nuevamente.');
            });
        }

        function mostrarNotificacion(mensaje, tipo = 'info') {
            // Crear notificación temporal
            const alertClass = tipo === 'success' ? 'bg-green-500' : tipo === 'error' ? 'bg-red-500' : 'bg-blue-500';
            const iconClass = tipo === 'success' ? 'fa-check-circle' : tipo === 'error' ? 'fa-exclamation-circle' : 'fa-info-circle';
            
            const notification = document.createElement('div');
            notification.className = `fixed top-4 right-4 ${alertClass} text-white px-6 py-4 rounded-lg shadow-lg z-50 max-w-sm`;
            notification.innerHTML = `
                <div class="flex items-center">
                    <i class="fas ${iconClass} mr-3"></i>
                    <span>${mensaje}</span>
                </div>
            `;
            
            document.body.appendChild(notification);
            
            // Remover después de 3 segundos
            setTimeout(() => {
                notification.remove();
            }, 3000);
        }
    });
    </script>