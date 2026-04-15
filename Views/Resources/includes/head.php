<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <meta name="author" content="<?= DEVELOPER ?>">
    <meta name="theme-color" content="#00acac">
    
    <!-- DNS Prefetch & Preconnect for performance -->
    <link rel="dns-prefetch" href="//fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.googleapis.com" crossorigin>
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    
    <?php
    if (!empty($_SESSION['businessData']['favicon'])) {
        if ($_SESSION['businessData']['favicon'] == "favicon.png") {
            $favicon = base_style() . '/images/logotypes/' . $_SESSION['businessData']['favicon'];
        } else {
            $favicon_url = base_style() . '/uploads/business/' . $_SESSION['businessData']['favicon'];
            if (@getimagesize($favicon_url)) {
                $favicon = base_style() . '/uploads/business/' . $_SESSION['businessData']['favicon'];
            } else {
                $favicon = base_style() . '/images/logotypes/favicon.png';
            }
        }
    } else {
        $favicon = base_style() . '/images/logotypes/favicon.png';
    }
    
    // Version for cache busting (change only when assets actually change)
    $assets_version = '1.0.2';
    ?>
    <!-- ================== INICIO ICONO ================== -->
    <link rel="icon" type="image/x-icon" href="<?= $favicon ?>">
    <!-- ================== FIN ICONO ===================== -->
    <!-- ================== INICIO ARCHIVOS CSS =========== -->
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Open+Sans:300,400,600,700&display=swap" />
    <link rel="stylesheet" href="<?= base_style() ?>/css/default/app.min.css?v=<?= $assets_version ?>">
    <link rel="stylesheet" href="<?= base_style() ?>/css/datatables.min.css?v=<?= $assets_version ?>" />
    <link rel="stylesheet" href="<?= base_style() ?>/css/superwisp.css?v=<?= $assets_version ?>">
    <link rel="stylesheet" href="<?= base_style() ?>/css/jquery-confirm.min.css?v=<?= $assets_version ?>">
    <link rel="stylesheet" href="<?= base_style() ?>/css/gritter.css?v=<?= $assets_version ?>">
    <link rel="stylesheet" href="<?= base_style() ?>/bookstores/simple-line-icons/css/simple-line-icons.css?v=<?= $assets_version ?>">
    <link rel="stylesheet" href="<?= base_style() ?>/bookstores/ionicons/css/ionicons.min.css?v=<?= $assets_version ?>">
    <link rel="stylesheet" href="<?= base_style() ?>/bookstores/gritter/css/jquery.gritter.css?v=<?= $assets_version ?>" />
    <link rel="stylesheet" href="<?= base_style() ?>/bookstores/select2/css/select2.min.css?v=<?= $assets_version ?>">
    <link rel="stylesheet" href="<?= base_style() ?>/bookstores/smartwizard/css/smart_wizard.css?v=<?= $assets_version ?>">
    <link rel="stylesheet"
        href="<?= base_style() ?>/bookstores/bootstrap-datetimepicker/css/bootstrap-datetimepicker.min.css?v=<?= $assets_version ?>">
    <link rel="stylesheet" href="<?= base_style() ?>/bookstores/lightbox/css/lightbox.css?v=<?= $assets_version ?>">
    <link rel="stylesheet" href="<?= base_style() ?>/css/custom.css?v=<?= $assets_version ?>">
    <!-- ================== FIN ARCHIVOS CSS ============== -->
    <!-- ================== INICIO TITULO ================= -->
    <title><?= $data['page_name'] ?></title>
    <!-- ================== FIN TITULO =================== -->

</head>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<body class="pace-done pace-done">
    <div id="loading"><span class="loading-spinner"></span></div>
    <div id="page-container" class="page-container fade page-sidebar-fixed page-header-fixed">
        <!-- ================== INICIO CABEZERA =============== -->
        <?php
        $current = explode("/", $_GET['route']);
        if (!isset($current[1])) $current[1] = '';
        require_once("navbar.php");
        require_once("sidemenu.php");
        ?>
        <!-- ================== FIN CABEZERA ================== -->
        <!-- ================== INICIO CONT. PAGINA =========== -->
        <div id="content" class="content">
