<?php
/**
 * SCRIPT DE MIGRACIÓN DE BASE DE DATOS
 * ====================================
 * Este script aplica las migraciones necesarias para resolver problemas
 * de guardado de clientes y funcionalidad de Queue Tree.
 * 
 * INSTRUCCIONES:
 * 1. Subir este archivo al directorio raíz del proyecto
 * 2. Acceder desde el navegador: http://tu-dominio.com/migrate_database.php
 * 3. Seguir las instrucciones en pantalla
 * 
 * IMPORTANTE: Eliminar este archivo después de ejecutar la migración
 */

// Configuración de seguridad
$MIGRATION_PASSWORD = 'SpaceConnect2025!'; // Cambiar esta contraseña por seguridad

// Verificar si ya se ejecutó
if (file_exists('migration_completed.lock')) {
    die('<h2 style="color: green;">✅ La migración ya fue ejecutada anteriormente.</h2><p>Si necesitas ejecutarla nuevamente, elimina el archivo "migration_completed.lock"</p>');
}

// Verificar contraseña si se envió el formulario
if ($_POST && isset($_POST['password'])) {
    if ($_POST['password'] !== $MIGRATION_PASSWORD) {
        $error = "Contraseña incorrecta. Contacta al desarrollador.";
    } else {
        // Ejecutar migración
        try {
            require_once 'Config/Config.php';
            
            // Conectar a la base de datos
            $connection = new mysqli(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
            
            if ($connection->connect_error) {
                throw new Exception("Error de conexión: " . $connection->connect_error);
            }
            
            // Leer el archivo de migración
            $migration_sql = file_get_contents('sql/migration_queue_tree.sql');
            
            if (!$migration_sql) {
                throw new Exception("No se pudo leer el archivo de migración");
            }
            
            // Ejecutar la migración
            $connection->multi_query($migration_sql);
            
            // Procesar todos los resultados
            do {
                if ($result = $connection->store_result()) {
                    $result->free();
                }
            } while ($connection->next_result());
            
            // Verificar errores
            if ($connection->error) {
                throw new Exception("Error en la migración: " . $connection->error);
            }
            
            // Crear archivo de bloqueo
            file_put_contents('migration_completed.lock', date('Y-m-d H:i:s') . " - Migración completada exitosamente\n");
            
            $success = true;
            $message = "✅ Migración ejecutada exitosamente. Las tablas de Queue Tree han sido creadas y los campos faltantes agregados.";
            
        } catch (Exception $e) {
            $error = "❌ Error durante la migración: " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Migración de Base de Datos - SpaceConnect</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 800px;
            margin: 50px auto;
            padding: 20px;
            background-color: #f5f5f5;
        }
        .container {
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .header {
            text-align: center;
            color: #333;
            border-bottom: 2px solid #007bff;
            padding-bottom: 20px;
            margin-bottom: 30px;
        }
        .form-group {
            margin-bottom: 20px;
        }
        label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
            color: #555;
        }
        input[type="password"] {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 16px;
        }
        .btn {
            background-color: #007bff;
            color: white;
            padding: 12px 30px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
            width: 100%;
        }
        .btn:hover {
            background-color: #0056b3;
        }
        .alert {
            padding: 15px;
            margin: 20px 0;
            border-radius: 5px;
        }
        .alert-success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        .alert-error {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        .info-box {
            background-color: #e7f3ff;
            border: 1px solid #b3d9ff;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        .warning-box {
            background-color: #fff3cd;
            border: 1px solid #ffeaa7;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        ul {
            text-align: left;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🔧 Migración de Base de Datos</h1>
            <p>SpaceConnect - Sistema de Gestión ISP</p>
        </div>

        <?php if (isset($success) && $success): ?>
            <div class="alert alert-success">
                <h3><?php echo $message; ?></h3>
                <p><strong>Próximos pasos:</strong></p>
                <ol>
                    <li>Elimina este archivo (migrate_database.php) por seguridad</li>
                    <li>Prueba el sistema de registro de clientes</li>
                    <li>Verifica que la funcionalidad de red funcione correctamente</li>
                </ol>
                <p><strong>⚠️ IMPORTANTE:</strong> Elimina este archivo inmediatamente por seguridad.</p>
            </div>
        <?php elseif (isset($error)): ?>
            <div class="alert alert-error">
                <h3><?php echo $error; ?></h3>
                <p>Si el problema persiste, contacta al desarrollador con el mensaje de error completo.</p>
            </div>
        <?php endif; ?>

        <?php if (!isset($success) || !$success): ?>
            <div class="info-box">
                <h3>📋 ¿Qué hace esta migración?</h3>
                <p>Este script resuelve el problema de guardado de clientes agregando las siguientes tablas y campos:</p>
                <ul>
                    <li><strong>queue_tree_policies:</strong> Políticas de QoS para gestión de ancho de banda</li>
                    <li><strong>client_queue_assignments:</strong> Asignaciones de políticas a clientes</li>
                    <li><strong>queue_tree_templates:</strong> Templates predefinidos para configuración rápida</li>
                    <li><strong>Campos adicionales en tabla clients:</strong> net_ip, nap_cliente_id, ap_cliente_id</li>
                </ul>
            </div>

            <div class="warning-box">
                <h3>⚠️ Antes de continuar:</h3>
                <ul>
                    <li>Asegúrate de tener un respaldo de tu base de datos</li>
                    <li>Verifica que no haya usuarios activos en el sistema</li>
                    <li>Este proceso es seguro y no afectará datos existentes</li>
                </ul>
            </div>

            <form method="POST">
                <div class="form-group">
                    <label for="password">Contraseña de migración:</label>
                    <input type="password" id="password" name="password" required 
                           placeholder="Ingresa la contraseña proporcionada por el desarrollador">
                </div>
                
                <button type="submit" class="btn">🚀 Ejecutar Migración</button>
            </form>

            <div style="margin-top: 30px; text-align: center; color: #666; font-size: 14px;">
                <p>Si no tienes la contraseña, contacta al desarrollador del sistema.</p>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>