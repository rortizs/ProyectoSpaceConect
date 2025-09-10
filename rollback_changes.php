<?php
echo "=== ROLLBACK TEMPORAL ===\n";
echo "Revirtiendo cambios para restaurar funcionalidad del sistema...\n\n";

// 1. Hacer backup de nuestros archivos creados
$new_files = [
    'Controllers/SocialMedia.php',
    'Models/SocialmediaModel.php', 
    'Views/SocialMedia/social_media_control.php',
    'Assets/js/functions/functions_social_media.js',
    'Libraries/MikroTik/SocialMediaBlocker.php'
];

echo "1. Haciendo backup de archivos creados...\n";
foreach ($new_files as $file) {
    if (file_exists($file)) {
        $backup_name = $file . '.backup.' . date('YmdHis');
        if (rename($file, $backup_name)) {
            echo "   ✅ Backup: $file -> $backup_name\n";
        }
    }
}

// 2. Revertir cambios en sidemenu.php
echo "\n2. Revirtiendo cambios en sidemenu.php...\n";

$sidemenu_original = '            <?php if (!empty($_SESSION[\'permits\'][RED][\'v\'])) { ?>
                <li class="has-sub <?php if (in_array($current[0], ["zones", "network", "cajaNap", "apclientes", "apemisor", "apreceptor"]))
                    echo "active"; ?>">
                    <a href="javascript:;">
                        <b class="caret"></b>
                        <i class="fa fa-network-wired"></i>
                        <span>Gestión de Red</span>
                    </a>
                    <ul class="sub-menu">
                        <li class="<?php if ($current[0] == "network")
                            echo "active"; ?>">
                            <a href="<?= base_url() ?>/network/routers">Routers</a>
                        </li>';

// Leer el archivo sidemenu
$sidemenu_content = file_get_contents('Views/Resources/includes/sidemenu.php');

// Buscar y reemplazar la sección modificada
$sidemenu_content = str_replace(
    'in_array($current[0], ["zones", "network", "cajaNap", "apclientes", "apemisor", "apreceptor", "socialmedia"])',
    'in_array($current[0], ["zones", "network", "cajaNap", "apclientes", "apemisor", "apreceptor"])',
    $sidemenu_content
);

// Remover la línea de Control de Redes Sociales
$sidemenu_content = preg_replace(
    '/\s*<li class="[^"]*socialmedia[^"]*">\s*<a href="[^"]*socialmedia[^"]*">.*?<\/a>\s*<\/li>/s',
    '',
    $sidemenu_content
);

if (file_put_contents('Views/Resources/includes/sidemenu.php', $sidemenu_content)) {
    echo "   ✅ sidemenu.php revertido\n";
} else {
    echo "   ❌ Error al revertir sidemenu.php\n";
}

// 3. Eliminar archivos temporales de debug
$debug_files = [
    'test_controller.php',
    'test_social_media_blocker.php', 
    'debug_social_media_view.php',
    'rollback_changes.php'
];

echo "\n3. Limpiando archivos de debug...\n";
foreach ($debug_files as $file) {
    if (file_exists($file)) {
        if (unlink($file)) {
            echo "   ✅ Eliminado: $file\n";
        }
    }
}

echo "\n=== ROLLBACK COMPLETADO ===\n";
echo "El sistema debería volver a funcionar normalmente.\n";
echo "Prueba accediendo a: http://online.test/users\n\n";

echo "Si necesitas restaurar el módulo de redes sociales más tarde:\n";
echo "1. Los archivos están guardados con extensión .backup.TIMESTAMP\n";
echo "2. Solo renombra los archivos para restaurarlos\n";
echo "3. Vuelve a agregar la línea del menú en sidemenu.php\n\n";

echo "Archivos respaldados:\n";
foreach ($new_files as $file) {
    $backup_name = $file . '.backup.' . date('YmdHis');
    if (file_exists($backup_name)) {
        echo "   📁 $backup_name\n";
    }
}

echo "\nEl rollback se ejecutará al acceder a este archivo desde el navegador.\n";
echo "Accede a: http://online.test/rollback_changes.php\n";
?>
