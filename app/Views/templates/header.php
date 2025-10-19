<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="">
    <meta name="author" content="">

    <title><?= isset($title) ? esc($title) : 'Sistema Hospitalario - Formulario Digital MSP 008' ?></title>

    <!-- Font Awesome (local + CDN como respaldo) -->
    <link rel="stylesheet" href="<?= base_url('public/js/vendor/fontawesome-free/css/all.min.css') ?>">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <!-- Google Fonts con fallback local -->
    <link href="https://fonts.googleapis.com/css?family=Nunito:200,200i,300,300i,400,400i,600,600i,700,700i,800,800i,900,900i" rel="stylesheet">
    <style>
        /* Fallback font si Google Fonts no carga */
        @font-face {
            font-family: 'Nunito-fallback';
            src: local('Arial'), local('Helvetica'), local('sans-serif');
        }

        body {
            font-family: 'Nunito', 'Nunito-fallback', Arial, Helvetica, sans-serif;
        }
    </style>

    <!-- Bootstrap 5 -->
    <link rel="stylesheet" href="<?= base_url('public/css/bootstrap5CSS/bootstrap.min.css') ?>">

    <!-- DataTables CSS con Bootstrap 5 -->
    <link rel="stylesheet" href="<?= base_url('public/css/bootstrap5CSS/dataTables.bootstrap5.css') ?>">

    <!-- Estilos locales -->
    <link rel="stylesheet" href="<?= base_url('public/css/styles.css') ?>">
    <!-- TailwindCSS compilado con PostCSS (producción) -->
    <link rel="stylesheet" href="<?= base_url('public/css/tailwind.css') ?>">
    <link rel="stylesheet" href="<?= base_url('public/css/jquery-ui.css') ?>">

    <!-- SweetAlert2 CSS -->
    <link rel="stylesheet" href="<?= base_url('node_modules/sweetalert2/dist/sweetalert2.min.css') ?>">

    <!-- CSS específico para reportes (administrador y especialidades) -->
    <?php if (session()->get('rol_id') == 1 || session()->get('rol_id') == 5): ?>
        <link rel="stylesheet" href="<?= base_url('public/css/reportesCss.css') ?>">
    <?php endif; ?>

    <!-- jQuery (local Bootstrap5JS version) -->
    <script src="<?= base_url('public/js/bootstrap5JS/jquery-3.7.1.js') ?>"></script>
    <script src="<?= base_url('public/js/jquery-ui.min.js') ?>"></script>

    <!-- Variables globales de JavaScript -->
    <script>
        const BASE_URL = '<?= base_url() ?>';
        const CURRENT_USER_ID = <?= session()->get('usu_id') ?? 'null' ?>;
        window.base_url = '<?= base_url() ?>';
    </script>

</head>