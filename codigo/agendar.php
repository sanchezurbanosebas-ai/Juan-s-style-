<?php
session_start();
require 'conexion.php';

// Protección: Solo entra si es cliente
if (!isset($_SESSION['usuario_id']) || $_SESSION['rol'] != 'cliente') {
    header("Location: login.php");
    exit();
}

$mensaje = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id_usuario = $_SESSION['usuario_id'];
    $id_servicio = $_POST['servicio'];
    $fecha = $_POST['fecha'];
    $hora = $_POST['hora'];

    // 1. VALIDACIÓN: Comprobar si la hora ya está ocupada
    $stmt_check = $conn->prepare("SELECT id FROM citas WHERE fecha = ? AND hora = ? AND estado != 'Cancelada'");
    $stmt_check->bind_param("ss", $fecha, $hora);
    $stmt_check->execute();
    $resultado_check = $stmt_check->get_result();

    if ($resultado_check->num_rows > 0) {
        $mensaje = "<div class='alert alert-warning'>Lo sentimos, esa hora ya está reservada. Elige otra.</div>";
    } else {
        // 2. GUARDAR CITA si está disponible
        $stmt_insert = $conn->prepare("INSERT INTO citas (id_usuario, id_servicio, fecha, hora) VALUES (?, ?, ?, ?)");
        $stmt_insert->bind_param("iiss", $id_usuario, $id_servicio, $fecha, $hora);
        
        if ($stmt_insert->execute()) {
            $mensaje = "<div class='alert alert-success'>Cita agendada correctamente. <a href='mis_citas.php'>Ver mis citas</a></div>";
        } else {
            $mensaje = "<div class='alert alert-danger'>Error al agendar la cita.</div>";
        }
        $stmt_insert->close();
    }
    $stmt_check->close();
}

// Obtener servicios para llenar el select
$servicios = $conn->query("SELECT * FROM servicios");
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Agendar Cita</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <?php include 'navbar.php'; // Navbar genérico ?>
    <div class="container mt-5">
        <h2>Hola, <?php echo $_SESSION['nombre']; ?> - Agenda tu cita</h2>
        <?php echo $mensaje; ?>
        <div class="card shadow mt-4">
            <div class="card-body">
                <form method="POST" action="">
                    <div class="mb-3">
                        <label>Selecciona el Servicio</label>
                        <select name="servicio" class="form-control" required>
                            <option value="">-- Elige un servicio --</option>
                            <?php while($row = $servicios->fetch_assoc()): ?>
                                <option value="<?php echo $row['id']; ?>">
                                    <?php echo $row['nombre'] . " - $" . number_format($row['precio'], 0); ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label>Fecha</label>
                        <input type="date" name="fecha" class="form-control" required min="<?php echo date('Y-m-d'); ?>">
                    </div>
                    <div class="mb-3">
                        <label>Hora</label>
                        <input type="time" name="hora" class="form-control" required>
                    </div>
                    <button type="submit" class="btn btn-dark w-100">Confirmar Cita</button>
                </form>
            </div>
        </div>
    </div>
</body>
</html>