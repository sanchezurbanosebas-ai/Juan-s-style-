<?php
require_once 'conexion.php';

$correo = 'admin@barberia.com';
$clave = 'admin123';
$hash = password_hash($clave, PASSWORD_DEFAULT);

// Eliminar el registro previo para evitar duplicados
$conn->query("DELETE FROM usuarios WHERE correo = '$correo'");

// Insertar el administrador con la contraseña encriptada correctamente
$stmt = $conn->prepare("INSERT INTO usuarios (nombre, apellido, correo, password, rol) VALUES ('Admin', 'Principal', ?, ?, 'admin')");
$stmt->bind_param("ss", $correo, $hash);

if ($stmt->execute()) {
    echo "<h2 style='color: green;'>¡Administrador configurado con éxito!</h2>";
    echo "<p><strong>Correo:</strong> admin@barberia.com</p>";
    echo "<p><strong>Contraseña:</strong> admin123</p>";
    echo "<a href='login.php'>Volver al Login</a>";
} else {
    echo "Error al crear administrador: " . $conn->error;
}
?>
