<!-- Librerías de exportación (cargar ANTES de DataTables) -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/pdfmake.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/vfs_fonts.js"></script>

<!-- Bootstrap 5 JS -->
<script src="<?= base_url('public/js/bootstrap5JS/bootstrap.bundle.min.js') ?>"></script>

<!-- DataTables JS con Bootstrap 5 -->
<script src="<?= base_url('public/js/bootstrap5JS/dataTables.js') ?>"></script>
<script src="<?= base_url('public/js/bootstrap5JS/dataTables.bootstrap5.js') ?>"></script>

<!-- DataTables Buttons (para exportación) -->
<script src="https://cdn.datatables.net/buttons/2.4.2/js/dataTables.buttons.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.html5.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.print.min.js"></script>

<!-- SweetAlert2 JS -->
<script src="<?= base_url('node_modules/sweetalert2/dist/sweetalert2.all.min.js') ?>"></script>

<!-- Scripts específicos del sistema -->
<script src="<?= base_url('public/js/jspdf.min.js') ?>"></script>

<?php if (session()->get('rol_id') == 2): ?>
    <script src="<?= base_url('public/js/admision/buscar_paciente.js') ?>"></script>
    <script src="<?= base_url('public/js/admision/buscar_hospital.js') ?>"></script>
    <script src="<?= base_url('public/js/admision/ubicacion.js') ?>"></script>
<?php endif; ?>
<script src="<?= base_url('public/js/alerta-mensaje.js') ?>"></script>
<script src="<?= base_url('public/js/limpiar_formulario.js') ?>"></script>
<script src="<?= base_url('public/js/alertas.js') ?>"></script>
<script src="<?= base_url('public/js/admision/calcularEdad.js') ?>"></script>

<!-- Sistema de timeout de sesión para áreas específicas -->
<script src="<?= base_url('public/js/session-timeout.js') ?>"></script>

<!-- Scripts de enfermería -->
<?php if (session()->get('rol_id') == 3): ?>
    <script src="<?= base_url('public/js/enfermeria/listaEnfermeria.js') ?>"></script>
    <script src="<?= base_url('public/js/enfermeria/precargarDatosEnfermeria.js') ?>"></script>
<?php endif; ?>

<!-- Scripts de médicos -->
<?php if (session()->get('rol_id') == 4): ?>
    <script src="<?= base_url('public/js/medicos/listaMedicos.js') ?>"></script>
    <script src="<?= base_url('public/js/medicos/precargarDatosMedicos.js') ?>"></script>
<?php endif; ?>

<!-- Scripts de administrador -->
<?php if (session()->get('rol_id') == 1): ?>
    <!-- Chart.js para gráficos -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>

    <!-- Cargar primero el coordinador -->
    <script src="<?= base_url('public/js/administrador/panelCoordinator.js') ?>"></script>

    <!-- Luego cargar los módulos específicos -->
    <script src="<?= base_url('public/js/administrador/dashboard-estadisticas.js') ?>"></script>
    <script src="<?= base_url('public/js/administrador/cargarListaUsuarios.js') ?>"></script>
    <script src="<?= base_url('public/js/administrador/cargarListaPacientes.js') ?>"></script>
    <script src="<?= base_url('public/js/administrador/alertasBusquedaFecha.js') ?>"></script>

    <!-- Inicializar dashboard de estadísticas -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Inicializar dashboard de estadísticas cuando se carga la página
            if (typeof inicializarDashboardEstadisticas === 'function') {
                inicializarDashboardEstadisticas();
            }
        });
    </script>
<?php endif; ?>

<!-- Scripts de especialidades -->
<?php if (session()->get('rol_id') == 5): ?>
    <script src="<?= base_url('public/js/especialidades/dashboard-reportes.js') ?>"></script>
<?php endif; ?>

</body>

</html>