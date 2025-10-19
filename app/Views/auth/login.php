<?php
$title = 'Sistema Hospitalario - Ingreso';
echo $this->include('templates/header')
?>
<body class="bg-gradient-to-br from-blue-50 via-white to-green-50 min-h-screen flex items-center justify-center p-4">
    
    <!-- Formas decorativas médicas -->
    <div class="absolute inset-0 overflow-hidden pointer-events-none">
        <div class="absolute top-20 left-10 w-32 h-32 bg-blue-100 opacity-20 rounded-full"></div>
        <div class="absolute top-60 right-20 w-24 h-24 bg-green-100 opacity-20 rounded-full"></div>
        <div class="absolute bottom-32 left-1/4 w-16 h-16 bg-blue-200 opacity-20 rounded-full"></div>
    </div>

    <div class="w-full max-w-md relative">
        <!-- Tarjeta principal -->
        <div class="bg-white rounded-2xl shadow-2xl overflow-hidden border border-gray-100">
            
            <!-- Header -->
            <div class="bg-gradient-to-r from-blue-600 to-blue-700 px-8 py-8 text-center text-white">
                <div class="w-16 h-16 bg-white/20 rounded-full mx-auto mb-4 flex items-center justify-center">
                    <i class="fas fa-hospital text-2xl"></i>
                </div>
                <h2 class="text-2xl font-bold mb-2">Sistema Hospitalario</h2>
            </div>

            <!-- Body -->
            <div class="px-8 py-8">
                
                <!-- Mensaje de error -->
                <?php if (session()->getFlashdata('error')): ?>
                    <div class="bg-red-50 border-l-4 border-red-400 text-red-700 p-4 mb-6 rounded-r-lg">
                        <div class="flex items-center">
                            <i class="fas fa-exclamation-circle mr-3 text-red-400"></i>
                            <span class="text-sm font-medium"><?= session()->getFlashdata('error') ?></span>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- Formulario -->
                <form action="<?= base_url('login/ingresar') ?>" method="post" class="space-y-6">
                    
                    <!-- Campo Usuario -->
                    <div class="space-y-2">
                        <label for="usuario" class="block text-sm font-semibold text-gray-700 flex items-center">
                            <i class="fas fa-user-md mr-2 text-blue-600"></i>
                            Usuario
                        </label>
                        <div class="relative">
                            <input 
                                type="text" 
                                name="usuario" 
                                id="usuario" 
                                class="w-full px-4 py-4 border-2 border-gray-200 rounded-lg focus:border-blue-500 focus:ring-2 focus:ring-blue-100 transition-all duration-200 bg-gray-50 focus:bg-white text-gray-800 placeholder-gray-500" 
                                placeholder="Ingresa tu código de usuario"
                                required>
                        </div>
                    </div>

                    <!-- Campo Contraseña -->
                    <div class="space-y-2">
                        <label for="password" class="block text-sm font-semibold text-gray-700 flex items-center">
                            <i class="fas fa-key mr-2 text-blue-600"></i>
                            Contraseña
                        </label>
                        <div class="relative">
                            <input 
                                type="password" 
                                name="password" 
                                id="password" 
                                class="w-full px-4 py-4 border-2 border-gray-200 rounded-lg focus:border-blue-500 focus:ring-2 focus:ring-blue-100 transition-all duration-200 bg-gray-50 focus:bg-white text-gray-800 placeholder-gray-500" 
                                placeholder="Ingresa tu contraseña"
                                required>
                        </div>
                    </div>

                    <!-- Botón de ingreso -->
                    <button 
                        type="submit" 
                        class="w-full bg-gradient-to-r from-blue-600 to-blue-700 hover:from-blue-700 hover:to-blue-800 text-white font-semibold py-4 px-6 rounded-lg transition-all duration-200 transform hover:scale-[1.02] hover:shadow-lg focus:outline-none focus:ring-4 focus:ring-blue-200">
                        <i class="fas fa-sign-in-alt mr-2"></i>
                        Acceder al Sistema
                    </button>
                </form>

                <!-- Información de seguridad -->
                <div class="mt-8 p-4 bg-blue-50 rounded-lg border-l-4 border-blue-400">
                    <div class="flex items-start">
                        <i class="fas fa-shield-alt text-blue-500 mt-1 mr-3"></i>
                        <div>
                            <p class="text-xs text-blue-800 font-medium mb-1">Acceso Seguro</p>
                            <p class="text-xs text-blue-600">
                                Este sistema está protegido y es de uso exclusivo para personal autorizado del hospital.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Footer -->
        <div class="text-center mt-6 space-y-2">
            <p class="text-gray-600 text-sm font-medium">
                <i class="fas fa-hospital-alt mr-2"></i>
                Sistema de Gestión Hospitalaria
            </p>
            <p class="text-gray-500 text-xs">
                © 2025
            </p>
        </div>
    </div>

    <!-- Scripts específicos del login -->
    <script>
        // Efecto de focus mejorado para inputs
        document.addEventListener('DOMContentLoaded', function() {
            const inputs = document.querySelectorAll('input[type="text"], input[type="password"]');

            inputs.forEach(input => {
                input.addEventListener('focus', function() {
                    this.parentElement.parentElement.classList.add('transform', 'scale-[1.02]');
                });

                input.addEventListener('blur', function() {
                    this.parentElement.parentElement.classList.remove('transform', 'scale-[1.02]');
                });
            });

            // Validación básica en tiempo real
            const form = document.querySelector('form');
            const button = form.querySelector('button[type="submit"]');

            inputs.forEach(input => {
                input.addEventListener('input', function() {
                    const allFilled = Array.from(inputs).every(inp => inp.value.trim() !== '');
                    if (allFilled) {
                        button.classList.add('ring-2', 'ring-blue-300');
                    } else {
                        button.classList.remove('ring-2', 'ring-blue-300');
                    }
                });
            });
        });
    </script>

<?= $this->include('templates/footer') ?>