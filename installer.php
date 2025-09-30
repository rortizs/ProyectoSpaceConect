<?php
/**
 * INSTALADOR SIMPLE DEL SISTEMA SPACECONNECT
 * ==========================================
 * Instalador automático para usuarios sin conocimientos técnicos
 * 
 * PASOS SIMPLES:
 * 1. Crear una base de datos VACÍA en phpMyAdmin (XAMPP)
 * 2. Abrir este archivo en el navegador
 * 3. Seguir las 3 pantallas del instalador
 * 4. ¡Listo! El sistema estará funcionando
 */

session_start();

// Verificar si ya está instalado
if (file_exists('Config/Config.php') && !isset($_GET['force'])) {
    $config_content = file_get_contents('Config/Config.php');
    if (strpos($config_content, 'DB_NAME') !== false && strpos($config_content, 'localhost') !== false) {
        die('<h2 style="color: green;">✅ El sistema ya está instalado.</h2><p>Si necesitas reinstalar, agrega ?force=1 a la URL</p>');
    }
}

$step = isset($_GET['step']) ? (int)$_GET['step'] : 1;
$errors = [];
$success = [];

// Procesar formularios
if ($_POST) {
    switch ($step) {
        case 1:
            // Validar conexión a base de datos
            $db_host = $_POST['db_host'] ?? '';
            $db_name = $_POST['db_name'] ?? '';
            $db_user = $_POST['db_user'] ?? '';
            $db_pass = $_POST['db_pass'] ?? '';
            
            if (empty($db_host) || empty($db_name) || empty($db_user)) {
                $errors[] = "Todos los campos de base de datos son obligatorios excepto la contraseña";
            } else {
                // Probar conexión
                try {
                    $connection = new mysqli($db_host, $db_user, $db_pass, $db_name);
                    if ($connection->connect_error) {
                        throw new Exception($connection->connect_error);
                    }
                    
                    // Guardar datos en sesión
                    $_SESSION['install_data'] = [
                        'db_host' => $db_host,
                        'db_name' => $db_name,
                        'db_user' => $db_user,
                        'db_pass' => $db_pass
                    ];
                    
                    header('Location: installer.php?step=2');
                    exit;
                    
                } catch (Exception $e) {
                    $errors[] = "Error de conexión: " . $e->getMessage();
                }
            }
            break;
            
        case 2:
            // Configurar empresa y administrador
            $company_name = $_POST['company_name'] ?? '';
            $admin_user = $_POST['admin_user'] ?? '';
            $admin_pass = $_POST['admin_pass'] ?? '';
            $admin_email = $_POST['admin_email'] ?? '';
            
            if (empty($company_name) || empty($admin_user) || empty($admin_pass)) {
                $errors[] = "Todos los campos son obligatorios";
            } else {
                $_SESSION['install_data']['company_name'] = $company_name;
                $_SESSION['install_data']['admin_user'] = $admin_user;
                $_SESSION['install_data']['admin_pass'] = $admin_pass;
                $_SESSION['install_data']['admin_email'] = $admin_email;
                
                header('Location: installer.php?step=3');
                exit;
            }
            break;
            
        case 3:
            // Ejecutar instalación
            try {
                $data = $_SESSION['install_data'];
                
                // 1. Crear archivo de configuración
                $config_content = "<?php
const BASE_URL = 'http://' . \$_SERVER['HTTP_HOST'];
const DB_HOST = '{$data['db_host']}';
const DB_NAME = '{$data['db_name']}';
const DB_USER = '{$data['db_user']}';
const DB_PASSWORD = '{$data['db_pass']}';
const DB_CHARSET = 'utf8';

// Configuración de encriptación
const SECRET_KEY = '" . bin2hex(random_bytes(32)) . "';
const SECRET_IV = '" . bin2hex(random_bytes(16)) . "';
const METHOD = 'AES-256-CBC';

// Configuración de empresa
const COMPANY_NAME = '{$data['company_name']}';
const COMPANY_EMAIL = '{$data['admin_email']}';

// Configuración de zona horaria
date_default_timezone_set('America/Lima');

// Configuración de errores
error_reporting(E_ALL);
ini_set('display_errors', 1);
?>";

                if (!file_put_contents('Config/Config.php', $config_content)) {
                    throw new Exception("No se pudo crear el archivo de configuración");
                }
                
                // 2. Conectar a la base de datos
                $connection = new mysqli($data['db_host'], $data['db_user'], $data['db_pass'], $data['db_name']);
                if ($connection->connect_error) {
                    throw new Exception("Error de conexión: " . $connection->connect_error);
                }
                
                // 3. Ejecutar script de base de datos
                $sql_file = 'sql/base_datos_clean_install.sql';
                if (!file_exists($sql_file)) {
                    throw new Exception("No se encontró el archivo de base de datos");
                }
                
                $sql_content = file_get_contents($sql_file);
                $connection->multi_query($sql_content);
                
                // Procesar todos los resultados
                do {
                    if ($result = $connection->store_result()) {
                        $result->free();
                    }
                } while ($connection->next_result());
                
                // 4. Ejecutar migración de Queue Tree
                $migration_file = 'sql/migration_queue_tree.sql';
                if (file_exists($migration_file)) {
                    $migration_content = file_get_contents($migration_file);
                    $connection->multi_query($migration_content);
                    
                    do {
                        if ($result = $connection->store_result()) {
                            $result->free();
                        }
                    } while ($connection->next_result());
                }
                
                // 5. Crear usuario administrador
                $admin_pass_hash = password_hash($data['admin_pass'], PASSWORD_DEFAULT);
                $admin_sql = "INSERT INTO users (names, surnames, email, phone, username, password, roleid, state) 
                             VALUES ('{$data['admin_user']}', 'Administrador', '{$data['admin_email']}', '', 
                                     '{$data['admin_user']}', '$admin_pass_hash', 1, 1)";
                
                if (!$connection->query($admin_sql)) {
                    throw new Exception("Error al crear usuario administrador: " . $connection->error);
                }
                
                // 6. Crear archivo de instalación completada
                file_put_contents('installation_completed.lock', date('Y-m-d H:i:s') . " - Instalación completada\n");
                
                $success[] = "✅ Instalación completada exitosamente";
                $step = 4;
                
            } catch (Exception $e) {
                $errors[] = "Error durante la instalación: " . $e->getMessage();
            }
            break;
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Instalador SpaceConnect</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            margin: 0;
            padding: 20px;
            min-height: 100vh;
        }
        .container {
            max-width: 600px;
            margin: 0 auto;
            background: white;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
            overflow: hidden;
        }
        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 30px;
            text-align: center;
        }
        .header h1 {
            margin: 0;
            font-size: 2.5em;
        }
        .header p {
            margin: 10px 0 0 0;
            opacity: 0.9;
        }
        .content {
            padding: 30px;
        }
        .step-indicator {
            display: flex;
            justify-content: center;
            margin-bottom: 30px;
        }
        .step {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: #e0e0e0;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 10px;
            font-weight: bold;
            color: #666;
        }
        .step.active {
            background: #667eea;
            color: white;
        }
        .step.completed {
            background: #4caf50;
            color: white;
        }
        .form-group {
            margin-bottom: 20px;
        }
        label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #333;
        }
        input[type="text"], input[type="password"], input[type="email"] {
            width: 100%;
            padding: 12px;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            font-size: 16px;
            transition: border-color 0.3s;
        }
        input:focus {
            outline: none;
            border-color: #667eea;
        }
        .btn {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 15px 30px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-size: 16px;
            font-weight: 600;
            width: 100%;
            transition: transform 0.2s;
        }
        .btn:hover {
            transform: translateY(-2px);
        }
        .alert {
            padding: 15px;
            margin: 20px 0;
            border-radius: 8px;
            border-left: 4px solid;
        }
        .alert-error {
            background-color: #ffebee;
            color: #c62828;
            border-left-color: #c62828;
        }
        .alert-success {
            background-color: #e8f5e8;
            color: #2e7d32;
            border-left-color: #2e7d32;
        }
        .info-box {
            background: #f8f9fa;
            border: 1px solid #e9ecef;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 20px;
        }
        .final-info {
            text-align: center;
            padding: 20px;
        }
        .final-info h3 {
            color: #4caf50;
            margin-bottom: 20px;
        }
        .credentials {
            background: #f8f9fa;
            border-radius: 8px;
            padding: 20px;
            margin: 20px 0;
            text-align: left;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🚀 SpaceConnect</h1>
            <p>Instalador del Sistema de Gestión ISP</p>
        </div>
        
        <div class="content">
            <div class="step-indicator">
                <div class="step <?php echo $step >= 1 ? ($step > 1 ? 'completed' : 'active') : ''; ?>">1</div>
                <div class="step <?php echo $step >= 2 ? ($step > 2 ? 'completed' : 'active') : ''; ?>">2</div>
                <div class="step <?php echo $step >= 3 ? ($step > 3 ? 'completed' : 'active') : ''; ?>">3</div>
                <div class="step <?php echo $step >= 4 ? 'active' : ''; ?>">4</div>
            </div>

            <?php if (!empty($errors)): ?>
                <div class="alert alert-error">
                    <?php foreach ($errors as $error): ?>
                        <p><?php echo htmlspecialchars($error); ?></p>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <?php if (!empty($success)): ?>
                <div class="alert alert-success">
                    <?php foreach ($success as $msg): ?>
                        <p><?php echo htmlspecialchars($msg); ?></p>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <?php if ($step == 1): ?>
                <h2>Paso 1: Datos de tu Base de Datos</h2>
                <div class="info-box">
                    <p><strong>📋 ¿Ya creaste la base de datos vacía en phpMyAdmin?</strong></p>
                    <ul>
                        <li>✅ Si ya la creaste, continúa llenando los datos abajo</li>
                        <li>❌ Si no la has creado, ve a phpMyAdmin y crea una base de datos vacía primero</li>
                        <li>💡 Normalmente en XAMPP el usuario es "root" y la contraseña está vacía</li>
                    </ul>
                </div>
                
                <form method="POST">
                    <div class="form-group">
                        <label for="db_host">🖥️ Servidor (normalmente es localhost):</label>
                        <input type="text" id="db_host" name="db_host" value="localhost" required readonly style="background-color: #f0f0f0;">
                    </div>
                    
                    <div class="form-group">
                        <label for="db_name">📁 Nombre de tu base de datos (la que creaste vacía):</label>
                        <input type="text" id="db_name" name="db_name" required placeholder="Ejemplo: spaceconnect_db">
                    </div>
                    
                    <div class="form-group">
                        <label for="db_user">👤 Usuario (normalmente es "root"):</label>
                        <input type="text" id="db_user" name="db_user" value="root" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="db_pass">🔐 Contraseña (déjalo vacío si no tienes):</label>
                        <input type="password" id="db_pass" name="db_pass" placeholder="Déjalo vacío si no configuraste contraseña">
                    </div>
                    
                    <button type="submit" class="btn">Probar Conexión y Continuar</button>
                </form>

            <?php elseif ($step == 2): ?>
                <h2>Paso 2: Datos de tu Empresa</h2>
                <div class="info-box">
                    <p><strong>🏢 Información básica:</strong></p>
                    <p>Ingresa el nombre de tu empresa y crea tu usuario administrador para acceder al sistema.</p>
                </div>
                
                <form method="POST">
                    <div class="form-group">
                        <label for="company_name">🏢 Nombre de tu empresa:</label>
                        <input type="text" id="company_name" name="company_name" required placeholder="Ejemplo: Internet Rápido S.A.">
                    </div>
                    
                    <div class="form-group">
                        <label for="admin_user">👤 Tu nombre de usuario (para entrar al sistema):</label>
                        <input type="text" id="admin_user" name="admin_user" required placeholder="Ejemplo: admin">
                    </div>
                    
                    <div class="form-group">
                        <label for="admin_pass">🔐 Tu contraseña (para entrar al sistema):</label>
                        <input type="password" id="admin_pass" name="admin_pass" required placeholder="Mínimo 6 caracteres">
                    </div>
                    
                    <div class="form-group">
                        <label for="admin_email">📧 Tu email (opcional):</label>
                        <input type="email" id="admin_email" name="admin_email" placeholder="tu@email.com">
                    </div>
                    
                    <button type="submit" class="btn">Continuar con la Instalación</button>
                </form>

            <?php elseif ($step == 3): ?>
                <h2>Paso 3: ¡Instalar el Sistema!</h2>
                <div class="info-box">
                    <p><strong>🚀 ¡Ya casi terminamos!</strong></p>
                    <p>Haz clic en el botón de abajo y el sistema se instalará automáticamente.</p>
                    <p>Esto puede tomar unos segundos... ¡Ten paciencia!</p>
                </div>
                
                <form method="POST">
                    <button type="submit" class="btn">🎯 ¡INSTALAR AHORA!</button>
                </form>

            <?php elseif ($step == 4): ?>
                <div class="final-info">
                    <h3>🎉 ¡Instalación Completada!</h3>
                    <p>El sistema SpaceConnect ha sido instalado exitosamente.</p>
                    
                    <div class="credentials">
                        <h4>📋 Datos de Acceso:</h4>
                        <p><strong>URL del Sistema:</strong> <a href="<?php echo 'http://' . $_SERVER['HTTP_HOST'] . dirname($_SERVER['REQUEST_URI']); ?>" target="_blank"><?php echo 'http://' . $_SERVER['HTTP_HOST'] . dirname($_SERVER['REQUEST_URI']); ?></a></p>
                        <p><strong>Usuario:</strong> <?php echo htmlspecialchars($_SESSION['install_data']['admin_user']); ?></p>
                        <p><strong>Contraseña:</strong> <?php echo htmlspecialchars($_SESSION['install_data']['admin_pass']); ?></p>
                    </div>
                    
                    <div class="info-box">
                        <h4>⚠️ ¡IMPORTANTE! Haz esto ahora:</h4>
                        <ol>
                            <li><strong>🗑️ ELIMINA el archivo "installer.php"</strong> de tu servidor por seguridad</li>
                            <li>🔗 Haz clic en el botón de abajo para entrar a tu sistema</li>
                            <li>🔐 Guarda bien tu usuario y contraseña</li>
                        </ol>
                    </div>
                    
                    <a href="<?php echo 'http://' . $_SERVER['HTTP_HOST'] . dirname($_SERVER['REQUEST_URI']); ?>" class="btn">🎉 ¡ENTRAR A MI SISTEMA!</a>
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>