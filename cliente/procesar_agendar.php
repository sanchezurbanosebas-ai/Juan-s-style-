<?php
session_start();
require_once '../conexion.php';

if (!isset($_SESSION['usuario_id']) || $_SESSION['rol'] !== 'cliente') {
    header("Location: ../login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $usuario_id = $_SESSION['usuario_id'];
    $servicio_id = intval($_POST['servicio_id']);
    $barbero_id = intval($_POST['barbero_id']);
    $fecha_cita = $_POST['fecha_cita'];
    $hora_cita = $_POST['hora_cita'];

    if (empty($servicio_id) || empty($barbero_id) || empty($fecha_cita) || empty($hora_cita)) {
        header("Location: agendar.php?error=campos_vacios");
        exit();
    }

    // Verificar disponibilidad del barbero en la fecha y hora seleccionada (excluyendo canceladas)
    $sql_verificar = "SELECT id FROM citas 
                      WHERE barbero_id = ? AND fecha_cita = ? AND hora_cita = ? AND estado != 'Cancelada'";
    $stmt_verificar = $pdo->prepare($sql_verificar);
    $stmt_verificar->execute([$barbero_id, $fecha_cita, $hora_cita]);

    if ($stmt_verificar->rowCount() > 0) {
        header("Location: agendar.php?error=hora_ocupada");
        exit();
    }

    // Insertar la cita
    $sql_insertar = "INSERT INTO citas (usuario_id, barbero_id, servicio_id, fecha_cita, hora_cita, estado) 
                     VALUES (?, ?, ?, ?, ?, 'Pendiente')";
    $stmt_insertar = $pdo->prepare($sql_insertar);

    if ($stmt_insertar->execute([$usuario_id, $barbero_id, $servicio_id, $fecha_cita, $hora_cita])) {
        header("Location: mis_citas.php?exito=creada");
        exit();
    } else {
        header("Location: agendar.php?error=error_bd");
        exit();
    }
}
?>