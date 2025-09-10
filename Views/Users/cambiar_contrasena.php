<?php

// Habilitar la visualización de errores
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Incluyendo el archivo de conexión
include('/Config/Config.php'); // Ajusta esta línea según la ubicación de Config.php

// Hash generado manualmente
$nuevo_hash = '$2y$10$XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX'; // Coloca aquí el hash generado

// ID del usuario a actualizar
$id_usuario = 1;

// Crear la consulta SQL para actualizar el campo `password`
$sql = "UPDATE usuarios SET password = '$nuevo_hash' WHERE id = $id_usuario";

// Ejecutar la consulta
if ($conn->query($sql) === TRUE) {
    echo "Contraseña actualizada exitosamente.";
} else {
    echo "Error actualizando la contraseña: " . $conn->error;
}

// Cerrar la conexión
$conn->close();
?>
