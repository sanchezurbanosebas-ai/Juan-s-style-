<?php
require_once 'conexion.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nombre = trim($_POST['nombre']);
    $correo = trim($_POST['correo']);
    $telefono = trim($_POST['telefono']);
    $password = $_POST['password'];
    $confirmar_password = $_POST['confirmar_password'];

    // Validaciones básicas
    if (empty($nombre) || empty($correo) || empty($telefono) || empty($password) || empty($confirmar_password)) {
        header("Location: registro.php?error=campos_vacios");
        exit();
    }

    if (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
        header("Location: registro.php?error=correo_invalido");
        exit();
    }

    if ($password !== $confirmar_password) {
        header("Location: registro.php?error=no_coinciden");
        exit();
    }

    // Verificar si el correo ya existe
    $sql_verificar = "SELECT id FROM usuarios WHERE correo = ?";
    $stmt = $pdo->prepare($sql_verificar);
    $stmt->execute([$correo]);

    if ($stmt->rowCount() > 0) {
        header("Location: registro.php?error=correo_existente");
        exit();
    }

    // Encriptar contraseña
    $password_hash = password_hash($password, PASSWORD_DEFAULT);

    // Insertar nuevo usuario como cliente
    $sql_insertar = "INSERT INTO usuarios (nombre, correo, telefono, password, rol) VALUES (?, ?, ?, ?, 'cliente')";
    $stmt_insertar = $pdo->prepare($sql_insertar);
    
    if ($stmt_insertar->execute([$nombre, $correo, $telefono, $password_hash])) {
        header("Location: login.php?exito=registrado");
        exit();
    } else {
        header("Location: registro.php?error=error_bd");
        exit();
    }
}
?>